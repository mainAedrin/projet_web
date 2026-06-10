<?php
/**
 * api/enregistrer-cv.php — Création / mise à jour du CV de l'étudiant connecté
 * Reçoit du multipart/form-data (à cause de l'upload photo).
 * Les listes dynamiques (formations, expériences, compétences) arrivent
 * en chaînes JSON dans les champs du formulaire.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';

if (role_actuel() !== 'etudiant') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux étudiants.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$userId = utilisateur_id();

// ─── Récupérer la fiche étudiant ─────────────────────────────────
$stmt = $pdo->prepare('SELECT id, photo FROM etudiants WHERE user_id = ?');
$stmt->execute([$userId]);
$etudiant = $stmt->fetch();
if (!$etudiant) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Profil étudiant introuvable.']);
    exit;
}
$etudiantId   = (int) $etudiant['id'];
$photoActuelle = $etudiant['photo'];

// ─── Champs simples ──────────────────────────────────────────────
$nom            = trim($_POST['nom'] ?? '');
$prenom         = trim($_POST['prenom'] ?? '');
$date_naissance = $_POST['date_naissance'] ?? null;
$telephone      = trim($_POST['telephone'] ?? '');
$adresse        = trim($_POST['adresse'] ?? '');
$ville          = trim($_POST['ville'] ?? '');
$code_postal    = trim($_POST['code_postal'] ?? '');
$biographie     = trim($_POST['biographie'] ?? '');
$promotion      = trim($_POST['promotion'] ?? '');

if ($nom === '' || $prenom === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Nom et prénom obligatoires.']);
    exit;
}
$date_naissance = ($date_naissance === '') ? null : $date_naissance;

// ─── Listes dynamiques (JSON) ────────────────────────────────────
$formations  = json_decode($_POST['formations']  ?? '[]', true) ?: [];
$experiences = json_decode($_POST['experiences'] ?? '[]', true) ?: [];
$techniques  = json_decode($_POST['competences_tech'] ?? '[]', true) ?: [];
$langues     = json_decode($_POST['competences_lang'] ?? '[]', true) ?: [];
$domaines    = $_POST['domaines'] ?? [];   // tableau de cases cochées
if (!is_array($domaines)) $domaines = [];

$domaines_valides = [
    'stage_1a', 'stage_2a', 'alternance_apprentissage',
    'alternance_professionnalisation', 'mobilite_internationale', 'cdi',
];

// ─── Upload photo (optionnel) ────────────────────────────────────
$photo = $photoActuelle;   // on garde l'ancienne par défaut

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $tmp  = $_FILES['photo']['tmp_name'];
    $size = $_FILES['photo']['size'];

    // Type MIME réel (pas l'extension fournie par le client)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $tmp);
    finfo_close($finfo);

    $autorises = ['image/jpeg' => 'jpg', 'image/png' => 'png'];

    if (!isset($autorises[$mime])) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Photo : formats acceptés jpg ou png.']);
        exit;
    }
    if ($size > 2 * 1024 * 1024) {   // 2 Mo max
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Photo : taille maximale 2 Mo.']);
        exit;
    }

    // Nom unique pour éviter les collisions
    $nomFichier = 'photo_' . $etudiantId . '_' . bin2hex(random_bytes(6)) . '.' . $autorises[$mime];
    $dossier    = __DIR__ . '/../uploads/';
    if (!move_uploaded_file($tmp, $dossier . $nomFichier)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Échec de l\'enregistrement de la photo.']);
        exit;
    }

    // Supprimer l'ancienne photo si elle existait
    if ($photoActuelle && is_file($dossier . $photoActuelle)) {
        @unlink($dossier . $photoActuelle);
    }
    $photo = $nomFichier;
}

// ─── Tout enregistrer dans une transaction ───────────────────────
try {
    $pdo->beginTransaction();

    // 1. Infos principales
    $stmt = $pdo->prepare(
        'UPDATE etudiants SET nom=?, prenom=?, date_naissance=?, telephone=?,
         adresse=?, ville=?, code_postal=?, biographie=?, promotion=?, photo=?
         WHERE id=?'
    );
    $stmt->execute([
        $nom, $prenom, $date_naissance, $telephone, $adresse, $ville,
        $code_postal, $biographie, $promotion, $photo, $etudiantId,
    ]);

    // 2. Domaines de recherche (on efface puis on réinsère)
    $pdo->prepare('DELETE FROM domaines_recherche WHERE etudiant_id=?')->execute([$etudiantId]);
    $ins = $pdo->prepare('INSERT INTO domaines_recherche (etudiant_id, type) VALUES (?, ?)');
    foreach ($domaines as $d) {
        if (in_array($d, $domaines_valides, true)) {
            $ins->execute([$etudiantId, $d]);
        }
    }

    // 3. Formations
    $pdo->prepare('DELETE FROM formations WHERE etudiant_id=?')->execute([$etudiantId]);
    $ins = $pdo->prepare(
        'INSERT INTO formations (etudiant_id, etablissement, diplome, domaine, date_debut, date_fin, description, ordre)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($formations as $i => $f) {
        if (empty($f['etablissement']) || empty($f['diplome'])) continue;
        $ins->execute([
            $etudiantId,
            trim($f['etablissement']),
            trim($f['diplome']),
            trim($f['domaine'] ?? ''),
            (int) ($f['date_debut'] ?? date('Y')),
            !empty($f['date_fin']) ? (int) $f['date_fin'] : null,
            trim($f['description'] ?? ''),
            $i,
        ]);
    }

    // 4. Expériences
    $pdo->prepare('DELETE FROM experiences WHERE etudiant_id=?')->execute([$etudiantId]);
    $ins = $pdo->prepare(
        'INSERT INTO experiences (etudiant_id, poste, entreprise, lieu, date_debut, date_fin, description, ordre)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    foreach ($experiences as $i => $x) {
        if (empty($x['poste']) || empty($x['entreprise'])) continue;
        $ins->execute([
            $etudiantId,
            trim($x['poste']),
            trim($x['entreprise']),
            trim($x['lieu'] ?? ''),
            !empty($x['date_debut']) ? $x['date_debut'] : date('Y-m-d'),
            !empty($x['date_fin']) ? $x['date_fin'] : null,
            trim($x['description'] ?? ''),
            $i,
        ]);
    }

    // 5. Compétences techniques
    $pdo->prepare('DELETE FROM competences_techniques WHERE etudiant_id=?')->execute([$etudiantId]);
    $ins = $pdo->prepare('INSERT INTO competences_techniques (etudiant_id, libelle, niveau) VALUES (?, ?, ?)');
    $niveauxTech = ['debutant', 'intermediaire', 'avance', 'expert'];
    foreach ($techniques as $c) {
        if (empty($c['libelle'])) continue;
        $niv = in_array($c['niveau'] ?? '', $niveauxTech, true) ? $c['niveau'] : 'intermediaire';
        $ins->execute([$etudiantId, trim($c['libelle']), $niv]);
    }

    // 6. Compétences linguistiques
    $pdo->prepare('DELETE FROM competences_linguistiques WHERE etudiant_id=?')->execute([$etudiantId]);
    $ins = $pdo->prepare('INSERT INTO competences_linguistiques (etudiant_id, langue, niveau) VALUES (?, ?, ?)');
    $niveauxLang = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2', 'natif'];
    foreach ($langues as $l) {
        if (empty($l['langue'])) continue;
        $niv = in_array($l['niveau'] ?? '', $niveauxLang, true) ? $l['niveau'] : 'B1';
        $ins->execute([$etudiantId, trim($l['langue']), $niv]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'CV enregistré avec succès.']);

} catch (PDOException $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur lors de l\'enregistrement du CV.']);
}
