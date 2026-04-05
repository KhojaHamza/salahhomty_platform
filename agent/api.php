<?php
// agent/api.php — version finale corrigée (MariaDB compatible)
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';

function jsonResponse(bool $success, string $message, array $data = []): never {
    echo json_encode(
        ['success' => $success, 'message' => $message, ...$data],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function getDB(): PDO {
    global $pdo;
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
}

// ── Auth ──────────────────────────────────────────────────
if (empty($_SESSION['user_id'])) {
    jsonResponse(false, 'Unauthorized');
}
if (!in_array($_SESSION['user_role'], ['agent', 'admin'], true)) {
    jsonResponse(false, 'Forbidden');
}

$user = [
    'id'   => (int)$_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'role' => $_SESSION['user_role'],
];

$action = trim($_GET['action'] ?? $_POST['action'] ?? '');
if ($action === '') {
    jsonResponse(false, 'No action specified.');
}

try {
    switch ($action) {

        // ── Dashboard stats ───────────────────────────────
        case 'stats': {
            $db  = getDB();
            $row = $db->query("
                SELECT
                    COUNT(*)                                                          AS total,
                    SUM(status = 'Open')                                              AS open,
                    SUM(status = 'In progress')                                       AS in_progress,
                    SUM(status IN ('Resolved','Closed'))                              AS resolved,
                    SUM(deadline IS NOT NULL AND deadline < NOW()
                        AND status NOT IN ('Resolved','Closed'))                      AS overdue
                FROM reports
            ")->fetch();
            foreach ($row as $k => $v) $row[$k] = (int)($v ?? 0);
            jsonResponse(true, 'ok', ['stats' => $row]);
        }

        // ── Recent reports (dashboard) ────────────────────
        case 'recent_reports': {
            $db    = getDB();
            $filter = $_GET['filter'] ?? 'all';
            // FIX MariaDB: LIMIT en int injecté directement (pas via binding)
            $limit  = max(1, min(50, (int)($_GET['limit'] ?? 8)));

            $conditions = [];
            $params     = [];
            if ($filter !== 'all') {
                $map = [
                    'pending'  => 'Open',
                    'progress' => 'In progress',
                    'resolved' => 'Resolved',
                ];
                if (isset($map[$filter])) {
                    $conditions[] = 'r.status = ?';
                    $params[]     = $map[$filter];
                }
            }
            $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $stmt = $db->prepare("
                SELECT
                    r.id, r.title, r.description, r.status,
                    r.created_at, r.deadline,
                    u.name  AS citizen_name,
                    a.name  AS agent_name,
                    c.name  AS category_name,
                    (SELECT COUNT(*) FROM comments cm WHERE cm.report_id = r.id) AS comment_count
                FROM reports r
                LEFT JOIN users      u ON u.id = r.user_id
                LEFT JOIN users      a ON a.id = r.assigned_to
                LEFT JOIN categories c ON c.id = r.category_id
                $where
                ORDER BY r.created_at DESC
                LIMIT $limit
            ");
            $stmt->execute($params);
            jsonResponse(true, 'ok', ['reports' => $stmt->fetchAll()]);
        }

        // ── All reports (pagination + search + filtre) ────
        case 'reports': {
            $db      = getDB();
            $filter  = $_GET['filter'] ?? 'all';
            $search  = trim($_GET['search'] ?? '');
            $page    = max(1, (int)($_GET['page'] ?? 1));
            $perPage = 10;
            // FIX MariaDB: LIMIT/OFFSET en int injectés directement
            $offset  = ($page - 1) * $perPage;

            $conditions = [];
            $params     = [];

            if ($filter !== 'all') {
                $map = [
                    'pending'  => 'Open',
                    'progress' => 'In progress',
                    'resolved' => 'Resolved',
                ];
                if (isset($map[$filter])) {
                    $conditions[] = 'r.status = ?';
                    $params[]     = $map[$filter];
                }
            }

            if ($search !== '') {
                $conditions[] = '(r.title LIKE ? OR r.description LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
                $like = '%' . addcslashes($search, '%_\\') . '%';
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
                $params[] = $like;
            }

            $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

            $countStmt = $db->prepare("
                SELECT COUNT(*) AS cnt
                FROM reports r
                LEFT JOIN users u ON u.id = r.user_id
                $where
            ");
            $countStmt->execute($params);
            $total = (int)($countStmt->fetch()['cnt'] ?? 0);
            $pages = $total > 0 ? (int)ceil($total / $perPage) : 1;

            $stmt = $db->prepare("
                SELECT
                    r.id, r.title, r.description,
                    r.status, r.created_at, r.deadline,
                    r.latitude, r.longitude, r.image,
                    u.name  AS citizen_name,
                    u.email AS citizen_email,
                    u.phone AS citizen_phone,
                    a.name  AS agent_name,
                    c.name  AS category_name,
                    (SELECT COUNT(*) FROM comments cm WHERE cm.report_id = r.id) AS comment_count
                FROM reports r
                LEFT JOIN users      u ON u.id = r.user_id
                LEFT JOIN users      a ON a.id = r.assigned_to
                LEFT JOIN categories c ON c.id = r.category_id
                $where
                ORDER BY r.created_at DESC
                LIMIT $perPage OFFSET $offset
            ");
            $stmt->execute($params);
            $reports = $stmt->fetchAll();

            jsonResponse(true, 'ok', [
                'reports'  => $reports,
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
                'pages'    => $pages,
            ]);
        }

        // ── Détail d'un report ────────────────────────────
        case 'report_detail': {
            $id = (int)($_GET['id'] ?? 0);
            if (!$id) jsonResponse(false, 'Invalid report ID.');
            $db = getDB();

            $stmt = $db->prepare("
                SELECT r.*,
                    u.name  AS citizen_name,
                    u.email AS citizen_email,
                    u.phone AS citizen_phone,
                    a.name  AS agent_name,
                    c.name  AS category_name
                FROM reports r
                LEFT JOIN users      u ON u.id = r.user_id
                LEFT JOIN users      a ON a.id = r.assigned_to
                LEFT JOIN categories c ON c.id = r.category_id
                WHERE r.id = ?
            ");
            $stmt->execute([$id]);
            $report = $stmt->fetch();
            if (!$report) jsonResponse(false, 'Report not found.');

            $cStmt = $db->prepare("
                SELECT cm.*, u.name AS author_name, u.role AS author_role
                FROM comments cm
                LEFT JOIN users u ON u.id = cm.user_id
                WHERE cm.report_id = ?
                ORDER BY cm.created_at ASC
            ");
            $cStmt->execute([$id]);

            $lStmt = $db->prepare("
                SELECT rl.*, u.name AS changed_by_name
                FROM report_logs rl
                LEFT JOIN users u ON u.id = rl.changed_by
                WHERE rl.report_id = ?
                ORDER BY rl.created_at DESC
            ");
            $lStmt->execute([$id]);

            jsonResponse(true, 'ok', [
                'report'   => $report,
                'comments' => $cStmt->fetchAll(),
                'logs'     => $lStmt->fetchAll(),
            ]);
        }

        // ── Mise à jour du statut ─────────────────────────
        case 'update_status': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $id     = (int)($_POST['id'] ?? 0);
            $status = trim($_POST['status'] ?? '');
            $note   = trim($_POST['note'] ?? '');
            $valid  = ['Open', 'In progress', 'Resolved', 'Closed'];

            if (!$id) jsonResponse(false, 'Invalid report ID.');
            if (!in_array($status, $valid, true)) jsonResponse(false, 'Invalid status value.');

            $db  = getDB();
            $old = $db->prepare("SELECT status FROM reports WHERE id = ?");
            $old->execute([$id]);
            $row = $old->fetch();
            if (!$row) jsonResponse(false, 'Report not found.');

            $db->prepare("UPDATE reports SET status = ? WHERE id = ?")->execute([$status, $id]);
            $db->prepare("
                INSERT INTO report_logs (report_id, changed_by, old_status, new_status, note)
                VALUES (?, ?, ?, ?, ?)
            ")->execute([$id, $user['id'], $row['status'], $status, $note ?: null]);

            jsonResponse(true, 'Status updated successfully.');
        }

        // ── Assignation d'un agent ────────────────────────
        case 'assign_report': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $id      = (int)($_POST['id'] ?? 0);
            $agentId = ($_POST['agent_id'] ?? '') !== '' ? (int)$_POST['agent_id'] : null;
            if (!$id) jsonResponse(false, 'Invalid report ID.');

            $db = getDB();
            if ($agentId !== null) {
                $chk = $db->prepare("SELECT id FROM users WHERE id = ? AND role IN ('agent','admin')");
                $chk->execute([$agentId]);
                if (!$chk->fetch()) jsonResponse(false, 'Agent not found.');
            }
            $db->prepare("UPDATE reports SET assigned_to = ? WHERE id = ?")->execute([$agentId, $id]);
            jsonResponse(true, 'Report assigned successfully.');
        }

        // ── Deadline ──────────────────────────────────────
        case 'set_deadline': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $id       = (int)($_POST['id'] ?? 0);
            $deadline = trim($_POST['deadline'] ?? '');
            if (!$id) jsonResponse(false, 'Invalid report ID.');

            $deadlineVal = $deadline ?: null;
            if ($deadlineVal !== null && strtotime($deadlineVal) === false) {
                jsonResponse(false, 'Invalid deadline format.');
            }

            $db = getDB();
            $db->prepare("UPDATE reports SET deadline = ? WHERE id = ?")->execute([$deadlineVal, $id]);
            jsonResponse(true, 'Deadline updated.');
        }

        // ── Ajouter un commentaire ────────────────────────
        case 'add_comment': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $reportId = (int)($_POST['report_id'] ?? 0);
            $content  = trim($_POST['content'] ?? '');
            if (!$reportId) jsonResponse(false, 'Invalid report ID.');
            if ($content === '') jsonResponse(false, 'Comment cannot be empty.');
            if (mb_strlen($content) > 2000) jsonResponse(false, 'Comment too long (max 2000 chars).');

            $db  = getDB();
            $chk = $db->prepare("SELECT id FROM reports WHERE id = ?");
            $chk->execute([$reportId]);
            if (!$chk->fetch()) jsonResponse(false, 'Report not found.');

            $db->prepare("INSERT INTO comments (report_id, user_id, content) VALUES (?,?,?)")
               ->execute([$reportId, $user['id'], $content]);
            $newId = (int)$db->lastInsertId();

            $stmt = $db->prepare("
                SELECT cm.*, u.name AS author_name, u.role AS author_role
                FROM comments cm
                LEFT JOIN users u ON u.id = cm.user_id
                WHERE cm.id = ?
            ");
            $stmt->execute([$newId]);
            jsonResponse(true, 'Comment added.', ['comment' => $stmt->fetch()]);
        }

        // ── Supprimer un commentaire (admin) ──────────────
        case 'delete_comment': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            if ($user['role'] !== 'admin') jsonResponse(false, 'Admin only.');
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) jsonResponse(false, 'Invalid ID.');
            $db = getDB();
            $db->prepare("DELETE FROM comments WHERE id = ?")->execute([$id]);
            jsonResponse(true, 'Comment deleted.');
        }

        // ── Liste des agents ──────────────────────────────
        case 'agents': {
            $db = getDB();
            jsonResponse(true, 'ok', [
                'agents' => $db->query("
                    SELECT id, name, email, role
                    FROM users
                    WHERE role IN ('agent','admin')
                    ORDER BY name ASC
                ")->fetchAll()
            ]);
        }

        // ── Mise à jour du profil ─────────────────────────
        case 'update_profile': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $name  = trim($_POST['name']  ?? '');
            $phone = trim($_POST['phone'] ?? '');
            if (!$name) jsonResponse(false, 'Name is required.');

            $db = getDB();
            $db->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?")
               ->execute([$name, $phone ?: null, $user['id']]);

            // FIX: bonnes clés de session
            $_SESSION['user_name']  = $name;
            $_SESSION['user_phone'] = $phone;

            jsonResponse(true, 'Profile updated successfully.');
        }

        // ── Changement de mot de passe ────────────────────
        case 'change_password': {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, 'POST required.');
            $current = $_POST['current_password'] ?? '';
            $new     = $_POST['new_password']     ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            if (!$current || !$new || !$confirm) jsonResponse(false, 'All fields are required.');
            if ($new !== $confirm) jsonResponse(false, 'New passwords do not match.');
            if (strlen($new) < 6)  jsonResponse(false, 'Password must be at least 6 characters.');

            $db   = getDB();
            $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $row  = $stmt->fetch();

            if (!$row || !password_verify($current, $row['password'])) {
                jsonResponse(false, 'Current password is incorrect.');
            }
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")
               ->execute([password_hash($new, PASSWORD_BCRYPT), $user['id']]);

            jsonResponse(true, 'Password changed successfully.');
        }

        // ── Stats par catégorie ───────────────────────────
        case 'category_stats': {
            $db   = getDB();
            $rows = $db->query("
                SELECT
                    COALESCE(c.name, 'Uncategorized') AS category,
                    COUNT(r.id)                        AS total
                FROM reports r
                LEFT JOIN categories c ON c.id = r.category_id
                GROUP BY r.category_id, c.name
                ORDER BY total DESC
            ")->fetchAll();

            foreach ($rows as &$row) $row['total'] = (int)$row['total'];
            unset($row);

            jsonResponse(true, 'ok', ['categories' => $rows]);
        }

        // ── Charge de travail des agents ──────────────────
        case 'workload': {
            $db   = getDB();
            $rows = $db->query("
                SELECT
                    u.id, u.name, u.role,
                    COUNT(CASE WHEN r.status NOT IN ('Resolved','Closed') THEN 1 END) AS open_count,
                    COUNT(r.id)                                                        AS total_count
                FROM users u
                LEFT JOIN reports r ON r.assigned_to = u.id
                WHERE u.role IN ('agent','admin')
                GROUP BY u.id, u.name, u.role
                ORDER BY open_count DESC, u.name ASC
            ")->fetchAll();

            foreach ($rows as &$row) {
                $row['open_count']  = (int)$row['open_count'];
                $row['total_count'] = (int)$row['total_count'];
            }
            unset($row);

            jsonResponse(true, 'ok', ['workload' => $rows]);
        }

        default:
            jsonResponse(false, 'Unknown action: ' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8'));
    }

} catch (PDOException $e) {
    error_log('[MuniPortal API] PDOException: ' . $e->getMessage());
    jsonResponse(false, 'Database error: ' . $e->getMessage());
} catch (Throwable $e) {
    error_log('[MuniPortal API] Error: ' . $e->getMessage());
    jsonResponse(false, 'Server error: ' . $e->getMessage());
}