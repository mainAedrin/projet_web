<?php
$titrePage = "Accueil";
require_once __DIR__ . '/inc/header.php';
?>

<section class="hero">
    <h1>Votre CV, vu par les bonnes entreprises</h1>
    <p>
        La plateforme JUNIA qui centralise les CV étudiants et les connecte
        aux entreprises partenaires : stages, alternance, mobilité internationale et CDI.
    </p>
    <div class="hero-actions">
        <?php if (!est_connecte()): ?>
            <a href="/pages/inscription.php" class="btn-principal">Créer mon CV</a>
            <a href="/pages/connexion.php" class="btn-secondaire">Se connecter</a>
        <?php elseif (role_actuel() === 'etudiant'): ?>
            <a href="/pages/profil.php" class="btn-principal">Voir mon CV</a>
        <?php elseif (role_actuel() === 'entreprise'): ?>
            <a href="/pages/catalogue.php" class="btn-principal">Parcourir les profils</a>
        <?php endif; ?>
    </div>
</section>

<div class="section-titre">
    <h2>Ce que vous pouvez faire</h2>
    <p>Une plateforme pensée pour les étudiants et les entreprises.</p>
</div>

<section class="features">
    <div class="feature-card">
        <div class="icone">📝</div>
        <h3>Un CV standardisé</h3>
        <p>Remplissez un formulaire complet et obtenez un CV propre et uniforme, prêt à être consulté.</p>
    </div>
    <div class="feature-card">
        <div class="icone">🎯</div>
        <h3>Vos objectifs ciblés</h3>
        <p>Stage, alternance, mobilité, CDI : choisissez vos domaines de recherche et soyez visible des bons recruteurs.</p>
    </div>
    <div class="feature-card">
        <div class="icone">🏢</div>
        <h3>Des entreprises partenaires</h3>
        <p>Le réseau ALUMNI JUNIA et nos partenaires consultent les profils et convoquent directement les candidats.</p>
    </div>
    <div class="feature-card">
        <div class="icone">🔒</div>
        <h3>Vos données protégées</h3>
        <p>Conformité RGPD, consentement explicite et suppression de compte à tout moment.</p>
    </div>
</section>

<section class="partenaires">
    <div class="section-titre">
        <h2>Ils recrutent sur la plateforme</h2>
        <p>Un aperçu de nos entreprises partenaires.</p>
    </div>
    <div class="partenaires-logos">
        <span class="partenaire-pastille">ALUMNI JUNIA</span>
        <span class="partenaire-pastille">Capgemini</span>
        <span class="partenaire-pastille">Decathlon</span>
        <span class="partenaire-pastille">OVHcloud</span>
        <span class="partenaire-pastille">Bouygues</span>
        <span class="partenaire-pastille">Auchan</span>
    </div>
    <p style="margin-top:1.8rem">
        Votre entreprise souhaite nous rejoindre ?
        <a href="/pages/contact.php">Contactez-nous</a>.
    </p>
</section>

<?php require_once __DIR__ . '/inc/footer.php'; ?>
