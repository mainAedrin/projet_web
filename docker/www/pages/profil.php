<?php
/**
 * pages/profil.php — Affichage du CV de l'étudiant connecté (rendu serveur)
 */
require_once __DIR__ . '/../inc/auth.php';
exiger_role('etudiant');
require_once __DIR__ . '/../inc/db.php';

$userId = utilisateur_id();

// Récupération du CV
$stmt = $pdo->prepare('SELECT * FROM etudiants WHERE user_id = ?');
$stmt->execute([$userId]);
$e = $stmt->fetch();

if (!$e) {
    header('Location: /pages/formulaire-cv.php');
    exit;
}

$id = (int) $e['id'];
$get = function (string $sql) use ($pdo, $id) {
    $s = $pdo->prepare($sql);
    $s->execute([$id]);
    return $s->fetchAll();
};
$domaines    = array_column($get('SELECT type FROM domaines_recherche WHERE etudiant_id = ?'), 'type');
$formations  = $get('SELECT * FROM formations WHERE etudiant_id = ? ORDER BY ordre');
$experiences = $get('SELECT * FROM experiences WHERE etudiant_id = ? ORDER BY ordre');
$techniques  = $get('SELECT * FROM competences_techniques WHERE etudiant_id = ?');
$langues     = $get('SELECT * FROM competences_linguistiques WHERE etudiant_id = ?');

$LIB_DOMAINE = [
    'stage_1a' => 'Stage 1re année',
    'stage_2a' => 'Stage 2e année',
    'alternance_apprentissage' => 'Apprentissage',
    'alternance_professionnalisation' => 'Professionnalisation',
    'mobilite_internationale' => 'Mobilité internationale',
    'cdi' => 'CDI',
];

// Raccourci d'échappement
function h($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

$titrePage = "Mon CV";
require_once __DIR__ . '/../inc/header.php';
?>

<div class="cv">
    <div class="cv-entete">
        <?php if (!empty($e['photo'])): ?>
            <img class="avatar" src="/uploads/<?= h($e['photo']) ?>" alt="Photo de profil">
        <?php else: ?>
            <div class="avatar avatar-vide"><?= h(mb_substr($e['prenom'] ?: '?', 0, 1)) ?></div>
        <?php endif; ?>
        <div>
            <h1><?= h($e['prenom']) ?> <?= h($e['nom']) ?></h1>
            <?php if ($e['promotion']): ?><p class="promo"><?= h($e['promotion']) ?></p><?php endif; ?>
            <p class="coords">
                <?= h($e['ville']) ?>
                <?php if ($e['telephone']): ?> · <?= h($e['telephone']) ?><?php endif; ?>
                · <?= h($_SESSION['email']) ?>
            </p>
            <div class="tags" style="margin-top:.6rem">
                <?php foreach ($domaines as $d): ?>
                    <span class="tag"><?= h($LIB_DOMAINE[$d] ?? $d) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="cv-corps">
        <?php if ($e['biographie']): ?>
            <div class="cv-section">
                <h2>À propos</h2>
                <p><?= nl2br(h($e['biographie'])) ?></p>
            </div>
        <?php endif; ?>

        <div class="cv-section">
            <h2>Formation</h2>
            <?php if ($formations): foreach ($formations as $f): ?>
                <div class="cv-item">
                    <h3><?= h($f['diplome']) ?> — <?= h($f['etablissement']) ?></h3>
                    <div class="meta">
                        <?= h($f['date_debut']) ?><?= $f['date_fin'] ? ' – ' . h($f['date_fin']) : ' – en cours' ?>
                        <?= $f['domaine'] ? ' · ' . h($f['domaine']) : '' ?>
                    </div>
                    <?php if ($f['description']): ?><p><?= nl2br(h($f['description'])) ?></p><?php endif; ?>
                </div>
            <?php endforeach; else: ?>
                <p class="meta">Non renseigné.</p>
            <?php endif; ?>
        </div>

        <div class="cv-section">
            <h2>Expériences professionnelles</h2>
            <?php if ($experiences): foreach ($experiences as $x): ?>
                <div class="cv-item">
                    <h3><?= h($x['poste']) ?> — <?= h($x['entreprise']) ?></h3>
                    <div class="meta">
                        <?= h($x['date_debut']) ?><?= $x['date_fin'] ? ' – ' . h($x['date_fin']) : ' – en cours' ?>
                        <?= $x['lieu'] ? ' · ' . h($x['lieu']) : '' ?>
                    </div>
                    <?php if ($x['description']): ?><p><?= nl2br(h($x['description'])) ?></p><?php endif; ?>
                </div>
            <?php endforeach; else: ?>
                <p class="meta">Non renseigné.</p>
            <?php endif; ?>
        </div>

        <div class="cv-section">
            <h2>Compétences techniques</h2>
            <div class="tags">
                <?php if ($techniques): foreach ($techniques as $c): ?>
                    <span class="tag"><?= h($c['libelle']) ?> <span class="tag-niveau">(<?= h($c['niveau']) ?>)</span></span>
                <?php endforeach; else: ?>
                    <p class="meta">Non renseigné.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="cv-section">
            <h2>Langues</h2>
            <div class="tags">
                <?php if ($langues): foreach ($langues as $l): ?>
                    <span class="tag"><?= h($l['langue']) ?> <span class="tag-niveau">(<?= h($l['niveau']) ?>)</span></span>
                <?php endforeach; else: ?>
                    <p class="meta">Non renseigné.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="cv-actions">
        <a class="btn-principal" href="/pages/formulaire-cv.php">Modifier mon CV</a>
        <button class="btn-danger" id="btn-supprimer-compte">Supprimer mon compte</button>
    </div>
</div>

<script src="/js/profil.js"></script>
<?php require_once __DIR__ . '/../inc/footer.php'; ?>
