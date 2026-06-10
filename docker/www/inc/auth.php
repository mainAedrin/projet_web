<?php
/**
 * inc/auth.php — Contrôle d'accès par rôle
 * À inclure tout en haut des pages protégées, AVANT le header.
 *
 * Usage :
 *   require_once __DIR__ . '/../inc/auth.php';
 *   exiger_connexion();              // n'importe quel rôle connecté
 *   exiger_role('etudiant');         // un seul rôle
 *   exiger_role(['admin','entreprise']); // plusieurs rôles autorisés
 */
require_once __DIR__ . '/session.php';

/**
 * Bloque l'accès si l'utilisateur n'est pas connecté.
 */
function exiger_connexion(): void {
    if (!est_connecte()) {
        header('Location: /pages/connexion.php');
        exit;
    }
}

/**
 * Bloque l'accès si le rôle ne correspond pas.
 * @param string|array $roles  Rôle(s) autorisé(s)
 */
function exiger_role(string|array $roles): void {
    exiger_connexion();   // d'abord vérifier qu'on est connecté

    $autorises = (array) $roles;   // accepte "admin" ou ['admin','entreprise']

    if (!in_array(role_actuel(), $autorises, true)) {
        http_response_code(403);
        // Page d'accès refusé minimaliste
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">'
           . '<title>Accès refusé</title></head><body style="font-family:sans-serif;'
           . 'max-width:500px;margin:80px auto;text-align:center;">'
           . '<h1 style="color:#6B2C91;">403 — Accès refusé</h1>'
           . '<p>Vous n\'avez pas les droits pour accéder à cette page.</p>'
           . '<a href="/index.php" style="color:#F39200;">Retour à l\'accueil</a>'
           . '</body></html>';
        exit;
    }
}
