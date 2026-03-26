<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign in – SalahHomty</title>

  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap"
    rel="stylesheet"
  />

  <!-- Shared base stylesheet -->
  <link rel="stylesheet" href="../assets/css/signin.css" />
  <link rel="stylesheet" href="../assets/css/style.css" />
  <!-- Sign-in page stylesheet -->
  <link rel="stylesheet" href="signin.css" />
</head>

<body>

  <!-- ══════════════════════════════════════════════════════
       NAVIGATION BAR
  ═══════════════════════════════════════════════════════ -->
  <nav class="navbar" aria-label="Main navigation">

    <a href="index.html" class="navbar__brand" aria-label="SalahHomty home">
      <div class="navbar__brand-icon" aria-hidden="true">
        <img src="teamlogo.png" alt="SalahHomty Logo" width="44" height="44" />
      </div>
      <span class="navbar__brand-name">
        <span class="word--salah">Salah</span><span class="word--homty">Homty</span>
      </span>
    </a>

    <div class="navbar__spacer"></div>

    <div class="navbar__links">
      <a href="#" class="navbar__report-btn">Report a problem</a>
      <a href="signin.html">Sign in</a>
      <a href="#">All reports</a>
      <a href="#">Local alerts</a>
      <a href="#">Help</a>
    </div>

  </nav>


  <!-- ══════════════════════════════════════════════════════
       MAIN — Split screen: left branding / right form
  ═══════════════════════════════════════════════════════ -->
  <main class="signin-main">

    <!-- ── Left Panel: Branding ─────────────────────── -->
    <div class="signin-panel">
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

    <!-- ── Right Panel: Form ────────────────────────── -->
    <div class="signin-form-panel">
      <div class="signin-card">

        <!-- Card header -->
        <div class="signin-card__header">
          <h2 class="signin-card__title">Welcome back</h2>
          <p class="signin-card__subtitle">
            No account yet?
            <a href="#">Create one for free</a>
          </p>
        </div>

        <div class="signin-card__divider" aria-hidden="true"></div>

        <!-- Email field -->
        <div class="form-group">
          <label class="form-group__label" for="email-input">
            Email address
          </label>
          <input
            id="email-input"
            class="form-group__input"
            type="email"
            placeholder="you@example.com"
            autocomplete="email"
            aria-required="true"
          />
        </div>

        <!-- Password field -->
        <div class="form-group">
          <label class="form-group__label" for="password-input">
            Password
          </label>
          <input
            id="password-input"
            class="form-group__input"
            type="password"
            placeholder="Enter your password"
            autocomplete="current-password"
            aria-required="true"
          />
        </div>

        <!-- Actions -->
        <div class="signin-card__actions">
          <button class="btn-primary" type="button">
            Sign in
          </button>
          <a href="#" class="btn-secondary">
            Email me a sign-in link instead
          </a>
        </div>

        <!-- Footer note -->
        <p class="signin-card__note">
          By signing in you agree to our
          <a href="#">Terms of Service</a> and
          <a href="#">Privacy Policy</a>.
        </p>

      </div>
    </div><!-- /signin-form-panel -->

  </main>


  <!-- ══════════════════════════════════════════════════════
       FOOTER
  ═══════════════════════════════════════════════════════ -->
  <footer class="footer">

    <div class="footer__col">
      <span class="footer__label">Built &amp; powered by</span>
      <span class="footer__brand">Sharp Minds Squad</span>
    </div>

    <div class="footer__divider" aria-hidden="true"></div>

    <div class="footer__col footer__col--wide">
      <p class="footer__legal">
        <strong>SalahHomty</strong> is a community reporting platform built by
        <strong>Sharp Minds Squad</strong>. For any enquiries, reach us at
        <a href="mailto:hello@sharpmindssquad.com" class="footer__email">hello@sharpmindssquad.com</a>
        or
        <a href="mailto:support@sharpmindssquad.com" class="footer__email">support@sharpmindssquad.com</a>.
      </p>
    </div>

    <div class="footer__social" aria-label="Social media links">

      <a href="#" class="footer__social-link" aria-label="GitHub">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482
                   0-.237-.009-.868-.013-1.703-2.782.604-3.369-1.34-3.369-1.34-.454-1.156-1.11-1.463-1.11-1.463
                   -.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832
                   .092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683
                   -.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0 1 12 6.836a9.58 9.58
                   0 0 1 2.504.337c1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.202 2.394.1 2.647.64.699
                   1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336
                   -.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10z"/>
        </svg>
      </a>

      <a href="#" class="footer__social-link" aria-label="X (Twitter)">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68
                   l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
        </svg>
      </a>

      <a href="#" class="footer__social-link" aria-label="Facebook">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073c0 6.027 4.388 11.024 10.125
                   11.927v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0
                   2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.887v2.267h3.328l-.532
                   3.49h-2.796V24C19.612 23.097 24 18.1 24 12.073z"/>
        </svg>
      </a>

    </div>

  </footer>

</body>
</html>