<?php
session_start();
require_once 'config/database.php';

// ── 5 most recent reports (all users) ──
$recentReports = [];
try {
    $stmt = $pdo->query("
        SELECT r.id, r.image, r.description, r.created_at, r.status,
               u.name AS user_name
        FROM reports r
        LEFT JOIN users u ON u.id = r.user_id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");
    $recentReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Fail silently — will fall back to empty list
}

// ── Helper: human-readable time ──
function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)        return 'Just now';
    if ($diff < 3600)      return floor($diff / 60) . ' min ago';
    if ($diff < 86400)     return 'Today, ' . date('H:i', strtotime($datetime));
    if ($diff < 172800)    return 'Yesterday, ' . date('H:i', strtotime($datetime));
    return date('M j, Y', strtotime($datetime));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SalahHomty – Report, view, or discuss local problems</title>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet" />
  <style>
    /* ── CSS Variables ── */
    :root {
      --teal:        #1aaa96;
      --teal-dark:   #138a79;
      --teal-light:  #e8f9f7;
      --dark:        #111827;
      --dark2:       #1f2937;
      --mid:         #374151;
      --muted:       #6b7280;
      --border:      #e5e7eb;
      --white:       #ffffff;
      --cream:       #f9fafb;
      --accent:      #f7c200;

      --font-head:   'Syne', sans-serif;
      --font-body:   'DM Sans', sans-serif;

      --r-sm: 6px;
      --r-md: 12px;
      --r-lg: 20px;
      --shadow-sm: 0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
      --shadow-md: 0 4px 16px rgba(0,0,0,.10), 0 2px 6px rgba(0,0,0,.07);
      --shadow-lg: 0 12px 40px rgba(0,0,0,.14);
      --t: 0.2s ease;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; }
    body {
      font-family: var(--font-body);
      background: var(--cream);
      color: var(--dark);
      min-height: 100vh;
      overflow-x: hidden;
    }
    a { color: inherit; text-decoration: none; }
    button { font-family: var(--font-body); cursor: pointer; }
    ul, ol { list-style: none; }
    img { display: block; }

    /* BANNER */
    .banner {
      background: var(--dark);
      color: #d1d5db;
      text-align: center;
      padding: 10px 60px;
      font-size: 13.5px;
      line-height: 1.6;
      position: relative;
      letter-spacing: 0.01em;
    }
    .banner a { color: var(--accent); text-decoration: underline; font-weight: 600; }
    .banner__close {
      position: absolute; right: 20px; top: 50%; transform: translateY(-50%);
      background: none; border: none; color: #9ca3af; font-size: 18px; line-height: 1;
      transition: color var(--t);
    }
    .banner__close:hover { color: var(--white); }

    /* NAVBAR */
    .navbar {
      background: var(--dark2);
      display: flex; align-items: center;
      height: 68px; padding: 0 40px; gap: 18px;
      position: sticky; top: 0; z-index: 100;
      box-shadow: 0 2px 12px rgba(0,0,0,.25);
    }
    .navbar__logo {
      display: flex; align-items: center; justify-content: center;
      width: 40px; height: 40px;
      background: var(--teal);
      border-radius: var(--r-sm);
      flex-shrink: 0;
    }
    .navbar__logo svg { width: 22px; height: 22px; }
    .navbar__brand { display: flex; align-items: center; gap: 10px; }
    .navbar__brand-name { font-family: var(--font-head); font-size: 22px; font-weight: 800; letter-spacing: -0.5px; }
    .word--salah { color: var(--white); }
    .word--homty { color: var(--teal); }
    .navbar__spacer { flex: 1; }
    .navbar__links { display: flex; align-items: center; gap: 4px; }
    .navbar__links .lang {
      color: #9ca3af; font-size: 13.5px; padding: 6px 12px;
      cursor: pointer; border-radius: var(--r-sm);
      font-family: var(--font-head); font-weight: 600;
      transition: color var(--t), background var(--t);
    }
    .navbar__links .lang:hover { color: var(--white); background: rgba(255,255,255,.08); }
    .navbar__links a {
      color: #9ca3af; font-size: 13.5px; padding: 6px 14px;
      border-radius: var(--r-sm); transition: color var(--t), background var(--t);
    }
    .navbar__links a:hover { color: var(--white); background: rgba(255,255,255,.08); }
    .navbar__links .btn-signin {
      background: var(--teal); color: var(--white) !important;
      font-weight: 600; border-radius: var(--r-sm);
      transition: background var(--t) !important;
    }
    .navbar__links .btn-signin:hover { background: var(--teal-dark) !important; }

    /* HERO */
    .hero {
      background: linear-gradient(135deg, var(--teal) 0%, #16c9b2 60%, #0e8c7e 100%);
      padding: 80px 60px 100px;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute; top: -80px; right: -120px;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    .hero::after {
      content: '';
      position: absolute; bottom: -60px; left: 30%;
      width: 320px; height: 320px;
      background: radial-gradient(circle, rgba(0,0,0,.07) 0%, transparent 70%);
      border-radius: 50%;
      pointer-events: none;
    }
    .hero__eyebrow {
      display: inline-flex; align-items: center; gap: 8px;
      background: rgba(255,255,255,.18);
      color: var(--white);
      font-size: 12px; font-weight: 700; letter-spacing: 1.2px;
      text-transform: uppercase; padding: 6px 14px;
      border-radius: 100px; margin-bottom: 24px;
      backdrop-filter: blur(4px);
    }
    .hero__eyebrow span { width: 6px; height: 6px; background: var(--accent); border-radius: 50%; }
    .hero__title {
      font-family: var(--font-head);
      font-size: clamp(36px, 5vw, 62px);
      font-weight: 800;
      line-height: 1.05;
      letter-spacing: -1.5px;
      color: var(--white);
      max-width: 680px;
      margin-bottom: 14px;
    }
    .hero__subtitle {
      font-size: 17px;
      color: rgba(255,255,255,.78);
      margin-bottom: 52px;
      max-width: 500px;
      line-height: 1.6;
    }
    .hero__grid {
      display: grid;
      grid-template-columns: 1.1fr 1fr;
      gap: 40px; align-items: start;
      max-width: 860px;
      position: relative; z-index: 1;
    }

    /* Search Box */
    .search-card {
      background: var(--white);
      border-radius: var(--r-lg);
      padding: 32px;
      box-shadow: var(--shadow-lg);
    }
    .search-card__title { font-family: var(--font-head); font-size: 15px; font-weight: 700; color: var(--dark); margin-bottom: 4px; }
    .search-card__hint { font-size: 13px; color: var(--muted); margin-bottom: 16px; }

    .search__row {
      display: flex; margin-bottom: 14px; border-radius: var(--r-sm);
      overflow: hidden; border: 2px solid var(--dark);
      box-shadow: var(--shadow-sm);
    }
    .search__input {
      flex: 1; padding: 13px 16px;
      font-size: 15px; font-family: var(--font-body);
      border: none; outline: none;
      background: var(--white);
      color: var(--dark);
    }
    .search__go-btn {
      background: var(--dark); color: var(--white);
      border: none; padding: 0 22px;
      font-size: 15px; font-weight: 700;
      font-family: var(--font-head); letter-spacing: 0.5px;
      transition: background var(--t);
      cursor: pointer;
    }
    .search__go-btn:hover { background: var(--teal); }

    /* Description field */
    .search__description {
      width: 100%;
      padding: 11px 14px;
      font-size: 14px; font-family: var(--font-body);
      border: 1.5px solid var(--border);
      border-radius: var(--r-sm);
      outline: none;
      color: var(--dark);
      resize: vertical;
      min-height: 72px;
      margin-bottom: 14px;
      transition: border-color var(--t);
    }
    .search__description:focus { border-color: var(--teal); }
    .search__description::placeholder { color: var(--muted); }

    .search__location-btn {
      display: inline-flex; align-items: center; gap: 8px;
      width: 100%;
      background: var(--teal-light); border: 1.5px solid var(--teal);
      padding: 10px 16px;
      font-size: 14px; font-weight: 600; color: var(--teal-dark);
      border-radius: var(--r-sm);
      transition: background var(--t), color var(--t);
    }
    .search__location-btn:hover { background: var(--teal); color: var(--white); }

    /* Upload Box */
    .upload-card {
      background: rgba(255,255,255,.15);
      border-radius: var(--r-lg);
      padding: 28px;
      border: 2px dashed rgba(255,255,255,.5);
      backdrop-filter: blur(8px);
    }
    .upload-card__header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
    .upload-card__badge {
      background: var(--accent); color: var(--dark);
      font-size: 10px; font-weight: 800; letter-spacing: 1px;
      padding: 3px 8px; border-radius: 4px; text-transform: uppercase;
    }
    .upload-card__label { font-size: 14px; font-weight: 600; color: var(--white); }
    .upload__dropzone {
      display: flex; flex-direction: column; align-items: center; justify-content: center;
      gap: 14px; padding: 40px 24px;
      background: rgba(255,255,255,.1);
      border: 2px dashed rgba(255,255,255,.4);
      border-radius: var(--r-md);
      cursor: pointer;
      transition: background var(--t), border-color var(--t);
      text-align: center;
    }
    .upload__dropzone:hover { background: rgba(255,255,255,.22); border-color: rgba(255,255,255,.8); }
    .upload__dropzone-icon {
      width: 52px; height: 52px;
      background: rgba(255,255,255,.2);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
    }
    .upload__dropzone-text { font-size: 14px; color: rgba(255,255,255,.9); line-height: 1.5; }
    .upload__dropzone-text strong { color: var(--white); }

    /* STATS BAR */
    .stats-bar {
      background: var(--dark2);
      display: flex; align-items: center; justify-content: center;
      gap: 60px; padding: 24px 60px; flex-wrap: wrap;
    }
    .stat { display: flex; flex-direction: column; align-items: center; gap: 2px; }
    .stat__number { font-family: var(--font-head); font-size: 28px; font-weight: 800; color: var(--teal); letter-spacing: -1px; }
    .stat__label { font-size: 12px; color: #9ca3af; letter-spacing: 0.5px; text-transform: uppercase; }
    .stats-bar__divider { width: 1px; height: 40px; background: #374151; }

    /* INFO SECTION */
    .info-section {
      background: var(--white);
      padding: 72px 60px;
      border-top: 1px solid var(--border);
    }
    .info-section__grid { display: grid; grid-template-columns: 1fr 1fr; gap: 80px; max-width: 1100px; margin: 0 auto; }
    .section-title {
      font-family: var(--font-head); font-size: 22px; font-weight: 800;
      color: var(--dark); margin-bottom: 28px;
      display: flex; align-items: center; gap: 12px;
    }
    .section-title::after { content: ''; flex: 1; height: 2px; background: var(--border); }

    .how-to__steps { display: flex; flex-direction: column; }
    .how-to__step { display: flex; align-items: flex-start; gap: 20px; padding: 20px 0; border-bottom: 1px solid var(--border); }
    .how-to__step:last-child { border-bottom: none; }
    .how-to__number {
      width: 36px; height: 36px; border-radius: 50%;
      background: var(--teal); color: var(--white);
      font-family: var(--font-head); font-size: 16px; font-weight: 800;
      display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    }
    .how-to__content { padding-top: 6px; }
    .how-to__text { font-size: 15px; color: var(--dark); line-height: 1.6; }

    .recent__list { display: flex; flex-direction: column; }
    .recent__item {
      display: flex; align-items: center; justify-content: space-between;
      gap: 16px; padding: 16px 0; border-bottom: 1px solid var(--border);
      transition: background var(--t);
    }
    .recent__item:last-child { border-bottom: none; }
    .recent__item:hover { padding-left: 8px; }
    .recent__info { display: flex; flex-direction: column; gap: 4px; }
    .recent__category { font-size: 11px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: var(--teal); }
    .recent__title { font-size: 15px; font-weight: 600; color: var(--dark); transition: color var(--t); }
    .recent__title:hover { color: var(--teal); }
    .recent__time { font-size: 12px; color: var(--muted); font-style: italic; }
    .recent__thumb { flex-shrink: 0; width: 72px; height: 54px; object-fit: cover; border-radius: var(--r-sm); background: var(--border); }

    /* CATEGORIES */
    .categories-section { background: var(--cream); padding: 56px 60px; border-top: 1px solid var(--border); }
    .categories-section h2 { font-family: var(--font-head); font-size: 20px; font-weight: 800; color: var(--dark); margin-bottom: 28px; text-align: center; }
    .categories-grid { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; max-width: 900px; margin: 0 auto; }
    .category-pill {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--white); border: 1.5px solid var(--border);
      padding: 10px 18px; border-radius: 100px;
      font-size: 14px; font-weight: 500; color: var(--dark);
      cursor: pointer; transition: all var(--t); box-shadow: var(--shadow-sm);
    }
    .category-pill:hover { background: var(--teal); color: var(--white); border-color: var(--teal); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(26,170,150,.3); }
    .category-pill svg { width: 16px; height: 16px; flex-shrink: 0; }

    /* AUDIENCE CARDS */
    .cards-section { background: var(--dark2); padding: 72px 60px; border-top: 4px solid var(--teal); }
    .cards-section__intro { text-align: center; margin-bottom: 48px; }
    .cards-section__intro h2 { font-family: var(--font-head); font-size: 32px; font-weight: 800; color: var(--white); letter-spacing: -0.5px; margin-bottom: 8px; }
    .cards-section__intro p { font-size: 16px; color: #9ca3af; }
    .cards-section__grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 1100px; margin: 0 auto; }
    .card {
      background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.1);
      border-radius: var(--r-lg); padding: 36px 32px;
      display: flex; flex-direction: column; gap: 16px;
      transition: background var(--t), transform var(--t);
    }
    .card:hover { background: rgba(255,255,255,.09); transform: translateY(-3px); }
    .card__icon-wrap { width: 52px; height: 52px; background: var(--teal); border-radius: var(--r-md); display: flex; align-items: center; justify-content: center; }
    .card__icon { width: 26px; height: 26px; color: var(--white); }
    .card__title { font-family: var(--font-head); font-size: 20px; font-weight: 800; color: var(--white); }
    .card__body { font-size: 14px; line-height: 1.7; color: #9ca3af; flex: 1; }
    .card__btn {
      display: inline-flex; align-items: center; gap: 8px;
      background: transparent; border: 1.5px solid rgba(255,255,255,.25);
      color: var(--white); padding: 10px 20px;
      border-radius: var(--r-sm); font-size: 14px; font-weight: 600;
      align-self: flex-start; transition: all var(--t);
    }
    .card__btn:hover { background: var(--teal); border-color: var(--teal); }
    .card__btn svg { width: 14px; height: 14px; }

    /* FOOTER */
    .footer { background: var(--dark); color: #9ca3af; padding: 52px 60px 32px; }
    .footer__top { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px; padding-bottom: 48px; border-bottom: 1px solid #1f2937; }
    .footer__brand { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
    .footer__brand-logo { width: 36px; height: 36px; background: var(--teal); border-radius: 6px; display: flex; align-items: center; justify-content: center; }
    .footer__brand-logo svg { width: 20px; height: 20px; }
    .footer__brand-name { font-family: var(--font-head); font-size: 20px; font-weight: 800; }
    .footer__brand-name .w1 { color: var(--white); }
    .footer__brand-name .w2 { color: var(--teal); }
    .footer__tagline { font-size: 13px; color: #6b7280; line-height: 1.6; max-width: 260px; }
    .footer__col-title { font-family: var(--font-head); font-size: 13px; font-weight: 700; color: var(--white); text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 16px; }
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

    /* FLASH MESSAGE */
    .flash {
      padding: 14px 24px; text-align: center; font-size: 14px; font-weight: 600;
    }
    .flash--success { background: #d1fae5; color: #065f46; }
    .flash--error   { background: #fee2e2; color: #991b1b; }

    /* RESPONSIVE */
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
      .navbar__links a:not(.btn-signin):not([href="#"]) { display: none; }
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
    This site is for reporting <strong>problems in Sousse</strong>.
    There are SalahHomty sites <a href="#">all over Tunisia</a> —
    or <a href="#">set up your own</a> using the SalahHomty Platform.
    <button class="banner__close" aria-label="Close"
      onclick="document.getElementById('top-banner').style.display='none'">✕</button>
  </div>

  <!-- NAVBAR -->
  <nav class="navbar" aria-label="Main navigation">
    <a href="#" class="navbar__brand">
      <div class="navbar__logo" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2"
             stroke-linecap="round" stroke-linejoin="round">
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
        <a href="pages/signin.php" class="btn-signin">Sign in</a>
      <?php endif; ?>
      <a href="pages/history.php">History</a>
      <a href="pages/helppage.php">Help</a>
    </div>
  </nav>

  <!-- FLASH MESSAGE -->
  <?php if (!empty($_GET['success'])): ?>
    <div class="flash flash--success">✔ Your report was submitted successfully. Thank you!</div>
  <?php elseif (!empty($_GET['error'])): ?>
    <div class="flash flash--error">✖ <?= htmlspecialchars($_GET['error']) ?></div>
  <?php endif; ?>

  <main>

    <!-- HERO -->
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero__eyebrow">
        <span></span> Community Issue Reporting — Sousse
      </div>
      <h1 class="hero__title" id="hero-title">
        Report, view, or discuss local problems
      </h1>
      <p class="hero__subtitle">
        Graffiti, fly tipping, potholes, broken paving, street lighting — we forward your report straight to the right authority.
      </p>

      <!-- REPORT FORM -->
      <form action="actions/submit_report.php" method="POST" enctype="multipart/form-data">

        <!-- Hidden lat/lng populated by JS -->
        <input type="hidden" id="latitude"  name="latitude"  value="">
        <input type="hidden" id="longitude" name="longitude" value="">

        <div class="hero__grid">

          <!-- Search / Location card -->
          <div class="search-card">
            <p class="search-card__title">Find your location</p>
            <p class="search-card__hint">Enter a nearby postcode or street name and area</p>

            <div class="search__row">
              <input id="location-input" class="search__input" type="text" name="location"
                placeholder="e.g. Rue de la Corniche, Sousse"
                aria-label="Enter location" />
              <!-- Go = submit the form -->
              <button class="search__go-btn" type="submit">Go</button>
            </div>

            <textarea
              class="search__description"
              name="description"
              placeholder="Describe the problem (optional)"
              aria-label="Description of the problem"
            ></textarea>

            <button class="search__location-btn" type="button" id="use-location-btn">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                   stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                <polygon points="3 11 22 2 13 21 11 13 3 11"/>
              </svg>
              Use my current location
            </button>
          </div>

          <!-- Upload card -->
          <div class="upload-card">
            <div class="upload-card__header">
              <span class="upload-card__badge">NEW</span>
              <span class="upload-card__label">Start a report with a photo</span>
            </div>
            <div class="upload__dropzone" id="dropzone" role="button" tabindex="0"
                 aria-label="Upload a photo to start a report">
              <div class="upload__dropzone-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" width="24" height="24">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                  <polyline points="17 8 12 3 7 8"/>
                  <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
              </div>
              <span class="upload__dropzone-text" id="dropzone-text">
                <strong>Browse or drag</strong> your photo here<br>
                <span style="font-size:12px;opacity:.8">JPG, PNG, WEBP — up to 10 MB</span>
              </span>
              <input id="file-input" type="file" accept="image/*" name="image" style="display:none" aria-hidden="true"/>
            </div>
          </div>

        </div>
      </form>
    </section>

    <!-- STATS BAR -->
    <div class="stats-bar">
      <div class="stat">
        <span class="stat__number">12,480</span>
        <span class="stat__label">Reports filed</span>
      </div>
      <div class="stats-bar__divider" aria-hidden="true"></div>
      <div class="stat">
        <span class="stat__number">8,930</span>
        <span class="stat__label">Issues resolved</span>
      </div>
      <div class="stats-bar__divider" aria-hidden="true"></div>
      <div class="stat">
        <span class="stat__number">24</span>
        <span class="stat__label">Councils connected</span>
      </div>
      <div class="stats-bar__divider" aria-hidden="true"></div>
      <div class="stat">
        <span class="stat__number">4.8 days</span>
        <span class="stat__label">Avg. response time</span>
      </div>
    </div>

    <!-- INFO SECTION -->
    <section class="info-section" aria-label="How to report and recent problems">
      <div class="info-section__grid">
        <div class="how-to">
          <h2 class="section-title">How it works</h2>
          <ol class="how-to__steps">
            <li class="how-to__step">
              <span class="how-to__number" aria-hidden="true">1</span>
              <div class="how-to__content">
                <p class="how-to__text">Enter a nearby postcode, or street name and area</p>
              </div>
            </li>
            <li class="how-to__step">
              <span class="how-to__number" aria-hidden="true">2</span>
              <div class="how-to__content">
                <p class="how-to__text">Locate the problem on an interactive map of the area</p>
              </div>
            </li>
            <li class="how-to__step">
              <span class="how-to__number" aria-hidden="true">3</span>
              <div class="how-to__content">
                <p class="how-to__text">Describe the problem and add photos if you have them</p>
              </div>
            </li>
            <li class="how-to__step">
              <span class="how-to__number" aria-hidden="true">4</span>
              <div class="how-to__content">
                <p class="how-to__text">We send your report directly to the right council or authority</p>
              </div>
            </li>
          </ol>
        </div>

        <div class="recent">
          <h2 class="section-title">Recent reports</h2>
          <ul class="recent__list" aria-label="Recent problem reports">
            <?php if (empty($recentReports)): ?>
              <li style="padding:28px 0; text-align:center; color:var(--muted); font-size:14px;">
                No reports yet — be the first to report a problem!
              </li>
            <?php else: foreach ($recentReports as $r):
              // Description: truncate to ~50 chars for display
              $desc = !empty($r['description'])
                ? (mb_strlen($r['description']) > 50
                    ? mb_substr($r['description'], 0, 50) . '…'
                    : $r['description'])
                : 'Report #' . $r['id'];

              // Thumbnail
              $hasImage = !empty($r['image']) && file_exists(__DIR__ . '/' . $r['image']);
            ?>
              <li class="recent__item">
                <div class="recent__info">
                  <span class="recent__category">
                    <?= htmlspecialchars($r['status'] ?? 'Open') ?>
                  </span>
                  <a href="pages/history.php" class="recent__title">
                    <?= htmlspecialchars($desc) ?>
                  </a>
                  <time class="recent__time" datetime="<?= htmlspecialchars($r['created_at']) ?>">
                    <?= timeAgo($r['created_at']) ?>
                  </time>
                </div>
                <?php if ($hasImage): ?>
                  <img class="recent__thumb"
                       src="<?= htmlspecialchars($r['image']) ?>"
                       alt="Report photo"
                       width="72" height="54" />
                <?php else: ?>
                  <div class="recent__thumb"
                       style="display:flex;align-items:center;justify-content:center;
                              background:var(--border);border-radius:var(--r-sm);
                              width:72px;height:54px;font-size:22px;">
                    📷
                  </div>
                <?php endif; ?>
              </li>
            <?php endforeach; endif; ?>
          </ul>
        </div>
      </div>
    </section>

    <!-- CATEGORIES -->
    <section class="categories-section" aria-label="Report categories">
      <h2>What can you report?</h2>
      <div class="categories-grid">
        <button class="category-pill" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Graffiti
        </button>
        <button class="category-pill" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Fly Tipping
        </button>
        <button class="category-pill" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
          Potholes
        </button>
        <button class="category-pill" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/></svg>
          Street Lighting
        </button>
        <button class="category-pill" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          Parks & Green Spaces
        </button>
        <button class="category-pill" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          Flooding & Drainage
        </button>
        <button class="category-pill" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
          Broken Infrastructure
        </button>
        <button class="category-pill" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          Noise Complaints
        </button>
        <button class="category-pill" type="button">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 11a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.6 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          Other
        </button>
      </div>
    </section>

    <!-- AUDIENCE CARDS -->
    <section class="cards-section" aria-label="Who SalahHomty is for">
      <div class="cards-section__intro">
        <h2>Who is SalahHomty for?</h2>
        <p>Whether you're a resident, a local authority, or a developer — there's a place for you.</p>
      </div>
      <div class="cards-section__grid">
        <article class="card">
          <div class="card__icon-wrap">
            <svg class="card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
          </div>
          <h3 class="card__title">For citizens</h3>
          <p class="card__body">Need to report a problem in your local area? Learn all about SalahHomty, how it works, and what happens to your report once you've made it.</p>
          <a href="#" class="card__btn">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </article>
        <article class="card">
          <div class="card__icon-wrap">
            <svg class="card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="2" y="7" width="20" height="14" rx="1"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/><path d="M2 10h20"/>
            </svg>
          </div>
          <h3 class="card__title">For councils</h3>
          <p class="card__body">Discover how SalahHomty helps you receive, triage, and respond to community reports. Integrate with your existing workflows and improve local service delivery.</p>
          <a href="#" class="card__btn">Learn more <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </article>
        <article class="card">
          <div class="card__icon-wrap">
            <svg class="card__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/>
            </svg>
          </div>
          <h3 class="card__title">For developers</h3>
          <p class="card__body">Use our open API to build your own integrations, custom dashboards, or add SalahHomty reporting capabilities to your own applications and platforms.</p>
          <a href="#" class="card__btn">View API docs <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
        </article>
      </div>
    </section>

  </main>

  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer__top">
      <div class="footer__brand-wrap">
        <div class="footer__brand">
          <div class="footer__brand-logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
          </div>
          <span class="footer__brand-name"><span class="w1">Salah</span><span class="w2">Homty</span></span>
        </div>
        <p class="footer__tagline">A community-powered platform for reporting and resolving local problems in Sousse and beyond.</p>
      </div>
      <div>
        <p class="footer__col-title">Platform</p>
        <div class="footer__links">
          <a href="#">All reports</a><a href="#">Local alerts</a>
          <a href="#">Report a problem</a><a href="#">Statistics</a>
        </div>
      </div>
      <div>
        <p class="footer__col-title">About</p>
        <div class="footer__links">
          <a href="#">How it works</a><a href="#">For councils</a>
          <a href="#">For developers</a><a href="#">Blog</a>
        </div>
      </div>
      <div>
        <p class="footer__col-title">Support</p>
        <div class="footer__links">
          <a href="#">Help centre</a><a href="mailto:hello@salahhomty.com">Contact us</a>
          <a href="#">Privacy policy</a><a href="#">Terms of use</a>
        </div>
      </div>
    </div>
    <div class="footer__bottom">
      <p class="footer__legal">
        © 2025 <strong style="color:#fff">SalahHomty</strong> — Built by <strong style="color:#fff">Sharp Minds Squad</strong>.
        Questions? <a href="mailto:hello@sharpmindssquad.com">hello@sharpmindssquad.com</a>
      </p>
      <div class="footer__social" aria-label="Social links">
        <a href="#" class="footer__social-link" aria-label="GitHub">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.868-.013-1.703-2.782.604-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0 1 12 6.836a9.58 9.58 0 0 1 2.504.337c1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.202 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10z"/></svg>
        </a>
        <a href="#" class="footer__social-link" aria-label="X (Twitter)">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
        </a>
        <a href="#" class="footer__social-link" aria-label="Facebook">
          <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073c0 6.027 4.388 11.024 10.125 11.927v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.887v2.267h3.328l-.532 3.49h-2.796V24C19.612 23.097 24 18.1 24 12.073z"/></svg>
        </a>
      </div>
    </div>
  </footer>

  <script>
    /* ── Drop zone ── */
    const dropzone     = document.getElementById('dropzone');
    const fileInput    = document.getElementById('file-input');
    const dropzoneText = document.getElementById('dropzone-text');

    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') fileInput.click(); });
    dropzone.addEventListener('dragover',  e => { e.preventDefault(); dropzone.style.background = 'rgba(255,255,255,.25)'; });
    dropzone.addEventListener('dragleave', ()  => { dropzone.style.background = ''; });
    dropzone.addEventListener('drop', e => {
      e.preventDefault(); dropzone.style.background = '';
      const file = e.dataTransfer.files[0];
      if (file) {
        // Transfer dragged file to the real input
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        dropzoneText.innerHTML = `<strong>${file.name}</strong><br><span style="font-size:12px;opacity:.8">Ready to upload</span>`;
      }
    });
    fileInput.addEventListener('change', function () {
      if (this.files.length)
        dropzoneText.innerHTML = `<strong>${this.files[0].name}</strong><br><span style="font-size:12px;opacity:.8">Ready to upload</span>`;
    });

    /* ── Geolocation ── */
    const locationBtn = document.getElementById('use-location-btn');
    const latInput    = document.getElementById('latitude');
    const lngInput    = document.getElementById('longitude');

    locationBtn.addEventListener('click', () => {
      if (!navigator.geolocation) { alert('Geolocation not supported.'); return; }
      locationBtn.textContent = 'Locating…';
      navigator.geolocation.getCurrentPosition(
        pos => {
          const { latitude, longitude } = pos.coords;
          latInput.value = latitude.toFixed(6);
          lngInput.value = longitude.toFixed(6);
          document.getElementById('location-input').value = `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
          locationBtn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> Location detected ✔`;
        },
        () => {
          alert('Could not retrieve location.');
          locationBtn.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><polygon points="3 11 22 2 13 21 11 13 3 11"/></svg> Use my current location`;
        }
      );
    });
  </script>

</body>
</html>