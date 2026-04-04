<?php
/**
 * api.php — Central AJAX API for Agent Portal
 * All requests must be authenticated (agent or admin).
 */
require_once __DIR__ . '/includes/auth.php';

header('Content-Type: application/json');

// Authenticate
$user = currentUser();
if (!$user || !in_array($user['role'], ['agent', 'admin'], true)) {
    jsonResponse(false, 'Unauthorized', ['code' => 401]);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {

        // ── Dashboard stats ──────────────────────────────────────────────
        case 'stats':
            $db = getDB();
            $stats = [];
            $row = $db->query("SELECT
                COUNT(*) AS total,
                SUM(status='Open') AS open,
                SUM(status='In Progress') AS in_progress,
                SUM(status='Resolved') AS resolved,
                SUM(deadline IS NOT NULL AND deadline < NOW() AND status NOT IN ('Resolved','Closed')) AS overdue
                FROM reports")->fetch();
            jsonResponse(true, 'ok', ['stats' => $row]);

        // ── Recent reports for dashboard ─────────────────────────────────
        case 'recent_reports':
            $db     = getDB();
            $filter = $_GET['filter'] ?? 'all';
            $limit  = max(1, min(50, (int)($_GET['limit'] ?? 8)));
            $where  = '';
            $params = [];
            if ($filter !== 'all') {
                $map = ['pending' => 'Open', 'progress' => 'In Progress', 'resolved' => 'Resolved'];
                if (isset($map[$filter])) {
                    $where  = 'WHERE r.status = ?';
                    $params = [$map[$filter]];
                }
            }
            $sql = "SELECT r.id, r.title, r.description, r.image, r.latitude, r.longitude,
                           r.status, r.category_id, r.assigned_to, r.deadline,
                           r.created_at,
                           u.name AS citizen_name,
                           a.name AS agent_name,
                           c.name AS category_name,
                           (SELECT COUNT(*) FROM comments cm WHERE cm.report_id = r.id) AS comment_count
                    FROM reports r
                    LEFT JOIN users u ON u.id = r.user_id
                    LEFT JOIN users a ON a.id = r.assigned_to
                    LEFT JOIN categories c ON c.id = r.category_id
                    $where
                    ORDER BY r.created_at DESC
                    LIMIT $limit";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reports = $stmt->fetchAll();
            jsonResponse(true, 'ok', ['reports' => $reports]);

        // ── All reports (with search & pagination) ───────────────────────
        case 'reports':
            $db     = getDB();
            $filter = $_GET['filter'] ?? 'all';
            $search = trim($_GET['search'] ?? '');
            $page   = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 10;
            $offset = ($page - 1) * $perPage;

            $conditions = [];
            $params     = [];

            if ($filter !== 'all') {
                $map = ['pending' => 'Open', 'progress' => 'In Progress', 'resolved' => 'Resolved'];
                if (isset($map[$filter])) {
                    $conditions[] = 'r.status = ?';
                    $params[]     = $map[$filter];
                }
            }
            if ($search !== '') {
                $conditions[] = '(r.title LIKE ? OR r.description LIKE ? OR u.name LIKE ?)';
                $like = "%$search%";
                $params[] = $like; $params[] = $like; $params[] = $like;
            }
            $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

            // total count
            $countStmt = $db->prepare("SELECT COUNT(*) FROM reports r LEFT JOIN users u ON u.id = r.user_id $where");
            $countStmt->execute($params);
            $total = (int)$countStmt->fetchColumn();

            $sql = "SELECT r.id, r.title, r.description, r.image, r.latitude, r.longitude,
                           r.status, r.category_id, r.assigned_to, r.deadline,
                           r.created_at,
                           u.name AS citizen_name, u.email AS citizen_email, u.phone AS citizen_phone,
                           a.name AS agent_name,
                           c.name AS category_name,
                           (SELECT COUNT(*) FROM comments cm WHERE cm.report_id = r.id) AS comment_count
                    FROM reports r
                    LEFT JOIN users u ON u.id = r.user_id
                    LEFT JOIN users a ON a.id = r.assigned_to
                    LEFT JOIN categories c ON c.id = r.category_id
                    $where
                    ORDER BY r.created_at DESC
                    LIMIT $perPage OFFSET $offset";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $reports = $stmt->fetchAll();

            jsonResponse(true, 'ok', [
                'reports'   => $reports,
                'total'     => $total,
                'page'      => $page,
                'per_page'  => $perPage,
                'pages'     => (int)ceil($total / $perPage),
            ]);

        // ── Single report detail ─────────────────────────────────────────
        case 'report_detail':
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) jsonResponse(false, 'Invalid report ID.');
            $db = getDB();
            $stmt = $db->prepare("SELECT r.*, 
                           u.name AS citizen_name, u.email AS citizen_email, u.phone AS citizen_phone,
                           a.name AS agent_name,
                           c.name AS category_name
                    FROM reports r
                    LEFT JOIN users u ON u.id = r.user_id
                    LEFT JOIN users a ON a.id = r.assigned_to
                    LEFT JOIN categories c ON c.id = r.category_id
                    WHERE r.id = ?");
            $stmt->execute([$id]);
            $report = $stmt->fetch();
            if (!$report) jsonResponse(false, 'Report not found.');

            // comments
            $cStmt = $db->prepare("SELECT cm.*, u.name AS author_name, u.role AS author_role
                FROM comments cm
                LEFT JOIN users u ON u.id = cm.user_id
                WHERE cm.report_id = ?
                ORDER BY cm.created_at ASC");
            $cStmt->execute([$id]);
            $comments = $cStmt->fetchAll();

            // logs
            $lStmt = $db->prepare("SELECT rl.*, u.name AS changed_by_name
                FROM report_logs rl
                LEFT JOIN users u ON u.id = rl.changed_by
                WHERE rl.report_id = ?
                ORDER BY rl.created_at DESC");
            $lStmt->execute([$id]);
            $logs = $lStmt->fetchAll();

            jsonResponse(true, 'ok', ['report' => $report, 'comments' => $comments, 'logs' => $logs]);

        // ── Update report status ─────────────────────────────────────────
        case 'update_status':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $id      = (int)($_POST['id'] ?? 0);
            $status  = trim($_POST['status'] ?? '');
            $note    = trim($_POST['note'] ?? '');
            $valid   = ['Open', 'In Progress', 'Resolved', 'Closed'];
            if (!$id) jsonResponse(false, 'Invalid report ID.');
            if (!in_array($status, $valid, true)) jsonResponse(false, 'Invalid status value.');

            $db = getDB();
            // get old status
            $old = $db->prepare("SELECT status FROM reports WHERE id = ?");
            $old->execute([$id]);
            $row = $old->fetch();
            if (!$row) jsonResponse(false, 'Report not found.');

            $db->prepare("UPDATE reports SET status = ? WHERE id = ?")->execute([$status, $id]);
            $db->prepare("INSERT INTO report_logs (report_id, changed_by, old_status, new_status, note) VALUES (?,?,?,?,?)")
               ->execute([$id, $user['id'], $row['status'], $status, $note]);
            jsonResponse(true, 'Status updated successfully.');

        // ── Assign report to agent ───────────────────────────────────────
        case 'assign_report':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $id       = (int)($_POST['id'] ?? 0);
            $agentId  = $_POST['agent_id'] !== '' ? (int)$_POST['agent_id'] : null;
            if (!$id) jsonResponse(false, 'Invalid report ID.');

            $db = getDB();
            if ($agentId !== null) {
                $chk = $db->prepare("SELECT id FROM users WHERE id = ? AND role IN ('agent','admin')");
                $chk->execute([$agentId]);
                if (!$chk->fetch()) jsonResponse(false, 'Agent not found.');
            }
            $db->prepare("UPDATE reports SET assigned_to = ? WHERE id = ?")->execute([$agentId, $id]);
            jsonResponse(true, 'Report assigned successfully.');

        // ── Set deadline ─────────────────────────────────────────────────
        case 'set_deadline':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $id       = (int)($_POST['id'] ?? 0);
            $deadline = trim($_POST['deadline'] ?? '');
            if (!$id) jsonResponse(false, 'Invalid report ID.');
            $deadlineVal = $deadline ?: null;
            if ($deadlineVal && !strtotime($deadlineVal)) jsonResponse(false, 'Invalid deadline format.');

            $db = getDB();
            $db->prepare("UPDATE reports SET deadline = ? WHERE id = ?")->execute([$deadlineVal, $id]);
            jsonResponse(true, 'Deadline updated.');

        // ── Add comment ──────────────────────────────────────────────────
        case 'add_comment':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $reportId = (int)($_POST['report_id'] ?? 0);
            $content  = trim($_POST['content'] ?? '');
            if (!$reportId) jsonResponse(false, 'Invalid report ID.');
            if ($content === '') jsonResponse(false, 'Comment cannot be empty.');
            if (mb_strlen($content) > 2000) jsonResponse(false, 'Comment too long (max 2000 characters).');

            $db = getDB();
            $chk = $db->prepare("SELECT id FROM reports WHERE id = ?");
            $chk->execute([$reportId]);
            if (!$chk->fetch()) jsonResponse(false, 'Report not found.');

            $db->prepare("INSERT INTO comments (report_id, user_id, content) VALUES (?,?,?)")
               ->execute([$reportId, $user['id'], $content]);
            $newId = $db->lastInsertId();

            $stmt = $db->prepare("SELECT cm.*, u.name AS author_name, u.role AS author_role
                FROM comments cm LEFT JOIN users u ON u.id = cm.user_id WHERE cm.id = ?");
            $stmt->execute([$newId]);
            $comment = $stmt->fetch();
            jsonResponse(true, 'Comment added.', ['comment' => $comment]);

        // ── Delete comment (admin only) ──────────────────────────────────
        case 'delete_comment':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            if ($user['role'] !== 'admin') jsonResponse(false, 'Admin only.');
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonResponse(false, 'Invalid ID.');
            $db = getDB();
            $db->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
            jsonResponse(true, 'Comment deleted.');

        // ── List agents ──────────────────────────────────────────────────
        case 'agents':
            $db = getDB();
            $stmt = $db->query("SELECT id, name, email, role FROM users WHERE role IN ('agent','admin') ORDER BY name");
            jsonResponse(true, 'ok', ['agents' => $stmt->fetchAll()]);

        // ── Profile update ───────────────────────────────────────────────
        case 'update_profile':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $name  = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if (!$name) jsonResponse(false, 'Name is required.');

            $db = getDB();
            $db->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?")
               ->execute([$name, $phone ?: null, $user['id']]);
            // refresh session
            $_SESSION['user']['name']  = $name;
            $_SESSION['user']['phone'] = $phone;
            jsonResponse(true, 'Profile updated successfully.');

        // ── Change password ──────────────────────────────────────────────
        case 'change_password':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            if (!$current || !$new || !$confirm) jsonResponse(false, 'All password fields are required.');
            if ($new !== $confirm) jsonResponse(false, 'New passwords do not match.');
            if (strlen($new) < 6) jsonResponse(false, 'New password must be at least 6 characters.');

            $db = getDB();
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $row = $stmt->fetch();
            if (!$row || !password_verify($current, $row['password'])) {
                jsonResponse(false, 'Current password is incorrect.');
            }
            $hash = password_hash($new, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user['id']]);
            jsonResponse(true, 'Password changed successfully.');

        // ── Category stats (for dashboard chart) ─────────────────────────
        case 'category_stats':
            $db = getDB();
            $rows = $db->query("SELECT 
                c.name AS category,
                COUNT(r.id) AS total
                FROM reports r
                LEFT JOIN categories c ON c.id = r.category_id
                GROUP BY c.id, c.name
                ORDER BY total DESC")->fetchAll();
            // uncategorized
            $unc = $db->query("SELECT COUNT(*) FROM reports WHERE category_id IS NULL")->fetchColumn();
            if ($unc > 0) $rows[] = ['category' => 'Uncategorized', 'total' => $unc];
            jsonResponse(true, 'ok', ['categories' => $rows]);

        // ── Agent workload ────────────────────────────────────────────────
        case 'workload':
            $db = getDB();
            $rows = $db->query("SELECT u.id, u.name, u.role,
                COUNT(CASE WHEN r.status NOT IN ('Resolved','Closed') THEN 1 END) AS open_count,
                COUNT(r.id) AS total_count
                FROM users u
                LEFT JOIN reports r ON r.assigned_to = u.id
                WHERE u.role IN ('agent','admin')
                GROUP BY u.id, u.name, u.role
                ORDER BY open_count DESC")->fetchAll();
            jsonResponse(true, 'ok', ['workload' => $rows]);

        default:
            jsonResponse(false, 'Unknown action: ' . h($action));
    }
} catch (PDOException $e) {
    jsonResponse(false, 'Database error: ' . $e->getMessage());
} catch (Throwable $e) {
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}