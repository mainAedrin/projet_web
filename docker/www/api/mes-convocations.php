<?php
/**
 * api/mes-convocations.php — Historique des convocations de l'entreprise connectée
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (role_actuel() !== 'entreprise') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux entreprises.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM entreprises WHERE user_id = ?');
$stmt->execute([utilisateur_id()]);
$entreprise = $stmt->fetch();
if (!$entreprise) {
    echo json_encode(['success' => true, 'convocations' => []]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.id, c.date_entretien, c.lieu, c.message, c.statut, c.created_at,
           e.nom, e.prenom, e.id AS etudiant_id
    FROM convocations c
    INNER JOIN etudiants e ON e.id = c.etudiant_id
    WHERE c.entreprise_id = ?
    ORDER BY c.date_entretien DESC
");
$stmt->execute([$entreprise['id']]);

echo json_encode(['success' => true, 'convocations' => $stmt->fetchAll()]);
