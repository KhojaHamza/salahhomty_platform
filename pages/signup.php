<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
$error   = $_GET['error']   ?? '';
$success = $_GET['success'] ?? '';
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>SalahHomty — Créer un compte</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet"/>
  <style>
    :root {
      --teal:        #1a9e8f;
      --teal-dark:   #127a6e;
      --teal-light:  #e8f7f5;
      --teal-mid:    #2bbdac;
      --white:       #ffffff;
      --gray-100:    #f4f6f8;
      --gray-300:    #d1d8dd;
      --gray-500:    #7a8b96;
      --gray-800:    #1e2d35;
      --error:       #e05252;
      --success:     #27ae60;
      --radius:      14px;
      --shadow:      0 8px 40px rgba(26,158,143,.13);
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
      background: linear-gradient(145deg, var(--teal-dark) 0%, var(--teal-mid) 100%);
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
      background: rgba(255,255,255,.07);
      bottom: -120px; left: -100px;
    }
    .panel-left::after {
      content: '';
      position: absolute;
      width: 260px; height: 260px;
      border-radius: 50%;
      background: rgba(255,255,255,.05);
      top: -60px; right: -60px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 56px;
      z-index: 1;
    }
    .brand-icon {
      width: 42px; height: 42px;
      background: rgba(255,255,255,.2);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 20px;
    }
    .brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      color: var(--white);
      letter-spacing: .3px;
    }

    .panel-left h1 {
      font-family: 'Playfair Display', serif;
      font-size: 42px;
      line-height: 1.2;
      color: var(--white);
      margin-bottom: 20px;
      z-index: 1;
    }
    .panel-left p {
      font-size: 16px;
      color: rgba(255,255,255,.75);
      line-height: 1.7;
      z-index: 1;
      max-width: 320px;
    }

    .features {
      margin-top: 48px;
      display: flex;
      flex-direction: column;
      gap: 18px;
      z-index: 1;
    }
    .feature {
      display: flex;
      align-items: center;
      gap: 14px;
      color: rgba(255,255,255,.9);
      font-size: 14px;
    }
    .feature-dot {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: rgba(255,255,255,.6);
      flex-shrink: 0;
    }

    /* ── Right panel ── */
    .panel-right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 32px;
      overflow-y: auto;
    }

    .card {
      width: 100%;
      max-width: 480px;
      background: var(--white);
      border-radius: 24px;
      padding: 48px 44px;
      box-shadow: var(--shadow);
      animation: fadeUp .5s ease both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .card-header {
      margin-bottom: 32px;
    }
    .card-header h2 {
      font-size: 28px;
      font-weight: 700;
      color: var(--gray-800);
      margin-bottom: 6px;
    }
    .card-header p {
      font-size: 14px;
      color: var(--gray-500);
    }
    .card-header a {
      color: var(--teal);
      font-weight: 600;
      text-decoration: none;
    }
    .card-header a:hover { text-decoration: underline; }

    .divider { height: 1px; background: var(--gray-300); margin-bottom: 28px; }

    /* ── Alert ── */
    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13.5px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .alert-error   { background: #fdf0f0; color: var(--error);   border: 1px solid #f5c0c0; }
    .alert-success { background: #edfaf3; color: var(--success); border: 1px solid #a8e6c1; }

    /* ── Form ── */
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }

    .form-group {
      margin-bottom: 18px;
      position: relative;
    }

    label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--gray-800);
      margin-bottom: 7px;
    }

    input {
      width: 100%;
      padding: 13px 16px;
      border: 1.5px solid var(--gray-300);
      border-radius: var(--radius);
      font-family: 'DM Sans', sans-serif;
      font-size: 14.5px;
      color: var(--gray-800);
      background: var(--gray-100);
      transition: border-color .2s, box-shadow .2s, background .2s;
      outline: none;
    }
    input:focus {
      border-color: var(--teal);
      background: var(--teal-light);
      box-shadow: 0 0 0 4px rgba(26,158,143,.1);
    }
    input::placeholder { color: var(--gray-300); }

    .input-icon-wrap { position: relative; }
    .input-icon-wrap input { padding-right: 44px; }
    .toggle-pass {
      position: absolute;
      right: 14px; top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      cursor: pointer; color: var(--gray-500);
      font-size: 16px; padding: 0;
      display: flex; align-items: center;
    }

    /* Password strength */
    .strength-bar {
      display: flex; gap: 4px; margin-top: 8px;
    }
    .strength-segment {
      flex: 1; height: 3px; border-radius: 4px;
      background: var(--gray-300);
      transition: background .3s;
    }
    .strength-label {
      font-size: 11px; color: var(--gray-500);
      margin-top: 4px; text-align: right;
    }

    /* ── Submit ── */
    .btn-submit {
      width: 100%;
      padding: 15px;
      background: linear-gradient(135deg, var(--teal) 0%, var(--teal-dark) 100%);
      color: var(--white);
      border: none;
      border-radius: var(--radius);
      font-family: 'DM Sans', sans-serif;
      font-size: 15px;
      font-weight: 700;
      cursor: pointer;
      letter-spacing: .3px;
      margin-top: 8px;
      transition: opacity .2s, transform .15s, box-shadow .2s;
      box-shadow: 0 4px 18px rgba(26,158,143,.35);
    }
    .btn-submit:hover {
      opacity: .93;
      transform: translateY(-1px);
      box-shadow: 0 6px 24px rgba(26,158,143,.45);
    }
    .btn-submit:active { transform: translateY(0); }

    .terms {
      text-align: center;
      font-size: 12px;
      color: var(--gray-500);
      margin-top: 18px;
      line-height: 1.6;
    }
    .terms a { color: var(--teal); text-decoration: none; font-weight: 600; }

    /* ── Responsive ── */
    @media (max-width: 860px) {
      .panel-left { display: none; }
      .panel-right { padding: 24px 16px; }
      .card { padding: 36px 28px; }
      .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- Left decorative panel -->
<aside class="panel-left">
  <div class="brand">
    <div class="brand-icon">🔧</div>
<span class="brand-name">
  <span style="color: white;">Salah</span><span style="color: #1a9e8f;">Homty</span>
</span>
  </div>

  <h1>Start reporting<br>today.</h1>
  <p>Join thousands of users who trust SalahHomty to manage and report their properties efficiently.</p>

  <div class="features">
    <div class="feature"><div class="feature-dot"></div> Free account, no credit card required</div>
    <div class="feature"><div class="feature-dot"></div> Real-time property reporting</div>
    <div class="feature"><div class="feature-dot"></div> Secure and private by design</div>
    <div class="feature"><div class="feature-dot"></div> Access from any device</div>
  </div>
</aside>

<!-- Right form panel -->
<main class="panel-right">
  <div class="card">

    <div class="card-header">
      <h2>Create your account</h2>
      <p>Already have an account? <a href="signin.php">Sign in</a></p>
    </div>

    <div class="divider"></div>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="../auth/signup.php" novalidate>

      <!-- Name + Phone -->
      <div class="form-row">
        <div class="form-group">
          <label for="name">Full name</label>
          <input type="text" id="name" name="name" placeholder="Siham Zaoui" required />
        </div>
        <div class="form-group">
          <label for="phone">Phone number</label>
          <input type="tel" id="phone" name="phone" placeholder="+216 xx xxx xxx" />
        </div>
      </div>

      <!-- Email -->
      <div class="form-group">
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" placeholder="you@example.com" required />
      </div>

      <!-- Password -->
      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-icon-wrap">
          <input type="password" id="password" name="password"
                 placeholder="Min. 8 characters" required
                 oninput="checkStrength(this.value)" />
          <button type="button" class="toggle-pass" onclick="togglePass('password', this)">👁</button>
        </div>
        <div class="strength-bar">
          <div class="strength-segment" id="s1"></div>
          <div class="strength-segment" id="s2"></div>
          <div class="strength-segment" id="s3"></div>
          <div class="strength-segment" id="s4"></div>
        </div>
        <div class="strength-label" id="strength-label"></div>
      </div>

      <!-- Confirm Password -->
      <div class="form-group">
        <label for="confirm_password">Confirm password</label>
        <div class="input-icon-wrap">
          <input type="password" id="confirm_password" name="confirm_password"
                 placeholder="Repeat your password" required />
          <button type="button" class="toggle-pass" onclick="togglePass('confirm_password', this)">👁</button>
        </div>
      </div>

     <button  type="submit" class="btn-submit">Create free account →</button>
    </form>

    <p class="terms">
      By signing up you agree to our
      <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
    </p>

  </div>
</main>

<script>
  /* Toggle password visibility */
  function togglePass(id, btn) {
    const input = document.getElementById(id);
    if (input.type === 'password') {
      input.type = 'text';
      btn.textContent = '🙈';
    } else {
      input.type = 'password';
      btn.textContent = '👁';
    }
  }

  /* Password strength indicator */
  function checkStrength(val) {
    const segs   = [s1, s2, s3, s4];
    const label  = document.getElementById('strength-label');
    const colors = ['#e05252','#e09a35','#1a9e8f','#127a6e'];
    const labels = ['Weak','Fair','Good','Strong'];

    let score = 0;
    if (val.length >= 8)            score++;
    if (/[A-Z]/.test(val))          score++;
    if (/[0-9]/.test(val))          score++;
    if (/[^A-Za-z0-9]/.test(val))   score++;

    segs.forEach((s, i) => {
      s.style.background = i < score ? colors[score - 1] : '#d1d8dd';
    });
    label.textContent = val.length ? labels[score - 1] || '' : '';
    label.style.color = score ? colors[score - 1] : '#7a8b96';
  }
</script>

</body>
</html>