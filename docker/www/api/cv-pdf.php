<?php
/**
 * api/cv-pdf.php — Génération du CV en PDF (FPDF)
 * GET sans paramètre  → CV de l'étudiant connecté
 * GET ?id=X           → CV de l'étudiant X (entreprise ou admin)
 */
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

/* ── Déterminer quel étudiant exporter ──────────────────────────── */
$role = role_actuel();

if (!$role) {
    http_response_code(401); exit;
}

if (isset($_GET['id']) && in_array($role, ['entreprise', 'admin'], true)) {
    $etudiantId = (int) $_GET['id'];
} elseif ($role === 'etudiant') {
    $s = $pdo->prepare('SELECT id FROM etudiants WHERE user_id = ?');
    $s->execute([utilisateur_id()]);
    $etudiantId = (int) $s->fetchColumn();
} else {
    http_response_code(403); exit;
}

if (!$etudiantId) { http_response_code(404); exit; }

/* ── Récupérer les données ──────────────────────────────────────── */
$s = $pdo->prepare('SELECT e.*, u.email FROM etudiants e JOIN users u ON u.id=e.user_id WHERE e.id=?');
$s->execute([$etudiantId]);
$etu = $s->fetch();
if (!$etu) { http_response_code(404); exit; }

$formations = $pdo->prepare('SELECT * FROM formations WHERE etudiant_id=? ORDER BY ordre');
$formations->execute([$etudiantId]); $formations = $formations->fetchAll();

$experiences = $pdo->prepare('SELECT * FROM experiences WHERE etudiant_id=? ORDER BY ordre');
$experiences->execute([$etudiantId]); $experiences = $experiences->fetchAll();

$competences = $pdo->prepare('SELECT * FROM competences_techniques WHERE etudiant_id=?');
$competences->execute([$etudiantId]); $competences = $competences->fetchAll();

$langues = $pdo->prepare('SELECT * FROM competences_linguistiques WHERE etudiant_id=?');
$langues->execute([$etudiantId]); $langues = $langues->fetchAll();

$domaines = $pdo->prepare('SELECT type FROM domaines_recherche WHERE etudiant_id=?');
$domaines->execute([$etudiantId]); $domaines = $domaines->fetchAll(PDO::FETCH_COLUMN);

/* ── Helper encodage (FPDF = ISO-8859-1) ──────────────────────── */
function e(string $s): string {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $s) ?: $s;
}

$DOMAINE_LABELS = [
    'stage_1a'                       => 'Stage 1re annee',
    'stage_2a'                       => 'Stage 2e annee',
    'alternance_apprentissage'       => 'Alternance - Apprentissage',
    'alternance_professionnalisation'=> 'Alternance - Professionnalisation',
    'mobilite_internationale'        => 'Mobilite internationale',
    'cdi'                            => 'CDI',
];

$NIVEAU_LABELS = [
    'debutant'     => 'Debutant',
    'intermediaire'=> 'Intermediaire',
    'avance'       => 'Avance',
    'expert'       => 'Expert',
];

/* ── Classe PDF personnalisée ───────────────────────────────────── */
class CV extends FPDF {
    private string $violet  = '';
    private string $orange  = '';
    private string $gris    = '';

    function __construct() {
        parent::__construct('P', 'mm', 'A4');
        $this->SetMargins(18, 18, 18);
        $this->SetAutoPageBreak(true, 18);
    }

    function Header() {}
    function Footer() {
        $this->SetY(-13);
        $this->SetFont('Helvetica', 'I', 8);
        $this->SetTextColor(160, 160, 160);
        $this->Cell(0, 5, e('CV genere via JUNIA CV Platform'), 0, 0, 'C');
    }

    /* ─ Bande de titre colorée ─ */
    function bandeTitre(string $prenom, string $nom, string $email, string $tel = '', string $ville = '') {
        // Fond violet
        $this->SetFillColor(107, 44, 145);
        $this->Rect(0, 0, 210, 42, 'F');
        // Bande orange fine en bas
        $this->SetFillColor(243, 146, 0);
        $this->Rect(0, 42, 210, 3, 'F');

        $this->SetTextColor(255, 255, 255);
        $this->SetY(9);
        $this->SetFont('Helvetica', 'B', 22);
        $this->Cell(0, 9, e(strtoupper($nom).' '.$prenom), 0, 1, 'C');

        $this->SetFont('Helvetica', '', 10);
        $infos = array_filter([$email, $tel, $ville]);
        $this->Cell(0, 6, e(implode('   |   ', $infos)), 0, 1, 'C');
        $this->Ln(8);
    }

    /* ─ Titre de section ─ */
    function section(string $titre) {
        $this->Ln(4);
        $this->SetFont('Helvetica', 'B', 11);
        $this->SetTextColor(107, 44, 145);
        $this->Cell(0, 6, e($titre), 0, 1);
        // Ligne orange
        $this->SetFillColor(243, 146, 0);
        $this->Rect($this->GetX(), $this->GetY(), 174, 0.7, 'F');
        $this->Ln(4);
        $this->SetTextColor(43, 37, 51);
    }

