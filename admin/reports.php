<?php
// admin/reports.php
require_once 'auth_check.php';

// ── Handle POST actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action']    ?? '';
    $report_id = (int)($_POST['report_id'] ?? 0);

    switch ($action) {

        // ── Update status ──
        case 'update_status':
            $newStatus = $_POST['status'] ?? '';
            $note      = trim($_POST['note'] ?? '');
            $allowed   = ['Open','In progress','Resolved','Transferred'];
            if ($report_id && in_array($newStatus, $allowed)) {
                $old = $pdo->prepare("SELECT status FROM reports WHERE id=:id")->execute([':id'=>$report_id]);
                $old = $pdo->query("SELECT status FROM reports WHERE id=$report_id")->fetchColumn();
                $pdo->prepare("UPDATE reports SET status=:s WHERE id=:id")->execute([':s'=>$newStatus,':id'=>$report_id]);
                $pdo->prepare("INSERT INTO report_logs (report_id,changed_by,old_status,new_status,note) VALUES (:r,:u,:o,:n,:note)")
                    ->execute([':r'=>$report_id,':u'=>$currentUser['id'],':o'=>$old,':n'=>$newStatus,':note'=>$note]);
            }
            break;

        // ── Assign agent ──
        case 'assign_agent':
            $agent_id = (int)($_POST['agent_id'] ?? 0);
            $deadline = $_POST['deadline'] ?? '';
            if ($report_id && $agent_id) {
                $dl = $deadline ? date('Y-m-d H:i:s', strtotime($deadline)) : date('Y-m-d H:i:s', strtotime('+7 days'));
                $pdo->prepare("UPDATE reports SET assigned_to=:a, deadline=:d, status='In progress' WHERE id=:id")
                    ->execute([':a'=>$agent_id,':d'=>$dl,':id'=>$report_id]);
                $pdo->prepare("INSERT INTO report_logs (report_id,changed_by,old_status,new_status,note) VALUES (:r,:u,'Open','In progress','Assigned to agent')")
                    ->execute([':r'=>$report_id,':u'=>$currentUser['id']]);
            }
            break;

        // ── Transfer to another agent ──
        case 'transfer':
            $new_agent = (int)($_POST['agent_id'] ?? 0);
            $note      = trim($_POST['note'] ?? '');
            if ($report_id && $new_agent) {
                $pdo->prepare("UPDATE reports SET assigned_to=:a, status='In progress', deadline=:d WHERE id=:id")
                    ->execute([':a'=>$new_agent,':d'=>date('Y-m-d H:i:s', strtotime('+7 days')),':id'=>$report_id]);
                $pdo->prepare("INSERT INTO report_logs (report_id,changed_by,old_status,new_status,note) VALUES (:r,:u,'Overdue','In progress',:n)")
                    ->execute([':r'=>$report_id,':u'=>$currentUser['id'],':n'=>$note ?: 'Transferred to another agent']);
            }
            break;
    }

    header('Location: reports.php?' . http_build_query(array_filter([
        'filter'   => $_GET['filter']   ?? '',
        'status'   => $_GET['status']   ?? '',
        'category' => $_GET['category'] ?? '',
    ])));
    exit;
}

// ── Filters ──
$filterStatus   = $_GET['status']   ?? '';
$filterCat      = $_GET['category'] ?? '';
$filterPreset   = $_GET['filter']   ?? '';  // overdue | unassigned
$search         = trim($_GET['q']   ?? '');

// ── Build WHERE ──
$where = ['1=1'];
$params = [];

if ($filterPreset === 'overdue') {
    $where[] = "(r.status = 'Overdue' OR (r.deadline < NOW() AND r.status NOT IN ('Resolved','Transferred')))";
} elseif ($filterPreset === 'unassigned') {
    $where[] = 'r.assigned_to IS NULL';
}

// Agents can only see their own reports
if (!isAdmin()) {
    $where[] = 'r.assigned_to = :agent_id';
    $params[':agent_id'] = $currentUser['id'];
}

if ($filterStatus) { $where[] = 'r.status = :status'; $params[':status'] = $filterStatus; }
if ($filterCat)    { $where[] = 'r.category_id = :cat'; $params[':cat'] = $filterCat; }
if ($search)       { $where[] = '(r.description LIKE :q OR u.name LIKE :q OR r.id = :qid)'; $params[':q'] = "%$search%"; $params[':qid'] = (int)$search; }

$whereStr = implode(' AND ', $where);

