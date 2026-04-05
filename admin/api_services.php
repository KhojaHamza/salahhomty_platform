<?php
// admin/api_services.php
require_once 'auth_check.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// ── Fetch all services with agent + report stats ────────────
$stmt = $pdo->query("
    SELECT
        s.id,
        s.name,
        s.category,
        s.description,
        s.status,
        TIME_FORMAT(s.availability_start, '%H:%i') AS availability_start,
        TIME_FORMAT(s.availability_end,   '%H:%i') AS availability_end,
        u.id   AS agent_id,
        u.name AS agent_name,
        (SELECT COUNT(*) FROM reports r
         JOIN categories c ON r.category_id = c.id
         WHERE c.service_id = s.id)                                   AS total_reports,
        (SELECT COUNT(*) FROM reports r
         JOIN categories c ON r.category_id = c.id
         WHERE c.service_id = s.id AND r.status NOT IN ('Resolved','Closed')) AS active_reports,
        (SELECT COUNT(*) FROM reports r
         JOIN categories c ON r.category_id = c.id
         WHERE c.service_id = s.id AND r.status = 'Overdue')          AS overdue_reports
    FROM services s
    LEFT JOIN users u ON u.service_id = s.id AND u.role = 'agent'
    ORDER BY s.id DESC
");

$services = $stmt->fetchAll();

// ── Fetch agents list (for add/edit dropdowns) ──────────────
$agents = $pdo->query("
    SELECT id, name FROM users
    WHERE role = 'agent'
    ORDER BY name ASC
")->fetchAll();

echo json_encode([
    'services' => $services,
    'agents'   => $agents,
]);
?>