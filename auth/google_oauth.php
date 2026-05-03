<?php
// auth/google_oauth.php
// ─────────────────────────────────────────────────────────────
// Google OAuth 2.0 — Step 1: redirect user to Google's consent screen
//
// SETUP REQUIRED:
//   1. Go to https://console.cloud.google.com/
//   2. Create a project → APIs & Services → Credentials
//   3. Create "OAuth 2.0 Client ID" (Web application)
//   4. Set Authorized redirect URI:
//      http://localhost/salahhomty_platform/auth/google_callback.php
//   5. Copy Client ID and Client Secret into config below
// ─────────────────────────────────────────────────────────────
session_start();

// ── Your Google OAuth credentials ──
define('GOOGLE_CLIENT_ID',     '******');      // ← replace
define('GOOGLE_CLIENT_SECRET', '*******'); // ← replace
define('GOOGLE_REDIRECT_URI',  'http://localhost/salahhomty_platform/auth/google_callback.php');

// CSRF protection: store a random state in session
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = http_build_query([
    'client_id'     => GOOGLE_CLIENT_ID,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope'         => 'openid email profile',
    'state'         => $state,
    'access_type'   => 'online',
    'prompt'        => 'select_account', // always show account picker
]);

header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
exit;
?>
