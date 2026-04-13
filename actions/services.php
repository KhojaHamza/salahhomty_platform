<?php
// actions/services.php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

// ── Actions publiques (pas besoin d'être connecté) ────────
if ($action === 'list' || $action === 'detail') {

    // ── list : GET ?action=list&search=&category=&region=&status= ──
    if ($action === 'list') {
        $search   = trim($_GET['search']   ?? '');
        $category = trim($_GET['category'] ?? '');
        $region   = trim($_GET['region']   ?? '');
        $status   = trim($_GET['status']   ?? '');

        $conditions = [];
        $params     = [];

        if ($search !== '') {
            $conditions[] = '(s.name LIKE ? OR s.description LIKE ?)';
            $like = "%$search%";
            $params[] = $like;
            $params[] = $like;
        }
        if ($category !== '' && $category !== 'all') {
            $conditions[] = 's.category = ?';
            $params[]     = $category;
        }
        if ($region !== '') {
            $conditions[] = 's.region = ?';
            $params[]     = $region;
        }
        if ($status !== '') {
            $conditions[] = 's.status = ?';
            $params[]     = $status;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $pdo->prepare("
            SELECT s.id, s.name, s.category, s.description, s.status,
                   s.region, s.latitude, s.longitude, s.image, s.qr_code,
                   TIME_FORMAT(s.availability_start,'%H:%i') AS availability_start,
                   TIME_FORMAT(s.availability_end,  '%H:%i') AS availability_end,
                   s.created_at
            FROM services s
            $where
            ORDER BY s.name ASC
        ");
        $stmt->execute($params);
        $services = $stmt->fetchAll();

        echo json_encode(['success' => true, 'services' => $services]);
        exit;
    }

    // ── detail : GET ?action=detail&id=X ──
    if ($action === 'detail') {
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) { echo json_encode(['success' => false, 'message' => 'Invalid ID.']); exit; }

        $stmt = $pdo->prepare("
            SELECT s.id, s.name, s.category, s.description, s.status,
                   s.region, s.latitude, s.longitude, s.image, s.qr_code,
                   TIME_FORMAT(s.availability_start,'%H:%i') AS availability_start,
                   TIME_FORMAT(s.availability_end,  '%H:%i') AS availability_end,
                   s.created_at
            FROM services s
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        $service = $stmt->fetch();

        if (!$service) { echo json_encode(['success' => false, 'message' => 'Service not found.']); exit; }

        echo json_encode(['success' => true, 'service' => $service]);
        exit;
    }
}

// ── Action protégée : request (citoyen connecté) ──────────
if ($action === 'request') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'message' => 'Vous devez être connecté.']);
        exit;
    }

    $serviceId = (int)($_POST['service_id'] ?? 0);
    $message   = trim($_POST['message'] ?? '');

    if (!$serviceId) {
        echo json_encode(['success' => false, 'message' => 'Service invalide.']);
        exit;
    }

    // Vérifier que le service existe et est actif
    $chk = $pdo->prepare("SELECT id, status FROM services WHERE id = ?");
    $chk->execute([$serviceId]);
    $svc = $chk->fetch();
    if (!$svc) {
        echo json_encode(['success' => false, 'message' => 'Service introuvable.']);
        exit;
    }
    if ($svc['status'] !== 'Active') {
        echo json_encode(['success' => false, 'message' => 'Ce service n\'est pas disponible actuellement.']);
        exit;
    }

    // Vérifier doublon (même user, même service, statut pending)
    $dup = $pdo->prepare("SELECT id FROM service_requests WHERE user_id = ? AND service_id = ? AND status = 'pending'");
    $dup->execute([$_SESSION['user_id'], $serviceId]);
    if ($dup->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Vous avez déjà une demande en attente pour ce service.']);
        exit;
    }

    $pdo->prepare("INSERT INTO service_requests (user_id, service_id, message) VALUES (?, ?, ?)")
        ->execute([$_SESSION['user_id'], $serviceId, $message ?: null]);

    echo json_encode(['success' => true, 'message' => 'Demande envoyée avec succès.']);
    exit;
}

// ── Actions admin (add / edit / delete) ───────────────────
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

