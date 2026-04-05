<?php
// actions/services.php
// Handles: add, edit, delete for Municipal Services (BF6)
// Called via fetch() from admin/services.php

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

// Auth guard: verify admin role from DB (matches auth_check.php pattern)
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}
$_authStmt = $pdo->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
$_authStmt->execute([$_SESSION['user_id']]);
$_authUser = $_authStmt->fetch();
if (!$_authUser || $_authUser['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {

    // ────────────────────────────────────────────────────────
    case 'add':
        $name  = trim($_POST['name']  ?? '');
        $desc  = trim($_POST['desc']  ?? '');
        $cat   = $_POST['category']   ?? 'Water';
        $stat  = $_POST['status']     ?? 'Active';
        $start = $_POST['start']      ?? '08:00';
        $end   = $_POST['end']        ?? '18:00';
        $agent = (int)($_POST['agent_id'] ?? 0);

        if ($name === '') {
            echo json_encode(['success' => false, 'message' => 'Service name is required.']);
            exit;
        }

        $validCats  = ['Water','Environment','Lighting','Infrastructure','Waste'];
        $validStats = ['Active','Inactive','Maintenance'];
        if (!in_array($cat,  $validCats))  $cat  = 'Water';
        if (!in_array($stat, $validStats)) $stat = 'Active';

        $stmt = $pdo->prepare("
            INSERT INTO services (name, category, description, status,
                                  availability_start, availability_end)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $cat, $desc, $stat, $start, $end]);
        $newId = (int)$pdo->lastInsertId();

        // Link agent: update users.service_id if an agent was chosen
        if ($agent > 0) {
            $pdo->prepare("UPDATE users SET service_id = ? WHERE id = ? AND role = 'agent'")
                ->execute([$newId, $agent]);
        }

        // Return the full new service row (with agent name) for the frontend
        $row = fetchServiceById($pdo, $newId);
        echo json_encode(['success' => true, 'service' => $row]);
        break;

    // ────────────────────────────────────────────────────────
    case 'edit':
        $id    = (int)($_POST['id']       ?? 0);
        $name  = trim($_POST['name']      ?? '');
        $desc  = trim($_POST['desc']      ?? '');
        $cat   = $_POST['category']       ?? 'Water';
        $stat  = $_POST['status']         ?? 'Active';
        $start = $_POST['start']          ?? '08:00';
        $end   = $_POST['end']            ?? '18:00';
        $agent = (int)($_POST['agent_id'] ?? 0);

        if ($id === 0 || $name === '') {
            echo json_encode(['success' => false, 'message' => 'Invalid data.']);
            exit;
        }

        $validCats  = ['Water','Environment','Lighting','Infrastructure','Waste'];
        $validStats = ['Active','Inactive','Maintenance'];
        if (!in_array($cat,  $validCats))  $cat  = 'Water';
        if (!in_array($stat, $validStats)) $stat = 'Active';

        $stmt = $pdo->prepare("
            UPDATE services
            SET name = ?, category = ?, description = ?, status = ?,
                availability_start = ?, availability_end = ?
            WHERE id = ?
        ");
        $stmt->execute([$name, $cat, $desc, $stat, $start, $end, $id]);

        // Re-assign agent: unlink old agent from this service, link new one
        $pdo->prepare("UPDATE users SET service_id = NULL WHERE service_id = ? AND role = 'agent'")
            ->execute([$id]);
        if ($agent > 0) {
            $pdo->prepare("UPDATE users SET service_id = ? WHERE id = ? AND role = 'agent'")
                ->execute([$id, $agent]);
        }

        $row = fetchServiceById($pdo, $id);
        echo json_encode(['success' => true, 'service' => $row]);
        break;

    // ────────────────────────────────────────────────────────
    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
            exit;
        }

        // Unlink agents before deleting (FK allows SET NULL but let's be explicit)
        $pdo->prepare("UPDATE users SET service_id = NULL WHERE service_id = ? AND role = 'agent'")
            ->execute([$id]);

        $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    // ────────────────────────────────────────────────────────
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

// ── Helper ─────────────────────────────────────────────────
function fetchServiceById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("
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
        WHERE s.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}
?>