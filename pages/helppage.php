<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SalahHomty – Aide</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&family=Syne:wght@700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --teal: #1abc9c;
      --teal-dark: #17a589;
      --teal-light: #e8faf6;
      --teal-mid: #a8e8d8;
      --navy: #1e1e1e;
      --white: #ffffff;
      --gray-50: #f8f9fa;
      --gray-100: #f1f3f4;
      --gray-200: #e2e6ea;
      --gray-400: #9aa3ae;
      --gray-600: #6c757d;
      --gray-800: #343a40;
      --shadow-sm: 0 1px 4px rgba(0,0,0,.07);
      --shadow-md: 0 4px 20px rgba(0,0,0,.10);
      --shadow-teal: 0 4px 20px rgba(26,188,156,.18);
      --radius: 12px;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--gray-50);
      color: var(--navy);
      min-height: 100vh;
    }

    /* ───── NAVBAR ───── */
    nav {
      background: var(--navy);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px;
      height: 56px;
      position: sticky;
      top: 0;
      z-index: 200;
    }
    .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
    .logo-icon {
      width: 32px; height: 32px;
      background: var(--teal); border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Syne', sans-serif; font-size: 13px; font-weight: 800; color: #fff;
    }
    .logo-text { font-family: 'Syne', sans-serif; font-size: 18px; font-weight: 800; color: #fff; }
    .logo-text span { color: var(--teal); }
    .nav-links { display: flex; align-items: center; gap: 6px; }
    .nav-links a {
      color: var(--gray-400); text-decoration: none;
      font-size: 14px; font-weight: 500;
      padding: 6px 14px; border-radius: 6px;
      transition: color .2s, background .2s;
    }
    .nav-links a:hover { color: #fff; background: rgba(255,255,255,.07); }
    .nav-links a.active { color: #fff; background: var(--teal); }
    .btn-report {
      background: var(--teal) !important; color: #fff !important;
      font-weight: 600 !important; padding: 8px 18px !important; border-radius: 7px !important;
    }
    .btn-report:hover { background: var(--teal-dark) !important; }

    /* ───── HERO ───── */
    .hero {
      background: linear-gradient(135deg, #1e1e1e 0%, #1e3a34 60%, #1abc9c22 100%);
      padding: 64px 24px 56px;
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute; inset: 0;
      background: radial-gradient(ellipse 60% 50% at 50% 110%, rgba(26,188,156,.22) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-chip {
      display: inline-flex; align-items: center; gap: 7px;
      background: rgba(26,188,156,.15); border: 1px solid rgba(26,188,156,.3);
      color: var(--teal); font-size: 12px; font-weight: 700;
      padding: 5px 14px; border-radius: 20px; margin-bottom: 20px;
      letter-spacing: .5px; text-transform: uppercase;
    }
    .hero h1 {
      font-family: 'Syne', sans-serif;
      font-size: clamp(28px, 5vw, 46px);
      font-weight: 800; color: #fff;
      line-height: 1.15; margin-bottom: 14px;
    }
    .hero h1 span { color: var(--teal); }
    .hero p {
      font-size: 16px; color: rgba(255,255,255,.65);
      max-width: 520px; margin: 0 auto 32px;
      line-height: 1.65;
    }

    /* search bar */
    .hero-search {
      max-width: 560px; margin: 0 auto;
      display: flex; gap: 0;
      background: #fff; border-radius: 10px;
      box-shadow: var(--shadow-teal);
      overflow: hidden;
    }
    .hero-search svg {
      flex-shrink: 0; margin: auto 14px; color: var(--gray-400);
    }
    .hero-search input {
      flex: 1; border: none; outline: none;
      font-family: 'DM Sans', sans-serif; font-size: 15px;
      padding: 14px 0; color: var(--navy);
      background: transparent;
    }
    .hero-search input::placeholder { color: var(--gray-400); }
    .hero-search button {
      background: var(--teal); color: #fff;
      border: none; padding: 0 24px;
      font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 700;
      cursor: pointer; transition: background .2s;
    }
    .hero-search button:hover { background: var(--teal-dark); }

    .hero-tags {
      display: flex; flex-wrap: wrap; gap: 8px;
      justify-content: center; margin-top: 18px;
    }
    .hero-tags button {
      background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.15);
      color: rgba(255,255,255,.75); font-family: 'DM Sans', sans-serif;
      font-size: 12.5px; padding: 5px 13px; border-radius: 20px;
      cursor: pointer; transition: background .2s, color .2s;
    }
    .hero-tags button:hover { background: var(--teal); color: #fff; border-color: var(--teal); }

    /* ───── LAYOUT ───── */
    .page {
      max-width: 1100px; margin: 0 auto;
      padding: 48px 24px 80px;
    }

    /* ───── SECTION TITLES ───── */
    .section-title {
      font-family: 'Syne', sans-serif;
      font-size: 22px; font-weight: 800;
      color: var(--navy); margin-bottom: 20px;
      display: flex; align-items: center; gap: 10px;
    }
    .section-title::after {
      content: ''; flex: 1; height: 2px;
      background: var(--gray-200); border-radius: 2px;
    }

    /* ───── QUICK ACTIONS ───── */
    .quick-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px; margin-bottom: 52px;
    }
    .quick-card {
      background: var(--white);
      border-radius: var(--radius);
      border: 1.5px solid var(--gray-200);
      padding: 22px 18px;
      display: flex; flex-direction: column;
      align-items: flex-start; gap: 10px;
      cursor: pointer; text-decoration: none;
      transition: transform .18s, box-shadow .18s, border-color .18s;
    }
    .quick-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-teal);
      border-color: var(--teal);
    }
    .quick-icon {
      width: 44px; height: 44px;
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
    }
    .qi-teal   { background: var(--teal-light); }
    .qi-orange { background: #fef3e2; }
    .qi-blue   { background: #eaf4fd; }
    .qi-purple { background: #f5eefa; }
    .quick-card h3 { font-size: 14px; font-weight: 700; color: var(--navy); margin: 0; }
    .quick-card p  { font-size: 12.5px; color: var(--gray-600); line-height: 1.5; }

    /* ───── FAQ ───── */
    .faq-layout {
      display: grid;
      grid-template-columns: 220px 1fr;
      gap: 32px; margin-bottom: 52px;
    }

    /* sidebar categories */
    .faq-sidebar { position: sticky; top: 76px; height: fit-content; }
    .faq-sidebar h4 {
      font-size: 11px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .7px; color: var(--gray-400); margin-bottom: 10px;
    }
    .faq-cat {
      display: flex; align-items: center; gap: 9px;
      padding: 9px 12px; border-radius: 8px;
      font-size: 13.5px; font-weight: 500; color: var(--gray-800);
      cursor: pointer; transition: background .15s, color .15s;
      margin-bottom: 2px;
    }
    .faq-cat:hover { background: var(--gray-100); color: var(--navy); }
    .faq-cat.active { background: var(--teal-light); color: var(--teal-dark); font-weight: 700; }
    .faq-cat .badge {
      margin-left: auto; background: var(--gray-200); color: var(--gray-600);
      font-size: 11px; font-weight: 700; padding: 1px 7px; border-radius: 10px;
    }
    .faq-cat.active .badge { background: var(--teal-mid); color: var(--teal-dark); }

    /* accordion */
    .faq-list { display: flex; flex-direction: column; gap: 10px; }

    .faq-section { display: none; }
    .faq-section.visible { display: flex; flex-direction: column; gap: 10px; }

    .faq-item {
      background: var(--white);
      border: 1.5px solid var(--gray-200);
      border-radius: var(--radius);
      overflow: hidden;
      transition: border-color .2s;
    }
    .faq-item.open { border-color: var(--teal); }

    .faq-q {
      display: flex; align-items: center; justify-content: space-between;
      padding: 16px 18px; cursor: pointer;
      font-size: 14.5px; font-weight: 600; color: var(--navy);
      gap: 12px; user-select: none;
    }
    .faq-q:hover { background: var(--gray-50); }
    .faq-item.open .faq-q { background: var(--teal-light); color: var(--teal-dark); }

    .faq-chevron {
      flex-shrink: 0; width: 20px; height: 20px;
      border-radius: 50%; background: var(--gray-100);
      display: flex; align-items: center; justify-content: center;
      transition: transform .25s, background .2s;
    }
    .faq-item.open .faq-chevron {
      transform: rotate(180deg);
      background: var(--teal); color: #fff;
    }
    .faq-a {
      max-height: 0; overflow: hidden;
      transition: max-height .35s ease, padding .25s ease;
      padding: 0 18px;
    }
    .faq-a-inner {
      padding: 14px 0;
      font-size: 14px; color: var(--gray-800); line-height: 1.7;
      border-top: 1px solid var(--gray-100);
    }
    .faq-a-inner ul { padding-left: 20px; margin-top: 8px; }
    .faq-a-inner li { margin-bottom: 5px; }
    .faq-a-inner a { color: var(--teal-dark); text-decoration: underline; }
    .faq-item.open .faq-a { max-height: 600px; padding: 0 18px; }

    /* ───── VIDEO GUIDES ───── */
    .videos-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px; margin-bottom: 52px;
    }
    .video-card {
      background: var(--white); border-radius: var(--radius);
      border: 1.5px solid var(--gray-200);
      overflow: hidden;
      transition: transform .18s, box-shadow .18s;
      cursor: pointer;
    }
    .video-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); }
    .video-thumb {
      width: 100%; aspect-ratio: 16/9;
      background: linear-gradient(135deg, #1e2e2b 0%, #1abc9c33 100%);
      display: flex; align-items: center; justify-content: center;
      position: relative;
    }
    .video-thumb .emo { font-size: 36px; }
    .play-btn {
      position: absolute; inset: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .play-btn span {
      width: 48px; height: 48px; border-radius: 50%;
      background: rgba(255,255,255,.15); backdrop-filter: blur(4px);
      border: 2px solid rgba(255,255,255,.4);
      display: flex; align-items: center; justify-content: center;
      transition: background .2s;
    }
    .video-card:hover .play-btn span { background: var(--teal); border-color: var(--teal); }
    .video-info { padding: 14px 16px; }
    .video-info h4 { font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 4px; }
    .video-info p  { font-size: 12px; color: var(--gray-600); }
    .video-dur {
      display: inline-block; margin-top: 8px;
      background: var(--gray-100); color: var(--gray-600);
      font-size: 11.5px; font-weight: 600; padding: 2px 9px; border-radius: 8px;
    }

    /* ───── CONTACT CARDS ───── */
    .contact-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px; margin-bottom: 52px;
    }
    .contact-card {
      background: var(--white); border-radius: var(--radius);
      border: 1.5px solid var(--gray-200);
      padding: 26px 22px;
      display: flex; flex-direction: column; gap: 10px;
      transition: border-color .2s, box-shadow .2s;
    }
    .contact-card:hover { border-color: var(--teal); box-shadow: var(--shadow-teal); }
    .contact-icon {
      width: 48px; height: 48px; border-radius: 12px;
      background: var(--teal-light);
      display: flex; align-items: center; justify-content: center;
      font-size: 22px;
    }
    .contact-card h3 { font-size: 15px; font-weight: 700; color: var(--navy); }
    .contact-card p  { font-size: 13px; color: var(--gray-600); line-height: 1.55; flex: 1; }
    .contact-badge {
      display: inline-flex; align-items: center; gap: 5px;
      font-size: 11.5px; font-weight: 700;
      padding: 3px 10px; border-radius: 20px;
    }
    .cb-green  { background: #eafaf1; color: #27ae60; }
    .cb-orange { background: #fef3e2; color: #e67e22; }
    .cb-blue   { background: #eaf4fd; color: #2980b9; }
    .contact-btn {
      display: block; text-align: center;
      background: var(--navy); color: #fff;
      font-family: 'DM Sans', sans-serif; font-size: 13.5px; font-weight: 700;
      padding: 10px 16px; border-radius: 8px; text-decoration: none;
      border: none; cursor: pointer;
      transition: background .2s;
    }
    .contact-btn:hover { background: var(--teal); }
    .contact-btn.outline {
      background: transparent; border: 1.5px solid var(--gray-200); color: var(--gray-800);
    }
    .contact-btn.outline:hover { background: var(--teal-light); border-color: var(--teal); color: var(--teal-dark); }

    /* ───── CONTACT FORM ───── */
    .form-section {
      background: var(--white); border-radius: var(--radius);
      border: 1.5px solid var(--gray-200);
      padding: 32px; margin-bottom: 52px;
      display: grid; grid-template-columns: 1fr 1fr; gap: 32px;
    }
    .form-left h3 {
      font-family: 'Syne', sans-serif;
      font-size: 20px; font-weight: 800; color: var(--navy); margin-bottom: 8px;
    }
    .form-left p { font-size: 14px; color: var(--gray-600); line-height: 1.6; margin-bottom: 20px; }
    .form-info-item {
      display: flex; align-items: flex-start; gap: 12px;
      margin-bottom: 14px;
    }
    .form-info-item .ico {
      width: 36px; height: 36px; border-radius: 8px;
      background: var(--teal-light);
      display: flex; align-items: center; justify-content: center;
      font-size: 16px; flex-shrink: 0;
    }
    .form-info-item h5 { font-size: 13px; font-weight: 700; color: var(--navy); }
    .form-info-item p  { font-size: 12.5px; color: var(--gray-600); margin-top: 2px; }

    .form-right { display: flex; flex-direction: column; gap: 14px; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .field { display: flex; flex-direction: column; gap: 5px; }
    .field label { font-size: 12.5px; font-weight: 700; color: var(--gray-800); }
    .field input, .field select, .field textarea {
      padding: 10px 13px;
      border: 1.5px solid var(--gray-200);
      border-radius: 8px;
      font-family: 'DM Sans', sans-serif; font-size: 14px; color: var(--navy);
      background: var(--white); outline: none;
      transition: border-color .2s;
    }
    .field input:focus, .field select:focus, .field textarea:focus { border-color: var(--teal); }
    .field textarea { resize: vertical; min-height: 110px; }
    .submit-btn {
      background: var(--teal); color: #fff; border: none;
      font-family: 'DM Sans', sans-serif; font-size: 14.5px; font-weight: 700;
      padding: 12px 24px; border-radius: 9px;
      cursor: pointer; transition: background .2s, transform .15s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .submit-btn:hover { background: var(--teal-dark); transform: translateY(-1px); }

    /* ───── STATUS BANNER ───── */
    .status-banner {
      display: flex; align-items: center; justify-content: space-between;
      background: #eafaf1; border: 1.5px solid #a9dfbf;
      border-radius: var(--radius); padding: 16px 22px;
      margin-bottom: 52px; flex-wrap: wrap; gap: 12px;
    }
    .status-left { display: flex; align-items: center; gap: 12px; }
    .status-dot { width: 10px; height: 10px; border-radius: 50%; background: #27ae60; flex-shrink: 0;
      animation: pulse2 2s infinite; }
    @keyframes pulse2 { 0%,100%{box-shadow:0 0 0 0 rgba(39,174,96,.4)} 50%{box-shadow:0 0 0 6px rgba(39,174,96,0)} }
    .status-banner h4 { font-size: 14px; font-weight: 700; color: #1a7a40; }
    .status-banner p  { font-size: 12.5px; color: #27ae60; }
    .status-right a {
      font-size: 13px; font-weight: 700; color: #1a7a40;
      text-decoration: underline;
    }

    /* ───── FOOTER ───── */
    footer {
      background: var(--navy); padding: 32px 24px;
      text-align: center;
    }
    footer p { font-size: 13px; color: var(--gray-400); }
    footer a { color: var(--teal); text-decoration: none; }

    /* ───── TOAST ───── */
    .toast {
      position: fixed; bottom: 28px; right: 28px;
      background: var(--navy); color: #fff;
      font-size: 14px; font-weight: 600;
      padding: 13px 22px; border-radius: 10px;
      box-shadow: var(--shadow-md);
      display: flex; align-items: center; gap: 9px;
      transform: translateY(80px); opacity: 0;
      transition: transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s;
      z-index: 999;
    }
    .toast.show { transform: translateY(0); opacity: 1; }

    /* ───── RESPONSIVE ───── */
    @media (max-width: 900px) {
      .quick-grid { grid-template-columns: repeat(2, 1fr); }
      .faq-layout { grid-template-columns: 1fr; }
      .faq-sidebar { position: static; display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
      .faq-sidebar h4 { display: none; }
      .faq-cat { padding: 6px 12px; border: 1.5px solid var(--gray-200); border-radius: 20px; margin: 0; }
      .faq-cat.active { border-color: var(--teal); }
      .videos-grid { grid-template-columns: repeat(2, 1fr); }
      .contact-grid { grid-template-columns: 1fr; }
      .form-section { grid-template-columns: 1fr; }
      .form-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 600px) {
      .quick-grid { grid-template-columns: 1fr; }
      .videos-grid { grid-template-columns: 1fr; }
      nav { padding: 0 16px; }
      .nav-links a:not(.btn-report):not(.active) { display: none; }
    }
  </style>
</head>
<body>

<!-- ── NAVBAR ── -->
<nav>
  <a href="#" class="logo">
    <div class="logo-icon">ah</div>
    <span class="logo-text">Salah<span>Homty</span></span>
  </a>
  <div class="nav-links">
    <a href="#" class="btn-report">Signaler un problème</a>
    <a href="#">Connexion</a>
    <a href="#">Historique</a>
    <a href="#">Alertes locales</a>
    <a href="#" class="active">Aide</a>
  </div>
</nav>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-chip">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    Centre d'aide
  </div>
  <h1>Comment pouvons-nous<br><span>vous aider ?</span></h1>
  <p>Trouvez des réponses à vos questions, des guides vidéo, et contactez notre équipe de support.</p>
  <div class="hero-search">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="heroSearch" placeholder="Ex : comment soumettre une réclamation ?" oninput="heroFilter()" />
    <button onclick="heroFilter()">Rechercher</button>
  </div>
  <div class="hero-tags">
    <button onclick="jumpFaq('soumettre')">Soumettre une réclamation</button>
    <button onclick="jumpFaq('statut')">Suivre le statut</button>
    <button onclick="jumpFaq('compte')">Mon compte</button>
    <button onclick="jumpFaq('notif')">Notifications</button>
    <button onclick="jumpFaq('admin')">Administration</button>
  </div>
</section>

<!-- ── PAGE ── -->
<div class="page">

  <!-- SYSTÈME OK -->
  <div class="status-banner">
    <div class="status-left">
      <span class="status-dot"></span>
      <div>
        <h4>Tous les systèmes sont opérationnels</h4>
        <p>Dernière vérification il y a 3 minutes</p>
      </div>
    </div>
    <div class="status-right">
      <a href="#">Voir la page de statut →</a>
    </div>
  </div>

  <!-- ACCÈS RAPIDE -->
  <div class="section-title">Accès rapide</div>
  <div class="quick-grid">
    <a href="#faq-section" class="quick-card">
      <div class="quick-icon qi-teal">📋</div>
      <h3>FAQ</h3>
      <p>Réponses aux questions les plus fréquentes sur la plateforme.</p>
    </a>
    <a href="#video-section" class="quick-card">
      <div class="quick-icon qi-orange">🎬</div>
      <h3>Guides vidéo</h3>
      <p>Tutoriels pas à pas pour utiliser toutes les fonctionnalités.</p>
    </a>
    <a href="#contact-section" class="quick-card">
      <div class="quick-icon qi-blue">💬</div>
      <h3>Nous contacter</h3>
      <p>Chat en direct, e-mail ou formulaire — nous répondons rapidement.</p>
    </a>
    <a href="#form-section" class="quick-card">
      <div class="quick-icon qi-purple">📨</div>
      <h3>Signaler un bug</h3>
      <p>Un problème technique ? Envoyez-nous un rapport détaillé.</p>
    </a>
  </div>

  <!-- FAQ -->
  <div id="faq-section" class="section-title">Questions fréquentes</div>
  <div class="faq-layout">
    <!-- Sidebar -->
    <aside class="faq-sidebar">
      <h4>Catégories</h4>
      <div class="faq-cat active" data-cat="soumettre" onclick="selectCat(this)">
        📋 Soumettre <span class="badge">5</span>
      </div>
      <div class="faq-cat" data-cat="statut" onclick="selectCat(this)">
        🔍 Suivre le statut <span class="badge">4</span>
      </div>
      <div class="faq-cat" data-cat="compte" onclick="selectCat(this)">
        👤 Mon compte <span class="badge">4</span>
      </div>
      <div class="faq-cat" data-cat="notif" onclick="selectCat(this)">
        🔔 Notifications <span class="badge">3</span>
      </div>
      <div class="faq-cat" data-cat="admin" onclick="selectCat(this)">
        🛡️ Administration <span class="badge">4</span>
      </div>
    </aside>

    <!-- Accordion -->
    <div class="faq-list">

      <!-- SOUMETTRE -->
      <div class="faq-section visible" id="sec-soumettre">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment soumettre une réclamation ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Cliquez sur le bouton <strong>« Signaler un problème »</strong> en haut à droite. Remplissez le formulaire en indiquant :
            <ul>
              <li>La catégorie du problème (voirie, éclairage, graffiti, etc.)</li>
              <li>Une description claire du problème</li>
              <li>L'adresse ou la localisation sur la carte</li>
              <li>Une ou plusieurs photos (facultatif mais recommandé)</li>
            </ul>
            Validez pour obtenir un identifiant de suivi unique.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Faut-il un compte pour soumettre une réclamation ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Non, vous pouvez soumettre une réclamation de manière anonyme. Toutefois, créer un compte vous permet de <strong>suivre vos réclamations</strong>, de recevoir des notifications et d'accéder à votre historique complet.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Quels types de problèmes puis-je signaler ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Vous pouvez signaler tout problème d'espace public, notamment :
            <ul>
              <li>🛣️ Voirie : nids-de-poule, trottoirs abîmés, signalisation manquante</li>
              <li>💡 Éclairage : lampadaires éteints, câbles dangereux</li>
              <li>🎨 Graffiti : tags, dégradations de façades</li>
              <li>🗑️ Déchets : dépôts sauvages, bacs renversés</li>
              <li>🌳 Espaces verts : arbres dangereux, équipements cassés</li>
            </ul>
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Puis-je modifier une réclamation après soumission ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Vous pouvez modifier la description ou ajouter des photos tant que la réclamation est au statut <strong>« Ouverte »</strong>. Une fois prise en charge par un agent, seul un commentaire complémentaire est possible.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment ajouter des photos à ma réclamation ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Dans le formulaire de signalement, cliquez sur la zone <strong>« Ajouter des photos »</strong>. Vous pouvez importer jusqu'à 5 images (JPG, PNG, max 10 Mo chacune). Les photos sont très utiles pour accélérer le traitement de votre réclamation.
          </div></div>
        </div>
      </div>

      <!-- STATUT -->
      <div class="faq-section" id="sec-statut">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment suivre l'état de ma réclamation ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Rendez-vous dans la page <strong>« Historique »</strong> de votre espace personnel. Chaque réclamation affiche son statut en temps réel : Ouverte, En cours, Résolue, Transférée ou En retard.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Que signifient les différents statuts ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            <ul>
              <li>🟡 <strong>Ouverte</strong> : réclamation reçue, pas encore prise en charge.</li>
              <li>🔵 <strong>En cours</strong> : un agent ou service travaille dessus.</li>
              <li>🟢 <strong>Résolue</strong> : le problème a été traité et clôturé.</li>
              <li>🟣 <strong>Transférée</strong> : redirigée vers un autre service compétent.</li>
              <li>🔴 <strong>En retard</strong> : le délai de traitement fixé a été dépassé.</li>
            </ul>
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Quel est le délai de traitement d'une réclamation ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Le délai standard est de <strong>7 jours ouvrés</strong> pour une première réponse. La résolution dépend de la complexité du problème. Les urgences (danger immédiat) sont traitées en priorité sous 24h.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Ma réclamation est marquée « En retard », que faire ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Vous pouvez contacter notre support via le formulaire ci-dessous en citant votre <strong>ID de réclamation</strong>. Si vous êtes administrateur, vous pouvez directement transférer la réclamation à un autre service depuis la page Historique.
          </div></div>
        </div>
      </div>

      <!-- COMPTE -->
      <div class="faq-section" id="sec-compte">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment créer un compte ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Cliquez sur <strong>« Connexion »</strong> puis sur « Créer un compte gratuitement ». Renseignez votre e-mail et un mot de passe sécurisé. Un e-mail de confirmation vous sera envoyé.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            J'ai oublié mon mot de passe, comment le réinitialiser ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Sur la page de connexion, cliquez sur <strong>« Mot de passe oublié »</strong>. Entrez votre adresse e-mail et vous recevrez un lien de réinitialisation valable 30 minutes. Vérifiez votre dossier spam si vous ne le trouvez pas.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment modifier mes informations personnelles ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Accédez à <strong>Mon Profil</strong> via l'icône en haut à droite. Vous pouvez y modifier votre nom, prénom, e-mail, numéro de téléphone et votre quartier de résidence.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment supprimer mon compte ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            La suppression de compte est disponible dans <strong>Paramètres → Confidentialité → Supprimer le compte</strong>. Cette action est irréversible. Vos réclamations anonymisées resteront dans la base de données à des fins statistiques.
          </div></div>
        </div>
      </div>

      <!-- NOTIF -->
      <div class="faq-section" id="sec-notif">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment activer les notifications ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Dans <strong>Paramètres → Notifications</strong>, vous pouvez activer les alertes e-mail et/ou push pour chaque changement de statut de vos réclamations. Vous recevez aussi des alertes locales sur les problèmes signalés dans votre quartier.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Je ne reçois plus les e-mails de notification, pourquoi ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Vérifiez d'abord votre dossier <strong>spam/indésirables</strong>. Ensuite, assurez-vous que les notifications sont bien activées dans vos paramètres. Si le problème persiste, contactez notre support en précisant votre adresse e-mail.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Qu'est-ce que les « Alertes locales » ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Les alertes locales vous informent en temps réel des problèmes signalés dans un rayon que vous définissez autour de chez vous (500 m à 5 km). Très utile pour suivre l'état de votre quartier.
          </div></div>
        </div>
      </div>

      <!-- ADMIN -->
      <div class="faq-section" id="sec-admin">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment accéder au tableau de bord administrateur ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Le tableau de bord est accessible uniquement aux comptes ayant le rôle <strong>Administrateur</strong> ou <strong>Agent</strong>. Connectez-vous et cliquez sur l'icône grille en haut à droite pour accéder au back-office.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment transférer une réclamation à un autre service ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Dans la page <strong>Historique</strong>, identifiez la réclamation à transférer (indiquée par le statut « En retard » ou manuellement). Cliquez sur <strong>« Transférer »</strong>, choisissez le service ou l'agent destinataire, ajoutez une note et confirmez.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment gérer les délais de traitement ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Dans <strong>Paramètres → Délais</strong>, vous pouvez configurer les seuils d'alerte par catégorie de réclamation (ex : 3 jours pour l'éclairage, 7 jours pour la voirie). Une notification automatique est envoyée à l'agent responsable à l'approche du délai.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Comment exporter les données de réclamations ?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Depuis la page Historique, cliquez sur <strong>« Exporter CSV »</strong>. Vous pouvez filtrer par date, statut, catégorie avant d'exporter. Le fichier CSV est compatible avec Excel, LibreOffice et tout outil BI.
          </div></div>
        </div>
      </div>

    </div><!-- end faq-list -->
  </div><!-- end faq-layout -->

  <!-- GUIDES VIDÉO -->
  <div id="video-section" class="section-title">Guides vidéo</div>
  <div class="videos-grid">
    <div class="video-card" onclick="showToast('Lecture de la vidéo...')">
      <div class="video-thumb">
        <span class="emo">📋</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Soumettre votre première réclamation</h4>
        <p>Guide complet pour signaler un problème en moins de 2 minutes.</p>
        <span class="video-dur">⏱ 2 min 15 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Lecture de la vidéo...')">
      <div class="video-thumb">
        <span class="emo">🗂️</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Lire l'historique de vos réclamations</h4>
        <p>Comprendre les statuts, filtres et actions disponibles.</p>
        <span class="video-dur">⏱ 3 min 40 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Lecture de la vidéo...')">
      <div class="video-thumb">
        <span class="emo">🔔</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Configurer les alertes locales</h4>
        <p>Restez informé des problèmes dans votre quartier.</p>
        <span class="video-dur">⏱ 1 min 58 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Lecture de la vidéo...')">
      <div class="video-thumb">
        <span class="emo">🛡️</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Prise en main du back-office</h4>
        <p>Guide destiné aux agents et administrateurs de la plateforme.</p>
        <span class="video-dur">⏱ 5 min 20 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Lecture de la vidéo...')">
      <div class="video-thumb">
        <span class="emo">⇄</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Transférer une réclamation</h4>
        <p>Comment réassigner une réclamation hors délai à un autre service.</p>
        <span class="video-dur">⏱ 2 min 05 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Lecture de la vidéo...')">
      <div class="video-thumb">
        <span class="emo">📊</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Exporter et analyser les données</h4>
        <p>Exploiter l'export CSV pour vos rapports mensuels.</p>
        <span class="video-dur">⏱ 3 min 12 s</span>
      </div>
    </div>
  </div>

  <!-- NOUS CONTACTER -->
  <div id="contact-section" class="section-title">Nous contacter</div>
  <div class="contact-grid">
    <div class="contact-card">
      <div class="contact-icon">💬</div>
      <h3>Chat en direct</h3>
      <p>Discutez avec un membre de notre équipe support en temps réel. Disponible du lundi au vendredi.</p>
      <span class="contact-badge cb-green">🟢 En ligne maintenant</span>
      <button class="contact-btn" onclick="showToast('Ouverture du chat...')">Démarrer le chat</button>
    </div>
    <div class="contact-card">
      <div class="contact-icon">📧</div>
      <h3>E-mail</h3>
      <p>Envoyez-nous un message détaillé. Nous répondons sous 24h ouvrées. Idéal pour les questions complexes.</p>
      <span class="contact-badge cb-orange">⏱ Réponse sous 24h</span>
      <a class="contact-btn outline" href="mailto:support@salahhomty.tn">support@salahhomty.tn</a>
    </div>
    <div class="contact-card">
      <div class="contact-icon">📞</div>
      <h3>Téléphone</h3>
      <p>Pour les urgences ou les situations critiques. Notre ligne est disponible en heures de bureau.</p>
      <span class="contact-badge cb-blue">📞 Lun–Ven 8h–17h</span>
      <a class="contact-btn outline" href="tel:+21671234567">+216 71 234 567</a>
    </div>
  </div>

  <!-- FORMULAIRE CONTACT -->
  <div id="form-section" class="section-title">Envoyer un message</div>
  <div class="form-section">
    <div class="form-left">
      <h3>Une question ? Un bug ?<br>Écrivez-nous.</h3>
      <p>Notre équipe examine chaque message avec attention. Merci de fournir un maximum de détails pour un traitement rapide.</p>

      <div class="form-info-item">
        <div class="ico">⚡</div>
        <div>
          <h5>Réponse rapide</h5>
          <p>Temps de réponse moyen : moins de 4h en jours ouvrés.</p>
        </div>
      </div>
      <div class="form-info-item">
        <div class="ico">🔒</div>
        <div>
          <h5>Données sécurisées</h5>
          <p>Vos informations sont traitées conformément au RGPD.</p>
        </div>
      </div>
      <div class="form-info-item">
        <div class="ico">🌍</div>
        <div>
          <h5>Support multilingue</h5>
          <p>Nous répondons en français et en arabe.</p>
        </div>
      </div>
    </div>

    <div class="form-right">
      <div class="form-row">
        <div class="field">
          <label>Prénom</label>
          <input type="text" placeholder="Votre prénom" />
        </div>
        <div class="field">
          <label>Nom</label>
          <input type="text" placeholder="Votre nom" />
        </div>
      </div>
      <div class="field">
        <label>Adresse e-mail</label>
        <input type="email" placeholder="vous@exemple.com" />
      </div>
      <div class="field">
        <label>Sujet</label>
        <select>
          <option value="">Sélectionnez un sujet</option>
          <option>Question générale</option>
          <option>Problème technique / Bug</option>
          <option>Ma réclamation n'avance pas</option>
          <option>Demande d'accès administrateur</option>
          <option>Suggestion d'amélioration</option>
          <option>Autre</option>
        </select>
      </div>
      <div class="field">
        <label>ID de réclamation (optionnel)</label>
        <input type="text" placeholder="Ex : REC-0041" />
      </div>
      <div class="field">
        <label>Message</label>
        <textarea placeholder="Décrivez votre problème ou question en détail…"></textarea>
      </div>
      <button class="submit-btn" onclick="submitForm()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Envoyer le message
      </button>
    </div>
  </div>

</div><!-- end page -->

<!-- FOOTER -->
<footer>
  <p>© 2025 SalahHomty — <a href="#">Conditions d'utilisation</a> · <a href="#">Politique de confidentialité</a> · <a href="#">Statut du service</a></p>
</footer>

<!-- TOAST -->
<div class="toast" id="toast">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1abc9c" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <span id="toastMsg">Message envoyé !</span>
</div>

<script>
  /* ── FAQ ACCORDION ── */
  function toggleFaq(btn) {
    const item = btn.closest('.faq-item');
    const isOpen = item.classList.contains('open');
    // close all in same section
    item.closest('.faq-section').querySelectorAll('.faq-item.open').forEach(i => i.classList.remove('open'));
    if (!isOpen) item.classList.add('open');
  }

  /* ── FAQ CATEGORY ── */
  function selectCat(el) {
    document.querySelectorAll('.faq-cat').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    const cat = el.dataset.cat;
    document.querySelectorAll('.faq-section').forEach(s => s.classList.remove('visible'));
    document.getElementById('sec-' + cat).classList.add('visible');
  }

  function jumpFaq(cat) {
    document.getElementById('faq-section').scrollIntoView({ behavior: 'smooth', block: 'start' });
    const el = document.querySelector(`.faq-cat[data-cat="${cat}"]`);
    if (el) selectCat(el);
  }

  /* ── HERO SEARCH ── */
  function heroFilter() {
    const q = document.getElementById('heroSearch').value.toLowerCase().trim();
    if (!q) return;
    // map keywords to categories
    const map = [
      { words: ['soumett','signaler','photo','catégorie','type'], cat: 'soumettre' },
      { words: ['statut','délai','retard','suivi'], cat: 'statut' },
      { words: ['compte','mot de passe','profil','créer','supprimer'], cat: 'compte' },
      { words: ['notif','alerte','email','mail'], cat: 'notif' },
      { words: ['admin','agent','transférer','export','back'], cat: 'admin' },
    ];
    for (const entry of map) {
      if (entry.words.some(w => q.includes(w))) {
        jumpFaq(entry.cat);
        return;
      }
    }
    jumpFaq('soumettre'); // default
  }

  /* ── TOAST ── */
  function showToast(msg) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3200);
  }

  /* ── FORM SUBMIT ── */
  function submitForm() {
    showToast('Message envoyé ! Nous vous répondrons sous 24h.');
  }

  /* ── NAV ACTIVE ── */
  document.querySelectorAll('.nav-links a').forEach(a => {
    a.addEventListener('click', function() {
      if (this.classList.contains('btn-report')) return;
      document.querySelectorAll('.nav-links a').forEach(x => x.classList.remove('active'));
      this.classList.add('active');
    });
  });
</script>
</body>
</html>