<?php
/**
 * pages/formulaire-cv.php — Formulaire de création/modification du CV
 */
require_once __DIR__ . '/../inc/auth.php';
exiger_role('etudiant');

$titrePage = "Modifier mon CV";
require_once __DIR__ . '/../inc/header.php';
?>

<div class="page-titre">
    <h1>Mon CV</h1>
    <p>Renseignez vos informations. Elles seront visibles par les entreprises partenaires.</p>
</div>

<div id="msg" class="message"></div>

<form id="form-cv">

    <!-- ─── Données personnelles ─── -->
    <div class="bloc-form">
        <h2>Données personnelles</h2>
        <div class="grille-2">
            <div><label for="prenom">Prénom *</label><input type="text" id="prenom" required></div>
            <div><label for="nom">Nom *</label><input type="text" id="nom" required></div>
            <div><label for="date_naissance">Date de naissance</label><input type="date" id="date_naissance"></div>
            <div><label for="telephone">Téléphone</label><input type="tel" id="telephone"></div>
            <div><label for="adresse">Adresse</label><input type="text" id="adresse"></div>
            <div><label for="ville">Ville</label><input type="text" id="ville"></div>
            <div><label for="code_postal">Code postal</label><input type="text" id="code_postal"></div>
            <div><label for="promotion">Promotion</label>
                <input type="text" id="promotion" placeholder="Ex : Ingénieur 3e année"></div>
        </div>
        <label for="photo">Photo de profil (jpg/png, 2 Mo max)</label>
        <input type="file" id="photo" accept="image/jpeg,image/png">
        <img id="apercu-photo" class="apercu-photo" alt="Aperçu">
    </div>

    <!-- ─── Biographie ─── -->
    <div class="bloc-form">
        <h2>Biographie / Lettre de motivation</h2>
        <textarea id="biographie" rows="5" placeholder="Présentez-vous en quelques lignes…"></textarea>
    </div>

    <!-- ─── Domaines de recherche ─── -->
    <div class="bloc-form">
        <h2>Domaines de recherche</h2>
        <div class="cases">
            <div class="case-item"><input type="checkbox" name="domaines[]" value="stage_1a" id="d1"><label for="d1">Stage 1re année</label></div>
            <div class="case-item"><input type="checkbox" name="domaines[]" value="stage_2a" id="d2"><label for="d2">Stage 2e année</label></div>
            <div class="case-item"><input type="checkbox" name="domaines[]" value="alternance_apprentissage" id="d3"><label for="d3">Contrat d'apprentissage</label></div>
            <div class="case-item"><input type="checkbox" name="domaines[]" value="alternance_professionnalisation" id="d4"><label for="d4">Contrat de professionnalisation</label></div>
            <div class="case-item"><input type="checkbox" name="domaines[]" value="mobilite_internationale" id="d5"><label for="d5">Mobilité internationale</label></div>
            <div class="case-item"><input type="checkbox" name="domaines[]" value="cdi" id="d6"><label for="d6">CDI</label></div>
        </div>
    </div>

    <!-- ─── Parcours académique ─── -->
    <div class="bloc-form">
        <h2>Parcours académique</h2>
        <div id="liste-formations"></div>
        <button type="button" id="ajouter-formation" class="ajouter-entree">+ Ajouter une formation</button>
    </div>

    <!-- ─── Expériences ─── -->
    <div class="bloc-form">
        <h2>Expériences professionnelles</h2>
        <div id="liste-experiences"></div>
        <button type="button" id="ajouter-experience" class="ajouter-entree">+ Ajouter une expérience</button>
    </div>

    <!-- ─── Compétences techniques ─── -->
    <div class="bloc-form">
        <h2>Compétences techniques</h2>
        <div id="liste-techniques"></div>
        <button type="button" id="ajouter-technique" class="ajouter-entree">+ Ajouter une compétence</button>
    </div>

    <!-- ─── Langues ─── -->
    <div class="bloc-form">
        <h2>Langues</h2>
        <div id="liste-langues"></div>
        <button type="button" id="ajouter-langue" class="ajouter-entree">+ Ajouter une langue</button>
    </div>

    <div class="barre-actions">
        <a href="/pages/profil.php" class="btn-secondaire">Annuler</a>
        <button type="submit" class="btn-principal">Enregistrer mon CV</button>
    </div>
</form>

<script src="/js/form-cv.js"></script>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
