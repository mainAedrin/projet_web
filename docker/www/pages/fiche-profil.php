<?php
/**
 * pages/fiche-profil.php — CV complet d'un étudiant + convocation (entreprise)
 */
require_once __DIR__ . '/../inc/auth.php';
exiger_role(['entreprise', 'admin']);

$titrePage = "Profil étudiant";
require_once __DIR__ . '/../inc/header.php';
?>

<div id="fiche"><p class="vide-message">Chargement…</p></div>

<!-- Modale de convocation -->
<div class="modale-fond" id="modale-convoc">
    <div class="modale">
        <h2>Convoquer à un entretien</h2>
        <div id="msg-convoc" class="message"></div>
        <input type="hidden" id="convoc-etudiant-id">

        <label for="convoc-date">Date et heure *</label>
        <input type="datetime-local" id="convoc-date" required>

        <label for="convoc-lieu">Lieu</label>
        <input type="text" id="convoc-lieu" placeholder="Adresse, visio…">

        <label for="convoc-message">Message</label>
        <textarea id="convoc-message" placeholder="Précisions pour le candidat…"></textarea>

        <div class="barre-actions">
            <button class="btn-secondaire" id="convoc-annuler">Annuler</button>
            <button class="btn-principal" id="convoc-envoyer">Envoyer la convocation</button>
        </div>
    </div>
</div>

<script src="/js/fiche-profil.js"></script>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
