<?php
// inc/session.php — démarrage de session sécurisé
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,   // pas accessible en JS → anti-XSS
        'cookie_samesite' => 'Lax',  // anti-CSRF basique
        // 'cookie_secure' => true,  // à activer si HTTPS
    ]);
}

// Helpers pratiques réutilisés partout
function est_connecte(): bool {
    return isset($_SESSION['user_id']);
}

function role_actuel(): ?string {
    return $_SESSION['role'] ?? null;
}

function utilisateur_id(): ?int {
    return $_SESSION['user_id'] ?? null;
}
