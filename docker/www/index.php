<?php
$titrePage = "Accueil";
require_once __DIR__ . '/inc/header.php';
?>

<!-- ═══════════════════════════════════════════════════════════ HERO -->
<section class="hero">
    <div class="hero-content">
        <span class="hero-eyebrow">🎓 Plateforme officielle JUNIA</span>
        <h1>Votre profil, vu par<br><em>les bonnes entreprises</em></h1>
        <p class="hero-desc">
            Centralisez votre CV étudiant et soyez visible des recruteurs partenaires JUNIA —
            stages, alternances, mobilité internationale et CDI.
        </p>
        <div class="hero-actions">
            <?php if (!est_connecte()): ?>
                <a href="/pages/inscription.php" class="btn-principal">Créer mon CV</a>
                <a href="/pages/connexion.php" class="btn-secondaire">Se connecter</a>
            <?php elseif (role_actuel() === 'etudiant'): ?>
                <a href="/pages/profil.php" class="btn-principal">Voir mon CV</a>
                <a href="/pages/formulaire-cv.php" class="btn-secondaire">Modifier</a>
            <?php elseif (role_actuel() === 'entreprise'): ?>
                <a href="/pages/catalogue.php" class="btn-principal">Parcourir les profils</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Carte CV décorative (masquée sur mobile) -->
    <div class="hero-visual" aria-hidden="true">
        <div class="cv-preview">
            <div class="cvp-header">
                <div class="cvp-avatar"></div>
                <div>
                    <div class="cvp-name"></div>
                    <div class="cvp-title"></div>
                </div>
            </div>
            <div class="cvp-section">
                <div class="cvp-label"></div>
                <div class="cvp-line"></div>
                <div class="cvp-line short"></div>
            </div>
            <div class="cvp-section">
                <div class="cvp-label"></div>
                <div class="cvp-chips">
                    <div class="cvp-chip"></div>
                    <div class="cvp-chip"></div>
                    <div class="cvp-chip"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ════════════════════════════════════════════════ COMMENT ÇA MARCHE -->
<div class="section-titre" style="margin-top: 3.5rem">
    <h2>Comment ça fonctionne&nbsp;?</h2>
    <p>Trois étapes, et votre profil est prêt à être consulté par les recruteurs.</p>
</div>

<div class="etapes">
    <div class="etape">
        <h3>Créez votre profil</h3>
        <p>Renseignez vos informations, votre parcours académique, vos expériences et vos compétences via un formulaire guidé.</p>
    </div>
    <div class="etape">
        <h3>Définissez vos objectifs</h3>
        <p>Stage, alternance, CDI ou mobilité internationale — cochez les types de contrats qui correspondent à votre projet professionnel.</p>
    </div>
    <div class="etape">
        <h3>Recevez des convocations</h3>
        <p>Les entreprises partenaires JUNIA consultent les profils et vous invitent directement à des entretiens par e-mail.</p>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════ FONCTIONNALITÉS -->
<div class="section-titre">
    <h2>Ce que vous pouvez faire</h2>
    <p>Une plateforme pensée pour les étudiants et les entreprises partenaires.</p>
</div>

<section class="features">
    <div class="feature-card">
        <div class="icone">📝</div>
        <h3>Un CV standardisé</h3>
        <p>Remplissez un formulaire complet et obtenez un profil propre, uniforme et immédiatement lisible par les recruteurs.</p>
    </div>
    <div class="feature-card">
        <div class="icone">🎯</div>
        <h3>Vos objectifs ciblés</h3>
        <p>Stage, alternance, mobilité, CDI : définissez vos domaines de recherche et soyez visible des bons recruteurs.</p>
    </div>
    <div class="feature-card">
        <div class="icone">🏢</div>
        <h3>Des entreprises partenaires</h3>
        <p>Le réseau ALUMNI JUNIA et nos partenaires consultent les profils et convoquent directement les candidats.</p>
    </div>
    <div class="feature-card">
        <div class="icone">🔒</div>
        <h3>Vos données protégées</h3>
        <p>Conformité RGPD, consentement explicite à l'inscription et suppression complète du compte à tout moment.</p>
    </div>
</section>

<!-- ═══════════════════════════════════════════════════════ PARTENAIRES -->
<section class="partenaires">
    <div class="section-titre">
        <h2>Ils recrutent sur la plateforme</h2>
        <p>Un aperçu de nos entreprises partenaires et du réseau ALUMNI.</p>
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
