<?php
//agent/citizenrequests.php

session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if (!in_array($_SESSION['user_role'], ['agent', 'admin'], true)) {
    header('Location: ../auth/login.php?error=access_denied');
    exit;
}

$user = [
    'id'   => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'role' => $_SESSION['user_role'],
];
$init = implode('', array_map(
    fn($w) => mb_strtoupper(mb_substr($w, 0, 1)),
    array_slice(explode(' ', trim($user['name'])), 0, 2)
));
function h($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>MuniPortal — Citizen Requests</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
/* ─── (même CSS que votre frontend existant — copié intégralement) ─── */
:root {
  --bg:#f0f2f5;--sidebar:#111827;--sidebar-hover:#1f2937;
  --accent:#14b8a6;--accent-dark:#0d9488;--accent-light:rgba(20,184,166,.12);
  --white:#fff;--border:#e5e7eb;--text:#111827;--muted:#6b7280;--muted2:#9ca3af;
  --green:#10b981;--yellow:#f59e0b;--red:#ef4444;--blue:#3b82f6;--purple:#8b5cf6;
  --r:12px;--sw:224px;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;font-size:14px}
.sidebar{width:var(--sw);min-height:100vh;background:var(--sidebar);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100}
.sb-logo{padding:20px 18px 16px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.07)}
.logo-icon{width:36px;height:36px;background:var(--accent);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.logo-text{font-size:15px;font-weight:700;color:#fff}.logo-sub{font-size:10px;color:rgba(255,255,255,.4);letter-spacing:.3px}
.nav{flex:1;padding:10px;overflow-y:auto}
.nav-section{font-size:9px;color:rgba(255,255,255,.3);letter-spacing:1.5px;font-weight:600;padding:14px 8px 5px;text-transform:uppercase}
.nav-item{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:8px;font-size:13px;color:rgba(255,255,255,.55);cursor:pointer;transition:all .15s;margin-bottom:2px;font-weight:500;text-decoration:none}
.nav-item:hover{background:var(--sidebar-hover);color:rgba(255,255,255,.9)}
.nav-item.active{background:var(--accent);color:#fff}
.nav-icon{font-size:15px;width:16px;text-align:center;flex-shrink:0}
.nav-badge{margin-left:auto;font-size:10px;font-weight:700;padding:1px 7px;border-radius:99px;background:rgba(255,255,255,.15);color:#fff;min-width:22px;text-align:center}
.nav-item.active .nav-badge{background:rgba(0,0,0,.2)}
.nav-badge.green-badge{background:var(--green)}
.agent-card-sb{margin:10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:10px;display:flex;align-items:center;gap:9px}
.avatar-sm{width:34px;height:34px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0}
.agent-name-sb{font-size:12px;font-weight:600;color:#fff}.agent-role-sb{font-size:10px;color:rgba(255,255,255,.4)}
.status-dot{width:6px;height:6px;border-radius:50%;background:var(--green);box-shadow:0 0 5px var(--green);flex-shrink:0}
.sb-footer{padding:10px;border-top:1px solid rgba(255,255,255,.07)}
.logout-btn{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:8px;font-size:12px;color:rgba(255,255,255,.5);cursor:pointer;background:none;border:none;width:100%;transition:all .15s;font-family:'Poppins',sans-serif;font-weight:500}
.logout-btn:hover{background:rgba(239,68,68,.15);color:#f87171}
.main{margin-left:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh}
.topbar{height:58px;background:var(--white);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;padding:0 24px;position:sticky;top:0;z-index:90}
.topbar-title{font-size:18px;font-weight:700;flex:1;color:var(--text)}
.topbar-subtitle{font-size:13px;font-weight:400;color:var(--muted);margin-left:8px}
.search-box{display:flex;align-items:center;gap:8px;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:0 12px;height:36px;width:220px}
.search-box input{background:none;border:none;outline:none;color:var(--text);font-family:'Poppins',sans-serif;font-size:12px;flex:1}
.search-box input::placeholder{color:var(--muted2)}
.content{padding:24px;flex:1}
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:24px}
.stat-card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:18px 20px;position:relative;overflow:hidden;transition:box-shadow .2s,transform .15s}
.stat-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.08);transform:translateY(-1px)}
.stat-card::after{content:'';position:absolute;top:0;left:0;right:0;height:3px;border-radius:var(--r) var(--r) 0 0}
.stat-card.s-blue::after{background:var(--blue)}.stat-card.s-yellow::after{background:var(--yellow)}
.stat-card.s-green::after{background:var(--green)}.stat-card.s-red::after{background:var(--red)}
.stat-label{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;font-weight:600;margin-bottom:8px}
.stat-value{font-size:32px;font-weight:800;line-height:1;color:var(--text);margin-bottom:4px}
.stat-sub{font-size:11px;color:var(--muted2)}
.grid2{display:grid;grid-template-columns:1fr 320px;gap:18px}
.panel{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden}
.panel-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.panel-title{font-size:15px;font-weight:700;flex:1}
.filter-tabs{display:flex;gap:4px;padding:12px 20px;border-bottom:1px solid var(--border);flex-wrap:wrap;align-items:center}
.ftab{font-size:12px;padding:5px 14px;border-radius:99px;cursor:pointer;transition:all .15s;border:1px solid transparent;color:var(--muted);font-weight:500;user-select:none}
.ftab:hover{color:var(--text);background:var(--bg)}
.ftab.active{background:var(--accent-light);color:var(--accent);border-color:rgba(20,184,166,.25);font-weight:600}
.sort-btn-top{margin-left:auto;height:30px;padding:0 12px;border-radius:7px;border:1px solid var(--border);background:var(--white);color:var(--muted);cursor:pointer;font-size:11px;font-weight:600;font-family:'Poppins',sans-serif;transition:all .12s}
.sort-btn-top:hover{background:var(--accent-light);color:var(--accent);border-color:rgba(20,184,166,.3)}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead th{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;font-weight:600;padding:10px 16px;text-align:left;border-bottom:1px solid var(--border);background:#fafafa;white-space:nowrap}
tbody tr{border-bottom:1px solid var(--border);transition:background .12s;cursor:pointer}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#f9fafb}
tbody tr.row-selected{background:var(--accent-light)!important}
tbody td{padding:11px 16px;font-size:13px;vertical-align:middle}
.c-id{font-size:12px;color:var(--accent);font-weight:700}
.c-title{font-weight:500}.c-sub{font-size:11px;color:var(--muted);margin-top:2px}
.citizen-cell{display:flex;align-items:center;gap:8px}
.cit-av{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:10px;color:#fff;flex-shrink:0}
.st-badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:4px 10px;border-radius:99px;white-space:nowrap}
.st-badge .dot{width:6px;height:6px;border-radius:50%;flex-shrink:0}
.st-pending{background:#fffbeb;color:#b45309}.st-pending .dot{background:var(--yellow)}
.st-in_progress{background:#f0f9ff;color:#0369a1}.st-in_progress .dot{background:#0ea5e9;animation:pulse 1.5s infinite}
.st-approved{background:#f0fdf4;color:#065f46}.st-approved .dot{background:var(--green)}
.st-rejected{background:#fef2f2;color:#b91c1c}.st-rejected .dot{background:var(--red)}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.35}}
.act-btns{display:flex;gap:5px}
.act-btn{height:28px;padding:0 10px;border-radius:6px;border:1px solid var(--border);background:var(--white);color:var(--muted);cursor:pointer;display:flex;align-items:center;gap:4px;font-size:11px;font-weight:600;transition:all .12s;font-family:'Poppins',sans-serif;white-space:nowrap}
.act-btn:hover{background:var(--accent-light);color:var(--accent);border-color:rgba(20,184,166,.3)}
.act-btn.btn-accept:hover{background:#f0fdf4;color:#065f46;border-color:#6ee7b7}
.act-btn.btn-reject:hover{background:#fef2f2;color:#b91c1c;border-color:#fca5a5}
.act-btn.btn-msg:hover{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe}
.pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border);font-size:12px;color:var(--muted);flex-wrap:wrap;gap:8px}
.page-btns{display:flex;gap:5px;flex-wrap:wrap}
.page-btn{width:30px;height:30px;border-radius:6px;background:var(--white);border:1px solid var(--border);color:var(--muted);cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:all .12s;font-family:'Poppins',sans-serif}
.page-btn:hover,.page-btn.active-page{background:var(--accent);color:#fff;border-color:var(--accent)}
.page-btn:disabled{opacity:.4;cursor:default;pointer-events:none}
.right-col{display:flex;flex-direction:column;gap:18px}

/* ── DETAIL PANEL ── */
.detail-panel{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden}
.detail-panel-head{padding:16px 20px;border-bottom:1px solid var(--border);background:linear-gradient(135deg,var(--accent-light) 0%,transparent 80%);position:relative}
.detail-panel-head::after{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--accent)}
.dp-id-row{display:flex;align-items:center;gap:8px;margin-bottom:4px}
.dp-id{font-size:12px;color:var(--accent);font-weight:700}
.dp-title{font-size:14px;font-weight:700;color:var(--text)}
.detail-panel-body{padding:16px 20px;display:flex;flex-direction:column;gap:12px}
.info-field{}
.field-lbl{font-size:10px;color:var(--muted2);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-bottom:4px}
.field-val{font-size:13px;color:var(--text);font-weight:500}
.citizen-info-row{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--bg);border-radius:8px;border:1px solid var(--border)}
.cit-av-md{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0}
.ci-name{font-size:13px;font-weight:600}.ci-email{font-size:11px;color:var(--muted)}
.note-area{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;color:var(--text);font-family:'Poppins',sans-serif;font-size:12px;outline:none;resize:vertical;min-height:60px;transition:border-color .12s}
.note-area::placeholder{color:var(--muted2)}.note-area:focus{border-color:var(--accent);background:#fff}
.detail-actions{display:flex;gap:6px}
.detail-act-btn{flex:1;padding:8px 0;border-radius:8px;font-size:11px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;border:1px solid var(--border);background:var(--bg);color:var(--muted);transition:all .15s}
.detail-act-btn:hover{color:var(--text);background:#e5e7eb}
.detail-act-btn.accept{background:#f0fdf4;border-color:#6ee7b7;color:#065f46}
.detail-act-btn.accept:hover{background:#d1fae5}
.detail-act-btn.reject{background:#fef2f2;border-color:#fca5a5;color:#b91c1c}
.detail-act-btn.reject:hover{background:#fee2e2}
.detail-act-btn.msg{background:#eff6ff;border-color:#bfdbfe;color:#1d4ed8}
.detail-act-btn.msg:hover{background:#dbeafe}

/* ── CHAT ── */
.chat-box{display:flex;flex-direction:column;height:260px;border:1.5px solid var(--border);border-radius:8px;overflow:hidden}
.chat-msgs{flex:1;overflow-y:auto;padding:10px;display:flex;flex-direction:column;gap:8px;background:#fafafa}
.chat-bubble{max-width:85%;padding:8px 11px;border-radius:10px;font-size:12px;line-height:1.5;word-break:break-word}
.chat-bubble.mine{align-self:flex-end;background:var(--accent);color:#fff;border-bottom-right-radius:3px}
.chat-bubble.theirs{align-self:flex-start;background:var(--white);border:1px solid var(--border);color:var(--text);border-bottom-left-radius:3px}
.chat-bubble .bname{font-size:10px;font-weight:700;margin-bottom:3px;opacity:.7}
.chat-bubble .btime{font-size:10px;opacity:.6;margin-top:3px;text-align:right}
.chat-input-row{display:flex;gap:6px;padding:8px;border-top:1px solid var(--border);background:var(--white)}
.chat-input{flex:1;border:1.5px solid var(--border);border-radius:7px;padding:7px 10px;font-size:12px;font-family:'Poppins',sans-serif;outline:none;transition:border-color .12s}
.chat-input:focus{border-color:var(--accent)}
.chat-send{height:34px;padding:0 14px;border-radius:7px;background:var(--accent);color:#fff;border:none;font-size:12px;font-weight:600;cursor:pointer;transition:background .12s;font-family:'Poppins',sans-serif}
.chat-send:hover{background:var(--accent-dark)}

/* ── CHART ── */
.chart-area{padding:14px 20px 16px}
.bar-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.bar-label{font-size:12px;color:var(--muted);width:96px;flex-shrink:0;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.bar-track{flex:1;height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden}
.bar-fill{height:100%;border-radius:99px;transition:width .5s ease}
.bar-count{font-size:11px;color:var(--muted);width:24px;text-align:right;font-weight:600}

/* ── MODAL ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:200;opacity:0;pointer-events:none;transition:opacity .2s}
.modal-overlay.open{opacity:1;pointer-events:all}
.modal{background:var(--white);border:1px solid var(--border);border-radius:16px;width:480px;max-width:95vw;max-height:90vh;overflow-y:auto;transform:translateY(14px) scale(.97);transition:transform .22s;box-shadow:0 20px 60px rgba(0,0,0,.14)}
.modal-overlay.open .modal{transform:none}
.modal-head{padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
.modal-title{font-size:16px;font-weight:700;flex:1}
.modal-close{width:30px;height:30px;border-radius:7px;background:var(--bg);border:none;color:var(--muted);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;transition:all .12s}
.modal-close:hover{background:#fee2e2;color:var(--red)}
.modal-body{padding:20px 24px}
.modal-actions{display:flex;gap:8px;justify-content:flex-end;padding-top:8px}
.form-group{margin-bottom:14px}
.form-label{font-size:11px;color:var(--muted);font-weight:600;margin-bottom:5px;display:block;letter-spacing:.3px;text-transform:uppercase}
.form-control{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:8px;padding:9px 12px;color:var(--text);font-family:'Poppins',sans-serif;font-size:13px;outline:none;transition:border-color .12s}
.form-control:focus{border-color:var(--accent);background:#fff}
textarea.form-control{resize:vertical;min-height:70px}
.btn{padding:9px 20px;border-radius:8px;font-family:'Poppins',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .12s;display:inline-flex;align-items:center;gap:6px}
.btn-primary{background:var(--accent);color:#fff}.btn-primary:hover{background:var(--accent-dark)}
.btn-ghost{background:var(--bg);color:var(--muted);border:1px solid var(--border)}.btn-ghost:hover{color:var(--text);background:#e5e7eb}
.btn-danger{background:#fef2f2;color:#b91c1c;border:1px solid #fca5a5}.btn-danger:hover{background:#fee2e2}

/* ── TOAST ── */
.toast{position:fixed;bottom:24px;right:24px;z-index:300;background:var(--white);border:1px solid var(--border);border-radius:10px;padding:12px 18px;display:flex;align-items:center;gap:10px;font-size:13px;box-shadow:0 8px 24px rgba(0,0,0,.12);transform:translateY(14px);opacity:0;pointer-events:none;transition:all .25s;font-weight:500;max-width:360px}
.toast.show{transform:none;opacity:1;pointer-events:all}
.toast.error{border-color:#fecaca;background:#fef2f2;color:var(--red)}
.toast.success{border-color:#bbf7d0;color:#065f46}
.toast.info{border-color:#bfdbfe;color:#1d4ed8}
.empty{text-align:center;padding:44px 20px;color:var(--muted)}
.empty .ei{font-size:36px;margin-bottom:10px;opacity:.5}
.empty p{font-size:13px}
.loading-row td{text-align:center;padding:30px;color:var(--muted);font-size:13px}
::-webkit-scrollbar{width:4px;height:4px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border);border-radius:99px}
@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr)}.grid2{grid-template-columns:1fr}}
@media(max-width:768px){:root{--sw:0px}.sidebar{display:none}.main{margin-left:0}.stats-grid{grid-template-columns:1fr 1fr}}
</style>
</head>
<body>

<!-- ─── SIDEBAR ─── -->
<aside class="sidebar">
  <div class="sb-logo">
    <div class="logo-icon">🏛️</div>
    <div>
      <div class="logo-text">MuniPortal</div>
      <div class="logo-sub">Agent Space</div>
    </div>
  </div>
  <div class="agent-card-sb" style="margin-top:10px">
    <div class="avatar-sm"><?= h($init) ?></div>
    <div style="flex:1;min-width:0">
      <div class="agent-name-sb"><?= h($user['name']) ?></div>
      <div class="agent-role-sb"><?= ucfirst(h($user['role'])) ?></div>
    </div>
    <div class="status-dot"></div>
  </div>
  <nav class="nav">
    <div class="nav-section">Main</div>
    <a href="index.php" class="nav-item"><span class="nav-icon">📊</span> Dashboard</a>
    <a href="reports.php" class="nav-item"><span class="nav-icon">📋</span> All Reports</a>
    <a href="citizenrequests.php" class="nav-item active">
      <span class="nav-icon">🙏</span> Citizen Requests
      <span class="nav-badge green-badge" id="nav-pending-badge" style="display:none">0</span>
    </a>
    <div class="nav-section">Account</div>
    <a href="profile.php" class="nav-item"><span class="nav-icon">👤</span> My Profile</a>
  </nav>
  <div class="sb-footer">
    <button class="logout-btn" onclick="if(confirm('Sign out?'))window.location.href='../auth/logout.php'">🚪 Sign Out</button>
  </div>
</aside>

<!-- ─── MAIN ─── -->
<div class="main">
  <header class="topbar">
    <div class="topbar-title">
      Citizen Requests
      <span class="topbar-subtitle" id="topbar-sub">Loading…</span>
    </div>
    <div class="search-box">
      <span>🔍</span>
      <input type="text" id="search-input" placeholder="Search requests…"/>
    </div>
  </header>

  <div class="content">
    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card s-blue">
        <div class="stat-label">Total</div>
        <div class="stat-value" id="stat-total">—</div>
        <div class="stat-sub">all requests</div>
      </div>
      <div class="stat-card s-yellow">
        <div class="stat-label">Pending</div>
        <div class="stat-value" id="stat-pending">—</div>
        <div class="stat-sub">awaiting action</div>
      </div>
      <div class="stat-card s-green">
        <div class="stat-label">Approved</div>
        <div class="stat-value" id="stat-approved">—</div>
        <div class="stat-sub">accepted</div>
      </div>
      <div class="stat-card s-red">
        <div class="stat-label">Rejected</div>
        <div class="stat-value" id="stat-rejected">—</div>
        <div class="stat-sub">refused</div>
      </div>
    </div>

    <!-- BODY GRID -->
    <div class="grid2">
      <!-- TABLE -->
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">Requests list</div>
          <span style="font-size:12px;color:var(--muted)" id="full-count"></span>
        </div>
        <div class="filter-tabs">
          <div class="ftab active" onclick="setFilter(this,'all')">All</div>
          <div class="ftab" onclick="setFilter(this,'pending')">Pending</div>
          <div class="ftab" onclick="setFilter(this,'in_progress')">In Progress</div>
          <div class="ftab" onclick="setFilter(this,'approved')">Approved</div>
          <div class="ftab" onclick="setFilter(this,'rejected')">Rejected</div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#ID</th>
                <th>Service</th>
                <th>Citizen</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="req-tbody">
              <tr class="loading-row"><td colspan="6">Loading…</td></tr>
            </tbody>
          </table>
          <div class="empty" id="empty-state" style="display:none">
            <div class="ei">📭</div><p>No requests match your filter</p>
          </div>
        </div>
        <div class="pagination">
          <span id="footer-count" style="font-size:12px;color:var(--muted)"></span>
          <div class="page-btns" id="page-btns"></div>
        </div>
      </div>

      <!-- RIGHT COLUMN -->
      <div class="right-col">
        <!-- DETAIL + CHAT -->
        <div class="detail-panel" id="detail-panel">
          <div class="detail-panel-head">
            <div class="dp-id-row">
              <span class="dp-id" id="dp-id">Select a request</span>
            </div>
            <div class="dp-title" id="dp-title" style="color:var(--muted)">Click on a row to view details</div>
          </div>
          <div class="detail-panel-body" id="dp-body">
            <div class="empty"><div class="ei">📄</div><p>Select a request to view details</p></div>
          </div>
        </div>

        <!-- SERVICES CHART -->
        <div class="panel">
          <div class="panel-head"><div class="panel-title">By Service</div></div>
          <div class="chart-area" id="cat-chart">
            <div style="text-align:center;font-size:12px;color:var(--muted)">Loading…</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ─── MODAL : Accept ─── -->
<div class="modal-overlay" id="modal-accept" onclick="closeModalOutside(event,'modal-accept')">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">✅ Accept Request</div>
      <button class="modal-close" onclick="closeModal('modal-accept')">✕</button>
    </div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--muted);margin-bottom:14px">
        You are about to <strong>accept</strong> request <strong id="accept-rid"></strong>.
      </p>
      <div class="form-group">
        <label class="form-label">Note (optional)</label>
        <textarea class="form-control" id="accept-note" placeholder="Add a note for the citizen…"></textarea>
      </div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="closeModal('modal-accept')">Cancel</button>
        <button class="btn btn-primary" onclick="confirmAccept()">✅ Confirm Accept</button>
      </div>
    </div>
  </div>
</div>

<!-- ─── MODAL : Reject ─── -->
<div class="modal-overlay" id="modal-reject" onclick="closeModalOutside(event,'modal-reject')">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">❌ Reject Request</div>
      <button class="modal-close" onclick="closeModal('modal-reject')">✕</button>
    </div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--muted);margin-bottom:14px">
        You are about to <strong>reject</strong> request <strong id="reject-rid"></strong>.
      </p>
      <div class="form-group">
        <label class="form-label">Reason (optional)</label>
        <textarea class="form-control" id="reject-note" placeholder="Explain why this request is rejected…"></textarea>
      </div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="closeModal('modal-reject')">Cancel</button>
        <button class="btn btn-danger" onclick="confirmReject()">❌ Confirm Reject</button>
      </div>
    </div>
  </div>
</div>

<!-- ─── TOAST ─── -->
<div class="toast" id="toast">
  <span id="toast-icon">✅</span>
  <span id="toast-msg"></span>
</div>

<script>
// ─────────────────────────────────────────────
const API      = 'api.php';
const COLORS   = ['#14b8a6','#3b82f6','#f59e0b','#ec4899','#8b5cf6','#ef4444'];
const CURRENT_USER_ID = <?= json_encode($user['id']) ?>;

let currentFilter = 'all';
let currentPage   = 1;
let selectedId    = null;
let searchQuery   = '';
let pendingAction = null; // {type:'accept'|'reject', id}
let toastTimer;
let searchTimer;

// ─────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  loadRequests();
  loadChart();

  document.getElementById('search-input').addEventListener('input', e => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      searchQuery = e.target.value.trim();
      currentPage = 1;
      loadRequests();
    }, 350);
  });
});

// ─────────────────────────────────────────────
// LOAD REQUESTS
// ─────────────────────────────────────────────
async function loadRequests() {
  showTableLoading();
  try {
    const params = new URLSearchParams({
      action: 'citizen_requests',
      filter: currentFilter,
      search: searchQuery,
      page:   currentPage,
    });
    const res  = await fetch(`${API}?${params}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message);

    renderStats(data.stats);
    renderTable(data.requests, data.total, data.page, data.pages);
    updateNavBadge(data.stats.pending || 0);
  } catch (err) {
    showToast('Error loading requests: ' + err.message, 'error');
    document.getElementById('req-tbody').innerHTML =
      `<tr class="loading-row"><td colspan="6">Failed to load.</td></tr>`;
  }
}

// ─────────────────────────────────────────────
// RENDER STATS
// ─────────────────────────────────────────────
function renderStats(s) {
  document.getElementById('stat-total').textContent    = s.total    ?? 0;
  document.getElementById('stat-pending').textContent  = (s.pending ?? 0) + (s.in_progress ?? 0);
  document.getElementById('stat-approved').textContent = s.approved ?? 0;
  document.getElementById('stat-rejected').textContent = s.rejected ?? 0;
  document.getElementById('topbar-sub').textContent    = (s.total ?? 0) + ' requests';
}

function updateNavBadge(count) {
  const b = document.getElementById('nav-pending-badge');
  if (!b) return;
  b.textContent    = count;
  b.style.display  = count > 0 ? '' : 'none';
}

// ─────────────────────────────────────────────
// RENDER TABLE
// ─────────────────────────────────────────────
function renderTable(requests, total, page, pages) {
  const tbody    = document.getElementById('req-tbody');
  const empty    = document.getElementById('empty-state');
  const countEl  = document.getElementById('full-count');
  const footerEl = document.getElementById('footer-count');

  if (countEl)  countEl.textContent  = `${total} request${total!==1?'s':''}`;
  if (footerEl) footerEl.textContent = `${total} request${total!==1?'s':''}`;

  if (!requests || requests.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = '';
    renderPagination(0, 1, 1);
    return;
  }
  empty.style.display = 'none';

  tbody.innerHTML = requests.map(r => {
    const color  = avatarColor(r.citizen_name);
    const init   = initials(r.citizen_name);
    const isSelected = selectedId == r.id;
    return `
    <tr onclick="selectRow(${r.id})" class="${isSelected ? 'row-selected' : ''}">
      <td><span class="c-id">#${r.id}</span></td>
      <td>
        <div class="c-title">${esc(r.service_name)}</div>
        <div class="c-sub">${esc(r.service_category)}</div>
      </td>
      <td>
        <div class="citizen-cell">
          <div class="cit-av" style="background:${color}">${init}</div>
          <div>
            <div style="font-size:13px;font-weight:500">${esc(r.citizen_name)}</div>
            <div class="c-sub">${esc(r.citizen_email)}</div>
          </div>
        </div>
      </td>
      <td>${statusBadge(r.status)}</td>
      <td style="font-size:12px;color:var(--muted)">${formatDate(r.created_at)}</td>
      <td onclick="event.stopPropagation()">
        <div class="act-btns">
          ${r.status === 'pending' || r.status === 'in_progress' ? `
            <button class="act-btn btn-accept" onclick="openAccept(${r.id})">✅</button>
            <button class="act-btn btn-reject" onclick="openReject(${r.id})">❌</button>
          ` : ''}
          <button class="act-btn btn-msg" onclick="selectRow(${r.id})">💬 ${r.msg_count > 0 ? r.msg_count : ''}</button>
        </div>
      </td>
    </tr>`;
  }).join('');

  renderPagination(total, page, pages);
}

function renderPagination(total, page, pages) {
  const cont = document.getElementById('page-btns');
  if (pages <= 1) { cont.innerHTML = ''; return; }
  let html = `<button class="page-btn" onclick="goPage(${page-1})" ${page===1?'disabled':''}>‹</button>`;
  for (let i = 1; i <= pages; i++) {
    html += `<button class="page-btn ${i===page?'active-page':''}" onclick="goPage(${i})">${i}</button>`;
  }
  html += `<button class="page-btn" onclick="goPage(${page+1})" ${page===pages?'disabled':''}>›</button>`;
  cont.innerHTML = html;
}

function goPage(p) { currentPage = p; loadRequests(); }

function setFilter(el, f) {
  document.querySelectorAll('.ftab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  currentFilter = f;
  currentPage   = 1;
  loadRequests();
}

function showTableLoading() {
  document.getElementById('req-tbody').innerHTML =
    `<tr class="loading-row"><td colspan="6">Loading…</td></tr>`;
}

// ─────────────────────────────────────────────
// SELECT ROW → DETAIL + CHAT
// ─────────────────────────────────────────────
async function selectRow(id) {
  if (selectedId === id) {
    selectedId = null;
    resetDetail();
    // Re-render pour retirer la sélection visuelle
    loadRequests();
    return;
  }
  selectedId = id;
  loadRequests(); // update row highlight

  // Load detail
  document.getElementById('dp-id').textContent    = `#${id}`;
  document.getElementById('dp-title').textContent = 'Loading…';
  document.getElementById('dp-title').style.color = 'var(--muted)';
  document.getElementById('dp-body').innerHTML    = '<div class="empty"><div class="ei">⏳</div><p>Loading details…</p></div>';

  try {
    const res  = await fetch(`${API}?action=request_detail&id=${id}`);
    const data = await res.json();
    if (!data.success) throw new Error(data.message);
    renderDetail(data.request, data.messages);
  } catch (err) {
    document.getElementById('dp-body').innerHTML =
      `<div class="empty"><div class="ei">❌</div><p>${esc(err.message)}</p></div>`;
  }
}

// ─────────────────────────────────────────────
// RENDER DETAIL
// ─────────────────────────────────────────────
function renderDetail(r, messages) {
  const color = avatarColor(r.citizen_name);

  document.getElementById('dp-id').innerHTML =
    `<strong>#${r.id}</strong> &nbsp; ${statusBadge(r.status)}`;
  document.getElementById('dp-title').textContent = r.service_name;
  document.getElementById('dp-title').style.color = 'var(--text)';

  const canAct = r.status === 'pending' || r.status === 'in_progress';

  document.getElementById('dp-body').innerHTML = `
    <div class="info-field">
      <div class="field-lbl">Citizen</div>
      <div class="citizen-info-row">
        <div class="cit-av-md" style="background:${color}">${initials(r.citizen_name)}</div>
        <div>
          <div class="ci-name">${esc(r.citizen_name)}</div>
          <div class="ci-email">${esc(r.citizen_email)}</div>
          ${r.citizen_phone ? `<div class="ci-email">📞 ${esc(r.citizen_phone)}</div>` : ''}
        </div>
      </div>
    </div>

    <div class="info-field">
      <div class="field-lbl">Service</div>
      <div class="field-val">${esc(r.service_name)}
        <span style="font-size:11px;color:var(--muted);margin-left:6px">${esc(r.service_category)}</span>
      </div>
    </div>

    ${r.message ? `
    <div class="info-field">
      <div class="field-lbl">Citizen Message</div>
      <div class="field-val" style="background:var(--bg);border-radius:8px;padding:9px 12px;font-size:12px;border:1px solid var(--border)">
        ${esc(r.message)}
      </div>
    </div>` : ''}

    ${r.admin_message ? `
    <div class="info-field">
      <div class="field-lbl">Agent Note</div>
      <div class="field-val" style="font-size:12px;color:var(--muted)">${esc(r.agent_note)}</div>
    </div>` : ''}

    <div class="info-field">
      <div class="field-lbl">Submitted</div>
      <div class="field-val" style="font-size:12px;color:var(--muted)">${formatDate(r.created_at)}</div>
    </div>

    ${canAct ? `
    <div class="detail-actions">
      <button class="detail-act-btn accept" onclick="openAccept(${r.id})">✅ Accept</button>
      <button class="detail-act-btn reject"  onclick="openReject(${r.id})">❌ Reject</button>
    </div>` : `
    <div style="text-align:center;padding:6px 0;font-size:12px;color:var(--muted)">
      This request is <strong>${esc(r.status)}</strong>
      ${r.handled_at ? ' · ' + formatDate(r.handled_at) : ''}
    </div>`}

    <!-- CHAT -->
    <div class="info-field">
      <div class="field-lbl">💬 Messages with Citizen</div>
      <div class="chat-box">
        <div class="chat-msgs" id="chat-msgs-${r.id}">
          ${renderMessages(messages, r.id)}
        </div>
        <div class="chat-input-row">
          <input class="chat-input" id="chat-input-${r.id}"
            type="text" placeholder="Send a message to the citizen…"
            onkeydown="if(event.key==='Enter')sendMessage(${r.id})"/>
          <button class="chat-send" onclick="sendMessage(${r.id})">Send</button>
        </div>
      </div>
    </div>
  `;

  scrollChat(r.id);
}

function renderMessages(messages, rid) {
  if (!messages || messages.length === 0) {
    return `<div style="text-align:center;padding:20px;font-size:12px;color:var(--muted)">No messages yet. Start the conversation!</div>`;
  }
  return messages.map(m => {
    const mine = parseInt(m.sender_id) === CURRENT_USER_ID;
    return `
    <div class="chat-bubble ${mine ? 'mine' : 'theirs'}">
      ${!mine ? `<div class="bname">${esc(m.sender_name)}</div>` : ''}
      ${esc(m.content).replace(/\n/g,'<br>')}
      <div class="btime">${formatDate(m.created_at)}</div>
    </div>`;
  }).join('');
}

function scrollChat(rid) {
  setTimeout(() => {
    const el = document.getElementById(`chat-msgs-${rid}`);
    if (el) el.scrollTop = el.scrollHeight;
  }, 50);
}

// ─────────────────────────────────────────────
// SEND MESSAGE
// ─────────────────────────────────────────────
async function sendMessage(requestId) {
  const input   = document.getElementById(`chat-input-${requestId}`);
  const content = input.value.trim();
  if (!content) return;

  input.value    = '';
  input.disabled = true;

  try {
    const fd = new FormData();
    fd.append('action',     'send_message');
    fd.append('request_id', requestId);
    fd.append('content',    content);

    const res  = await fetch(API, { method: 'POST', body: fd });
    const data = await res.json();
    if (!data.success) throw new Error(data.message);

    // Ajouter le message dans le chat sans recharger
    const container = document.getElementById(`chat-msgs-${requestId}`);
    if (container) {
      // Retirer le message "no messages" si présent
      const empty = container.querySelector('div[style*="No messages"]');
      if (empty) empty.remove();

      const bubble = document.createElement('div');
      bubble.className = 'chat-bubble mine';
      bubble.innerHTML = `${esc(content).replace(/\n/g,'<br>')}
        <div class="btime">Just now</div>`;
      container.appendChild(bubble);
      container.scrollTop = container.scrollHeight;
    }
  } catch (err) {
    showToast('Failed to send: ' + err.message, 'error');
    input.value = content; // restore
  } finally {
    input.disabled = false;
    input.focus();
  }
}

// ─────────────────────────────────────────────
// ACCEPT / REJECT
// ─────────────────────────────────────────────
function openAccept(id) {
  pendingAction = { type: 'accept', id };
  document.getElementById('accept-rid').textContent = '#' + id;
  document.getElementById('accept-note').value      = '';
  openModal('modal-accept');
}

function openReject(id) {
  pendingAction = { type: 'reject', id };
  document.getElementById('reject-rid').textContent = '#' + id;
  document.getElementById('reject-note').value      = '';
  openModal('modal-reject');
}

async function confirmAccept() {
  if (!pendingAction) return;
  const note = document.getElementById('accept-note').value.trim();
  await doAction('accept_request', pendingAction.id, note);
  closeModal('modal-accept');
}

async function confirmReject() {
  if (!pendingAction) return;
  const note = document.getElementById('reject-note').value.trim();
  await doAction('reject_request', pendingAction.id, note);
  closeModal('modal-reject');
}

async function doAction(action, id, note = '') {
  try {
    const fd = new FormData();
    fd.append('action', action);
    fd.append('id',     id);
    if (note) fd.append('note', note);

    const res  = await fetch(API, { method: 'POST', body: fd });
    const data = await res.json();
    if (!data.success) throw new Error(data.message);

    const label = action === 'accept_request' ? 'accepted ✅' : 'rejected ❌';
    showToast(`Request #${id} ${label}`, action === 'accept_request' ? 'success' : 'error');

    // Refresh
    if (selectedId == id) {
      selectedId = null;
      resetDetail();
    }
    loadRequests();
  } catch (err) {
    showToast(err.message, 'error');
  }
}

// ─────────────────────────────────────────────
// CHART — by service
// ─────────────────────────────────────────────
async function loadChart() {
  try {
    const res  = await fetch(`${API}?action=citizen_requests&filter=all&page=1`);
    const data = await res.json();
    if (!data.success) return;

    // Grouper par service côté JS
    const map = {};
    (data.requests || []).forEach(r => {
      map[r.service_name] = (map[r.service_name] || 0) + 1;
    });

    // Pour le chart, on recharge avec per_page plus grand
    // → on utilise les stats déjà disponibles
    renderChart(map);
  } catch {}
}

function renderChart(map) {
  const el    = document.getElementById('cat-chart');
  const items = Object.entries(map).sort((a,b) => b[1]-a[1]);
  if (!items.length) { el.innerHTML = '<div style="text-align:center;font-size:12px;color:var(--muted);padding:10px">No data</div>'; return; }
  const max   = Math.max(...items.map(x=>x[1]));
  const clrs  = ['var(--blue)','var(--yellow)','var(--green)','var(--purple)','var(--muted2)'];
  el.innerHTML = items.map(([name,count],i) => `
    <div class="bar-row">
      <div class="bar-label" title="${esc(name)}">${esc(name)}</div>
      <div class="bar-track"><div class="bar-fill" style="width:${Math.round(count/max*100)}%;background:${clrs[i%clrs.length]}"></div></div>
      <div class="bar-count">${count}</div>
    </div>`).join('');
}

// ─────────────────────────────────────────────
// DETAIL RESET
// ─────────────────────────────────────────────
function resetDetail() {
  document.getElementById('dp-id').textContent    = 'Select a request';
  document.getElementById('dp-title').textContent = 'Click on a row to view details';
  document.getElementById('dp-title').style.color = 'var(--muted)';
  document.getElementById('dp-body').innerHTML    =
    `<div class="empty"><div class="ei">📄</div><p>Select a request to view details</p></div>`;
}

// ─────────────────────────────────────────────
// MODALS
// ─────────────────────────────────────────────
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
function closeModalOutside(e, id) {
  if (e.target === document.getElementById(id)) closeModal(id);
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
});

// ─────────────────────────────────────────────
// TOAST
// ─────────────────────────────────────────────
function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  document.getElementById('toast-icon').textContent = type==='success' ? '✅' : type==='error' ? '❌' : 'ℹ️';
  document.getElementById('toast-msg').textContent  = msg;
  t.className = 'toast show ' + type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

// ─────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────
function esc(str) {
  return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function statusBadge(s) {
  const map = {
    pending:     ['st-pending',    '⏳ Pending'],
    in_progress: ['st-in_progress','🔄 In Progress'],
    approved:    ['st-approved',   '✅ Approved'],
    rejected:    ['st-rejected',   '❌ Rejected'],
  };
  const [cls, label] = map[s] || ['st-pending', s];
  return `<span class="st-badge ${cls}"><span class="dot"></span>${label}</span>`;
}

function initials(name) {
  return (name || '?').split(' ').slice(0,2).map(w => (w[0]||'').toUpperCase()).join('') || '?';
}

function avatarColor(name) {
  let h = 0;
  for (let i = 0; i < (name||'').length; i++) h = (h * 31 + (name||'').charCodeAt(i)) & 0xffffffff;
  return COLORS[Math.abs(h) % COLORS.length];
}

function formatDate(d) {
  if (!d) return '—';
  const dt = new Date(d);
  if (isNaN(dt)) return d;
  return dt.toLocaleDateString('fr-FR', { day:'2-digit', month:'2-digit', year:'numeric' })
    + ' ' + dt.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' });
}
</script>
</body>
</html>