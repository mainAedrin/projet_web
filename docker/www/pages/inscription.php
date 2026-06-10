<?php
$titrePage = "Inscription";
require_once __DIR__ . '/../inc/header.php';
?>

<section class="form-auth">
    <h1>Créer mon compte étudiant</h1>
    <div id="msg" class="message"></div>

    <form id="form-inscription" novalidate>
        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" required>

        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" required>

        <label for="email">Email JUNIA</label>
        <input type="email" id="email" name="email" placeholder="prenom.nom@junia.com" required>

        <label for="password">Mot de passe (8 caractères min.)</label>
        <input type="password" id="password" name="password" minlength="8" autocomplete="new-password" required>

        <div class="consentement">
            <input type="checkbox" id="consentement" name="consentement">
            <label for="consentement">
                J'accepte la
                <a href="/pages/confidentialite.php" target="_blank">politique de confidentialité</a>
                et la collecte de mes données dans le cadre de la plateforme.
            </label>
        </div>

        <button type="submit" class="btn-principal">Créer mon compte</button>
    </form>

    <p class="lien-bas">Déjà un compte ? <a href="/pages/connexion.php">Se connecter</a></p>
</section>

<script src="/js/auth.js"></script>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
