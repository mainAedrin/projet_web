</main>

<footer class="site-footer">
    <div class="footer-inner">

        <div class="footer-brand">
            <a href="/index.php" class="logo footer-logo">
                <span class="logo-mark">J</span>
                <span class="logo-text footer-logo-text">JUNIA <strong>CV</strong></span>
            </a>
            <p>La plateforme qui connecte les étudiants JUNIA aux entreprises partenaires — stages, alternances et recrutements.</p>
        </div>

        <nav class="footer-nav">
            <strong>Plateforme</strong>
            <a href="/index.php">Accueil</a>
            <a href="/pages/connexion.php">Se connecter</a>
            <a href="/pages/contact.php">Devenir partenaire</a>
        </nav>

        <nav class="footer-nav">
            <strong>Légal & RGPD</strong>
            <a href="/pages/mentions-legales.php">Mentions légales</a>
            <a href="/pages/confidentialite.php">Confidentialité</a>
        </nav>

    </div>
    <div class="footer-bottom">
        &copy; <?= date('Y') ?> JUNIA — Architecture Web AP3
    </div>
</footer>

<script>
/* ── Menu burger mobile ─────────────────────────────────────── */
(function () {
    var burger = document.getElementById('menu-burger');
    var nav    = document.getElementById('main-nav');
    if (!burger || !nav) return;

    burger.addEventListener('click', function () {
        var open = nav.classList.toggle('ouverte');
        burger.classList.toggle('actif', open);
        burger.setAttribute('aria-expanded', open);
    });

    /* Fermer en cliquant en dehors */
    document.addEventListener('click', function (e) {
        if (!burger.contains(e.target) && !nav.contains(e.target)) {
            nav.classList.remove('ouverte');
            burger.classList.remove('actif');
            burger.setAttribute('aria-expanded', 'false');
        }
    });
}());
</script>

</body>
</html>
