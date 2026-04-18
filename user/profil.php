<?php
session_start();
require_once '../config/database.php';

// 🔒 Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";
$error   = "";

// ──────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────
function fetchUser(PDO $pdo, int $id) {
    $stmt = $pdo->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ──────────────────────────────────────────────
// POST handler
// ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── 1. UPDATE PROFILE INFO ──────────────────
    if ($action === 'update_profile') {
        $name  = trim($_POST['name']  ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (mb_strlen($name) < 2) {
            $error = "Name must be at least 2 characters.";

        } elseif (!empty($phone) && !preg_match('/^\+?[0-9\s\-]{7,20}$/', $phone)) {
            $error = "Please enter a valid phone number.";

        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone ?: null, $user_id]);
            $message = "Profile updated successfully!";
        }
    }

    // ── 2. UPDATE PASSWORD ──────────────────────
    elseif ($action === 'update_password') {
        $current  = $_POST['current_password']  ?? '';
        $password = $_POST['password']          ?? '';
        $confirm  = $_POST['confirm_password']  ?? '';

        // Fetch current hash to verify
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($current) || !password_verify($current, $row['password'])) {
            $error = "Current password is incorrect.";

        } elseif (strlen($password) < 8) {
            $error = "New password must be at least 8 characters.";

        } elseif ($password !== $confirm) {
            $error = "Passwords do not match.";

        } elseif (password_verify($password, $row['password'])) {
            $error = "New password must be different from the current one.";

        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt   = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $user_id]);
            $message = "Password updated successfully!";
        }
    }

    // ── 3. DELETE ACCOUNT ───────────────────────
    elseif ($action === 'delete_account') {
        $confirm_text = trim($_POST['confirm_delete'] ?? '');

        // Require the user to type "DELETE" as confirmation
        if ($confirm_text !== 'DELETE') {
            $error = "Please type DELETE to confirm account deletion.";

        } else {
            try {
                $pdo->beginTransaction();

                // Delete related data first (adjust table names to your schema)
                $pdo->prepare("DELETE FROM reports WHERE user_id = ?")->execute([$user_id]);

                // Delete user
                $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);

                $pdo->commit();

                // Destroy session and redirect
                session_destroy();
                header("Location: ../index.php?account=deleted");
                exit();

            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = "Could not delete account. Please try again later.";
                // In production, log $e->getMessage() to a file — never display it
            }
        }
    }
}

// ──────────────────────────────────────────────
// Fetch fresh user data after any update
// ──────────────────────────────────────────────
$user = fetchUser($pdo, $user_id);

if (!$user) {
    // Account no longer exists — force logout
    session_destroy();
    header("Location: ../auth/signin.php");
    exit();
}

// Stats (adjust queries to match your schema)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_reports = (int) $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM reports WHERE user_id = ? AND status = 'resolved'");
$stmt->execute([$user_id]);
$resolved_reports = (int) $stmt->fetchColumn();

