<?php
/**
 * inc/db.php — Connexion PDO à MySQL
 * 
 * ⚠️  Ne jamais committer ce fichier avec des credentials en dur.
 *     Les variables d'env sont injectées par Docker (docker-compose.yml).
 */

$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'cv_platform';
$user = getenv('DB_USER') ?: 'cv_user';
$pass = getenv('DB_PASS') ?: 'password123';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,   // requêtes vraiment préparées
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // En prod, ne jamais afficher $e->getMessage() directement
    http_response_code(500);
    echo json_encode(['error' => 'Connexion base de données impossible.']);
    exit;
}
