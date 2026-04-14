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
    .nav-logo {
      display: flex;
      align-items: center;
      gap: 9px;
      text-decoration: none;
    }
    .nav-logo-box {
      background: var(--teal);
      border-radius: 7px;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.3px;
    }
    .nav-logo-text {
      color: #fff;
      font-size: 15px;
      font-weight: 500;
    }
    .nav-logo-text span { color: var(--teal); }
    .nav-links {
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .nav-links a {
      color: #9aabb8;
      font-size: 13px;
      padding: 5px 11px;
      text-decoration: none;
      border-radius: var(--radius-sm);
      transition: color .15s, background .15s;
    }
    .nav-links a:hover { color: #fff; background: rgba(255,255,255,0.08); }
    .nav-links a.active { color: #fff; background: rgba(255,255,255,0.12); }
.btn-report {
      background: var(--teal) !important; color: var(--white) !important;
      font-weight: 600 !important; transition: background var(--t) !important;
    }
    .btn-report:hover { background: var(--teal-dark) !important; }

    

    /* ── Page layout ── */
    .page {
      max-width: 1100px;
      margin: 0 auto;
      padding: 30px 24px 48px;
    }

    /* ── Tabs ── */
    .tabs {
      display: flex;
      gap: 0;
      border-bottom: 1.5px solid #e2e5ea;
      margin-bottom: 26px;
    }
    .tab-btn {
      font-size: 14px;
      padding: 9px 20px;
      border: none;
      background: none;
      cursor: pointer;
      color: var(--text-secondary);
      border-bottom: 2.5px solid transparent;
      margin-bottom: -1.5px;
      font-family: inherit;
      border-radius: 0;
      transition: color .15s;
    }
    .tab-btn:hover { color: var(--text-primary); }
    .tab-btn.active {
      color: var(--navy);
      border-bottom-color: var(--teal);
      font-weight: 500;
    }

    /* ── Page header ── */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 22px;
    }
   .page-header h1 {
   font-family: var(--font-head);
   font-size: 28px;
   font-weight: 800;
   color: var(--dark);
   letter-spacing: -0.5px;
   margin-bottom: 4px;

   line-height: 1.2;              /* évite chevauchement */
   overflow-wrap: break-word;     /* casse les mots longs */
   white-space: normal;           /* autorise retour ligne */
}
    .page-header p {
      font-size: 13px;
      color: var(--text-secondary);
      margin-top: 3px;
    }
    .page-header p strong { font-weight: 600; color: var(--text-primary); }
    .btn-export {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 13px;
      padding: 8px 15px;
      border: 1px solid #dde1e8;
      border-radius: var(--radius-md);
      background: var(--bg-white);
      cursor: pointer;
      color: var(--text-primary);
      font-family: inherit;
      transition: border-color .15s, background .15s;
      white-space: nowrap;
    }
    .btn-export:hover { border-color: #b0b8c6; background: #fafbfc; }
    .btn-export svg { flex-shrink: 0; }

    /* ── Stat cards ── */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 10px;
      margin-bottom: 18px;
    }
    .stat-card {
      background: var(--bg-white);
      border-radius: var(--radius-md);
      padding: 14px 16px;
      border-left: 3px solid #ccc;
      border-top: 1px solid var(--border-light);
      border-right: 1px solid var(--border-light);
      border-bottom: 1px solid var(--border-light);
    }
    .stat-card.c-navy  { border-left-color: var(--navy); }
    .stat-card.c-blue  { border-left-color: #3b82f6; }
    .stat-card.c-orange{ border-left-color: #f97316; }
    .stat-card.c-green { border-left-color: #22c55e; }
    .stat-card.c-red   { border-left-color: #ef4444; }
    .stat-label {
      font-size: 10px;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      font-weight: 500;
    }
    .stat-number {
      font-size: 28px;
      font-weight: 600;
      color: var(--navy);
      line-height: 1.1;
      margin: 3px 0 2px;
    }
    .stat-sub {
      font-size: 11px;
      color: var(--text-muted);
    }

    /* ── Alert banner ── */
    .alert-banner {
      display: flex;
      align-items: flex-start;
      gap: 11px;
      background: #fff5f5;
      border: 1px solid #fca5a5;
      border-radius: var(--radius-md);
      padding: 12px 16px;
      margin-bottom: 18px;
    }
    .alert-banner svg { flex-shrink: 0; margin-top: 1px; }
    .alert-banner p { font-size: 13px; color: #7f1d1d; }
    .alert-banner strong { font-weight: 600; }

    /* ── Controls ── */
    .controls {
      display: flex;
      gap: 10px;
      margin-bottom: 14px;
    }
    .search-wrap {
      flex: 1;
      position: relative;
    }
    .search-wrap svg {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      pointer-events: none;
      color: var(--text-muted);
    }
    .search-input {
      width: 100%;
      padding: 9px 14px 9px 34px;
      border: 1px solid #dde1e8;
      border-radius: var(--radius-md);
      font-size: 13px;
      background: var(--bg-white);
      color: var(--text-primary);
      font-family: inherit;
      transition: border-color .15s;
      outline: none;
    }
    .search-input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(46,196,160,0.12); }
    .filter-select {
      padding: 9px 12px;
      border: 1px solid #dde1e8;
      border-radius: var(--radius-md);
      font-size: 13px;
      background: var(--bg-white);
      color: var(--text-primary);
      font-family: inherit;
      outline: none;
      cursor: pointer;
      transition: border-color .15s;
    }
    .filter-select:focus { border-color: var(--teal); }

    /* ── Table ── */
    .table-wrap {
      background: var(--bg-white);
      border-radius: var(--radius-lg);
      border: 1px solid #e2e5ea;
      overflow: hidden;
    }
    .table-head {
      display: grid;
      grid-template-columns: 52px 64px minmax(0, 1fr) 130px 108px 100px 155px;
      padding: 10px 18px;
      border-bottom: 1px solid #e8ebf0;
      background: var(--bg-surface);
    }
    .th {
      font-size: 11px;
      font-weight: 600;
      color: var(--text-secondary);
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .table-body { }
    .table-row {
      display: grid;
      grid-template-columns: 52px 64px minmax(0, 1fr) 130px 108px 100px 155px;
      padding: 11px 18px;
      border-bottom: 1px solid #f0f2f5;
      align-items: center;
      transition: background .12s;
    }
    .table-row:last-child { border-bottom: none; }
    .table-row:hover { background: #fafbfd; }

    .cell-id {
      font-size: 13px;
      font-weight: 600;
      color: var(--teal);
    }
    .cell-icon {
      width: 44px;
      height: 36px;
      border-radius: 7px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .cell-title {
      font-size: 13px;
      color: var(--text-primary);
      font-weight: 500;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .cell-cat {
      font-size: 11px;
      color: var(--text-muted);
      margin-top: 2px;
    }
    .cell-loc {
      font-size: 12px;
      color: var(--text-secondary);
    }
    .cell-date {
      font-size: 12px;
      color: var(--text-secondary);
    }

    /* ── Badges ── */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 11px;
      font-weight: 500;
      padding: 3px 9px;
      border-radius: 20px;
      white-space: nowrap;
    }
    .badge-dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      flex-shrink: 0;
    }
    .badge-pending   { background: #eff6ff; color: #1d4ed8; }
    .badge-pending   .badge-dot { background: #3b82f6; }
    .badge-progress  { background: #fff7ed; color: #c2410c; }
    .badge-progress  .badge-dot { background: #f97316; }
    .badge-done      { background: #f0fdf4; color: #166534; }
    .badge-done      .badge-dot { background: #22c55e; }
    .badge-overdue   { background: #fff1f2; color: #be123c; }
    .badge-overdue   .badge-dot { background: #ef4444; }
    .badge-cancelled { background: #f1f5f9; color: #475569; }
    .badge-cancelled .badge-dot { background: #94a3b8; }

    /* ── Action buttons ── */
    .actions {
      display: flex;
      gap: 5px;
      flex-wrap: wrap;
    }
    .btn {
      font-size: 12px;
      padding: 5px 10px;
      border-radius: var(--radius-sm);
      cursor: pointer;
      font-family: inherit;
      font-weight: 500;
      border: 1px solid;
      transition: opacity .15s, background .15s;
      white-space: nowrap;
    }
    .btn:hover { opacity: 0.85; }
    .btn-view    { border-color: #dde1e8; background: #fff; color: var(--text-primary); }
    .btn-cancel  { border-color: #fca5a5; background: #fff5f5; color: #b91c1c; }
    .btn-rate    { border-color: #86efac; background: #f0fdf4; color: #166534; display: flex; align-items: center; gap: 4px; }
    .btn-follow  { border-color: #fed7aa; background: #fff7ed; color: #c2410c; display: flex; align-items: center; gap: 4px; }

    /* ── Table footer ── */
    .table-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 13px 18px;
      border-top: 1px solid #f0f2f5;
    }
    .footer-info { font-size: 12px; color: var(--text-secondary); }
    .page-num {
      width: 30px;
      height: 30px;
      border-radius: var(--radius-sm);
      background: var(--teal);
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ── Empty state ── */
    .empty-state {
      padding: 40px 20px;
      text-align: center;
      color: var(--text-secondary);
      font-size: 13px;
    }
    .empty-state svg { margin-bottom: 10px; color: var(--text-muted); }

    /* ── Modal overlay ── */
    .modal-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.48);
      z-index: 200;
      align-items: center;
      justify-content: center;
    }
    .modal-overlay.open { display: flex; }

    .modal {
      background: var(--bg-white);
      border-radius: var(--radius-lg);
      padding: 26px;
      width: 380px;
      max-width: calc(100vw - 32px);
      border: 1px solid #e2e5ea;
      animation: modal-in .18s ease;
    }
    @keyframes modal-in {
      from { opacity: 0; transform: translateY(-10px) scale(.97); }
      to   { opacity: 1; transform: none; }
    }
    .modal h2 { font-size: 17px; font-weight: 600; color: var(--navy); margin-bottom: 6px; }
    .modal-sub { font-size: 13px; color: var(--text-secondary); margin-bottom: 20px; }

    /* Rating modal */
    .svc-pill {
      display: flex;
      align-items: center;
      gap: 9px;
      background: var(--bg-surface);
      border: 1px solid #e8ebf0;
      border-radius: var(--radius-md);
      padding: 9px 13px;
      font-size: 13px;
      font-weight: 500;
      color: var(--navy);
      margin-bottom: 18px;
    }
    .stars-row {
      display: flex;
      gap: 6px;
      margin-bottom: 18px;
    }
    .star-btn {
      font-size: 28px;
      cursor: pointer;
      color: #d1d5db;
      background: none;
      border: none;
      padding: 0;
      line-height: 1;
      transition: color .1s, transform .1s;
    }
    .star-btn:hover,
    .star-btn.lit { color: #f59e0b; }
    .star-btn:hover { transform: scale(1.15); }
    .modal-textarea {
      width: 100%;
      height: 82px;
      padding: 10px 13px;
      border: 1px solid #dde1e8;
      border-radius: var(--radius-md);
      font-size: 13px;
      font-family: inherit;
      resize: none;
      color: var(--text-primary);
      background: var(--bg-white);
      outline: none;
      transition: border-color .15s;
    }
    .modal-textarea:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(46,196,160,0.12); }
    .modal-actions {
      display: flex;
      gap: 9px;
      justify-content: flex-end;
      margin-top: 16px;
    }
    .btn-modal-close {
      font-size: 13px;
      padding: 8px 17px;
      border: 1px solid #dde1e8;
      border-radius: var(--radius-md);
      background: none;
      cursor: pointer;
      color: var(--text-secondary);
      font-family: inherit;
      font-weight: 500;
      transition: background .15s;
    }
    .btn-modal-close:hover { background: var(--bg-surface); }
    .btn-modal-submit {
      font-size: 13px;
      padding: 8px 20px;
      border: none;
      border-radius: var(--radius-md);
      background: var(--teal);
      cursor: pointer;
      color: #fff;
      font-family: inherit;
      font-weight: 500;
      transition: background .15s;
    }
    .btn-modal-submit:hover { background: var(--teal-dark); }

    /* Cancel modal */
    .cancel-warn {
      background: #fff5f5;
      border: 1px solid #fca5a5;
      border-radius: var(--radius-md);
      padding: 11px 14px;
      font-size: 12px;
      color: #7f1d1d;
      margin-bottom: 18px;
      display: flex;
      gap: 9px;
      align-items: flex-start;
    }
    .cancel-warn svg { flex-shrink: 0; margin-top: 1px; }
    .btn-confirm-cancel {
      font-size: 13px;
      padding: 8px 20px;
      border: none;
      border-radius: var(--radius-md);
      background: #ef4444;
      cursor: pointer;
      color: #fff;
      font-family: inherit;
      font-weight: 500;
      transition: background .15s;
    }
    .btn-confirm-cancel:hover { background: #dc2626; }

    /* Success modal */
    .success-icon {
      width: 52px;
      height: 52px;
      background: #f0fdf4;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
    }
    .modal-centered { text-align: center; }
    .modal-centered h2 { margin-bottom: 8px; }
    .modal-centered .modal-sub { margin-bottom: 22px; }
    .btn-full {
      width: 100%;
      font-size: 14px;
      padding: 10px;
      border: none;
      border-radius: var(--radius-md);
      background: var(--teal);
      cursor: pointer;
      color: #fff;
      font-family: inherit;
      font-weight: 500;
      transition: background .15s;
    }
    .btn-full:hover { background: var(--teal-dark); }

    /* Detail modal */
    .detail-header {
      display: flex;
      align-items: center;
      gap: 13px;
      margin-bottom: 20px;
    }
    .detail-icon {
      width: 48px;
      height: 42px;
      border-radius: 9px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .detail-rows { border-top: 1px solid #f0f2f5; padding-top: 14px; }
    .detail-row {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      padding: 7px 0;
      border-bottom: 1px solid #f8f9fb;
      font-size: 13px;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-lbl { color: var(--text-secondary); flex-shrink: 0; }
    .detail-val { color: var(--text-primary); font-weight: 500; text-align: right; }

    /* Responsive */
    @media (max-width: 860px) {
      .stats-grid { grid-template-columns: repeat(3, 1fr); }
      .table-head,
      .table-row { grid-template-columns: 44px minmax(0,1fr) 90px 80px 140px; }
      .table-head .th:nth-child(2),
      .table-row .cell-icon { display: none; }
      .table-head .th:nth-child(4),
      .table-row .cell-loc { display: none; }
    }
    @media (max-width: 600px) {
      .page { padding: 20px 14px 40px; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
      .controls { flex-wrap: wrap; }
      .filter-select { flex: 1; }
      .nav-links { display: none; }
    }
    a {
    text-decoration: none;
 }
  </style>
</head>
<body>

<!-- ── Navbar ── -->
<nav class="nav">
  <a href="#" class="nav-logo">
    <div class="nav-logo-box">SH</div>
    <span class="nav-logo-text">Salah<span>Homty</span></span>
  </a>
  <div class="nav-links">
    <a href="../index.php" class="btn-report">Report a problem</a>
    <a href="#" class = "active">History</a>
    <a href="helppage.php">Help</a>
    
    <a href="services.php" >Services</a>
    <a href="profil.php">Profile</a>
    
  </div>
</nav>

<!-- ── Main page ── -->
<main class="page">

  <!-- Tabs -->
  <div class="tabs">
    <a href ="history.php" class="tab-btn" >My reports </a>
    <a href="#" class="tab-btn active" > My service requests</a>
  </div>

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1>My service requests</h1>
      <p>All service requests submitted by <strong>Hamza Agent</strong> and their current status.</p>
    </div>
    <button class="btn-export" onclick="exportCSV()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/>
      </svg>
      Export CSV
    </button>
  </div>

  <!-- Stat cards -->
  <div class="stats-grid">
    <div class="stat-card c-navy">
      <div class="stat-label">Total</div>
      <div class="stat-number" id="cnt-all">6</div>
      <div class="stat-sub">requests</div>
    </div>
    <div class="stat-card c-blue">
      <div class="stat-label">Pending</div>
      <div class="stat-number" id="cnt-pending">2</div>
      <div class="stat-sub">awaiting review</div>
    </div>
    <div class="stat-card c-orange">
      <div class="stat-label">In progress</div>
      <div class="stat-number" id="cnt-progress">1</div>
      <div class="stat-sub">being processed</div>
    </div>
    <div class="stat-card c-green">
      <div class="stat-label">Closed</div>
      <div class="stat-number" id="cnt-done">2</div>
      <div class="stat-sub">completed</div>
    </div>
    <div class="stat-card c-red">
      <div class="stat-label">Overdue</div>
      <div class="stat-number" id="cnt-overdue">1</div>
      <div class="stat-sub">deadline exceeded</div>
    </div>
  </div>

  <!-- Alert banner -->
  <div class="alert-banner" id="overdue-banner">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
      <line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <p><strong>1 request has exceeded the processing deadline.</strong> Contact your local authority if you need an urgent follow-up.</p>
  </div>

  <!-- Controls -->
  <div class="controls">
    <div class="search-wrap">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
      </svg>
      <input class="search-input" id="searchInput" type="text"
        placeholder="Search by ID, description, category..." oninput="filterRows()" />
    </div>
    <select class="filter-select" id="statusFilter" onchange="filterRows()">
      <option value="">All statuses</option>
      <option value="pending">Pending</option>
      <option value="progress">In progress</option>
      <option value="done">Closed</option>
      <option value="overdue">Overdue</option>
      <option value="cancelled">Cancelled</option>
    </select>
    <select class="filter-select" id="catFilter" onchange="filterRows()">
      <option value="">All categories</option>
      <option value="admin">Administrative</option>
      <option value="infra">Infrastructure</option>
      <option value="sanitation">Sanitation</option>
      <option value="social">Social services</option>
    </select>
  </div>

  <!-- Table -->
  <div class="table-wrap">
    <div class="table-head">
      <div class="th">ID</div>
      <div class="th">Icon</div>
      <div class="th">Service</div>
      <div class="th">Location</div>
      <div class="th">Date</div>
      <div class="th">Status</div>
      <div class="th">Action</div>
    </div>
    <div class="table-body" id="tableBody"></div>
    <div class="table-footer">
      <span class="footer-info" id="footerInfo">Showing 1–6 of 6 requests</span>
      <div class="page-num">1</div>
    </div>
  </div>

</main>

<!-- ── Rating Modal ── -->
<div class="modal-overlay" id="rateModal">
  <div class="modal">
    <h2>Rate this service</h2>
    <p class="modal-sub">Share your experience to help improve municipal services.</p>
    <div class="svc-pill" id="rateSvcPill">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2ec4a0" stroke-width="1.8">
        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
      </svg>
      <span id="rateSvcName">Service name</span>
    </div>
    <div class="stars-row" id="starsRow">
      <button class="star-btn" onclick="rate(1)">&#9733;</button>
      <button class="star-btn" onclick="rate(2)">&#9733;</button>
      <button class="star-btn" onclick="rate(3)">&#9733;</button>
      <button class="star-btn" onclick="rate(4)">&#9733;</button>
      <button class="star-btn" onclick="rate(5)">&#9733;</button>
    </div>
    <textarea class="modal-textarea" id="rateComment"
      placeholder="Leave a comment (optional) — what went well, what could be improved?"></textarea>
    <div class="modal-actions">
      <button class="btn-modal-close" onclick="closeModal('rateModal')">Cancel</button>
      <button class="btn-modal-submit" onclick="submitRating()">Submit rating</button>
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
      <button class="btn-confirm-cancel" onclick="confirmCancel()">Yes, cancel it</button>
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
    <h2 id="successTitle">Rating submitted!</h2>
    <p class="modal-sub" id="successMsg">Thank you for your feedback. It helps improve municipal services.</p>
    <button class="btn-full" onclick="closeModal('successModal')">Done</button>
  </div>
</div>

<!-- ── Detail Modal ── -->
<div class="modal-overlay" id="detailModal">
  <div class="modal" style="width:420px">
    <h2 id="detailTitle">Request details</h2>
    <div class="detail-header" style="margin-top:14px">
      <div class="detail-icon" id="detailIcon"></div>
      <div>
        <div style="font-size:14px;font-weight:600;color:var(--navy)" id="detailSvcTitle"></div>
        <div style="font-size:12px;color:var(--text-secondary);margin-top:3px" id="detailSvcCat"></div>
      </div>
    </div>
    <div class="detail-rows" id="detailRows"></div>
    <div class="modal-actions" style="margin-top:20px">
      <button class="btn-modal-close" onclick="closeModal('detailModal')">Close</button>
    </div>
  </div>
</div>

<script>
  /* ── Data ── */
  const REQUESTS = [
    {
      id: '#6', icon: 'admin', bg: '#e0f2fe', stroke: '#0ea5e9',
      title: 'Building permit application', cat: 'Administrative', catKey: 'admin',
      loc: 'Sfax, Tunisia', date: 'Apr 10, 2026', status: 'pending',
      canCancel: true, canRate: false, canFollow: false, rated: false,
      submittedBy: 'Hamza Agent', ref: 'SRQ-2026-006'
    },
    {
      id: '#5', icon: 'infra', bg: '#fff7ed', stroke: '#f97316',
      title: 'Street lighting repair', cat: 'Infrastructure', catKey: 'infra',
      loc: '35.8120, 10.6210', date: 'Apr 4, 2026', status: 'done',
      canCancel: false, canRate: true, canFollow: false, rated: false,
      submittedBy: 'Hamza Agent', ref: 'SRQ-2026-005'
    },
    {
      id: '#4', icon: 'sanitation', bg: '#f0fdf4', stroke: '#22c55e',
      title: 'Waste collection request', cat: 'Sanitation', catKey: 'sanitation',
      loc: '35.8129, 10.6255', date: 'Mar 31, 2026', status: 'progress',
      canCancel: false, canRate: false, canFollow: false, rated: false,
      submittedBy: 'Hamza Agent', ref: 'SRQ-2026-004'
    },
    {
      id: '#3', icon: 'social', bg: '#faf5ff', stroke: '#8b5cf6',
      title: 'Social housing assistance', cat: 'Social services', catKey: 'social',
      loc: '35.6701, 10.9008', date: 'Mar 29, 2026', status: 'overdue',
      canCancel: false, canRate: false, canFollow: true, rated: false,
      submittedBy: 'Hamza Agent', ref: 'SRQ-2026-003'
    },
    {
      id: '#2', icon: 'admin', bg: '#eff6ff', stroke: '#3b82f6',
      title: 'Residence certificate', cat: 'Administrative', catKey: 'admin',
      loc: '—', date: 'Mar 20, 2026', status: 'done',
      canCancel: false, canRate: true, canFollow: false, rated: false,
      submittedBy: 'Hamza Agent', ref: 'SRQ-2026-002'
    },
    {
      id: '#1', icon: 'infra', bg: '#fff7ed', stroke: '#f97316',
      title: 'Road repair — pothole', cat: 'Infrastructure', catKey: 'infra',
      loc: '35.6701, 10.9008', date: 'Mar 15, 2026', status: 'pending',
      canCancel: true, canRate: false, canFollow: false, rated: false,
      submittedBy: 'Hamza Agent', ref: 'SRQ-2026-001'
    }
  ];

  /* ── SVG icons per category ── */
  function getIcon(type, stroke, size = 18) {
    const s = `width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="${stroke}" stroke-width="1.8"`;
    const paths = {
      admin: `<svg ${s}><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M8 6V5a2 2 0 012-2h4a2 2 0 012 2v1"/></svg>`,
      infra: `<svg ${s}><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>`,
      sanitation: `<svg ${s}><path d="M3 6h18l-1.5 12a2 2 0 01-2 1.8H6.5a2 2 0 01-2-1.8L3 6z"/><path d="M8 6V4a1 1 0 011-1h6a1 1 0 011 1v2"/></svg>`,
      social: `<svg ${s}><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>`
    };
    return paths[type] || paths.admin;
  }

  /* ── Badge HTML ── */
  function badge(status) {
    const map = {
      pending:   ['badge-pending',   'Pending'],
      progress:  ['badge-progress',  'In progress'],
      done:      ['badge-done',      'Closed'],
      overdue:   ['badge-overdue',   'Overdue'],
      cancelled: ['badge-cancelled', 'Cancelled']
    };
    const [cls, label] = map[status] || ['badge-cancelled', status];
    return `<span class="badge ${cls}"><span class="badge-dot"></span>${label}</span>`;
  }

  /* ── Render rows ── */
  function renderRows(data) {
    const body = document.getElementById('tableBody');
    if (!data.length) {
      body.innerHTML = `<div class="empty-state">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
        </svg>
        <p>No requests match your filters.</p>
      </div>`;
      document.getElementById('footerInfo').textContent = 'No results';
      return;
    }

    body.innerHTML = data.map(r => {
      const icon = getIcon(r.icon, r.stroke);
      const actions = [];
      actions.push(`<button class="btn btn-view" onclick="openDetail('${r.id}')">View details</button>`);
      if (r.canCancel) {
        actions.push(`<button class="btn btn-cancel" onclick="openCancel('${r.id}')">Cancel</button>`);
      }
      if (r.canRate && !r.rated) {
        actions.push(`<button class="btn btn-rate" onclick="openRate('${r.id}')">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b" stroke="none">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg> Rate</button>`);
      }
      if (r.canFollow) {
        actions.push(`<button class="btn btn-follow" onclick="followUp('${r.id}')">Follow up</button>`);
      }

      return `<div class="table-row">
        <div class="cell-id">${r.id}</div>
        <div class="cell-icon" style="background:${r.bg}">${icon}</div>
        <div>
          <div class="cell-title">${r.title}</div>
          <div class="cell-cat">${r.cat}</div>
        </div>
        <div class="cell-loc">${r.loc}</div>
        <div class="cell-date">${r.date}</div>
        <div>${badge(r.status)}</div>
        <div class="actions">${actions.join('')}</div>
      </div>`;
    }).join('');

    document.getElementById('footerInfo').textContent =
      `Showing 1–${data.length} of ${REQUESTS.length} requests`;
  }

  /* ── Filter ── */
  function filterRows() {
    const q  = document.getElementById('searchInput').value.toLowerCase();
    const st = document.getElementById('statusFilter').value;
    const ct = document.getElementById('catFilter').value;
    const filtered = REQUESTS.filter(r => {
      const matchQ = !q || r.title.toLowerCase().includes(q)
                        || r.id.includes(q)
                        || r.cat.toLowerCase().includes(q);
      const matchS = !st || r.status === st;
      const matchC = !ct || r.catKey === ct;
      return matchQ && matchS && matchC;
    });
    renderRows(filtered);
  }

  /* ── Modal helpers ── */
  function openModal(id)  { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }

  /* Close on overlay click */
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) overlay.classList.remove('open');
    });
  });

  /* ── Rate ── */
  let currentRateId = null;
  function openRate(id) {
    currentRateId = id;
    const r = REQUESTS.find(x => x.id === id);
    document.getElementById('rateSvcName').textContent = r.title;
    document.querySelectorAll('.star-btn').forEach(s => s.classList.remove('lit'));
    document.getElementById('rateComment').value = '';
    openModal('rateModal');
  }
  function rate(n) {
    document.querySelectorAll('.star-btn').forEach((s, i) => s.classList.toggle('lit', i < n));
  }
  function submitRating() {
    const r = REQUESTS.find(x => x.id === currentRateId);
    if (r) r.rated = true;
    closeModal('rateModal');
    document.getElementById('successTitle').textContent = 'Rating submitted!';
    document.getElementById('successMsg').textContent = 'Thank you for your feedback. It helps improve municipal services.';
    openModal('successModal');
    filterRows();
  }

  /* ── Cancel ── */
  let currentCancelId = null;
  function openCancel(id) {
    currentCancelId = id;
    const r = REQUESTS.find(x => x.id === id);
    document.getElementById('cancelSvcName').textContent = '"' + r.title + '"';
    openModal('cancelModal');
  }
  function confirmCancel() {
    const r = REQUESTS.find(x => x.id === currentCancelId);
    if (r) { r.status = 'cancelled'; r.canCancel = false; }
    closeModal('cancelModal');
    document.getElementById('successTitle').textContent = 'Request cancelled';
    document.getElementById('successMsg').textContent = 'Your service request has been successfully cancelled.';
    openModal('successModal');
    updateStats();
    filterRows();
  }

  /* ── Follow up ── */
  function followUp(id) {
    const r = REQUESTS.find(x => x.id === id);
    alert(`Follow-up sent for: ${r.title}\n\nYour local authority has been notified.`);
  }

  /* ── Detail ── */
  function openDetail(id) {
    const r = REQUESTS.find(x => x.id === id);
    document.getElementById('detailTitle').textContent = 'Request ' + r.id;
    document.getElementById('detailSvcTitle').textContent = r.title;
    document.getElementById('detailSvcCat').textContent = r.cat;
    const iconEl = document.getElementById('detailIcon');
    iconEl.style.background = r.bg;
    iconEl.innerHTML = getIcon(r.icon, r.stroke, 20);

    const statusLabels = {
      pending: 'Pending', progress: 'In progress', done: 'Closed',
      overdue: 'Overdue', cancelled: 'Cancelled'
    };

    document.getElementById('detailRows').innerHTML = [
      ['Reference', r.ref],
      ['Submitted by', r.submittedBy],
      ['Date submitted', r.date],
      ['Location', r.loc],
      ['Status', badge(r.status)],
      ['Category', r.cat]
    ].map(([lbl, val]) => `
      <div class="detail-row">
        <span class="detail-lbl">${lbl}</span>
        <span class="detail-val">${val}</span>
      </div>`).join('');

    openModal('detailModal');
  }

  /* ── Tab switch ── */
  function switchTab(idx, el) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    el.classList.add('active');
    if (idx === 0) {
      window.location.href = 'reports.html';
    }
  }

  /* ── Update stats ── */
  function updateStats() {
    const count = s => REQUESTS.filter(r => r.status === s).length;
    document.getElementById('cnt-all').textContent     = REQUESTS.length;
    document.getElementById('cnt-pending').textContent = count('pending');
    document.getElementById('cnt-progress').textContent= count('progress');
    document.getElementById('cnt-done').textContent    = count('done');
    document.getElementById('cnt-overdue').textContent = count('overdue');
    const overdue = count('overdue');
    const banner  = document.getElementById('overdue-banner');
    banner.style.display = overdue > 0 ? 'flex' : 'none';
    banner.querySelector('p').innerHTML = `<strong>${overdue} request${overdue > 1 ? 's have' : ' has'} exceeded the processing deadline.</strong> Contact your local authority if you need an urgent follow-up.`;
  }

  /* ── Export CSV ── */
  function exportCSV() {
    const header = ['ID', 'Title', 'Category', 'Location', 'Date', 'Status'];
    const rows = REQUESTS.map(r => [r.id, r.title, r.cat, r.loc, r.date, r.status]);
    const csv = [header, ...rows].map(row => row.map(v => `"${v}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'service-requests.csv';
    a.click();
  }

  /* ── Keyboard: close modals on Escape ── */
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
    }
  });

  /* ── Init ── */
  updateStats();
  renderRows(REQUESTS);
</script>
</body>
</html>