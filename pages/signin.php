<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign in – SalahHomty</title>
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    :root {
      --color-primary: #20b1a0;
      --color-primary-dark: #178a7c;
      --color-dark: #1a1a1a;
      --color-nav-bg: #222222;
      --color-nav-text: #cccccc;
      --color-white: #ffffff;
      --color-muted: #666666;
      --color-border: #e0e0e0;
      --font-base: 'Source Sans 3', sans-serif;
      --radius-sm: 2px;
      --radius-md: 6px;
      --transition-fast: 0.18s ease;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: var(--font-base);
      background-color: #f4f4f4;
      color: var(--color-dark);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    a { color: inherit; text-decoration: none; }
    button { font-family: var(--font-base); cursor: pointer; }

    /* ── NAVBAR ── */
    .navbar {
      background-color: var(--color-nav-bg);
      display: flex;
      align-items: center;
      height: 68px;
      padding: 0 32px;
      gap: 16px;
      flex-shrink: 0;
      box-shadow: 0 2px 8px rgba(0,0,0,0.3);
    }

    .navbar__brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }

    /* Wrench logo icon */
    .navbar__brand-icon {
      width: 38px;
      height: 38px;
      background-color: var(--color-primary);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .navbar__brand-icon svg {
      width: 22px;
      height: 22px;
      color: white;
    }

    .navbar__brand-name {
      font-size: 24px;
      font-weight: 800;
      letter-spacing: -0.4px;
    }

    .navbar__brand-name .word--salah { color: var(--color-white); }
    .navbar__brand-name .word--homty { color: var(--color-primary); }

    .navbar__spacer { flex: 1; }

    .navbar__links {
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .navbar__links a {
      color: var(--color-nav-text);
      font-size: 14px;
      padding: 7px 14px;
      border-radius: var(--radius-md);
      transition: color var(--transition-fast), background-color var(--transition-fast);
      font-weight: 600;
    }

    .navbar__links a:hover {
      color: var(--color-white);
      background-color: rgba(255,255,255,0.06);
    }

    /* Report a problem — always colored */
    .navbar__report-btn {
      background-color: var(--color-primary) !important;
      color: var(--color-white) !important;
      padding: 8px 18px !important;
      border-radius: var(--radius-md) !important;
      font-weight: 700 !important;
      font-size: 14px !important;
      transition: background-color var(--transition-fast) !important;
    }

    .navbar__report-btn:hover {
      background-color: var(--color-primary-dark) !important;
    }

    /* Language switcher */
    .navbar__lang {
      display: flex;
      align-items: center;
      gap: 2px;
      border: 1px solid #444;
      border-radius: var(--radius-md);
      padding: 5px 10px;
      color: var(--color-nav-text) !important;
      font-size: 13px !important;
      font-weight: 600 !important;
    }

    .navbar__lang svg { width: 14px; height: 14px; }
    .navbar__lang:hover { border-color: #888; color: #fff !important; }

    /* ── MAIN ── */
    .signin-main {
      flex: 1;
      display: grid;
      grid-template-columns: 1fr 1fr;
      min-height: calc(100vh - 68px - 80px);
    }

    /* ── LEFT PANEL ── */
    .signin-panel {
      background: linear-gradient(145deg, var(--color-primary) 0%, #178a7c 60%, #0f6b61 100%);
      padding: 72px 60px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }

    .signin-panel::before {
      content: '';
      position: absolute;
      top: -80px; right: -80px;
      width: 340px; height: 340px;
      border-radius: 50%;
      background: rgba(255,255,255,0.06);
    }

    .signin-panel::after {
      content: '';
      position: absolute;
      bottom: -60px; left: -60px;
      width: 260px; height: 260px;
      border-radius: 50%;
      background: rgba(0,0,0,0.08);
    }

    .signin-panel__eyebrow {
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: rgba(255,255,255,0.6);
      margin-bottom: 20px;
    }

    .signin-panel__title {
      font-size: clamp(28px, 3.5vw, 44px);
      font-weight: 800;
      line-height: 1.1;
      color: var(--color-white);
      margin-bottom: 20px;
      letter-spacing: -0.8px;
    }

    .signin-panel__subtitle {
      font-size: 16px;
      line-height: 1.7;
      color: rgba(255,255,255,0.82);
      margin-bottom: 40px;
      max-width: 380px;
    }

    .signin-panel__features {
      list-style: none;
      display: flex;
      flex-direction: column;
      gap: 14px;
    }

    .signin-panel__feature {
      display: flex;
      align-items: center;
      gap: 12px;
      color: rgba(255,255,255,0.92);
      font-size: 15px;
      font-weight: 600;
    }

    .signin-panel__feature-dot {
      width: 8px; height: 8px;
      background-color: rgba(255,255,255,0.7);
      border-radius: 50%;
      flex-shrink: 0;
    }

    /* ── RIGHT PANEL ── */
    .signin-form-panel {
      background-color: var(--color-white);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 40px;
    }

    .signin-card {
      width: 100%;
      max-width: 420px;
      display: flex;
      flex-direction: column;
      gap: 0;
    }

    .signin-card__header {
      margin-bottom: 28px;
    }

    .signin-card__title {
      font-size: 30px;
      font-weight: 800;
      color: var(--color-dark);
      letter-spacing: -0.5px;
      margin-bottom: 6px;
    }

    .signin-card__subtitle {
      font-size: 15px;
      color: var(--color-muted);
    }

    .signin-card__subtitle a {
      color: var(--color-primary);
      font-weight: 700;
      text-decoration: underline;
    }

    .signin-card__subtitle a:hover { color: var(--color-primary-dark); }

    .signin-card__divider {
      height: 1px;
      background-color: var(--color-border);
      margin-bottom: 28px;
    }

    /* Form fields */
    .form-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      margin-bottom: 18px;
    }

    .form-group__label {
      font-size: 14px;
      font-weight: 700;
      color: var(--color-dark);
    }

    .form-group__input {
      padding: 13px 16px;
      font-size: 15px;
      font-family: var(--font-base);
      border: 2px solid var(--color-border);
      border-radius: var(--radius-md);
      outline: none;
      background-color: #fafafa;
      transition: border-color var(--transition-fast), background-color var(--transition-fast), box-shadow var(--transition-fast);
      color: var(--color-dark);
    }

    .form-group__input:focus {
      border-color: var(--color-primary);
      background-color: #fff;
      box-shadow: 0 0 0 3px rgba(32,177,160,0.12);
    }

    .form-group__input::placeholder { color: #aaa; }

    /* Forgot password */
    .form-group__forgot {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .forgot-link {
      font-size: 13px;
      color: var(--color-primary);
      font-weight: 600;
      text-decoration: underline;
    }

    .forgot-link:hover { color: var(--color-primary-dark); }

    /* Actions */
    .signin-card__actions {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: 6px;
      margin-bottom: 24px;
    }

    /* PRIMARY — Sign in button (teal, desirable) */
    .btn-primary {
      width: 100%;
      padding: 14px;
      font-size: 16px;
      font-weight: 800;
      font-family: var(--font-base);
      background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
      color: var(--color-white);
      border: none;
      border-radius: var(--radius-md);
      cursor: pointer;
      letter-spacing: 0.3px;
      transition: transform var(--transition-fast), box-shadow var(--transition-fast), filter var(--transition-fast);
      box-shadow: 0 4px 16px rgba(32,177,160,0.35);
    }

    .btn-primary:hover {
      transform: translateY(-1px);
      box-shadow: 0 6px 22px rgba(32,177,160,0.45);
      filter: brightness(1.05);
    }

    .btn-primary:active {
      transform: translateY(0);
      box-shadow: 0 2px 8px rgba(32,177,160,0.3);
    }

    /* Divider OR */
    .or-divider {
      display: flex;
      align-items: center;
      gap: 12px;
      color: #bbb;
      font-size: 13px;
      font-weight: 600;
    }

    .or-divider::before, .or-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background-color: var(--color-border);
    }

    /* SECONDARY — alternative account */
    .btn-secondary {
      width: 100%;
      padding: 13px;
      font-size: 15px;
      font-weight: 700;
      font-family: var(--font-base);
      background-color: var(--color-white);
      color: var(--color-dark);
      border: 2px solid var(--color-dark);
      border-radius: var(--radius-md);
      cursor: pointer;
      transition: background-color var(--transition-fast), transform var(--transition-fast);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      text-decoration: none;
    }

    .btn-secondary:hover {
      background-color: #f5f5f5;
      transform: translateY(-1px);
    }

    .btn-secondary svg { width: 18px; height: 18px; flex-shrink: 0; }

    /* Register card */
    .register-card {
      margin-top: 4px;
      padding: 18px 20px;
      background-color: #f7fffe;
      border: 2px solid rgba(32,177,160,0.2);
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }

    .register-card__text {
      font-size: 14px;
      color: var(--color-muted);
      line-height: 1.4;
    }

    .register-card__text strong { color: var(--color-dark); }

    .register-card__btn {
      flex-shrink: 0;
      padding: 9px 18px;
      background: var(--color-primary);
      color: white;
      font-size: 14px;
      font-weight: 700;
      font-family: var(--font-base);
      border: none;
      border-radius: var(--radius-sm);
      cursor: pointer;
      transition: background-color var(--transition-fast);
      white-space: nowrap;
    }

    .register-card__btn:hover { background-color: var(--color-primary-dark); }

    /* Note */
    .signin-card__note {
      font-size: 12px;
      color: #aaa;
      text-align: center;
      line-height: 1.6;
      margin-top: 4px;
    }

    .signin-card__note a {
      color: var(--color-primary);
      text-decoration: underline;
    }

    /* ── FOOTER ── */
    .footer {
      background-color: #2d2d2d;
      color: #bbbbbb;
      display: flex;
      align-items: center;
      gap: 32px;
      padding: 22px 60px;
      flex-wrap: wrap;
    }

    .footer__col { display: flex; flex-direction: column; gap: 3px; }
    .footer__col--wide { flex: 1; min-width: 260px; }
    .footer__label { font-size: 12px; color: #777; text-transform: uppercase; letter-spacing: 0.6px; }
    .footer__brand { font-size: 16px; font-weight: 700; color: #fff; }
    .footer__divider { width: 1px; height: 40px; background-color: #444; flex-shrink: 0; }
    .footer__legal { font-size: 13px; line-height: 1.6; color: #999; }
    .footer__legal strong { color: #fff; }
    .footer__email { color: var(--color-primary); text-decoration: underline; }
    .footer__email:hover { color: #5dd9cc; }

    .footer__social { display: flex; align-items: center; gap: 8px; margin-left: auto; }

    .footer__social-link {
      width: 34px; height: 34px;
      background-color: #444;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff;
      transition: background-color var(--transition-fast);
    }

    .footer__social-link svg { width: 16px; height: 16px; }
    .footer__social-link:hover { background-color: var(--color-primary); }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      .signin-main { grid-template-columns: 1fr; }
      .signin-panel { padding: 48px 28px; }
      .signin-panel::before, .signin-panel::after { display: none; }
      .signin-form-panel { padding: 40px 24px; }
      .navbar { padding: 0 16px; }
      .navbar__links .hide-mobile { display: none; }
      .footer { padding: 28px 24px; flex-direction: column; align-items: flex-start; gap: 16px; }
      .footer__divider { width: 100%; height: 1px; }
      .footer__social { margin-left: 0; }
    }
  </style>
</head>
<body>

  <!-- NAVBAR -->
  <nav class="navbar" aria-label="Main navigation">

    <a href="index.html" class="navbar__brand" aria-label="SalahHomty home">
      <div class="navbar__brand-icon" aria-hidden="true">
        <!-- Wrench SVG icon -->
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
        </svg>
      </div>
      <span class="navbar__brand-name">
        <span class="word--salah">Salah</span><span class="word--homty">Homty</span>
      </span>
    </a>

    <div class="navbar__spacer"></div>

    <div class="navbar__links">
      <a href="../index.php" class="navbar__report-btn">Report a problem</a>
      <a href="signin.php" class="hide-mobile">Sign in</a>
      <a href="history.php" class="hide-mobile">History</a>
      
      <a href="helppage.php" class="hide-mobile">Help</a>
      <!-- Language selector -->
      <a href="#" class="navbar__lang" aria-label="Language: English">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/>
          <line x1="2" y1="12" x2="22" y2="12"/>
          <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        EN
      </a>
    </div>

  </nav>


  <!-- MAIN -->
  <main class="signin-main">

    <!-- Left: Branding -->
    <div class="signin-panel">
      <p class="signin-panel__eyebrow">Community Platform</p>
      <h1 class="signin-panel__title">
        Report local problems.<br />Make your street better.
      </h1>
      <p class="signin-panel__subtitle">
        Join thousands of citizens using SalahHomty to report
        graffiti, potholes, broken lighting, and more — right
        to the people who can fix them.
      </p>
      <ul class="signin-panel__features" aria-label="Platform features">
        <li class="signin-panel__feature">
          <span class="signin-panel__feature-dot" aria-hidden="true"></span>
          Report a problem in under 2 minutes
        </li>
        <li class="signin-panel__feature">
          <span class="signin-panel__feature-dot" aria-hidden="true"></span>
          Track the status of your report
        </li>
        <li class="signin-panel__feature">
          <span class="signin-panel__feature-dot" aria-hidden="true"></span>
          Get notified when it's fixed
        </li>
        <li class="signin-panel__feature">
          <span class="signin-panel__feature-dot" aria-hidden="true"></span>
          Free to use, always
        </li>
      </ul>
    </div>

    <!-- Right: Form -->
    <div class="signin-form-panel">
      <div class="signin-card">

        <div class="signin-card__header">
          <h2 class="signin-card__title">Welcome back</h2>
          <p class="signin-card__subtitle">
            Don't have an account?
            <a href="#">Create one for free</a>
          </p>
        </div>

        <div class="signin-card__divider" aria-hidden="true"></div>

        <!-- Email -->
        <div class="form-group">
          <label class="form-group__label" for="email-input">Email address</label>
          <input id="email-input" class="form-group__input" type="email"
            placeholder="you@example.com" autocomplete="email" aria-required="true" />
        </div>

        <!-- Password -->
        <div class="form-group">
          <div class="form-group__forgot">
            <label class="form-group__label" for="password-input">Password</label>
            <a href="#" class="forgot-link">Forgot password?</a>
          </div>
          <input id="password-input" class="form-group__input" type="password"
            placeholder="Enter your password" autocomplete="current-password" aria-required="true" />
        </div>

        <!-- Actions -->
        <div class="signin-card__actions">
          <button class="btn-primary" type="button">Sign in</button>

          <div class="or-divider">or</div>

          <!-- Connect with another account -->
          <button class="btn-secondary" type="button">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            Sign in with another account
          </button>
        </div>

        <!-- Register nudge -->
        <div class="register-card">
          <div class="register-card__text">
            <strong>New to SalahHomty?</strong><br />
            Create a free account and start reporting today.
          </div>
          <button class="register-card__btn" type="button">Sign up</button>
        </div>

        <p class="signin-card__note" style="margin-top:16px;">
          By signing in you agree to our
          <a href="#">Terms of Service</a> and
          <a href="#">Privacy Policy</a>.
        </p>

      </div>
    </div>

  </main>


  <!-- FOOTER -->
  <footer class="footer">
    <div class="footer__col">
      <span class="footer__label">Built &amp; powered by</span>
      <span class="footer__brand">Sharp Minds Squad</span>
    </div>
    <div class="footer__divider" aria-hidden="true"></div>
    <div class="footer__col footer__col--wide">
      <p class="footer__legal">
        <strong>SalahHomty</strong> is a community reporting platform built by
        <strong>Sharp Minds Squad</strong>. For enquiries, contact us at
        <a href="mailto:hello@sharpmindssquad.com" class="footer__email">hello@sharpmindssquad.com</a>
        or
        <a href="mailto:support@sharpmindssquad.com" class="footer__email">support@sharpmindssquad.com</a>.
      </p>
    </div>
    <div class="footer__social" aria-label="Social media links">
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
  </footer>

</body>
</html>