$initials = strtoupper(mb_substr($user['name'] ?? 'U', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile — SalahHomty</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --teal:        #2bbbad;
    --teal-dark:   #239c90;
    --teal-deep:   #1f7a70;
    --navy:        #1f2a38;
    --navy-light:  #263344;
    --bg:          #f5f7fa;
    --white:       #ffffff;
    --text:        #1a202c;
    --muted:       #6b7280;
    --border:      #e5e7eb;
    --danger:      #ef4444;
    --success:     #10b981;
    --radius:      10px;
    --shadow:      0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
  }

  body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; }

  nav {
    background: var(--navy); height: 60px; display: flex; align-items: center;
    justify-content: space-between; padding: 0 32px; position: sticky; top: 0;
    z-index: 100; box-shadow: 0 2px 8px rgba(0,0,0,.2);
  }
  .nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
  .nav-logo-icon { width: 32px; height: 32px; background: var(--teal); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
  .nav-logo-icon svg { width: 18px; height: 18px; fill: white; }
  .nav-brand-text { font-size: 16px; font-weight: 700; color: white; letter-spacing: -.2px; }
  .nav-brand-text span { color: var(--teal); }
  .nav-links { display: flex; align-items: center; gap: 4px; }
  .nav-links a { color: rgba(255,255,255,.75); text-decoration: none; font-size: 14px; font-weight: 500; padding: 6px 12px; border-radius: 6px; transition: all .15s; }
  .nav-links a:hover { color: white; background: rgba(255,255,255,.08); }
  .nav-links a.active { color: white; background: rgba(43,187,173,.2); }

  .page { max-width: 960px; margin: 0 auto; padding: 40px 24px 80px; display: grid; grid-template-columns: 260px 1fr; gap: 28px; align-items: start; }

  .sidebar { display: flex; flex-direction: column; gap: 16px; }
  .profile-card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); padding: 28px 20px; text-align: center; }
  .avatar { width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--teal), var(--teal-deep)); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; color: white; margin: 0 auto 14px; }
  .profile-name { font-size: 17px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
  .profile-email { font-size: 13px; color: var(--muted); word-break: break-all; }
  .divider { height: 1px; background: var(--border); margin: 18px 0; }
  .stats-row { display: flex; justify-content: center; gap: 24px; }
  .stat { text-align: center; }
  .stat-num { font-size: 20px; font-weight: 700; color: var(--teal); }
  .stat-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: .5px; margin-top: 2px; }

  .nav-menu { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
  .nav-menu a { display: flex; align-items: center; gap: 10px; padding: 13px 18px; font-size: 14px; font-weight: 500; color: var(--muted); text-decoration: none; border-left: 3px solid transparent; transition: all .15s; }
  .nav-menu a:not(:last-child) { border-bottom: 1px solid var(--border); }
  .nav-menu a:hover { color: var(--text); background: var(--bg); }
  .nav-menu a.active { color: var(--teal); border-left-color: var(--teal); background: rgba(43,187,173,.05); }
  .nav-menu a svg { width: 16px; height: 16px; flex-shrink: 0; }
  .nav-menu .logout { color: var(--danger); }
  .nav-menu .logout:hover { background: rgba(239,68,68,.05); }

  .panel { display: flex; flex-direction: column; gap: 20px; }
  .panel-header { margin-bottom: 4px; }
  .panel-title { font-size: 22px; font-weight: 700; color: var(--text); }
  .panel-subtitle { font-size: 14px; color: var(--muted); margin-top: 4px; }

  .card { background: var(--white); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
  .card-head { padding: 18px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; }
  .card-head-icon { width: 32px; height: 32px; border-radius: 8px; background: rgba(43,187,173,.12); display: flex; align-items: center; justify-content: center; }
  .card-head-icon svg { width: 16px; height: 16px; stroke: var(--teal); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
  .card-head-title { font-size: 15px; font-weight: 600; color: var(--text); }
  .card-body { padding: 24px; }

  .alert { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 8px; font-size: 14px; font-weight: 500; margin-bottom: 20px; }
  .alert svg { width: 16px; height: 16px; flex-shrink: 0; }
  .alert-success { background: rgba(16,185,129,.1); color: #065f46; border: 1px solid rgba(16,185,129,.25); }
  .alert-error   { background: rgba(239,68,68,.08); color: #991b1b; border: 1px solid rgba(239,68,68,.2); }

  .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  .form-group { display: flex; flex-direction: column; gap: 6px; }
  .form-group.full { grid-column: 1 / -1; }
  label { font-size: 13px; font-weight: 600; color: var(--text); }
  .label-optional { font-size: 12px; font-weight: 400; color: var(--muted); margin-left: 4px; }
  .input-wrap { position: relative; }
  .input-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--muted); display: flex; }
  .input-icon svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

  input[type="text"], input[type="email"], input[type="password"], input[type="tel"] {
    width: 100%; padding: 10px 12px 10px 38px; font-size: 14px; font-family: 'Inter', sans-serif;
    color: var(--text); background: var(--bg); border: 1.5px solid var(--border); border-radius: 8px;
    outline: none; transition: border-color .15s, box-shadow .15s;
  }
  input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(43,187,173,.15); background: var(--white); }
  input[readonly] { cursor: not-allowed; opacity: .65; }
  .hint { font-size: 12px; color: var(--muted); margin-top: 2px; }

  .strength-bar { height: 4px; border-radius: 2px; background: var(--border); margin-top: 6px; overflow: hidden; }
  .strength-fill { height: 100%; border-radius: 2px; width: 0%; transition: width .3s, background .3s; }
  .strength-label { font-size: 11px; color: var(--muted); margin-top: 4px; text-align: right; }

  .form-actions { display: flex; justify-content: flex-end; gap: 12px; padding-top: 4px; }

  .btn { display: inline-flex; align-items: center; gap: 7px; padding: 10px 22px; font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif; border-radius: 8px; border: none; cursor: pointer; text-decoration: none; transition: all .15s; }
  .btn svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
  .btn-ghost { background: transparent; color: var(--muted); border: 1.5px solid var(--border); }
  .btn-ghost:hover { border-color: #9ca3af; color: var(--text); }
  .btn-primary { background: var(--teal); color: white; box-shadow: 0 2px 8px rgba(43,187,173,.3); }
  .btn-primary:hover { background: var(--teal-dark); }
  .btn-danger { background: transparent; color: var(--danger); border: 1.5px solid var(--danger); white-space: nowrap; flex-shrink: 0; }
  .btn-danger:hover { background: var(--danger); color: white; }

  .danger-zone { background: rgba(239,68,68,.04); border: 1.5px solid rgba(239,68,68,.2); border-radius: var(--radius); padding: 20px 24px; }
  .danger-title { font-size: 14px; font-weight: 600; color: var(--danger); }
  .danger-desc  { font-size: 13px; color: var(--muted); margin-top: 2px; }
  .danger-actions { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-top: 0; }

  /* Modal */
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 200; align-items: center; justify-content: center; }
  .modal-overlay.open { display: flex; }
  .modal { background: var(--white); border-radius: 14px; padding: 32px; width: 420px; max-width: 90vw; box-shadow: 0 20px 60px rgba(0,0,0,.2); }
  .modal-title { font-size: 18px; font-weight: 700; color: var(--danger); margin-bottom: 8px; }
  .modal-body  { font-size: 14px; color: var(--muted); line-height: 1.6; margin-bottom: 20px; }
  .modal-body strong { color: var(--text); }
  .modal-input { width: 100%; padding: 10px 14px; font-size: 14px; font-family: 'Inter', sans-serif; border: 1.5px solid var(--border); border-radius: 8px; outline: none; margin-bottom: 20px; }
  .modal-input:focus { border-color: var(--danger); box-shadow: 0 0 0 3px rgba(239,68,68,.15); }
  .modal-actions { display: flex; gap: 10px; justify-content: flex-end; }

  @media (max-width: 720px) {
    .page { grid-template-columns: 1fr; }
    .form-grid { grid-template-columns: 1fr; }
    .danger-actions { flex-direction: column; align-items: flex-start; }
  }
</style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="../index.php" class="nav-brand">
    <div class="navbar__logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
    </div>
      <svg viewBox="0 0 24 24"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
    </div>
    <span class="nav-brand-text">Salah<span>Homty</span></span>
  </a>
  <div class="nav-links">
    <a href="../index.php">Report a problem</a>
    <a href="signin.php">Sign in</a>
    <a href="history.php">History</a>
    <a href="services.php">Services</a>
    <a href="profile.php" class="active">Profile</a>
    <a href="helppage.php">Help</a>
  </div>
</nav>

<!-- PAGE -->
<div class="page">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <div class="profile-card">
      <div class="avatar"><?= htmlspecialchars($initials) ?></div>
      <div class="profile-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
      <div class="profile-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
      <div class="divider"></div>
      <div class="stats-row">
        <div class="stat">
          <div class="stat-num"><?= $total_reports ?></div>
          <div class="stat-label">Reports</div>
        </div>
        <div class="stat">
          <div class="stat-num"><?= $resolved_reports ?></div>
          <div class="stat-label">Resolved</div>
        </div>
      </div>
    </div>

    <nav class="nav-menu">
      <a href="../index.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Home
      </a>
      <a href="history.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        My Reports
      </a>
      <a href="profile.php" class="active">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile
      </a>
      <a href="helppage.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Help
      </a>
      <a href="../auth/logout.php" class="logout">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout
      </a>
    </nav>
  </aside>

  <!-- MAIN PANEL -->
  <main class="panel">

    <div class="panel-header">
      <h1 class="panel-title">My Profile</h1>
      <p class="panel-subtitle">Manage your personal information and account settings.</p>
    </div>

    <!-- Alerts (shown once for whichever form was submitted) -->
    <?php if ($message): ?>
    <div class="alert alert-success">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="alert alert-error">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <!-- ── PERSONAL INFORMATION ── -->
    <div class="card">
      <div class="card-head">
        <div class="card-head-icon">
          <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <span class="card-head-title">Personal Information</span>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="update_profile">

          <div class="form-grid">
            <div class="form-group">
              <label for="name">Full Name</label>
              <div class="input-wrap">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </span>
                <input type="text" id="name" name="name"
                       value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                       placeholder="Your full name" required>
              </div>
            </div>

            <div class="form-group">
              <label for="phone">Phone Number <span class="label-optional">(optional)</span></label>
              <div class="input-wrap">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13.1 19.79 19.79 0 0 1 1.61 4.55 2 2 0 0 1 3.58 2.36h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.16 6.16l.92-.92a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21.68 17.39z"/></svg>
                </span>
                <input type="tel" id="phone" name="phone"
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                       placeholder="+213 000 000 000">
              </div>
            </div>

            <div class="form-group full">
              <label for="email">Email Address</label>
              <div class="input-wrap">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </span>
                <input type="email" id="email"
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
              </div>
              <span class="hint">Email address cannot be changed. Contact support if needed.</span>
            </div>
          </div>

          <div class="form-actions" style="margin-top:22px;">
            <button type="reset" class="btn btn-ghost">
              <svg viewBox="0 0 24 24"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-4.95"/></svg>
              Reset
            </button>
            <button type="submit" class="btn btn-primary">
              <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── CHANGE PASSWORD ── -->
    <div class="card">
      <div class="card-head">
        <div class="card-head-icon">
          <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <span class="card-head-title">Change Password</span>
      </div>
      <div class="card-body">
        <form method="POST">
          <input type="hidden" name="action" value="update_password">

          <div class="form-grid">
            <div class="form-group full">
              <label for="current_password">Current Password</label>
              <div class="input-wrap">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <input type="password" id="current_password" name="current_password"
                       placeholder="Enter your current password">
              </div>
            </div>

            <div class="form-group full">
              <label for="password">New Password <span class="label-optional">(min. 8 characters)</span></label>
              <div class="input-wrap">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <input type="password" id="password" name="password"
                       placeholder="Enter new password" oninput="checkStrength(this.value)">
              </div>
              <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
              <div class="strength-label" id="strengthLabel"></div>
            </div>

            <div class="form-group full">
              <label for="confirm_password">Confirm New Password</label>
              <div class="input-wrap">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </span>
                <input type="password" id="confirm_password" name="confirm_password"
                       placeholder="Repeat new password">
              </div>
            </div>
          </div>

          <div class="form-actions" style="margin-top:22px;">
            <button type="submit" class="btn btn-primary">
              <svg viewBox="0 0 24 24"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0 3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
              Update Password
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── DANGER ZONE ── -->
    <div class="danger-zone">
      <div class="danger-actions">
        <div>
          <div class="danger-title">Delete Account</div>
          <div class="danger-desc">Permanently remove your account and all your reports. This action cannot be undone.</div>
        </div>
        <button class="btn btn-danger" onclick="document.getElementById('deleteModal').classList.add('open')">
          Delete Account
        </button>
      </div>
    </div>

  </main>
</div>

<!-- ── DELETE ACCOUNT MODAL ── -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal">
    <div class="modal-title">⚠️ Delete Account</div>
    <div class="modal-body">
      This will permanently delete your account, all your reports, and all associated data.<br><br>
      Type <strong>DELETE</strong> below to confirm.
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="delete_account">
      <input type="text" class="modal-input" name="confirm_delete"
             placeholder='Type "DELETE" to confirm' autocomplete="off">
      <div class="modal-actions">
        <button type="button" class="btn btn-ghost"
                onclick="document.getElementById('deleteModal').classList.remove('open')">
          Cancel
        </button>
        <button type="submit" class="btn btn-danger">
          Yes, Delete My Account
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Close modal on overlay click
document.getElementById('deleteModal').addEventListener('click', function(e) {
  if (e.target === this) this.classList.remove('open');
});

// Password strength meter
function checkStrength(val) {
  const fill  = document.getElementById('strengthFill');
  const label = document.getElementById('strengthLabel');
  if (!val) { fill.style.width = '0%'; label.textContent = ''; return; }

  let score = 0;
  if (val.length >= 8)            score++;
  if (/[A-Z]/.test(val))         score++;
  if (/[0-9]/.test(val))         score++;
  if (/[^A-Za-z0-9]/.test(val))  score++;

  const levels = [
    { w: '20%',  c: '#ef4444', t: 'Too weak' },
    { w: '45%',  c: '#f59e0b', t: 'Weak' },
    { w: '70%',  c: '#3b82f6', t: 'Good' },
    { w: '100%', c: '#10b981', t: 'Strong' },
  ];
  const l = levels[score - 1] || levels[0];
  fill.style.width      = l.w;
  fill.style.background = l.c;
  label.textContent     = l.t;
  label.style.color     = l.c;
}

// Client-side: confirm passwords match before submit
document.querySelector('form[action="update_password"]') // not used here;
// Instead, attach to the password form by data attribute
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function(e) {
    const action = this.querySelector('[name="action"]')?.value;
    if (action === 'update_password') {
      const pw  = this.querySelector('#password').value;
      const cpw = this.querySelector('#confirm_password').value;
      if (pw && pw !== cpw) {
        e.preventDefault();
        alert('Passwords do not match.');
      }
    }
  });
});
</script>
</body>
</html>