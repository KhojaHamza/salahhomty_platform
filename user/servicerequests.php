<?php
// user/servicerequests.php
// Page : Mes demandes de services — citoyen connecté
// Fonctionnalités : consulter, annuler (< 24h), évaluer (service clôturé)

if (session_status() === PHP_SESSION_NONE) session_start();

// ── Auth : rediriger si non connecté ──────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php?error=login_required&redirect=servicerequests.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$userId   = (int)$_SESSION['user_id'];
$userName = htmlspecialchars($_SESSION['user_name'] ?? 'Citoyen');

// ══════════════════════════════════════════════════════════════════════════════
// RÉCUPÉRATION INITIALE DES DONNÉES (server-side pour le premier rendu)
// Les mises à jour suivantes passent par actions/service_requests.php (AJAX)
// ══════════════════════════════════════════════════════════════════════════════

// Stats globales
$statsStmt = $pdo->prepare("
    SELECT
        COUNT(*)                        AS total,
        SUM(status = 'pending')         AS pending,
        SUM(status = 'in_progress')     AS in_progress,
        SUM(status = 'done')            AS done,
        SUM(status = 'overdue')         AS overdue,
        SUM(status = 'cancelled')       AS cancelled
    FROM service_requests
    WHERE user_id = ?
");
$statsStmt->execute([$userId]);
$stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
foreach ($stats as $k => $v) $stats[$k] = (int)$v;

// Catégories disponibles pour le filtre
$catStmt = $pdo->prepare("
    SELECT DISTINCT s.category
    FROM service_requests sr
    INNER JOIN services s ON s.id = sr.service_id
    WHERE sr.user_id = ?
    ORDER BY s.category ASC
");
$catStmt->execute([$userId]);
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SalahHomty — My Service Requests</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --teal: #2ec4a0;
      --teal-dark: #24a085;
      --navy: #1a1f2e;
      --bg-page: #f4f5f7;
      --bg-white: #ffffff;
      --bg-surface: #f8f9fb;
      --text-primary: #1a1f2e;
      --text-secondary: #6b7280;
      --text-muted: #9ca3af;
      --border: rgba(0,0,0,0.1);
      --border-light: rgba(0,0,0,0.06);
      --radius-sm: 6px;
      --radius-md: 8px;
      --radius-lg: 12px;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.06);
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
      background: var(--bg-page);
      color: var(--text-primary);
      font-size: 14px;
      line-height: 1.5;
      min-height: 100vh;
    }

    /* ── Navbar ── */
    .nav {
      background: var(--navy);
      height: 52px;
      padding: 0 28px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 50;
    }
    .nav-logo { display: flex; align-items: center; gap: 9px; text-decoration: none; }
    .nav-logo-box {
      background: var(--teal); border-radius: 7px; width: 30px; height: 30px;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 11px; font-weight: 700; letter-spacing: 0.3px;
    }
    .nav-logo-text { color: #fff; font-size: 15px; font-weight: 500; }
    .nav-logo-text span { color: var(--teal); }
    .nav-links { display: flex; align-items: center; gap: 4px; }
    .nav-links a {
      color: #9aabb8; font-size: 13px; padding: 5px 11px;
      text-decoration: none; border-radius: var(--radius-sm);
      transition: color .15s, background .15s;
    }
    .nav-links a:hover { color: #fff; background: rgba(255,255,255,0.08); }
    .nav-links a.active { color: #fff; background: rgba(255,255,255,0.12); }
    .btn-report { background: var(--teal) !important; color: #fff !important; font-weight: 600 !important; }
    .btn-report:hover { background: var(--teal-dark) !important; }

    /* ── Page layout ── */
    .page { max-width: 1100px; margin: 0 auto; padding: 30px 24px 48px; }

    /* ── Tabs ── */
    .tabs { display: flex; gap: 0; border-bottom: 1.5px solid #e2e5ea; margin-bottom: 26px; }
    .tab-btn {
      font-size: 14px; padding: 9px 20px; border: none; background: none;
      cursor: pointer; color: var(--text-secondary); border-bottom: 2.5px solid transparent;
      margin-bottom: -1.5px; font-family: inherit; border-radius: 0;
      transition: color .15s; text-decoration: none; display: inline-block;
    }
    .tab-btn:hover { color: var(--text-primary); }
    .tab-btn.active { color: var(--navy); border-bottom-color: var(--teal); font-weight: 500; }

    /* ── Page header ── */
    .page-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 22px; }
    .page-header h1 { font-size: 26px; font-weight: 800; color: var(--navy); letter-spacing: -0.5px; margin-bottom: 4px; }
    .page-header p { font-size: 13px; color: var(--text-secondary); margin-top: 3px; }
    .page-header p strong { font-weight: 600; color: var(--text-primary); }
    .btn-export {
      display: flex; align-items: center; gap: 7px; font-size: 13px;
      padding: 8px 15px; border: 1px solid #dde1e8; border-radius: var(--radius-md);
      background: var(--bg-white); cursor: pointer; color: var(--text-primary);
      font-family: inherit; transition: border-color .15s, background .15s; white-space: nowrap;
      text-decoration: none;
    }
    .btn-export:hover { border-color: #b0b8c6; background: #fafbfc; }

    /* ── Stat cards ── */
    .stats-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-bottom: 18px; }
    .stat-card {
      background: var(--bg-white); border-radius: var(--radius-md); padding: 14px 16px;
      border-left: 3px solid #ccc; border-top: 1px solid var(--border-light);
      border-right: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light);
    }
    .stat-card.c-navy   { border-left-color: var(--navy); }
    .stat-card.c-blue   { border-left-color: #3b82f6; }
    .stat-card.c-orange { border-left-color: #f97316; }
    .stat-card.c-green  { border-left-color: #22c55e; }
    .stat-card.c-red    { border-left-color: #ef4444; }
    .stat-label { font-size: 10px; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.6px; font-weight: 500; }
    .stat-number { font-size: 28px; font-weight: 600; color: var(--navy); line-height: 1.1; margin: 3px 0 2px; }
    .stat-sub { font-size: 11px; color: var(--text-muted); }

    /* ── Alert banner ── */
    .alert-banner {
      display: flex; align-items: flex-start; gap: 11px; background: #fff5f5;
      border: 1px solid #fca5a5; border-radius: var(--radius-md); padding: 12px 16px;
      margin-bottom: 18px;
    }
    .alert-banner svg { flex-shrink: 0; margin-top: 1px; }
    .alert-banner p { font-size: 13px; color: #7f1d1d; }
    .alert-banner strong { font-weight: 600; }

    /* ── Controls ── */
    .controls { display: flex; gap: 10px; margin-bottom: 14px; }
    .search-wrap { flex: 1; position: relative; }
    .search-wrap svg { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--text-muted); }
    .search-input {
      width: 100%; padding: 9px 14px 9px 34px; border: 1px solid #dde1e8;
      border-radius: var(--radius-md); font-size: 13px; background: var(--bg-white);
      color: var(--text-primary); font-family: inherit; transition: border-color .15s; outline: none;
    }
    .search-input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(46,196,160,0.12); }
    .filter-select {
      padding: 9px 12px; border: 1px solid #dde1e8; border-radius: var(--radius-md);
      font-size: 13px; background: var(--bg-white); color: var(--text-primary);
      font-family: inherit; outline: none; cursor: pointer; transition: border-color .15s;
    }
    .filter-select:focus { border-color: var(--teal); }

    /* ── Table ── */
    .table-wrap { background: var(--bg-white); border-radius: var(--radius-lg); border: 1px solid #e2e5ea; overflow: hidden; }
    .table-head {
      display: grid;
      grid-template-columns: 52px 64px minmax(0,1fr) 130px 108px 100px 170px;
      padding: 10px 18px; border-bottom: 1px solid #e8ebf0; background: var(--bg-surface);
    }
    .th { font-size: 11px; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.5px; }
    .table-body {}
    .table-row {
      display: grid;
      grid-template-columns: 52px 64px minmax(0,1fr) 130px 108px 100px 170px;
      padding: 11px 18px; border-bottom: 1px solid #f0f2f5; align-items: center;
      transition: background .12s;
    }
    .table-row:last-child { border-bottom: none; }
    .table-row:hover { background: #fafbfd; }

    .cell-id { font-size: 13px; font-weight: 600; color: var(--teal); }
    .cell-icon { width: 44px; height: 36px; border-radius: 7px; display: flex; align-items: center; justify-content: center; }
    .cell-title { font-size: 13px; color: var(--text-primary); font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cell-cat { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
    .cell-loc { font-size: 12px; color: var(--text-secondary); }
    .cell-date { font-size: 12px; color: var(--text-secondary); }

    /* ── Badges ── */
    .badge { display: inline-flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 500; padding: 3px 9px; border-radius: 20px; white-space: nowrap; }
    .badge-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .badge-pending    { background: #eff6ff; color: #1d4ed8; }
    .badge-pending    .badge-dot { background: #3b82f6; }
    .badge-in_progress { background: #fff7ed; color: #c2410c; }
    .badge-in_progress .badge-dot { background: #f97316; }
    .badge-done       { background: #f0fdf4; color: #166534; }
    .badge-done       .badge-dot { background: #22c55e; }
    .badge-overdue    { background: #fff1f2; color: #be123c; }
    .badge-overdue    .badge-dot { background: #ef4444; }
    .badge-cancelled  { background: #f1f5f9; color: #475569; }
    .badge-cancelled  .badge-dot { background: #94a3b8; }

    /* ── Action buttons ── */
    .actions { display: flex; gap: 5px; flex-wrap: wrap; }
    .btn {
      font-size: 12px; padding: 5px 10px; border-radius: var(--radius-sm);
      cursor: pointer; font-family: inherit; font-weight: 500; border: 1px solid;
      transition: opacity .15s, background .15s; white-space: nowrap;
    }
    .btn:hover { opacity: 0.85; }
    .btn-view   { border-color: #dde1e8; background: #fff; color: var(--text-primary); }
    .btn-cancel { border-color: #fca5a5; background: #fff5f5; color: #b91c1c; }
    .btn-rate   { border-color: #86efac; background: #f0fdf4; color: #166534; display: flex; align-items: center; gap: 4px; }

    /* ── Table footer ── */
    .table-footer {
      display: flex; justify-content: space-between; align-items: center;
      padding: 13px 18px; border-top: 1px solid #f0f2f5;
    }
    .footer-info { font-size: 12px; color: var(--text-secondary); }
    .page-num {
      width: 30px; height: 30px; border-radius: var(--radius-sm);
      background: var(--teal); color: #fff; font-size: 13px; font-weight: 600;
      display: flex; align-items: center; justify-content: center;
    }

    /* ── Loading / empty ── */
    .loading-row { padding: 40px 20px; text-align: center; color: var(--text-muted); font-size: 13px; }
    .loading-spinner {
      display: inline-block; width: 20px; height: 20px; border: 2px solid #e5e7eb;
      border-top-color: var(--teal); border-radius: 50%; animation: spin .7s linear infinite;
      margin-bottom: 8px;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .empty-state { padding: 40px 20px; text-align: center; color: var(--text-secondary); font-size: 13px; }
    .empty-state svg { margin-bottom: 10px; color: var(--text-muted); }

    /* ── Modal overlay ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.48);
      z-index: 200; align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: var(--bg-white); border-radius: var(--radius-lg); padding: 26px;
      width: 380px; max-width: calc(100vw - 32px); border: 1px solid #e2e5ea;
      animation: modal-in .18s ease;
    }
    @keyframes modal-in { from { opacity:0; transform:translateY(-10px) scale(.97); } to { opacity:1; transform:none; } }
    .modal h2 { font-size: 17px; font-weight: 600; color: var(--navy); margin-bottom: 6px; }
    .modal-sub { font-size: 13px; color: var(--text-secondary); margin-bottom: 20px; }

    /* Rating modal */
    .svc-pill {
      display: flex; align-items: center; gap: 9px; background: var(--bg-surface);
      border: 1px solid #e8ebf0; border-radius: var(--radius-md); padding: 9px 13px;
      font-size: 13px; font-weight: 500; color: var(--navy); margin-bottom: 18px;
    }
    .stars-row { display: flex; gap: 6px; margin-bottom: 18px; }
    .star-btn {
      font-size: 28px; cursor: pointer; color: #d1d5db; background: none;
      border: none; padding: 0; line-height: 1; transition: color .1s, transform .1s;
    }
    .star-btn:hover, .star-btn.lit { color: #f59e0b; }
    .star-btn:hover { transform: scale(1.15); }
    .modal-textarea {
      width: 100%; height: 82px; padding: 10px 13px; border: 1px solid #dde1e8;
      border-radius: var(--radius-md); font-size: 13px; font-family: inherit;
      resize: none; color: var(--text-primary); background: var(--bg-white); outline: none;
      transition: border-color .15s;
    }
    .modal-textarea:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(46,196,160,0.12); }
    .modal-actions { display: flex; gap: 9px; justify-content: flex-end; margin-top: 16px; }
    .btn-modal-close {
      font-size: 13px; padding: 8px 17px; border: 1px solid #dde1e8; border-radius: var(--radius-md);
      background: none; cursor: pointer; color: var(--text-secondary); font-family: inherit;
      font-weight: 500; transition: background .15s;
    }
    .btn-modal-close:hover { background: var(--bg-surface); }
    .btn-modal-submit {
      font-size: 13px; padding: 8px 20px; border: none; border-radius: var(--radius-md);
      background: var(--teal); cursor: pointer; color: #fff; font-family: inherit;
      font-weight: 500; transition: background .15s;
    }
    .btn-modal-submit:hover { background: var(--teal-dark); }
    .btn-modal-submit:disabled { opacity: .55; cursor: not-allowed; }

    /* Cancel modal */
    .cancel-warn {
      background: #fff5f5; border: 1px solid #fca5a5; border-radius: var(--radius-md);
      padding: 11px 14px; font-size: 12px; color: #7f1d1d; margin-bottom: 18px;
      display: flex; gap: 9px; align-items: flex-start;
    }
    .cancel-warn svg { flex-shrink: 0; margin-top: 1px; }
    .btn-confirm-cancel {
      font-size: 13px; padding: 8px 20px; border: none; border-radius: var(--radius-md);
      background: #ef4444; cursor: pointer; color: #fff; font-family: inherit;
      font-weight: 500; transition: background .15s;
    }
    .btn-confirm-cancel:hover { background: #dc2626; }
    .btn-confirm-cancel:disabled { opacity: .55; cursor: not-allowed; }

    /* Success modal */
    .success-icon {
      width: 52px; height: 52px; background: #f0fdf4; border-radius: 50%;
      display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;
    }
    .modal-centered { text-align: center; }
    .modal-centered h2 { margin-bottom: 8px; }
    .modal-centered .modal-sub { margin-bottom: 22px; }
    .btn-full {
      width: 100%; font-size: 14px; padding: 10px; border: none; border-radius: var(--radius-md);
      background: var(--teal); cursor: pointer; color: #fff; font-family: inherit;
      font-weight: 500; transition: background .15s;
    }
    .btn-full:hover { background: var(--teal-dark); }

    /* Detail modal */
    .detail-header { display: flex; align-items: center; gap: 13px; margin-bottom: 20px; }
    .detail-icon { width: 48px; height: 42px; border-radius: 9px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .detail-rows { border-top: 1px solid #f0f2f5; padding-top: 14px; }
    .detail-row {
      display: flex; justify-content: space-between; align-items: flex-start;
      padding: 7px 0; border-bottom: 1px solid #f8f9fb; font-size: 13px;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-lbl { color: var(--text-secondary); flex-shrink: 0; }
    .detail-val { color: var(--text-primary); font-weight: 500; text-align: right; max-width: 220px; word-break: break-word; }

    /* Admin message highlight */
    .admin-msg-box {
      background: #f0fdf4; border: 1px solid #86efac; border-radius: var(--radius-md);
      padding: 10px 13px; font-size: 12px; color: #166534; margin-top: 10px;
    }
    .admin-msg-box strong { display: block; margin-bottom: 3px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.4px; }

    /* Toast notification */
    .toast {
      position: fixed; bottom: 24px; right: 24px; z-index: 300;
      padding: 12px 18px; border-radius: var(--radius-md); font-size: 13px;
      font-weight: 500; color: #fff; background: var(--navy);
      box-shadow: 0 4px 16px rgba(0,0,0,0.2); transform: translateY(10px);
      opacity: 0; pointer-events: none; transition: all .25s;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.success { background: #166534; }
    .toast.error   { background: #b91c1c; }

    /* Responsive */
    @media (max-width: 860px) {
      .stats-grid { grid-template-columns: repeat(3, 1fr); }
      .table-head, .table-row { grid-template-columns: 44px minmax(0,1fr) 90px 80px 150px; }
      .table-head .th:nth-child(2), .table-row .cell-icon { display: none; }
      .table-head .th:nth-child(4), .table-row .cell-loc { display: none; }
    }
    @media (max-width: 600px) {
      .page { padding: 20px 14px 40px; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .controls { flex-wrap: wrap; }
      .filter-select { flex: 1; }
      .nav-links { display: none; }
    }
    a { text-decoration: none; }
  </style>
</head>
<body>

<!-- ── Navbar ── -->
<nav class="nav">
  <a href="../index.php" class="nav-logo">
    <div class="nav-logo-box">SH</div>
    <span class="nav-logo-text">Salah<span>Homty</span></span>
  </a>
  <div class="nav-links">
    <a href="../index.php" class="btn-report">Report a problem</a>
    <a href="history.php">History</a>
    <a href="helppage.php">Help</a>
    <a href="services.php">Services</a>
    <a href="profil.php">Profile</a>
    <a href="../auth/logout.php" style="color:#ef4444">Sign out</a>
  </div>
</nav>

<!-- ── Main page ── -->
<main class="page">

  <!-- Tabs -->
  <div class="tabs">
    <a href="history.php" class="tab-btn">My reports</a>
    <a href="servicerequests.php" class="tab-btn active">My service requests</a>
  </div>

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1>My service requests</h1>
      <p>All service requests submitted by <strong><?= $userName ?></strong> and their current status.</p>
    </div>
    <a href="../actions/service_requests.php?action=export" class="btn-export">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
      </svg>
      Export CSV
    </a>
  </div>

  <!-- Stat cards -->
  <div class="stats-grid">
    <div class="stat-card c-navy">
      <div class="stat-label">Total</div>
      <div class="stat-number" id="cnt-all"><?= $stats['total'] ?></div>
      <div class="stat-sub">requests</div>
    </div>
    <div class="stat-card c-blue">
      <div class="stat-label">Pending</div>
      <div class="stat-number" id="cnt-pending"><?= $stats['pending'] ?></div>
      <div class="stat-sub">awaiting review</div>
    </div>
    <div class="stat-card c-orange">
      <div class="stat-label">In progress</div>
      <div class="stat-number" id="cnt-progress"><?= $stats['in_progress'] ?></div>
      <div class="stat-sub">being processed</div>
    </div>
    <div class="stat-card c-green">
      <div class="stat-label">Closed</div>
      <div class="stat-number" id="cnt-done"><?= $stats['done'] ?></div>
      <div class="stat-sub">completed</div>
    </div>
    <div class="stat-card c-red">
      <div class="stat-label">Overdue</div>
      <div class="stat-number" id="cnt-overdue"><?= $stats['overdue'] ?></div>
      <div class="stat-sub">deadline exceeded</div>
    </div>
  </div>

  <!-- Alert banner (overdue) -->
  <div class="alert-banner" id="overdue-banner" style="display:none">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
      <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <p id="overdue-msg"><strong>Some requests have exceeded the processing deadline.</strong> Contact your local authority if you need an urgent follow-up.</p>
  </div>

  <!-- Controls -->
  <div class="controls">
    <div class="search-wrap">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
      </svg>
      <input class="search-input" id="searchInput" type="text"
        placeholder="Search by ID, service name..." oninput="debounceLoad()" />
    </div>
    <select class="filter-select" id="statusFilter" onchange="loadRequests()">
      <option value="">All statuses</option>
      <option value="pending">Pending</option>
      <option value="in_progress">In progress</option>
      <option value="done">Closed</option>
      <option value="overdue">Overdue</option>
      <option value="cancelled">Cancelled</option>
    </select>
    <select class="filter-select" id="catFilter" onchange="loadRequests()">
      <option value="">All categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <!-- Table -->
  <div class="table-wrap">
    <div class="table-head">
      <div class="th">ID</div>
      <div class="th">Icon</div>
      <div class="th">Service</div>
      <div class="th">Region</div>
      <div class="th">Date</div>
      <div class="th">Status</div>
      <div class="th">Actions</div>
    </div>
    <div class="table-body" id="tableBody">
      <div class="loading-row">
        <div class="loading-spinner"></div><br>Loading your requests…
      </div>
    </div>
    <div class="table-footer">
      <span class="footer-info" id="footerInfo">Loading…</span>
      <div class="page-num">1</div>
    </div>
  </div>

</main>

<!-- ── Rating Modal ── -->
<div class="modal-overlay" id="rateModal">
  <div class="modal">
    <h2>Rate this service</h2>
    <p class="modal-sub">Share your experience to help improve municipal services.</p>
    <div class="svc-pill">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2ec4a0" stroke-width="1.8">
        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
      </svg>
      <span id="rateSvcName">Service name</span>
    </div>
    <div class="stars-row" id="starsRow">
      <button class="star-btn" data-val="1" onclick="rate(1)">&#9733;</button>
      <button class="star-btn" data-val="2" onclick="rate(2)">&#9733;</button>
      <button class="star-btn" data-val="3" onclick="rate(3)">&#9733;</button>
      <button class="star-btn" data-val="4" onclick="rate(4)">&#9733;</button>
      <button class="star-btn" data-val="5" onclick="rate(5)">&#9733;</button>
    </div>
    <p id="rateError" style="font-size:12px;color:#b91c1c;margin-bottom:10px;display:none">Please select a rating before submitting.</p>
    <textarea class="modal-textarea" id="rateComment"
      placeholder="Leave a comment (optional) — what went well, what could be improved?"></textarea>
    <div class="modal-actions">
      <button class="btn-modal-close" onclick="closeModal('rateModal')">Cancel</button>
      <button class="btn-modal-submit" id="rateSubmitBtn" onclick="submitRating()">Submit rating</button>
    </div>
  </div>
</div>

<!-- ── Cancel Modal ── -->
<div class="modal-overlay" id="cancelModal">
  <div class="modal">
    <h2>Cancel this request?</h2>
    <p class="modal-sub">You are about to cancel <strong id="cancelSvcName">this service request</strong>.</p>
    <div class="cancel-warn">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
        <line x1="12" y1="16" x2="12.01" y2="16"/>
      </svg>
      <span>Cancellation is only available within <strong>24 hours</strong> of submission. After this period the request cannot be cancelled.</span>
    </div>
    <div class="modal-actions">
      <button class="btn-modal-close" onclick="closeModal('cancelModal')">Keep request</button>
      <button class="btn-confirm-cancel" id="confirmCancelBtn" onclick="confirmCancel()">Yes, cancel it</button>
    </div>
  </div>
</div>

<!-- ── Success Modal ── -->
<div class="modal-overlay" id="successModal">
  <div class="modal modal-centered">
    <div class="success-icon">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2.5">
        <path d="M20 6L9 17l-5-5"/>
      </svg>
    </div>
    <h2 id="successTitle">Done!</h2>
    <p class="modal-sub" id="successMsg">Operation completed successfully.</p>
    <button class="btn-full" onclick="closeModal('successModal')">Done</button>
  </div>
</div>

<!-- ── Detail Modal ── -->
<div class="modal-overlay" id="detailModal">
  <div class="modal" style="width:430px">
    <h2 id="detailTitle">Request details</h2>
    <div class="detail-header" style="margin-top:14px">
      <div class="detail-icon" id="detailIcon"></div>
      <div>
        <div style="font-size:14px;font-weight:600;color:var(--navy)" id="detailSvcTitle"></div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:3px" id="detailSvcCat"></div>
      </div>
    </div>
    <div class="detail-rows" id="detailRows"></div>
    <div id="detailAdminMsg"></div>
    <div class="modal-actions" style="margin-top:20px">
      <button class="btn-modal-close" onclick="closeModal('detailModal')">Close</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
// ═══════════════════════════════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════════════════════════════
let allRequests      = [];     // données brutes du dernier fetch
let currentRateId    = null;   // request_id en cours d'évaluation
let currentCancelId  = null;   // request_id en cours d'annulation
let currentRating    = 0;      // étoiles sélectionnées
let debounceTimer    = null;

// ═══════════════════════════════════════════════════════════════════════════
// CATÉGORIE → couleurs + icônes SVG
// ═══════════════════════════════════════════════════════════════════════════
const CAT_CONFIG = {
  Water:          { bg: '#e0f2fe', stroke: '#0ea5e9' },
  Environment:    { bg: '#d1fae5', stroke: '#059669' },
  Lighting:       { bg: '#fef9c3', stroke: '#ca8a04' },
  Infrastructure: { bg: '#fff7ed', stroke: '#f97316' },
  Waste:          { bg: '#f3e8ff', stroke: '#8b5cf6' },
  Administrative: { bg: '#eff6ff', stroke: '#3b82f6' },
  Social:         { bg: '#fdf2f8', stroke: '#ec4899' },
};

function catConfig(cat) {
  return CAT_CONFIG[cat] || { bg: '#f1f5f9', stroke: '#64748b' };
}

function catIcon(cat, stroke, size = 18) {
  const s = `width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${stroke}" stroke-width="1.8"`;
  const icons = {
    Water:          `<svg ${s}><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>`,
    Environment:    `<svg ${s}><path d="M17 8C8 10 5.9 16.17 3.82 19.99M22.35 4.65A12.47 12.47 0 0 1 17 8"/><path d="M2 9c5-2 5.5-7 11-7 1.7 0 3 .5 4 1"/></svg>`,
    Lighting:       `<svg ${s}><line x1="12" y1="1" x2="12" y2="3"/><circle cx="12" cy="13" r="3"/><line x1="12" y1="16" x2="12" y2="21"/><line x1="9" y1="21" x2="15" y2="21"/></svg>`,
    Infrastructure: `<svg ${s}><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>`,
    Waste:          `<svg ${s}><path d="M3 6h18l-1.5 12a2 2 0 01-2 1.8H6.5a2 2 0 01-2-1.8L3 6z"/><path d="M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2"/></svg>`,
    Administrative: `<svg ${s}><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M8 6V5a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>`,
    Social:         `<svg ${s}><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>`,
  };
  return icons[cat] || `<svg ${s}><circle cx="12" cy="12" r="9"/></svg>`;
}

// ═══════════════════════════════════════════════════════════════════════════
// BADGE HTML
// ═══════════════════════════════════════════════════════════════════════════
function badge(status) {
  const map = {
    pending:     ['badge-pending',     'Pending'],
    in_progress: ['badge-in_progress', 'In progress'],
    done:        ['badge-done',        'Closed'],
    overdue:     ['badge-overdue',     'Overdue'],
    cancelled:   ['badge-cancelled',   'Cancelled'],
  };
  const [cls, label] = map[status] || ['badge-cancelled', status];
  return `<span class="badge ${cls}"><span class="badge-dot"></span>${label}</span>`;
}

// ═══════════════════════════════════════════════════════════════════════════
// CHARGEMENT AJAX
// ═══════════════════════════════════════════════════════════════════════════
async function loadRequests() {
  showTableLoading();

  const search   = document.getElementById('searchInput').value.trim();
  const status   = document.getElementById('statusFilter').value;
  const category = document.getElementById('catFilter').value;

  const params = new URLSearchParams({ action: 'list', search, status, category });

  try {
    const res  = await fetch(`../actions/service_requests.php?${params}`);
    const data = await res.json();

    if (!data.success) {
      showTableError(data.message || 'Failed to load requests.');
      return;
    }

    allRequests = data.requests || [];
    updateStats(data.stats || {});
    renderRows(allRequests);

  } catch (err) {
    showTableError('Network error. Please refresh the page.');
  }
}

function debounceLoad() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(loadRequests, 350);
}

// ═══════════════════════════════════════════════════════════════════════════
// RENDER
// ═══════════════════════════════════════════════════════════════════════════
function renderRows(requests) {
  const body = document.getElementById('tableBody');

  if (!requests.length) {
    body.innerHTML = `<div class="empty-state">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
      </svg>
      <p>No requests match your filters.</p>
    </div>`;
    document.getElementById('footerInfo').textContent = 'No results';
    return;
  }

  body.innerHTML = requests.map(r => {
    const cfg  = catConfig(r.category);
    const icon = catIcon(r.category, cfg.stroke);
    const loc  = r.region || '—';

    const actions = [];
    actions.push(`<button class="btn btn-view" onclick="openDetail(${r.id})">View</button>`);

    if (r.can_cancel) {
      actions.push(`<button class="btn btn-cancel" onclick="openCancel(${r.id},'${esc(r.service_name)}')">Cancel</button>`);
    }
    if (r.can_rate) {
      actions.push(`<button class="btn btn-rate" onclick="openRate(${r.id},'${esc(r.service_name)}')">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="#f59e0b" stroke="none">
          <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
        </svg>Rate</button>`);
    }
    if (r.already_rated) {
      actions.push(`<span style="font-size:11px;color:#f59e0b;">★ Rated</span>`);
    }

    return `<div class="table-row" data-id="${r.id}">
      <div class="cell-id">#${r.id}</div>
      <div class="cell-icon" style="background:${cfg.bg}">${icon}</div>
      <div>
        <div class="cell-title">${esc(r.service_name)}</div>
        <div class="cell-cat">${esc(r.category)}</div>
      </div>
      <div class="cell-loc">${esc(loc)}</div>
      <div class="cell-date">${esc(r.created_at_formatted)}</div>
      <div>${badge(r.status)}</div>
      <div class="actions">${actions.join('')}</div>
    </div>`;
  }).join('');

  document.getElementById('footerInfo').textContent =
    `Showing ${requests.length} request${requests.length !== 1 ? 's' : ''}`;
}

function showTableLoading() {
  document.getElementById('tableBody').innerHTML =
    `<div class="loading-row"><div class="loading-spinner"></div><br>Loading…</div>`;
  document.getElementById('footerInfo').textContent = 'Loading…';
}

function showTableError(msg) {
  document.getElementById('tableBody').innerHTML =
    `<div class="empty-state" style="color:#b91c1c">${esc(msg)}</div>`;
}

// ═══════════════════════════════════════════════════════════════════════════
// STATS
// ═══════════════════════════════════════════════════════════════════════════
function updateStats(s) {
  document.getElementById('cnt-all').textContent      = s.total      ?? 0;
  document.getElementById('cnt-pending').textContent  = s.pending    ?? 0;
  document.getElementById('cnt-progress').textContent = s.in_progress ?? 0;
  document.getElementById('cnt-done').textContent     = s.done       ?? 0;
  document.getElementById('cnt-overdue').textContent  = s.overdue    ?? 0;

  const overdue = s.overdue ?? 0;
  const banner  = document.getElementById('overdue-banner');
  banner.style.display = overdue > 0 ? 'flex' : 'none';
  if (overdue > 0) {
    document.getElementById('overdue-msg').innerHTML =
      `<strong>${overdue} request${overdue > 1 ? 's have' : ' has'} exceeded the processing deadline.</strong> Contact your local authority if you need an urgent follow-up.`;
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// MODALS — helpers
// ═══════════════════════════════════════════════════════════════════════════
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => { if (e.target === overlay) overlay.classList.remove('open'); });
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
});

// ═══════════════════════════════════════════════════════════════════════════
// DETAIL MODAL
// ═══════════════════════════════════════════════════════════════════════════
function openDetail(id) {
  const r = allRequests.find(x => x.id == id);
  if (!r) return;

  const cfg = catConfig(r.category);

  document.getElementById('detailTitle').textContent    = `Request #${r.id}`;
  document.getElementById('detailSvcTitle').textContent = r.service_name;
  document.getElementById('detailSvcCat').textContent   = r.category;

  const iconEl = document.getElementById('detailIcon');
  iconEl.style.background = cfg.bg;
  iconEl.innerHTML        = catIcon(r.category, cfg.stroke, 20);

  const rows = [
    ['Reference',      r.reference],
    ['Date submitted', r.created_at_formatted],
    ['Region',         r.region || '—'],
    ['Status',         badge(r.status)],
    ['Category',       r.category],
    ['Hours (availability)', `${r.availability_start || '—'} – ${r.availability_end || '—'}`],
  ];

  if (r.request_message) {
    rows.push(['Your message', esc(r.request_message)]);
  }

  if (r.already_rated && r.rating_score) {
    const stars = '★'.repeat(r.rating_score) + '☆'.repeat(5 - r.rating_score);
    rows.push(['Your rating', `<span style="color:#f59e0b">${stars}</span>`]);
  }

  document.getElementById('detailRows').innerHTML = rows.map(([lbl, val]) =>
    `<div class="detail-row">
      <span class="detail-lbl">${lbl}</span>
      <span class="detail-val">${val}</span>
    </div>`
  ).join('');

  // Message de l'admin
  const adminBox = document.getElementById('detailAdminMsg');
  if (r.admin_message) {
    adminBox.innerHTML = `<div class="admin-msg-box">
      <strong>📋 Message from the municipality</strong>
      ${esc(r.admin_message)}
    </div>`;
  } else {
    adminBox.innerHTML = '';
  }

  openModal('detailModal');
}

// ═══════════════════════════════════════════════════════════════════════════
// RATING MODAL
// ═══════════════════════════════════════════════════════════════════════════
function openRate(requestId, serviceName) {
  currentRateId  = requestId;
  currentRating  = 0;
  document.getElementById('rateSvcName').textContent = serviceName;
  document.querySelectorAll('.star-btn').forEach(s => s.classList.remove('lit'));
  document.getElementById('rateComment').value = '';
  document.getElementById('rateError').style.display = 'none';
  openModal('rateModal');
}

function rate(n) {
  currentRating = n;
  document.querySelectorAll('.star-btn').forEach((s, i) => s.classList.toggle('lit', i < n));
  document.getElementById('rateError').style.display = 'none';
}

async function submitRating() {
  if (currentRating < 1) {
    document.getElementById('rateError').style.display = 'block';
    return;
  }

  const btn = document.getElementById('rateSubmitBtn');
  btn.disabled    = true;
  btn.textContent = 'Submitting…';

  const body = new FormData();
  body.append('action',     'rate');
  body.append('request_id', currentRateId);
  body.append('score',      currentRating);
  body.append('comment',    document.getElementById('rateComment').value.trim());

  try {
    const res  = await fetch('../actions/service_requests.php', { method: 'POST', body });
    const data = await res.json();

    if (data.success) {
      closeModal('rateModal');
      showSuccess('Rating submitted!', data.message);
      await loadRequests(); // rafraîchir
    } else {
      showToast(data.message || 'Error submitting rating.', 'error');
    }
  } catch {
    showToast('Network error. Please try again.', 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Submit rating';
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// CANCEL MODAL
// ═══════════════════════════════════════════════════════════════════════════
function openCancel(requestId, serviceName) {
  currentCancelId = requestId;
  document.getElementById('cancelSvcName').textContent = `"${serviceName}"`;
  openModal('cancelModal');
}

async function confirmCancel() {
  const btn = document.getElementById('confirmCancelBtn');
  btn.disabled    = true;
  btn.textContent = 'Cancelling…';

  const body = new FormData();
  body.append('action',     'cancel');
  body.append('request_id', currentCancelId);

  try {
    const res  = await fetch('../actions/service_requests.php', { method: 'POST', body });
    const data = await res.json();

    if (data.success) {
      closeModal('cancelModal');
      showSuccess('Request cancelled', data.message);
      await loadRequests(); // rafraîchir
    } else {
      showToast(data.message || 'Could not cancel request.', 'error');
    }
  } catch {
    showToast('Network error. Please try again.', 'error');
  } finally {
    btn.disabled    = false;
    btn.textContent = 'Yes, cancel it';
  }
}

// ═══════════════════════════════════════════════════════════════════════════
// SUCCESS MODAL
// ═══════════════════════════════════════════════════════════════════════════
function showSuccess(title, msg) {
  document.getElementById('successTitle').textContent = title;
  document.getElementById('successMsg').textContent   = msg;
  openModal('successModal');
}

// ═══════════════════════════════════════════════════════════════════════════
// TOAST
// ═══════════════════════════════════════════════════════════════════════════
let toastTimer;
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = `toast show ${type}`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 4000);
}

// ═══════════════════════════════════════════════════════════════════════════
// HELPERS
// ═══════════════════════════════════════════════════════════════════════════
function esc(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g, '&amp;').replace(/</g, '&lt;')
    .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// ═══════════════════════════════════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════════════════════════════════
loadRequests();
</script>
</body>
</html>