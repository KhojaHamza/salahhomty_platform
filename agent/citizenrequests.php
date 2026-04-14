<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>MuniPortal — Citizen Requests</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
/* ─── ROOT / VARIABLES ──────────────────────────────────── */
:root {
  --bg:           #f0f2f5;
  --sidebar:      #111827;
  --sidebar-hover:#1f2937;
  --accent:       #14b8a6;
  --accent-dark:  #0d9488;
  --accent-light: rgba(20,184,166,.12);
  --white:        #fff;
  --border:       #e5e7eb;
  --text:         #111827;
  --muted:        #6b7280;
  --muted2:       #9ca3af;
  --green:        #10b981;
  --yellow:       #f59e0b;
  --red:          #ef4444;
  --blue:         #3b82f6;
  --purple:       #8b5cf6;
  --r:            12px;
  --sw:           224px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'Poppins', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  display: flex;
  font-size: 14px;
}

/* ─── SIDEBAR ────────────────────────────────────────────── */
.sidebar {
  width: var(--sw);
  min-height: 100vh;
  background: var(--sidebar);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0; bottom: 0;
  z-index: 100;
}
.sb-logo {
  padding: 20px 18px 16px;
  display: flex;
  align-items: center;
  gap: 10px;
  border-bottom: 1px solid rgba(255,255,255,.07);
}
.logo-icon {
  width: 36px; height: 36px;
  background: var(--accent);
  border-radius: 9px;
  display: flex; align-items: center; justify-content: center;
  font-size: 18px;
  flex-shrink: 0;
}
.logo-text { font-size: 15px; font-weight: 700; color: #fff; }
.logo-sub  { font-size: 10px; color: rgba(255,255,255,.4); letter-spacing: .3px; }

.nav { flex: 1; padding: 10px; overflow-y: auto; }
.nav-section {
  font-size: 9px;
  color: rgba(255,255,255,.3);
  letter-spacing: 1.5px;
  font-weight: 600;
  padding: 14px 8px 5px;
  text-transform: uppercase;
}
.nav-item {
  display: flex;
  align-items: center;
  gap: 9px;
  padding: 9px 10px;
  border-radius: 8px;
  font-size: 13px;
  color: rgba(255,255,255,.55);
  cursor: pointer;
  transition: all .15s;
  margin-bottom: 2px;
  font-weight: 500;
  user-select: none;
  text-decoration: none;
}
.nav-item:hover  { background: var(--sidebar-hover); color: rgba(255,255,255,.9); }
.nav-item.active { background: var(--accent); color: #fff; }
.nav-icon { font-size: 15px; width: 16px; text-align: center; flex-shrink: 0; }
.nav-badge {
  margin-left: auto;
  font-size: 10px; font-weight: 700;
  padding: 1px 7px;
  border-radius: 99px;
  background: rgba(255,255,255,.15);
  color: #fff;
  min-width: 22px;
  text-align: center;
}
.nav-item.active .nav-badge { background: rgba(0,0,0,.2); }
.nav-badge.green-badge { background: var(--green); }

.agent-card-sb {
  margin: 10px;
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.08);
  border-radius: 10px;
  padding: 10px;
  display: flex;
  align-items: center;
  gap: 9px;
}
.avatar-sm {
  width: 34px; height: 34px;
  border-radius: 50%;
  background: var(--accent);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 12px;
  color: #fff;
  flex-shrink: 0;
}
.agent-name-sb { font-size: 12px; font-weight: 600; color: #fff; }
.agent-role-sb  { font-size: 10px; color: rgba(255,255,255,.4); }
.status-dot {
  width: 6px; height: 6px;
  border-radius: 50%;
  background: var(--green);
  box-shadow: 0 0 5px var(--green);
  flex-shrink: 0;
}
.sb-footer { padding: 10px; border-top: 1px solid rgba(255,255,255,.07); }
.logout-btn {
  display: flex; align-items: center; gap: 9px;
  padding: 9px 10px;
  border-radius: 8px;
  font-size: 12px;
  color: rgba(255,255,255,.5);
  cursor: pointer;
  background: none;
  border: none;
  width: 100%;
  transition: all .15s;
  font-family: 'Poppins', sans-serif;
  font-weight: 500;
}
.logout-btn:hover { background: rgba(239,68,68,.15); color: #f87171; }

/* ─── MAIN ───────────────────────────────────────────────── */
.main {
  margin-left: var(--sw);
  flex: 1;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}
.topbar {
  height: 58px;
  background: var(--white);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 0 24px;
  position: sticky;
  top: 0;
  z-index: 90;
}
.topbar-title { font-size: 18px; font-weight: 700; flex: 1; color: var(--text); }
.topbar-subtitle { font-size: 13px; font-weight: 400; color: var(--muted); margin-left: 8px; }
.search-box {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 8px;
  padding: 0 12px;
  height: 36px;
  width: 220px;
}
.search-box input {
  background: none;
  border: none;
  outline: none;
  color: var(--text);
  font-family: 'Poppins', sans-serif;
  font-size: 12px;
  flex: 1;
}
.search-box input::placeholder { color: var(--muted2); }
.top-btn {
  height: 36px;
  padding: 0 16px;
  border-radius: 8px;
  background: var(--accent);
  color: #fff;
  border: none;
  font-family: 'Poppins', sans-serif;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: background .15s;
  white-space: nowrap;
}
.top-btn:hover { background: var(--accent-dark); }

/* ─── CONTENT ────────────────────────────────────────────── */
.content { padding: 24px; flex: 1; }

/* ─── STATS ──────────────────────────────────────────────── */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 14px;
  margin-bottom: 24px;
}
.stat-card {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r);
  padding: 18px 20px;
  position: relative;
  overflow: hidden;
  transition: box-shadow .2s, transform .15s;
}
.stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.08); transform: translateY(-1px); }
.stat-card::after {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  border-radius: var(--r) var(--r) 0 0;
}
.stat-card.s-blue::after   { background: var(--blue); }
.stat-card.s-yellow::after { background: var(--yellow); }
.stat-card.s-green::after  { background: var(--green); }
.stat-card.s-red::after    { background: var(--red); }
.stat-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: .7px; font-weight: 600; margin-bottom: 8px; }
.stat-value { font-size: 32px; font-weight: 800; line-height: 1; color: var(--text); margin-bottom: 4px; }
.stat-sub   { font-size: 11px; color: var(--muted2); }

