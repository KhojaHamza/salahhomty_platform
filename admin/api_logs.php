<?php
// admin/api_logs.php
require_once 'auth_check.php';
header('Content-Type: application/json');

$report_id = (int)($_GET['report_id'] ?? 0);
if (!$report_id) { echo json_encode([]); exit; }

$stmt = $pdo->prepare("
    SELECT l.old_status, l.new_status, l.note, l.created_at,
           u.name AS changed_by
    FROM report_logs l
    LEFT JOIN users u ON u.id = l.changed_by
    WHERE l.report_id = :rid
    ORDER BY l.created_at DESC
    LIMIT 20
");
$stmt->execute([':rid' => $report_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));