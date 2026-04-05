<?php
//agent/index.php
session_start();

// Vérifier authentification
if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if (!in_array($_SESSION['user_role'], ['agent', 'admin'], true)) {
    header('Location: ../auth/login.php?error=access_denied');
    exit;
}

// Construire $user depuis la session
$user = [
    'id'         => $_SESSION['user_id'],
    'name'       => $_SESSION['user_name'],
    'email'      => $_SESSION['user_email'],
    'role'       => $_SESSION['user_role'],
    'phone'      => $_SESSION['user_phone'] ?? '',
    'created_at' => $_SESSION['user_created_at'] ?? date('Y-m-d'),
];
$init = implode('', array_map(
    fn($w) => mb_strtoupper(mb_substr($w, 0, 1)),
    array_slice(explode(' ', trim($user['name'])), 0, 2)
));

function h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Agent Portal — MuniPortal</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
  :root{
    --bg:#f0f2f5;--sidebar:#111827;--sidebar-hover:#1f2937;
    --accent:#14b8a6;--accent-dark:#0d9488;--accent-light:rgba(20,184,166,.12);
    --white:#fff;--border:#e5e7eb;--text:#111827;--muted:#6b7280;--muted2:#9ca3af;
    --green:#10b981;--yellow:#f59e0b;--red:#ef4444;--blue:#3b82f6;
    --r:12px;--sw:224px;
  }
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;font-size:14px}

  /* ─ SIDEBAR ─────────────────────────────────────────── */
  .sidebar{width:var(--sw);min-height:100vh;background:var(--sidebar);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100}
  .sb-logo{padding:20px 18px 16px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.07)}
  .logo-icon{width:36px;height:36px;background:var(--accent);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
  .logo-text{font-size:15px;font-weight:700;color:#fff}
  .logo-sub{font-size:10px;color:rgba(255,255,255,.4);letter-spacing:.3px}
  .nav{flex:1;padding:10px;overflow-y:auto}
  .nav-section{font-size:9px;color:rgba(255,255,255,.3);letter-spacing:1.5px;font-weight:600;padding:14px 8px 5px;text-transform:uppercase}
  .nav-item{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:8px;font-size:13px;color:rgba(255,255,255,.55);cursor:pointer;transition:all .15s;margin-bottom:2px;font-weight:500}
  .nav-item:hover{background:var(--sidebar-hover);color:rgba(255,255,255,.9)}
  .nav-item.active{background:var(--accent);color:#fff}
  .nav-icon{font-size:14px;width:16px;text-align:center;flex-shrink:0}
  .nav-badge{margin-left:auto;font-size:10px;font-weight:700;padding:1px 7px;border-radius:99px;background:rgba(255,255,255,.15);color:#fff;min-width:22px;text-align:center}
  .nav-item.active .nav-badge{background:rgba(0,0,0,.2)}
  .agent-card-sb{margin:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:10px;display:flex;align-items:center;gap:9px;cursor:pointer;transition:background .2s}
  .agent-card-sb:hover{background:rgba(255,255,255,.09)}
  .avatar-sm{width:34px;height:34px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0}
  .agent-name-sb{font-size:12px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .agent-role-sb{font-size:10px;color:rgba(255,255,255,.4)}
  .status-dot{width:6px;height:6px;border-radius:50%;background:var(--green);box-shadow:0 0 5px var(--green);flex-shrink:0}
  .sb-footer{padding:10px;border-top:1px solid rgba(255,255,255,.07)}
  .logout-btn{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:8px;font-size:12px;color:rgba(255,255,255,.5);cursor:pointer;background:none;border:none;width:100%;transition:all .15s;font-family:'Poppins',sans-serif;font-weight:500}
  .logout-btn:hover{background:rgba(239,68,68,.15);color:#f87171}

  /* ─ MAIN ─────────────────────────────────────────────── */
  .main{margin-left:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh}
  .topbar{height:58px;background:var(--white);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;padding:0 24px;position:sticky;top:0;z-index:90}
  .topbar-title{font-size:18px;font-weight:700;flex:1;color:var(--text)}
  .search-box{display:flex;align-items:center;gap:8px;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:0 12px;height:36px;width:220px}
  .search-box input{background:none;border:none;outline:none;color:var(--text);font-family:'Poppins',sans-serif;font-size:12px;flex:1}
  .search-box input::placeholder{color:var(--muted2)}
  .top-btn{height:36px;padding:0 16px;border-radius:8px;background:var(--accent);color:#fff;border:none;font-family:'Poppins',sans-serif;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s;white-space:nowrap}
  .top-btn:hover{background:var(--accent-dark)}

  /* ─ CONTENT / PAGES ─────────────────────────────────── */
  .content{padding:24px;flex:1;}
  @keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
  .page{display:none}
  .page.active{display:block}

  /* ─ STATS ────────────────────────────────────────────── */
  .stats-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px}
  .stat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:18px 20px;position:relative;overflow:hidden;cursor:pointer;transition:box-shadow .2s,transform .15s}
  .stat-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);transform:translateY(-1px)}
  .stat-card::after{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--r) var(--r) 0 0}
  .stat-card.s-default::after{background:#d1d5db}
  .stat-card.s-blue::after{background:var(--blue)}
  .stat-card.s-yellow::after{background:var(--yellow)}
  .stat-card.s-green::after{background:var(--green)}
  .stat-card.s-red::after{background:var(--red)}
  .stat-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;font-weight:600;margin-bottom:8px}
  .stat-value{font-size:32px;font-weight:800;line-height:1;color:var(--text);margin-bottom:4px}
  .stat-sub{font-size:11px;color:var(--muted2)}

  /* ─ PANELS ───────────────────────────────────────────── */
  .grid2{display:grid;grid-template-columns:1fr 300px;gap:18px;margin-bottom:18px}
  .panel{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden}
  .panel-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
  .panel-title{font-size:15px;font-weight:700;flex:1}
  .panel-link{font-size:12px;color:var(--accent);font-weight:600;cursor:pointer}
  .panel-link:hover{color:var(--accent-dark)}

  /* ─ FILTER TABS ──────────────────────────────────────── */
  .filter-tabs{display:flex;gap:4px;padding:12px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap}
  .ftab{font-size:12px;padding:5px 14px;border-radius:99px;cursor:pointer;transition:all .15s;border:1px solid transparent;color:var(--muted);font-weight:500}
  .ftab:hover{color:var(--text);background:var(--bg)}
  .ftab.active{background:var(--accent-light);color:var(--accent);border-color:rgba(20,184,166,.25);font-weight:600}

  /* ─ TABLE ────────────────────────────────────────────── */
  .table-wrap{overflow-x:auto}
  table{width:100%;border-collapse:collapse}
  thead th{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;font-weight:600;padding:10px 16px;text-align:left;border-bottom:1px solid var(--border);background:#fafafa;white-space:nowrap}
  tbody tr{border-bottom:1px solid var(--border);transition:background .12s;cursor:pointer}
  tbody tr:last-child{border-bottom:none}
  tbody tr:hover{background:#f9fafb}
  tbody td{padding:11px 16px;font-size:13px;vertical-align:middle}
  .c-id{font-size:12px;color:var(--accent);font-weight:600}
  .c-title{font-weight:500}
  .c-sub{font-size:11px;color:var(--muted);margin-top:2px}

  /* ─ BADGES ───────────────────────────────────────────── */
  .st-badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:4px 10px;border-radius:99px;white-space:nowrap}
  .st-badge .dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
  .st-open{background:#f0f9ff;color:#0369a1}.st-open .dot{background:#0ea5e9}
  .st-progress{background:#eff6ff;color:#1d4ed8}.st-progress .dot{background:var(--blue);animation:pulse 1.5s infinite}
  .st-resolved{background:#f0fdf4;color:#065f46}.st-resolved .dot{background:var(--green)}
  @keyframes pulse{0%,100%{opacity:1}50%{opacity:.35}}

  /* ─ ACTION BUTTONS ───────────────────────────────────── */
  .act-btns{display:flex;gap:5px}
  .act-btn{height:28px;padding:0 10px;border-radius:6px;border:1px solid var(--border);background:var(--white);color:var(--muted);cursor:pointer;display:flex;align-items:center;gap:4px;font-size:11px;font-weight:600;transition:all .12s;font-family:'Poppins',sans-serif;white-space:nowrap}
  .act-btn:hover{background:var(--accent-light);color:var(--accent);border-color:rgba(20,184,166,.3)}

  /* ─ PAGINATION ───────────────────────────────────────── */
  .pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)}
  .page-btns{display:flex;gap:5px}
  .page-btn{width:30px;height:30px;border-radius:6px;background:var(--white);border:1px solid var(--border);color:var(--muted);cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:all .12s;font-family:'Poppins',sans-serif}
  .page-btn:hover,.page-btn.active-page{background:var(--accent);color:#fff;border-color:var(--accent)}
  .page-btn:disabled{opacity:.4;cursor:default}

  /* ─ WORKLOAD ─────────────────────────────────────────── */
  .workload-item{display:flex;align-items:center;gap:10px;padding:12px 18px;border-bottom:1px solid var(--border)}
  .workload-item:last-child{border-bottom:none}
  .wl-avatar{width:34px;height:34px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;color:#fff;flex-shrink:0}
  .wl-name{font-size:12px;font-weight:600}
  .wl-meta{font-size:11px;color:var(--muted)}
  .wl-status{margin-left:auto;font-size:10px;font-weight:700;padding:3px 9px;border-radius:99px}
  .wls-active{background:#f0fdf4;color:#065f46}
  .wls-busy{background:#fffbeb;color:#b45309}
  .wls-free{background:#f3f4f6;color:#6b7280}

  /* ─ BAR CHART ────────────────────────────────────────── */
  .chart-area{padding:14px 20px 16px}
  .bar-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
  .bar-label{font-size:12px;color:var(--muted);width:96px;flex-shrink:0;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
  .bar-track{flex:1;height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden}
  .bar-fill{height:100%;border-radius:99px;transition:width .5s ease}
  .bar-count{font-size:11px;color:var(--muted);width:24px;text-align:right;font-weight:600}

  /* ─ PROFILE ──────────────────────────────────────────── */
  .profile-hero{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:28px 32px;display:flex;align-items:center;gap:24px;margin-bottom:20px;position:relative;overflow:hidden}
  .profile-hero::after{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--accent)}
  .avatar-lg{width:68px;height:68px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:22px;color:#fff;border:3px solid rgba(20,184,166,.2);flex-shrink:0}
  .profile-name{font-size:20px;font-weight:800}
  .profile-role-badge{display:inline-flex;align-items:center;gap:5px;background:var(--accent-light);color:var(--accent);font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px;margin-top:5px;border:1px solid rgba(20,184,166,.25)}
  .profile-stats{display:flex;gap:28px;margin-top:14px}
  .ps-val{font-size:20px;font-weight:800}
  .ps-key{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px}
  .profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}

  /* ─ FORMS ────────────────────────────────────────────── */
  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
  .form-group{margin-bottom:14px}
  .form-label{font-size:11px;color:var(--muted);font-weight:600;margin-bottom:5px;display:block;letter-spacing:.3px;text-transform:uppercase}
  .form-control{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;color:var(--text);font-family:'Poppins',sans-serif;font-size:13px;outline:none;transition:border-color .12s}
  .form-control:focus{border-color:var(--accent);background:#fff}
  .form-control:disabled{opacity:.55;cursor:not-allowed;background:#f9fafb}
  select.form-control option{background:#fff}
  textarea.form-control{resize:vertical;min-height:80px}

  /* ─ MODAL ────────────────────────────────────────────── */
  .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:200;opacity:0;pointer-events:none;transition:opacity .2s}
  .modal-overlay.open{opacity:1;pointer-events:all}
  .modal{background:var(--white);border:1px solid var(--border);border-radius:16px;width:600px;max-width:95vw;max-height:90vh;overflow-y:auto;transform:translateY(14px) scale(.97);transition:transform .22s;box-shadow:0 20px 60px rgba(0,0,0,.14)}
  .modal-overlay.open .modal{transform:none}
  .modal-head{padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;position:sticky;top:0;background:var(--white);z-index:1}
  .modal-title{font-size:16px;font-weight:700;flex:1}
  .modal-close{width:30px;height:30px;border-radius:7px;background:var(--bg);border:none;color:var(--muted);cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;transition:all .12s}
  .modal-close:hover{background:#fee2e2;color:var(--red)}
  .modal-body{padding:20px 24px}

  .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px}
  .info-cell{background:var(--bg);border-radius:8px;padding:10px 12px;border:1px solid var(--border)}
  .info-cell-lbl{font-size:10px;color:var(--muted2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;font-weight:600}
  .info-cell-val{font-size:13px;font-weight:600}
  .info-cell-val a{color:var(--accent);text-decoration:none}
  .info-cell-val a:hover{text-decoration:underline}

  .modal-section{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);margin:18px 0 8px;border-bottom:1px solid var(--border);padding-bottom:6px}
  .modal-actions{display:flex;gap:8px;justify-content:flex-end;padding-top:8px}

  /* ─ COMMENTS ─────────────────────────────────────────── */
  .comments-list{max-height:240px;overflow-y:auto;margin-bottom:10px;padding-right:2px}
  .comment-item{display:flex;gap:10px;margin-bottom:12px;animation:fadeIn .2s ease}
  .comment-avatar{width:30px;height:30px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;flex-shrink:0}
  .comment-avatar.admin-av{background:#6366f1}
  .comment-bubble{flex:1;background:var(--bg);border-radius:0 10px 10px 10px;padding:9px 12px;border:1px solid var(--border);position:relative}
  .comment-meta{font-size:10px;color:var(--muted2);margin-bottom:4px;display:flex;align-items:center;gap:8px}
  .comment-author{font-weight:600;color:var(--muted)}
  .comment-text{font-size:12px;line-height:1.55;color:var(--text)}
  .comment-del{margin-left:auto;background:none;border:none;color:var(--muted2);cursor:pointer;font-size:10px;padding:0;font-family:'Poppins',sans-serif}
  .comment-del:hover{color:var(--red)}
  .no-comments{font-size:12px;color:var(--muted2);text-align:center;padding:16px 0}

  /* ─ LOGS ─────────────────────────────────────────────── */
  .log-item{display:flex;gap:10px;font-size:12px;padding:8px 0;border-bottom:1px solid var(--border);align-items:flex-start}
  .log-item:last-child{border-bottom:none}
  .log-dot{width:22px;height:22px;border-radius:50%;background:var(--accent-light);border:1px solid rgba(20,184,166,.3);display:flex;align-items:center;justify-content:center;font-size:10px;flex-shrink:0}
  .log-text{flex:1;color:var(--text)}
  .log-time{font-size:10px;color:var(--muted2);flex-shrink:0}

  /* ─ IMAGE PREVIEW ────────────────────────────────────── */
  .report-image{width:100%;max-height:200px;object-fit:cover;border-radius:8px;border:1px solid var(--border);cursor:zoom-in;display:block}
  .report-map-link{font-size:12px;color:var(--accent);font-weight:600;display:inline-flex;align-items:center;gap:5px;margin-top:6px}
  .report-map-link:hover{color:var(--accent-dark)}

  /* ─ BUTTONS ──────────────────────────────────────────── */
  .btn{padding:9px 20px;border-radius:8px;font-family:'Poppins',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .12s;display:inline-flex;align-items:center;gap:6px}
  .btn-primary{background:var(--accent);color:#fff}
  .btn-primary:hover{background:var(--accent-dark)}
  .btn-ghost{background:var(--bg);color:var(--muted);border:1px solid var(--border)}
  .btn-ghost:hover{color:var(--text);background:#e5e7eb}
  .btn-sm{padding:6px 14px;font-size:12px}
  .btn:disabled{opacity:.5;cursor:not-allowed}

  /* ─ TOAST ────────────────────────────────────────────── */
  .toast{position:fixed;bottom:24px;right:24px;z-index:300;background:var(--white);border:1px solid var(--border);border-radius:10px;padding:12px 18px;display:flex;align-items:center;gap:10px;font-size:13px;box-shadow:0 8px 24px rgba(0,0,0,.12);transform:translateY(14px);opacity:0;pointer-events:none;transition:all .25s;font-weight:500;max-width:360px}
  .toast.show{transform:none;opacity:1;pointer-events:all}
  .toast.error{border-color:#fecaca;background:#fef2f2;color:var(--red)}
  .toast.success{border-color:#bbf7d0;color:#065f46}

  /* ─ SPINNER ──────────────────────────────────────────── */
  .spinner{display:inline-block;width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .6s linear infinite;flex-shrink:0}
  .spinner.dark{border-color:rgba(0,0,0,.1);border-top-color:var(--accent)}
  @keyframes spin{to{transform:rotate(360deg)}}

  /* ─ EMPTY / LOADING ──────────────────────────────────── */
  .empty{text-align:center;padding:44px 20px;color:var(--muted)}
  .empty .ei{font-size:36px;margin-bottom:10px;opacity:.5}
  .empty p{font-size:13px}
  .loading-row td{text-align:center;padding:30px;color:var(--muted);font-size:13px}

  ::-webkit-scrollbar{width:4px;height:4px}
  ::-webkit-scrollbar-track{background:transparent}
  ::-webkit-scrollbar-thumb{background:var(--border);border-radius:99px}
</style>
</head>
<body>

<!-- ═══════════════ SIDEBAR ═══════════════ -->
<aside class="sidebar">
  <div class="sb-logo">
    <div class="logo-icon">🏛️</div>
    <div>
      <div class="logo-text">MuniPortal</div>
      <div class="logo-sub">Agent Space</div>
    </div>
  </div>

  <div class="agent-card-sb" onclick="showPage('profile')">
    <div class="avatar-sm"><?= h($init) ?></div>
    <div style="flex:1;min-width:0">
      <div class="agent-name-sb"><?= h($user['name']) ?></div>
      <div class="agent-role-sb"><?= ucfirst(h($user['role'])) ?></div>
    </div>
    <div class="status-dot"></div>
  </div>

  <nav class="nav">
    <div class="nav-section">Main</div>
    <div class="nav-item active" id="nav-dashboard" onclick="showPage('dashboard')">
      <span class="nav-icon">📊</span> Dashboard
    </div>
    <div class="nav-item" id="nav-reports" onclick="showPage('reports'); loadReports()">
      <span class="nav-icon">📋</span> All Reports
      <span class="nav-badge" id="badge-total">—</span>
    </div>

    <div class="nav-section">Account</div>
    <div class="nav-item" id="nav-profile" onclick="showPage('profile')">
      <span class="nav-icon">👤</span> My Profile
    </div>
  </nav>

  <div class="sb-footer">
    <button class="logout-btn" onclick="confirmLogout()">
      <span>←</span> Sign out
    </button>
  </div>
</aside>

<!-- ═══════════════ MAIN ═══════════════ -->
<main class="main">
  <header class="topbar">
    <div class="topbar-title" id="topbar-title">Dashboard</div>
    <div class="search-box">
      <span style="color:var(--muted2);font-size:13px">🔍</span>
      <input type="text" id="search-input" placeholder="Search reports…" oninput="onSearchInput(this.value)"/>
    </div>
    <div style="display:flex;gap:8px">
      <button class="top-btn" onclick="showPage('reports'); applyFilter('all')">+ All Reports</button>
    </div>
  </header>

  <!-- ─ DASHBOARD PAGE ─ -->
  <div class="content page active" id="page-dashboard">
    <div class="stats-grid">
      <div class="stat-card s-default" onclick="showPage('reports'); applyFilter('all')">
        <div class="stat-label">Total</div>
        <div class="stat-value" id="stat-total">—</div>
        <div class="stat-sub">all reports</div>
      </div>
      <div class="stat-card s-blue" onclick="showPage('reports'); applyFilter('pending')">
        <div class="stat-label">Open</div>
        <div class="stat-value" id="stat-open">—</div>
        <div class="stat-sub">awaiting action</div>
      </div>
      <div class="stat-card s-yellow" onclick="showPage('reports'); applyFilter('progress')">
        <div class="stat-label">In Progress</div>
        <div class="stat-value" id="stat-progress">—</div>
        <div class="stat-sub">being handled</div>
      </div>
      <div class="stat-card s-green" onclick="showPage('reports'); applyFilter('resolved')">
        <div class="stat-label">Resolved</div>
        <div class="stat-value" id="stat-resolved">—</div>
        <div class="stat-sub">closed</div>
      </div>
      <div class="stat-card s-red">
        <div class="stat-label">Overdue</div>
        <div class="stat-value" id="stat-overdue">—</div>
        <div class="stat-sub">deadline exceeded</div>
      </div>
    </div>

    <div class="grid2">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">Recent Reports</div>
          <span class="panel-link" onclick="showPage('reports'); applyFilter('all')">View all →</span>
        </div>
        <div class="filter-tabs">
          <div class="ftab active" onclick="loadDashRecent('all',this)">All</div>
          <div class="ftab" onclick="loadDashRecent('pending',this)">Open</div>
          <div class="ftab" onclick="loadDashRecent('progress',this)">In Progress</div>
          <div class="ftab" onclick="loadDashRecent('resolved',this)">Resolved</div>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#ID</th><th>Description</th><th>Citizen</th><th>Status</th><th>Date</th></tr></thead>
            <tbody id="dash-tbody"><tr class="loading-row"><td colspan="5"><span class="spinner dark"></span> Loading…</td></tr></tbody>
          </table>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:18px">
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">Agent Workload</div>
          </div>
          <div id="workload-list"><div style="padding:16px;text-align:center;font-size:12px;color:var(--muted)">Loading…</div></div>
        </div>
        <div class="panel">
          <div class="panel-head"><div class="panel-title">By Category</div></div>
          <div class="chart-area" id="category-chart">
            <div style="font-size:12px;color:var(--muted);text-align:center">Loading…</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ─ REPORTS PAGE ─ -->
  <div class="content page" id="page-reports">
    <div class="panel">
      <div class="panel-head">
        <div class="panel-title" id="reports-page-title">All Reports</div>
      </div>
      <div class="filter-tabs">
        <div class="ftab active" id="ftab-all" onclick="applyFilter('all')">All</div>
        <div class="ftab" id="ftab-pending" onclick="applyFilter('pending')">Open</div>
        <div class="ftab" id="ftab-progress" onclick="applyFilter('progress')">In Progress</div>
        <div class="ftab" id="ftab-resolved" onclick="applyFilter('resolved')">Resolved</div>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#ID</th><th>Description</th><th>Citizen</th><th>Category</th><th>Agent</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
          <tbody id="full-tbody"><tr class="loading-row"><td colspan="8"><span class="spinner dark"></span> Loading…</td></tr></tbody>
        </table>
      </div>
      <div class="pagination">
        <span id="full-count">—</span>
        <div class="page-btns" id="page-btns"></div>
      </div>
    </div>
  </div>

  <!-- ─ PROFILE PAGE ─ -->
  <div class="content page" id="page-profile">
    <div class="profile-hero">
      <div class="avatar-lg"><?= h($init) ?></div>
      <div>
        <div class="profile-name" id="p-name"><?= h($user['name']) ?></div>
        <div class="profile-role-badge">🛡 <?= ucfirst(h($user['role'])) ?></div>
      </div>
    </div>
    <div class="profile-grid">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">Personal Information</div>
          <button class="btn btn-primary btn-sm" onclick="saveProfile()">💾 Save</button>
        </div>
        <div style="padding:20px 22px">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input class="form-control" id="prof-name" value="<?= h($user['name']) ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input class="form-control" value="<?= h($user['email']) ?>" disabled/>
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input class="form-control" id="prof-phone" value="<?= h($user['phone'] ?? '') ?>"/>
          </div>
          <div class="form-group">
            <label class="form-label">Role</label>
            <input class="form-control" value="<?= ucfirst(h($user['role'])) ?>" disabled/>
          </div>
          <div class="form-group">
            <label class="form-label">Member Since</label>
            <input class="form-control" value="<?= h(date('d/m/Y', strtotime($user['created_at']))) ?>" disabled/>
          </div>
        </div>
      </div>
      <div class="panel">
        <div class="panel-head"><div class="panel-title">Change Password</div></div>
        <div style="padding:20px 22px">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input class="form-control" type="password" id="pw-current" placeholder="••••••••" autocomplete="current-password"/>
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input class="form-control" type="password" id="pw-new" placeholder="••••••••" autocomplete="new-password"/>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input class="form-control" type="password" id="pw-confirm" placeholder="••••••••" autocomplete="new-password"/>
          </div>
          <button class="btn btn-primary" onclick="changePassword()">🔐 Update Password</button>
          <div style="margin-top:20px;padding-top:18px;border-top:1px solid var(--border)">
            <div style="font-size:12px;font-weight:600;margin-bottom:10px">Active Sessions</div>
            <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;padding:10px 12px;background:var(--bg);border-radius:8px;border:1px solid var(--border)">
              <div>
                <div style="font-weight:600">Current Browser</div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px">Active session</div>
              </div>
              <span style="color:var(--green);font-size:11px;font-weight:700">● Active</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- ═══════════════ REPORT DETAIL MODAL ═══════════════ -->
<div class="modal-overlay" id="report-modal">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title" id="modal-title">Report Details</div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body" id="modal-body">
      <div style="text-align:center;padding:30px"><span class="spinner dark"></span></div>
    </div>
  </div>
</div>

<!-- ═══════════════ IMAGE LIGHTBOX ═══════════════ -->
<div class="modal-overlay" id="lightbox" onclick="closeLightbox()" style="align-items:center;justify-content:center">
  <img id="lightbox-img" style="max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.4)"/>
</div>

<!-- ═══════════════ TOAST ═══════════════ -->
<div class="toast" id="toast"><span id="toast-msg">Done</span></div>

<script>
// ─── State ────────────────────────────────────────────────
let currentFilter = 'all';
let currentPage   = 1;
let currentSearch = '';
let openReportId  = null;
let searchTimer   = null;
const isAdmin     = <?= json_encode($user['role'] === 'admin') ?>;

// ─── API Helper ───────────────────────────────────────────
async function api(action, params = {}, method = 'GET') {
  try {
    let url  = `api.php?action=${action}`;
    let opts = { method };
    if (method === 'GET') {
      Object.entries(params).forEach(([k,v]) => url += `&${k}=${encodeURIComponent(v)}`);
    } else {
      const fd = new FormData();
      fd.append('action', action);
      Object.entries(params).forEach(([k,v]) => fd.append(k, v ?? ''));
      opts.body = fd;
    }
    const res  = await fetch(url, opts);
    const data = await res.json();
    return data;
  } catch (e) {
    return { success: false, message: 'Network error: ' + e.message };
  }
}

// ─── Toast ────────────────────────────────────────────────
let toastTimer;
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.className = 'toast show ' + type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

// ─── Page Navigation ──────────────────────────────────────
function showPage(p) {
  document.querySelectorAll('.page').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  document.getElementById('page-' + p).classList.add('active');
  const titles = { dashboard: 'Dashboard', reports: 'Reports', profile: 'My Profile' };
  document.getElementById('topbar-title').textContent = titles[p] || '';
  const navMap = { dashboard: 'nav-dashboard', profile: 'nav-profile', reports: 'nav-reports' };
  if (navMap[p]) document.getElementById(navMap[p])?.classList.add('active');
}

// ─── Stats ────────────────────────────────────────────────
async function loadStats() {
  const r = await api('stats');
  if (!r.success) return;
  const s = r.stats;
  document.getElementById('stat-total').textContent    = s.total    ?? 0;
  document.getElementById('stat-open').textContent     = s.open     ?? 0;
  document.getElementById('stat-progress').textContent = s.in_progress ?? 0;
  document.getElementById('stat-resolved').textContent = s.resolved  ?? 0;
  document.getElementById('stat-overdue').textContent  = s.overdue   ?? 0;
  document.getElementById('badge-total').textContent   = s.total     ?? 0;

}

// ─── Dashboard Recent ─────────────────────────────────────
async function loadDashRecent(filter = 'all', tabEl = null) {
  if (tabEl) {
    document.querySelectorAll('#page-dashboard .ftab').forEach(t => t.classList.remove('active'));
    tabEl.classList.add('active');
  }
  const tb = document.getElementById('dash-tbody');
  tb.innerHTML = `<tr class="loading-row"><td colspan="5"><span class="spinner dark"></span> Loading…</td></tr>`;
  const r = await api('recent_reports', { filter, limit: 8 });
  if (!r.success) {
    tb.innerHTML = `<tr><td colspan="5"><div class="empty"><div class="ei">⚠️</div><p>${r.message}</p></div></td></tr>`;
    return;
  }
  if (!r.reports.length) {
    tb.innerHTML = `<tr><td colspan="5"><div class="empty"><div class="ei">📭</div><p>No reports found</p></div></td></tr>`;
    return;
  }
  tb.innerHTML = r.reports.map(rep => `
    <tr onclick="openReport(${rep.id})">
      <td><span class="c-id">#${rep.id}</span></td>
      <td><div class="c-title">${esc(rep.title || rep.description?.substring(0,50) || '—')}</div></td>
      <td style="font-size:12px;color:var(--muted)">${esc(rep.citizen_name || '—')}</td>
      <td>${statusBadge(rep.status)}</td>
      <td style="font-size:12px;color:var(--muted)">${formatDate(rep.created_at)}</td>
    </tr>`).join('');
}

// ─── Workload ─────────────────────────────────────────────
async function loadWorkload() {
  const r = await api('workload');
  const el = document.getElementById('workload-list');
  if (!r.success) { el.innerHTML = `<div style="padding:12px;font-size:12px;color:var(--red)">${r.message}</div>`; return; }
  if (!r.workload.length) { el.innerHTML = `<div style="padding:16px;font-size:12px;color:var(--muted);text-align:center">No agents found</div>`; return; }
  const colors = ['var(--accent)','#6366f1','#f59e0b','#ec4899','#8b5cf6'];
  el.innerHTML = r.workload.slice(0, 5).map((a, i) => {
    const ini   = initials(a.name);
    const count = parseInt(a.open_count);
    const cls   = count > 8 ? 'wls-busy' : count > 0 ? 'wls-active' : 'wls-free';
    const lbl   = count > 8 ? 'busy' : count > 0 ? 'active' : 'free';
    return `<div class="workload-item">
      <div class="wl-avatar" style="background:${colors[i % colors.length]}">${ini}</div>
      <div>
        <div class="wl-name">${esc(a.name)}</div>
        <div class="wl-meta">${a.open_count} open · ${a.total_count} total</div>
      </div>
      <span class="wl-status ${cls}">${lbl}</span>
    </div>`;
  }).join('');
}

// ─── Category Chart ───────────────────────────────────────
async function loadCategoryChart() {
  const r = await api('category_stats');
  const el = document.getElementById('category-chart');
  if (!r.success || !r.categories.length) { el.innerHTML = `<div style="font-size:12px;color:var(--muted);text-align:center;padding:16px">No data</div>`; return; }
  const max  = Math.max(...r.categories.map(c => parseInt(c.total)));
  const clrs = ['var(--blue)','var(--yellow)','var(--green)','#0891b2','var(--muted2)'];
  el.innerHTML = r.categories.slice(0, 6).map((c, i) => {
    const pct = max ? Math.round((parseInt(c.total) / max) * 100) : 0;
    return `<div class="bar-row">
      <div class="bar-label" title="${esc(c.category || 'Uncategorized')}">${esc(c.category || 'None')}</div>
      <div class="bar-track"><div class="bar-fill" style="width:${pct}%;background:${clrs[i % clrs.length]}"></div></div>
      <div class="bar-count">${c.total}</div>
    </div>`;
  }).join('');
}

// ─── Reports Table ────────────────────────────────────────
async function loadReports(page = 1, filter = null, search = null) {
  if (filter !== null) currentFilter = filter;
  if (search !== null) currentSearch = search;
  currentPage = page;

  const tb = document.getElementById('full-tbody');
  tb.innerHTML = `<tr class="loading-row"><td colspan="8"><span class="spinner dark"></span> Loading…</td></tr>`;

  const r = await api('reports', { filter: currentFilter, page: currentPage, search: currentSearch });
  if (!r.success) {
    tb.innerHTML = `<tr><td colspan="8"><div class="empty"><div class="ei">⚠️</div><p>${r.message}</p></div></td></tr>`;
    return;
  }

  const titleMap = { all: 'All Reports', pending: 'Open Reports', progress: 'In Progress', resolved: 'Resolved Reports' };
  document.getElementById('reports-page-title').textContent = titleMap[currentFilter] || 'Reports';
  document.getElementById('full-count').textContent = `${r.total} report${r.total !== 1 ? 's' : ''}`;

  if (!r.reports.length) {
    tb.innerHTML = `<tr><td colspan="8"><div class="empty"><div class="ei">📭</div><p>No reports found</p></div></td></tr>`;
    renderPagination(0, 0);
    return;
  }

  tb.innerHTML = r.reports.map(rep => `
    <tr onclick="openReport(${rep.id})">
      <td><span class="c-id">#${rep.id}</span></td>
      <td>
        <div class="c-title">${esc(rep.title || rep.description?.substring(0,50) || '—')}</div>
        <div class="c-sub">${rep.comment_count} comment${rep.comment_count != 1 ? 's' : ''}</div>
      </td>
      <td style="font-size:12px">
        <div>${esc(rep.citizen_name || '—')}</div>
        <div class="c-sub">${esc(rep.citizen_email || '')}</div>
      </td>
      <td style="font-size:12px;color:var(--muted)">${esc(rep.category_name || '—')}</td>
      <td style="font-size:12px;color:var(--muted)">${esc(rep.agent_name || '—')}</td>
      <td>${statusBadge(rep.status)}</td>
      <td style="font-size:12px;color:var(--muted)">${formatDate(rep.created_at)}</td>
      <td onclick="event.stopPropagation()">
        <div class="act-btns">
          <button class="act-btn" onclick="openReport(${rep.id})">✏️ View</button>
        </div>
      </td>
    </tr>`).join('');

  renderPagination(r.page, r.pages);
}

function renderPagination(page, pages) {
  const cont = document.getElementById('page-btns');
  if (pages <= 1) { cont.innerHTML = ''; return; }
  let html = `<button class="page-btn" onclick="loadReports(${page-1})" ${page<=1?'disabled':''}>‹</button>`;
  for (let i = 1; i <= pages; i++) {
    if (pages > 7 && Math.abs(i - page) > 2 && i !== 1 && i !== pages) {
      if (Math.abs(i - page) === 3) html += `<button class="page-btn" disabled>…</button>`;
      continue;
    }
    html += `<button class="page-btn ${i===page?'active-page':''}" onclick="loadReports(${i})">${i}</button>`;
  }
  html += `<button class="page-btn" onclick="loadReports(${page+1})" ${page>=pages?'disabled':''}>›</button>`;
  cont.innerHTML = html;
}

function applyFilter(f) {
  currentFilter = f;
  currentPage   = 1;
  ['all','pending','progress','resolved'].forEach(k => {
    const el = document.getElementById('ftab-' + k);
    if (el) el.classList.toggle('active', k === f);
  });

  const titleMap = { all: 'All Reports', pending: 'Open Reports', progress: 'In Progress', resolved: 'Resolved Reports' };
  const titleEl = document.getElementById('reports-page-title');
  if (titleEl) titleEl.textContent = titleMap[f] || 'Reports';

  // Only call showPage if not already on reports
  if (!document.getElementById('page-reports').classList.contains('active')) {
    showPage('reports');
  } else {
    // Already on reports — just update nav highlight without re-triggering animation
    document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
    document.getElementById('nav-reports')?.classList.add('active');
  }

  loadReports();
}

// ─── Search ───────────────────────────────────────────────
function onSearchInput(val) {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    currentSearch = val.trim();
    loadReports(1);
  }, 350);
}

// ─── Report Modal ─────────────────────────────────────────
async function openReport(id) {
  openReportId = id;
  document.getElementById('report-modal').classList.add('open');
  document.getElementById('modal-title').textContent = `Report #${id}`;
  document.getElementById('modal-body').innerHTML = `<div style="text-align:center;padding:30px"><span class="spinner dark"></span> Loading report…</div>`;

  // also load agents list
  const [rDetail, rAgents] = await Promise.all([
    api('report_detail', { id }),
    api('agents')
  ]);

  if (!rDetail.success) {
    document.getElementById('modal-body').innerHTML = `<div class="empty"><div class="ei">⚠️</div><p>${rDetail.message}</p></div>`;
    return;
  }

  const rep      = rDetail.report;
  const comments = rDetail.comments;
  const logs     = rDetail.logs;
  const agents   = rAgents.agents || [];

  document.getElementById('modal-title').textContent = `Report #${rep.id} — ${rep.status}`;

  const agentsOptions = `<option value="">— Unassigned —</option>` +
    agents.map(a => `<option value="${a.id}" ${rep.assigned_to == a.id ? 'selected' : ''}>${esc(a.name)} (${a.role})</option>`).join('');

  const commentsHtml = comments.length
    ? comments.map(c => `
      <div class="comment-item" id="comment-${c.id}">
        <div class="comment-avatar ${c.author_role === 'admin' ? 'admin-av' : ''}">${initials(c.author_name || '?')}</div>
        <div class="comment-bubble">
          <div class="comment-meta">
            <span class="comment-author">${esc(c.author_name || 'Unknown')}</span>
            <span>${esc(c.author_role || '')}</span>
            <span>${formatDate(c.created_at)}</span>
            ${isAdmin ? `<button class="comment-del" onclick="deleteComment(${c.id})">🗑 Delete</button>` : ''}
          </div>
          <div class="comment-text">${esc(c.content)}</div>
        </div>
      </div>`).join('')
    : `<div class="no-comments">💬 No comments yet. Be the first to add one.</div>`;

  const logsHtml = logs.length
    ? logs.map(l => `
      <div class="log-item">
        <div class="log-dot">📝</div>
        <div class="log-text">
          <strong>${esc(l.changed_by_name || 'System')}</strong> changed status
          from <strong>${esc(l.old_status || '—')}</strong> to <strong>${esc(l.new_status || '—')}</strong>
          ${l.note ? `<br><span style="color:var(--muted);font-size:11px">${esc(l.note)}</span>` : ''}
        </div>
        <div class="log-time">${formatDate(l.created_at)}</div>
      </div>`).join('')
    : `<div style="font-size:12px;color:var(--muted2);padding:8px 0">No status changes logged yet.</div>`;

  const imageHtml = rep.image
    ? `<img class="report-image" src="${esc(rep.image)}" alt="Report image" onclick="openLightbox('${esc(rep.image)}')" onerror="this.style.display='none'"/>`
    : '';

  const mapHtml = rep.latitude && rep.longitude
    ? `<a class="report-map-link" href="https://maps.google.com/?q=${rep.latitude},${rep.longitude}" target="_blank" rel="noopener">📍 View on map (${parseFloat(rep.latitude).toFixed(5)}, ${parseFloat(rep.longitude).toFixed(5)})</a>`
    : '<span style="font-size:12px;color:var(--muted2)">📍 No location data</span>';

  document.getElementById('modal-body').innerHTML = `
    <!-- INFO GRID -->
    <div class="info-grid">
      <div class="info-cell"><div class="info-cell-lbl">Report ID</div><div class="info-cell-val">#${rep.id}</div></div>
      <div class="info-cell"><div class="info-cell-lbl">Submitted</div><div class="info-cell-val">${formatDate(rep.created_at)}</div></div>
      <div class="info-cell"><div class="info-cell-lbl">Citizen</div><div class="info-cell-val">${esc(rep.citizen_name || '—')}</div></div>
      <div class="info-cell"><div class="info-cell-lbl">Contact</div><div class="info-cell-val">
        ${rep.citizen_email ? `<a href="mailto:${esc(rep.citizen_email)}">${esc(rep.citizen_email)}</a>` : '—'}
        ${rep.citizen_phone ? `<br><small>${esc(rep.citizen_phone)}</small>` : ''}
      </div></div>
      <div class="info-cell"><div class="info-cell-lbl">Category</div><div class="info-cell-val">${esc(rep.category_name || '—')}</div></div>
      <div class="info-cell"><div class="info-cell-lbl">Deadline</div><div class="info-cell-val" id="m-deadline-display">${rep.deadline ? formatDate(rep.deadline) : '—'}</div></div>
    </div>

    <!-- TITLE & DESCRIPTION -->
    ${rep.title ? `<div class="form-group"><label class="form-label">Title</label><input class="form-control" value="${esc(rep.title)}" readonly/></div>` : ''}
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea class="form-control" readonly style="min-height:70px">${esc(rep.description || 'No description provided.')}</textarea>
    </div>

    <!-- IMAGE -->
    ${imageHtml ? `<div class="form-group">${imageHtml}</div>` : ''}
    <div class="form-group">${mapHtml}</div>

    <!-- UPDATE STATUS -->
    <div class="modal-section">Update Status</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-control" id="m-status">
          <option value="Open" ${rep.status==='Open'?'selected':''}>⏳ Open</option>
          <option value="In progress" ${rep.status==='In progress'?'selected':''}>🔄 In Progress</option>
          <option value="Resolved" ${rep.status==='Resolved'?'selected':''}>✅ Resolved</option>
          <option value="Closed" ${rep.status==='Closed'?'selected':''}>🔒 Closed</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Assign To</label>
        <select class="form-control" id="m-agent">${agentsOptions}</select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Deadline</label>
      <input class="form-control" type="datetime-local" id="m-deadline" value="${rep.deadline ? rep.deadline.replace(' ','T').substring(0,16) : ''}"/>
    </div>
    <div class="form-group">
      <label class="form-label">Internal Note (logged)</label>
      <textarea class="form-control" id="m-note" placeholder="Add a processing note…" style="min-height:60px"></textarea>
    </div>
    <div class="modal-actions" style="margin-bottom:20px">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" id="save-btn" onclick="saveReport()">💾 Save Changes</button>
    </div>

    <!-- COMMENTS -->
    <div class="modal-section">💬 Comments (${comments.length})</div>
    <div class="comments-list" id="comments-list">${commentsHtml}</div>
    <div style="display:flex;gap:8px;margin-bottom:20px">
      <textarea class="form-control" id="new-comment" placeholder="Write a comment…" style="min-height:50px;flex:1"></textarea>
      <button class="btn btn-primary btn-sm" style="align-self:flex-end;white-space:nowrap" onclick="addComment()">Send 💬</button>
    </div>

    <!-- ACTIVITY LOG -->
    <div class="modal-section">📋 Activity Log</div>
    <div>${logsHtml}</div>
  `;
}

async function saveReport() {
  if (!openReportId) return;
  const btn    = document.getElementById('save-btn');
  const status = document.getElementById('m-status').value;
  const agentId = document.getElementById('m-agent').value;
  const deadline = document.getElementById('m-deadline').value;
  const note   = document.getElementById('m-note').value.trim();

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner"></span> Saving…';

  const [r1, r2, r3] = await Promise.all([
    api('update_status', { id: openReportId, status, note }, 'POST'),
    api('assign_report', { id: openReportId, agent_id: agentId }, 'POST'),
    api('set_deadline',  { id: openReportId, deadline }, 'POST'),
  ]);

  btn.disabled = false;
  btn.innerHTML = '💾 Save Changes';

  if (!r1.success) { showToast('❌ ' + r1.message, 'error'); return; }
  if (!r2.success) { showToast('❌ ' + r2.message, 'error'); return; }
  if (!r3.success) { showToast('❌ ' + r3.message, 'error'); return; }

  showToast('✅ Report updated successfully');
  closeModal();
  loadStats();
  if (document.getElementById('page-reports').classList.contains('active')) loadReports();
  if (document.getElementById('page-dashboard').classList.contains('active')) loadDashRecent('all');
}

async function addComment() {
  if (!openReportId) return;
  const textarea = document.getElementById('new-comment');
  const content  = textarea.value.trim();
  if (!content) { showToast('⚠️ Comment cannot be empty.', 'error'); return; }

  const r = await api('add_comment', { report_id: openReportId, content }, 'POST');
  if (!r.success) { showToast('❌ ' + r.message, 'error'); return; }

  textarea.value = '';
  const c   = r.comment;
  const ini = initials(c.author_name || '?');
  const html = `
    <div class="comment-item" id="comment-${c.id}">
      <div class="comment-avatar ${c.author_role === 'admin' ? 'admin-av' : ''}">${ini}</div>
      <div class="comment-bubble">
        <div class="comment-meta">
          <span class="comment-author">${esc(c.author_name || 'You')}</span>
          <span>${esc(c.author_role || '')}</span>
          <span>Just now</span>
          ${isAdmin ? `<button class="comment-del" onclick="deleteComment(${c.id})">🗑 Delete</button>` : ''}
        </div>
        <div class="comment-text">${esc(c.content)}</div>
      </div>
    </div>`;
  const list = document.getElementById('comments-list');
  const noC  = list.querySelector('.no-comments');
  if (noC) noC.remove();
  list.insertAdjacentHTML('beforeend', html);
  list.scrollTop = list.scrollHeight;
  showToast('💬 Comment added');
}

async function deleteComment(id) {
  if (!confirm('Delete this comment?')) return;
  const r = await api('delete_comment', { id }, 'POST');
  if (!r.success) { showToast('❌ ' + r.message, 'error'); return; }
  document.getElementById('comment-' + id)?.remove();
  showToast('🗑 Comment deleted');
}

function closeModal() {
  document.getElementById('report-modal').classList.remove('open');
  openReportId = null;
}

// ─── Profile ──────────────────────────────────────────────
async function saveProfile() {
  const name  = document.getElementById('prof-name').value.trim();
  const phone = document.getElementById('prof-phone').value.trim();
  if (!name) { showToast('⚠️ Name is required.', 'error'); return; }
  const r = await api('update_profile', { name, phone }, 'POST');
  if (!r.success) { showToast('❌ ' + r.message, 'error'); return; }
  document.getElementById('p-name').textContent = name;
  showToast('✅ Profile updated successfully');
}

async function changePassword() {
  const current = document.getElementById('pw-current').value;
  const newPw   = document.getElementById('pw-new').value;
  const confirm = document.getElementById('pw-confirm').value;
  if (!current || !newPw || !confirm) { showToast('⚠️ All fields are required.', 'error'); return; }
  if (newPw !== confirm) { showToast('⚠️ New passwords do not match.', 'error'); return; }
  if (newPw.length < 6) { showToast('⚠️ Password must be at least 6 characters.', 'error'); return; }
  const r = await api('change_password', { current_password: current, new_password: newPw, confirm_password: confirm }, 'POST');
  if (!r.success) { showToast('❌ ' + r.message, 'error'); return; }
  ['pw-current','pw-new','pw-confirm'].forEach(id => document.getElementById(id).value = '');
  showToast('🔐 Password changed successfully');
}

// ─── Logout ───────────────────────────────────────────────
function confirmLogout() {
  if (confirm('Are you sure you want to sign out?')) {
      window.location.href = '../auth/logout.php';
  }
}

// ─── Lightbox ─────────────────────────────────────────────
function openLightbox(src) {
  document.getElementById('lightbox-img').src = src;
  document.getElementById('lightbox').classList.add('open');
}
function closeLightbox() {
  document.getElementById('lightbox').classList.remove('open');
}

// ─── Helpers ──────────────────────────────────────────────
function esc(str) {
  if (str == null) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function statusBadge(s) {
  const map = {
    'Open':        'st-open',
    'In progress': 'st-progress',
    'Resolved':    'st-resolved',
    'Closed':      'st-resolved',
  };
  const cls = map[s] || 'st-open';
  return `<span class="st-badge ${cls}"><span class="dot"></span>${esc(s)}</span>`;
}

function initials(name) {
  return (name || '?').split(' ').slice(0,2).map(w => w[0]?.toUpperCase() || '').join('') || '?';
}

function formatDate(dt) {
  if (!dt) return '—';
  const d = new Date(dt.replace(' ', 'T'));
  if (isNaN(d)) return dt;
  return d.toLocaleDateString('en-GB') + ' ' + d.toLocaleTimeString('en-GB', { hour:'2-digit', minute:'2-digit' });
}

// ─── Close modal on overlay click ────────────────────────
document.getElementById('report-modal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') { closeModal(); closeLightbox(); }
});

// ─── Init ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  loadStats();
  loadDashRecent('all');
  loadWorkload();
  loadCategoryChart();
});
</script>
</body>
</html>