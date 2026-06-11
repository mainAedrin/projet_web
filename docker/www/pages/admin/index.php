<?php
/**
 * pages/admin/index.php — Tableau de bord administrateur
 */
require_once __DIR__ . '/../../inc/auth.php';
exiger_role('admin');

$titrePage = "Administration";
require_once __DIR__ . '/../../inc/header.php';
?>

<div class="page-titre">
    <h1>Tableau de bord</h1>
    <p>Gestion de la plateforme CV JUNIA.</p>
</div>

<!-- ─── Statistiques ─── -->
<div class="stats-grille">
    <div class="stat-carte">
        <div class="chiffre" id="stat-etudiants">—</div>
        <div class="libelle">Étudiants</div>
    </div>
    <div class="stat-carte">
        <div class="chiffre" id="stat-entreprises">—</div>
        <div class="libelle">Entreprises</div>
    </div>
    <div class="stat-carte">
        <div class="chiffre" id="stat-convocations">—</div>
        <div class="libelle">Convocations</div>
    </div>
    <div class="stat-carte">
        <div class="chiffre" id="stat-demandes">—</div>
        <div class="libelle">Demandes en attente</div>
    </div>
</div>

<!-- ─── Graphiques ─── -->
<div class="charts-grille">
    <div class="chart-carte">
        <h2>Étudiants vs Entreprises</h2>
        <div class="chart-wrap"><canvas id="graphique-comptes"></canvas></div>
    </div>
    <div class="chart-carte">
        <h2>Domaines les plus recherchés</h2>
        <div class="chart-wrap"><canvas id="graphique-domaines"></canvas></div>
    </div>
</div>

<!-- ─── Création d'un compte entreprise ─── -->
<div class="bloc-form">
    <h2>Créer un compte entreprise</h2>
    <div id="msg-entreprise" class="message"></div>
    <form id="form-entreprise">
        <div class="grille-2">
            <div><label for="e-nom">Nom de l'entreprise *</label><input type="text" id="e-nom" required></div>
            <div><label for="e-email">Email *</label><input type="email" id="e-email" required></div>
            <div><label for="e-secteur">Secteur</label><input type="text" id="e-secteur"></div>
            <div><label for="e-contact">Nom du contact</label><input type="text" id="e-contact"></div>
        </div>
        <div class="barre-actions">
            <button type="submit" class="btn-principal">Créer le compte</button>
        </div>
    </form>
</div>

<!-- ─── Gestion des comptes ─── -->
<div class="bloc-form">
    <h2>Gestion des comptes</h2>
    <div class="onglets">
        <button class="onglet actif" data-role="etudiant">Étudiants</button>
        <button class="onglet" data-role="entreprise">Entreprises</button>
        <button class="onglet" data-role="admin">Administrateurs</button>
    </div>
    <div id="liste-comptes"></div>
</div>

<script src="/js/chart.umd.min.js"></script>
<script src="/js/admin.js"></script>
<?php require_once __DIR__ . '/../../inc/footer.php'; ?>
