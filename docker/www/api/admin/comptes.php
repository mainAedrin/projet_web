<?php
/**
 * api/admin/comptes.php — Gestion des comptes (admin)
 * GET                 → liste des comptes (filtrable par ?role=)
 * POST action=suspend → suspend/réactive un compte
 * POST action=delete  → supprime un compte
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../../inc/session.php';
require_once __DIR__ . '/../../inc/db.php';

if (role_actuel() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Réservé aux administrateurs.']);
    exit;
}

// ─── Lecture : liste des comptes ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $role = $_GET['role'] ?? '';
    $sql  = "
        SELECT u.id, u.email, u.role, u.is_active, u.created_at,
               COALESCE(CONCAT(e.prenom, ' ', e.nom), ent.nom, '—') AS nom_affiche
        FROM users u
        LEFT JOIN etudiants   e   ON e.user_id   = u.id
        LEFT JOIN entreprises ent ON ent.user_id = u.id
    ";
    $params = [];
    if (in_array($role, ['etudiant', 'entreprise', 'admin'], true)) {
        $sql .= ' WHERE u.role = ?';
        $params[] = $role;
    }
    $sql .= ' ORDER BY u.created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'comptes' => $stmt->fetchAll()]);
    exit;
}

// ─── Écriture : actions ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data   = json_decode(file_get_contents('php://input'), true);
    $action = $data['action'] ?? '';
    $id     = (int) ($data['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Identifiant invalide.']);
        exit;
    }
    if ($id === utilisateur_id()) {
        http_response_code(422);
        echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas agir sur votre propre compte.']);
        exit;
    }

    try {
        if ($action === 'suspend') {
            // Bascule is_active 1↔0
            $pdo->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = ?')->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Statut du compte modifié.']);
        } elseif ($action === 'delete') {
            $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Compte supprimé.']);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Action inconnue.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erreur serveur.']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