switch ($action) {

    case 'add':
        $name  = trim($_POST['name']  ?? '');
        $desc  = trim($_POST['desc']  ?? '');
        $cat   = $_POST['category']   ?? 'Water';
        $stat  = $_POST['status']     ?? 'Active';
        $start = $_POST['start']      ?? '08:00';
        $end   = $_POST['end']        ?? '18:00';
        $agent = (int)($_POST['agent_id'] ?? 0);
        $region = trim($_POST['region'] ?? '');
        $lat   = $_POST['latitude']  !== '' ? (float)$_POST['latitude']  : null;
        $lng   = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;

        if ($name === '') { echo json_encode(['success' => false, 'message' => 'Service name is required.']); exit; }

        $validCats  = ['Water','Environment','Lighting','Infrastructure','Waste'];
        $validStats = ['Active','Inactive','Maintenance'];
        if (!in_array($cat,  $validCats))  $cat  = 'Water';
        if (!in_array($stat, $validStats)) $stat = 'Active';

        $stmt = $pdo->prepare("
            INSERT INTO services (name, category, description, status,
                                  availability_start, availability_end, region, latitude, longitude)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $cat, $desc, $stat, $start, $end, $region ?: null, $lat, $lng]);
        $newId = (int)$pdo->lastInsertId();

        if ($agent > 0) {
            $pdo->prepare("UPDATE users SET service_id = ? WHERE id = ? AND role = 'agent'")
                ->execute([$newId, $agent]);
        }

        $row = fetchServiceById($pdo, $newId);
        echo json_encode(['success' => true, 'service' => $row]);
        break;

    case 'edit':
        $id    = (int)($_POST['id']       ?? 0);
        $name  = trim($_POST['name']      ?? '');
        $desc  = trim($_POST['desc']      ?? '');
        $cat   = $_POST['category']       ?? 'Water';
        $stat  = $_POST['status']         ?? 'Active';
        $start = $_POST['start']          ?? '08:00';
        $end   = $_POST['end']            ?? '18:00';
        $agent = (int)($_POST['agent_id'] ?? 0);
        $region = trim($_POST['region']   ?? '');
        $lat   = $_POST['latitude']  !== '' ? (float)$_POST['latitude']  : null;
        $lng   = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;

        if ($id === 0 || $name === '') { echo json_encode(['success' => false, 'message' => 'Invalid data.']); exit; }

        $validCats  = ['Water','Environment','Lighting','Infrastructure','Waste'];
        $validStats = ['Active','Inactive','Maintenance'];
        if (!in_array($cat,  $validCats))  $cat  = 'Water';
        if (!in_array($stat, $validStats)) $stat = 'Active';

        $pdo->prepare("
            UPDATE services SET name=?, category=?, description=?, status=?,
                                availability_start=?, availability_end=?,
                                region=?, latitude=?, longitude=?
            WHERE id=?
        ")->execute([$name, $cat, $desc, $stat, $start, $end, $region ?: null, $lat, $lng, $id]);

        $pdo->prepare("UPDATE users SET service_id = NULL WHERE service_id = ? AND role = 'agent'")->execute([$id]);
        if ($agent > 0) {
            $pdo->prepare("UPDATE users SET service_id = ? WHERE id = ? AND role = 'agent'")->execute([$id, $agent]);
        }

        echo json_encode(['success' => true, 'service' => fetchServiceById($pdo, $id)]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if ($id === 0) { echo json_encode(['success' => false, 'message' => 'Invalid ID.']); exit; }
        $pdo->prepare("UPDATE users SET service_id = NULL WHERE service_id = ? AND role = 'agent'")->execute([$id]);
        $pdo->prepare("DELETE FROM services WHERE id = ?")->execute([$id]);
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
}

// ── Helper ────────────────────────────────────────────────
function fetchServiceById(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare("
        SELECT s.id, s.name, s.category, s.description, s.status,
               s.region, s.latitude, s.longitude, s.image, s.qr_code,
               TIME_FORMAT(s.availability_start,'%H:%i') AS availability_start,
               TIME_FORMAT(s.availability_end,  '%H:%i') AS availability_end,
               u.id   AS agent_id,
               u.name AS agent_name
        FROM services s
        LEFT JOIN users u ON u.service_id = s.id AND u.role = 'agent'
        WHERE s.id = ?
        LIMIT 1
    ");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}