// ── Pagination ──
$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$totalQ = $pdo->prepare("SELECT COUNT(*) FROM reports r LEFT JOIN users u ON u.id=r.user_id WHERE $whereStr");
$totalQ->execute($params);
$total = (int)$totalQ->fetchColumn();
$pages = ceil($total / $perPage);

$reportsQ = $pdo->prepare("
    SELECT r.*, u.name AS citizen, ag.name AS agent_name,
           c.name AS category, s.name AS service, s.id AS service_id
    FROM reports r
    LEFT JOIN users u  ON u.id = r.user_id
    LEFT JOIN users ag ON ag.id = r.assigned_to
    LEFT JOIN categories c ON c.id = r.category_id
    LEFT JOIN services s   ON s.id = c.service_id
    WHERE $whereStr
    ORDER BY
      CASE WHEN r.status='Overdue' THEN 0 WHEN r.status='Open' THEN 1 ELSE 2 END,
      r.created_at DESC
    LIMIT $perPage OFFSET $offset
");
$reportsQ->execute($params);
$reports = $reportsQ->fetchAll(PDO::FETCH_ASSOC);

// ── Agents for assignment dropdowns ──
$agents = $pdo->query("SELECT u.id, u.name, s.name AS service FROM users u LEFT JOIN services s ON s.id=u.service_id WHERE u.role='agent' ORDER BY u.name")->fetchAll(PDO::FETCH_ASSOC);

// ── Categories for filter ──
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// ── Auto-mark overdue ──
$pdo->exec("UPDATE reports SET status='Overdue' WHERE deadline < NOW() AND status NOT IN ('Resolved','Transferred','Overdue')");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Reports — Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
<style>
:root{
  --ink:#0d1117;--ink2:#1c2230;--ink3:#2d3748;
  --teal:#1aaa96;--teal-d:#138a79;--teal-l:#e8f9f7;
  --red:#ef4444;--red-l:#fee2e2;
  --amber:#f59e0b;--amber-l:#fffbeb;
  --green:#22c55e;--green-l:#f0fdf4;
  --blue:#3b82f6;--blue-l:#eff6ff;
  --border:#e5e7eb;--muted:#6b7280;
  --white:#fff;--cream:#f8fafc;
  --sidebar:240px;
  --font-h:'Syne',sans-serif;--font-b:'DM Sans',sans-serif;
  --r:8px;--t:.18s ease;
  --shadow:0 1px 3px rgba(0,0,0,.08);--shadow-md:0 4px 16px rgba(0,0,0,.08);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:var(--font-b);background:var(--cream);color:var(--ink);display:flex;min-height:100vh}
a{color:inherit;text-decoration:none}button{font-family:var(--font-b);cursor:pointer}

/* Sidebar (shared style) */
.sidebar{width:var(--sidebar);background:var(--ink);display:flex;flex-direction:column;position:fixed;top:0;left:0;bottom:0;z-index:50;overflow-y:auto}
.sidebar__brand{display:flex;align-items:center;gap:10px;padding:20px 18px 16px;border-bottom:1px solid rgba(255,255,255,.07)}
.sidebar__logo{width:36px;height:36px;background:var(--teal);border-radius:8px;display:flex;align-items:center;justify-content:center}
.sidebar__logo svg{width:20px;height:20px}
.sidebar__name{font-family:var(--font-h);font-size:17px;font-weight:800;color:#fff}
.sidebar__name span{color:var(--teal)}
.sidebar__section{padding:10px 12px 4px;font-size:10px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.3)}
.sidebar__nav{display:flex;flex-direction:column;gap:2px;padding:0 8px}
.sidebar__link{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:6px;font-size:13.5px;font-weight:500;color:rgba(255,255,255,.6);transition:background var(--t),color var(--t)}
.sidebar__link svg{width:16px;height:16px;flex-shrink:0}
.sidebar__link:hover{background:rgba(255,255,255,.07);color:#fff}
.sidebar__link.active{background:var(--teal);color:#fff}
.sidebar__link .badge{margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:100px}
.sidebar__bottom{margin-top:auto;padding:12px 8px;border-top:1px solid rgba(255,255,255,.07)}
.sidebar__avatar{width:32px;height:32px;border-radius:50%;background:var(--teal);color:#fff;font-size:13px;font-weight:700;font-family:var(--font-h);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sidebar__user{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:6px}
.sidebar__user-info{display:flex;flex-direction:column;min-width:0}
.sidebar__user-name{font-size:13px;font-weight:600;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sidebar__user-role{font-size:11px;color:rgba(255,255,255,.4);text-transform:capitalize}
.sidebar__logout{margin-top:6px;width:100%;padding:8px;border:none;background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);border-radius:6px;font-size:12px;font-weight:600;transition:all var(--t)}
.sidebar__logout:hover{background:var(--red);color:#fff}

.main{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column}
.topbar{background:var(--white);border-bottom:1px solid var(--border);padding:0 28px;height:60px;display:flex;align-items:center;justify-content:space-between;gap:16px;position:sticky;top:0;z-index:40}
.topbar__title{font-family:var(--font-h);font-size:18px;font-weight:800;color:var(--ink)}
.page{padding:24px 28px 60px}

/* Toolbar */
.toolbar{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;align-items:center}
.search-box{display:flex;align-items:center;gap:8px;background:var(--white);border:1.5px solid var(--border);border-radius:var(--r);padding:0 12px;flex:1;min-width:180px}
.search-box input{flex:1;border:none;outline:none;font-size:14px;font-family:var(--font-b);padding:10px 0;color:var(--ink);background:transparent}
.filter-select{background:var(--white);border:1.5px solid var(--border);border-radius:var(--r);padding:9px 12px;font-size:13.5px;font-family:var(--font-b);color:var(--ink);cursor:pointer}
.filter-select:focus{outline:none;border-color:var(--teal)}
.btn{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:6px;font-size:13px;font-weight:600;border:1.5px solid var(--border);background:var(--white);color:var(--ink);transition:all var(--t);cursor:pointer;text-decoration:none}
.btn:hover{border-color:var(--teal);color:var(--teal)}
.btn-primary{background:var(--teal);color:#fff;border-color:var(--teal)}
.btn-primary:hover{background:var(--teal-d);border-color:var(--teal-d)}
.btn-danger{border-color:#fca5a5;color:var(--red)}
.btn-danger:hover{background:var(--red);color:#fff;border-color:var(--red)}
.btn-sm{padding:5px 10px;font-size:12px}

/* Tab filters */
.tabs{display:flex;gap:6px;margin-bottom:18px;flex-wrap:wrap}
.tab{
  padding:6px 14px;border-radius:100px;font-size:12.5px;font-weight:600;
  background:var(--white);border:1.5px solid var(--border);color:var(--muted);
  cursor:pointer;transition:all var(--t);text-decoration:none;
}
.tab:hover{border-color:var(--teal);color:var(--teal)}
.tab.active{background:var(--ink);color:#fff;border-color:var(--ink)}
.tab-overdue.active{background:var(--red);border-color:var(--red)}

/* Table */
.table-card{background:var(--white);border-radius:var(--r);box-shadow:var(--shadow-md);overflow:hidden}
table{width:100%;border-collapse:collapse}
thead{background:var(--cream);border-bottom:2px solid var(--border)}
th{text-align:left;padding:11px 14px;font-size:11px;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:var(--muted)}
td{padding:12px 14px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle}
tr:last-child td{border-bottom:none}
tr.overdue-row{background:#fff8f8}
tr:hover td{background:#fafafa}
.td-id{font-family:var(--font-h);font-size:12px;font-weight:700;color:var(--teal)}
.td-desc{max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.td-muted{color:var(--muted);font-size:12.5px}

/* Pagination */
.pagination{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-top:1px solid var(--border);font-size:13px;color:var(--muted);flex-wrap:wrap;gap:10px}
.page-btns{display:flex;gap:4px}
.page-btns a{width:32px;height:32px;border-radius:6px;border:1.5px solid var(--border);background:var(--white);font-size:13px;font-weight:600;color:var(--ink);display:flex;align-items:center;justify-content:center;transition:all var(--t)}
.page-btns a:hover,.page-btns a.active{background:var(--teal);border-color:var(--teal);color:#fff}

/* ── MODAL ── */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:200;align-items:center;justify-content:center;padding:20px}
.modal-overlay.open{display:flex}
.modal{background:var(--white);border-radius:12px;width:100%;max-width:480px;box-shadow:0 24px 64px rgba(0,0,0,.2);overflow:hidden;max-height:90vh;overflow-y:auto}
.modal-header{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border);position:sticky;top:0;background:var(--white);z-index:1}
.modal-header h2{font-family:var(--font-h);font-size:17px;font-weight:800;color:var(--ink)}
.modal-close{background:none;border:none;font-size:22px;color:var(--muted);cursor:pointer;line-height:1;transition:color var(--t)}
.modal-close:hover{color:var(--ink)}
.modal-body{padding:22px}
.modal-field{margin-bottom:16px}
.modal-field label{display:block;font-size:12px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);margin-bottom:6px}
.modal-field select,.modal-field textarea,.modal-field input[type=datetime-local]{
  width:100%;padding:10px 12px;font-size:14px;font-family:var(--font-b);
  border:1.5px solid var(--border);border-radius:6px;outline:none;color:var(--ink);
  transition:border-color var(--t);background:var(--cream);
}
.modal-field select:focus,.modal-field textarea:focus,.modal-field input:focus{border-color:var(--teal)}
.modal-field textarea{resize:vertical;min-height:80px}
.modal-footer{display:flex;gap:10px;justify-content:flex-end;padding:14px 22px;border-top:1px solid var(--border);background:var(--cream)}

/* Info box in modal */
.info-box{background:var(--cream);border-radius:6px;padding:12px;margin-bottom:16px;font-size:13px;line-height:1.6}
.info-box strong{display:block;font-weight:700;color:var(--ink);margin-bottom:4px}

/* Log timeline */
.log-list{display:flex;flex-direction:column;gap:0}
.log-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--border);font-size:13px}
.log-item:last-child{border-bottom:none}
.log-dot{width:10px;height:10px;border-radius:50%;background:var(--teal);flex-shrink:0;margin-top:4px}
.log-meta{color:var(--muted);font-size:12px;margin-top:2px}

@media(max-width:900px){.sidebar{display:none}.main{margin-left:0}}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar__brand">
    <div class="sidebar__logo">
      <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
    </div>
    <div class="sidebar__name">Salah<span>Homty</span></div>
  </div>
  <div style="height:12px"></div>
  <div class="sidebar__section">Main</div>
  <nav class="sidebar__nav">
    <a href="index.php" class="sidebar__link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Dashboard
    </a>
    <a href="reports.php" class="sidebar__link active">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      All Reports
    </a>
    <a href="reports.php?filter=overdue" class="sidebar__link">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      Overdue
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

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <span class="topbar__title">
      <?= $filterPreset === 'overdue' ? '⚠ Overdue Reports' : 'All Reports' ?>
    </span>
    <div style="display:flex;gap:8px">
      <a href="reports.php?filter=unassigned" class="btn btn-sm">Unassigned</a>
      <a href="reports.php" class="btn btn-sm btn-primary">All</a>
    </div>
  </div>

  <div class="page">

    <!-- Tabs -->
    <div class="tabs">
      <a href="reports.php" class="tab <?= !$filterPreset && !$filterStatus ? 'active' : '' ?>">All (<?= $total ?>)</a>
      <a href="reports.php?status=Open" class="tab <?= $filterStatus==='Open'?'active':'' ?>">Open</a>
      <a href="reports.php?status=In+progress" class="tab <?= $filterStatus==='In progress'?'active':'' ?>">In progress</a>
      <a href="reports.php?status=Resolved" class="tab <?= $filterStatus==='Resolved'?'active':'' ?>">Resolved</a>
      <a href="reports.php?filter=overdue" class="tab tab-overdue <?= $filterPreset==='overdue'?'active':'' ?>">⚠ Overdue</a>
      <a href="reports.php?filter=unassigned" class="tab <?= $filterPreset==='unassigned'?'active':'' ?>">Unassigned</a>
    </div>

    <!-- Toolbar -->
    <form method="GET" action="reports.php">
      <input type="hidden" name="filter" value="<?= htmlspecialchars($filterPreset) ?>">
      <div class="toolbar">
        <div class="search-box">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search by ID, description…" />
        </div>
        <select class="filter-select" name="category">
          <option value="">All categories</option>
          <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $filterCat == $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="reports.php" class="btn">Reset</a>
      </div>
    </form>

    <!-- Table -->
    <div class="table-card">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Description</th>
            <th>Category / Service</th>
            <th>Citizen</th>
            <th>Agent</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($reports as $r):
          $isOverdue = $r['status'] === 'Overdue';
          $deadlineFmt = $r['deadline'] ? date('M j, H:i', strtotime($r['deadline'])) : '—';
          $deadlinePast = $r['deadline'] && strtotime($r['deadline']) < time();
        ?>
          <tr class="<?= $isOverdue ? 'overdue-row' : '' ?>">
            <td><span class="td-id">#<?= $r['id'] ?></span></td>
            <td>
              <span class="td-desc" title="<?= htmlspecialchars($r['description'] ?? '') ?>">
                <?= htmlspecialchars(mb_strimwidth($r['description'] ?? 'No description', 0, 40, '…')) ?>
              </span>
              <div class="td-muted" style="font-size:11px;margin-top:2px"><?= timeAgo($r['created_at']) ?></div>
            </td>
            <td>
              <div style="font-size:13px;font-weight:500"><?= htmlspecialchars($r['category'] ?? '—') ?></div>
              <div class="td-muted" style="font-size:11px"><?= htmlspecialchars($r['service'] ?? '—') ?></div>
            </td>
            <td class="td-muted"><?= htmlspecialchars($r['citizen'] ?? 'Anonymous') ?></td>
            <td>
              <?php if ($r['agent_name']): ?>
                <span style="font-size:13px;font-weight:500"><?= htmlspecialchars($r['agent_name']) ?></span>
              <?php else: ?>
                <span style="color:var(--amber);font-size:12px;font-weight:600">⚠ Unassigned</span>
              <?php endif ?>
            </td>
            <td>
              <span style="font-size:12.5px;color:<?= $deadlinePast && !in_array($r['status'],['Resolved','Transferred']) ? 'var(--red)' : 'var(--muted)' ?>;font-weight:<?= $deadlinePast?'700':'400' ?>">
                <?= $deadlineFmt ?>
              </span>
            </td>
            <td><?= statusBadge($r['status']) ?></td>
            <td>
              <div style="display:flex;gap:6px;flex-wrap:wrap">
                <!-- View / update status -->
                <button class="btn btn-sm" onclick='openDetailModal(<?= json_encode($r) ?>)'>Detail</button>
                <?php if (isAdmin()): ?>
                  <button class="btn btn-sm" onclick='openAssignModal(<?= $r['id'] ?>, <?= json_encode($r['description'] ?? '') ?>, <?= $r['assigned_to'] ?? 'null' ?>)'>Assign</button>
                <?php endif ?>
                <?php if ($isOverdue && isAdmin()): ?>
                  <button class="btn btn-sm btn-danger" onclick='openTransferModal(<?= $r['id'] ?>, <?= json_encode($r['description'] ?? '') ?>)'>⇄ Transfer</button>
                <?php endif ?>
              </div>
            </td>
          </tr>
        <?php endforeach ?>
        <?php if (empty($reports)): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">No reports found matching your filters.</td></tr>
        <?php endif ?>
        </tbody>
      </table>
      <!-- Pagination -->
      <div class="pagination">
        <span>Showing <?= min(($page-1)*$perPage+1,$total) ?>–<?= min($page*$perPage,$total) ?> of <?= $total ?></span>
        <div class="page-btns">
          <?php for ($i=1;$i<=$pages;$i++): ?>
          <a href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>" class="<?= $i==$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor ?>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ═══ MODALS ═══ -->

<!-- Detail / Status Update Modal -->
<div class="modal-overlay" id="detailModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-header">
      <h2 id="detailTitle">Report Detail</h2>
      <button class="modal-close" onclick="document.getElementById('detailModal').classList.remove('open')">✕</button>
    </div>
    <div class="modal-body">
      <div class="info-box" id="detailInfo"></div>
      <div id="logSection"></div>
      <form method="POST" action="reports.php">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="report_id" id="detail_report_id">
        <div class="modal-field">
          <label>Update Status</label>
          <select name="status" id="detail_status">
            <option value="Open">Open</option>
            <option value="In progress">In progress</option>
            <option value="Resolved">Resolved</option>
            <option value="Transferred">Transferred</option>
          </select>
        </div>
        <div class="modal-field">
          <label>Note (optional)</label>
          <textarea name="note" placeholder="Add a note about this status change…"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn" onclick="document.getElementById('detailModal').classList.remove('open')">Cancel</button>
          <button type="submit" class="btn btn-primary">Save status</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Assign Agent Modal -->
<div class="modal-overlay" id="assignModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-header">
      <h2>Assign Agent</h2>
      <button class="modal-close" onclick="document.getElementById('assignModal').classList.remove('open')">✕</button>
    </div>
    <div class="modal-body">
      <div class="info-box" id="assignInfo"></div>
      <form method="POST" action="reports.php">
        <input type="hidden" name="action" value="assign_agent">
        <input type="hidden" name="report_id" id="assign_report_id">
        <div class="modal-field">
          <label>Select Agent</label>
          <select name="agent_id" id="assign_agent_select">
            <option value="">— Choose an agent —</option>
            <?php foreach ($agents as $ag): ?>
            <option value="<?= $ag['id'] ?>"><?= htmlspecialchars($ag['name']) ?> (<?= htmlspecialchars($ag['service'] ?? 'No service') ?>)</option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="modal-field">
          <label>Deadline</label>
          <input type="datetime-local" name="deadline" value="<?= date('Y-m-d\TH:i', strtotime('+7 days')) ?>">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn" onclick="document.getElementById('assignModal').classList.remove('open')">Cancel</button>
          <button type="submit" class="btn btn-primary">Assign</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Transfer Modal -->
<div class="modal-overlay" id="transferModal" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-header">
      <h2>⇄ Transfer Report</h2>
      <button class="modal-close" onclick="document.getElementById('transferModal').classList.remove('open')">✕</button>
    </div>
    <div class="modal-body">
      <div class="info-box" id="transferInfo" style="background:#fee2e2;color:#991b1b">
        This report is <strong>overdue</strong>. Transfer it to another agent.
      </div>
      <form method="POST" action="reports.php">
        <input type="hidden" name="action" value="transfer">
        <input type="hidden" name="report_id" id="transfer_report_id">
        <div class="modal-field">
          <label>Transfer to Agent</label>
          <select name="agent_id">
            <option value="">— Choose an agent —</option>
            <?php foreach ($agents as $ag): ?>
            <option value="<?= $ag['id'] ?>"><?= htmlspecialchars($ag['name']) ?> (<?= htmlspecialchars($ag['service'] ?? '') ?>)</option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="modal-field">
          <label>Reason for transfer</label>
          <textarea name="note" placeholder="e.g. Original agent unavailable, deadline exceeded…"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn" onclick="document.getElementById('transferModal').classList.remove('open')">Cancel</button>
          <button type="submit" class="btn btn-danger">Transfer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const statusLabels = {Open:'Open','In progress':'In progress',Resolved:'Resolved',Transferred:'Transferred',Overdue:'Overdue'};

function openDetailModal(r) {
  document.getElementById('detailTitle').textContent = 'Report #' + r.id;
  document.getElementById('detail_report_id').value  = r.id;
  document.getElementById('detail_status').value     = r.status;

  // Info box
  document.getElementById('detailInfo').innerHTML =
    `<strong>${r.description || 'No description'}</strong>
     <div style="margin-top:6px;color:var(--muted);font-size:12px">
       Category: ${r.category||'—'} &nbsp;·&nbsp;
       Service: ${r.service||'—'} &nbsp;·&nbsp;
       Agent: ${r.agent_name||'Unassigned'}
     </div>`;

  // Fetch logs
  fetch('api_logs.php?report_id=' + r.id)
    .then(res=>res.json())
    .then(logs=>{
      if (!logs.length) { document.getElementById('logSection').innerHTML=''; return; }
      document.getElementById('logSection').innerHTML =
        '<div style="font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin-bottom:8px">History</div>'
        + '<div class="log-list">'
        + logs.map(l=>`<div class="log-item">
            <div class="log-dot"></div>
            <div>
              <div style="font-weight:600">${l.old_status} → ${l.new_status}</div>
              ${l.note ? '<div style="color:var(--muted);font-size:12px">'+l.note+'</div>' : ''}
              <div class="log-meta">${l.changed_by} · ${l.created_at}</div>
            </div>
          </div>`).join('')
        + '</div>';
    })
    .catch(()=>{ document.getElementById('logSection').innerHTML=''; });

  document.getElementById('detailModal').classList.add('open');
}

function openAssignModal(id, desc, currentAgent) {
  document.getElementById('assign_report_id').value = id;
  document.getElementById('assignInfo').innerHTML =
    '<strong>Report #' + id + '</strong><div style="margin-top:4px;font-size:12px;color:var(--muted)">' + (desc||'No description') + '</div>';
  document.getElementById('assignModal').classList.add('open');
}

function openTransferModal(id, desc) {
  document.getElementById('transfer_report_id').value = id;
  document.getElementById('transferInfo').innerHTML =
    'This report <strong>#' + id + '</strong> is <strong>overdue</strong>. Transfer it to another agent.<br><span style="font-size:12px;color:#991b1b">' + (desc||'') + '</span>';
  document.getElementById('transferModal').classList.add('open');
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m=>m.classList.remove('open'));
});
</script>
</body>
</html>