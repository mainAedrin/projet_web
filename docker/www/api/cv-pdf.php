<?php
/**
 * api/cv-pdf.php — Génération du CV en PDF (FPDF 1.9)
 */
require_once __DIR__ . '/../inc/session.php';
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../lib/fpdf/fpdf.php';

/* ── Contrôle d'accès ───────────────────────────────────────────── */
$role = role_actuel();
if (!$role) { http_response_code(401); exit; }

if (isset($_GET['id']) && in_array($role, ['entreprise','admin'], true)) {
    $etudiantId = (int) $_GET['id'];
} elseif ($role === 'etudiant') {
    $s = $pdo->prepare('SELECT id FROM etudiants WHERE user_id = ?');
    $s->execute([utilisateur_id()]);
    $etudiantId = (int) $s->fetchColumn();
} else {
    http_response_code(403); exit;
}
if (!$etudiantId) { http_response_code(404); exit; }

/* ── Récupération des données ───────────────────────────────────── */
$s = $pdo->prepare('SELECT e.*, u.email FROM etudiants e JOIN users u ON u.id=e.user_id WHERE e.id=?');
$s->execute([$etudiantId]);
$etu = $s->fetch();
if (!$etu) { http_response_code(404); exit; }

$qFormations = $pdo->prepare('SELECT * FROM formations        WHERE etudiant_id=? ORDER BY ordre');
$qFormations->execute([$etudiantId]); $formations = $qFormations->fetchAll();

$qExp = $pdo->prepare('SELECT * FROM experiences              WHERE etudiant_id=? ORDER BY ordre');
$qExp->execute([$etudiantId]); $experiences = $qExp->fetchAll();

$qComp = $pdo->prepare('SELECT * FROM competences_techniques  WHERE etudiant_id=?');
$qComp->execute([$etudiantId]); $competences = $qComp->fetchAll();

$qLang = $pdo->prepare('SELECT * FROM competences_linguistiques WHERE etudiant_id=?');
$qLang->execute([$etudiantId]); $langues = $qLang->fetchAll();

$qDom = $pdo->prepare('SELECT type FROM domaines_recherche    WHERE etudiant_id=?');
$qDom->execute([$etudiantId]); $domaines = $qDom->fetchAll(PDO::FETCH_COLUMN);

/* ── Constantes ─────────────────────────────────────────────────── */
const VIOLET = [107, 44, 145];
const ORANGE = [243, 146, 0];
const TEXTE  = [43,  37, 51];
const DOUX   = [108, 101, 119];

const DOMAINE_LABELS = [
    'stage_1a'                        => 'Stage 1re annee',
    'stage_2a'                        => 'Stage 2e annee',
    'alternance_apprentissage'        => 'Alternance apprentissage',
    'alternance_professionnalisation' => 'Alternance professionnalisation',
    'mobilite_internationale'         => 'Mobilite internationale',
    'cdi'                             => 'CDI',
];

const NIVEAU_LABELS = [
    'debutant'      => 'Debutant',
    'intermediaire' => 'Intermediaire',
    'avance'        => 'Avance',
    'expert'        => 'Expert',
];

/* ── Encodage UTF-8 → ISO-8859-1 ───────────────────────────────── */
function e(string $s): string {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $s) ?: $s;
}

function dateFr(?string $d): string {
    if (!$d) return 'En cours';
    try { return (new DateTime($d))->format('m/Y'); }
    catch (Exception $e) { return $d; }
}

/* ── Classe PDF ─────────────────────────────────────────────────── */
class CvPdf extends FPDF
{
    private float $margeG = 18;
    private float $largeur; // largeur utile

    public function __construct()
    {
        parent::__construct('P', 'mm', 'A4');
        $this->SetMargins($this->margeG, 0, $this->margeG);
        $this->SetAutoPageBreak(true, 16);
        $this->largeur = 210 - 2 * $this->margeG;
    }

