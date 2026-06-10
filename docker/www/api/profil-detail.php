<?php
/**
 * api/profil-detail.php — CV complet d'un étudiant (vue entreprise)
 * Paramètre : ?id=<etudiant_id>
 * Vue entreprise : on masque l'adresse précise (RGPD / données sensibles).
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (!in_array(role_actuel(), ['entreprise', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès réservé.']);
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Identifiant manquant.']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT e.id, e.nom, e.prenom, e.photo, e.promotion, e.ville,
           e.biographie, e.telephone
    FROM etudiants e
    INNER JOIN users u ON u.id = e.user_id AND u.is_active = 1
    WHERE e.id = ?
");
$stmt->execute([$id]);
$etudiant = $stmt->fetch();
if (!$etudiant) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Profil introuvable.']);
    exit;
}

$get = function (string $sql) use ($pdo, $id) {
    $s = $pdo->prepare($sql);
    $s->execute([$id]);
    return $s->fetchAll();
};

echo json_encode([
    'success'     => true,
    'etudiant'    => $etudiant,
    'domaines'    => array_column($get('SELECT type FROM domaines_recherche WHERE etudiant_id = ?'), 'type'),
    'formations'  => $get('SELECT * FROM formations WHERE etudiant_id = ? ORDER BY ordre'),
    'experiences' => $get('SELECT * FROM experiences WHERE etudiant_id = ? ORDER BY ordre'),
    'techniques'  => $get('SELECT * FROM competences_techniques WHERE etudiant_id = ?'),
    'langues'     => $get('SELECT * FROM competences_linguistiques WHERE etudiant_id = ?'),
]);
