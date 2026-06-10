<?php
/**
 * api/admin/stats.php — Statistiques du tableau de bord (admin)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../inc/session.php';
require_once __DIR__ . '/../../inc/db.php';

if (role_actuel() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux administrateurs.']);
    exit;
}

$compter = fn(string $sql) => (int) $pdo->query($sql)->fetchColumn();

$stats = [
    'etudiants'    => $compter("SELECT COUNT(*) FROM users WHERE role = 'etudiant'"),
    'entreprises'  => $compter("SELECT COUNT(*) FROM users WHERE role = 'entreprise'"),
    'convocations' => $compter("SELECT COUNT(*) FROM convocations"),
    'demandes'     => $compter("SELECT COUNT(*) FROM demandes_partenariat WHERE traite = 0"),
];

// Répartition des domaines de recherche (pour un graphique)
$rows = $pdo->query("
    SELECT type, COUNT(*) AS total
    FROM domaines_recherche
    GROUP BY type
")->fetchAll();
$stats['domaines'] = $rows;

echo json_encode(['success' => true, 'stats' => $stats]);
