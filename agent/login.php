<?php
session_start();
require_once __DIR__ . '/includes/auth.php';

// Already logged in → redirect
if (!empty($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $user = loginUser($email, $password);
        if ($user) {
            if (!in_array($user['role'], ['agent', 'admin'], true)) {
                logoutUser();
                $error = 'Access denied. This portal is for agents and administrators only.';
            } else {
                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

$presetError = match ($_GET['error'] ?? '') {
    'access_denied' => 'Access denied. Agents and admins only.',
    default         => '',
};
if ($presetError) $error = $presetError;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Agent Login — MuniPortal</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
  :root{--accent:#14b8a6;--accent-dark:#0d9488;--bg:#f0f2f5;--white:#fff;--border:#e5e7eb;--text:#111827;--muted:#6b7280;--red:#ef4444}
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Poppins',sans-serif;background:var(--bg);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
  .card{background:var(--white);border:1px solid var(--border);border-radius:16px;width:420px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.08)}
  .card-head{background:var(--accent);padding:28px 32px;text-align:center}
  .logo{font-size:32px;margin-bottom:8px}
  .card-head h1{font-size:20px;font-weight:800;color:#fff}
  .card-head p{font-size:12px;color:rgba(255,255,255,.75);margin-top:4px}
  .card-body{padding:28px 32px}
  .form-group{margin-bottom:16px}
  label{font-size:11px;color:var(--muted);font-weight:600;display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.4px}
  input{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;padding:10px 14px;color:var(--text);font-family:'Poppins',sans-serif;font-size:13px;outline:none;transition:border-color .15s}
  input:focus{border-color:var(--accent);background:#fff}
  .btn{width:100%;padding:11px;border-radius:8px;background:var(--accent);color:#fff;border:none;font-family:'Poppins',sans-serif;font-size:14px;font-weight:700;cursor:pointer;transition:background .15s;margin-top:6px}
  .btn:hover{background:var(--accent-dark)}
  .error-box{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;color:var(--red);font-size:12px;font-weight:500;margin-bottom:16px;display:flex;gap:8px;align-items:center}
  .footer-note{text-align:center;font-size:11px;color:var(--muted);margin-top:18px}
</style>
</head>
<body>
<div class="card">
  <div class="card-head">
    <div class="logo">🏛️</div>
    <h1>MuniPortal</h1>
    <p>Agent &amp; Administrator Access</p>
  </div>
  <div class="card-body">
    <?php if ($error): ?>
    <div class="error-box">⚠️ <?= h($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" placeholder="agent@municipality.gov" value="<?= h($_POST['email'] ?? '') ?>" required autocomplete="username"/>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password"/>
      </div>
      <button type="submit" class="btn">Sign In →</button>
    </form>
    <div class="footer-note">This portal is restricted to municipal agents and administrators.</div>
  </div>
</div>
</body>
</html>