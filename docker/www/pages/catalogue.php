<?php
/**
 * pages/catalogue.php — Catalogue des profils étudiants (entreprise)
 */
require_once __DIR__ . '/../inc/auth.php';
exiger_role(['entreprise', 'admin']);

$titrePage = "Catalogue des profils";
require_once __DIR__ . '/../inc/header.php';
?>

<div class="page-titre">
    <h1>Catalogue des profils</h1>
    <p>Recherchez et filtrez les étudiants à la recherche d'opportunités.</p>
</div>

<div class="filtres">
    <div>
        <label for="f-recherche">Recherche (nom)</label>
        <input type="text" id="f-recherche" placeholder="Nom ou prénom…">
    </div>
    <div>
        <label for="f-domaine">Domaine de recherche</label>
        <select id="f-domaine">
            <option value="">Tous</option>
            <option value="stage_1a">Stage 1re année</option>
            <option value="stage_2a">Stage 2e année</option>
            <option value="alternance_apprentissage">Apprentissage</option>
            <option value="alternance_professionnalisation">Professionnalisation</option>
            <option value="mobilite_internationale">Mobilité internationale</option>
            <option value="cdi">CDI</option>
        </select>
    </div>
    <div>
        <label for="f-competence">Compétence</label>
        <input type="text" id="f-competence" placeholder="Ex : Python, SQL…">
    </div>
    <div>
        <label for="f-promotion">École / promotion</label>
        <input type="text" id="f-promotion" placeholder="Ex : Ingénieur 3e année">
    </div>
</div>

<div id="grille-profils" class="grille-profils"></div>

<script src="/js/catalogue.js"></script>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
