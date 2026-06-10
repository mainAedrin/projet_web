<?php require_once __DIR__ . '/session.php'; ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titrePage) ? htmlspecialchars($titrePage) . ' — ' : '' ?>JUNIA CV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800&family=Open+Sans:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">

        <a href="/index.php" class="logo">
            <span class="logo-mark">J</span>
            <span class="logo-text">JUNIA <strong>CV</strong></span>
        </a>

        <!-- Burger AVANT le nav — ne peut pas couvrir les liens -->
        <button class="menu-burger" id="menu-burger" aria-label="Ouvrir le menu" aria-expanded="false">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <nav class="main-nav" id="main-nav">
            <a href="/index.php">Accueil</a>

            <?php if (!est_connecte()): ?>
                <a href="/pages/contact.php">Contact</a>
                <a href="/pages/connexion.php">Connexion</a>
                <a href="/pages/inscription.php" class="btn-nav">Inscription</a>

            <?php elseif (role_actuel() === 'etudiant'): ?>
                <a href="/pages/profil.php">Mon CV</a>
                <a href="/pages/formulaire-cv.php">Modifier</a>
                <a href="/api/logout.php" class="btn-nav">Déconnexion</a>

            <?php elseif (role_actuel() === 'entreprise'): ?>
                <a href="/pages/catalogue.php">Catalogue</a>
                <a href="/pages/mes-convocations.php">Mes convocations</a>
                <a href="/api/logout.php" class="btn-nav">Déconnexion</a>

            <?php elseif (role_actuel() === 'admin'): ?>
                <a href="/pages/admin/index.php">Administration</a>
                <a href="/api/logout.php" class="btn-nav">Déconnexion</a>
            <?php endif; ?>
        </nav>

    </div>
</header>

<main class="site-main">
