<?php
/**
 * api/suggestions.php — Suggestions autocomplétion compétences
 * GET ?q=py  →  ["Python","Python (Pandas)","..."]
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (!in_array(role_actuel(), ['entreprise','admin'], true)) {
    echo json_encode([]); exit;
}

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 1) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("
    SELECT libelle, COUNT(*) AS nb
    FROM competences_techniques
    WHERE libelle LIKE ?
    GROUP BY libelle
    ORDER BY nb DESC, libelle ASC
    LIMIT 8
");
$stmt->execute(['%' . $q . '%']);

echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
