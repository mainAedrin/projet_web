<?php
/**
 * api/convoquer.php — Une entreprise convoque un étudiant à un entretien
 * Enregistre en BDD + envoie un courriel (mail() avec repli sur log fichier).
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (role_actuel() !== 'entreprise') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux entreprises.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$data          = json_decode(file_get_contents('php://input'), true);
$etudiantId    = (int) ($data['etudiant_id'] ?? 0);
$dateEntretien = trim($data['date_entretien'] ?? '');   // "2025-06-20T14:30"
$lieu          = trim($data['lieu'] ?? '');
$message       = trim($data['message'] ?? '');

if ($etudiantId <= 0 || $dateEntretien === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Étudiant et date d\'entretien requis.']);
    exit;
}

// ─── Récupérer l'entreprise connectée ────────────────────────────
$stmt = $pdo->prepare('SELECT id, nom FROM entreprises WHERE user_id = ?');
$stmt->execute([utilisateur_id()]);
$entreprise = $stmt->fetch();
if (!$entreprise) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Entreprise introuvable.']);
    exit;
}

// ─── Récupérer l'email de l'étudiant ─────────────────────────────
$stmt = $pdo->prepare("
    SELECT u.email, e.nom, e.prenom
    FROM etudiants e
    INNER JOIN users u ON u.id = e.user_id
    WHERE e.id = ?
");
$stmt->execute([$etudiantId]);
$etu = $stmt->fetch();
if (!$etu) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Étudiant introuvable.']);
    exit;
}

// ─── Enregistrer la convocation ──────────────────────────────────
try {
    $stmt = $pdo->prepare(
        'INSERT INTO convocations (entreprise_id, etudiant_id, date_entretien, lieu, message)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $entreprise['id'],
        $etudiantId,
        str_replace('T', ' ', $dateEntretien),   // format DATETIME MySQL
        $lieu,
        $message,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement.']);
    exit;
}

// ─── Envoi du courriel ───────────────────────────────────────────
$dateLisible = date('d/m/Y à H\hi', strtotime($dateEntretien));
$sujet = "Convocation à un entretien — {$entreprise['nom']}";
$corps = "Bonjour {$etu['prenom']} {$etu['nom']},\n\n"
       . "L'entreprise {$entreprise['nom']} souhaite vous rencontrer.\n\n"
       . "Date : {$dateLisible}\n"
       . ($lieu ? "Lieu : {$lieu}\n" : '')
       . ($message ? "\nMessage :\n{$message}\n" : '')
       . "\nCordialement,\nPlateforme CV JUNIA";

$entetes = "From: no-reply@junia-cv.local\r\nContent-Type: text/plain; charset=UTF-8";

// En environnement Docker sans serveur SMTP, mail() échoue souvent :
// on enregistre alors le courriel dans un fichier log (suffisant pour la démo).
$envoye = @mail($etu['email'], $sujet, $corps, $entetes);
if (!$envoye) {
    $log = __DIR__ . '/../uploads/courriels.log';
    $ligne = "==== " . date('Y-m-d H:i:s') . " ====\n"
           . "À : {$etu['email']}\nObjet : {$sujet}\n{$corps}\n\n";
    @file_put_contents($log, $ligne, FILE_APPEND);
}

echo json_encode([
    'success' => true,
    'message' => 'Convocation envoyée à ' . htmlspecialchars($etu['prenom']) . '.',
]);
