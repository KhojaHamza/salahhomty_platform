<?php
// admin/services.php
require_once 'auth_check.php';
 
if (!isAdmin()) {
    header('Location: ../index.php?error=access_denied');
    exit;
}
 
$adminName    = $currentUser['name'] ?? 'Admin';
$adminInitial = strtoupper(substr($adminName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SalahHomty – Municipal Services</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', sans-serif; }
    :root {
      --teal: #1ab58a; --teal-dark: #0f6e56; --teal-light: #e1f5ee;
      --sidebar-bg: #1a2332; --sidebar-text: #a0aec0;
      --bg: #f4f6f9; --white: #ffffff; --border: #e2e8f0;
      --text: #2d3748; --text-muted: #718096;
      --danger: #e53e3e; --warning: #d97706; --success: #1ab58a; --info: #3182ce;
    }
    html, body { height: 100%; }
    body { background: var(--bg); display: flex; min-height: 100vh; }

    /* ── SIDEBAR ── */
    .sidebar { width: 210px; min-height: 100vh; background: var(--sidebar-bg); display: flex; flex-direction: column; flex-shrink: 0; }
    .sidebar-logo { padding: 18px 20px 14px; border-bottom: 1px solid rgba(255,255,255,0.07); display: flex; align-items: center; gap: 8px; }
    .logo-icon { width: 32px; height: 32px; background: var(--teal); border-radius: 8px; display: flex; align-items: center; justify-content: center; }
    .logo-icon svg { width: 18px; height: 18px; fill: white; }
    .logo-text { font-size: 16px; font-weight: 700; color: white; }
    .logo-text span { color: var(--teal); }
    .sidebar-section { padding: 18px 0 4px; }
    .sidebar-label { font-size: 10px; font-weight: 600; color: #4a5568; text-transform: uppercase; letter-spacing: 1px; padding: 0 20px 8px; }
    .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 20px; color: var(--sidebar-text); font-size: 13.5px; cursor: pointer; transition: all 0.15s; text-decoration: none; }
    .nav-item:hover { background: rgba(255,255,255,0.06); color: white; }
    .nav-item.active { background: rgba(26,181,138,0.15); color: var(--teal); border-right: 3px solid var(--teal); }
    .nav-item svg { width: 16px; height: 16px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex-shrink: 0; }
    .sidebar-footer { margin-top: auto; padding: 14px 20px; border-top: 1px solid rgba(255,255,255,0.07); display: flex; align-items: center; gap: 10px; }
    .user-avatar { width: 32px; height: 32px; border-radius: 50%; background: var(--teal); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 13px; flex-shrink: 0; }
    .user-info { flex: 1; min-width: 0; }
    .user-name { font-size: 13px; color: white; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-role { font-size: 11px; color: var(--sidebar-text); }
    .signout-btn { font-size: 12px; color: var(--sidebar-text); background: none; border: none; cursor: pointer; padding: 6px 8px; border-radius: 6px; transition: all 0.15s; white-space: nowrap; }
    .signout-btn:hover { background: rgba(255,255,255,0.08); color: white; }

    /* ── MAIN ── */
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .topbar { background: white; border-bottom: 1px solid var(--border); padding: 0 28px; height: 58px; display: flex; align-items: center; justify-content: space-between; }
    .topbar h1 { font-size: 20px; font-weight: 700; color: var(--text); }
    .topbar-actions { display: flex; gap: 10px; }
    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 7px; font-size: 13.5px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s; }
    .btn-primary { background: var(--teal); color: white; }
    .btn-primary:hover { background: var(--teal-dark); }
    .btn-outline { background: white; color: var(--text); border: 1.5px solid var(--border); }
    .btn-outline:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-light); }
    .btn svg { width: 15px; height: 15px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .content { flex: 1; padding: 24px 28px; overflow-y: auto; }

    /* ── STATS ── */
    .stats-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
    .stat-card { background: white; border-radius: 10px; border: 1px solid var(--border); padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
    .stat-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .stat-icon svg { width: 20px; height: 20px; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .stat-icon.teal { background: var(--teal-light); } .stat-icon.teal svg { stroke: var(--teal); }
    .stat-icon.blue { background: #ebf4ff; } .stat-icon.blue svg { stroke: var(--info); }
    .stat-icon.orange { background: #fff7ed; } .stat-icon.orange svg { stroke: var(--warning); }
    .stat-icon.red { background: #fff5f5; } .stat-icon.red svg { stroke: var(--danger); }
    .stat-value { font-size: 22px; font-weight: 700; color: var(--text); }
    .stat-label { font-size: 12px; color: var(--text-muted); margin-top: 1px; }

    /* ── TOOLBAR ── */
    .toolbar { background: white; border-radius: 10px; border: 1px solid var(--border); padding: 14px 18px; display: flex; align-items: center; gap: 12px; margin-bottom: 18px; }
    .search-wrap { flex: 1; position: relative; }
    .search-wrap svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; stroke: var(--text-muted); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .search-wrap input { width: 100%; padding: 8px 10px 8px 34px; border: 1.5px solid var(--border); border-radius: 7px; font-size: 13.5px; color: var(--text); outline: none; background: var(--bg); }
    .search-wrap input:focus { border-color: var(--teal); background: white; }
    .filter-select { padding: 8px 12px; border: 1.5px solid var(--border); border-radius: 7px; font-size: 13px; color: var(--text); background: var(--bg); outline: none; cursor: pointer; }
    .filter-select:focus { border-color: var(--teal); }

    /* ── CATEGORY TABS ── */
    .cat-tabs { display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap; }
    .cat-tab { padding: 6px 14px; border-radius: 20px; font-size: 12.5px; font-weight: 600; cursor: pointer; border: 1.5px solid var(--border); background: white; color: var(--text-muted); transition: all 0.15s; }
    .cat-tab.active { background: var(--teal); color: white; border-color: var(--teal); }
    .cat-tab:hover:not(.active) { border-color: var(--teal); color: var(--teal); }

    /* ── SERVICE CARDS ── */
    .services-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .service-card { background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; transition: all 0.2s; cursor: pointer; }
    .service-card:hover { box-shadow: 0 6px 24px rgba(0,0,0,0.09); transform: translateY(-2px); }
    .card-header { padding: 16px 18px 12px; display: flex; align-items: flex-start; justify-content: space-between; }
    .card-icon-wrap { display: flex; align-items: center; gap: 12px; }
    .card-icon { width: 46px; height: 46px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .card-icon svg { width: 22px; height: 22px; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .card-icon.water { background: #ebf8ff; } .card-icon.water svg { stroke: #2b6cb0; }
    .card-icon.clean { background: #f0fff4; } .card-icon.clean svg { stroke: #276749; }
    .card-icon.light { background: #fffff0; } .card-icon.light svg { stroke: #b7791f; }
    .card-icon.road  { background: #fff5f5; } .card-icon.road  svg { stroke: #c53030; }
    .card-icon.waste { background: #fff7ed; } .card-icon.waste svg { stroke: #c05621; }
    .card-title { font-size: 14.5px; font-weight: 700; color: var(--text); margin-bottom: 2px; }
    .card-category { font-size: 11.5px; color: var(--text-muted); }
    .status-badge { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
    .status-badge.active      { background: var(--teal-light); color: var(--teal-dark); }
    .status-badge.inactive    { background: #fff5f5; color: var(--danger); }
    .status-badge.maintenance { background: #fff7ed; color: var(--warning); }
    .card-body { padding: 0 18px 14px; }
    .card-desc { font-size: 12.5px; color: var(--text-muted); line-height: 1.5; margin-bottom: 12px; }
    .card-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-bottom: 12px; }
    .meta-item { background: var(--bg); border-radius: 7px; padding: 8px 10px; }
    .meta-label { font-size: 10.5px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .meta-value { font-size: 12.5px; color: var(--text); font-weight: 600; margin-top: 2px; }
    .card-footer { padding: 10px 18px; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
    .card-stats { display: flex; gap: 14px; }
    .card-stat { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--text-muted); }
    .card-stat svg { width: 13px; height: 13px; fill: none; stroke: var(--text-muted); stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .card-stat.overdue { color: var(--danger); }
    .card-stat.overdue svg { stroke: var(--danger); }
    .card-actions { display: flex; gap: 6px; }
    .icon-btn { width: 30px; height: 30px; border-radius: 7px; border: 1.5px solid var(--border); background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
    .icon-btn:hover { border-color: var(--teal); background: var(--teal-light); }
    .icon-btn svg { width: 14px; height: 14px; fill: none; stroke: var(--text-muted); stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .icon-btn:hover svg { stroke: var(--teal); }
    .icon-btn.danger:hover { border-color: var(--danger); background: #fff5f5; }
    .icon-btn.danger:hover svg { stroke: var(--danger); }

    /* ── MODALS ── */
    .modal-backdrop { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1000; align-items: center; justify-content: center; }
    .modal-backdrop.open { display: flex; }
    .modal { background: white; border-radius: 14px; width: 520px; max-height: 90vh; overflow-y: auto; padding: 28px; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
    .modal-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 20px; }
    .modal-close { position: absolute; top: 20px; right: 20px; width: 32px; height: 32px; border-radius: 8px; border: 1.5px solid var(--border); background: white; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 16px; color: var(--text-muted); }
    .modal-close:hover { background: var(--bg); }
    .form-group { margin-bottom: 16px; }
    .form-label { font-size: 12.5px; font-weight: 600; color: var(--text); margin-bottom: 6px; display: block; }
    .form-control { width: 100%; padding: 9px 12px; border: 1.5px solid var(--border); border-radius: 8px; font-size: 13.5px; color: var(--text); outline: none; background: white; transition: border-color 0.15s; }
    .form-control:focus { border-color: var(--teal); }
    textarea.form-control { resize: vertical; min-height: 80px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; padding-top: 18px; border-top: 1px solid var(--border); }

    /* Detail modal */
    .detail-modal { width: 600px; }
    .detail-top { display: flex; gap: 20px; margin-bottom: 20px; }
    .detail-info { flex: 1; }
    .detail-name { font-size: 20px; font-weight: 700; color: var(--text); }
    .detail-cat { font-size: 13px; color: var(--text-muted); margin-top: 3px; }
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 18px; }
    .detail-item { background: var(--bg); border-radius: 9px; padding: 12px; }
    .detail-item-label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
    .detail-item-value { font-size: 14px; font-weight: 600; color: var(--text); margin-top: 4px; }
    .qr-section { text-align: center; padding: 18px; background: var(--bg); border-radius: 12px; }
    .qr-title { font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 12px; }
    .qr-download { margin-top: 10px; font-size: 12px; color: var(--teal); cursor: pointer; font-weight: 600; }
    .qr-download:hover { text-decoration: underline; }
    .section-label { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 10px; margin-top: 16px; }

    /* Toast */
    .toast { position: fixed; bottom: 24px; right: 24px; background: #1a2332; color: white; padding: 12px 20px; border-radius: 10px; font-size: 13.5px; font-weight: 500; z-index: 9999; opacity: 0; transform: translateY(10px); transition: all 0.3s; pointer-events: none; }
    .toast.show { opacity: 1; transform: translateY(0); }
    .toast.success { border-left: 4px solid var(--teal); }
    .toast.error   { border-left: 4px solid var(--danger); }

    /* Loading spinner */
    .spinner { display: inline-block; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }

    /* Empty state */
    .empty-state { grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: var(--text-muted); }
    .empty-state svg { width: 48px; height: 48px; stroke: #cbd5e0; fill: none; stroke-width: 1.5; margin-bottom: 14px; }
    .empty-state p { font-size: 15px; }
  </style>
</head>
<body>

<!-- ════════════════════ SIDEBAR ════════════════════ -->
<div class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon">
      <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22" fill="white"/></svg>
    </div>
    <div class="logo-text">Salah<span>Homty</span></div>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Main</div>
    <a href="index.php" class="nav-item">
      <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="reports.php" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      All Reports
    </a>
  </div>
  <div class="sidebar-section">
    <div class="sidebar-label">Management</div>
    <a href="agents.php" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Agents
    </a>
    <a href="services.php" class="nav-item active">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
      Services
    </a>
  </div>
  <div class="sidebar-footer">
    <div class="user-avatar"><?= htmlspecialchars($adminInitial) ?></div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($adminName) ?></div>
      <div class="user-role">Admin</div>
    </div>
    <a href="../auth/logout.php" class="signout-btn">← Sign out</a>
  </div>
</div>

<!-- ════════════════════ MAIN ════════════════════ -->
<div class="main">
  <div class="topbar">
    <h1>Municipal Services</h1>
    <div class="topbar-actions">
      <button class="btn btn-outline" onclick="openAddModal()">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        Add Service
      </button>
    </div>
  </div>

  <div class="content">
    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card">
        <div class="stat-icon teal"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 12l2 2 4-4"/></svg></div>
        <div><div class="stat-value" id="statTotal">–</div><div class="stat-label">Total Services</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon blue"><svg viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
        <div><div class="stat-value" id="statActive">–</div><div class="stat-label">Active Services</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon orange"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg></div>
        <div><div class="stat-value" id="statMaint">–</div><div class="stat-label">Under Maintenance</div></div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
        <div><div class="stat-value" id="statInactive">–</div><div class="stat-label">Inactive Services</div></div>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="search-wrap">
        <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Search services..." id="searchInput" oninput="filterCards()">
      </div>
      <select class="filter-select" id="statusFilter" onchange="filterCards()">
        <option value="">All Status</option>
        <option>Active</option><option>Inactive</option><option>Maintenance</option>
      </select>
      <select class="filter-select" id="catFilter" onchange="filterCards()">
        <option value="">All Categories</option>
        <option>Water</option><option>Environment</option>
        <option>Lighting</option><option>Infrastructure</option><option>Waste</option>
      </select>
    </div>

    <!-- Category Tabs -->
    <div class="cat-tabs">
      <div class="cat-tab active" onclick="setCat(this,'')">All Services</div>
      <div class="cat-tab" onclick="setCat(this,'Water')">💧 Water</div>
      <div class="cat-tab" onclick="setCat(this,'Environment')">🌿 Environment</div>
      <div class="cat-tab" onclick="setCat(this,'Lighting')">💡 Lighting</div>
      <div class="cat-tab" onclick="setCat(this,'Infrastructure')">🛣️ Infrastructure</div>
      <div class="cat-tab" onclick="setCat(this,'Waste')">♻️ Waste</div>
    </div>

    <!-- Cards Grid -->
    <div class="services-grid" id="servicesGrid">
      <div class="empty-state" style="grid-column:1/-1;">
        <div class="spinner" style="border-color:rgba(0,0,0,0.1);border-top-color:var(--teal);width:32px;height:32px;margin:0 auto 14px;"></div>
        <p>Loading services…</p>
      </div>
    </div>
  </div>
</div>

<!-- ════════ ADD MODAL ════════ -->
<div class="modal-backdrop" id="addModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('addModal')">✕</button>
    <div class="modal-title">+ Add New Service</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Service Name *</label>
        <input class="form-control" placeholder="e.g. Water Supply" id="newName">
      </div>
      <div class="form-group">
        <label class="form-label">Category *</label>
        <select class="form-control" id="newCat">
          <option>Water</option><option>Environment</option>
          <option>Lighting</option><option>Infrastructure</option><option>Waste</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea class="form-control" placeholder="Brief description…" id="newDesc"></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Availability Start</label>
        <input type="time" class="form-control" value="08:00" id="newStart">
      </div>
      <div class="form-group">
        <label class="form-label">Availability End</label>
        <input type="time" class="form-control" value="18:00" id="newEnd">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Assigned Agent</label>
        <select class="form-control" id="newAgent">
          <option value="0">— No agent —</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-control" id="newStatus">
          <option>Active</option><option>Inactive</option><option>Maintenance</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('addModal')">Cancel</button>
      <button class="btn btn-primary" id="addBtn" onclick="addService()">
        <svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Add &amp; Generate QR
      </button>
    </div>
  </div>
</div>

<!-- ════════ DETAIL / QR MODAL ════════ -->
<div class="modal-backdrop" id="detailModal">
  <div class="modal detail-modal">
    <button class="modal-close" onclick="closeModal('detailModal')">✕</button>
    <div id="detailContent"></div>
  </div>
</div>

<!-- ════════ EDIT MODAL ════════ -->
<div class="modal-backdrop" id="editModal">
  <div class="modal">
    <button class="modal-close" onclick="closeModal('editModal')">✕</button>
    <div class="modal-title">✏️ Edit Service</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Service Name</label>
        <input class="form-control" id="editName">
      </div>
      <div class="form-group">
        <label class="form-label">Category</label>
        <select class="form-control" id="editCat">
          <option>Water</option><option>Environment</option>
          <option>Lighting</option><option>Infrastructure</option><option>Waste</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea class="form-control" id="editDesc"></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Availability Start</label>
        <input type="time" class="form-control" id="editStart">
      </div>
      <div class="form-group">
        <label class="form-label">Availability End</label>
        <input type="time" class="form-control" id="editEnd">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Assigned Agent</label>
        <select class="form-control" id="editAgent">
          <option value="0">— No agent —</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Status</label>
        <select class="form-control" id="editStatus">
          <option>Active</option><option>Inactive</option><option>Maintenance</option>
        </select>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
      <button class="btn btn-primary" id="editBtn" onclick="saveEdit()">Save Changes</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<!-- QRCode.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
/* ══════════════════════════════════════════════════════
   STATE
══════════════════════════════════════════════════════ */
let allServices = [];
let allAgents   = [];
let activeTab   = '';
let editingId   = null;

const catIcons = {
  Water: `<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>`,
  Environment: `<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 8C8 10 5.9 16.17 3.82 19.99M22.35 4.65A12.47 12.47 0 0 1 17 8"/><path d="M2 9c5-2 5.5-7 11-7 1.7 0 3 .5 4 1"/></svg>`,
  Lighting: `<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="3"/><path d="M5.05 5.05a7 7 0 0 0 0 9.9"/><path d="M18.95 5.05a7 7 0 0 1 0 9.9"/><circle cx="12" cy="13" r="3"/><line x1="12" y1="16" x2="12" y2="21"/><line x1="9" y1="21" x2="15" y2="21"/></svg>`,
  Infrastructure: `<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>`,
  Waste: `<svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.51"/></svg>`,
};
const catClass = { Water:'water', Environment:'clean', Lighting:'light', Infrastructure:'road', Waste:'waste' };

/* ══════════════════════════════════════════════════════
   INIT – load from backend
══════════════════════════════════════════════════════ */
async function loadServices() {
  try {
    const res  = await fetch('api_services.php');
    const data = await res.json();
    allServices = data.services || [];
    allAgents   = data.agents   || [];
    populateAgentDropdowns();
    renderCards(getFiltered());
  } catch(e) {
    showToast('Failed to load services.', 'error');
  }
}

function populateAgentDropdowns() {
  const opts = `<option value="0">— No agent —</option>` +
    allAgents.map(a => `<option value="${a.id}">${a.name}</option>`).join('');
  document.getElementById('newAgent').innerHTML  = opts;
  document.getElementById('editAgent').innerHTML = opts;
}

/* ══════════════════════════════════════════════════════
   RENDER
══════════════════════════════════════════════════════ */
function renderCards(list) {
  const grid = document.getElementById('servicesGrid');

  // Update stats from full list (not filtered)
  document.getElementById('statTotal').textContent    = allServices.length;
  document.getElementById('statActive').textContent   = allServices.filter(s => s.status === 'Active').length;
  document.getElementById('statMaint').textContent    = allServices.filter(s => s.status === 'Maintenance').length;
  document.getElementById('statInactive').textContent = allServices.filter(s => s.status === 'Inactive').length;

  if (!list.length) {
    grid.innerHTML = `<div class="empty-state">
      <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="8" y1="15" x2="16" y2="15"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
      <p>No services found. Try adjusting your filters.</p>
    </div>`;
    return;
  }

  grid.innerHTML = list.map(s => {
    const icon       = catIcons[s.category] || catIcons['Water'];
    const cls        = catClass[s.category]  || 'water';
    const statusCls  = s.status === 'Active' ? 'active' : s.status === 'Inactive' ? 'inactive' : 'maintenance';
    const agentFirst = s.agent_name ? s.agent_name.split(' ')[0] : '—';
    const total      = parseInt(s.total_reports)   || 0;
    const active     = parseInt(s.active_reports)  || 0;
    const overdue    = parseInt(s.overdue_reports)  || 0;

    return `
      <div class="service-card" id="card-${s.id}" onclick="openDetail(${s.id})">
        <div class="card-header">
          <div class="card-icon-wrap">
            <div class="card-icon ${cls}">${icon}</div>
            <div>
              <div class="card-title">${esc(s.name)}</div>
              <div class="card-category">${esc(s.category)}</div>
            </div>
          </div>
          <span class="status-badge ${statusCls}">${esc(s.status)}</span>
        </div>
        <div class="card-body">
          <div class="card-desc">${esc(s.description || '—')}</div>
          <div class="card-meta">
            <div class="meta-item">
              <div class="meta-label">Availability</div>
              <div class="meta-value">${esc(s.availability_start)} – ${esc(s.availability_end)}</div>
            </div>
            <div class="meta-item">
              <div class="meta-label">Agent</div>
              <div class="meta-value">${esc(agentFirst)}</div>
            </div>
          </div>
        </div>
        <div class="card-footer" onclick="event.stopPropagation()">
          <div class="card-stats">
            <div class="card-stat">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
              ${total} Total
            </div>
            <div class="card-stat">
              <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
              ${active} Active
            </div>
            ${overdue > 0 ? `
            <div class="card-stat overdue">
              <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              ${overdue} Overdue
            </div>` : ''}
          </div>
          <div class="card-actions">
            <button class="icon-btn" title="Edit" onclick="openEdit(${s.id})">
              <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button class="icon-btn" title="View QR" onclick="openDetail(${s.id})">
              <svg viewBox="0 0 24 24"><rect x="3" y="3" width="5" height="5"/><rect x="16" y="3" width="5" height="5"/><rect x="3" y="16" width="5" height="5"/><path d="M21 16h-3a2 2 0 0 0-2 2v3"/><line x1="21" y1="21" x2="21" y2="21"/></svg>
            </button>
            <button class="icon-btn danger" title="Delete" onclick="deleteService(${s.id})">
              <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </button>
          </div>
        </div>
      </div>`;
  }).join('');
}

/* ══════════════════════════════════════════════════════
   FILTERING
══════════════════════════════════════════════════════ */
function getFiltered() {
  const search = document.getElementById('searchInput').value.toLowerCase();
  const status = document.getElementById('statusFilter').value;
  const cat    = document.getElementById('catFilter').value;
  return allServices.filter(s => {
    const matchSearch = s.name.toLowerCase().includes(search) || (s.description||'').toLowerCase().includes(search);
    const matchStatus = !status || s.status === status;
    const matchCat    = !cat    || s.category === cat;
    const matchTab    = !activeTab || s.category === activeTab;
    return matchSearch && matchStatus && matchCat && matchTab;
  });
}
function filterCards() { renderCards(getFiltered()); }
function setCat(el, cat) {
  document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  activeTab = cat;
  filterCards();
}

/* ══════════════════════════════════════════════════════
   MODALS
══════════════════════════════════════════════════════ */
function openAddModal() { document.getElementById('addModal').classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-backdrop').forEach(m =>
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); })
);

/* ══════════════════════════════════════════════════════
   ADD SERVICE
══════════════════════════════════════════════════════ */
async function addService() {
  const name = document.getElementById('newName').value.trim();
  if (!name) { showToast('Please enter a service name.', 'error'); return; }

  const btn = document.getElementById('addBtn');
  btn.innerHTML = '<span class="spinner"></span> Saving…';
  btn.disabled = true;

  const body = new FormData();
  body.append('action',    'add');
  body.append('name',      name);
  body.append('category',  document.getElementById('newCat').value);
  body.append('desc',      document.getElementById('newDesc').value);
  body.append('status',    document.getElementById('newStatus').value);
  body.append('start',     document.getElementById('newStart').value);
  body.append('end',       document.getElementById('newEnd').value);
  body.append('agent_id',  document.getElementById('newAgent').value);

  try {
    const res  = await fetch('../actions/services.php', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
      allServices.unshift(data.service);
      document.getElementById('newName').value = '';
      document.getElementById('newDesc').value = '';
      closeModal('addModal');
      renderCards(getFiltered());
      showToast('Service added successfully!', 'success');
      setTimeout(() => openDetail(data.service.id), 200);
    } else {
      showToast(data.message || 'Error adding service.', 'error');
    }
  } catch(e) {
    showToast('Network error. Please try again.', 'error');
  } finally {
    btn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg> Add &amp; Generate QR';
    btn.disabled = false;
  }
}

/* ══════════════════════════════════════════════════════
   EDIT SERVICE
══════════════════════════════════════════════════════ */
function openEdit(id) {
  const s = allServices.find(x => x.id == id);
  if (!s) return;
  editingId = id;
  document.getElementById('editName').value   = s.name;
  document.getElementById('editDesc').value   = s.description || '';
  document.getElementById('editStart').value  = s.availability_start;
  document.getElementById('editEnd').value    = s.availability_end;
  document.getElementById('editStatus').value = s.status;
  document.getElementById('editCat').value    = s.category;
  // Pre-select current agent
  const agentSel = document.getElementById('editAgent');
  agentSel.value = s.agent_id || '0';
  document.getElementById('editModal').classList.add('open');
}

async function saveEdit() {
  const btn = document.getElementById('editBtn');
  btn.innerHTML = '<span class="spinner"></span> Saving…';
  btn.disabled = true;

  const body = new FormData();
  body.append('action',    'edit');
  body.append('id',        editingId);
  body.append('name',      document.getElementById('editName').value);
  body.append('category',  document.getElementById('editCat').value);
  body.append('desc',      document.getElementById('editDesc').value);
  body.append('status',    document.getElementById('editStatus').value);
  body.append('start',     document.getElementById('editStart').value);
  body.append('end',       document.getElementById('editEnd').value);
  body.append('agent_id',  document.getElementById('editAgent').value);

  try {
    const res  = await fetch('../actions/services.php', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
      const idx = allServices.findIndex(x => x.id == editingId);
      if (idx !== -1) allServices[idx] = data.service;
      closeModal('editModal');
      renderCards(getFiltered());
      showToast('Service updated successfully!', 'success');
    } else {
      showToast(data.message || 'Error updating service.', 'error');
    }
  } catch(e) {
    showToast('Network error. Please try again.', 'error');
  } finally {
    btn.innerHTML = 'Save Changes';
    btn.disabled = false;
  }
}

/* ══════════════════════════════════════════════════════
   DELETE SERVICE
══════════════════════════════════════════════════════ */
async function deleteService(id) {
  if (!confirm('Are you sure you want to delete this service?')) return;

  const body = new FormData();
  body.append('action', 'delete');
  body.append('id', id);

  try {
    const res  = await fetch('../actions/services.php', { method: 'POST', body });
    const data = await res.json();
    if (data.success) {
      allServices = allServices.filter(x => x.id != id);
      renderCards(getFiltered());
      showToast('Service deleted.', 'success');
    } else {
      showToast(data.message || 'Error deleting service.', 'error');
    }
  } catch(e) {
    showToast('Network error. Please try again.', 'error');
  }
}

/* ══════════════════════════════════════════════════════
   DETAIL + QR
══════════════════════════════════════════════════════ */
function openDetail(id) {
  const s = allServices.find(x => x.id == id);
  if (!s) return;

  const icon      = catIcons[s.category] || catIcons['Water'];
  const cls       = catClass[s.category]  || 'water';
  const statusCls = s.status === 'Active' ? 'active' : s.status === 'Inactive' ? 'inactive' : 'maintenance';

  document.getElementById('detailContent').innerHTML = `
    <div class="detail-top">
      <div class="card-icon ${cls}" style="width:60px;height:60px;border-radius:14px;flex-shrink:0;">${icon}</div>
      <div class="detail-info">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
          <div class="detail-name">${esc(s.name)}</div>
          <span class="status-badge ${statusCls}">${esc(s.status)}</span>
        </div>
        <div class="detail-cat">${esc(s.category)} · Assigned to ${esc(s.agent_name || '—')}</div>
        <div style="font-size:13px;color:var(--text-muted);margin-top:6px;line-height:1.5;">${esc(s.description || '—')}</div>
      </div>
    </div>
    <div class="section-label">Service Details</div>
    <div class="detail-grid">
      <div class="detail-item"><div class="detail-item-label">Availability</div><div class="detail-item-value">${esc(s.availability_start)} – ${esc(s.availability_end)}</div></div>
      <div class="detail-item"><div class="detail-item-label">Agent</div><div class="detail-item-value">${esc(s.agent_name || '—')}</div></div>
      <div class="detail-item"><div class="detail-item-label">Total Reports</div><div class="detail-item-value">${parseInt(s.total_reports)||0}</div></div>
      <div class="detail-item"><div class="detail-item-label">Active Reports</div><div class="detail-item-value">${parseInt(s.active_reports)||0}</div></div>
    </div>
    <div class="section-label">QR Code</div>
    <div class="qr-section">
      <div class="qr-title">Scan to access full service information</div>
      <div id="qrContainer"></div>
      <div class="qr-download" onclick="downloadQR()">⬇ Download QR Code</div>
    </div>`;

  document.getElementById('detailModal').classList.add('open');

  setTimeout(() => {
    const container = document.getElementById('qrContainer');
    if (!container) return;
    container.innerHTML = '';
    const qrData = [
      `Service: ${s.name}`,
      `Category: ${s.category}`,
      `Agent: ${s.agent_name || 'Unassigned'}`,
      `Availability: ${s.availability_start} - ${s.availability_end}`,
      `Status: ${s.status}`,
      `Description: ${s.description || ''}`,
    ].join('\n');
    new QRCode(container, {
      text: qrData,
      width: 160, height: 160,
      colorDark: '#1a2332', colorLight: '#ffffff',
      correctLevel: QRCode.CorrectLevel.H,
    });
  }, 150);
}

function downloadQR() {
  const img = document.querySelector('#qrContainer img');
  if (!img) return;
  const a = document.createElement('a');
  a.href = img.src;
  a.download = 'service-qrcode.png';
  a.click();
}

/* ══════════════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════════════ */
function esc(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function showToast(msg, type = 'success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className   = `toast ${type} show`;
  setTimeout(() => t.classList.remove('show'), 3000);
}

/* ══════════════════════════════════════════════════════
   BOOT
══════════════════════════════════════════════════════ */
loadServices();
</script>
</body>
</html>