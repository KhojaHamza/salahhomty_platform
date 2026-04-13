<?php
// user/services.php
if (session_status() === PHP_SESSION_NONE) session_start();
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SalahHomty — Municipal Services</title>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'Segoe UI', sans-serif; background: #0d1f2d; color: #fff; min-height: 100vh; }
a { text-decoration: none; color: inherit; }

/* NAVBAR */
.sh-nav { display:flex; justify-content:space-between; align-items:center; padding:16px 32px; background:#0d1f2d; border-bottom:1px solid rgba(255,255,255,0.07); position:sticky; top:0; z-index:200; }
.sh-logo { display:flex; align-items:center; gap:10px; font-size:20px; font-weight:700; color:#fff; letter-spacing:-0.5px; }
.sh-logo-icon { width:36px; height:36px; background:#1abc9c; border-radius:9px; display:flex; align-items:center; justify-content:center; font-size:18px; }
.sh-logo span { color:#1abc9c; }
.sh-nav-right { display:flex; align-items:center; gap:4px; }
.sh-nav-right a { color:rgba(255,255,255,0.55); font-size:13px; padding:7px 14px; border-radius:8px; transition:all 0.2s; }
.sh-nav-right a:hover { color:#fff; background:rgba(255,255,255,0.08); }
.sh-nav-right a.active { color:#fff; background:rgba(26,188,156,0.18); }
.sh-signin { background:#1abc9c; color:#fff; font-size:13px; font-weight:600; padding:8px 18px; border-radius:8px; border:none; cursor:pointer; transition:opacity 0.2s; margin-left:8px; }
.sh-signin:hover { opacity:0.85; }

/* HERO */
.sh-hero { background:linear-gradient(135deg,#1abc9c 0%,#0f9b77 100%); padding:52px 32px 44px; }
.sh-hero-label { display:inline-flex; align-items:center; gap:6px; background:rgba(255,255,255,0.18); border-radius:20px; padding:4px 12px; font-size:11px; font-weight:600; letter-spacing:1px; text-transform:uppercase; margin-bottom:18px; }
.sh-hero-dot { width:7px; height:7px; border-radius:50%; background:#fff; display:inline-block; }
.sh-hero h1 { font-size:44px; font-weight:800; line-height:1.1; max-width:540px; margin-bottom:16px; }
.sh-hero p { font-size:14px; opacity:0.82; max-width:420px; line-height:1.7; }

/* CONTAINER */
.sh-container { padding:32px; }

/* TABS */
.sh-tabs { display:flex; gap:4px; margin-bottom:24px; background:rgba(255,255,255,0.05); border-radius:12px; padding:4px; width:fit-content; }
.sh-tab { padding:8px 20px; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; transition:all 0.2s; color:rgba(255,255,255,0.5); border:none; background:none; }
.sh-tab.active { background:#1abc9c; color:#fff; }

/* SEARCH + FILTER */
.sh-top-bar { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; align-items:center; }
.sh-search { flex:1; min-width:180px; position:relative; }
.sh-search input { width:100%; padding:10px 14px 10px 38px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.07); color:#fff; font-size:13px; outline:none; transition:border-color 0.2s; }
.sh-search input:focus { border-color:rgba(26,188,156,0.5); }
.sh-search input::placeholder { color:rgba(255,255,255,0.38); }
.sh-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); opacity:0.45; font-size:14px; pointer-events:none; }
.sh-select { padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.07); color:#fff; font-size:13px; outline:none; cursor:pointer; }
.sh-select option { background:#1a2e3f; color:#fff; }

/* CATEGORIES */
.sh-cats { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:28px; }
.sh-cat { padding:8px 18px; border-radius:20px; border:1px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.06); color:rgba(255,255,255,0.7); font-size:13px; cursor:pointer; transition:all 0.2s; }
.sh-cat:hover { background:rgba(255,255,255,0.12); color:#fff; }
.sh-cat.active { background:#1abc9c; color:#fff; border-color:#1abc9c; font-weight:600; }

/* CARDS GRID */
.sh-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:16px; }
.sh-card { background:#1a2e3f; border:1px solid rgba(255,255,255,0.08); border-radius:16px; padding:22px; transition:transform 0.2s,border-color 0.2s; }
.sh-card:hover { transform:translateY(-3px); border-color:rgba(26,188,156,0.35); }
.sh-card-icon { width:42px; height:42px; border-radius:10px; background:rgba(26,188,156,0.15); display:flex; align-items:center; justify-content:center; font-size:20px; margin-bottom:14px; }
.sh-card h3 { font-size:15px; font-weight:600; color:#fff; margin-bottom:6px; }
.sh-card-region { font-size:12px; color:rgba(255,255,255,0.45); margin-bottom:8px; }
.sh-card-hours { font-size:11px; color:rgba(255,255,255,0.35); margin-bottom:12px; }
.sh-status { font-size:12px; font-weight:600; padding:4px 10px; border-radius:20px; display:inline-block; margin-bottom:16px; }
.sh-available   { background:rgba(26,188,156,0.15); color:#1abc9c; }
.sh-unavailable { background:rgba(231,76,60,0.12);  color:#e74c3c; }
.sh-maintenance { background:rgba(245,166,35,0.12); color:#f5a623; }
.sh-card-actions { display:flex; gap:8px; }
.sh-btn { padding:8px 14px; border-radius:8px; font-size:12px; font-weight:600; border:none; cursor:pointer; transition:opacity 0.2s; }
.sh-btn:hover { opacity:0.82; }
.sh-btn-detail  { background:rgba(255,255,255,0.1); color:rgba(255,255,255,0.85); }
.sh-btn-request { background:#1abc9c; color:#fff; }

/* MAP */
#sh-map { width:100%; height:420px; border-radius:16px; border:1px solid rgba(255,255,255,0.1); margin-bottom:10px; }

/* LOADING / EMPTY */
.sh-empty { color:rgba(255,255,255,0.35); font-size:14px; text-align:center; padding:60px 0; grid-column:1/-1; }
.sh-loading { text-align:center; padding:60px 0; grid-column:1/-1; color:rgba(255,255,255,0.4); font-size:14px; }

/* MODAL OVERLAY */
.sh-modal-bg { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.65); z-index:999; align-items:center; justify-content:center; }
.sh-modal-bg.open { display:flex; }
.sh-modal { background:#1a2e3f; border:1px solid rgba(255,255,255,0.1); border-radius:20px; padding:30px; width:420px; max-width:92vw; max-height:90vh; overflow-y:auto; }
.sh-modal h3 { font-size:16px; font-weight:700; color:#fff; margin-bottom:4px; }
.sh-modal-sub { font-size:12px; color:rgba(255,255,255,0.4); margin-bottom:20px; }
.sh-modal-info { background:rgba(255,255,255,0.05); border-radius:10px; padding:14px; margin-bottom:16px; font-size:13px; line-height:1.8; color:rgba(255,255,255,0.75); }
.sh-modal-info strong { color:#fff; }
.sh-modal textarea { width:100%; padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.06); color:#fff; font-size:13px; outline:none; resize:vertical; font-family:inherit; transition:border-color 0.2s; min-height:80px; }
.sh-modal textarea:focus { border-color:rgba(26,188,156,0.5); }
.sh-modal textarea::placeholder { color:rgba(255,255,255,0.3); }
.sh-modal-actions { display:flex; gap:8px; margin-top:14px; }
.sh-btn-cancel { background:rgba(255,255,255,0.08); color:rgba(255,255,255,0.7); flex:1; }
.sh-btn-send   { background:#1abc9c; color:#fff; flex:1; }
.sh-qr { text-align:center; margin:14px 0; }
.sh-qr img { width:120px; height:120px; border-radius:8px; border:2px solid rgba(255,255,255,0.1); }

/* DETAIL MODAL */
.sh-detail-modal { width:500px; }
.sh-detail-badge { display:inline-flex; align-items:center; gap:6px; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:600; margin-bottom:16px; }

/* TOAST */
.sh-toast { position:fixed; bottom:24px; right:24px; z-index:1000; background:#1a2e3f; border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:12px 20px; font-size:13px; font-weight:500; box-shadow:0 8px 24px rgba(0,0,0,0.3); transform:translateY(20px); opacity:0; pointer-events:none; transition:all 0.25s; }
.sh-toast.show { transform:none; opacity:1; }
.sh-toast.success { border-color:rgba(26,188,156,0.4); color:#1abc9c; }
.sh-toast.error   { border-color:rgba(231,76,60,0.4);  color:#e74c3c; }

@media(max-width:640px){
  .sh-nav{padding:14px 16px;}
  .sh-nav-right a{display:none;}
  .sh-hero{padding:36px 16px 32px;}
  .sh-hero h1{font-size:28px;}
  .sh-container{padding:20px 16px;}
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="sh-nav">
  <div class="sh-logo">
    <div class="sh-logo-icon">🔧</div>
    Salah<span>Homty</span>
  </div>
  <div class="sh-nav-right">
    <a href="../index.php">Report a Problem</a>
    <a href="services.php" class="active">Services</a>
    <a href="history.php">History</a>
    <a href="profil.php">Profile</a>
    <?php if ($isLoggedIn): ?>
      <a href="../auth/logout.php" class="sh-signin" style="background:rgba(255,255,255,0.1)">Sign Out</a>
    <?php else: ?>
      <a href="signin.php" class="sh-signin">Sign In</a>
    <?php endif; ?>
    <a href="helppage.php">Help</a>
  </div>
</nav>

<!-- HERO -->
<div class="sh-hero">
  <div class="sh-hero-label">
    <span class="sh-hero-dot"></span> Municipal Services — Sfax
  </div>
  <h1>Explore & Request<br>Municipal Services</h1>
  <p>Browse available services in your region, view details, scan QR codes, and submit service requests directly.</p>
</div>

<!-- MAIN -->
<div class="sh-container">

  <!-- TABS -->
  <div class="sh-tabs">
    <button class="sh-tab active" id="tab-list" onclick="switchTab('list')">📋 Services</button>
    <button class="sh-tab" id="tab-map"  onclick="switchTab('map')">🗺️ Map</button>
  </div>

  <!-- LIST VIEW -->
  <div id="view-list">
    <div class="sh-top-bar">
      <div class="sh-search">
        <span class="sh-search-icon">🔍</span>
        <input type="text" id="sh-search" placeholder="Search for a service...">
      </div>
      <select class="sh-select" id="sh-region">
        <option value="">All regions</option>
        <option value="Sfax">Sfax</option>
        <option value="Sousse">Sousse</option>
        <option value="Tunis">Tunis</option>
        <option value="Monastir">Monastir</option>
        <option value="Bizerte">Bizerte</option>
      </select>
      <select class="sh-select" id="sh-status">
        <option value="">All statuses</option>
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
        <option value="Maintenance">Maintenance</option>
      </select>
    </div>

    <div class="sh-cats">
      <button class="sh-cat active" data-cat="all">All</button>
      <button class="sh-cat" data-cat="Water">Water</button>
      <button class="sh-cat" data-cat="Environment">Environment</button>
      <button class="sh-cat" data-cat="Lighting">Lighting</button>
      <button class="sh-cat" data-cat="Infrastructure">Infrastructure</button>
      <button class="sh-cat" data-cat="Waste">Waste</button>
    </div>

    <div class="sh-grid" id="sh-list">
      <div class="sh-loading">Loading services…</div>
    </div>
  </div>

  <!-- MAP VIEW -->
  <div id="view-map" style="display:none">
    <div id="sh-map"></div>
    <p style="font-size:12px;color:rgba(255,255,255,0.35);text-align:center">
      🟢 Available &nbsp;|&nbsp; 🔴 Unavailable / Maintenance
    </p>
  </div>

</div>

<!-- DETAIL MODAL -->
<div class="sh-modal-bg" id="modal-detail">
  <div class="sh-modal sh-detail-modal">
    <h3 id="d-name">—</h3>
    <div class="sh-modal-sub" id="d-category">—</div>
    <div id="d-status-badge" class="sh-detail-badge"></div>
    <div class="sh-modal-info" id="d-info"></div>
    <div class="sh-qr" id="d-qr" style="display:none">
      <p style="font-size:11px;color:rgba(255,255,255,0.4);margin-bottom:8px">QR Code</p>
      <img id="d-qr-img" src="" alt="QR Code"/>
    </div>
    <div class="sh-modal-actions">
      <button class="sh-btn sh-btn-cancel" onclick="closeModal('modal-detail')">Close</button>
      <button class="sh-btn sh-btn-request" id="d-request-btn" onclick="openRequestFromDetail()">Request Service</button>
    </div>
  </div>
</div>

<!-- REQUEST MODAL -->
<div class="sh-modal-bg" id="modal-request">
  <div class="sh-modal">
    <h3>Request a Service</h3>
    <div class="sh-modal-sub" id="req-service-name">—</div>
    <?php if (!$isLoggedIn): ?>
      <div style="background:rgba(231,76,60,0.1);border:1px solid rgba(231,76,60,0.3);border-radius:10px;padding:14px;font-size:13px;color:#e74c3c;margin-bottom:16px;">
        ⚠️ You must <a href="signin.php" style="color:#1abc9c;font-weight:600;">sign in</a> to submit a request.
      </div>
    <?php endif; ?>
    <textarea id="req-message" placeholder="Describe your request or issue (optional)…"></textarea>
    <div class="sh-modal-actions">
      <button class="sh-btn sh-btn-cancel" onclick="closeModal('modal-request')">Cancel</button>
      <button class="sh-btn sh-btn-send" id="req-submit" onclick="submitRequest()" <?= !$isLoggedIn ? 'disabled style="opacity:.4;cursor:not-allowed"' : '' ?>>Submit</button>
    </div>
  </div>
</div>

<!-- TOAST -->
<div class="sh-toast" id="sh-toast"></div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── State ─────────────────────────────────────────────────
let allServices  = [];
let currentCat   = 'all';
let currentSvcId = null;
let mapInstance  = null;
let mapLoaded    = false;
let searchTimer  = null;

// ── API ───────────────────────────────────────────────────
async function apiFetch(params = {}) {
  const url = new URL('../actions/services.php', location.href);
  Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
  const res  = await fetch(url);
  return res.json();
}

async function apiPost(data = {}) {
  const fd = new FormData();
  Object.entries(data).forEach(([k, v]) => fd.append(k, v ?? ''));
  const res = await fetch('../actions/services.php', { method: 'POST', body: fd });
  return res.json();
}

// ── Toast ─────────────────────────────────────────────────
let toastTimer;
function toast(msg, type = 'success') {
  const el = document.getElementById('sh-toast');
  el.textContent = msg;
  el.className = `sh-toast show ${type}`;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => el.classList.remove('show'), 3500);
}

// ── Helpers ───────────────────────────────────────────────
const catIcons = { Water:'💧', Environment:'🌿', Lighting:'💡', Infrastructure:'🏗️', Waste:'♻️' };

function statusClass(s) {
  if (s === 'Active') return 'sh-available';
  if (s === 'Maintenance') return 'sh-maintenance';
  return 'sh-unavailable';
}

function statusLabel(s) {
  if (s === 'Active') return '✔ Available';
  if (s === 'Maintenance') return '🔧 Maintenance';
  return '✖ Unavailable';
}

// ── Load & Render Services ────────────────────────────────
async function loadServices() {
  const search = document.getElementById('sh-search').value.trim();
  const region = document.getElementById('sh-region').value;
  const status = document.getElementById('sh-status').value;

  const params = { action: 'list', search, region, status };
  if (currentCat !== 'all') params.category = currentCat;

  const r = await apiFetch(params);
  if (!r.success) { toast('Failed to load services', 'error'); return; }

  allServices = r.services;
  renderCards(allServices);
}

function renderCards(services) {
  const list = document.getElementById('sh-list');
  if (!services.length) {
    list.innerHTML = '<div class="sh-empty">📭 No services found.</div>';
    return;
  }
  list.innerHTML = services.map(s => `
    <div class="sh-card">
      <div class="sh-card-icon">${catIcons[s.category] || '🔧'}</div>
      <h3>${esc(s.name)}</h3>
      ${s.region ? `<div class="sh-card-region">📍 ${esc(s.region)}</div>` : '<div class="sh-card-region">📍 —</div>'}
      <div class="sh-card-hours">🕐 ${s.availability_start || '08:00'} – ${s.availability_end || '18:00'}</div>
      <span class="sh-status ${statusClass(s.status)}">${statusLabel(s.status)}</span>
      <div class="sh-card-actions">
        <button class="sh-btn sh-btn-detail" onclick="openDetail(${s.id})">Details</button>
        ${s.status === 'Active'
          ? `<button class="sh-btn sh-btn-request" onclick="openRequest(${s.id},'${esc(s.name)}')">Request</button>`
          : ''}
      </div>
    </div>`).join('');
}

// ── Detail Modal ──────────────────────────────────────────
async function openDetail(id) {
  currentSvcId = id;
  const r = await apiFetch({ action: 'detail', id });
  if (!r.success) { toast('Service not found', 'error'); return; }
  const s = r.service;

  document.getElementById('d-name').textContent     = s.name;
  document.getElementById('d-category').textContent = s.category;

  const badge = document.getElementById('d-status-badge');
  badge.textContent  = statusLabel(s.status);
  badge.className    = `sh-detail-badge ${statusClass(s.status)}`;

  document.getElementById('d-info').innerHTML = `
    <strong>Category:</strong> ${esc(s.category)}<br>
    <strong>Region:</strong>   ${esc(s.region || '—')}<br>
    <strong>Hours:</strong>    ${s.availability_start || '—'} – ${s.availability_end || '—'}<br>
    ${s.description ? `<strong>Description:</strong> ${esc(s.description)}<br>` : ''}
    ${s.latitude && s.longitude
      ? `<strong>Location:</strong> <a href="https://maps.google.com/?q=${s.latitude},${s.longitude}" target="_blank" style="color:#1abc9c">View on map</a><br>`
      : ''}
  `;

  // QR code
  const qrWrap = document.getElementById('d-qr');
  if (s.qr_code) {
    document.getElementById('d-qr-img').src = s.qr_code;
    qrWrap.style.display = 'block';
  } else {
    qrWrap.style.display = 'none';
  }

  // Hide request btn if not active
  document.getElementById('d-request-btn').style.display = s.status === 'Active' ? '' : 'none';

  openModal('modal-detail');
}

function openRequestFromDetail() {
  closeModal('modal-detail');
  const svc = allServices.find(s => s.id == currentSvcId);
  if (svc) openRequest(svc.id, svc.name);
}

// ── Request Modal ─────────────────────────────────────────
function openRequest(id, name) {
  currentSvcId = id;
  document.getElementById('req-service-name').textContent = name;
  document.getElementById('req-message').value = '';
  openModal('modal-request');
}

async function submitRequest() {
  const message = document.getElementById('req-message').value.trim();
  const btn = document.getElementById('req-submit');
  btn.disabled = true;
  btn.textContent = 'Sending…';

  const r = await apiPost({ action: 'request', service_id: currentSvcId, message });

  btn.disabled = false;
  btn.textContent = 'Submit';

  if (!r.success) { toast('❌ ' + r.message, 'error'); return; }
  toast('✅ ' + r.message, 'success');
  closeModal('modal-request');
}

// ── Map ───────────────────────────────────────────────────
function switchTab(tab) {
  document.getElementById('tab-list').classList.toggle('active', tab === 'list');
  document.getElementById('tab-map').classList.toggle('active', tab === 'map');
  document.getElementById('view-list').style.display = tab === 'list' ? '' : 'none';
  document.getElementById('view-map').style.display  = tab === 'map'  ? '' : 'none';

  if (tab === 'map' && !mapLoaded) initMap();
}

function initMap() {
  mapLoaded = true;
  mapInstance = L.map('sh-map', { zoomControl: true }).setView([34.74, 10.76], 7);

  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '© OpenStreetMap © CARTO',
    maxZoom: 18
  }).addTo(mapInstance);

  // Plot all services that have coordinates
  const withCoords = allServices.filter(s => s.latitude && s.longitude);

  if (!withCoords.length) {
    // No coords — show a note
    L.popup().setLatLng([34.74, 10.76]).setContent('<b style="color:#333">No services with location data yet.</b>').openOn(mapInstance);
    return;
  }

  const bounds = [];
  withCoords.forEach(s => {
    const lat = parseFloat(s.latitude);
    const lng = parseFloat(s.longitude);
    bounds.push([lat, lng]);

    const color  = s.status === 'Active' ? '#1abc9c' : '#e74c3c';
    const marker = L.circleMarker([lat, lng], {
      radius: 10,
      fillColor: color,
      color: '#fff',
      weight: 2,
      opacity: 1,
      fillOpacity: 0.85
    }).addTo(mapInstance);

    marker.bindPopup(`
      <div style="font-family:Segoe UI,sans-serif;min-width:160px">
        <strong style="font-size:14px">${esc(s.name)}</strong><br>
        <span style="color:#888;font-size:12px">${esc(s.category)} · ${esc(s.region || '—')}</span><br>
        <span style="font-size:12px;font-weight:600;color:${color}">${statusLabel(s.status)}</span><br>
        <span style="font-size:11px;color:#666">🕐 ${s.availability_start} – ${s.availability_end}</span>
      </div>
    `);
  });

  if (bounds.length) mapInstance.fitBounds(bounds, { padding: [40, 40] });
}

// ── Modals ────────────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.sh-modal-bg').forEach(bg => {
  bg.addEventListener('click', e => { if (e.target === bg) bg.classList.remove('open'); });
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.sh-modal-bg.open').forEach(m => m.classList.remove('open'));
});

// ── Filters ───────────────────────────────────────────────
document.querySelectorAll('.sh-cat').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.sh-cat').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentCat = btn.dataset.cat;
    loadServices();
  });
});

document.getElementById('sh-search').addEventListener('input', () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadServices, 350);
});

document.getElementById('sh-region').addEventListener('change', loadServices);
document.getElementById('sh-status').addEventListener('change', loadServices);

// ── Escape helper ─────────────────────────────────────────
function esc(str) {
  if (str == null) return '';
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// ── Init ──────────────────────────────────────────────────
loadServices();
</script>
</body>
</html>