    /* ─ Entrée formation/expérience ─ */
    function entree(string $titre, string $sousTitre, string $periode, string $desc = '') {
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetTextColor(43, 37, 51);
        $this->Cell(130, 5, e($titre), 0, 0);
        $this->SetFont('Helvetica', 'I', 9);
        $this->SetTextColor(108, 101, 119);
        $this->Cell(0, 5, e($periode), 0, 1, 'R');

        if ($sousTitre) {
            $this->SetFont('Helvetica', 'I', 9);
            $this->SetTextColor(108, 101, 119);
            $this->Cell(0, 4, e($sousTitre), 0, 1);
        }
        if ($desc) {
            $this->SetFont('Helvetica', '', 9);
            $this->SetTextColor(43, 37, 51);
            $this->MultiCell(0, 4.5, e($desc), 0, 'L');
        }
        $this->Ln(2);
    }

    /* ─ Badge compétence ─ */
    function badge(string $texte, bool $orange = false) {
        $w = $this->GetStringWidth($texte) + 6;
        if ($this->GetX() + $w > 192) { $this->Ln(7); $this->SetX(18); }
        if ($orange) {
            $this->SetFillColor(243, 146, 0);
            $this->SetTextColor(255, 255, 255);
        } else {
            $this->SetFillColor(243, 237, 247);
            $this->SetTextColor(107, 44, 145);
        }
        $this->SetFont('Helvetica', '', 8.5);
        $this->Cell($w, 6, e($texte), 0, 0, 'C', true);
        $this->SetX($this->GetX() + 3);
    }
}

/* ── Construction du PDF ────────────────────────────────────────── */
$pdf = new CV();
$pdf->AddPage();

// En-tête
$pdf->bandeTitre(
    $etu['prenom'] ?? '',
    $etu['nom'] ?? '',
    $etu['email'] ?? '',
    $etu['telephone'] ?? '',
    $etu['ville'] ?? ''
);

// Promotion
if ($etu['promotion']) {
    $pdf->SetFont('Helvetica', 'I', 10);
    $pdf->SetTextColor(108, 101, 119);
    $pdf->Cell(0, 5, e($etu['promotion']), 0, 1, 'C');
    $pdf->Ln(2);
}

// Biographie
if ($etu['biographie']) {
    $pdf->section('Profil');
    $pdf->SetFont('Helvetica', '', 9.5);
    $pdf->SetTextColor(43, 37, 51);
    $pdf->MultiCell(0, 5, e($etu['biographie']), 0, 'L');
}

// Domaines de recherche
if ($domaines) {
    $pdf->section('Domaines recherches');
    $pdf->SetX(18);
    foreach ($domaines as $d) {
        $pdf->badge($DOMAINE_LABELS[$d] ?? $d, true);
    }
    $pdf->Ln(8);
}

// Formations
if ($formations) {
    $pdf->section('Formation');
    foreach ($formations as $f) {
        $debut = $f['date_debut'];
        $fin   = $f['date_fin'] ? $f['date_fin'] : 'En cours';
        $pdf->entree(
            $f['diplome'],
            $f['etablissement'] . ($f['domaine'] ? ' — ' . $f['domaine'] : ''),
            "$debut – $fin",
            $f['description'] ?? ''
        );
    }
}

// Expériences
if ($experiences) {
    $pdf->section('Experiences professionnelles');
    foreach ($experiences as $x) {
        $debut = (new DateTime($x['date_debut']))->format('m/Y');
        $fin   = $x['date_fin'] ? (new DateTime($x['date_fin']))->format('m/Y') : 'Auj.';
        $lieu  = $x['lieu'] ? ' — ' . $x['lieu'] : '';
        $pdf->entree(
            $x['poste'],
            $x['entreprise'] . $lieu,
            "$debut – $fin",
            $x['description'] ?? ''
        );
    }
}

// Compétences techniques
if ($competences) {
    $pdf->section('Competences techniques');
    $pdf->SetX(18);
    foreach ($competences as $c) {
        $pdf->badge($c['libelle'] . ' · ' . ($NIVEAU_LABELS[$c['niveau']] ?? $c['niveau']));
    }
    $pdf->Ln(8);
}

// Langues
if ($langues) {
    $pdf->section('Langues');
    $pdf->SetX(18);
    foreach ($langues as $l) {
        $pdf->badge($l['langue'] . ' ' . $l['niveau']);
    }
    $pdf->Ln(8);
}

/* ── Sortie ─────────────────────────────────────────────────────── */
$nom  = preg_replace('/[^a-z0-9]/i', '_', ($etu['prenom'] ?? 'cv') . '_' . ($etu['nom'] ?? 'junia'));
$pdf->Output('D', "CV_{$nom}.pdf");
