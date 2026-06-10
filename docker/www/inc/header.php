<?php require_once __DIR__ . '/session.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titrePage) ? htmlspecialchars($titrePage) . ' — ' : '' ?>JUNIA CV</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a href="/index.php" class="logo">🎓 JUNIA <span>CV</span></a>

        <nav class="main-nav">
            <a href="/index.php">Accueil</a>

            <?php if (!est_connecte()): ?>
                <!-- Visiteur non connecté -->
                <a href="/pages/contact.php">Contact</a>
                <a href="/pages/connexion.php">Connexion</a>
                <a href="/pages/inscription.php" class="btn-nav">Inscription</a>

            <?php elseif (role_actuel() === 'etudiant'): ?>
                <!-- Étudiant connecté -->
                <a href="/pages/profil.php">Mon CV</a>
                <a href="/api/logout.php" class="btn-nav">Déconnexion</a>

            <?php elseif (role_actuel() === 'entreprise'): ?>
                <!-- Entreprise connectée -->
                <a href="/pages/catalogue.php">Catalogue</a>
                <a href="/pages/mes-convocations.php">Mes convocations</a>
                <a href="/api/logout.php" class="btn-nav">Déconnexion</a>

            <?php elseif (role_actuel() === 'admin'): ?>
                <!-- Admin connecté -->
                <a href="/pages/admin/index.php">Administration</a>
                <a href="/api/logout.php" class="btn-nav">Déconnexion</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="site-main">
