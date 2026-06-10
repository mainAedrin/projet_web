<?php
/**
 * api/register.php — Inscription d'un étudiant
 * Reçoit du JSON, renvoie du JSON.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/db.php';

// ─── 1. N'accepter que du POST ────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

// ─── 2. Lire le corps JSON ────────────────────────────────────────
$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données invalides.']);
    exit;
}

// ─── 3. Récupérer et nettoyer les champs ──────────────────────────
$email      = trim($data['email'] ?? '');
$password   = $data['password'] ?? '';
$nom        = trim($data['nom'] ?? '');
$prenom     = trim($data['prenom'] ?? '');
$consentement = !empty($data['consentement']);

// ─── 4. Validation serveur (ne JAMAIS faire confiance au client) ──
$erreurs = [];

if ($nom === '' || $prenom === '') {
    $erreurs[] = 'Nom et prénom obligatoires.';
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = 'Adresse email invalide.';
}

// Bonus cahier des charges : email institutionnel @junia.com
if (!str_ends_with(strtolower($email), '@junia.com')) {
    $erreurs[] = 'Vous devez utiliser une adresse @junia.com.';
}

if (strlen($password) < 8) {
    $erreurs[] = 'Le mot de passe doit faire au moins 8 caractères.';
}

// RGPD : le consentement explicite est obligatoire
if (!$consentement) {
    $erreurs[] = 'Vous devez accepter la politique de confidentialité.';
}

if ($erreurs) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => implode(' ', $erreurs)]);
    exit;
}

// ─── 5. Vérifier que l'email n'existe pas déjà (requête préparée) ──
$stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['success' => false, 'error' => 'Cet email est déjà utilisé.']);
    exit;
}

// ─── 6. Créer le compte (transaction : users + etudiants) ─────────
try {
    $pdo->beginTransaction();

    // Hachage du mot de passe (RGPD obligatoire)
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (email, password_hash, role) VALUES (?, ?, ?)'
    );
    $stmt->execute([$email, $hash, 'etudiant']);
    $userId = (int) $pdo->lastInsertId();

    // Créer la fiche étudiant liée
    $stmt = $pdo->prepare(
        'INSERT INTO etudiants (user_id, nom, prenom) VALUES (?, ?, ?)'
    );
    $stmt->execute([$userId, $nom, $prenom]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Compte créé avec succès.'
    ]);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de la création du compte.']);
}
