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
    <!-- FIX: utiliser applyFilter('all') au lieu de loadReports() directement -->
    <div class="nav-item" id="nav-reports" onclick="showPage('reports'); applyFilter('all')">
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
      <button class="top-btn" onclick="showPage('reports'); applyFilter('all')">📋 All Reports</button>
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
        <!-- FIX: bouton refresh -->
        <button class="btn btn-ghost btn-sm" onclick="loadReports(currentPage)" title="Refresh">🔄 Refresh</button>
      </div>
      <div class="filter-tabs">
        <div class="ftab active" id="ftab-all"      onclick="applyFilter('all')">All</div>
        <div class="ftab"        id="ftab-pending"  onclick="applyFilter('pending')">Open</div>
        <div class="ftab"        id="ftab-progress" onclick="applyFilter('progress')">In Progress</div>
        <div class="ftab"        id="ftab-resolved" onclick="applyFilter('resolved')">Resolved</div>
      </div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#ID</th>
              <th>Description</th>
              <th>Citizen</th>
              <th>Category</th>
              <th>Agent</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="full-tbody">
            <tr class="loading-row"><td colspan="8"><span class="spinner dark"></span> Loading…</td></tr>
          </tbody>
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