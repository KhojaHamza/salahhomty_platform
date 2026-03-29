<?php
// admin/agents.php
require_once 'auth_check.php';
if (!isAdmin()) { header('Location: index.php'); exit; }

// ── Handle POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'promote') {
        $uid = (int)$_POST['user_id'];
        $sid = (int)$_POST['service_id'];
        $pdo->prepare("UPDATE users SET role='agent', service_id=:s WHERE id=:id")->execute([':s'=>$sid,':id'=>$uid]);
    } elseif ($action === 'demote') {
        $uid = (int)$_POST['user_id'];
        $pdo->prepare("UPDATE users SET role='citizen', service_id=NULL WHERE id=:id")->execute([':id'=>$uid]);
    } elseif ($action === 'update_service') {
        $uid = (int)$_POST['user_id'];
        $sid = (int)$_POST['service_id'];
        $pdo->prepare("UPDATE users SET service_id=:s WHERE id=:id")->execute([':s'=>$sid,':id'=>$uid]);
    }
    header('Location: agents.php');
    exit;
}

// ── Load agents ──
$agents = $pdo->query("
    SELECT u.id, u.name, u.email, u.role, u.service_id,
           s.name AS service,
           COUNT(r.id)                         AS total,
           SUM(r.status IN ('Open','In progress')) AS active,
           SUM(r.status = 'Resolved')          AS resolved,
           SUM(r.status = 'Overdue')           AS overdue
    FROM users u
    LEFT JOIN services s ON s.id = u.service_id
    LEFT JOIN reports r  ON r.assigned_to = u.id
    WHERE u.role IN ('agent','admin')
    GROUP BY u.id
    ORDER BY u.role DESC, u.name
")->fetchAll(PDO::FETCH_ASSOC);

// ── Citizens (for promotion) ──
$citizens = $pdo->query("SELECT id, name, email FROM users WHERE role='citizen' ORDER BY name LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);

// ── Services ──
$services = $pdo->query("SELECT id, name FROM services ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Agents — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
<style>
:root{--ink:#0d1117;--teal:#1aaa96;--teal-d:#138a79;--red:#ef4444;--red-l:#fee2e2;--green:#22c55e;--green-l:#f0fdf4;--amber:#f59e0b;--border:#e5e7eb;--muted:#6b7280;--white:#fff;--cream:#f8fafc;--sidebar:240px;--font-h:'Syne',sans-serif;--font-b:'DM Sans',sans-serif;--r:8px;--t:.18s ease;--shadow:0 1px 3px rgba(0,0,0,.08);--shadow-md:0 4px 16px rgba(0,0,0,.08)}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--font-b);background:var(--cream);color:var(--ink);display:flex;min-height:100vh}
a{color:inherit;text-decoration:none}button{font-family:var(--font-b);cursor:pointer}
.sidebar{width:var(--sidebar);background:var(--ink);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50;overflow-y:auto}
.sidebar__brand{display:flex;align-items:center;gap:10px;padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.07)}
.sidebar__logo{width:36px;height:36px;background:var(--teal);border-radius:8px;display:flex;align-items:center;justify-content:center}
.sidebar__logo svg{width:20px;height:20px}
.sidebar__name{font-family:var(--font-h);font-size:17px;font-weight:800;color:#fff}.sidebar__name span{color:var(--teal)}
.sidebar__section{padding:10px 12px 4px;font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.sidebar__nav{display:flex;flex-direction:column;gap:2px;padding:0 8px}
.sidebar__link{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:6px;font-size:13.5px;font-weight:500;color:rgba(255,255,255,.6);transition:background var(--t),color var(--t)}
.sidebar__link svg{width:16px;height:16px;flex-shrink:0}
.sidebar__link:hover{background:rgba(255,255,255,.07);color:#fff}
.sidebar__link.active{background:var(--teal);color:#fff}
.sidebar__bottom{margin-top:auto;padding:12px 8px;border-top:1px solid rgba(255,255,255,.07)}
.sidebar__avatar{width:32px;height:32px;border-radius:50%;background:var(--teal);color:#fff;font-size:13px;font-weight:700;font-family:var(--font-h);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sidebar__user{display:flex;align-items:center;gap:10px;padding:8px 10px}
.sidebar__user-name{font-size:13px;font-weight:600;color:#fff}
.sidebar__user-role{font-size:11px;color:rgba(255,255,255,.4);text-transform:capitalize}
.sidebar__user-info{display:flex;flex-direction:column;min-width:0}
.sidebar__logout{margin-top:6px;width:100%;padding:8px;border:none;background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);border-radius:6px;font-size:12px;font-weight:600;transition:all var(--t)}
.sidebar__logout:hover{background:var(--red);color:#fff}

.main{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column}
.topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 28px;height:60px;display:flex;align-items:center;justify-content:space-between;gap:16px;position:sticky;top:0;z-index:40}
.topbar__title{font-family:var(--font-h);font-size:18px;font-weight:800;color:var(--ink)}
.page{padding:24px 28px 60px}

.section-title{font-family:var(--font-h);font-size:15px;font-weight:800;color:var(--ink);margin-bottom:14px}

/* Agent grid */
.agents-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:32px}
.agent-card{background:var(--white);border-radius:var(--r);box-shadow:var(--shadow-md);padding:20px;transition:transform var(--t)}
.agent-card:hover{transform:translateY(-2px)}
.agent-card-top{display:flex;align-items:center;gap:12px;margin-bottom:14px}
.agent-av{width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,var(--teal),var(--teal-d));color:#fff;font-family:var(--font-h);font-size:18px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.agent-av.admin-av{background:linear-gradient(135deg,#6d28d9,#4c1d95)}
.agent-card-name{font-weight:700;font-size:15px;color:var(--ink)}
.agent-card-email{font-size:12px;color:var(--muted)}
.role-badge{display:inline-block;padding:2px 8px;border-radius:100px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px}
.role-admin{background:#f5f3ff;color:#6d28d9}
.role-agent{background:var(--green-l);color:#15803d}

.agent-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px}
.agent-stat{background:var(--cream);border-radius:6px;padding:8px;text-align:center}
.agent-stat-val{font-family:var(--font-h);font-size:18px;font-weight:800;color:var(--ink)}
.agent-stat-lbl{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px}
.agent-stat-lbl.red{color:var(--red)}

.service-form{display:flex;gap:8px;align-items:center}
.service-form select{flex:1;padding:8px 10px;font-size:13px;font-family:var(--font-b);border:1.5px solid var(--border);border-radius:6px;outline:none;color:var(--ink);background:var(--cream);transition:border-color var(--t)}
.service-form select:focus{border-color:var(--teal)}

.btn{display:inline-flex;align-items:center;gap:6px;padding:7px 12px;border-radius:6px;font-size:12.5px;font-weight:600;border:1.5px solid var(--border);background:var(--white);color:var(--ink);transition:all var(--t);cursor:pointer}
.btn:hover{border-color:var(--teal);color:var(--teal)}
.btn-primary{background:var(--teal);color:#fff;border-color:var(--teal)}
.btn-primary:hover{background:var(--teal-d);border-color:var(--teal-d)}
.btn-danger{border-color:#fca5a5;color:var(--red)}
.btn-danger:hover{background:var(--red);color:#fff;border-color:var(--red)}
.btn-sm{padding:5px 10px;font-size:12px}

/* Promote section */
.promote-card{background:var(--white);border-radius:var(--r);box-shadow:var(--shadow);padding:20px;margin-bottom:32px}
.promote-card h3{font-family:var(--font-h);font-size:14px;font-weight:800;color:var(--ink);margin-bottom:14px}
.promote-form{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.promote-form label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);display:block;margin-bottom:5px}
.promote-form select{padding:9px 12px;font-size:14px;font-family:var(--font-b);border:1.5px solid var(--border);border-radius:6px;outline:none;color:var(--ink);background:var(--cream);min-width:200px}
.promote-form select:focus{border-color:var(--teal)}

@media(max-width:900px){.sidebar{display:none}.main{margin-left:0}}
</style>
</head>
<body>
<aside class="sidebar">
  <div class="sidebar__brand">
    <div class="sidebar__logo"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div>
    <div class="sidebar__name">Salah<span>Homty</span></div>
  </div>
  <div style="height:12px"></div>
  <div class="sidebar__section">Main</div>
  <nav class="sidebar__nav">
    <a href="index.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Dashboard</a>
    <a href="reports.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>All Reports</a>
  </nav>
  <div style="height:8px"></div>
  <div class="sidebar__section">Management</div>
  <nav class="sidebar__nav">
    <a href="agents.php" class="sidebar__link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Agents</a>
    <a href="services.php" class="sidebar__link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="1"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>Services</a>
  </nav>
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

<div class="main">
  <div class="topbar">
    <span class="topbar__title">Agents & Staff</span>
  </div>
  <div class="page">

    <!-- Promote citizen to agent -->
    <div class="promote-card">
      <h3>➕ Promote Citizen to Agent</h3>
      <form method="POST">
        <input type="hidden" name="action" value="promote">
        <div class="promote-form">
          <div>
            <label>Citizen</label>
            <select name="user_id" required>
              <option value="">— Select user —</option>
              <?php foreach ($citizens as $c): ?>
              <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?> &lt;<?= htmlspecialchars($c['email']) ?>&gt;</option>
              <?php endforeach ?>
            </select>
          </div>
          <div>
            <label>Assign to Service</label>
            <select name="service_id" required>
              <option value="">— Select service —</option>
              <?php foreach ($services as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Promote to Agent</button>
        </div>
      </form>
    </div>

    <!-- Agent cards -->
    <div class="section-title"><?= count($agents) ?> Staff members</div>
    <div class="agents-grid">
      <?php foreach ($agents as $ag): ?>
      <div class="agent-card">
        <div class="agent-card-top">
          <div class="agent-av <?= $ag['role']==='admin'?'admin-av':'' ?>"><?= strtoupper(substr($ag['name'],0,1)) ?></div>
          <div>
            <div class="agent-card-name"><?= htmlspecialchars($ag['name']) ?></div>
            <div class="agent-card-email"><?= htmlspecialchars($ag['email']) ?></div>
            <span class="role-badge role-<?= $ag['role'] ?>"><?= $ag['role'] ?></span>
          </div>
        </div>

        <div class="agent-stats">
          <div class="agent-stat">
            <div class="agent-stat-val"><?= $ag['total'] ?></div>
            <div class="agent-stat-lbl">Total</div>
          </div>
          <div class="agent-stat">
            <div class="agent-stat-val"><?= $ag['active'] ?></div>
            <div class="agent-stat-lbl">Active</div>
          </div>
          <div class="agent-stat">
            <div class="agent-stat-val" style="color:var(--red)"><?= $ag['overdue'] ?></div>
            <div class="agent-stat-lbl red">Overdue</div>
          </div>
        </div>

        <!-- Change service -->
        <form method="POST" class="service-form" style="margin-bottom:10px">
          <input type="hidden" name="action" value="update_service">
          <input type="hidden" name="user_id" value="<?= $ag['id'] ?>">
          <select name="service_id">
            <option value="">No service</option>
            <?php foreach ($services as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $ag['service_id']==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option>
            <?php endforeach ?>
          </select>
          <button type="submit" class="btn btn-primary btn-sm">Save</button>
        </form>

        <!-- Demote (protect own account and other admins) -->
        <?php if ($ag['role']==='agent'): ?>
        <form method="POST">
          <input type="hidden" name="action" value="demote">
          <input type="hidden" name="user_id" value="<?= $ag['id'] ?>">
          <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Remove agent role from <?= addslashes($ag['name']) ?>?')">Remove agent role</button>
        </form>
        <?php endif ?>
      </div>
      <?php endforeach ?>
      <?php if (empty($agents)): ?>
      <div style="grid-column:1/-1;padding:32px;text-align:center;color:var(--muted)">No agents yet. Promote a citizen above.</div>
      <?php endif ?>
    </div>

  </div>
</div>
</body>
</html>