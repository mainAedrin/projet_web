<?php
/**
 * api/mes-invitations.php — Convocations reçues par l'étudiant connecté
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (role_actuel() !== 'etudiant') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux étudiants.']);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM etudiants WHERE user_id = ?');
$stmt->execute([utilisateur_id()]);
$etudiant = $stmt->fetch();

if (!$etudiant) {
    echo json_encode(['success' => true, 'invitations' => []]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.id, c.date_entretien, c.lieu, c.message, c.statut, c.created_at,
           en.nom AS entreprise_nom, en.secteur
    FROM convocations c
    INNER JOIN entreprises en ON en.id = c.entreprise_id
    WHERE c.etudiant_id = ?
    ORDER BY c.date_entretien DESC
");
$stmt->execute([$etudiant['id']]);

echo json_encode(['success' => true, 'invitations' => $stmt->fetchAll()]);
