<?php
/**
 * api/recuperer-cv.php — Récupère le CV de l'étudiant connecté (pré-remplissage)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (role_actuel() !== 'etudiant') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux étudiants.']);
    exit;
}

$userId = utilisateur_id();

$stmt = $pdo->prepare('SELECT * FROM etudiants WHERE user_id = ?');
$stmt->execute([$userId]);
$etudiant = $stmt->fetch();
if (!$etudiant) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Profil introuvable.']);
    exit;
}
$id = (int) $etudiant['id'];

// Sous-listes
$get = function (string $sql) use ($pdo, $id) {
    $s = $pdo->prepare($sql);
    $s->execute([$id]);
    return $s->fetchAll();
};

$reponse = [
    'success'     => true,
    'etudiant'    => $etudiant,
    'domaines'    => array_column($get('SELECT type FROM domaines_recherche WHERE etudiant_id = ?'), 'type'),
    'formations'  => $get('SELECT * FROM formations WHERE etudiant_id = ? ORDER BY ordre'),
    'experiences' => $get('SELECT * FROM experiences WHERE etudiant_id = ? ORDER BY ordre'),
    'techniques'  => $get('SELECT * FROM competences_techniques WHERE etudiant_id = ?'),
    'langues'     => $get('SELECT * FROM competences_linguistiques WHERE etudiant_id = ?'),
];

echo json_encode($reponse);
