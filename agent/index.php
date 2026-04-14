<?php
//agent/index.php
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
<title>Dashboard — MuniPortal</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="../assets/css/agent_dashboard.css">
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

  <div class="agent-card-sb" onclick="window.location.href='profile.php'">
    <div class="avatar-sm"><?= h($init) ?></div>
    <div style="flex:1;min-width:0">
      <div class="agent-name-sb"><?= h($user['name']) ?></div>
      <div class="agent-role-sb"><?= ucfirst(h($user['role'])) ?></div>
    </div>
    <div class="status-dot"></div>
  </div>

  <nav class="nav">
    <div class="nav-section">Main</div>
    <div class="nav-item active">
      <span class="nav-icon">📊</span> Dashboard
    </div>
    <div class="nav-item" onclick="window.location.href='reports.php'">
      <span class="nav-icon">📋</span> All Reports
    </div>
    <div class="nav-item" onclick="window.location.href='citizenrequests.php'">
      <span class="nav-icon">🙏</span> Citizen Requests
    
      <span class="nav-badge" id="badge-total">—</span>
    </div>

    <div class="nav-section">Account</div>
    <div class="nav-item" onclick="window.location.href='profile.php'">
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
    <div class="topbar-title">Dashboard</div>
    <div class="search-box">
      <span style="color:var(--muted2);font-size:13px">🔍</span>
      <input type="text" id="search-input" placeholder="Search reports…" oninput="onSearchInput(this.value)"/>
    </div>
    <div style="display:flex;gap:8px">
      <button class="top-btn" onclick="window.location.href='reports.php'">📋 All Reports</button>
    </div>
  </header>

  <!-- ─ DASHBOARD ─ -->
  <div class="content page active" id="page-dashboard">
    <div class="stats-grid">
      <div class="stat-card s-default" onclick="window.location.href='reports.php?filter=all'">
        <div class="stat-label">Total</div>
        <div class="stat-value" id="stat-total">—</div>
        <div class="stat-sub">all reports</div>
      </div>
      <div class="stat-card s-blue" onclick="window.location.href='reports.php?filter=pending'">
        <div class="stat-label">Open</div>
        <div class="stat-value" id="stat-open">—</div>
        <div class="stat-sub">awaiting action</div>
      </div>
      <div class="stat-card s-yellow" onclick="window.location.href='reports.php?filter=progress'">
        <div class="stat-label">In Progress</div>
        <div class="stat-value" id="stat-progress">—</div>
        <div class="stat-sub">being handled</div>
      </div>
      <div class="stat-card s-green" onclick="window.location.href='reports.php?filter=resolved'">
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
          <span class="panel-link" onclick="window.location.href='reports.php'">View all →</span>
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
  <img id="lightbox-img" style="max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.4)" alt="Report image"/>
</div>

<!-- ═══════════════ TOAST ═══════════════ -->
<div class="toast" id="toast"><span id="toast-msg">Done</span></div>

<script>
    window.APP = {
        isAdmin: <?= json_encode($user['role'] === 'admin') ?>
    };
</script>
<script src="../assets/js/agentdashboard_script.js"></script>
</body>
</html>