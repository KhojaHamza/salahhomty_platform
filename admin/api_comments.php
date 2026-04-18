<?php
// admin/api_comments.php
require_once 'auth_check.php';

header('Content-Type: application/json');

$report_id = (int)($_GET['report_id'] ?? 0);

if (!$report_id) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.id, c.content, c.created_at,
           u.name AS user_name, u.role
    FROM comments c
    LEFT JOIN users u ON u.id = c.user_id
    WHERE c.report_id = ?
    ORDER BY c.created_at ASC
");
$stmt->execute([$report_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($comments);
?>