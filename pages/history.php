<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SalahHomty – Complaint History</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/history.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>

<!-- ── NAVBAR ── -->
<nav>
  <a href="#" class="logo">
    <div class="logo-icon">ah</div>
    <span class="logo-text">Salah<span>Homty</span></span>
  </a>
  <div class="nav-links">
    <a href="../index.php" class="btn-report">Report a problem</a>
    <a href="signin.php">Sign in</a>
    <a href="history.php" id="nav-historic" class="active">History</a>
    
    <a href="helppage.php">Help</a>
  </div>
</nav>

<!-- ── PAGE ── -->
<div class="page">

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1>Complaint History</h1>
      <p>Track all submitted complaints and their processing status.</p>
    </div>
    <button class="export-btn">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Export CSV
    </button>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card all">
      <div class="stat-label">Total</div>
      <div class="stat-value">128</div>
      <div class="stat-sub">complaints</div>
    </div>
    <div class="stat-card open">
      <div class="stat-label">Open</div>
      <div class="stat-value">34</div>
      <div class="stat-sub">pending</div>
    </div>
    <div class="stat-card prog">
      <div class="stat-label">In progress</div>
      <div class="stat-value">51</div>
      <div class="stat-sub">processing</div>
    </div>
    <div class="stat-card done">
      <div class="stat-label">Resolved</div>
      <div class="stat-value">37</div>
      <div class="stat-sub">closed</div>
    </div>
    <div class="stat-card over">
      <div class="stat-label">Overdue</div>
      <div class="stat-value">6</div>
      <div class="stat-sub">deadline exceeded</div>
    </div>
  </div>

  <!-- Admin overdue alert -->
  <div class="admin-alert" id="overdueAlert">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <div>
      <strong>6 complaints have exceeded the processing deadline.</strong>
      You can transfer them to another agent or service using the "Transfer" button in the list below.
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-wrap">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" id="searchInput" placeholder="Search by ID, description, address…" oninput="filterTable()" />
    </div>
    <select class="filter-select" id="filterStatus" onchange="filterTable()">
      <option value="">All statuses</option>
      <option value="Open">Open</option>
      <option value="In progress">In progress</option>
      <option value="Resolved">Resolved</option>
      <option value="Transferred">Transferred</option>
      <option value="Overdue">Overdue</option>
    </select>
    <select class="filter-select" id="filterCat" onchange="filterTable()">
      <option value="">All categories</option>
      <option value="Roadway">Roadway</option>
      <option value="Lighting">Lighting</option>
      <option value="Graffiti">Graffiti</option>
      <option value="Waste">Waste</option>
      <option value="Other">Other</option>
    </select>
  </div>

  <!-- Table -->
  <div class="table-card">
    <table id="claimsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Category</th>
          <th>Description</th>
          <th>Address</th>
          <th>Submission date</th>
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

