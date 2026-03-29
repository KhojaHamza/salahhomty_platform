<?php
// ─── BACKEND ──────────────────────────────────────────────────────────────────
session_start();

$userName = $_SESSION['user_name'] ?? 'there';

// Détruire la session
session_unset();
session_destroy();

// Ne pas rediriger immédiatement — afficher la page d'abord
// La redirection se fait via JS après 3 secondes
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SalahHomty — Goodbye</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --teal:       #1a9e8f;
      --teal-dark:  #127a6e;
      --teal-light: #e8f7f5;
      --teal-mid:   #2bbdac;
      --navy:       #1a2332;
      --white:      #ffffff;
      --gray-100:   #f4f6f8;
      --gray-300:   #d1d8dd;
      --gray-500:   #7a8b96;
      --gray-800:   #1e2d35;
      --radius:     14px;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'DM Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      background: var(--gray-100);
      color: var(--gray-800);
    }

    /* ── Left panel ── */
    .panel-left {
      width: 42%;
      background: var(--navy);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      padding: 60px 56px;
      position: relative;
      overflow: hidden;
    }
    .panel-left::before {
      content: '';
      position: absolute;
      width: 420px; height: 420px;
      border-radius: 50%;
      background: rgba(26,158,143,.08);
      bottom: -120px; left: -100px;
    }
    .panel-left::after {
      content: '';
      position: absolute;
      width: 260px; height: 260px;
      border-radius: 50%;
      background: rgba(26,158,143,.05);
      top: -60px; right: -60px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 56px;
      z-index: 1;
      text-decoration: none;
    }
    .brand-icon {
      width: 42px; height: 42px;
      background: var(--teal);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
    }
    .brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      letter-spacing: .3px;
    }
    .brand-name .salah { color: var(--white); }
    .brand-name .homty { color: var(--teal); }

    .panel-left h1 {
      font-family: 'Playfair Display', serif;
      font-size: 40px;
      line-height: 1.2;
      color: var(--white);
      margin-bottom: 16px;
      z-index: 1;
    }
    .panel-left p {
      font-size: 15px;
      color: rgba(255,255,255,.6);
      line-height: 1.7;
      z-index: 1;
      max-width: 300px;
    }

    /* ── Right panel ── */
    .panel-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 32px;
    }

    .card {
      width: 100%;
      max-width: 440px;
      background: var(--white);
      border-radius: 24px;
      padding: 52px 44px;
      box-shadow: 0 8px 40px rgba(26,158,143,.13);
      text-align: center;
      animation: fadeUp .5s ease both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Wave icon ── */
    .icon-wrap {
      width: 80px; height: 80px;
      background: var(--teal-light);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 36px;
      margin: 0 auto 28px;
      animation: wave 1.5s ease infinite;
      transform-origin: 70% 70%;
    }
    @keyframes wave {
      0%,100% { transform: rotate(0deg); }
      20%      { transform: rotate(-15deg); }
      40%      { transform: rotate(15deg); }
      60%      { transform: rotate(-10deg); }
      80%      { transform: rotate(10deg); }
    }

    .card h2 {
      font-size: 26px;
      font-weight: 700;
      color: var(--gray-800);
      margin-bottom: 10px;
    }
    .card .subtitle {
      font-size: 14px;
      color: var(--gray-500);
      line-height: 1.6;
      margin-bottom: 36px;
    }
    .card .subtitle strong {
      color: var(--teal);
    }

    /* ── Countdown bar ── */
    .countdown-wrap {
      margin-bottom: 32px;
    }
    .countdown-text {
      font-size: 13px;
      color: var(--gray-500);
      margin-bottom: 10px;
    }
    .countdown-text span {
      font-weight: 700;
      color: var(--teal);
    }
    .progress-bar {
      width: 100%;
      height: 4px;
      background: var(--gray-100);
      border-radius: 4px;
      overflow: hidden;
    }
    .progress-fill {
      height: 100%;
      width: 100%;
      background: linear-gradient(90deg, var(--teal), var(--teal-mid));
      border-radius: 4px;
      animation: shrink 3s linear forwards;
    }
    @keyframes shrink {
      from { width: 100%; }
      to   { width: 0%; }
    }

    /* ── Buttons ── */
    .btn-primary {
      display: block;
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--teal), var(--teal-dark));
      color: var(--white);
      border: none;
      border-radius: var(--radius);
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      text-decoration: none;
      margin-bottom: 12px;
      transition: opacity .2s, transform .15s;
      box-shadow: 0 4px 18px rgba(26,158,143,.3);
    }
    .btn-primary:hover {
      opacity: .92;
      transform: translateY(-1px);
    }

    .btn-secondary {
      display: block;
      width: 100%;
      padding: 14px;
      background: transparent;
      color: var(--gray-500);
      border: 1.5px solid var(--gray-300);
      border-radius: var(--radius);
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: border-color .2s, color .2s;
    }
    .btn-secondary:hover {
      border-color: var(--teal);
      color: var(--teal);
    }

    /* ── Responsive ── */
    @media (max-width: 860px) {
      .panel-left { display: none; }
      .panel-right { padding: 24px 16px; }
      .card { padding: 40px 28px; }
    }
  </style>
</head>
<body>

<!-- Left panel -->
<aside class="panel-left">
  <a href="../index.php" class="brand">
    <div class="brand-icon">🔧</div>
    <span class="brand-name">
      <span class="salah">Salah</span><span class="homty">Homty</span>
    </span>
  </a>

  <h1>See you<br>next time.</h1>
  <p>Your session has been securely closed. Your data is safe with us.</p>
</aside>

<!-- Right panel -->
<main class="panel-right">
  <div class="card">

    <div class="icon-wrap">👋</div>

    <h2>You've been signed out</h2>
    <p class="subtitle">
      Goodbye, <strong><?= htmlspecialchars($userName) ?></strong>!<br>
      You have been successfully logged out.
    </p>

    <!-- Countdown -->
    <div class="countdown-wrap">
      <p class="countdown-text">
        Redirecting to Sign in in <span id="counter">3</span>s...
      </p>
      <div class="progress-bar">
        <div class="progress-fill"></div>
      </div>
    </div>

    <!-- Buttons -->
    <a href="../pages/signin.php" class="btn-primary">Sign in again →</a>
    <a href="../index.php"        class="btn-secondary">Go to homepage</a>

  </div>
</main>

<script>
  // Countdown 3 → 0 puis redirect
  let count = 3;
  const counter = document.getElementById('counter');

  const timer = setInterval(() => {
    count--;
    counter.textContent = count;
    if (count <= 0) {
      clearInterval(timer);
      window.location.href = '../pages/signin.php';
    }
  }, 1000);
</script>

</body>
</html>