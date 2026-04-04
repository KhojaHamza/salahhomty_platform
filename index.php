<?php
session_start();
require_once 'config/database.php';

// ── 5 most recent reports with comment count ──
$recentReports = [];
try {
    $stmt = $pdo->query("
        SELECT r.id, r.title, r.image, r.description, r.created_at, r.status,
               u.name AS user_name,
               (SELECT COUNT(*) FROM comments c WHERE c.report_id = r.id) AS comment_count
        FROM reports r
        LEFT JOIN users u ON u.id = r.user_id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $recentReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fail silently
}

// ── Load comments for recent reports ──
$reportIds = array_column($recentReports, 'id');
$commentsByReport = [];
if (!empty($reportIds)) {
    $inList = implode(',', array_map('intval', $reportIds));
    $cstmt  = $pdo->query("
        SELECT c.id, c.report_id, c.content, c.created_at, u.name AS user_name
        FROM comments c
        LEFT JOIN users u ON u.id = c.user_id
        WHERE c.report_id IN ($inList)
        ORDER BY c.created_at ASC
    ");
    foreach ($cstmt->fetchAll(PDO::FETCH_ASSOC) as $cm) {
        $commentsByReport[$cm['report_id']][] = $cm;
    }
}

// ── Helper: human-readable time ──
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'À l\'instant';
    if ($diff < 3600)   return floor($diff / 60) . ' min';
    if ($diff < 86400)  return 'Aujourd\'hui ' . date('H:i', strtotime($datetime));
    if ($diff < 172800) return 'Hier ' . date('H:i', strtotime($datetime));
    return date('d M Y', strtotime($datetime));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SalahHomty – Signalez, visualisez et discutez des problèmes locaux</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet" />
  <style>
    :root {
      --teal:       #1aaa96;
      --teal-dark:  #138a79;
      --teal-light: #e8f9f7;
      --dark:       #111827;
      --dark2:      #1f2937;
      --mid:        #374151;
      --muted:      #6b7280;
      --border:     #e5e7eb;
      --white:      #ffffff;
      --cream:      #f9fafb;
      --accent:     #f7c200;
      --font-head:  'Syne', sans-serif;
      --font-body:  'DM Sans', sans-serif;
      --r-sm: 6px; --r-md: 12px; --r-lg: 20px;
      --shadow-sm: 0 1px 3px rgba(0,0,0,.08);
      --shadow-md: 0 4px 16px rgba(0,0,0,.10);
      --shadow-lg: 0 12px 40px rgba(0,0,0,.14);
      --t: 0.2s ease;
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body { font-family: var(--font-body); background: var(--cream); color: var(--dark); min-height: 100vh; overflow-x: hidden; }
    a { color: inherit; text-decoration: none; }
    button { font-family: var(--font-body); cursor: pointer; }
    ul, ol { list-style: none; }
    img { display: block; }

    /* ── BANNER ── */
    .banner { background: var(--dark); color: #d1d5db; text-align: center; padding: 10px 60px; font-size: 13.5px; line-height: 1.6; position: relative; }
    .banner a { color: var(--accent); text-decoration: underline; font-weight: 600; }
    .banner__close { position: absolute; right: 20px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #9ca3af; font-size: 18px; cursor: pointer; }
    .banner__close:hover { color: var(--white); }

    /* ── NAVBAR ── */
    .navbar { background: var(--dark2); display: flex; align-items: center; height: 68px; padding: 0 40px; gap: 18px; position: sticky; top: 0; z-index: 100; box-shadow: 0 2px 12px rgba(0,0,0,.25); }
    .navbar__logo { width: 40px; height: 40px; background: var(--teal); border-radius: var(--r-sm); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .navbar__logo svg { width: 22px; height: 22px; }
    .navbar__brand { display: flex; align-items: center; gap: 10px; }
    .navbar__brand-name { font-family: var(--font-head); font-size: 22px; font-weight: 800; letter-spacing: -.5px; }
    .word--salah { color: var(--white); } .word--homty { color: var(--teal); }
    .navbar__spacer { flex: 1; }
    .navbar__links { display: flex; align-items: center; gap: 4px; }
    .navbar__links a { color: #9ca3af; font-size: 13.5px; padding: 6px 14px; border-radius: var(--r-sm); transition: color var(--t), background var(--t); }
    .navbar__links a:hover { color: var(--white); background: rgba(255,255,255,.08); }
    .navbar__links .btn-signin { background: var(--teal); color: var(--white) !important; font-weight: 600; }
    .navbar__links .btn-signin:hover { background: var(--teal-dark) !important; }

    /* ── HERO ── */
    .hero { background: linear-gradient(135deg, var(--teal) 0%, #16c9b2 60%, #0e8c7e 100%); padding: 80px 60px 100px; position: relative; overflow: hidden; }
    .hero::before { content: ''; position: absolute; top: -80px; right: -120px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 70%); border-radius: 50%; pointer-events: none; }
    .hero__eyebrow { display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,.18); color: var(--white); font-size: 12px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; padding: 6px 14px; border-radius: 100px; margin-bottom: 24px; backdrop-filter: blur(4px); }
    .hero__eyebrow span { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; }
    .hero__title { font-family: var(--font-head); font-size: clamp(36px,5vw,62px); font-weight: 800; line-height: 1.05; letter-spacing: -1.5px; color: var(--white); max-width: 680px; margin-bottom: 14px; }
    .hero__subtitle { font-size: 17px; color: rgba(255,255,255,.78); margin-bottom: 52px; max-width: 500px; line-height: 1.6; }
    .hero__grid { display: grid; grid-template-columns: 1.1fr 1fr; gap: 40px; align-items: start; max-width: 860px; position: relative; z-index: 1; }

    /* ── FORM CARD (redesigned) ── */
    .search-card { background: var(--white); border-radius: var(--r-lg); padding: 32px; box-shadow: var(--shadow-lg); }
    .search-card__title { font-family: var(--font-head); font-size: 18px; font-weight: 800; color: var(--dark); margin-bottom: 6px; }
    .search-card__subtitle { font-size: 13px; color: var(--muted); margin-bottom: 24px; line-height: 1.5; }

    .form-field { margin-bottom: 16px; }
    .form-label {
      display: flex; align-items: center; gap: 6px;
      font-size: 11px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase;
      color: var(--muted); margin-bottom: 6px;
    }
    .form-label svg { width: 12px; height: 12px; stroke: currentColor; fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }
    .form-input {
      width: 100%; padding: 11px 14px; font-size: 14px; font-family: var(--font-body);
      color: var(--dark); background: var(--cream);
      border: 1.5px solid var(--border); border-radius: var(--r-sm);
      outline: none; transition: border-color var(--t), box-shadow var(--t), background var(--t);
    }
    .form-input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(26,170,150,.15); background: var(--white); }
    .form-input::placeholder { color: #adb5bd; }
    textarea.form-input { resize: vertical; min-height: 80px; line-height: 1.5; }

    .search__submit-btn {
      width: 100%; padding: 13px 20px;
      background: var(--dark); color: var(--white);
      border: none; border-radius: var(--r-sm);
      font-family: var(--font-head); font-size: 15px; font-weight: 700; letter-spacing: .4px;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      cursor: pointer; transition: background var(--t), transform var(--t), box-shadow var(--t);
      box-shadow: 0 2px 8px rgba(17,24,39,.25);
      margin-top: 8px;
    }
    .search__submit-btn:hover { background: var(--teal); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,170,150,.35); }
    .search__submit-btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; }

    .search__location-btn {
      display: inline-flex; align-items: center; gap: 8px; width: 100%;
      background: var(--teal-light); border: 1.5px solid var(--teal);
      padding: 10px 16px; font-size: 13.5px; font-weight: 600; color: var(--teal-dark);
      border-radius: var(--r-sm); transition: background var(--t), color var(--t); margin-top: 10px;
    }
    .search__location-btn:hover { background: var(--teal); color: var(--white); }

    .form-divider { display: flex; align-items: center; gap: 10px; margin: 16px 0; }
    .form-divider span { font-size: 11px; color: var(--muted); font-weight: 600; letter-spacing: .5px; white-space: nowrap; }
    .form-divider::before, .form-divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

    /* ── UPLOAD CARD ── */
    .upload-card { background: rgba(255,255,255,.15); border-radius: var(--r-lg); padding: 28px; border: 2px dashed rgba(255,255,255,.5); backdrop-filter: blur(8px); }
    .upload-card__header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
    .upload-card__badge { background: var(--accent); color: var(--dark); font-size: 10px; font-weight: 800; letter-spacing: 1px; padding: 3px 8px; border-radius: 4px; text-transform: uppercase; }
    .upload-card__label { font-size: 14px; font-weight: 600; color: var(--white); }
    .upload__dropzone { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; padding: 40px 24px; background: rgba(255,255,255,.1); border: 2px dashed rgba(255,255,255,.4); border-radius: var(--r-md); cursor: pointer; transition: all var(--t); text-align: center; }
    .upload__dropzone:hover { background: rgba(255,255,255,.22); border-color: rgba(255,255,255,.8); }
    .upload__dropzone-icon { width: 52px; height: 52px; background: rgba(255,255,255,.2); border-radius: 50%; display: flex; align-items: center; justify-content: center; }
    .upload__dropzone-text { font-size: 14px; color: rgba(255,255,255,.9); line-height: 1.5; }
    .upload__dropzone-text strong { color: var(--white); }

    /* ── STATUS BADGE ── */
    .status-badge { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 10px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; }
    .status-open       { background: #fef3c7; color: #92400e; }
    .status-resolved   { background: #d1fae5; color: #065f46; }
    .status-progress   { background: #dbeafe; color: #1e40af; }
    .status-overdue    { background: #fee2e2; color: #991b1b; }

    /* ── STATS BAR ── */
    .stats-bar { background: var(--dark2); display: flex; align-items: center; justify-content: center; gap: 60px; padding: 24px 60px; flex-wrap: wrap; }
    .stat { display: flex; flex-direction: column; align-items: center; gap: 2px; }
    .stat__number { font-family: var(--font-head); font-size: 28px; font-weight: 800; color: var(--teal); letter-spacing: -1px; }
    .stat__label { font-size: 12px; color: #9ca3af; letter-spacing: .5px; text-transform: uppercase; }
    .stats-bar__divider { width: 1px; height: 40px; background: #374151; }

    /* ── INFO SECTION ── */
    .info-section { background: var(--white); padding: 72px 60px; border-top: 1px solid var(--border); }
    .info-section__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; max-width: 1100px; margin: 0 auto; }
    .section-title { font-family: var(--font-head); font-size: 22px; font-weight: 800; color: var(--dark); margin-bottom: 28px; display: flex; align-items: center; gap: 12px; }
    .section-title::after { content: ''; flex: 1; height: 2px; background: var(--border); }

    .how-to__steps { display: flex; flex-direction: column; }
    .how-to__step { display: flex; align-items: flex-start; gap: 20px; padding: 20px 0; border-bottom: 1px solid var(--border); }
    .how-to__step:last-child { border-bottom: none; }
    .how-to__number { width: 36px; height: 36px; border-radius: 50%; background: var(--teal); color: var(--white); font-family: var(--font-head); font-size: 16px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .how-to__text { font-size: 15px; color: var(--dark); line-height: 1.6; padding-top: 6px; }

    /* ── RECENT REPORTS ── */
    .recent__list { display: flex; flex-direction: column; gap: 0; }

    .recent__item {
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      overflow: hidden;
      margin-bottom: 14px;
      background: var(--white);
      box-shadow: var(--shadow-sm);
      transition: box-shadow var(--t);
    }
    .recent__item:hover { box-shadow: var(--shadow-md); }

    .recent__item-header {
      display: flex; align-items: center; justify-content: space-between;
      gap: 12px; padding: 14px 16px;
      cursor: pointer;
      user-select: none;
    }
    .recent__item-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
    .recent__thumb { flex-shrink: 0; width: 60px; height: 46px; object-fit: cover; border-radius: var(--r-sm); background: var(--border); }
    .recent__thumb-placeholder { flex-shrink: 0; width: 60px; height: 46px; border-radius: var(--r-sm); background: var(--border); display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .recent__meta { flex: 1; min-width: 0; }
    .recent__title-text { font-size: 14px; font-weight: 600; color: var(--dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .recent__time { font-size: 11px; color: var(--muted); margin-top: 2px; }
    .recent__right { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .recent__comment-count { display: flex; align-items: center; gap: 4px; font-size: 12px; color: var(--muted); }
    .recent__comment-count svg { width: 13px; height: 13px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .recent__chevron { width: 16px; height: 16px; stroke: var(--muted); fill: none; stroke-width: 2.2; stroke-linecap: round; stroke-linejoin: round; transition: transform var(--t); }
    .recent__item.open .recent__chevron { transform: rotate(180deg); }

    /* Comments panel */
    .comments-panel { display: none; border-top: 1px solid var(--border); background: #fafafa; }
    .recent__item.open .comments-panel { display: block; }

    .comments-list { padding: 14px 16px; display: flex; flex-direction: column; gap: 10px; max-height: 260px; overflow-y: auto; }
    .comment-item { display: flex; gap: 10px; }
    .comment-avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, var(--teal), #0e8c7e); color: var(--white); font-size: 12px; font-weight: 700; font-family: var(--font-head); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .comment-body { flex: 1; min-width: 0; }
    .comment-header { display: flex; align-items: baseline; gap: 8px; margin-bottom: 3px; }
    .comment-author { font-size: 12px; font-weight: 700; color: var(--dark); }
    .comment-time { font-size: 11px; color: var(--muted); }
    .comment-text { font-size: 13.5px; color: var(--mid); line-height: 1.5; word-break: break-word; }
    .no-comments { text-align: center; padding: 20px; font-size: 13px; color: var(--muted); }

    /* Comment form */
    .comment-form { padding: 12px 16px; border-top: 1px solid var(--border); background: var(--white); }
    <?php if (!isset($_SESSION['user_id'])): ?>
    .comment-login-notice { font-size: 13px; color: var(--muted); text-align: center; padding: 10px; }
    .comment-login-notice a { color: var(--teal); font-weight: 600; }
    <?php endif; ?>
    .comment-input-row { display: flex; gap: 8px; align-items: flex-end; }
    .comment-input {
      flex: 1; padding: 9px 12px; font-size: 13.5px; font-family: var(--font-body);
      border: 1.5px solid var(--border); border-radius: var(--r-sm);
      outline: none; resize: none; min-height: 38px; max-height: 120px;
      transition: border-color var(--t);
      background: var(--cream);
    }
    .comment-input:focus { border-color: var(--teal); background: var(--white); }
    .comment-submit {
      padding: 9px 16px; background: var(--teal); color: var(--white);
      border: none; border-radius: var(--r-sm); font-size: 13px; font-weight: 600;
      cursor: pointer; transition: background var(--t); white-space: nowrap; flex-shrink: 0;
    }
    .comment-submit:hover { background: var(--teal-dark); }
    .comment-submit:disabled { opacity: .5; cursor: not-allowed; }

    /* ── CATEGORIES ── */
    .categories-section { background: var(--cream); padding: 56px 60px; border-top: 1px solid var(--border); }
    .categories-section h2 { font-family: var(--font-head); font-size: 20px; font-weight: 800; color: var(--dark); margin-bottom: 28px; text-align: center; }
    .categories-grid { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; max-width: 900px; margin: 0 auto; }
    .category-pill { display: inline-flex; align-items: center; gap: 8px; background: var(--white); border: 1.5px solid var(--border); padding: 10px 18px; border-radius: 100px; font-size: 14px; font-weight: 500; color: var(--dark); cursor: pointer; transition: all var(--t); box-shadow: var(--shadow-sm); }
    .category-pill:hover { background: var(--teal); color: var(--white); border-color: var(--teal); transform: translateY(-1px); }
    .category-pill svg { width: 16px; height: 16px; }

    /* ── AUDIENCE CARDS ── */
    .cards-section { background: var(--dark2); padding: 72px 60px; border-top: 4px solid var(--teal); }
    .cards-section__intro { text-align: center; margin-bottom: 48px; }
    .cards-section__intro h2 { font-family: var(--font-head); font-size: 32px; font-weight: 800; color: var(--white); letter-spacing: -.5px; margin-bottom: 8px; }
    .cards-section__intro p { font-size: 16px; color: #9ca3af; }
    .cards-section__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 1100px; margin: 0 auto; }
    .card { background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1); border-radius: var(--r-lg); padding: 36px 32px; display: flex; flex-direction: column; gap: 16px; transition: background var(--t), transform var(--t); }
    .card:hover { background: rgba(255,255,255,.09); transform: translateY(-3px); }
    .card__icon-wrap { width: 52px; height: 52px; background: var(--teal); border-radius: var(--r-md); display: flex; align-items: center; justify-content: center; }
    .card__icon { width: 26px; height: 26px; color: var(--white); }
    .card__title { font-family: var(--font-head); font-size: 20px; font-weight: 800; color: var(--white); }
    .card__body { font-size: 14px; line-height: 1.7; color: #9ca3af; flex: 1; }
    .card__btn { display: inline-flex; align-items: center; gap: 8px; background: transparent; border: 1.5px solid rgba(255,255,255,.25); color: var(--white); padding: 10px 20px; border-radius: var(--r-sm); font-size: 14px; font-weight: 600; align-self: flex-start; transition: all var(--t); }
    .card__btn:hover { background: var(--teal); border-color: var(--teal); }

    /* ── FOOTER ── */
    .footer { background: var(--dark); color: #9ca3af; padding: 52px 60px 32px; }
    .footer__top { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px; padding-bottom: 48px; border-bottom: 1px solid #1f2937; }
    .footer__brand { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .footer__brand-logo { width: 36px; height: 36px; background: var(--teal); border-radius: 6px; display: flex; align-items: center; justify-content: center; }
    .footer__brand-name { font-family: var(--font-head); font-size: 20px; font-weight: 800; }
    .footer__brand-name .w1 { color: var(--white); } .footer__brand-name .w2 { color: var(--teal); }
    .footer__tagline { font-size: 13px; color: #6b7280; line-height: 1.6; max-width: 260px; }
    .footer__col-title { font-family: var(--font-head); font-size: 13px; font-weight: 700; color: var(--white); text-transform: uppercase; letter-spacing: .8px; margin-bottom: 16px; }
    .footer__links { display: flex; flex-direction: column; gap: 10px; }
    .footer__links a { font-size: 13.5px; color: #6b7280; transition: color var(--t); }
    .footer__links a:hover { color: var(--white); }
    .footer__bottom { display: flex; align-items: center; justify-content: space-between; padding-top: 28px; flex-wrap: wrap; gap: 16px; }
    .footer__legal { font-size: 12.5px; color: #4b5563; }
    .footer__legal a { color: var(--teal); }
    .footer__social { display: flex; align-items: center; gap: 10px; }
    .footer__social-link { width: 36px; height: 36px; background: #1f2937; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #6b7280; transition: all var(--t); }
    .footer__social-link svg { width: 16px; height: 16px; }
    .footer__social-link:hover { background: var(--teal); color: var(--white); }

    /* FLASH */
    .flash { padding: 14px 24px; text-align: center; font-size: 14px; font-weight: 600; }
    .flash--success { background: #d1fae5; color: #065f46; }
    .flash--error   { background: #fee2e2; color: #991b1b; }

    @media (max-width: 900px) {
      .hero { padding: 56px 32px 72px; }
      .hero__grid { grid-template-columns: 1fr; gap: 28px; }
      .info-section { padding: 52px 32px; }
      .info-section__grid { grid-template-columns: 1fr; gap: 52px; }
      .categories-section { padding: 48px 32px; }
      .cards-section { padding: 52px 32px; }
      .cards-section__grid { grid-template-columns: 1fr 1fr; }
      .footer { padding: 48px 32px 28px; }
      .footer__top { grid-template-columns: 1fr 1fr; gap: 32px; }
      .stats-bar { gap: 32px; padding: 20px 32px; }
    }
    @media (max-width: 600px) {
      .banner { padding: 10px 44px 10px 16px; }
      .navbar { padding: 0 20px; }
      .hero { padding: 40px 20px 60px; }
      .cards-section__grid { grid-template-columns: 1fr; }
      .footer__top { grid-template-columns: 1fr; }
      .stats-bar { flex-direction: column; gap: 16px; }
      .stats-bar__divider { width: 40px; height: 1px; }
    }
  </style>
</head>
<body>

  <!-- BANNER -->
  <div class="banner" id="top-banner">
    Ce site permet de signaler des <strong>problèmes à Sousse</strong>.
    Il existe des sites SalahHomty <a href="#">partout en Tunisie</a> —
    ou <a href="#">créez le vôtre</a> avec la plateforme SalahHomty.
    <button class="banner__close" onclick="document.getElementById('top-banner').style.display='none'">✕</button>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar">
    <a href="#" class="navbar__brand">
      <div class="navbar__logo">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
      </div>
      <span class="navbar__brand-name">
        <span class="word--salah">Salah</span><span class="word--homty">Homty</span>
      </span>
    </a>
    <div class="navbar__spacer"></div>
    <div class="navbar__links">
      <?php if (isset($_SESSION['user_id'])): ?>
        <a href="auth/logout.php" class="btn-signin">Logout</a>
      <?php else: ?>
        <a href="user/signin.php" class="btn-signin">Sign in</a>
      <?php endif; ?>
      <a href="user/history.php">History</a>
      <a href="user/helppage.php">Help</a>
      <a href="user/profil.php">Profil</a>
    </div>
  </nav>

  <!-- FLASH -->
  <?php if (!empty($_GET['success'])): ?>
    <div class="flash flash--success">✔ Votre signalement a été envoyé avec succès. Merci !</div>
  <?php elseif (!empty($_GET['error'])): ?>
    <div class="flash flash--error">✖ <?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <main>

    <!-- ── HERO ── -->
    <section class="hero">
      <div class="hero__eyebrow">
        <span></span> Signalement communautaire — Sousse
      </div>
      <h1 class="hero__title">Signalez, visualisez et discutez des problèmes locaux</h1>
      <p class="hero__subtitle">
        Graffitis, décharges sauvages, nids-de-poule, éclairage défectueux — nous transmettons votre signalement directement à l'autorité compétente.
      </p>

      <form action="actions/submit_report.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" id="latitude"  name="latitude"  value="">
        <input type="hidden" id="longitude" name="longitude" value="">

        <div class="hero__grid">

          <!-- ── FORM CARD ── -->
          <div class="search-card">
            <p class="search-card__title">Signaler un problème</p>
            <p class="search-card__subtitle">Remplissez les informations ci-dessous et envoyez votre signalement. Un agent vous répondra dans les meilleurs délais.</p>

            <!-- Titre -->
            <div class="form-field">
              <label class="form-label">
                <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Titre du signalement *
              </label>
              <input type="text" name="title" class="form-input" placeholder="Ex : Nid-de-poule Rue de la Corniche" required maxlength="150">
            </div>

            <!-- Localisation -->
            <div class="form-field">
              <label class="form-label">
                <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                Localisation *
              </label>
              <input id="location-input" class="form-input" type="text" name="location"
                placeholder="Ex : Rue de la Corniche, Sousse" required />
            </div>

            <button class="search__location-btn" type="button" id="use-location-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg>
              Utiliser ma position actuelle
            </button>

            <div class="form-divider"><span>détails</span></div>

            <!-- Description -->
            <div class="form-field">
              <label class="form-label">
                <svg viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                Description du problème
                <span style="font-weight:400;font-size:10px;text-transform:none;letter-spacing:0;margin-left:4px">(optionnel)</span>
              </label>
              <textarea name="description" class="form-input" placeholder="Décrivez le problème en détail : depuis combien de temps, gravité, etc."></textarea>
            </div>

            <!-- Submit -->
            <button class="search__submit-btn" type="submit">
              <svg viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Envoyer le signalement
            </button>
          </div>

          <!-- ── UPLOAD CARD ── -->
          <div class="upload-card">
            <div class="upload-card__header">
              <span class="upload-card__badge">PHOTO</span>
              <span class="upload-card__label">Ajouter une photo</span>
            </div>
            <div class="upload__dropzone" id="dropzone" role="button" tabindex="0">
              <div class="upload__dropzone-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="24" height="24">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
              </div>
              <span class="upload__dropzone-text" id="dropzone-text">
                <strong>Cliquez ou glissez</strong> votre photo ici<br>
                <span style="font-size:12px;opacity:.8">JPG, PNG, WEBP — max 10 Mo</span>
              </span>
              <input id="file-input" type="file" accept="image/*" name="image" style="display:none"/>
            </div>
          </div>

        </div>
      </form>
    </section>

    <!-- ── STATS BAR ── -->
    <div class="stats-bar">
      <div class="stat"><span class="stat__number">12 480</span><span class="stat__label">Signalements</span></div>
      <div class="stats-bar__divider"></div>
      <div class="stat"><span class="stat__number">8 930</span><span class="stat__label">Résolus</span></div>
      <div class="stats-bar__divider"></div>
      <div class="stat"><span class="stat__number">24</span><span class="stat__label">Municipalités</span></div>
      <div class="stats-bar__divider"></div>
      <div class="stat"><span class="stat__number">4,8 j</span><span class="stat__label">Délai moyen</span></div>
    </div>

    <!-- ── INFO SECTION ── -->
    <section class="info-section">
      <div class="info-section__grid">

        <!-- HOW IT WORKS -->
        <div class="how-to">
          <h2 class="section-title">Comment ça marche</h2>
          <ol class="how-to__steps">
            <li class="how-to__step">
              <span class="how-to__number">1</span>
              <p class="how-to__text">Entrez un titre clair et l'adresse du problème (ou utilisez votre position GPS)</p>
            </li>
            <li class="how-to__step">
              <span class="how-to__number">2</span>
              <p class="how-to__text">Ajoutez une description et une photo pour aider les agents à mieux comprendre le problème</p>
            </li>
            <li class="how-to__step">
              <span class="how-to__number">3</span>
              <p class="how-to__text">Envoyez votre signalement — il est transmis directement à l'autorité compétente</p>
            </li>
            <li class="how-to__step">
              <span class="how-to__number">4</span>
              <p class="how-to__text">Suivez l'évolution et échangez avec la communauté via les commentaires</p>
            </li>
          </ol>
        </div>

        <!-- RECENT REPORTS WITH COMMENTS -->
        <div class="recent">
          <h2 class="section-title">Signalements récents</h2>
          <ul class="recent__list">
            <?php if (empty($recentReports)): ?>
              <li style="padding:28px 0;text-align:center;color:var(--muted);font-size:14px;">
                Aucun signalement pour le moment — soyez le premier !
              </li>
            <?php else: foreach ($recentReports as $r):
              $reportTitle = !empty($r['title'])
                ? $r['title']
                : (!empty($r['description'])
                    ? mb_substr($r['description'], 0, 50) . (mb_strlen($r['description']) > 50 ? '…' : '')
                    : 'Signalement #' . $r['id']);

              $statusClass = match(strtolower($r['status'] ?? '')) {
                'resolved'    => 'status-resolved',
                'in progress' => 'status-progress',
                'overdue'     => 'status-overdue',
                default       => 'status-open',
              };
              $hasImage = !empty($r['image']) && file_exists(__DIR__ . '/' . $r['image']);
              $comments = $commentsByReport[$r['id']] ?? [];
            ?>
              <li class="recent__item" id="report-item-<?= $r['id'] ?>">

                <!-- Header (clickable toggle) -->
                <div class="recent__item-header" onclick="toggleComments(<?= $r['id'] ?>)">
                  <div class="recent__item-left">
                    <?php if ($hasImage): ?>
                      <img class="recent__thumb" src="<?= htmlspecialchars($r['image']) ?>" alt="Photo" width="60" height="46"/>
                    <?php else: ?>
                      <div class="recent__thumb-placeholder">📷</div>
                    <?php endif; ?>
                    <div class="recent__meta">
                      <div class="recent__title-text"><?= htmlspecialchars($reportTitle) ?></div>
                      <div class="recent__time"><?= timeAgo($r['created_at']) ?> · <?= htmlspecialchars($r['user_name'] ?? 'Anonyme') ?></div>
                    </div>
                  </div>
                  <div class="recent__right">
                    <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($r['status'] ?? 'Open') ?></span>
                    <span class="recent__comment-count">
                      <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                      <?= $r['comment_count'] ?>
                    </span>
                    <svg class="recent__chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                  </div>
                </div>

                <!-- Comments panel -->
                <div class="comments-panel" id="comments-panel-<?= $r['id'] ?>">

                  <div class="comments-list" id="comments-list-<?= $r['id'] ?>">
                    <?php if (empty($comments)): ?>
                      <div class="no-comments">Aucun commentaire. Soyez le premier !</div>
                    <?php else: foreach ($comments as $cm): ?>
                      <div class="comment-item">
                        <div class="comment-avatar"><?= strtoupper(mb_substr($cm['user_name'] ?? 'A', 0, 1)) ?></div>
                        <div class="comment-body">
                          <div class="comment-header">
                            <span class="comment-author"><?= htmlspecialchars($cm['user_name'] ?? 'Anonyme') ?></span>
                            <span class="comment-time"><?= timeAgo($cm['created_at']) ?></span>
                          </div>
                          <div class="comment-text"><?= nl2br(htmlspecialchars($cm['content'])) ?></div>
                        </div>
                      </div>
                    <?php endforeach; endif; ?>
                  </div>

                  <!-- Comment form -->
                  <div class="comment-form">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                      <div class="comment-login-notice">
                        <a href="user/signin.php">Connectez-vous</a> pour laisser un commentaire.
                      </div>
                    <?php else: ?>
                      <div class="comment-input-row">
                        <textarea
                          class="comment-input"
                          id="comment-input-<?= $r['id'] ?>"
                          placeholder="Écrire un commentaire…"
                          rows="1"
                          maxlength="1000"
                        ></textarea>
                        <button class="comment-submit" onclick="submitComment(<?= $r['id'] ?>, this)">
                          Envoyer
                        </button>
                      </div>
                    <?php endif; ?>
                  </div>

                </div>
              </li>
            <?php endforeach; endif; ?>
          </ul>
        </div>

      </div>
    </section>

    <!-- ── CATEGORIES ── -->
    <section class="categories-section">
      <h2>Que pouvez-vous signaler ?</h2>
      <div class="categories-grid">
        <button class="category-pill" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>Graffiti</button>
        <button class="category-pill" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>Décharge sauvage</button>
        <button class="category-pill" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Nid-de-poule</button>
        <button class="category-pill" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/></svg>Éclairage</button>
        <button class="category-pill" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>Espaces verts</button>
        <button class="category-pill" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>Inondation</button>
        <button class="category-pill" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>Nuisances sonores</button>
        <button class="category-pill" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>Infrastructure</button>
        <button class="category-pill" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/></svg>Autre</button>
      </div>
    </section>

    <!-- ── AUDIENCE CARDS ── -->
    <section class="cards-section">
      <div class="cards-section__intro">
        <h2>SalahHomty, pour qui ?</h2>
        <p>Citoyen, municipalité ou développeur — il y a une place pour vous.</p>
      </div>
      <div class="cards-section__grid">
        <article class="card">
          <div class="card__icon-wrap"><svg class="card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
          <h3 class="card__title">Pour les citoyens</h3>
          <p class="card__body">Signalez un problème dans votre quartier, suivez son évolution et échangez avec la communauté.</p>
          <a href="#" class="card__btn">En savoir plus <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </article>
        <article class="card">
          <div class="card__icon-wrap"><svg class="card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="1"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg></div>
          <h3 class="card__title">Pour les municipalités</h3>
          <p class="card__body">Recevez, triez et répondez aux signalements. Intégrez SalahHomty à vos flux de travail existants.</p>
          <a href="#" class="card__btn">En savoir plus <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </article>
        <article class="card">
          <div class="card__icon-wrap"><svg class="card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg></div>
          <h3 class="card__title">Pour les développeurs</h3>
          <p class="card__body">Utilisez notre API ouverte pour créer vos propres intégrations et tableaux de bord personnalisés.</p>
          <a href="#" class="card__btn">Docs API <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </article>
      </div>
    </section>

  </main>

  <!-- ── FOOTER ── -->
  <footer class="footer">
    <div class="footer__top">
      <div>
        <div class="footer__brand">
          <div class="footer__brand-logo"><svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div>
          <span class="footer__brand-name"><span class="w1">Salah</span><span class="w2">Homty</span></span>
        </div>
        <p class="footer__tagline">Une plateforme communautaire pour signaler et résoudre les problèmes locaux à Sousse et au-delà.</p>
      </div>
      <div><p class="footer__col-title">Plateforme</p><div class="footer__links"><a href="#">Tous les signalements</a><a href="#">Alertes locales</a><a href="#">Signaler</a><a href="#">Statistiques</a></div></div>
      <div><p class="footer__col-title">À propos</p><div class="footer__links"><a href="#">Comment ça marche</a><a href="#">Pour les municipalités</a><a href="#">Pour les développeurs</a><a href="#">Blog</a></div></div>
      <div><p class="footer__col-title">Support</p><div class="footer__links"><a href="#">Centre d'aide</a><a href="mailto:hello@salahhomty.com">Nous contacter</a><a href="#">Confidentialité</a><a href="#">Conditions d'utilisation</a></div></div>
    </div>
    <div class="footer__bottom">
      <p class="footer__legal">© 2025 <strong style="color:#fff">SalahHomty</strong> — Développé par <strong style="color:#fff">Sharp Minds Squad</strong>. <a href="mailto:hello@sharpmindssquad.com">hello@sharpmindssquad.com</a></p>
      <div class="footer__social">
        <a href="#" class="footer__social-link"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.868-.013-1.703-2.782.604-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0 1 12 6.836a9.58 9.58 0 0 1 2.504.337c1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.202 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10z"/></svg></a>
      </div>
    </div>
  </footer>

  <script>
    // ── Drop zone ──
    const dropzone     = document.getElementById('dropzone');
    const fileInput    = document.getElementById('file-input');
    const dropzoneText = document.getElementById('dropzone-text');

    dropzone.addEventListener('click',   () => fileInput.click());
    dropzone.addEventListener('keydown', e => { if (e.key==='Enter'||e.key===' ') fileInput.click(); });
    dropzone.addEventListener('dragover',  e => { e.preventDefault(); dropzone.style.background='rgba(255,255,255,.25)'; });
    dropzone.addEventListener('dragleave', () => { dropzone.style.background=''; });
    dropzone.addEventListener('drop', e => {
      e.preventDefault(); dropzone.style.background='';
      const file = e.dataTransfer.files[0];
      if (file) { const dt = new DataTransfer(); dt.items.add(file); fileInput.files = dt.files; dropzoneText.innerHTML = `<strong>${file.name}</strong><br><span style="font-size:12px;opacity:.8">Prêt à envoyer</span>`; }
    });
    fileInput.addEventListener('change', function() {
      if (this.files.length) dropzoneText.innerHTML = `<strong>${this.files[0].name}</strong><br><span style="font-size:12px;opacity:.8">Prêt à envoyer</span>`;
    });

    // ── Geolocation ──
    const locationBtn = document.getElementById('use-location-btn');
    const latInput    = document.getElementById('latitude');
    const lngInput    = document.getElementById('longitude');
    locationBtn.addEventListener('click', () => {
      if (!navigator.geolocation) { alert('Géolocalisation non supportée.'); return; }
      locationBtn.textContent = 'Localisation…';
      navigator.geolocation.getCurrentPosition(
        pos => {
          const { latitude, longitude } = pos.coords;
          latInput.value = latitude.toFixed(6);
          lngInput.value = longitude.toFixed(6);
          document.getElementById('location-input').value = `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
          locationBtn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> Position détectée ✔`;
        },
        () => {
          alert('Impossible de récupérer votre position.');
          locationBtn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="14" height="14"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> Utiliser ma position actuelle`;
        }
      );
    });

    // ── Toggle comments panel ──
    function toggleComments(reportId) {
      const item = document.getElementById('report-item-' + reportId);
      item.classList.toggle('open');
    }

    // ── Submit comment via AJAX ──
    function submitComment(reportId, btn) {
      const input = document.getElementById('comment-input-' + reportId);
      const content = input.value.trim();
      if (!content) { input.focus(); return; }

      btn.disabled = true;
      btn.textContent = '…';

      const fd = new FormData();
      fd.append('report_id', reportId);
      fd.append('content', content);

      fetch('actions/submit_comment.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            input.value = '';
            appendComment(reportId, data.comment);
            // Update counter
            const countEl = document.querySelector(`#report-item-${reportId} .recent__comment-count`);
            if (countEl) {
              const num = parseInt(countEl.textContent) || 0;
              countEl.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>${num + 1}`;
            }
            // Remove "no comments" placeholder
            const noComment = document.querySelector(`#comments-list-${reportId} .no-comments`);
            if (noComment) noComment.remove();
          } else {
            alert(data.error || 'Erreur lors de l\'envoi.');
          }
        })
        .catch(() => alert('Erreur réseau.'))
        .finally(() => { btn.disabled = false; btn.textContent = 'Envoyer'; });
    }

    function appendComment(reportId, cm) {
      const list = document.getElementById('comments-list-' + reportId);
      const initial = (cm.user_name || 'A').charAt(0).toUpperCase();
      const div = document.createElement('div');
      div.className = 'comment-item';
      div.innerHTML = `
        <div class="comment-avatar">${initial}</div>
        <div class="comment-body">
          <div class="comment-header">
            <span class="comment-author">${escHtml(cm.user_name || 'Anonyme')}</span>
            <span class="comment-time">À l'instant</span>
          </div>
          <div class="comment-text">${escHtml(cm.content).replace(/\n/g,'<br>')}</div>
        </div>`;
      list.appendChild(div);
      list.scrollTop = list.scrollHeight;
    }

    function escHtml(str) {
      return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
  </script>

</body>
</html>