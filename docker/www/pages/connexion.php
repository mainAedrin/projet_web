<?php
$titrePage = "Connexion";
require_once __DIR__ . '/../inc/header.php';
?>

<section class="form-auth">
    <h1>Connexion</h1>
    <div id="msg" class="message"></div>

    <form id="form-connexion" novalidate>
        <label for="email">Email</label>
        <input type="email" id="email" name="email" autocomplete="email" required>

        <label for="password">Mot de passe</label>
        <input type="password" id="password" name="password" autocomplete="current-password" required>

        <button type="submit" class="btn-principal">Se connecter</button>
    </form>

    <p class="lien-bas">Pas encore de compte ? <a href="/pages/inscription.php">S'inscrire</a></p>
</section>

<script src="/js/auth.js"></script>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