/* ─── GRID LAYOUT ────────────────────────────────────────── */
.grid2 { display: grid; grid-template-columns: 1fr 300px; gap: 18px; }

/* ─── PANELS ─────────────────────────────────────────────── */
.panel {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r);
  overflow: hidden;
}
.panel-head {
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  gap: 10px;
}
.panel-title { font-size: 15px; font-weight: 700; flex: 1; }

/* ─── FILTER TABS ────────────────────────────────────────── */
.filter-tabs {
  display: flex;
  gap: 4px;
  padding: 12px 20px;
  border-bottom: 1px solid var(--border);
  flex-wrap: wrap;
  align-items: center;
}
.ftab {
  font-size: 12px;
  padding: 5px 14px;
  border-radius: 99px;
  cursor: pointer;
  transition: all .15s;
  border: 1px solid transparent;
  color: var(--muted);
  font-weight: 500;
  user-select: none;
}
.ftab:hover { color: var(--text); background: var(--bg); }
.ftab.active {
  background: var(--accent-light);
  color: var(--accent);
  border-color: rgba(20,184,166,.25);
  font-weight: 600;
}
.sort-btn-top {
  margin-left: auto;
  height: 30px;
  padding: 0 12px;
  border-radius: 7px;
  border: 1px solid var(--border);
  background: var(--white);
  color: var(--muted);
  cursor: pointer;
  font-size: 11px;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  transition: all .12s;
}
.sort-btn-top:hover { background: var(--accent-light); color: var(--accent); border-color: rgba(20,184,166,.3); }

/* ─── TABLE ──────────────────────────────────────────────── */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; }
thead th {
  font-size: 11px;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: .7px;
  font-weight: 600;
  padding: 10px 16px;
  text-align: left;
  border-bottom: 1px solid var(--border);
  background: #fafafa;
  white-space: nowrap;
}
tbody tr {
  border-bottom: 1px solid var(--border);
  transition: background .12s;
  cursor: pointer;
}
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f9fafb; }
tbody tr.row-selected { background: var(--accent-light) !important; }
tbody td { padding: 11px 16px; font-size: 13px; vertical-align: middle; }

.c-id   { font-size: 12px; color: var(--accent); font-weight: 700; }
.c-title { font-weight: 500; }
.c-sub  { font-size: 11px; color: var(--muted); margin-top: 2px; }

/* CITIZEN CELL */
.citizen-cell { display: flex; align-items: center; gap: 8px; }
.cit-av {
  width: 28px; height: 28px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 10px;
  color: #fff;
  flex-shrink: 0;
}

