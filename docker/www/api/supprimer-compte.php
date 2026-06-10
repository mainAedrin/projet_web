<?php
/**
 * api/supprimer-compte.php — Suppression du compte de l'utilisateur connecté (RGPD)
 * La suppression en cascade (ON DELETE CASCADE) efface toutes les données liées.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (!est_connecte()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non connecté.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$userId = utilisateur_id();

try {
    // Supprimer la photo physique si étudiant
    $stmt = $pdo->prepare('SELECT photo FROM etudiants WHERE user_id = ?');
    $stmt->execute([$userId]);
    if ($row = $stmt->fetch()) {
        $dossier = __DIR__ . '/../uploads/';
        if ($row['photo'] && is_file($dossier . $row['photo'])) {
            @unlink($dossier . $row['photo']);
        }
    }

    // Supprimer le compte (cascade BDD)
    $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);

    // Détruire la session
    $_SESSION = [];
    session_destroy();

    echo json_encode(['success' => true, 'message' => 'Compte et données supprimés.']);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la suppression.']);
}
