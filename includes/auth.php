<?php
session_start();

require_once __DIR__ . '/db.php';

/**
 * Require the user to be logged in and be an agent or admin.
 * If not, redirect to login page.
 */
function requireAgent(): array {
    if (empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
    $user = $_SESSION['user'];
    if (!in_array($user['role'], ['agent', 'admin'], true)) {
        header('Location: login.php?error=access_denied');
        exit;
    }
    return $user;
}

/**
 * Return the currently logged-in user array or null.
 */
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Log in a user by email + password.
 * Returns user array on success, or null on failure.
 */
function loginUser(string $email, string $password): ?array {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Remove password from session data
        unset($user['password']);
        $_SESSION['user'] = $user;
        return $user;
    }
    return null;
}

/**
 * Log out the current user.
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();
}

/**
 * Escape HTML output safely.
 */
function h(mixed $value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Return a JSON response and exit.
 */
function jsonResponse(bool $success, string $message, array $data = []): never {
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, ...$data]);
    exit;
}

/**
 * Return user initials from name.
 */
function initials(string $name): string {
    $parts = explode(' ', trim($name));
    $init = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $init .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $init ?: '??';
}

/**
 * Format a datetime string to a human-readable format.
 */
function formatDate(string $date): string {
    $ts = strtotime($date);
    if (!$ts) return '—';
    return date('d/m/Y H:i', $ts);
}

/**
 * Get status badge HTML.
 */
function statusBadge(string $status): string {
    $map = [
        'Open'        => ['class' => 'st-open',     'label' => 'Open',        'dot' => '#0ea5e9'],
        'In Progress' => ['class' => 'st-progress',  'label' => 'In Progress', 'dot' => '#3b82f6'],
        'Resolved'    => ['class' => 'st-resolved',  'label' => 'Resolved',    'dot' => '#10b981'],
        'Closed'      => ['class' => 'st-resolved',  'label' => 'Closed',      'dot' => '#10b981'],
    ];
    $s = $map[$status] ?? ['class' => 'st-open', 'label' => h($status), 'dot' => '#9ca3af'];
    return '<span class="st-badge ' . $s['class'] . '"><span class="dot"></span>' . $s['label'] . '</span>';
}