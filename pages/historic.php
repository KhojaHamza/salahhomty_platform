<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SalahHomty – Historique des réclamations</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Syne:wght@700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../assets/css/historic.css" />
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
    <a href="#" class="btn-report">Report a problem</a>
    <a href="#">Sign in</a>
    <a href="#" id="nav-historic" class="active">Historique</a>
    <a href="#">Local alerts</a>
    <a href="#">Help</a>
  </div>
</nav>

<!-- ── PAGE ── -->
<div class="page">

  <!-- Header -->
  <div class="page-header">
    <div>
      <h1>Historique des réclamations</h1>
      <p>Suivez toutes les réclamations soumises et leur état de traitement.</p>
    </div>
    <button class="export-btn">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
      </svg>
      Exporter CSV
    </button>
  </div>

  <!-- Stats -->
  <div class="stats-row">
    <div class="stat-card all">
      <div class="stat-label">Total</div>
      <div class="stat-value">128</div>
      <div class="stat-sub">réclamations</div>
    </div>
    <div class="stat-card open">
      <div class="stat-label">Ouvertes</div>
      <div class="stat-value">34</div>
      <div class="stat-sub">en attente</div>
    </div>
    <div class="stat-card prog">
      <div class="stat-label">En cours</div>
      <div class="stat-value">51</div>
      <div class="stat-sub">traitées</div>
    </div>
    <div class="stat-card done">
      <div class="stat-label">Résolues</div>
      <div class="stat-value">37</div>
      <div class="stat-sub">clôturées</div>
    </div>
    <div class="stat-card over">
      <div class="stat-label">En retard</div>
      <div class="stat-value">6</div>
      <div class="stat-sub">dépassé le délai</div>
    </div>
  </div>

  <!-- Admin overdue alert -->
  <div class="admin-alert" id="overdueAlert">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <div>
      <strong>6 réclamations ont dépassé le délai de traitement.</strong>
      Vous pouvez les transférer à un autre agent ou service via le bouton « Transférer » dans la liste ci-dessous.
    </div>
  </div>

  <!-- Toolbar -->
  <div class="toolbar">
    <div class="search-wrap">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" id="searchInput" placeholder="Rechercher par ID, description, adresse…" oninput="filterTable()" />
    </div>
    <select class="filter-select" id="filterStatus" onchange="filterTable()">
      <option value="">Tous les statuts</option>
      <option value="Ouverte">Ouverte</option>
      <option value="En cours">En cours</option>
      <option value="Résolue">Résolue</option>
      <option value="Transférée">Transférée</option>
      <option value="En retard">En retard</option>
    </select>
    <select class="filter-select" id="filterCat" onchange="filterTable()">
      <option value="">Toutes catégories</option>
      <option value="Voirie">Voirie</option>
      <option value="Éclairage">Éclairage</option>
      <option value="Graffiti">Graffiti</option>
      <option value="Déchets">Déchets</option>
      <option value="Autre">Autre</option>
    </select>
  </div>

  <!-- Table -->
  <div class="table-card">
    <table id="claimsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Catégorie</th>
          <th>Description</th>
          <th>Adresse</th>
          <th>Date soumission</th>
          <th>Statut</th>
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
    { id:"REC-0041", cat:"Voirie",    desc:"Nid de poule dangereux",           addr:"Av. Habib Bourguiba, 12",    date:"12 mars 2025",   status:"En retard" },
    { id:"REC-0040", cat:"Graffiti",  desc:"Tags sur mur de l'école",          addr:"Rue de la République, 5",    date:"11 mars 2025",   status:"Ouverte" },
    { id:"REC-0039", cat:"Éclairage", desc:"Lampadaire éteint depuis 3 jours", addr:"Bd du 7 novembre, 44",       date:"10 mars 2025",   status:"En cours" },
    { id:"REC-0038", cat:"Déchets",   desc:"Dépôt sauvage près du parc",       addr:"Allée des Jasmins, 3",       date:"9 mars 2025",    status:"Résolue" },
    { id:"REC-0037", cat:"Voirie",    desc:"Trottoir fissuré, risque de chute", addr:"Rue Ibn Khaldoun, 21",       date:"8 mars 2025",    status:"Transférée" },
    { id:"REC-0036", cat:"Éclairage", desc:"Feux de carrefour défaillants",    addr:"Av. de la Liberté, 78",      date:"7 mars 2025",    status:"En retard" },
    { id:"REC-0035", cat:"Graffiti",  desc:"Dégradation façade immeuble",      addr:"Rue Alain Savary, 9",        date:"6 mars 2025",    status:"En cours" },
    { id:"REC-0034", cat:"Déchets",   desc:"Bacs renversés non ramassés",       addr:"Rue des Orangers, 17",       date:"5 mars 2025",    status:"Résolue" },
    { id:"REC-0033", cat:"Voirie",    desc:"Signalisation routière manquante", addr:"Av. Mohamed V, 33",          date:"4 mars 2025",    status:"Ouverte" },
    { id:"REC-0032", cat:"Autre",     desc:"Banc public cassé au jardin",       addr:"Pl. du 14 janvier, 1",       date:"3 mars 2025",    status:"En retard" },
    { id:"REC-0031", cat:"Voirie",    desc:"Caniveau bouché inondation",        addr:"Impasse du Marché, 2",       date:"2 mars 2025",    status:"En cours" },
    { id:"REC-0030", cat:"Éclairage", desc:"Poteaux électriques inclinés",      addr:"Bd de la Terre, 66",         date:"1 mars 2025",    status:"Résolue" },
  ];

  const statusClass = {
    "Ouverte":     "s-open open",
    "En cours":    "s-progress progress",
    "Résolue":     "s-resolved resolved",
    "Transférée":  "s-transferred transferred",
    "En retard":   "s-overdue overdue",
  };

  const catIcon = {
    "Voirie": "🛣️", "Éclairage": "💡", "Graffiti": "🎨", "Déchets": "🗑️", "Autre": "📌"
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
        <p>Aucune réclamation trouvée</p></div></td></tr>`;
    } else {
      tbody.innerHTML = page.map(c => {
        const [sc, dc] = statusClass[c.status].split(' ');
        const isOverdue = c.status === "En retard";
        return `<tr>
          <td><span class="claim-id">${c.id}</span></td>
          <td><span class="category-badge">${catIcon[c.cat] || '📌'} ${c.cat}</span></td>
          <td>${c.desc}</td>
          <td class="date-col">${c.addr}</td>
          <td class="date-col">${c.date}</td>
          <td><span class="status-badge ${sc}"><span class="dot ${dc}"></span>${c.status}</span></td>
          <td>
            ${isOverdue
              ? `<button class="action-btn transfer-btn" onclick="transfer('${c.id}')">⇄ Transférer</button>`
              : `<button class="action-btn" onclick="view('${c.id}')">Voir détails</button>`}
          </td>
        </tr>`;
      }).join('');
    }

    // pagination info
    const total = filtered.length;
    document.getElementById('paginationInfo').textContent =
      `Affichage ${Math.min(start+1,total)}–${Math.min(start+perPage,total)} sur ${total} réclamations`;

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
    alert(`Détails de la réclamation ${id}\n(Ouvrir le panneau de détail ici)`);
  }

  function transfer(id) {
    if (confirm(`Transférer la réclamation ${id} à un autre agent ou service ?`)) {
      alert(`Réclamation ${id} transférée avec succès.`);
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