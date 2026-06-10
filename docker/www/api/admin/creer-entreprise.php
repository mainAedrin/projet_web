<?php
/**
 * api/admin/creer-entreprise.php — Création d'un compte entreprise (admin)
 * Génère un mot de passe initial renvoyé à l'admin (à transmettre à l'entreprise).
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../inc/session.php';
require_once __DIR__ . '/../../inc/db.php';

if (role_actuel() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux administrateurs.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$data       = json_decode(file_get_contents('php://input'), true);
$nom        = trim($data['nom'] ?? '');
$email      = trim($data['email'] ?? '');
$secteur    = trim($data['secteur'] ?? '');
$contactNom = trim($data['contact_nom'] ?? '');

$erreurs = [];
if ($nom === '')                                  $erreurs[] = 'Nom de l\'entreprise requis.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $erreurs[] = 'Email invalide.';

if ($erreurs) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => implode(' ', $erreurs)]);
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'Cet email est déjà utilisé.']);
    exit;
}

// Mot de passe initial aléatoire (lisible)
$motDePasse = 'Junia-' . bin2hex(random_bytes(3));

try {
    $pdo->beginTransaction();

    $hash = password_hash($motDePasse, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)');
    $stmt->execute([$email, $hash, 'entreprise']);
    $userId = (int) $pdo->lastInsertId();

    $stmt = $pdo->prepare('INSERT INTO entreprises (user_id, nom, secteur, contact_nom) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $nom, $secteur, $contactNom]);

    $pdo->commit();

    echo json_encode([
        'success'      => true,
        'message'      => 'Compte entreprise créé.',
        'identifiants' => ['email' => $email, 'mot_de_passe' => $motDePasse],
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la création.']);
}
