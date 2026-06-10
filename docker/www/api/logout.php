<?php
/**
 * api/logout.php — Déconnexion
 * Accessible directement par lien (GET) depuis le header.
 */
require_once __DIR__ . '/../inc/session.php';

$_SESSION = [];                         // vider les données

// Détruire le cookie de session côté navigateur
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

session_destroy();                      // détruire la session serveur

header('Location: /index.php');         // retour à l'accueil
exit;
