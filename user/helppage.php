<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SalahHomty – Help Center</title>
  <link href="../assets/css/helppage.css" rel="stylesheet" />
  
</head>
<body>

<!-- ── NAVBAR ── -->
<nav>
  <a href="#" class="logo">
    <div class="logo-icon">ah</div>
    <span class="logo-text">Salah<span>Homty</span></span>
  </a>
  <div class="nav-links">
    <a href="../index.php" class="btn-report">Report an issue</a>
    <a href="signin.php">Sign In</a>
    <a href="history.php">History</a>
    <a href="profil.php">profil</a>
    <a href="helppage.php" class="active">Help</a>

  </div>
</nav>

<!-- ── HERO ── -->
<section class="hero">
  <div class="hero-chip">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    Help Center
  </div>
  <h1>How can we<br><span>help you?</span></h1>
  <p>Find answers to your questions, video guides, and contact our support team.</p>
  <div class="hero-search">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="heroSearch" placeholder="e.g., how to submit a complaint?" oninput="heroFilter()" />
    <button onclick="heroFilter()">Search</button>
  </div>
  <div class="hero-tags">
    <button onclick="jumpFaq('submit')">Submit a complaint</button>
    <button onclick="jumpFaq('status')">Track status</button>
    <button onclick="jumpFaq('account')">My account</button>
    <button onclick="jumpFaq('notif')">Notifications</button>
    <button onclick="jumpFaq('admin')">Administration</button>
  </div>
</section>