    public function Header() {}

    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('Helvetica', 'I', 7.5);
        $this->SetTextColor(...DOUX);
        $this->Cell(0, 5, e('CV genere via JUNIA CV Platform  —  junia.com'), 0, 0, 'C');
    }

    /* ─── En-tête colorée ──────────────────────────────────────── */
    public function entete(string $prenom, string $nom, string $email,
                           string $tel, string $ville, string $promotion): void
    {
        // Calcul hauteur dynamique
        $h = 38 + ($promotion ? 10 : 0);

        // Fond violet pleine largeur
        $this->SetFillColor(...VIOLET);
        $this->Rect(0, 0, 210, $h, 'F');

        // Bande orange sous le fond
        $this->SetFillColor(...ORANGE);
        $this->Rect(0, $h, 210, 2.5, 'F');

        // Nom
        $this->SetTextColor(255, 255, 255);
        $this->SetY(10);
        $this->SetFont('Helvetica', 'B', 21);
        $this->Cell(0, 8, e(strtoupper($nom) . ' ' . $prenom), 0, 1, 'C');

        // Contact
        $infos = array_filter([$email, $tel, $ville]);
        $this->SetFont('Helvetica', '', 9.5);
        $this->SetTextColor(225, 210, 240);
        $this->Cell(0, 6, e(implode('   |   ', $infos)), 0, 1, 'C');

        // Promotion
        if ($promotion) {
            $this->SetFont('Helvetica', 'I', 9.5);
            $this->SetTextColor(200, 180, 225);
            $this->SetY($this->GetY() + 3);
            $this->Cell(0, 5, e($promotion), 0, 1, 'C');
        }

        // Position curseur après la bande orange + marge
        $this->SetY($h + 2.5 + 7);
        $this->SetTextColor(...TEXTE);
    }

    /* ─── Titre de section ─────────────────────────────────────── */
    public function section(string $titre): void
    {
        $this->Ln(4);
        $this->SetFont('Helvetica', 'B', 10.5);
        $this->SetTextColor(...VIOLET);
        $this->Cell(0, 6, e(strtoupper($titre)), 0, 1);

        // Ligne orange sous le titre
        $this->SetFillColor(...ORANGE);
        $this->SetX($this->margeG);
        $this->Cell($this->largeur, 0.6, '', 0, 1, '', true);

        $this->Ln(3);
        $this->SetTextColor(...TEXTE);
    }

    /* ─── Entrée formation / expérience ────────────────────────── */
    public function entree(string $titre, string $sousTitre, string $periode, string $desc = ''): void
    {
        $this->SetFont('Helvetica', 'B', 9.5);
        $this->SetTextColor(...TEXTE);
        // Titre + période sur la même ligne
        $periodeW = $this->GetStringWidth($periode) + 2;
        $titreW   = $this->largeur - $periodeW;
        $this->Cell($titreW, 5, e($titre), 0, 0);
        $this->SetFont('Helvetica', 'I', 9);
        $this->SetTextColor(...DOUX);
        $this->Cell($periodeW, 5, e($periode), 0, 1, 'R');

        // Sous-titre
        if ($sousTitre) {
            $this->SetFont('Helvetica', 'I', 9);
            $this->SetTextColor(...DOUX);
            $this->Cell(0, 4.5, e($sousTitre), 0, 1);
        }

        // Description
        if ($desc) {
            $this->SetFont('Helvetica', '', 9);
            $this->SetTextColor(...TEXTE);
            $this->MultiCell(0, 4.5, e($desc), 0, 'L');
        }

        $this->Ln(2.5);
    }

    /* ─── Badges (compétences / domaines) ─────────────────────── */
    public function badges(array $items, bool $orange = false): void
    {
        $this->SetX($this->margeG);

        foreach ($items as $texte) {
            $this->SetFont('Helvetica', '', 8.5);
            $w = $this->GetStringWidth($texte) + 7;

            // Retour à la ligne si plus de place
            if ($this->GetX() + $w > 210 - $this->margeG) {
                $this->Ln(8);
                $this->SetX($this->margeG);
            }

            if ($orange) {
                $this->SetFillColor(...ORANGE);
                $this->SetTextColor(255, 255, 255);
            } else {
                $this->SetFillColor(243, 237, 247);
                $this->SetTextColor(...VIOLET);
            }

            // Dessin du badge arrondi via un rectangle
            $x = $this->GetX();
            $y = $this->GetY();
            $this->Rect($x, $y, $w, 6.5, 'F');
            $this->SetXY($x, $y);
            $this->Cell($w, 6.5, e($texte), 0, 0, 'C');
            $this->SetX($this->GetX() + 2.5);
        }

        $this->Ln(10);
        $this->SetTextColor(...TEXTE);
    }
}

/* ── Construction du PDF ────────────────────────────────────────── */
$pdf = new CvPdf();
$pdf->AddPage();

// En-tête
$pdf->entete(
    $etu['prenom']   ?? '',
    $etu['nom']      ?? '',
    $etu['email']    ?? '',
    $etu['telephone'] ?? '',
    $etu['ville']    ?? '',
    $etu['promotion'] ?? ''
);

// Profil / biographie
if (!empty($etu['biographie'])) {
    $pdf->section('Profil');
    $pdf->SetFont('Helvetica', '', 9.5);
    $pdf->SetTextColor(...TEXTE);
    $pdf->MultiCell(0, 5, e($etu['biographie']), 0, 'L');
}

// Domaines de recherche
if ($domaines) {
    $pdf->section('Domaines recherches');
    $labels = array_map(fn($d) => DOMAINE_LABELS[$d] ?? $d, $domaines);
    $pdf->badges($labels, true);
}

// Formations
if ($formations) {
    $pdf->section('Formation');
    foreach ($formations as $f) {
        $debut = $f['date_debut'] ?? '';
        $fin   = $f['date_fin'] ? $f['date_fin'] : 'En cours';
        $sous  = trim(($f['etablissement'] ?? '') . ($f['domaine'] ? ' - ' . $f['domaine'] : ''));
        $pdf->entree(
            $f['diplome']     ?? '',
            $sous,
            "$debut - $fin",
            $f['description'] ?? ''
        );
    }
}

// Expériences
if ($experiences) {
    $pdf->section('Experiences professionnelles');
    foreach ($experiences as $x) {
        $debut = dateFr($x['date_debut']);
        $fin   = $x['date_fin'] ? dateFr($x['date_fin']) : 'Auj.';
        $sous  = trim(($x['entreprise'] ?? '') . ($x['lieu'] ? ' - ' . $x['lieu'] : ''));
        $pdf->entree(
            $x['poste']       ?? '',
            $sous,
            "$debut - $fin",
            $x['description'] ?? ''
        );
    }
}

// Compétences techniques
if ($competences) {
    $pdf->section('Competences techniques');
    $labels = array_map(
        fn($c) => ($c['libelle'] ?? '') . '  (' . (NIVEAU_LABELS[$c['niveau']] ?? $c['niveau']) . ')',
        $competences
    );
    $pdf->badges($labels);
}

// Langues
if ($langues) {
    $pdf->section('Langues');
    $labels = array_map(fn($l) => ($l['langue'] ?? '') . '  ' . ($l['niveau'] ?? ''), $langues);
    $pdf->badges($labels);
}

/* ── Envoi ──────────────────────────────────────────────────────── */
$filename = preg_replace('/[^a-z0-9_]/i', '_',
    'CV_' . ($etu['prenom'] ?? 'cv') . '_' . ($etu['nom'] ?? 'junia'));
$pdf->Output('D', "$filename.pdf");