<script>
  const claims = [
    { id:"REC-0041", cat:"Roadway",    desc:"Dangerous pothole",                  addr:"Habib Bourguiba Ave, 12",    date:"Mar 12, 2025",   status:"Overdue" },
    { id:"REC-0040", cat:"Graffiti",  desc:"Tags on school wall",                 addr:"Republic Street, 5",        date:"Mar 11, 2025",   status:"Open" },
    { id:"REC-0039", cat:"Lighting",  desc:"Streetlight out for 3 days",          addr:"November 7 Blvd, 44",       date:"Mar 10, 2025",   status:"In progress" },
    { id:"REC-0038", cat:"Waste",     desc:"Illegal dumping near park",           addr:"Jasmine Lane, 3",           date:"Mar 9, 2025",    status:"Resolved" },
    { id:"REC-0037", cat:"Roadway",   desc:"Cracked sidewalk, trip hazard",       addr:"Ibn Khaldoun Street, 21",   date:"Mar 8, 2025",    status:"Transferred" },
    { id:"REC-0036", cat:"Lighting",  desc:"Intersection traffic lights failing", addr:"Freedom Ave, 78",            date:"Mar 7, 2025",    status:"Overdue" },
    { id:"REC-0035", cat:"Graffiti",  desc:"Building facade vandalism",           addr:"Alain Savary Street, 9",    date:"Mar 6, 2025",    status:"In progress" },
    { id:"REC-0034", cat:"Waste",     desc:"Overturned bins not collected",       addr:"Orange Trees Street, 17",   date:"Mar 5, 2025",    status:"Resolved" },
    { id:"REC-0033", cat:"Roadway",   desc:"Missing road signage",                addr:"Mohamed V Ave, 33",         date:"Mar 4, 2025",    status:"Open" },
    { id:"REC-0032", cat:"Other",     desc:"Broken public bench in garden",       addr:"January 14 Square, 1",      date:"Mar 3, 2025",    status:"Overdue" },
    { id:"REC-0031", cat:"Roadway",   desc:"Clogged drain causing flooding",      addr:"Market Alley, 2",           date:"Mar 2, 2025",    status:"In progress" },
    { id:"REC-0030", cat:"Lighting",  desc:"Leaning utility poles",               addr:"Earth Blvd, 66",            date:"Mar 1, 2025",    status:"Resolved" },
  ];

  const statusClass = {
    "Open":          "s-open open",
    "In progress":   "s-progress progress",
    "Resolved":      "s-resolved resolved",
    "Transferred":   "s-transferred transferred",
    "Overdue":       "s-overdue overdue",
  };

  const catIcon = {
    "Roadway": "🛣️", "Lighting": "💡", "Graffiti": "🎨", "Waste": "🗑️", "Other": "📌"
  };

  let currentPage = 1;
  const perPage = 8;
  let filtered = [...claims];

  function filterTable() {
    const q  = document.getElementById('searchInput').value.toLowerCase();
    const st = document.getElementById('filterStatus').value;
    const ct = document.getElementById('filterCat').value;
    filtered = claims.filter(c =>
      (!q  || c.id.toLowerCase().includes(q) || c.desc.toLowerCase().includes(q) || c.addr.toLowerCase().includes(q)) &&
      (!st || c.status === st) &&
      (!ct || c.cat === ct)
    );
    currentPage = 1;
    render();
  }

  function render() {
    const start = (currentPage - 1) * perPage;
    const page  = filtered.slice(start, start + perPage);
    const tbody = document.getElementById('tableBody');

    if (filtered.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7"><div class="empty">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <p>No complaints found</p></div></td></tr>`;
    } else {
      tbody.innerHTML = page.map(c => {
        const [sc, dc] = statusClass[c.status].split(' ');
        const isOverdue = c.status === "Overdue";
        return `<tr>
          <td><span class="claim-id">${c.id}</span></td>
          <td><span class="category-badge">${catIcon[c.cat] || '📌'} ${c.cat}</span></td>
          <td>${c.desc}</td>
          <td class="date-col">${c.addr}</td>
          <td class="date-col">${c.date}</td>
          <td><span class="status-badge ${sc}"><span class="dot ${dc}"></span>${c.status}</span></td>
          <td>
            ${isOverdue
              ? `<button class="action-btn transfer-btn" onclick="transfer('${c.id}')">⇄ Transfer</button>`
              : `<button class="action-btn" onclick="view('${c.id}')">View details</button>`}
          </td>
        </tr>`;
      }).join('');
    }

    // pagination info
    const total = filtered.length;
    document.getElementById('paginationInfo').textContent =
      `Showing ${Math.min(start+1,total)}–${Math.min(start+perPage,total)} of ${total} complaints`;

    // page buttons
    const pages = Math.ceil(total / perPage);
    const btns  = document.getElementById('pageBtns');
    btns.innerHTML = '';
    for (let i = 1; i <= pages; i++) {
      const b = document.createElement('button');
      b.textContent = i;
      if (i === currentPage) b.classList.add('active');
      b.addEventListener('click', () => { currentPage = i; render(); });
      btns.appendChild(b);
    }
  }

  function view(id) {
    alert(`Details of complaint ${id}\n(Open detail panel here)`);
  }

  function transfer(id) {
    if (confirm(`Transfer complaint ${id} to another agent or service?`)) {
      alert(`Complaint ${id} successfully transferred.`);
    }
  }

  // Nav active toggle
  document.querySelectorAll('.nav-links a').forEach(a => {
    a.addEventListener('click', function(e) {
      if (this.classList.contains('btn-report')) return;
      document.querySelectorAll('.nav-links a').forEach(x => x.classList.remove('active'));
      this.classList.add('active');
    });
  });

  render();
</script>
</body>
</html>