/* ─── STATUS BADGES ──────────────────────────────────────── */
.st-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 11px;
  font-weight: 600;
  padding: 4px 10px;
  border-radius: 99px;
  white-space: nowrap;
}
.st-badge .dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
.st-new       { background: #eff6ff; color: #1d4ed8; }
.st-new .dot  { background: var(--blue); }
.st-pending   { background: #fffbeb; color: #b45309; }
.st-pending .dot { background: var(--yellow); }
.st-progress  { background: #f0f9ff; color: #0369a1; }
.st-progress .dot { background: #0ea5e9; animation: pulse 1.5s infinite; }
.st-processed { background: #f0fdf4; color: #065f46; }
.st-processed .dot { background: var(--green); }
.st-rejected  { background: #fef2f2; color: #b91c1c; }
.st-rejected .dot { background: var(--red); }
@keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: .35; } }

/* ─── ACTION BUTTONS ─────────────────────────────────────── */
.act-btns { display: flex; gap: 5px; }
.act-btn {
  height: 28px;
  padding: 0 10px;
  border-radius: 6px;
  border: 1px solid var(--border);
  background: var(--white);
  color: var(--muted);
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  font-size: 11px;
  font-weight: 600;
  transition: all .12s;
  font-family: 'Poppins', sans-serif;
  white-space: nowrap;
}
.act-btn:hover { background: var(--accent-light); color: var(--accent); border-color: rgba(20,184,166,.3); }
.act-btn.btn-accept:hover { background: #f0fdf4; color: #065f46; border-color: #6ee7b7; }
.act-btn.btn-reject:hover { background: #fef2f2; color: #b91c1c; border-color: #fca5a5; }

/* ─── PAGINATION ─────────────────────────────────────────── */
.pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 12px 20px;
  border-top: 1px solid var(--border);
  font-size: 12px;
  color: var(--muted);
  flex-wrap: wrap;
  gap: 8px;
}
.page-btns { display: flex; gap: 5px; flex-wrap: wrap; }
.page-btn {
  width: 30px; height: 30px;
  border-radius: 6px;
  background: var(--white);
  border: 1px solid var(--border);
  color: var(--muted);
  cursor: pointer;
  font-size: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all .12s;
  font-family: 'Poppins', sans-serif;
}
.page-btn:hover,
.page-btn.active-page { background: var(--accent); color: #fff; border-color: var(--accent); }
.page-btn:disabled { opacity: .4; cursor: default; pointer-events: none; }

/* ─── RIGHT COLUMN ───────────────────────────────────────── */
.right-col { display: flex; flex-direction: column; gap: 18px; }

/* ─── DETAIL PANEL ───────────────────────────────────────── */
.detail-panel {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: var(--r);
  overflow: hidden;
}
.detail-panel-head {
  padding: 16px 20px;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(135deg, var(--accent-light) 0%, transparent 80%);
  position: relative;
}
.detail-panel-head::after {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 3px;
  background: var(--accent);
}
.dp-id-row { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.dp-id { font-size: 12px; color: var(--accent); font-weight: 700; }
.dp-title { font-size: 14px; font-weight: 700; color: var(--text); }
.detail-panel-body { padding: 16px 20px; display: flex; flex-direction: column; gap: 12px; }

.info-field {}
.field-lbl {
  font-size: 10px;
  color: var(--muted2);
  text-transform: uppercase;
  letter-spacing: .5px;
  font-weight: 600;
  margin-bottom: 4px;
}
.field-val { font-size: 13px; color: var(--text); font-weight: 500; }

.citizen-info-row {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 12px;
  background: var(--bg);
  border-radius: 8px;
  border: 1px solid var(--border);
}
.cit-av-md {
  width: 36px; height: 36px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 12px;
  color: #fff;
  flex-shrink: 0;
}
.ci-name  { font-size: 13px; font-weight: 600; }
.ci-email { font-size: 11px; color: var(--muted); }

.docs-row { display: flex; gap: 6px; flex-wrap: wrap; }
.doc-pill {
  display: flex; align-items: center; gap: 4px;
  padding: 4px 10px;
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 7px;
  font-size: 11px;
  color: var(--muted);
  cursor: pointer;
  transition: all .12s;
}
.doc-pill:hover { border-color: var(--accent); color: var(--accent); background: var(--accent-light); }

.note-area {
  width: 100%;
  background: var(--bg);
  border: 1.5px solid var(--border);
  border-radius: 8px;
  padding: 9px 12px;
  color: var(--text);
  font-family: 'Poppins', sans-serif;
  font-size: 12px;
  outline: none;
  resize: vertical;
  min-height: 60px;
  transition: border-color .12s;
}
.note-area::placeholder { color: var(--muted2); }
.note-area:focus { border-color: var(--accent); background: #fff; }

.detail-actions { display: flex; gap: 6px; }
.detail-act-btn {
  flex: 1;
  padding: 8px 0;
  border-radius: 8px;
  font-size: 11px;
  font-weight: 600;
  font-family: 'Poppins', sans-serif;
  cursor: pointer;
  border: 1px solid var(--border);
  background: var(--bg);
  color: var(--muted);
  transition: all .15s;
}
.detail-act-btn:hover { color: var(--text); background: #e5e7eb; }
.detail-act-btn.accept { background: #f0fdf4; border-color: #6ee7b7; color: #065f46; }
.detail-act-btn.accept:hover { background: #d1fae5; }
.detail-act-btn.reject { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; }
.detail-act-btn.reject:hover { background: #fee2e2; }

/* ─── BAR CHART ──────────────────────────────────────────── */
.chart-area { padding: 14px 20px 16px; }
.bar-row    { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.bar-label  { font-size: 12px; color: var(--muted); width: 96px; flex-shrink: 0; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.bar-track  { flex: 1; height: 6px; background: #f3f4f6; border-radius: 99px; overflow: hidden; }
.bar-fill   { height: 100%; border-radius: 99px; transition: width .5s ease; }
.bar-count  { font-size: 11px; color: var(--muted); width: 24px; text-align: right; font-weight: 600; }

/* ─── WORKLOAD ───────────────────────────────────────────── */
.workload-item {
  display: flex; align-items: center; gap: 10px;
  padding: 12px 18px;
  border-bottom: 1px solid var(--border);
}
.workload-item:last-child { border-bottom: none; }
.wl-name { font-size: 12px; font-weight: 600; }
.wl-meta { font-size: 11px; color: var(--muted); }
.wl-status { margin-left: auto; font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 99px; }
.wls-active { background: #f0fdf4; color: #065f46; }
.wls-free   { background: #f3f4f6; color: #6b7280; }

/* ─── MODAL ──────────────────────────────────────────────── */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,.4);
  backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  z-index: 200;
  opacity: 0;
  pointer-events: none;
  transition: opacity .2s;
}
.modal-overlay.open { opacity: 1; pointer-events: all; }
.modal {
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 16px;
  width: 520px;
  max-width: 95vw;
  max-height: 90vh;
  overflow-y: auto;
  transform: translateY(14px) scale(.97);
  transition: transform .22s;
  box-shadow: 0 20px 60px rgba(0,0,0,.14);
}
.modal-overlay.open .modal { transform: none; }
.modal-head {
  padding: 20px 24px 16px;
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; gap: 12px;
}
.modal-title { font-size: 16px; font-weight: 700; flex: 1; }
.modal-close {
  width: 30px; height: 30px;
  border-radius: 7px;
  background: var(--bg);
  border: none;
  color: var(--muted);
  cursor: pointer;
  font-size: 16px;
  display: flex; align-items: center; justify-content: center;
  transition: all .12s;
}
.modal-close:hover { background: #fee2e2; color: var(--red); }
.modal-body { padding: 20px 24px; }
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; padding-top: 8px; }

.form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; }
.form-label {
  font-size: 11px; color: var(--muted); font-weight: 600;
  margin-bottom: 5px; display: block;
  letter-spacing: .3px; text-transform: uppercase;
}
.form-control {
  width: 100%;
  background: var(--bg);
  border: 1.5px solid var(--border);
  border-radius: 8px;
  padding: 9px 12px;
  color: var(--text);
  font-family: 'Poppins', sans-serif;
  font-size: 13px;
  outline: none;
  transition: border-color .12s;
}
.form-control:focus { border-color: var(--accent); background: #fff; }
textarea.form-control { resize: vertical; min-height: 70px; }

/* ─── BUTTONS ────────────────────────────────────────────── */
.btn {
  padding: 9px 20px;
  border-radius: 8px;
  font-family: 'Poppins', sans-serif;
  font-size: 13px; font-weight: 600;
  cursor: pointer;
  border: none;
  transition: all .12s;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.btn-primary { background: var(--accent); color: #fff; }
.btn-primary:hover { background: var(--accent-dark); }
.btn-ghost { background: var(--bg); color: var(--muted); border: 1px solid var(--border); }
.btn-ghost:hover { color: var(--text); background: #e5e7eb; }

/* ─── TOAST ──────────────────────────────────────────────── */
.toast {
  position: fixed;
  bottom: 24px; right: 24px;
  z-index: 300;
  background: var(--white);
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 12px 18px;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
  box-shadow: 0 8px 24px rgba(0,0,0,.12);
  transform: translateY(14px);
  opacity: 0;
  pointer-events: none;
  transition: all .25s;
  font-weight: 500;
  max-width: 360px;
}
.toast.show    { transform: none; opacity: 1; pointer-events: all; }
.toast.error   { border-color: #fecaca; background: #fef2f2; color: var(--red); }
.toast.success { border-color: #bbf7d0; color: #065f46; }
.toast.info    { border-color: #bfdbfe; color: #1d4ed8; }

/* ─── EMPTY ──────────────────────────────────────────────── */
.empty { text-align: center; padding: 44px 20px; color: var(--muted); }
.empty .ei { font-size: 36px; margin-bottom: 10px; opacity: .5; }
.empty p   { font-size: 13px; }

/* ─── SCROLLBAR ──────────────────────────────────────────── */
::-webkit-scrollbar       { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 99px; }

/* ─── RESPONSIVE ─────────────────────────────────────────── */
@media (max-width: 1100px) {
  .stats-grid { grid-template-columns: repeat(2,1fr); }
  .grid2      { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
  :root { --sw: 0px; }
  .sidebar { display: none; }
  .main { margin-left: 0; }
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .form-row   { grid-template-columns: 1fr; }
}
</style>
</head>
<body>

<!-- ─── SIDEBAR ─── -->
<aside class="sidebar">
  <div class="sb-logo">
    <div class="logo-icon">🏛️</div>
    <div>
      <div class="logo-text">MuniPortal</div>
      <div class="logo-sub">Agent Space</div>
    </div>
  </div>

  <!-- Agent card -->
  <div class="agent-card-sb" style="margin-top:10px">
    <div class="avatar-sm">HA</div>
    <div style="flex:1;min-width:0">
      <div class="agent-name-sb">Hamza Agent</div>
      <div class="agent-role-sb">Agent</div>
    </div>
    <div class="status-dot"></div>
  </div>

  <nav class="nav">
    <div class="nav-section">Main</div>
    <a href="index.php" class="nav-item">
      <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="reports.php" class="nav-item">
      <span class="nav-icon">📋</span> All Reports
      <span class="nav-badge" id="nav-reports-badge">9</span>
    </a>
    <a href="citizenrequests.php" class="nav-item active">
      <span class="nav-icon">🙏</span> Citizen Requests
      <span class="nav-badge green-badge" id="nav-new-badge">3</span>
    </a>
    <div class="nav-section">Account</div>
    <a href="profile.php" class="nav-item">
      <span class="nav-icon">👤</span> My Profile
    </a>
  </nav>

  <div class="sb-footer">
    <button class="logout-btn" onclick="confirmLogout()">🚪 Sign Out</button>
  </div>
</aside>

<!-- ─── MAIN ─── -->
<div class="main">
  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-title">
      Citizen Requests
      <span class="topbar-subtitle" id="topbar-sub">14 requests</span>
    </div>
    <div class="search-box">
      <span>🔍</span>
      <input type="text" id="search-input" placeholder="Search requests…"/>
    </div>
    <button class="top-btn" onclick="openModal()">+ New Request</button>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card s-blue">
        <div class="stat-label">Total Requests</div>
        <div class="stat-value" id="stat-total">0</div>
        <div class="stat-sub">all requests</div>
      </div>
      <div class="stat-card s-yellow">
        <div class="stat-label">Pending</div>
        <div class="stat-value" id="stat-pending">0</div>
        <div class="stat-sub">awaiting response</div>
      </div>
      <div class="stat-card s-green">
        <div class="stat-label">Processed</div>
        <div class="stat-value" id="stat-processed">0</div>
        <div class="stat-sub">handled</div>
      </div>
      <div class="stat-card s-red">
        <div class="stat-label">Rejected</div>
        <div class="stat-value" id="stat-rejected">0</div>
        <div class="stat-sub">not eligible</div>
      </div>
    </div>

    <!-- BODY GRID -->
    <div class="grid2">

      <!-- TABLE PANEL -->
      <div class="panel">
        <div class="panel-head">
          <div class="panel-title">Requests list</div>
          <span style="font-size:12px;color:var(--muted)" id="full-count">— requests</span>
        </div>

        <div class="filter-tabs">
          <div class="ftab active" onclick="setFilter(this,'all')">All</div>
          <div class="ftab" onclick="setFilter(this,'New')">New</div>
          <div class="ftab" onclick="setFilter(this,'Pending')">Pending</div>
          <div class="ftab" onclick="setFilter(this,'In Progress')">In Progress</div>
          <div class="ftab" onclick="setFilter(this,'Processed')">Processed</div>
          <div class="ftab" onclick="setFilter(this,'Rejected')">Rejected</div>
          <button class="sort-btn-top" id="sort-btn" onclick="toggleSort()">Date ↓</button>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#ID</th>
                <th>Service</th>
                <th>Citizen</th>
                <th>Category</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody id="req-tbody"></tbody>
          </table>
          <div class="empty" id="empty-state" style="display:none">
            <div class="ei">📭</div><p>No requests match your filter</p>
          </div>
        </div>

        <div class="pagination">
          <span id="footer-count" style="font-size:12px;color:var(--muted)">— requests</span>
          <div class="page-btns" id="page-btns"></div>
        </div>
      </div>

      <!-- RIGHT COLUMN -->
      <div class="right-col">

        <!-- DETAIL PANEL -->
        <div class="detail-panel" id="detail-panel">
          <div class="detail-panel-head">
            <div class="dp-id-row">
              <span class="dp-id" id="dp-id">Select a request</span>
            </div>
            <div class="dp-title" id="dp-title" style="font-size:14px;font-weight:700;color:var(--muted)">Click on a row to view details</div>
          </div>
          <div class="detail-panel-body" id="dp-body">
            <div class="empty">
              <div class="ei">📄</div>
              <p>Select a request to view details</p>
            </div>
          </div>
        </div>

        <!-- CATEGORIES -->
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">By Service Category</div>
          </div>
          <div class="chart-area" id="cat-chart"></div>
        </div>

        <!-- WORKLOAD -->
        <div class="panel">
          <div class="panel-head">
            <div class="panel-title">Agent Workload</div>
          </div>
          <div id="workload-list"></div>
        </div>

      </div><!-- /right-col -->
    </div><!-- /grid2 -->
  </div><!-- /content -->
</div><!-- /main -->

<!-- ─── MODAL ─── -->
<div class="modal-overlay" id="modal-overlay" onclick="closeModalOutside(event)">
  <div class="modal">
    <div class="modal-head">
      <div class="modal-title">New Citizen Request</div>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Citizen Name</label>
          <input class="form-control" id="f-name" type="text" placeholder="Full name"/>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-control" id="f-email" type="email" placeholder="email@example.com"/>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Service</label>
          <input class="form-control" id="f-service" type="text" placeholder="Service requested"/>
        </div>
        <div class="form-group">
          <label class="form-label">Category</label>
          <select class="form-control" id="f-category">
            <option value="Civil Status">Civil Status</option>
            <option value="Construction">Construction</option>
            <option value="Residency">Residency</option>
            <option value="Legal">Legal</option>
            <option value="Other">Other</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Notes</label>
        <textarea class="form-control" id="f-notes" placeholder="Additional information…"></textarea>
      </div>
      <div class="modal-actions">
        <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button class="btn btn-primary" onclick="submitRequest()">+ Create Request</button>
      </div>
    </div>
  </div>
</div>

<!-- ─── TOAST ─── -->
<div class="toast" id="toast">
  <span id="toast-icon">✅</span>
  <span id="toast-msg"></span>
</div>

<script>
// ─── DATA ─────────────────────────────────────────────────
const AVATAR_COLORS = [
  '#14b8a6','#3b82f6','#f59e0b','#ec4899','#8b5cf6','#ef4444'
];

let requests = [
  {id:'R14',service:'Demande de casier judiciaire',citizen:'Ali Mansour',email:'ali.mansour@email.com',status:'New',category:'Legal',date:'2026-04-14',docs:['CIN.pdf','formulaire.pdf'],notes:'',color:'#3b82f6'},
  {id:'R13',service:'Extrait de naissance',citizen:'Sara Farhat',email:'sara.farhat@email.com',status:'In Progress',category:'Civil Status',date:'2026-04-13',docs:['acte_naissance.pdf'],notes:'En cours de vérification',color:'#ec4899'},
  {id:'R12',service:'Permis de construire',citizen:'Karim Bel Haj',email:'k.belhaj@email.com',status:'Processed',category:'Construction',date:'2026-04-11',docs:['plan.pdf','terrain.pdf'],notes:'Approuvé',color:'#f59e0b'},
  {id:'R11',service:'Carte de résidence',citizen:'Nour Ben Ali',email:'nour.ba@email.com',status:'Pending',category:'Residency',date:'2026-04-10',docs:['passeport.pdf'],notes:'',color:'#14b8a6'},
  {id:'R10',service:'Attestation de résidence',citizen:'Mohamed Hajri',email:'m.hajri@email.com',status:'Processed',category:'Residency',date:'2026-04-09',docs:['CIN.pdf'],notes:'Délivré',color:'#8b5cf6'},
  {id:'R9',service:'Extrait de mariage',citizen:'Fatma Cherif',email:'fatma.c@email.com',status:'Pending',category:'Civil Status',date:'2026-04-08',docs:[],notes:'',color:'#ec4899'},
  {id:'R8',service:'Rénovation façade',citizen:'Anouar Slim',email:'anouar.slim@email.com',status:'Rejected',category:'Construction',date:'2026-04-07',docs:['devis.pdf'],notes:'Documents incomplets',color:'#3b82f6'},
  {id:'R7',service:'Changement adresse',citizen:'Leila Rezgui',email:'l.rezgui@email.com',status:'New',category:'Residency',date:'2026-04-07',docs:['CIN.pdf'],notes:'',color:'#f59e0b'},
  {id:'R6',service:'Acte de décès',citizen:'Hassan Tlili',email:'h.tlili@email.com',status:'New',category:'Civil Status',date:'2026-04-06',docs:['formulaire.pdf'],notes:'',color:'#ef4444'},
  {id:'R5',service:'Permis de démolition',citizen:'Sami Nasri',email:'s.nasri@email.com',status:'In Progress',category:'Construction',date:'2026-04-05',docs:['plan.pdf'],notes:'En cours',color:'#14b8a6'},
  {id:'R4',service:'Acte de naissance duplicata',citizen:'Rim Bouaziz',email:'rim.bz@email.com',status:'Processed',category:'Civil Status',date:'2026-04-04',docs:['CIN.pdf'],notes:'',color:'#ec4899'},
  {id:'R3',service:'Attestation fiscale',citizen:'Bilel Krichen',email:'b.krichen@email.com',status:'Rejected',category:'Legal',date:'2026-04-03',docs:[],notes:'Dossier incomplet',color:'#3b82f6'},
  {id:'R2',service:'Autorisation commerce',citizen:'Wafa Mansouri',email:'wafa.m@email.com',status:'Pending',category:'Legal',date:'2026-04-02',docs:['CIN.pdf','extrait.pdf'],notes:'',color:'#8b5cf6'},
  {id:'R1',service:'Extrait de naissance copie',citizen:'Tarek Gafsi',email:'t.gafsi@email.com',status:'Processed',category:'Civil Status',date:'2026-04-01',docs:['CIN.pdf'],notes:'',color:'#f59e0b'},
];

const AGENTS = [
  {name:'Hassayoun Aya',open:2,total:4,status:'active',color:'#3b82f6'},
  {name:'Aziz Jaziri',open:1,total:2,status:'active',color:'#f59e0b'},
  {name:'Kacem Hanin',open:1,total:2,status:'active',color:'#8b5cf6'},
  {name:'Hamza Agent',open:0,total:0,status:'free',color:'#14b8a6'},
];

const STATUS_MAP = {
  'New':        'st-new',
  'Pending':    'st-pending',
  'In Progress':'st-progress',
  'Processed':  'st-processed',
  'Rejected':   'st-rejected',
};
const STATUS_ICON = {
  'New':'🆕','Pending':'⏳','In Progress':'🔄','Processed':'✅','Rejected':'❌'
};

let currentFilter = 'all';
let currentSort   = 'desc';
let currentPage   = 1;
let selectedId    = null;
let searchQuery   = '';
let toastTimer;
let nextId        = 15;
const PER_PAGE    = 8;

// ─── INIT ──────────────────────────────────────────────────
function init() {
  renderTable();
  renderStats();
  renderCategories();
  renderWorkload();
  document.getElementById('search-input').addEventListener('input', e => {
    searchQuery = e.target.value.toLowerCase();
    currentPage = 1;
    renderTable();
  });
}

// ─── STATS ─────────────────────────────────────────────────
function renderStats() {
  const s = {New:0,Pending:0,'In Progress':0,Processed:0,Rejected:0};
  requests.forEach(r => s[r.status] = (s[r.status]||0)+1);
  document.getElementById('stat-total').textContent    = requests.length;
  document.getElementById('stat-pending').textContent  = (s.New||0)+(s.Pending||0);
  document.getElementById('stat-processed').textContent= s.Processed||0;
  document.getElementById('stat-rejected').textContent = s.Rejected||0;
  document.getElementById('topbar-sub').textContent    = requests.length + ' requests';
  const newCount = s.New||0;
  const badge = document.getElementById('nav-new-badge');
  if (badge) { badge.textContent = newCount; badge.style.display = newCount > 0 ? '' : 'none'; }
}

// ─── TABLE ─────────────────────────────────────────────────
function getFiltered() {
  let data = [...requests];
  if (currentFilter !== 'all') data = data.filter(r => r.status === currentFilter);
  if (searchQuery) data = data.filter(r =>
    r.id.toLowerCase().includes(searchQuery) ||
    r.service.toLowerCase().includes(searchQuery) ||
    r.citizen.toLowerCase().includes(searchQuery) ||
    r.status.toLowerCase().includes(searchQuery) ||
    r.category.toLowerCase().includes(searchQuery)
  );
  data.sort((a,b) => currentSort === 'desc'
    ? b.date.localeCompare(a.date)
    : a.date.localeCompare(b.date)
  );
  return data;
}

function renderTable() {
  const data   = getFiltered();
  const total  = data.length;
  const pages  = Math.max(1, Math.ceil(total / PER_PAGE));
  if (currentPage > pages) currentPage = pages;
  const slice  = data.slice((currentPage-1)*PER_PAGE, currentPage*PER_PAGE);

  const tbody = document.getElementById('req-tbody');
  const empty = document.getElementById('empty-state');
  const countEl = document.getElementById('full-count');
  if (countEl) countEl.textContent = `${total} request${total!==1?'s':''}`;
  document.getElementById('footer-count').textContent = `${total} request${total!==1?'s':''}`;

  if (slice.length === 0) {
    tbody.innerHTML = '';
    empty.style.display = '';
  } else {
    empty.style.display = 'none';
    tbody.innerHTML = slice.map(r => `
      <tr onclick="selectRow('${r.id}')" class="${selectedId===r.id?'row-selected':''}">
        <td><span class="c-id">#${r.id}</span></td>
        <td>
          <div class="c-title">${esc(r.service)}</div>
          <div class="c-sub">${esc(r.category)}</div>
        </td>
        <td>
          <div class="citizen-cell">
            <div class="cit-av" style="background:${r.color}">${initials(r.citizen)}</div>
            <div>
              <div style="font-size:13px;font-weight:500">${esc(r.citizen)}</div>
              <div class="c-sub">${esc(r.email)}</div>
            </div>
          </div>
        </td>
        <td style="font-size:12px;color:var(--muted)">${esc(r.category)}</td>
        <td>${statusBadge(r.status)}</td>
        <td style="font-size:12px;color:var(--muted)">${formatDate(r.date)}</td>
        <td onclick="event.stopPropagation()">
          <div class="act-btns">
            <button class="act-btn btn-accept" onclick="acceptRequest('${r.id}')">✅ Accept</button>
            <button class="act-btn btn-reject" onclick="rejectRequest('${r.id}')">❌ Reject</button>
            <button class="act-btn" onclick="selectRow('${r.id}')">👁 View</button>
          </div>
        </td>
      </tr>
    `).join('');
  }
  renderPagination(pages);
}

function renderPagination(pages) {
  const cont = document.getElementById('page-btns');
  if (pages <= 1) { cont.innerHTML = ''; return; }
  let html = `<button class="page-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;
  for (let i=1;i<=pages;i++) {
    html += `<button class="page-btn ${i===currentPage?'active-page':''}" onclick="goPage(${i})">${i}</button>`;
  }
  html += `<button class="page-btn" onclick="goPage(${currentPage+1})" ${currentPage===pages?'disabled':''}>›</button>`;
  cont.innerHTML = html;
}

function goPage(p) {
  const pages = Math.max(1, Math.ceil(getFiltered().length / PER_PAGE));
  if (p<1||p>pages) return;
  currentPage = p;
  renderTable();
}

function setFilter(el, f) {
  document.querySelectorAll('.ftab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  currentFilter = f;
  currentPage   = 1;
  renderTable();
}

function toggleSort() {
  currentSort = currentSort === 'desc' ? 'asc' : 'desc';
  document.getElementById('sort-btn').textContent = currentSort === 'desc' ? 'Date ↓' : 'Date ↑';
  renderTable();
}

// ─── ROW SELECT ────────────────────────────────────────────
function selectRow(id) {
  selectedId = selectedId === id ? null : id;
  renderTable();
  if (selectedId) renderDetail(selectedId);
  else resetDetail();
}

// ─── DETAIL ────────────────────────────────────────────────
function renderDetail(id) {
  const r = requests.find(x => x.id === id);
  if (!r) return;

  document.getElementById('dp-id').innerHTML =
    `<strong>#${r.id}</strong> &nbsp; ${statusBadge(r.status)}`;
  document.getElementById('dp-title').textContent = r.service;
  document.getElementById('dp-title').style.color = 'var(--text)';

  const docPills = r.docs.length
    ? r.docs.map(d => `<div class="doc-pill">📎 ${esc(d)}</div>`).join('')
    : `<span style="font-size:11px;color:var(--muted2)">No documents attached</span>`;

  document.getElementById('dp-body').innerHTML = `
    <div class="info-field">
      <div class="field-lbl">Citizen</div>
      <div class="citizen-info-row">
        <div class="cit-av-md" style="background:${r.color}">${initials(r.citizen)}</div>
        <div>
          <div class="ci-name">${esc(r.citizen)}</div>
          <div class="ci-email">${esc(r.email)}</div>
        </div>
      </div>
    </div>
    <div class="info-field">
      <div class="field-lbl">Service</div>
      <div class="field-val">${esc(r.service)}</div>
    </div>
    <div class="info-field">
      <div class="field-lbl">Category</div>
      <div class="field-val">${esc(r.category)}</div>
    </div>
    <div class="info-field">
      <div class="field-lbl">Submitted</div>
      <div class="field-val" style="font-size:12px;color:var(--muted)">${formatDate(r.date)}</div>
    </div>
    <div class="info-field">
      <div class="field-lbl">Documents</div>
      <div class="docs-row">${docPills}</div>
    </div>
    <div class="info-field">
      <div class="field-lbl">Agent Note</div>
      <textarea class="note-area" id="note-${r.id}" placeholder="Add a note…">${esc(r.notes)}</textarea>
    </div>
    <div class="detail-actions">
      <button class="detail-act-btn accept" onclick="acceptRequest('${r.id}')">✅ Accept</button>
      <button class="detail-act-btn" onclick="showToast('Assigned to team','info')">👤 Assign</button>
      <button class="detail-act-btn reject"  onclick="rejectRequest('${r.id}')">❌ Reject</button>
    </div>
  `;

  const noteTA = document.getElementById(`note-${r.id}`);
  if (noteTA) noteTA.addEventListener('change', e => {
    const req = requests.find(x => x.id === id);
    if (req) req.notes = e.target.value;
  });
}

function resetDetail() {
  document.getElementById('dp-id').textContent    = 'Select a request';
  document.getElementById('dp-title').textContent = 'Click on a row to view details';
  document.getElementById('dp-title').style.color = 'var(--muted)';
  document.getElementById('dp-body').innerHTML    = `<div class="empty"><div class="ei">📄</div><p>Select a request to view details</p></div>`;
}

// ─── CATEGORIES ────────────────────────────────────────────
function renderCategories() {
  const cats = {};
  requests.forEach(r => cats[r.category] = (cats[r.category]||0)+1);
  const max  = Math.max(...Object.values(cats));
  const clrs = ['var(--blue)','var(--yellow)','var(--green)','var(--purple)','var(--muted2)'];
  const el   = document.getElementById('cat-chart');
  el.innerHTML = Object.entries(cats)
    .sort((a,b) => b[1]-a[1])
    .map(([name,count],i) => `
      <div class="bar-row">
        <div class="bar-label" title="${esc(name)}">${esc(name)}</div>
        <div class="bar-track"><div class="bar-fill" style="width:${Math.round(count/max*100)}%;background:${clrs[i%clrs.length]}"></div></div>
        <div class="bar-count">${count}</div>
      </div>
    `).join('');
}

// ─── WORKLOAD ──────────────────────────────────────────────
function renderWorkload() {
  document.getElementById('workload-list').innerHTML = AGENTS.map(a => `
    <div class="workload-item">
      <div class="avatar-sm" style="background:${a.color}">${initials(a.name)}</div>
      <div style="flex:1;min-width:0">
        <div class="wl-name">${esc(a.name)}</div>
        <div class="wl-meta">${a.open} open · ${a.total} total</div>
      </div>
      <span class="wl-status ${a.status==='active'?'wls-active':'wls-free'}">${a.status}</span>
    </div>
  `).join('');
}

// ─── ACTIONS ───────────────────────────────────────────────
function acceptRequest(id) {
  const r = requests.find(x => x.id === id);
  if (!r) return;
  if (r.status === 'Processed') { showToast('Already processed','info'); return; }
  r.status = 'Processed';
  if (selectedId === id) renderDetail(id);
  renderTable(); renderStats(); renderCategories();
  showToast(`Request #${id} accepted ✅`,'success');
}

function rejectRequest(id) {
  const r = requests.find(x => x.id === id);
  if (!r) return;
  if (r.status === 'Rejected') { showToast('Already rejected','info'); return; }
  r.status = 'Rejected';
  if (selectedId === id) renderDetail(id);
  renderTable(); renderStats(); renderCategories();
  showToast(`Request #${id} rejected ❌`,'error');
}

// ─── MODAL ─────────────────────────────────────────────────
function openModal()  { document.getElementById('modal-overlay').classList.add('open'); }
function closeModal() { document.getElementById('modal-overlay').classList.remove('open'); }
function closeModalOutside(e) {
  if (e.target === document.getElementById('modal-overlay')) closeModal();
}

function submitRequest() {
  const name     = document.getElementById('f-name').value.trim();
  const email    = document.getElementById('f-email').value.trim();
  const service  = document.getElementById('f-service').value.trim();
  const category = document.getElementById('f-category').value;
  const notes    = document.getElementById('f-notes').value.trim();

  if (!name || !service) { showToast('Please fill required fields','error'); return; }

  const color = AVATAR_COLORS[Math.floor(Math.random()*AVATAR_COLORS.length)];
  const newReq = {
    id: `R${nextId++}`, service, citizen: name,
    email: email||'—', status:'New', category,
    date: new Date().toISOString().slice(0,10),
    docs: [], notes, color
  };

  requests.unshift(newReq);
  closeModal();
  ['f-name','f-email','f-service','f-notes'].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
  currentPage = 1;
  renderTable(); renderStats(); renderCategories();
  showToast(`Request #${newReq.id} created ✅`,'success');
}

// ─── TOAST ─────────────────────────────────────────────────
function showToast(msg, type='success') {
  const t   = document.getElementById('toast');
  const ico = document.getElementById('toast-icon');
  const m   = document.getElementById('toast-msg');
  if (!t) return;
  m.textContent   = msg;
  ico.textContent = type==='success' ? '✅' : type==='error' ? '❌' : 'ℹ️';
  t.className = 'toast show ' + type;
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
}

function confirmLogout() {
  if (confirm('Are you sure you want to sign out?')) {
    window.location.href = '../auth/logout.php';
  }
}

// ─── HELPERS ───────────────────────────────────────────────
function esc(str) {
  if (str == null) return '';
  return String(str)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function statusBadge(s) {
  const cls = STATUS_MAP[s] || 'st-new';
  return `<span class="st-badge ${cls}"><span class="dot"></span>${esc(s)}</span>`;
}

function initials(name) {
  return (name||'?').split(' ').slice(0,2).map(w=>(w[0]||'').toUpperCase()).join('')||'?';
}

function formatDate(d) {
  if (!d) return '—';
  const [y,m,day] = d.split('-');
  return `${day}/${m}/${y}`;
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModal();
});

init();
</script>
</body>
</html>