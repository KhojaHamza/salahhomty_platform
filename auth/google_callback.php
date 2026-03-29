<?php
// auth/google_callback.php
// Step 2: Google redirects back here with ?code=... and ?state=...
session_start();
require_once '../config/database.php';

// Same credentials as google_oauth.php
define('GOOGLE_CLIENT_ID',     'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI',  'http://localhost/salahhomty_platform/auth/google_callback.php');

// ── CSRF check ──
if (!isset($_GET['state']) || $_GET['state'] !== ($_SESSION['oauth_state'] ?? '')) {
    header('Location: ../pages/signin.php?error=oauth_state_mismatch');
    exit;
}
unset($_SESSION['oauth_state']);

// ── Error from Google ──
if (isset($_GET['error'])) {
    header('Location: ../pages/signin.php?error=google_cancelled');
    exit;
}

$code = $_GET['code'] ?? '';
if (empty($code)) {
    header('Location: ../pages/signin.php?error=google_no_code');
    exit;
}

// ── Exchange code for access token ──
$tokenResponse = file_get_contents('https://oauth2.googleapis.com/token', false, stream_context_create([
    'http' => [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/x-www-form-urlencoded',
        'content' => http_build_query([
            'code'          => $code,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code',
        ]),
    ],
]));

$tokenData = json_decode($tokenResponse, true);

if (empty($tokenData['access_token'])) {
    error_log('Google OAuth token error: ' . print_r($tokenData, true));
    header('Location: ../pages/signin.php?error=google_token_failed');
    exit;
}

// ── Fetch Google user info ──
$userInfoResponse = file_get_contents(
    'https://www.googleapis.com/oauth2/v3/userinfo',
    false,
    stream_context_create(['http' => [
        'method' => 'GET',
        'header' => 'Authorization: Bearer ' . $tokenData['access_token'],
    ]])
);

$googleUser = json_decode($userInfoResponse, true);

if (empty($googleUser['email'])) {
    header('Location: ../pages/signin.php?error=google_no_email');
    exit;
}

$googleEmail = strtolower($googleUser['email']);
$googleName  = $googleUser['name']    ?? $googleUser['given_name'] ?? 'User';
$googleSub   = $googleUser['sub']     ?? ''; // unique Google ID

// ── Find or create user in DB ──
try {
    // Check by email
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $googleEmail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Auto-register via Google
        $ins = $pdo->prepare("
            INSERT INTO users (name, email, password, google_id, created_at)
            VALUES (:name, :email, :password, :google_id, NOW())
        ");
        $ins->execute([
            ':name'      => $googleName,
            ':email'     => $googleEmail,
            ':password'  => '', // no password for Google users
            ':google_id' => $googleSub,
        ]);
        $userId = $pdo->lastInsertId();
        $user   = ['id' => $userId, 'name' => $googleName, 'email' => $googleEmail];
    }

    // ── Create session ──
    session_regenerate_id(true);
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];

    header('Location: ../index.php');
    exit;

} catch (PDOException $e) {
    error_log('google_callback PDO error: ' . $e->getMessage());
    header('Location: ../pages/signin.php?error=server_error');
    exit;
}
?>