<?php
// admin/index.php  — Admin Dashboard
require_once 'auth_check.php';

// ── Stats ──
$stats = $pdo->query("
    SELECT
        COUNT(*)                                          AS total,
        SUM(status = 'Open')                              AS open,
        SUM(status = 'In progress')                       AS in_progress,
        SUM(status = 'Resolved')                          AS resolved,
        SUM(status = 'Overdue' OR (deadline < NOW() AND status NOT IN ('Resolved','Transferred'))) AS overdue,
        SUM(status = 'Transferred')                       AS transferred
    FROM reports
")->fetch(PDO::FETCH_ASSOC);

// ── Overdue reports (not yet marked but deadline passed) ──
$overdueQ = $pdo->query("
    SELECT r.id, r.description, r.status, r.deadline, r.created_at,
           u.name AS citizen, ag.name AS agent_name,
           c.name AS category, s.name AS service
    FROM reports r
    LEFT JOIN users u  ON u.id = r.user_id
    LEFT JOIN users ag ON ag.id = r.assigned_to
    LEFT JOIN categories c ON c.id = r.category_id
    LEFT JOIN services s   ON s.id = c.service_id
    WHERE r.deadline < NOW() AND r.status NOT IN ('Resolved','Transferred')
    ORDER BY r.deadline ASC
    LIMIT 10
");
$overdueReports = $overdueQ->fetchAll(PDO::FETCH_ASSOC);

// Auto-mark overdue
if (!empty($overdueReports)) {
    $pdo->exec("
        UPDATE reports
        SET status = 'Overdue'
        WHERE deadline < NOW() AND status NOT IN ('Resolved','Transferred','Overdue')
    ");
}

// ── Recent reports ──
$recentQ = $pdo->query("
    SELECT r.id, r.description, r.status, r.created_at, r.image,
           u.name AS citizen,
           ag.name AS agent_name,
           c.name AS category,
           s.name AS service
    FROM reports r
    LEFT JOIN users u  ON u.id = r.user_id
    LEFT JOIN users ag ON ag.id = r.assigned_to
    LEFT JOIN categories c ON c.id = r.category_id
    LEFT JOIN services s   ON s.id = c.service_id
    ORDER BY r.created_at DESC
    LIMIT 8
");
$recentReports = $recentQ->fetchAll(PDO::FETCH_ASSOC);

// ── Agent workload ──
$agentLoad = $pdo->query("
    SELECT u.id, u.name, s.name AS service,
           COUNT(r.id) AS total,
           SUM(r.status = 'Open' OR r.status = 'In progress') AS active,
           SUM(r.status = 'Resolved') AS resolved
    FROM users u
    LEFT JOIN reports r ON r.assigned_to = u.id
    LEFT JOIN services s ON s.id = u.service_id
    WHERE u.role = 'agent'
    GROUP BY u.id
    ORDER BY active DESC
    LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Admin Dashboard — SalahHomty</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
<style>
:root{
  --ink:#0d1117;--ink2:#1c2230;--ink3:#2d3748;
  --teal:#1aaa96;--teal-d:#138a79;--teal-l:#e8f9f7;
  --red:#ef4444;--red-l:#fee2e2;
  --amber:#f59e0b;--amber-l:#fffbeb;
  --green:#22c55e;--green-l:#f0fdf4;
  --blue:#3b82f6;--blue-l:#eff6ff;
  --purple:#8b5cf6;--purple-l:#f5f3ff;
  --border:#e5e7eb;--muted:#6b7280;
  --white:#fff;--cream:#f8fafc;
  --sidebar:240px;
  --font-h:'Syne',sans-serif;--font-b:'DM Sans',sans-serif;
  --r:8px;--t:.18s ease;
  --shadow:0 1px 3px rgba(0,0,0,.08),0 1px 2px rgba(0,0,0,.05);
  --shadow-md:0 4px 16px rgba(0,0,0,.08);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--font-b);background:var(--cream);color:var(--ink);display:flex;min-height:100vh}
a{color:inherit;text-decoration:none}
button{font-family:var(--font-b);cursor:pointer}

/* ── SIDEBAR ── */
.sidebar{
  width:var(--sidebar);background:var(--ink);
  display:flex;flex-direction:column;
  position:fixed;top:0;left:0;bottom:0;z-index:50;
  overflow-y:auto;
}
.sidebar__brand{
  display:flex;align-items:center;gap:10px;
  padding:20px 18px 16px;
  border-bottom:1px solid rgba(255,255,255,.07);
}
.sidebar__logo{
  width:36px;height:36px;background:var(--teal);
  border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.sidebar__logo svg{width:20px;height:20px}
.sidebar__name{font-family:var(--font-h);font-size:17px;font-weight:800;color:#fff;letter-spacing:-.3px}
.sidebar__name span{color:var(--teal)}

.sidebar__section{padding:10px 12px 4px;font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.3)}

.sidebar__nav{display:flex;flex-direction:column;gap:2px;padding:0 8px}
.sidebar__link{
  display:flex;align-items:center;gap:10px;
  padding:9px 10px;border-radius:6px;
  font-size:13.5px;font-weight:500;color:rgba(255,255,255,.6);
  transition:background var(--t),color var(--t);
}
.sidebar__link svg{width:16px;height:16px;flex-shrink:0}
.sidebar__link:hover{background:rgba(255,255,255,.07);color:#fff}
.sidebar__link.active{background:var(--teal);color:#fff}
.sidebar__link .badge{
  margin-left:auto;background:var(--red);color:#fff;
  font-size:10px;font-weight:700;padding:1px 6px;border-radius:100px;
}

.sidebar__bottom{margin-top:auto;padding:12px 8px;border-top:1px solid rgba(255,255,255,.07)}
.sidebar__user{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:6px}
.sidebar__avatar{
  width:32px;height:32px;border-radius:50%;
  background:var(--teal);color:#fff;
  font-size:13px;font-weight:700;font-family:var(--font-h);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.sidebar__user-info{display:flex;flex-direction:column;min-width:0}
.sidebar__user-name{font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sidebar__user-role{font-size:11px;color:rgba(255,255,255,.4);text-transform:capitalize}
.sidebar__logout{
  margin-top:6px;width:100%;padding:8px;border:none;
  background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);
  border-radius:6px;font-size:12px;font-weight:600;
  transition:all var(--t);
}
.sidebar__logout:hover{background:var(--red);color:#fff}

/* ── MAIN ── */
.main{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column;min-height:100vh}

/* ── TOPBAR ── */
.topbar{
  background:var(--white);border-bottom:1px solid var(--border);
  padding:0 28px;height:60px;
  display:flex;align-items:center;justify-content:space-between;
  gap:16px;position:sticky;top:0;z-index:40;
}
.topbar__title{font-family:var(--font-h);font-size:18px;font-weight:800;color:var(--ink)}
.topbar__right{display:flex;align-items:center;gap:10px}
.topbar__badge{
  display:flex;align-items:center;gap:6px;
  background:var(--red-l);color:var(--red);
  padding:5px 12px;border-radius:100px;
  font-size:12px;font-weight:700;
}
.topbar__badge svg{width:13px;height:13px}

/* ── PAGE ── */
.page{padding:24px 28px 60px;flex:1}

/* ── STAT CARDS ── */
.stats-grid{
  display:grid;grid-template-columns:repeat(5,1fr);
  gap:14px;margin-bottom:24px;
}
.stat-card{
  background:var(--white);border-radius:var(--r);
  padding:18px 16px;border-top:3px solid transparent;
  box-shadow:var(--shadow);
}
.stat-card--total   {border-color:var(--ink3)}
.stat-card--open    {border-color:var(--blue)}
.stat-card--prog    {border-color:var(--amber)}
.stat-card--done    {border-color:var(--green)}
.stat-card--overdue {border-color:var(--red)}
.stat-card__label{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:8px}
.stat-card__value{font-family:var(--font-h);font-size:34px;font-weight:800;line-height:1;color:var(--ink)}
.stat-card__sub{font-size:12px;color:var(--muted);margin-top:4px}

/* ── SECTION TITLE ── */
.section-title{
  font-family:var(--font-h);font-size:15px;font-weight:800;
  color:var(--ink);margin-bottom:14px;
  display:flex;align-items:center;gap:10px;
}
.section-title a{margin-left:auto;font-family:var(--font-b);font-size:12px;font-weight:600;color:var(--teal)}

/* ── GRID ── */
.grid-2{display:grid;grid-template-columns:1.6fr 1fr;gap:18px;margin-bottom:24px}

/* ── ALERT BOX ── */
.alert-overdue{
  background:var(--red-l);border:1px solid #fca5a5;
  border-radius:var(--r);padding:14px 18px;
  display:flex;align-items:flex-start;gap:12px;
  font-size:13.5px;color:#991b1b;margin-bottom:20px;
}
.alert-overdue svg{flex-shrink:0;width:18px;height:18px;color:var(--red);margin-top:1px}
.alert-overdue strong{display:block;margin-bottom:2px;font-weight:700}

/* ── TABLE CARD ── */
.table-card{background:var(--white);border-radius:var(--r);box-shadow:var(--shadow-md);overflow:hidden}
table{width:100%;border-collapse:collapse}
thead{background:var(--cream);border-bottom:2px solid var(--border)}
th{
  text-align:left;padding:11px 16px;
  font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--muted);
}
td{padding:13px 16px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:#fafafa}

.td-id{font-family:var(--font-h);font-size:12px;font-weight:700;color:var(--teal)}
.td-desc{max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-weight:500}
.td-muted{color:var(--muted);font-size:12.5px}

/* ── AGENT CARD ── */
.agent-list{display:flex;flex-direction:column;gap:0}
.agent-item{
  display:flex;align-items:center;gap:12px;
  padding:13px 16px;border-bottom:1px solid var(--border);
}
.agent-item:last-child{border-bottom:none}
.agent-avatar{
  width:36px;height:36px;border-radius:50%;
  background:linear-gradient(135deg,var(--teal),var(--teal-d));
  color:#fff;font-family:var(--font-h);font-size:14px;font-weight:800;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.agent-info{flex:1;min-width:0}
.agent-name{font-size:13.5px;font-weight:600;color:var(--ink)}
.agent-service{font-size:11.5px;color:var(--muted)}
.agent-bar-wrap{display:flex;align-items:center;gap:8px;margin-top:4px}
.agent-bar{flex:1;height:4px;background:var(--border);border-radius:100px;overflow:hidden}
.agent-bar-fill{height:100%;background:var(--teal);border-radius:100px;transition:width .4s ease}
.agent-count{font-size:11px;font-weight:700;color:var(--muted);white-space:nowrap}

/* ── ACTION BUTTONS ── */
.btn{
  display:inline-flex;align-items:center;gap:6px;
  padding:6px 12px;border-radius:6px;font-size:12.5px;font-weight:600;
  border:1.5px solid var(--border);background:var(--white);color:var(--ink);
  transition:all var(--t);cursor:pointer;
}
.btn:hover{border-color:var(--teal);color:var(--teal)}
.btn-danger{border-color:#fca5a5;color:var(--red)}
.btn-danger:hover{background:var(--red);color:#fff;border-color:var(--red)}
.btn-primary{background:var(--teal);color:#fff;border-color:var(--teal)}
.btn-primary:hover{background:var(--teal-d);border-color:var(--teal-d)}

@media(max-width:900px){
  .sidebar{display:none}
  .main{margin-left:0}
  .stats-grid{grid-template-columns:repeat(3,1fr)}
  .grid-2{grid-template-columns:1fr}
}
</style>
</head>
<body>

<!-- ══ SIDEBAR ══ -->
<aside class="sidebar">
  <div class="sidebar__brand">
    <div class="sidebar__logo">
      <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
      </svg>
    </div>
    <div class="sidebar__name">Salah<span>Homty</span></div>
  </div>

  <div style="height:12px"></div>
  <div class="sidebar__section">Main</div>
  <nav class="sidebar__nav">
    <a href="index.php" class="sidebar__link active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="reports.php" class="sidebar__link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      All Reports
      <?php if ($stats['open'] > 0): ?><span class="badge"><?= $stats['open'] ?></span><?php endif ?>
    </a>
    <a href="reports.php?filter=overdue" class="sidebar__link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Overdue
      <?php if ($stats['overdue'] > 0): ?><span class="badge"><?= $stats['overdue'] ?></span><?php endif ?>
    </a>
    <a href="agents.php" class="sidebar__link">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
    <circle cx="9" cy="7" r="4"/>
    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
  </svg>
  Agents
</a>

<a href="services.php" class="sidebar__link">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="3"/>
    <path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>
    <path d="M4.93 4.93a10 10 0 0 0 0 14.14"/>
  </svg>
  Services
</a>
  </nav>

  <?php if (isAdmin()): ?>
  <div style="height:8px"></div>
  <div class="sidebar__section">Management</div>
  <nav class="sidebar__nav">
    <a href="agents.php" class="sidebar__link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      Agents
    </a>
    <a href="services.php" class="sidebar__link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="1"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
      Services
    </a>
  </nav>
  <?php endif ?>

  <div class="sidebar__bottom">
    <div class="sidebar__user">
      <div class="sidebar__avatar"><?= strtoupper(substr($currentUser['name'],0,1)) ?></div>
      <div class="sidebar__user-info">
        <span class="sidebar__user-name"><?= htmlspecialchars($currentUser['name']) ?></span>
        <span class="sidebar__user-role"><?= $currentUser['role'] ?></span>
      </div>
    </div>
    <a href="../auth/logout.php"><button class="sidebar__logout">← Sign out</button></a>
  </div>
</aside>

<!-- ══ MAIN ══ -->
<div class="main">

  <!-- Topbar -->
  <div class="topbar">
    <span class="topbar__title">Dashboard</span>
    <div class="topbar__right">
      <?php if ($stats['overdue'] > 0): ?>
      <span class="topbar__badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= $stats['overdue'] ?> overdue
      </span>
      <?php endif ?>
      <a href="reports.php" class="btn btn-primary">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        View all reports
      </a>
    </div>
  </div>

  <div class="page">

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card stat-card--total">
        <div class="stat-card__label">Total</div>
        <div class="stat-card__value"><?= $stats['total'] ?></div>
        <div class="stat-card__sub">all reports</div>
      </div>
      <div class="stat-card stat-card--open">
        <div class="stat-card__label">Open</div>
        <div class="stat-card__value"><?= $stats['open'] ?></div>
        <div class="stat-card__sub">pending</div>
      </div>
      <div class="stat-card stat-card--prog">
        <div class="stat-card__label">In progress</div>
        <div class="stat-card__value"><?= $stats['in_progress'] ?></div>
        <div class="stat-card__sub">being handled</div>
      </div>
      <div class="stat-card stat-card--done">
        <div class="stat-card__label">Resolved</div>
        <div class="stat-card__value"><?= $stats['resolved'] ?></div>
        <div class="stat-card__sub">closed</div>
      </div>
      <div class="stat-card stat-card--overdue">
        <div class="stat-card__label">Overdue</div>
        <div class="stat-card__value"><?= $stats['overdue'] ?></div>
        <div class="stat-card__sub">deadline exceeded</div>
      </div>
    </div>

    <!-- Overdue alert -->
    <?php if (!empty($overdueReports)): ?>
    <div class="alert-overdue">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <div>
        <strong><?= count($overdueReports) ?> report<?= count($overdueReports) > 1 ? 's have' : ' has' ?> exceeded the processing deadline.</strong>
        Transfer them to another agent using the <a href="reports.php?filter=overdue" style="color:var(--red);font-weight:700;text-decoration:underline">overdue reports</a> page.
      </div>
    </div>
    <?php endif ?>

    <!-- Grid: Recent + Agent workload -->
    <div class="grid-2">

      <!-- Recent reports -->
      <div>
        <div class="section-title">Recent reports <a href="reports.php">View all →</a></div>
        <div class="table-card">
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
            <tbody>
              <?php foreach ($recentReports as $r): ?>
              <tr>
                <td><span class="td-id">#<?= $r['id'] ?></span></td>
                <td><span class="td-desc" title="<?= htmlspecialchars($r['description'] ?? '') ?>"><?= htmlspecialchars(mb_strimwidth($r['description'] ?? 'No description', 0, 36, '…')) ?></span></td>
                <td class="td-muted"><?= htmlspecialchars($r['category'] ?? '—') ?></td>
                <td class="td-muted"><?= htmlspecialchars($r['agent_name'] ?? '—') ?></td>
                <td><?= statusBadge($r['status']) ?></td>
                <td class="td-muted"><?= timeAgo($r['created_at']) ?></td>
              </tr>
              <?php endforeach ?>
              <?php if (empty($recentReports)): ?>
              <tr><td colspan="6" style="text-align:center;padding:32px;color:var(--muted)">No reports yet</td></tr>
              <?php endif ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Agent workload -->
      <div>
        <div class="section-title">Agent workload <a href="agents.php">Manage →</a></div>
        <div class="table-card">
          <div class="agent-list">
            <?php foreach ($agentLoad as $ag):
              $max = max(1, $ag['total']);
              $pct = round(($ag['active'] / $max) * 100);
            ?>
            <div class="agent-item">
              <div class="agent-avatar"><?= strtoupper(substr($ag['name'],0,1)) ?></div>
              <div class="agent-info">
                <div class="agent-name"><?= htmlspecialchars($ag['name']) ?></div>
                <div class="agent-service"><?= htmlspecialchars($ag['service'] ?? 'Unassigned') ?></div>
                <div class="agent-bar-wrap">
                  <div class="agent-bar"><div class="agent-bar-fill" style="width:<?= $pct ?>%"></div></div>
                  <span class="agent-count"><?= $ag['active'] ?> active</span>
                </div>
              </div>
            </div>
            <?php endforeach ?>
            <?php if (empty($agentLoad)): ?>
            <div style="padding:24px;text-align:center;color:var(--muted);font-size:13px">No agents configured yet.<br><a href="agents.php" style="color:var(--teal);font-weight:600">Add agents →</a></div>
            <?php endif ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>