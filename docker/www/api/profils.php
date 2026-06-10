<?php
/**
 * api/profils.php — Liste des profils étudiants pour le catalogue (entreprise)
 * Filtres : domaine, compétence, promotion (school/promo), recherche texte.
 * Renvoie uniquement les infos publiques (pas l'adresse complète).
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (!in_array(role_actuel(), ['entreprise', 'admin'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès réservé.']);
    exit;
}

// ─── Filtres (GET) ───────────────────────────────────────────────
$domaine    = trim($_GET['domaine'] ?? '');
$competence = trim($_GET['competence'] ?? '');
$promotion  = trim($_GET['promotion'] ?? '');
$recherche  = trim($_GET['q'] ?? '');

// On construit la requête dynamiquement, mais TOUJOURS avec des
// placeholders → aucune injection possible.
$sql = "
    SELECT DISTINCT e.id, e.nom, e.prenom, e.photo, e.promotion, e.ville, e.biographie
    FROM etudiants e
    INNER JOIN users u ON u.id = e.user_id AND u.is_active = 1
";
$conditions = [];
$params     = [];

if ($domaine !== '') {
    $sql .= " INNER JOIN domaines_recherche d ON d.etudiant_id = e.id ";
    $conditions[] = "d.type = ?";
    $params[]     = $domaine;
}
if ($competence !== '') {
    $sql .= " INNER JOIN competences_techniques c ON c.etudiant_id = e.id ";
    $conditions[] = "c.libelle LIKE ?";
    $params[]     = '%' . $competence . '%';
}
if ($promotion !== '') {
    $conditions[] = "e.promotion LIKE ?";
    $params[]     = '%' . $promotion . '%';
}
if ($recherche !== '') {
    $conditions[] = "(e.nom LIKE ? OR e.prenom LIKE ?)";
    $params[]     = '%' . $recherche . '%';
    $params[]     = '%' . $recherche . '%';
}

if ($conditions) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY e.nom, e.prenom';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$profils = $stmt->fetchAll();

// Ajouter les domaines de chaque étudiant (pour les badges)
$stmtDom = $pdo->prepare('SELECT type FROM domaines_recherche WHERE etudiant_id = ?');
foreach ($profils as &$p) {
    $stmtDom->execute([$p['id']]);
    $p['domaines'] = array_column($stmtDom->fetchAll(), 'type');
}
unset($p);

echo json_encode(['success' => true, 'profils' => $profils]);
