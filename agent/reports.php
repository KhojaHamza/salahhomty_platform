<?php
//agent/reports.php
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

// Filtre initial passé depuis le dashboard (ex: reports.php?filter=pending)
$initFilter = in_array($_GET['filter'] ?? '', ['all', 'pending', 'progress', 'resolved'])
    ? $_GET['filter']
    : 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>All Reports — MuniPortal</title>
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
    <div class="nav-item" onclick="window.location.href='index.php'">
      <span class="nav-icon">📊</span> Dashboard
    </div>
    <div class="nav-item active">
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
    <div class="topbar-title">All Reports</div>
    <div class="search-box">
      <span style="color:var(--muted2);font-size:13px">🔍</span>
      <input type="text" id="search-input" placeholder="Search reports…" oninput="onSearchInput(this.value)"/>
    </div>
    <div style="display:flex;gap:8px">
      <button class="top-btn" onclick="loadReports(currentPage)">📋 All Reports</button>
    </div>
  </header>

  <!-- ─ REPORTS PAGE ─ -->
  <div class="content page active" id="page-reports">
    <div class="panel">
      <div class="panel-head">
        <div class="panel-title" id="reports-page-title">All Reports</div>
        <button class="btn btn-ghost btn-sm" onclick="loadReports(currentPage)" title="Refresh">🔄 Refresh</button>
      </div>
      <div class="filter-tabs">
        <div class="ftab" id="ftab-all"      onclick="applyFilter('all')">All</div>
        <div class="ftab" id="ftab-pending"  onclick="applyFilter('pending')">Open</div>
        <div class="ftab" id="ftab-progress" onclick="applyFilter('progress')">In Progress</div>
        <div class="ftab" id="ftab-resolved" onclick="applyFilter('resolved')">Resolved</div>
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
<script>
    // Appliquer le filtre transmis depuis le dashboard
    document.addEventListener('DOMContentLoaded', function () {
        applyFilter(<?= json_encode($initFilter) ?>);
    });
</script>
</body>
</html>