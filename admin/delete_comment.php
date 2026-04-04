<?php
// ============================================================
// admin/comments_section.php
// À inclure dans admin/reports.php OU utiliser comme référence
// pour ajouter les commentaires dans le modal "Detail"
// ============================================================
//
// ÉTAPE 1 : Ajouter cet endpoint dans admin/api_comments.php
// ÉTAPE 2 : Modifier le modal "Detail" dans admin/reports.php
//           comme indiqué ci-dessous
// ============================================================

// ── api_comments.php (créer ce fichier dans /admin/) ────────
?>
<?php
// admin/api_comments.php
require_once 'auth_check.php';

header('Content-Type: application/json');

$report_id = (int)($_GET['report_id'] ?? 0);
if (!$report_id) { echo json_encode([]); exit; }

$rows = $pdo->prepare("
    SELECT c.id, c.content, c.created_at,
           u.name AS user_name, u.role
    FROM comments c
    LEFT JOIN users u ON u.id = c.user_id
    WHERE c.report_id = ?
    ORDER BY c.created_at ASC
");
$rows->execute([$report_id]);
echo json_encode($rows->fetchAll(PDO::FETCH_ASSOC));

// ── admin_comment_delete.php (créer ce fichier dans /admin/) ─
// Permet à l'admin de supprimer un commentaire inapproprié

// admin/delete_comment.php
// require_once 'auth_check.php';
// if (!isAdmin()) { http_response_code(403); exit; }
// $id = (int)($_POST['id'] ?? 0);
// if ($id) $pdo->prepare("DELETE FROM comments WHERE id=?")->execute([$id]);
// echo json_encode(['success' => true]);