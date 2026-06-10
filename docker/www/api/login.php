<?php
/**
 * api/login.php — Connexion (tous rôles)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Email et mot de passe requis.']);
    exit;
}

// ─── Récupérer l'utilisateur (requête préparée) ───────────────────
$stmt = $pdo->prepare(
    'SELECT id, email, password_hash, role, is_active FROM users WHERE email = ?'
);
$stmt->execute([$email]);
$user = $stmt->fetch();

// ─── Vérification ─────────────────────────────────────────────────
// Message volontairement identique si email inconnu OU mauvais mdp
// → on ne révèle pas si l'email existe (bonne pratique sécurité)
if (!$user || !password_verify($password, $user['password_hash'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Email ou mot de passe incorrect.']);
    exit;
}

// Compte suspendu par l'admin ?
if ((int) $user['is_active'] !== 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Compte suspendu. Contactez JUNIA.']);
    exit;
}

// ─── Ouvrir la session ────────────────────────────────────────────
session_regenerate_id(true);          // anti session-fixation
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['email']   = $user['email'];
$_SESSION['role']    = $user['role'];

// URL de redirection selon le rôle (le JS s'en sert)
$redirect = match ($user['role']) {
    'etudiant'   => '/pages/profil.php',
    'entreprise' => '/pages/catalogue.php',
    'admin'      => '/pages/admin/index.php',
    default      => '/index.php',
};

echo json_encode([
    'success'  => true,
    'role'     => $user['role'],
    'redirect' => $redirect,
]);