<!-- ── PAGE ── -->
<div class="page">

  <!-- SYSTEM STATUS -->
  <div class="status-banner">
    <div class="status-left">
      <span class="status-dot"></span>
      <div>
        <h4>All systems operational</h4>
        <p>Last check 3 minutes ago</p>
      </div>
    </div>
    <div class="status-right">
      <a href="#">View status page →</a>
    </div>
  </div>

  <!-- QUICK ACCESS -->
  <div class="section-title">Quick access</div>
  <div class="quick-grid">
    <a href="#faq-section" class="quick-card">
      <div class="quick-icon qi-teal">📋</div>
      <h3>FAQ</h3>
      <p>Answers to the most frequently asked questions about the platform.</p>
    </a>
    <a href="#video-section" class="quick-card">
      <div class="quick-icon qi-orange">🎬</div>
      <h3>Video guides</h3>
      <p>Step-by-step tutorials to use all features.</p>
    </a>
    <a href="#contact-section" class="quick-card">
      <div class="quick-icon qi-blue">💬</div>
      <h3>Contact us</h3>
      <p>Live chat, email or form — we respond quickly.</p>
    </a>
    <a href="#form-section" class="quick-card">
      <div class="quick-icon qi-purple">📨</div>
      <h3>Report a bug</h3>
      <p>A technical issue? Send us a detailed report.</p>
    </a>
  </div>

  <!-- FAQ -->
  <div id="faq-section" class="section-title">Frequently asked questions</div>
  <div class="faq-layout">
    <!-- Sidebar -->
    <aside class="faq-sidebar">
      <h4>Categories</h4>
      <div class="faq-cat active" data-cat="submit" onclick="selectCat(this)">
        📋 Submit <span class="badge">5</span>
      </div>
      <div class="faq-cat" data-cat="status" onclick="selectCat(this)">
        🔍 Track status <span class="badge">4</span>
      </div>
      <div class="faq-cat" data-cat="account" onclick="selectCat(this)">
        👤 My account <span class="badge">4</span>
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

      <!-- SUBMIT -->
      <div class="faq-section visible" id="sec-submit">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How do I submit a complaint?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Click on the <strong>"Report an issue"</strong> button at the top right. Fill out the form by providing:
            <ul>
              <li>The category of the problem (roadways, lighting, graffiti, etc.)</li>
              <li>A clear description of the issue</li>
              <li>The address or location on the map</li>
              <li>One or more photos (optional but recommended)</li>
            </ul>
            Submit to receive a unique tracking ID.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Do I need an account to submit a complaint?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            No, you can submit a complaint anonymously. However, creating an account allows you to <strong>track your complaints</strong>, receive notifications, and access your full history.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            What types of issues can I report?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            You can report any public space issue, including:
            <ul>
              <li>🛣️ Roadways: potholes, damaged sidewalks, missing signage</li>
              <li>💡 Lighting: broken streetlights, hazardous wires</li>
              <li>🎨 Graffiti: tags, facade damage</li>
              <li>🗑️ Waste: illegal dumping, overturned bins</li>
              <li>🌳 Green spaces: dangerous trees, broken equipment</li>
            </ul>
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            Can I edit a complaint after submission?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            You can modify the description or add photos as long as the complaint status is <strong>"Open"</strong>. Once taken over by an agent, only a supplementary comment is possible.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How do I add photos to my complaint?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            In the report form, click on the <strong>"Add photos"</strong> area. You can upload up to 5 images (JPG, PNG, max 10 MB each). Photos are very helpful to speed up the processing of your complaint.
          </div></div>
        </div>
      </div>

      <!-- STATUS -->
      <div class="faq-section" id="sec-status">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How can I track the status of my complaint?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Go to the <strong>"History"</strong> page in your personal space. Each complaint displays its real-time status: Open, In progress, Resolved, Transferred, or Overdue.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            What do the different statuses mean?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            <ul>
              <li>🟡 <strong>Open</strong>: complaint received, not yet taken in charge.</li>
              <li>🔵 <strong>In progress</strong>: an agent or service is working on it.</li>
              <li>🟢 <strong>Resolved</strong>: the issue has been addressed and closed.</li>
              <li>🟣 <strong>Transferred</strong>: redirected to another competent service.</li>
              <li>🔴 <strong>Overdue</strong>: the set processing deadline has been exceeded.</li>
            </ul>
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            What is the processing time for a complaint?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            The standard response time is <strong>7 business days</strong> for an initial reply. Resolution time depends on the complexity of the issue. Emergencies (immediate danger) are prioritized and handled within 24 hours.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            My complaint is marked "Overdue", what should I do?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            You can contact our support using the form below, quoting your <strong>Complaint ID</strong>. If you are an administrator, you can directly transfer the complaint to another service from the History page.
          </div></div>
        </div>
      </div>

      <!-- ACCOUNT -->
      <div class="faq-section" id="sec-account">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How do I create an account?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Click on <strong>"Login"</strong> then on "Create a free account". Provide your email and a secure password. A confirmation email will be sent to you.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            I forgot my password, how can I reset it?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            On the login page, click on <strong>"Forgot password"</strong>. Enter your email address and you will receive a password reset link valid for 30 minutes. Check your spam folder if you don't see it.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How can I change my personal information?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Go to <strong>My Profile</strong> via the icon at the top right. You can update your first name, last name, email, phone number, and neighborhood of residence.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How do I delete my account?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Account deletion is available in <strong>Settings → Privacy → Delete Account</strong>. This action is irreversible. Your anonymized complaints will remain in the database for statistical purposes.
          </div></div>
        </div>
      </div>

      <!-- NOTIFICATIONS -->
      <div class="faq-section" id="sec-notif">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How do I enable notifications?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            In <strong>Settings → Notifications</strong>, you can enable email and/or push alerts for each status change of your complaints. You also receive local alerts about issues reported in your neighborhood.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            I no longer receive notification emails, why?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            First, check your <strong>spam/junk</strong> folder. Next, ensure notifications are enabled in your settings. If the problem persists, contact our support with your email address.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            What are "Local alerts"?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            Local alerts inform you in real-time about issues reported within a radius you define around your home (500 m to 5 km). Very useful for keeping track of your neighborhood's condition.
          </div></div>
        </div>
      </div>

      <!-- ADMIN -->
      <div class="faq-section" id="sec-admin">
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How do I access the administrator dashboard?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            The dashboard is only accessible to accounts with the <strong>Administrator</strong> or <strong>Agent</strong> role. Log in and click on the grid icon at the top right to access the back office.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How do I transfer a complaint to another service?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            On the <strong>History</strong> page, identify the complaint to transfer (indicated by "Overdue" status or manually). Click <strong>"Transfer"</strong>, choose the destination service or agent, add a note, and confirm.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How do I manage processing deadlines?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            In <strong>Settings → Deadlines</strong>, you can configure alert thresholds by complaint category (e.g., 3 days for lighting, 7 days for roadways). An automatic notification is sent to the responsible agent when the deadline approaches.
          </div></div>
        </div>
        <div class="faq-item">
          <div class="faq-q" onclick="toggleFaq(this)">
            How do I export complaint data?
            <div class="faq-chevron"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></div>
          </div>
          <div class="faq-a"><div class="faq-a-inner">
            From the History page, click <strong>"Export CSV"</strong>. You can filter by date, status, or category before exporting. The CSV file is compatible with Excel, LibreOffice, and any BI tool.
          </div></div>
        </div>
      </div>

    </div><!-- end faq-list -->
  </div><!-- end faq-layout -->

  <!-- VIDEO GUIDES -->
  <div id="video-section" class="section-title">Video guides</div>
  <div class="videos-grid">
    <div class="video-card" onclick="showToast('Playing video...')">
      <div class="video-thumb">
        <span class="emo">📋</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Submit your first complaint</h4>
        <p>Complete guide to report an issue in less than 2 minutes.</p>
        <span class="video-dur">⏱ 2 min 15 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Playing video...')">
      <div class="video-thumb">
        <span class="emo">🗂️</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>View your complaint history</h4>
        <p>Understand statuses, filters, and available actions.</p>
        <span class="video-dur">⏱ 3 min 40 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Playing video...')">
      <div class="video-thumb">
        <span class="emo">🔔</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Configure local alerts</h4>
        <p>Stay informed about issues in your neighborhood.</p>
        <span class="video-dur">⏱ 1 min 58 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Playing video...')">
      <div class="video-thumb">
        <span class="emo">🛡️</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Back-office overview</h4>
        <p>Guide for agents and administrators of the platform.</p>
        <span class="video-dur">⏱ 5 min 20 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Playing video...')">
      <div class="video-thumb">
        <span class="emo">⇄</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Transfer a complaint</h4>
        <p>How to reassign an overdue complaint to another service.</p>
        <span class="video-dur">⏱ 2 min 05 s</span>
      </div>
    </div>
    <div class="video-card" onclick="showToast('Playing video...')">
      <div class="video-thumb">
        <span class="emo">📊</span>
        <div class="play-btn"><span>▶</span></div>
      </div>
      <div class="video-info">
        <h4>Export and analyze data</h4>
        <p>Use CSV export for your monthly reports.</p>
        <span class="video-dur">⏱ 3 min 12 s</span>
      </div>
    </div>
  </div>

  <!-- CONTACT US -->
  <div id="contact-section" class="section-title">Contact us</div>
  <div class="contact-grid">
    <div class="contact-card">
      <div class="contact-icon">💬</div>
      <h3>Live chat</h3>
      <p>Chat with a support team member in real-time. Available Monday to Friday.</p>
      <span class="contact-badge cb-green">🟢 Online now</span>
      <button class="contact-btn" onclick="showToast('Opening chat...')">Start chat</button>
    </div>
    <div class="contact-card">
      <div class="contact-icon">📧</div>
      <h3>Email</h3>
      <p>Send us a detailed message. We respond within 24 business hours. Ideal for complex questions.</p>
      <span class="contact-badge cb-orange">⏱ Response within 24h</span>
      <a class="contact-btn outline" href="mailto:support@salahhomty.tn">support@salahhomty.tn</a>
    </div>
    <div class="contact-card">
      <div class="contact-icon">📞</div>
      <h3>Phone</h3>
      <p>For emergencies or critical situations. Our line is available during business hours.</p>
      <span class="contact-badge cb-blue">📞 Mon–Fri 8am–5pm</span>
      <a class="contact-btn outline" href="tel:+21671234567">+216 71 234 567</a>
    </div>
  </div>

  <!-- CONTACT FORM -->
  <div id="form-section" class="section-title">Send a message</div>
  <div class="form-section">
    <div class="form-left">
      <h3>A question? A bug?<br>Write to us.</h3>
      <p>Our team reviews every message carefully. Please provide as much detail as possible for faster processing.</p>

      <div class="form-info-item">
        <div class="ico">⚡</div>
        <div>
          <h5>Fast response</h5>
          <p>Average response time: less than 4 hours on business days.</p>
        </div>
      </div>
      <div class="form-info-item">
        <div class="ico">🔒</div>
        <div>
          <h5>Secure data</h5>
          <p>Your information is processed in compliance with GDPR.</p>
        </div>
      </div>
      <div class="form-info-item">
        <div class="ico">🌍</div>
        <div>
          <h5>Multilingual support</h5>
          <p>We respond in both French and Arabic.</p>
        </div>
      </div>
    </div>

    <div class="form-right">
      <div class="form-row">
        <div class="field">
          <label>First name</label>
          <input type="text" placeholder="Your first name" />
        </div>
        <div class="field">
          <label>Last name</label>
          <input type="text" placeholder="Your last name" />
        </div>
      </div>
      <div class="field">
        <label>Email address</label>
        <input type="email" placeholder="you@example.com" />
      </div>
      <div class="field">
        <label>Subject</label>
        <select>
          <option value="">Select a subject</option>
          <option>General question</option>
          <option>Technical issue / Bug</option>
          <option>My complaint is not progressing</option>
          <option>Administrator access request</option>
          <option>Improvement suggestion</option>
          <option>Other</option>
        </select>
      </div>
      <div class="field">
        <label>Complaint ID (optional)</label>
        <input type="text" placeholder="e.g., REC-0041" />
      </div>
      <div class="field">
        <label>Message</label>
        <textarea placeholder="Describe your issue or question in detail…"></textarea>
      </div>
      <button class="submit-btn" onclick="submitForm()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        Send message
      </button>
    </div>
  </div>

</div><!-- end page -->

<!-- FOOTER -->
<footer>
  <p>© 2025 SalahHomty — <a href="#">Terms of use</a> · <a href="#">Privacy policy</a> · <a href="#">Service status</a></p>
</footer>

<!-- TOAST -->
<div class="toast" id="toast">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1abc9c" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <span id="toastMsg">Message sent!</span>
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
      { words: ['submit', 'report', 'photo', 'category', 'type'], cat: 'submit' },
      { words: ['status', 'deadline', 'delay', 'overdue', 'track'], cat: 'status' },
      { words: ['account', 'password', 'profile', 'create', 'delete'], cat: 'account' },
      { words: ['notif', 'alert', 'email', 'mail'], cat: 'notif' },
      { words: ['admin', 'agent', 'transfer', 'export', 'back'], cat: 'admin' },
    ];
    for (const entry of map) {
      if (entry.words.some(w => q.includes(w))) {
        jumpFaq(entry.cat);
        return;
      }
    }
    jumpFaq('submit'); // default
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
    showToast('Message sent! We will respond within 24 hours.');
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