<?php
/**
 * api/contact.php — Demande de partenariat (entreprises non-partenaires)
 * Page publique, pas d'authentification requise.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$data       = json_decode(file_get_contents('php://input'), true);
$nomEntr    = trim($data['nom_entreprise'] ?? '');
$contactNom = trim($data['contact_nom'] ?? '');
$email      = trim($data['email'] ?? '');
$message    = trim($data['message'] ?? '');

$erreurs = [];
if ($nomEntr === '')                              $erreurs[] = 'Nom de l\'entreprise requis.';
if ($contactNom === '')                           $erreurs[] = 'Nom du contact requis.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $erreurs[] = 'Email invalide.';
if ($message === '')                              $erreurs[] = 'Message requis.';

if ($erreurs) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => implode(' ', $erreurs)]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'INSERT INTO demandes_partenariat (nom_entreprise, contact_nom, email, message)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$nomEntr, $contactNom, $email, $message]);
    echo json_encode(['success' => true, 'message' => 'Votre demande a bien été envoyée.']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'envoi.']);
}
