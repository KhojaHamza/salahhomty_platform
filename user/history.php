<?php
// user/history.php
session_start();

require_once '../config/database.php';

// ── Redirect if not logged in ──
if (!isset($_SESSION['user_id'])) {
    header('Location: signin.php?redirect=history');
    exit;
}

$user_id = (int) $_SESSION['user_id'];

// ── Fetch this user's reports ──
try {
    $stmt = $pdo->prepare("
        SELECT
            id,
            image,
            latitude,
            longitude,
            description,
            status,
            created_at
        FROM reports
        WHERE user_id = :user_id
        ORDER BY created_at DESC
    ");
    $stmt->execute([':user_id' => $user_id]);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('history.php PDO error: ' . $e->getMessage());
    $reports = [];
}

// ── Stats ──
$total      = count($reports);
$countOpen  = 0; $countProg = 0; $countDone = 0; $countOver = 0;
foreach ($reports as $r) {
    switch ($r['status']) {
        case 'Open':        $countOpen++; break;
        case 'In progress': $countProg++; break;
        case 'Resolved':    $countDone++; break;
        case 'Overdue':     $countOver++; break;
    }
}

// ── Helper: format date ──
function fmtDate(string $dt): string {
    return date('M j, Y', strtotime($dt));
}

// ── Helper: status CSS class ──
function statusClass(string $s): string {
    return match($s) {
        'Open'        => 's-open',
        'In progress' => 's-progress',
        'Resolved'    => 's-resolved',
        'Transferred' => 's-transferred',
        'Overdue'     => 's-overdue',
        default       => ''
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SalahHomty – My Report History</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet" />
  <style>
    /* ── Variables ── */
    :root {
      --teal:        #1aaa96;
      --teal-dark:   #138a79;
      --teal-light:  #14e7ce;
      --dark:        #111827;
      --dark2:       #1f2937;
      --mid:         #374151;
      --muted:       #6b7280;
      --border:      #e5e7eb;
      --white:       #ffffff;
      --cream:       #f9fafb;
      --accent:      #f7c200;
      --red:         #ef4444;
      --red-light:   #fef2f2;
      --orange:      #f97316;
      --orange-light:#fff7ed;
      --green:       #22c55e;
      --green-light: #f0fdf4;
      --blue:        #3b82f6;
      --blue-light:  #eff6ff;

      --font-head: 'Syne', sans-serif;
      --font-body: 'DM Sans', sans-serif;
      --r-sm: 6px; --r-md: 12px; --r-lg: 20px;
      --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
      --shadow-md: 0 4px 16px rgba(0,0,0,.10);
      --t: .2s ease;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: var(--font-body); background: var(--cream); color: var(--dark); min-height: 100vh; }
    a { color: inherit; text-decoration: none; }
    button { font-family: var(--font-body); cursor: pointer; }

    /* ── NAVBAR ── */
    nav {
      background: var(--dark2);
      display: flex; align-items: center;
      height: 68px; padding: 0 40px; gap: 18px;
      position: sticky; top: 0; z-index: 100;
      box-shadow: 0 2px 12px rgba(0,0,0,.25);
    }
    .logo { display: flex; align-items: center; gap: 10px; }
    .logo-icon {
      width: 38px; height: 38px; background: var(--teal);
      border-radius: var(--r-sm);
      display: flex; align-items: center; justify-content: center;
      font-family: var(--font-head); font-weight: 800; color: white; font-size: 14px;
    }
    .logo-text {
      font-family: var(--font-head); font-size: 22px; font-weight: 800;
      letter-spacing: -.5px; color: var(--white);
    }
    .logo-text span { color: var(--teal); }
    .nav-spacer { flex: 1; }
    .nav-links { display: flex; align-items: center; gap: 6px; }
    .nav-links a {
      font-size: 13.5px; color: #9ca3af; padding: 6px 14px;
      border-radius: var(--r-sm); transition: color var(--t), background var(--t);
    }
    .nav-links a:hover, .nav-links a.active { color: var(--white); background: rgba(255,255,255,.08); }
    .btn-report {
      background: var(--teal) !important; color: var(--white) !important;
      font-weight: 600 !important; transition: background var(--t) !important;
    }
    .btn-report:hover { background: var(--teal-dark) !important; }

    /* ── PAGE ── */
    .page { max-width: 1100px; margin: 0 auto; padding: 40px 32px 80px; }

    /* ── Header ── */
    .page-header {
      display: flex; align-items: flex-start; justify-content: space-between;
      margin-bottom: 32px; gap: 16px; flex-wrap: wrap;
    }
    .page-header h1 {
      font-family: var(--font-head); font-size: 28px; font-weight: 800;
      color: var(--dark); letter-spacing: -.5px; margin-bottom: 4px;
    }
    .page-header p { font-size: 14px; color: var(--muted); }

    .export-btn {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--white); border: 1.5px solid var(--border);
      padding: 10px 18px; border-radius: var(--r-sm);
      font-size: 14px; font-weight: 600; color: var(--dark);
      box-shadow: var(--shadow-sm); transition: all var(--t);
    }
    .export-btn:hover { border-color: var(--teal); color: var(--teal); }

    /* ── Stats ── */
    .stats-row {
      display: grid; grid-template-columns: repeat(5, 1fr);
      gap: 16px; margin-bottom: 24px;
    }
    .stat-card {
      background: var(--white); border-radius: var(--r-md);
      padding: 20px 18px; border-left: 4px solid transparent;
      box-shadow: var(--shadow-sm);
    }
    .stat-card.all  { border-color: var(--dark); }
    .stat-card.open { border-color: var(--blue); }
    .stat-card.prog { border-color: var(--orange); }
    .stat-card.done { border-color: var(--green); }
    .stat-card.over { border-color: var(--red); }
    .stat-label { font-size: 11px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; }
    .stat-value { font-family: var(--font-head); font-size: 32px; font-weight: 800; color: var(--dark); line-height: 1; }
    .stat-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }

    /* ── Overdue alert ── */
    .admin-alert {
      display: flex; align-items: flex-start; gap: 12px;
      background: var(--red-light); border: 1px solid #fca5a5;
      border-radius: var(--r-md); padding: 16px 20px;
      font-size: 14px; color: #991b1b; margin-bottom: 24px;
    }
    .admin-alert svg { flex-shrink: 0; margin-top: 1px; color: var(--red); }
    .admin-alert strong { display: block; margin-bottom: 2px; }

    /* ── Toolbar ── */
    .toolbar {
      display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap;
    }
    .search-wrap {
      display: flex; align-items: center; gap: 10px;
      background: var(--white); border: 1.5px solid var(--border);
      border-radius: var(--r-sm); padding: 0 14px; flex: 1; min-width: 200px;
      box-shadow: var(--shadow-sm);
    }
    .search-wrap svg { color: var(--muted); flex-shrink: 0; }
    .search-wrap input {
      flex: 1; border: none; outline: none; padding: 11px 0;
      font-size: 14px; font-family: var(--font-body); background: transparent; color: var(--dark);
    }
    .filter-select {
      background: var(--white); border: 1.5px solid var(--border);
      border-radius: var(--r-sm); padding: 10px 14px;
      font-size: 14px; font-family: var(--font-body); color: var(--dark);
      box-shadow: var(--shadow-sm); cursor: pointer; transition: border-color var(--t);
    }
    .filter-select:focus { outline: none; border-color: var(--teal); }

    /* ── Table ── */
    .table-card {
      background: var(--white); border-radius: var(--r-lg);
      box-shadow: var(--shadow-md); overflow: hidden;
    }
    table { width: 100%; border-collapse: collapse; }
    thead { background: var(--cream); border-bottom: 2px solid var(--border); }
    th {
      text-align: left; padding: 14px 18px;
      font-size: 11.5px; font-weight: 700; letter-spacing: .6px;
      text-transform: uppercase; color: var(--muted);
    }
    td { padding: 15px 18px; font-size: 14px; border-bottom: 1px solid var(--border); vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    tr:hover td { background: var(--cream); }

    .claim-id { font-family: var(--font-head); font-size: 13px; font-weight: 700; color: var(--teal); }

    .category-badge {
      display: inline-flex; align-items: center; gap: 5px;
      background: var(--cream); border: 1px solid var(--border);
      padding: 4px 10px; border-radius: 100px; font-size: 12.5px; font-weight: 500;
    }

    /* thumbnail */
    .report-thumb {
      width: 56px; height: 42px; object-fit: cover;
      border-radius: var(--r-sm); border: 1px solid var(--border);
    }
    .no-thumb {
      width: 56px; height: 42px; background: var(--cream);
      border-radius: var(--r-sm); border: 1px solid var(--border);
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; color: var(--muted);
    }

    .date-col { color: var(--muted); font-size: 13px; }

    /* Status badges */
    .status-badge {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 5px 10px; border-radius: 100px; font-size: 12.5px; font-weight: 600;
      white-space: nowrap;
    }
    .dot { width: 7px; height: 7px; border-radius: 50%; }

    .s-open        { background: var(--blue-light);   color: #1d4ed8; }
    .s-open   .dot { background: var(--blue); }
    .s-progress        { background: var(--orange-light); color: #c2410c; }
    .s-progress   .dot { background: var(--orange); }
    .s-resolved        { background: var(--green-light);  color: #15803d; }
    .s-resolved   .dot { background: var(--green); }
    .s-transferred        { background: #f5f3ff; color: #6d28d9; }
    .s-transferred   .dot { background: #8b5cf6; }
    .s-overdue        { background: var(--red-light);   color: #991b1b; }
    .s-overdue   .dot { background: var(--red); }

    /* Action buttons */
    .action-btn {
      padding: 7px 14px; border-radius: var(--r-sm);
      font-size: 13px; font-weight: 600; border: 1.5px solid var(--border);
      background: var(--white); color: var(--dark); transition: all var(--t);
    }
    .action-btn:hover { border-color: var(--teal); color: var(--teal); }
    .transfer-btn { border-color: var(--red); color: var(--red); }
    .transfer-btn:hover { background: var(--red); color: var(--white); }

    /* Empty state */
    .empty {
      display: flex; flex-direction: column; align-items: center;
      gap: 12px; padding: 60px 20px; color: var(--muted); text-align: center;
    }
    .empty svg { opacity: .4; }
    .empty p { font-size: 15px; }
    .empty a {
      display: inline-block; margin-top: 4px;
      background: var(--teal); color: var(--white);
      padding: 10px 22px; border-radius: var(--r-sm);
      font-size: 14px; font-weight: 600; transition: background var(--t);
    }
    .empty a:hover { background: var(--teal-dark); }

    /* Pagination */
    .pagination {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 20px; border-top: 1px solid var(--border);
      font-size: 13px; color: var(--muted); flex-wrap: wrap; gap: 12px;
    }
    .page-btns { display: flex; gap: 6px; flex-wrap: wrap; }
    .page-btns button {
      width: 34px; height: 34px; border-radius: var(--r-sm);
      border: 1.5px solid var(--border); background: var(--white);
      font-size: 13px; font-weight: 600; color: var(--dark);
      transition: all var(--t);
    }
    .page-btns button:hover, .page-btns button.active {
      background: var(--teal); border-color: var(--teal); color: var(--white);
    }

    /* ── Modal (detail view) ── */
    .modal-overlay {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,.5); z-index: 200;
      align-items: center; justify-content: center; padding: 20px;
    }
    .modal-overlay.open { display: flex; }
    .modal {
      background: var(--white); border-radius: var(--r-lg);
      width: 100%; max-width: 520px; box-shadow: 0 24px 64px rgba(0,0,0,.2);
      overflow: hidden;
    }
    .modal-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 20px 24px; border-bottom: 1px solid var(--border);
    }
    .modal-header h2 { font-family: var(--font-head); font-size: 18px; font-weight: 800; }
    .modal-close {
      background: none; border: none; font-size: 22px; color: var(--muted); line-height: 1;
      transition: color var(--t);
    }
    .modal-close:hover { color: var(--dark); }
    .modal-body { padding: 24px; }
    .modal-img {
      width: 100%; max-height: 240px; object-fit: cover;
      border-radius: var(--r-md); margin-bottom: 20px; border: 1px solid var(--border);
    }
    .modal-field { margin-bottom: 14px; }
    .modal-field label { display: block; font-size: 11px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; color: var(--muted); margin-bottom: 4px; }
    .modal-field p { font-size: 14px; color: var(--dark); line-height: 1.6; }

    /* ── Responsive ── */
    @media (max-width: 900px) {
      .stats-row { grid-template-columns: repeat(3, 1fr); }
      nav { padding: 0 20px; }
    }
    @media (max-width: 600px) {
      .page { padding: 24px 16px 60px; }
      .stats-row { grid-template-columns: 1fr 1fr; }
      th:nth-child(4), td:nth-child(4),
      th:nth-child(5), td:nth-child(5) { display: none; }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<nav>
  <a href="../index.php" class="logo">
    <div class="logo-icon">SH</div>
    <span class="logo-text">Salah<span>Homty</span></span>
  </a>
  <div class="nav-spacer"></div>
  <div class="nav-links">
    <a href="../index.php" class="btn-report">Report a problem</a>
    <a href="history.php" class="active">History</a>
    <a href="helppage.php">Help</a>
    <a href="services.php">Services</a>
    <a href="profil.php">profil</a>
    <a href="../auth/logout.php">Logout</a>
  </div>
</nav>

<!-- PAGE -->
<div class="page">

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1>My Report History</h1>
      <p>All complaints submitted by <strong><?= htmlspecialchars($_SESSION['user_name']) ?></strong> and their current status.</p>
    </div>
    <button class="export-btn" onclick="exportCSV()">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
        <polyline points="7 10 12 15 17 10"/>
        <line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Export CSV
    </button>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card all">
      <div class="stat-label">Total</div>
      <div class="stat-value"><?= $total ?></div>
      <div class="stat-sub">complaints</div>
    </div>
    <div class="stat-card open">
      <div class="stat-label">Open</div>
      <div class="stat-value"><?= $countOpen ?></div>
      <div class="stat-sub">pending</div>
    </div>
    <div class="stat-card prog">
      <div class="stat-label">In progress</div>
      <div class="stat-value"><?= $countProg ?></div>
      <div class="stat-sub">processing</div>
    </div>
    <div class="stat-card done">
      <div class="stat-label">Resolved</div>
      <div class="stat-value"><?= $countDone ?></div>
      <div class="stat-sub">closed</div>
    </div>
    <div class="stat-card over">
      <div class="stat-label">Overdue</div>
      <div class="stat-value"><?= $countOver ?></div>
      <div class="stat-sub">deadline exceeded</div>
    </div>
  </div>

  <?php if ($countOver > 0): ?>
  <div class="admin-alert">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <div>
      <strong><?= $countOver ?> complaint<?= $countOver > 1 ? 's have' : ' has' ?> exceeded the processing deadline.</strong>
      Contact your local authority if you need an urgent follow-up.
    </div>
  </div>
  <?php endif; ?>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-wrap">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" id="searchInput" placeholder="Search by ID, description…" oninput="filterTable()" />
    </div>
    <select class="filter-select" id="filterStatus" onchange="filterTable()">
      <option value="">All statuses</option>
      <option value="Open">Open</option>
      <option value="In progress">In progress</option>
      <option value="Resolved">Resolved</option>
      <option value="Transferred">Transferred</option>
      <option value="Overdue">Overdue</option>
    </select>
  </div>

  <!-- Table -->
  <div class="table-card">
    <table id="claimsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Photo</th>
          <th>Description</th>
          <th>Location</th>
          <th>Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody id="tableBody"></tbody>
    </table>
    <div class="pagination">
      <span id="paginationInfo"></span>
      <div class="page-btns" id="pageBtns"></div>
    </div>
  </div>

</div>

<!-- DETAIL MODAL -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
  <div class="modal">
    <div class="modal-header">
      <h2 id="modalTitle">Report details</h2>
      <button class="modal-close" onclick="closeModalDirect()">✕</button>
    </div>
    <div class="modal-body" id="modalBody"></div>
  </div>
</div>

<script>
  // ── Data from PHP ──
  const reports = <?= json_encode(array_map(function($r) {
      return [
          'id'          => $r['id'],
          'description' => $r['description'] ?? '',
          'image'       => $r['image'] ?? '',
          'latitude'    => $r['latitude'] ?? '',
          'longitude'   => $r['longitude'] ?? '',
          'status'      => $r['status'] ?? 'Open',
          'date'        => date('M j, Y', strtotime($r['created_at'])),
      ];
  }, $reports), JSON_UNESCAPED_UNICODE) ?>;

  const statusClass = {
    'Open':        's-open',
    'In progress': 's-progress',
    'Resolved':    's-resolved',
    'Transferred': 's-transferred',
    'Overdue':     's-overdue',
  };

  let filtered    = [...reports];
  let currentPage = 1;
  const perPage   = 8;

  /* ── Filter ── */
  function filterTable() {
    const q  = document.getElementById('searchInput').value.toLowerCase();
    const st = document.getElementById('filterStatus').value;
    filtered = reports.filter(r =>
      (!q  || String(r.id).includes(q) || (r.description || '').toLowerCase().includes(q)) &&
      (!st || r.status === st)
    );
    currentPage = 1;
    render();
  }

  /* ── Render ── */
  function render() {
    const start = (currentPage - 1) * perPage;
    const page  = filtered.slice(start, start + perPage);
    const tbody = document.getElementById('tableBody');

    if (filtered.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7">
        <div class="empty">
          <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <p>No reports found.</p>
          <a href="../index.php">Submit your first report</a>
        </div>
      </td></tr>`;
    } else {
      tbody.innerHTML = page.map(r => {
        const sc = statusClass[r.status] || '';
        const dotClass = sc.replace('s-', '');
        const isOverdue = r.status === 'Overdue';

        const thumb = r.image
          ? `<img class="report-thumb" src="../${r.image}" alt="Report photo" />`
          : `<div class="no-thumb">📷</div>`;

        const location = (r.latitude && r.longitude)
          ? `${parseFloat(r.latitude).toFixed(4)}, ${parseFloat(r.longitude).toFixed(4)}`
          : '—';

        return `<tr>
          <td><span class="claim-id">#${r.id}</span></td>
          <td>${thumb}</td>
          <td>${r.description ? r.description : '<span style="color:var(--muted);font-style:italic">No description</span>'}</td>
          <td class="date-col">${location}</td>
          <td class="date-col">${r.date}</td>
          <td>
            <span class="status-badge ${sc}">
              <span class="dot" style="background:${dotColor(r.status)}"></span>
              ${r.status}
            </span>
          </td>
          <td>
            <button class="action-btn${isOverdue ? ' transfer-btn' : ''}"
              onclick="viewDetail(${r.id})">
              ${isOverdue ? '⇄ Follow up' : 'View details'}
            </button>
          </td>
        </tr>`;
      }).join('');
    }

    // Pagination info
    const total = filtered.length;
    document.getElementById('paginationInfo').textContent =
      `Showing ${Math.min(start + 1, total)}–${Math.min(start + perPage, total)} of ${total} report${total !== 1 ? 's' : ''}`;

    // Page buttons
    const user = Math.ceil(total / perPage);
    const btns  = document.getElementById('pageBtns');
    btns.innerHTML = '';
    for (let i = 1; i <= user; i++) {
      const b = document.createElement('button');
      b.textContent = i;
      if (i === currentPage) b.classList.add('active');
      b.addEventListener('click', () => { currentPage = i; render(); });
      btns.appendChild(b);
    }
  }

  function dotColor(status) {
    return { Open:'#3b82f6', 'In progress':'#f97316', Resolved:'#22c55e', Transferred:'#8b5cf6', Overdue:'#ef4444' }[status] || '#ccc';
  }

  /* ── Detail modal ── */
  function viewDetail(id) {
    const r = reports.find(x => x.id == id);
    if (!r) return;

    document.getElementById('modalTitle').textContent = `Report #${r.id}`;

    const sc = statusClass[r.status] || '';
    const imgHtml = r.image
      ? `<img class="modal-img" src="../${r.image}" alt="Report photo" />`
      : '';

    const mapLink = (r.latitude && r.longitude)
      ? `<a href="https://www.openstreetmap.org/?mlat=${r.latitude}&mlon=${r.longitude}#map=16/${r.latitude}/${r.longitude}" target="_blank" rel="noopener" style="color:var(--teal);font-weight:600;">
           Open in map ↗
         </a>`
      : '<em style="color:var(--muted)">No GPS data</em>';

    document.getElementById('modalBody').innerHTML = `
      ${imgHtml}
      <div class="modal-field">
        <label>Status</label>
        <p><span class="status-badge ${sc}"><span class="dot" style="background:${dotColor(r.status)}"></span>${r.status}</span></p>
      </div>
      <div class="modal-field">
        <label>Description</label>
        <p>${r.description || '<em style="color:var(--muted)">No description provided</em>'}</p>
      </div>
      <div class="modal-field">
        <label>Location</label>
        <p>${mapLink}</p>
      </div>
      <div class="modal-field">
        <label>Date submitted</label>
        <p>${r.date}</p>
      </div>
    `;

    document.getElementById('modalOverlay').classList.add('open');
  }

  function closeModal(e) {
    if (e.target === document.getElementById('modalOverlay')) closeModalDirect();
  }
  function closeModalDirect() {
    document.getElementById('modalOverlay').classList.remove('open');
  }
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModalDirect(); });

  /* ── Export CSV ── */
  function exportCSV() {
    const rows = [['ID', 'Description', 'Latitude', 'Longitude', 'Status', 'Date']];
    filtered.forEach(r => rows.push([r.id, r.description || '', r.latitude || '', r.longitude || '', r.status, r.date]));
    const csv  = rows.map(r => r.map(v => `"${String(v).replace(/"/g, '""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = Object.assign(document.createElement('a'), { href: url, download: 'my_reports.csv' });
    document.body.appendChild(a); a.click(); document.body.removeChild(a);
    URL.revokeObjectURL(url);
  }

  render();
</script>
</body>
</html>