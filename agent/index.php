<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Agent Portal — Municipality</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
  :root {
    --bg:#f0f2f5;
    --sidebar:#111827;
    --sidebar-hover:#1f2937;
    --sidebar-active:#14b8a6;
    --accent:#14b8a6;
    --accent-dark:#0d9488;
    --accent-light:rgba(20,184,166,.12);
    --white:#ffffff;
    --border:#e5e7eb;
    --text:#111827;
    --muted:#6b7280;
    --muted2:#9ca3af;
    --green:#10b981;
    --yellow:#f59e0b;
    --red:#ef4444;
    --blue:#3b82f6;
    --r:12px;
    --sw:220px;
  }
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Poppins',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;overflow-x:hidden;font-size:14px}

  /* SIDEBAR */
  .sidebar{width:var(--sw);min-height:100vh;background:var(--sidebar);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:100}
  .sb-logo{padding:20px 18px 16px;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.07)}
  .logo-icon{width:36px;height:36px;background:var(--accent);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
  .logo-text{font-size:15px;font-weight:700;color:#fff;letter-spacing:-.2px}
  .logo-sub{font-size:10px;color:rgba(255,255,255,.4);letter-spacing:.3px}

  .nav{flex:1;padding:10px 10px;overflow-y:auto}
  .nav-section{font-size:9px;color:rgba(255,255,255,.3);letter-spacing:1.5px;font-weight:600;padding:14px 8px 5px;text-transform:uppercase}
  .nav-item{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:8px;font-size:13px;color:rgba(255,255,255,.55);cursor:pointer;transition:all .15s;margin-bottom:2px;position:relative;font-weight:500}
  .nav-item:hover{background:var(--sidebar-hover);color:rgba(255,255,255,.9)}
  .nav-item.active{background:var(--accent);color:#fff}
  .nav-icon{font-size:14px;width:16px;text-align:center;flex-shrink:0}
  .nav-badge{margin-left:auto;font-size:10px;font-weight:700;padding:1px 7px;border-radius:99px;background:rgba(255,255,255,.15);color:#fff}
  .nav-item.active .nav-badge{background:rgba(0,0,0,.2)}

  .agent-card-sb{margin:10px 10px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:10px;padding:10px;display:flex;align-items:center;gap:9px;cursor:pointer;transition:background .2s}
  .agent-card-sb:hover{background:rgba(255,255,255,.09)}
  .avatar-sm{width:34px;height:34px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:#fff;flex-shrink:0}
  .agent-name-sb{font-size:12px;font-weight:600;color:#fff}
  .agent-role-sb{font-size:10px;color:rgba(255,255,255,.4);margin-top:1px}
  .status-dot{width:6px;height:6px;border-radius:50%;background:var(--green);box-shadow:0 0 5px var(--green);flex-shrink:0}

  .sb-footer{padding:10px;border-top:1px solid rgba(255,255,255,.07)}
  .logout-btn{display:flex;align-items:center;gap:9px;padding:9px 10px;border-radius:8px;font-size:12px;color:rgba(255,255,255,.5);cursor:pointer;background:none;border:none;width:100%;transition:all .15s;font-family:'Poppins',sans-serif;font-weight:500}
  .logout-btn:hover{background:rgba(239,68,68,.15);color:#f87171}

  /* MAIN */
  .main{margin-left:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh}

  /* TOPBAR */
  .topbar{height:58px;background:var(--white);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px;padding:0 24px;position:sticky;top:0;z-index:90}
  .topbar-title{font-size:18px;font-weight:700;flex:1;color:var(--text)}
  .search-box{display:flex;align-items:center;gap:8px;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:0 12px;height:36px;width:220px}
  .search-box input{background:none;border:none;outline:none;color:var(--text);font-family:'Poppins',sans-serif;font-size:12px;flex:1}
  .search-box input::placeholder{color:var(--muted2)}
  .top-btn{height:36px;padding:0 16px;border-radius:8px;background:var(--accent);color:#fff;border:none;font-family:'Poppins',sans-serif;font-size:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s;white-space:nowrap}
  .top-btn:hover{background:var(--accent-dark)}
  .icon-btn{width:36px;height:36px;border-radius:8px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;position:relative;transition:background .15s}
  .icon-btn:hover{background:#e5e7eb}
  .notif-dot{position:absolute;top:5px;right:5px;width:6px;height:6px;border-radius:50%;background:var(--red);border:1.5px solid var(--white)}

  /* CONTENT */
  .content{padding:24px;flex:1;animation:fadeIn .25s ease}
  @keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
  .page{display:none}
  .page.active{display:block}

  /* STATS */
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

  /* LAYOUT */
  .grid2{display:grid;grid-template-columns:1fr 300px;gap:18px;margin-bottom:18px}
  .panel{background:var(--white);border:1px solid var(--border);border-radius:var(--r);overflow:hidden}
  .panel-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
  .panel-title{font-size:15px;font-weight:700;flex:1;color:var(--text)}
  .panel-link{font-size:12px;color:var(--accent);font-weight:600;cursor:pointer;text-decoration:none;white-space:nowrap}
  .panel-link:hover{color:var(--accent-dark)}

  /* FILTER TABS */
  .filter-tabs{display:flex;gap:4px;padding:12px 20px;border-bottom:1px solid var(--border)}
  .ftab{font-size:12px;padding:5px 14px;border-radius:99px;cursor:pointer;transition:all .15s;border:1px solid transparent;color:var(--muted);font-weight:500}
  .ftab:hover{color:var(--text);background:var(--bg)}
  .ftab.active{background:var(--accent-light);color:var(--accent);border-color:rgba(20,184,166,.25);font-weight:600}

  /* TABLE */
  .table-wrap{overflow-x:auto}
  table{width:100%;border-collapse:collapse}
  thead th{font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.7px;font-weight:600;padding:10px 16px;text-align:left;border-bottom:1px solid var(--border);background:#fafafa}
  tbody tr{border-bottom:1px solid var(--border);transition:background .12s;cursor:pointer}
  tbody tr:last-child{border-bottom:none}
  tbody tr:hover{background:#f9fafb}
  tbody td{padding:12px 16px;font-size:13px;vertical-align:middle}

  .c-id{font-size:12px;color:var(--accent);font-weight:600}
  .c-title{font-weight:500;color:var(--text)}
  .c-sub{font-size:11px;color:var(--muted);margin-top:2px}

  .cat-tag{font-size:11px;padding:3px 9px;border-radius:5px;font-weight:500;white-space:nowrap}
  .cat-voirie{background:#eff6ff;color:var(--blue)}
  .cat-eclairage{background:#fffbeb;color:var(--yellow)}
  .cat-proprete{background:#f0fdf4;color:var(--green)}
  .cat-eau{background:#ecfeff;color:#0891b2}
  .cat-autre{background:#f9fafb;color:var(--muted)}

  .st-badge{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;padding:4px 10px;border-radius:99px;white-space:nowrap}
  .st-badge .dot{width:6px;height:6px;border-radius:50%}
  .st-pending{background:#fffbeb;color:#b45309}.st-pending .dot{background:#f59e0b}
  .st-progress{background:#eff6ff;color:#1d4ed8}.st-progress .dot{background:var(--blue);animation:pulse 1.5s infinite}
  .st-resolved{background:#f0fdf4;color:#065f46}.st-resolved .dot{background:var(--green)}
  .st-open{background:#f0f9ff;color:#0369a1}.st-open .dot{background:#0ea5e9}
  @keyframes pulse{0%,100%{opacity:1}50%{opacity:.35}}

  .p-dot{width:8px;height:8px;border-radius:50%;display:inline-block}
  .p-high{background:var(--red)}
  .p-medium{background:var(--yellow)}
  .p-low{background:var(--green)}

  .act-btns{display:flex;gap:5px}
  .act-btn{height:28px;padding:0 10px;border-radius:6px;border:1px solid var(--border);background:var(--white);color:var(--muted);cursor:pointer;display:flex;align-items:center;gap:4px;font-size:11px;font-weight:600;transition:all .12s;font-family:'Poppins',sans-serif}
  .act-btn:hover{background:var(--accent-light);color:var(--accent);border-color:rgba(20,184,166,.3)}

  /* WORKLOAD */
  .workload-item{display:flex;align-items:center;gap:10px;padding:12px 18px;border-bottom:1px solid var(--border)}
  .workload-item:last-child{border-bottom:none}
  .wl-avatar{width:34px;height:34px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:11px;color:#fff;flex-shrink:0}
  .wl-name{font-size:12px;font-weight:600;color:var(--text)}
  .wl-meta{font-size:11px;color:var(--muted);margin-top:1px}
  .wl-status{margin-left:auto;font-size:10px;font-weight:700;padding:3px 9px;border-radius:99px}
  .wls-active{background:#f0fdf4;color:#065f46}
  .wls-busy{background:#fffbeb;color:#b45309}

  /* FEED */
  .feed-list{max-height:220px;overflow-y:auto}
  .feed-item{display:flex;gap:10px;padding:10px 18px;border-bottom:1px solid var(--border);align-items:flex-start}
  .feed-item:last-child{border-bottom:none}
  .feed-dot{width:26px;height:26px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:12px}
  .fd-green{background:#f0fdf4}
  .fd-blue{background:#eff6ff}
  .fd-yellow{background:#fffbeb}
  .feed-text{font-size:12px;line-height:1.45;flex:1;color:var(--text)}
  .feed-time{font-size:10px;color:var(--muted2);margin-top:2px}

  /* CHART */
  .chart-area{padding:14px 20px 16px}
  .bar-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
  .bar-label{font-size:12px;color:var(--muted);width:72px;flex-shrink:0;font-weight:500}
  .bar-track{flex:1;height:6px;background:#f3f4f6;border-radius:99px;overflow:hidden}
  .bar-fill{height:100%;border-radius:99px}
  .bar-count{font-size:11px;color:var(--muted);width:22px;text-align:right;font-weight:600}

  /* PAGINATION */
  .pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid var(--border);font-size:12px;color:var(--muted)}
  .page-btns{display:flex;gap:5px}
  .page-btn{width:30px;height:30px;border-radius:6px;background:var(--white);border:1px solid var(--border);color:var(--muted);cursor:pointer;font-size:12px;display:flex;align-items:center;justify-content:center;transition:all .12s;font-family:'Poppins',sans-serif}
  .page-btn:hover,.page-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}

  /* PROFILE */
  .profile-hero{background:var(--white);border:1px solid var(--border);border-radius:var(--r);padding:28px 32px;display:flex;align-items:center;gap:24px;margin-bottom:20px;position:relative;overflow:hidden}
  .profile-hero::after{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--accent)}
  .avatar-lg{width:68px;height:68px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:22px;color:#fff;border:3px solid rgba(20,184,166,.2);flex-shrink:0}
  .profile-name{font-size:20px;font-weight:800;color:var(--text)}
  .profile-role-badge{display:inline-flex;align-items:center;gap:5px;background:var(--accent-light);color:var(--accent);font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px;margin-top:5px;border:1px solid rgba(20,184,166,.25)}
  .profile-stats{display:flex;gap:28px;margin-top:14px}
  .ps-val{font-size:20px;font-weight:800;color:var(--text)}
  .ps-key{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px}
  .profile-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}

  /* FORM */
  .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px}
  .form-group{margin-bottom:14px}
  .form-label{font-size:11px;color:var(--muted);font-weight:600;margin-bottom:5px;display:block;letter-spacing:.3px;text-transform:uppercase}
  .form-control{width:100%;background:var(--bg);border:1px solid var(--border);border-radius:8px;padding:9px 12px;color:var(--text);font-family:'Poppins',sans-serif;font-size:13px;outline:none;transition:border-color .12s}
  .form-control:focus{border-color:var(--accent);background:#fff}
  .form-control:disabled{opacity:.6;cursor:not-allowed}
  select.form-control option{background:#fff}
  textarea.form-control{resize:vertical;min-height:80px}

  /* MODAL */
  .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:200;opacity:0;pointer-events:none;transition:opacity .2s}
  .modal-overlay.open{opacity:1;pointer-events:all}
  .modal{background:var(--white);border:1px solid var(--border);border-radius:16px;width:520px;max-width:92vw;max-height:88vh;overflow-y:auto;transform:translateY(14px) scale(.97);transition:transform .22s;box-shadow:0 20px 60px rgba(0,0,0,.12)}
  .modal-overlay.open .modal{transform:translateY(0) scale(1)}
  .modal-head{padding:20px 24px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px}
  .modal-title{font-size:16px;font-weight:700;flex:1;color:var(--text)}
  .modal-close{width:30px;height:30px;border-radius:7px;background:var(--bg);border:none;color:var(--muted);cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;transition:all .12s}
  .modal-close:hover{background:#fee2e2;color:var(--red)}
  .modal-body{padding:20px 24px}
  .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:18px}
  .info-cell{background:var(--bg);border-radius:8px;padding:10px 12px;border:1px solid var(--border)}
  .info-cell-lbl{font-size:10px;color:var(--muted2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px;font-weight:600}
  .info-cell-val{font-size:13px;font-weight:600;color:var(--text)}
  .modal-actions{display:flex;gap:8px;justify-content:flex-end;padding-top:6px}
  .btn{padding:9px 20px;border-radius:8px;font-family:'Poppins',sans-serif;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .12s}
  .btn-primary{background:var(--accent);color:#fff}
  .btn-primary:hover{background:var(--accent-dark)}
  .btn-ghost{background:var(--bg);color:var(--muted);border:1px solid var(--border)}
  .btn-ghost:hover{color:var(--text);background:#e5e7eb}

  /* TOAST */
  .toast{position:fixed;bottom:24px;right:24px;z-index:300;background:var(--white);border:1px solid var(--border);border-radius:10px;padding:12px 18px;display:flex;align-items:center;gap:10px;font-size:13px;box-shadow:0 8px 24px rgba(0,0,0,.12);transform:translateY(14px);opacity:0;pointer-events:none;transition:all .25s;font-weight:500}
  .toast.show{transform:translateY(0);opacity:1;pointer-events:all}

  /* EMPTY */
  .empty{text-align:center;padding:44px 20px;color:var(--muted)}
  .empty .ei{font-size:36px;margin-bottom:10px;opacity:.5}
  .empty p{font-size:13px}

  ::-webkit-scrollbar{width:4px;height:4px}
  ::-webkit-scrollbar-track{background:transparent}
  ::-webkit-scrollbar-thumb{background:var(--border);border-radius:99px}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sb-logo">
    <div class="logo-icon">🏛️</div>
    <div>
      <div class="logo-text">MuniPortal</div>
      <div class="logo-sub">Agent Space</div>
    </div>
  </div>

  <div class="agent-card-sb" onclick="showPage('profile')">
    <div class="avatar-sm">KA</div>
    <div style="flex:1;min-width:0">
      <div class="agent-name-sb">Karim Amara</div>
      <div class="agent-role-sb">Municipal Agent</div>
    </div>
    <div class="status-dot"></div>
  </div>

  <nav class="nav">
    <div class="nav-section">Main</div>
    <div class="nav-item active" id="nav-dashboard" onclick="showPage('dashboard')">
      <span class="nav-icon">📊</span> Dashboard
    </div>
    <div class="nav-item" id="nav-complaints" onclick="showPage('complaints'); filterComplaints('all')">
      <span class="nav-icon">📋</span> All Reports
      <span class="nav-badge">24</span>
    </div>
    <div class="nav-item" id="nav-pending" onclick="showPage('complaints'); filterComplaints('pending')">
      <span class="nav-icon">⏳</span> Overdue
      <span class="nav-badge">8</span>
    </div>

    <div class="nav-section">Tools</div>
    <div class="nav-item">
      <span class="nav-icon">📈</span> Reports
    </div>
    <div class="nav-item">
      <span class="nav-icon">🔔</span> Notifications
      <span class="nav-badge">3</span>
    </div>
    <div class="nav-section">Account</div>
    <div class="nav-item" id="nav-profile" onclick="showPage('profile')">
      <span class="nav-icon">👤</span> My Profile
    </div>
  </nav>

  <div class="sb-footer">
    <button class="logout-btn" onclick="confirmLogout()">
      <span style="font-size:14px">←</span> Sign out
    </button>
  </div>
</aside>

<!-- MAIN -->
<main class="main">
  <header class="topbar">
    <div class="topbar-title" id="topbar-title">Dashboard</div>
    <div class="search-box">
      <span style="color:var(--muted2);font-size:13px">🔍</span>
      <input type="text" placeholder="Search complaints…" oninput="searchComplaints(this.value)"/>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <div class="icon-btn">🔔<span class="notif-dot"></span></div>
      <button class="top-btn" onclick="showPage('complaints'); filterComplaints('all')">+ View all reports</button>
    </div>
  </header>

  <!-- DASHBOARD PAGE -->
  <div class="content page active" id="page-dashboard">
    <div class="stats-grid">
      <div class="stat-card s-default" onclick="showPage('complaints'); filterComplaints('all')">
        <div class="stat-label">Total</div>
        <div class="stat-value">247</div>
        <div class="stat-sub">all reports</div>
      </div>
      <div class="stat-card s-blue" onclick="showPage('complaints'); filterComplaints('pending')">
        <div class="stat-label">Open</div>
        <div class="stat-value">38</div>
        <div class="stat-sub">pending</div>
      </div>
      <div class="stat-card s-yellow" onclick="showPage('complaints'); filterComplaints('progress')">
        <div class="stat-label">In Progress</div>
        <div class="stat-value">54</div>
        <div class="stat-sub">being handled</div>
      </div>
      <div class="stat-card s-green" onclick="showPage('complaints'); filterComplaints('resolved')">
        <div class="stat-label">Resolved</div>
        <div class="stat-value">155</div>
        <div class="stat-sub">closed</div>
      </div>
      <div class="stat-card s-red">
        <div class="stat-label">Overdue</div>
        <div class="stat-value">0</div>
        <div class="stat-sub">deadline exceeded</div>
      </div>
    </div>

    <div class="grid2">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">Recent reports</div>
          <span class="panel-link" onclick="showPage('complaints'); filterComplaints('all')">View all →</span>
        </div>
        <div class="filter-tabs">
          <div class="ftab active" onclick="setDashFilter(this,'all')">All</div>
          <div class="ftab" onclick="setDashFilter(this,'pending')">Open</div>
          <div class="ftab" onclick="setDashFilter(this,'progress')">In Progress</div>
          <div class="ftab" onclick="setDashFilter(this,'resolved')">Resolved</div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Description</th>
                <th>Category</th>
                <th>Agent</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody id="dash-tbody"></tbody>
          </table>
        </div>
        <div class="pagination">
          <span id="dash-count">Showing 1–5 of 24</span>
          <div class="page-btns">
            <button class="page-btn">‹</button>
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
          </div>
        </div>
      </div>

      <div style="display:flex;flex-direction:column;gap:18px">
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">Agent workload</div>
            <span class="panel-link">Manage →</span>
          </div>
          <div class="workload-item">
            <div class="wl-avatar">KA</div>
            <div>
              <div class="wl-name">Karim Amara</div>
              <div class="wl-meta">District 3 · 12 open cases</div>
            </div>
            <span class="wl-status wls-active">active</span>
          </div>
          <div class="workload-item">
            <div class="wl-avatar" style="background:#6366f1">FZ</div>
            <div>
              <div class="wl-name">Fatima Zerrouk</div>
              <div class="wl-meta">District 1 · 8 open cases</div>
            </div>
            <span class="wl-status wls-busy">busy</span>
          </div>
          <div class="workload-item">
            <div class="wl-avatar" style="background:#f59e0b">MA</div>
            <div>
              <div class="wl-name">M. Abdelkader</div>
              <div class="wl-meta">District 2 · 5 open cases</div>
            </div>
            <span class="wl-status wls-active">active</span>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head"><div class="panel-title">By Category</div></div>
          <div class="chart-area">
            <div class="bar-row">
              <div class="bar-label">Roads</div>
              <div class="bar-track"><div class="bar-fill" style="width:72%;background:var(--blue)"></div></div>
              <div class="bar-count">72</div>
            </div>
            <div class="bar-row">
              <div class="bar-label">Lighting</div>
              <div class="bar-track"><div class="bar-fill" style="width:55%;background:var(--yellow)"></div></div>
              <div class="bar-count">55</div>
            </div>
            <div class="bar-row">
              <div class="bar-label">Cleanliness</div>
              <div class="bar-track"><div class="bar-fill" style="width:48%;background:var(--green)"></div></div>
              <div class="bar-count">48</div>
            </div>
            <div class="bar-row">
              <div class="bar-label">Water</div>
              <div class="bar-track"><div class="bar-fill" style="width:38%;background:#0891b2"></div></div>
              <div class="bar-count">38</div>
            </div>
            <div class="bar-row">
              <div class="bar-label">Other</div>
              <div class="bar-track"><div class="bar-fill" style="width:20%;background:var(--muted2)"></div></div>
              <div class="bar-count">34</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- COMPLAINTS PAGE -->
  <div class="content page" id="page-complaints">
    <div class="panel">
      <div class="panel-head">
        <div class="panel-title" id="complaints-page-title">All Reports</div>
      </div>
      <div class="filter-tabs">
        <div class="ftab active" id="ftab-all" onclick="filterComplaints('all',this)">All</div>
        <div class="ftab" id="ftab-pending" onclick="filterComplaints('pending',this)">Open</div>
        <div class="ftab" id="ftab-progress" onclick="filterComplaints('progress',this)">In Progress</div>
        <div class="ftab" id="ftab-resolved" onclick="filterComplaints('resolved',this)">Resolved</div>
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
          <tbody id="full-tbody"></tbody>
        </table>
      </div>
      <div class="pagination">
        <span id="full-count">24 complaints</span>
        <div class="page-btns">
          <button class="page-btn">‹</button>
          <button class="page-btn active">1</button>
          <button class="page-btn">2</button>
          <button class="page-btn">›</button>
        </div>
      </div>
    </div>
  </div>

  <!-- PROFILE PAGE -->
  <div class="content page" id="page-profile">
    <div class="profile-hero">
      <div class="avatar-lg">KA</div>
      <div>
        <div class="profile-name">Karim Amara</div>
        <div class="profile-role-badge">🛡 Municipal Agent — District 3</div>
        <div class="profile-stats">
          <div><div class="ps-val">247</div><div class="ps-key">Handled</div></div>
          <div><div class="ps-val">62%</div><div class="ps-key">Resolution</div></div>
          <div><div class="ps-val">4.8</div><div class="ps-key">Rating</div></div>
          <div><div class="ps-val">2 yrs</div><div class="ps-key">Seniority</div></div>
        </div>
      </div>
    </div>
    <div class="profile-grid">
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">Personal Information</div>
          <button class="btn btn-primary" style="padding:6px 16px;font-size:12px" onclick="showToast('✅ Profile updated successfully')">💾 Save</button>
        </div>
        <div style="padding:20px 22px">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">First Name</label>
              <input class="form-control" value="Karim"/>
            </div>
            <div class="form-group">
              <label class="form-label">Last Name</label>
              <input class="form-control" value="Amara"/>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input class="form-control" value="karim.amara@municipality.dz"/>
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input class="form-control" value="+213 55 123 4567"/>
          </div>
          <div class="form-group">
            <label class="form-label">Assigned Zone / District</label>
            <input class="form-control" value="District 3 — City Centre" disabled/>
          </div>
          <div class="form-group">
            <label class="form-label">Agent ID</label>
            <input class="form-control" value="AGT-2023-0034" disabled/>
          </div>
        </div>
      </div>
      <div class="panel">
        <div class="panel-head"><div class="panel-title">Account Security</div></div>
        <div style="padding:20px 22px">
          <div class="form-group">
            <label class="form-label">Current Password</label>
            <input class="form-control" type="password" placeholder="••••••••"/>
          </div>
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input class="form-control" type="password" placeholder="••••••••"/>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm New Password</label>
            <input class="form-control" type="password" placeholder="••••••••"/>
          </div>
          <button class="btn btn-primary" onclick="showToast('🔐 Password updated successfully')">Change Password</button>
          <div style="margin-top:20px;padding-top:18px;border-top:1px solid var(--border)">
            <div style="font-size:12px;font-weight:600;margin-bottom:10px;color:var(--text)">Active Sessions</div>
            <div style="display:flex;align-items:center;justify-content:space-between;font-size:12px;padding:10px 12px;background:var(--bg);border-radius:8px;border:1px solid var(--border)">
              <div>
                <div style="font-weight:600;color:var(--text)">Chrome — Windows 11</div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px">Algiers, DZ · Current session</div>
              </div>
              <span style="color:var(--green);font-size:11px;font-weight:700">● Active</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="edit-modal">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">Edit Complaint</div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="info-grid">
        <div class="info-cell"><div class="info-cell-lbl">Complaint ID</div><div class="info-cell-val" id="m-id">—</div></div>
        <div class="info-cell"><div class="info-cell-lbl">Submitted</div><div class="info-cell-val" id="m-date">—</div></div>
        <div class="info-cell"><div class="info-cell-lbl">Citizen</div><div class="info-cell-val" id="m-citizen">—</div></div>
        <div class="info-cell"><div class="info-cell-lbl">Category</div><div class="info-cell-val" id="m-cat">—</div></div>
      </div>
      <div class="form-group">
        <label class="form-label">Title</label>
        <input class="form-control" id="m-title" readonly/>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea class="form-control" id="m-desc" readonly></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Status</label>
          <select class="form-control" id="m-status">
            <option value="pending">⏳ Pending</option>
            <option value="progress">🔄 In Progress</option>
            <option value="resolved">✅ Resolved</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Priority</label>
          <select class="form-control" id="m-priority">
            <option value="high">🔴 High</option>
            <option value="medium">🟡 Medium</option>
            <option value="low">🟢 Low</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Internal Note</label>
        <textarea class="form-control" id="m-note" placeholder="Add a processing note…" style="min-height:65px"></textarea>
      </div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button class="btn btn-primary" onclick="saveComplaint()">💾 Save Changes</button>
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast">
  <span id="toast-msg">Operation successful</span>
</div>

<script>
const complaints = [
  {id:'RC-2044',title:'Pothole on main avenue',desc:'A large pothole appeared on the main avenue after the rains. Dangerous for vehicles and pedestrians.',citizen:'Mohamed Bensalem',category:'voirie',catLabel:'Roads',date:'31/03/2026',status:'pending',priority:'high'},
  {id:'RC-2043',title:'Street light out on Orange St.',desc:'The street lamp at the corner has been off for 5 days. Very dark area at night.',citizen:'Fatima Zahra',category:'eclairage',catLabel:'Lighting',date:'30/03/2026',status:'progress',priority:'medium'},
  {id:'RC-2042',title:'Illegal dumping near market',desc:'Garbage has been illegally dumped near the entrance of the weekly market.',citizen:'Ali Boukhalfa',category:'proprete',catLabel:'Cleanliness',date:'29/03/2026',status:'pending',priority:'medium'},
  {id:'RC-2041',title:'Water pipe leak — main line',desc:'A significant leak on the main distribution pipe. Substantial water loss.',citizen:'Naima Cherif',category:'eau',catLabel:'Water',date:'28/03/2026',status:'resolved',priority:'high'},
  {id:'RC-2040',title:'Damaged pavement — new estate',desc:'The pavement in the new estate is heavily damaged and poses a risk to elderly pedestrians.',citizen:'Omar Hadj',category:'voirie',catLabel:'Roads',date:'27/03/2026',status:'progress',priority:'low'},
  {id:'RC-2039',title:'Stop sign knocked over',desc:'The stop sign at the El Feth Street junction is knocked over, creating a traffic hazard.',citizen:'Leila Messaoud',category:'voirie',catLabel:'Roads',date:'26/03/2026',status:'pending',priority:'high'},
  {id:'RC-2038',title:'Blocked drain — Liberty Ave.',desc:'The drain is completely blocked causing flooding during rain.',citizen:'Youcef Hamdi',category:'autre',catLabel:'Other',date:'25/03/2026',status:'progress',priority:'medium'},
  {id:'RC-2037',title:'Multiple street lights down',desc:'Several street lights out over 200m. Unsafe at night in this residential area.',citizen:'Samira Khelil',category:'eclairage',catLabel:'Lighting',date:'24/03/2026',status:'resolved',priority:'medium'},
];

let currentFilter = 'all';
let editingId = null;

function statusBadge(s) {
  const map = {
    pending:`<span class="st-badge st-open"><span class="dot"></span>Open</span>`,
    progress:`<span class="st-badge st-progress"><span class="dot"></span>In Progress</span>`,
    resolved:`<span class="st-badge st-resolved"><span class="dot"></span>Resolved</span>`
  };
  return map[s] || map.pending;
}
function catTag(c,l) { return `<span class="cat-tag cat-${c}">${l}</span>`; }
function prioDot(p) { return `<span class="p-dot p-${p}" title="${p}"></span>`; }

function getFiltered(f) {
  return f === 'all' ? complaints : complaints.filter(c => c.status === f);
}

function renderDash(f='all') {
  const list = getFiltered(f).slice(0,5);
  const tb = document.getElementById('dash-tbody');
  if (!list.length) { tb.innerHTML = `<tr><td colspan="6"><div class="empty"><div class="ei">📭</div><p>No complaints</p></div></td></tr>`; return; }
  tb.innerHTML = list.map(c => `
    <tr onclick="openModal('${c.id}')">
      <td><span class="c-id">#${c.id.replace('RC-','')}</span></td>
      <td><div class="c-title">${c.title}</div></td>
      <td>${catTag(c.category,c.catLabel)}</td>
      <td style="font-size:12px;color:var(--muted)">—</td>
      <td>${statusBadge(c.status)}</td>
      <td style="font-size:12px;color:var(--muted)">${c.date}</td>
    </tr>`).join('');
  document.getElementById('dash-count').textContent = `Showing 1–${list.length} of ${getFiltered(f).length}`;
}

function renderFull(f='all') {
  const list = getFiltered(f);
  const tb = document.getElementById('full-tbody');
  const titleMap = {all:'All Reports',pending:'Open Reports',progress:'In Progress',resolved:'Resolved Reports'};
  document.getElementById('complaints-page-title').textContent = titleMap[f] || 'All Reports';
  if (!list.length) { tb.innerHTML = `<tr><td colspan="8"><div class="empty"><div class="ei">📭</div><p>No complaints</p></div></td></tr>`; return; }
  tb.innerHTML = list.map(c => `
    <tr onclick="openModal('${c.id}')">
      <td><span class="c-id">#${c.id.replace('RC-','')}</span></td>
      <td><div class="c-title">${c.title}</div><div class="c-sub">${c.date}</div></td>
      <td style="font-size:12px">${c.citizen}</td>
      <td>${catTag(c.category,c.catLabel)}</td>
      <td style="font-size:12px;color:var(--muted)">—</td>
      <td>${statusBadge(c.status)}</td>
      <td style="color:var(--muted2);font-size:12px">${c.date}</td>
      <td><div class="act-btns">
        <button class="act-btn" onclick="event.stopPropagation();openModal('${c.id}')">✏️ Edit</button>
        <button class="act-btn" onclick="event.stopPropagation()">👁 View</button>
      </div></td>
    </tr>`).join('');
  document.getElementById('full-count').textContent = `${list.length} complaint${list.length!==1?'s':''}`;
}

function setDashFilter(el, f) {
  document.querySelectorAll('#page-dashboard .ftab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
  renderDash(f);
}

function filterComplaints(f, el) {
  currentFilter = f;
  ['all','pending','progress','resolved'].forEach(k => {
    const tab = document.getElementById('ftab-'+k);
    if (tab) tab.classList.toggle('active', k===f);
  });
  ['nav-complaints','nav-pending'].forEach(id => {
    document.getElementById(id)?.classList.remove('active');
  });
  if (f==='pending') document.getElementById('nav-pending')?.classList.add('active');
  else document.getElementById('nav-complaints')?.classList.add('active');
  renderFull(f);
}

function searchComplaints(q) {
  if (!q) { renderFull(currentFilter); renderDash(); return; }
  const r = complaints.filter(c =>
    c.title.toLowerCase().includes(q.toLowerCase()) ||
    c.citizen.toLowerCase().includes(q.toLowerCase()) ||
    c.id.toLowerCase().includes(q.toLowerCase())
  );
  const tb = document.getElementById('dash-tbody');
  if (!r.length) { tb.innerHTML = `<tr><td colspan="6"><div class="empty"><div class="ei">🔍</div><p>No results for "${q}"</p></div></td></tr>`; return; }
  tb.innerHTML = r.slice(0,5).map(c=>`
    <tr onclick="openModal('${c.id}')">
      <td><span class="c-id">#${c.id.replace('RC-','')}</span></td>
      <td>${c.title}</td>
      <td>${catTag(c.category,c.catLabel)}</td>
      <td style="font-size:12px;color:var(--muted)">—</td>
      <td>${statusBadge(c.status)}</td>
      <td style="font-size:12px;color:var(--muted)">${c.date}</td>
    </tr>`).join('');
}

function openModal(id) {
  const c = complaints.find(x=>x.id===id);
  if (!c) return;
  editingId = id;
  document.getElementById('m-id').textContent = c.id;
  document.getElementById('m-date').textContent = c.date;
  document.getElementById('m-citizen').textContent = c.citizen;
  document.getElementById('m-cat').textContent = c.catLabel;
  document.getElementById('m-title').value = c.title;
  document.getElementById('m-desc').value = c.desc;
  document.getElementById('m-status').value = c.status;
  document.getElementById('m-priority').value = c.priority;
  document.getElementById('m-note').value = '';
  document.getElementById('edit-modal').classList.add('open');
}
function closeModal() {
  document.getElementById('edit-modal').classList.remove('open');
  editingId = null;
}
function saveComplaint() {
  const c = complaints.find(x=>x.id===editingId);
  if (!c) return;
  c.status = document.getElementById('m-status').value;
  c.priority = document.getElementById('m-priority').value;
  closeModal();
  renderDash(); renderFull(currentFilter);
  showToast('✅ Complaint updated successfully');
}

function showPage(p) {
  document.querySelectorAll('.page').forEach(el=>el.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(el=>el.classList.remove('active'));
  document.getElementById('page-'+p).classList.add('active');
  const titles = {dashboard:'Dashboard',complaints:'All Reports',profile:'My Profile'};
  document.getElementById('topbar-title').textContent = titles[p]||'';
  const navMap = {dashboard:'nav-dashboard',profile:'nav-profile'};
  if (navMap[p]) document.getElementById(navMap[p])?.classList.add('active');
  if (p==='complaints') renderFull(currentFilter);
}

let toastTimer;
function showToast(msg) {
  const t = document.getElementById('toast');
  document.getElementById('toast-msg').textContent = msg;
  t.classList.add('show');
  clearTimeout(toastTimer);
  toastTimer = setTimeout(()=>t.classList.remove('show'), 3200);
}

function confirmLogout() {
  if (confirm('Are you sure you want to sign out?')) showToast('👋 Signing out…');
}

document.addEventListener('DOMContentLoaded', ()=>{
  renderDash(); renderFull();
  document.getElementById('edit-modal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
});
</script>
</body>
</html>