<?php
/**
 * pages/mes-invitations.php — Invitations à un entretien (étudiant)
 */
require_once __DIR__ . '/../inc/auth.php';
exiger_role('etudiant');

$titrePage = "Mes invitations";
require_once __DIR__ . '/../inc/header.php';
?>

<div class="page-titre">
    <h1>Mes invitations</h1>
    <p>Retrouvez ici toutes les convocations à un entretien envoyées par les entreprises partenaires.</p>
</div>

<div id="liste-invitations"></div>

<script src="/js/mes-invitations.js"></script>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
