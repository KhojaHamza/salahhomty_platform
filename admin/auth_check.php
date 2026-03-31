<?php
// admin/auth_check.php
// Include at the top of every admin page
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../user/signin.php?redirect=admin');
    exit;
}

require_once '../config/database.php';

// Reload role from DB (don't trust session alone for role)
$stmt = $pdo->prepare("SELECT id, name, email, role, service_id FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $_SESSION['user_id']]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser || !in_array($currentUser['role'], ['admin', 'agent'])) {
    header('Location: ../index.php?error=access_denied');
    exit;
}

// Helper: is this user a full admin?
function isAdmin(): bool {
    global $currentUser;
    return $currentUser['role'] === 'admin';
}

// Helper: format relative time
function timeAgo(string $dt): string {
    $diff = time() - strtotime($dt);
    if ($diff < 60)     return 'Just now';
    if ($diff < 3600)   return floor($diff / 60) . 'm ago';
    if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', strtotime($dt));
}

// Helper: status badge HTML
function statusBadge(string $status): string {
    $map = [
        'Open'        => ['#dbeafe','#1d4ed8','#93c5fd'],
        'In progress' => ['#ffedd5','#c2410c','#fb923c'],
        'Resolved'    => ['#dcfce7','#15803d','#4ade80'],
        'Transferred' => ['#f5f3ff','#6d28d9','#a78bfa'],
        'Overdue'     => ['#fee2e2','#991b1b','#f87171'],
    ];
    [$bg, $text, $dot] = $map[$status] ?? ['#f3f4f6','#374151','#9ca3af'];
    return "<span style='display:inline-flex;align-items:center;gap:5px;background:$bg;color:$text;
            padding:3px 10px;border-radius:100px;font-size:12px;font-weight:700;white-space:nowrap'>
            <span style='width:6px;height:6px;border-radius:50%;background:$dot;flex-shrink:0'></span>
            $status</span>";
}
?>