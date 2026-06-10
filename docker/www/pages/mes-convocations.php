<?php
/**
 * pages/mes-convocations.php — Historique des convocations (entreprise)
 */
require_once __DIR__ . '/../inc/auth.php';
exiger_role('entreprise');

$titrePage = "Mes convocations";
require_once __DIR__ . '/../inc/header.php';
?>

<div class="page-titre">
    <h1>Mes convocations</h1>
    <p>Retrouvez l'ensemble des étudiants que vous avez convoqués.</p>
</div>

<div id="liste-convocations"></div>

<script src="/js/mes-convocations.js"></script>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
