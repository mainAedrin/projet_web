<?php
/**
 * api/repondre-invitation.php — L'étudiant accepte ou refuse une convocation
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (role_actuel() !== 'etudiant') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux étudiants.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$data   = json_decode(file_get_contents('php://input'), true);
$id     = (int) ($data['id'] ?? 0);
$statut = $data['statut'] ?? '';

if ($id <= 0 || !in_array($statut, ['acceptee', 'refusee'], true)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Données invalides.']);
    exit;
}

// Vérifier que la convocation appartient bien à cet étudiant
$stmt = $pdo->prepare('
    SELECT c.id FROM convocations c
    INNER JOIN etudiants e ON e.id = c.etudiant_id
    WHERE c.id = ? AND e.user_id = ?
');
$stmt->execute([$id, utilisateur_id()]);

if (!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Convocation introuvable.']);
    exit;
}

$stmt = $pdo->prepare('UPDATE convocations SET statut = ? WHERE id = ?');
$stmt->execute([$statut, $id]);

echo json_encode(['success' => true]);
