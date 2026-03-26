<!DOCTYPE html>
<html lang="en">
 
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>SalahHomty – Report, view, or discuss local problems</title>
 
  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css2?family=Source+Sans+3:wght@400;600;700&display=swap"
    rel="stylesheet"
  />
 
  <!-- App Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
 
<body>
 
  <!-- ══════════════════════════════════════════════════════
       TOP ANNOUNCEMENT BANNER
       Informs users this site is UK-specific.
       Dismissible via the Close button.
  ═══════════════════════════════════════════════════════ -->
  <div class="top-banner" id="top-banner">
    This site is for reporting <strong>problems in Sousse</strong>.
    There are SalahHomty sites <a href="#">all over the world</a>,
    or you could set up your own using the
    <a href="#">SalahHomty Platform</a>.
    <button
      class="top-banner__close"
      aria-label="Close announcement"
      onclick="document.getElementById('top-banner').style.display = 'none'"
    >
      Close
    </button>
  </div>
 
 
  <!-- ══════════════════════════════════════════════════════
       NAVIGATION BAR
       Contains: society badge, brand logo, and nav links.
  ═══════════════════════════════════════════════════════ -->
  <nav class="navbar" aria-label="Main navigation">
 
    <!-- Society shield badge -->
    <div class="navbar__badge" aria-hidden="true">
      <svg viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="14" cy="14" r="5" fill="#e63946" />
        <circle cx="30" cy="14" r="5" fill="#2a9d8f" />
        <circle cx="14" cy="30" r="5" fill="#e9c46a" />
        <circle cx="30" cy="30" r="5" fill="#457b9d" />
        <text
          x="22" y="43"
          text-anchor="middle"
          font-size="5.5"
          fill="#555"
          font-family="sans-serif"
          font-weight="bold"
        >
          mySociety
        </text>
      </svg>
    </div>
 
    <!-- Brand logotype -->
    <a href="#" class="navbar__brand" aria-label="SalahHomty home">
      <div class="navbar__brand-icon" aria-hidden="true">
        <!-- Wrench / tool icon -->
        
         <img src="teamlogo.png" alt="Your Logo" width="80" height="80" />
      </div>
        
      
      <span class="navbar__brand-name">
        <span class="word--salah">Salah</span><span class="word--homty">Homty</span>
      </span>
    </a>
 
    <!-- Push links to the right -->
    <div class="navbar__spacer"></div>
 
    <!-- Navigation links -->
    <div class="navbar__links">
      <span class="lang" role="button" tabindex="0">عربي</span>
      <a href="#">Sign in</a>
      <a href="#">All reports</a>
      <a href="#">Local alerts</a>
      <a href="#">Help</a>
    </div>
 
  </nav>
 
 
  <!-- ══════════════════════════════════════════════════════
       HERO SECTION
       Contains the page headline, address search, and the
       photo-upload drop zone.
  ═══════════════════════════════════════════════════════ -->
  <main>
    <section class="hero" aria-labelledby="hero-title">
 
      <h1 class="hero__title" id="hero-title">
        Report, view, or discuss local problems
      </h1>
      <p class="hero__subtitle">
        (like graffiti, fly tipping, broken paving slabs, or street lighting)
      </p>
 
      <div class="hero__grid">
 
        <!-- ── Search Column ───────────────────────── -->
        <div class="search">
          <label class="search__label" for="location-input">
            Enter a nearby UK postcode, or street name and area:
          </label>
          <p class="search__hint">e.g. 'B2 4QA' or 'Tib St, Manchester'</p>
 
          <div class="search__row">
            <input
              id="location-input"
              class="search__input"
              type="text"
              placeholder=""
              aria-label="Enter postcode or street name"
            />
            <button class="search__go-btn" type="button">Go</button>
          </div>
 
          <button class="search__location-btn" type="button" id="use-location-btn">
            <!-- Navigation / arrow icon -->
            <svg
              width="16" height="16"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2.5"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <polygon points="3 11 22 2 13 21 11 13 3 11" />
            </svg>
            Use my current location
          </button>
        </div><!-- /search -->
 
        <!-- ── Upload Column ───────────────────────── -->
        <div class="upload">
          <div class="upload__header">
            <span class="upload__badge">NEW</span>
            <span class="upload__label">Or start a report with a photo</span>
          </div>
 
          <!--
            Clicking the visible drop zone triggers the hidden
            <input type="file"> via JavaScript below.
          -->
          <div
            class="upload__dropzone"
            id="dropzone"
            role="button"
            tabindex="0"
            aria-label="Upload a photo to start a report"
          >
            <!-- Image / upload icon -->
            <svg
              width="32" height="32"
              viewBox="0 0 24 24"
              fill="none"
              stroke="#555"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
              <circle cx="8.5" cy="8.5" r="1.5" />
              <polyline points="21 15 16 10 5 21" />
              <polyline points="15 3 15 9 21 9" />
            </svg>
 
            <span class="upload__dropzone-text" id="dropzone-text">
              Browse or drag files here to upload
            </span>
 
            <!-- Hidden file input -->
            <input
              id="file-input"
              type="file"
              accept="image/*"
              style="display: none"
              aria-hidden="true"
            />
          </div>
        </div><!-- /upload -->
 
      </div><!-- /hero__grid -->
    </section>
 
 
    <!-- ══════════════════════════════════════════════════════
         INFO SECTION
         Left: How to report (4 steps).
         Right: Recently reported problems feed.
    ═══════════════════════════════════════════════════════ -->
    <section class="info-section" aria-label="How to report and recent problems">
      <div class="info-section__grid">
 
        <!-- ── How to Report ──────────────────────────── -->
        <div class="how-to">
          <h2 class="section-title">How to report a problem</h2>
          <ol class="how-to__steps">
            <li class="how-to__step">
              <span class="how-to__number" aria-hidden="true">1</span>
              <span class="how-to__text">Enter a nearby UK postcode, or street name and area</span>
            </li>
            <li class="how-to__step">
              <span class="how-to__number" aria-hidden="true">2</span>
              <span class="how-to__text">Locate the problem on a map of the area</span>
            </li>
            <li class="how-to__step">
              <span class="how-to__number" aria-hidden="true">3</span>
              <span class="how-to__text">Enter details of the problem</span>
            </li>
            <li class="how-to__step">
              <span class="how-to__number" aria-hidden="true">4</span>
              <span class="how-to__text">We send it to the council on your behalf</span>
            </li>
          </ol>
        </div><!-- /how-to -->
 
        <!-- ── Recently Reported Problems ────────────── -->
        <div class="recent">
          <h2 class="section-title">Recently reported problems</h2>
          <ul class="recent__list" aria-label="Recent problem reports">
 
            <li class="recent__item">
              <div class="recent__info">
                <a href="#" class="recent__title">Flytipping</a>
                <time class="recent__time">08:38 today</time>
              </div>
              <img
                class="recent__thumb"
                src="c:\Users\hamza\Downloads\files\Flytipping.jpg"
                alt="Flytipping report photo"
                width="80" height="60"
              />
            </li>
 
            <li class="recent__item">
              <div class="recent__info">
                <a href="#" class="recent__title">Dog Poo</a>
                <time class="recent__time">08:38 today, last updated 08:38 today</time>
              </div>
              <img
                class="recent__thumb"
                src="https://placehold.co/80x60/cccccc/888888?text=Photo"
                alt="Dog Poo report photo"
                width="80" height="60"
              />
            </li>
 
            <li class="recent__item">
              <div class="recent__info">
                <a href="#" class="recent__title">Large Potholes</a>
                <time class="recent__time">08:38 today, last updated 08:38 today</time>
              </div>
              <img
                class="recent__thumb"
                src="https://placehold.co/80x60/cccccc/888888?text=Photo"
                alt="Large Potholes report photo"
                width="80" height="60"
              />
            </li>
 
            <li class="recent__item">
              <div class="recent__info">
                <a href="#" class="recent__title">Tree branches in path</a>
                <time class="recent__time">08:37 today</time>
              </div>
              <img
                class="recent__thumb"
                src="https://placehold.co/80x60/cccccc/888888?text=Photo"
                alt="Tree branches report photo"
                width="80" height="60"
              />
            </li>
 
            <li class="recent__item">
              <div class="recent__info">
                <a href="#" class="recent__title">Blocked Drain</a>
                <time class="recent__time">08:37 today</time>
              </div>
              <img
                class="recent__thumb"
                src="https://placehold.co/80x60/cccccc/888888?text=Photo"
                alt="Blocked Drain report photo"
                width="80" height="60"
              />
            </li>
 
          </ul>
        </div><!-- /recent -->
 
      </div><!-- /info-section__grid -->
    </section>
 
 
    <!-- ══════════════════════════════════════════════════════
         AUDIENCE CARDS SECTION
         Two cards: For citizens | For councils.
         (The "For developers" card has been omitted as requested.)
    ═══════════════════════════════════════════════════════ -->
    <section class="cards-section" aria-label="Who SalahHomty is for">
      <div class="cards-section__grid">
 
        <!-- Card: Citizens -->
        <article class="card">
          <div class="card__header">
            <!-- People / group icon -->
            <svg
              class="card__icon"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
              <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            </svg>
            <h3 class="card__title">For citizens</h3>
          </div>
          <p class="card__body">
            Need to report a problem in your local area? Learn all about SalahHomty,
            how it works and what happens to your report once you've made it.
          </p>
          <a href="#" class="card__btn">Learn more</a>
        </article>
 
        <!-- Card: Councils -->
        <article class="card">
          <div class="card__header">
            <!-- Building / council icon -->
            <svg
              class="card__icon"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.8"
              stroke-linecap="round"
              stroke-linejoin="round"
              aria-hidden="true"
            >
              <rect x="2" y="7" width="20" height="14" rx="1" />
              <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
              <path d="M2 10h20" />
            </svg>
            <h3 class="card__title">For councils</h3>
          </div>
          <p class="card__body">
            Find the answers to some of our most frequently asked questions about how
            SalahHomty works for councils and discover your options for integrating
            with the service.
          </p>
          <a href="#" class="card__btn">Learn more</a>
        </article>
 
      </div><!-- /cards-section__grid -->
    </section>
 
  </main>
 
 
  <!-- ══════════════════════════════════════════════════════
       SCRIPTS
  ═══════════════════════════════════════════════════════ -->
  <script>
    /* ── Drop Zone Interactions ─────────────────────── */
    const dropzone     = document.getElementById('dropzone');
    const fileInput    = document.getElementById('file-input');
    const dropzoneText = document.getElementById('dropzone-text');
 
    /** Open the file picker when the drop zone is clicked or activated. */
    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') fileInput.click();
    });
 
    /** Show visual feedback while a file is dragged over the zone. */
    dropzone.addEventListener('dragover', (e) => {
      e.preventDefault();
      dropzone.style.borderColor  = '#1a1a1a';
      dropzone.style.backgroundColor = '#f0f0f0';
    });
 
    /** Reset visual state when the drag leaves. */
    dropzone.addEventListener('dragleave', () => {
      dropzone.style.borderColor     = '';
      dropzone.style.backgroundColor = '';
    });
 
    /** Handle a dropped file. */
    dropzone.addEventListener('drop', (e) => {
      e.preventDefault();
      dropzone.style.borderColor     = '';
      dropzone.style.backgroundColor = '';
 
      const file = e.dataTransfer.files[0];
      if (file) dropzoneText.textContent = `Selected: ${file.name}`;
    });
 
    /** Handle a file chosen via the native file picker. */
    fileInput.addEventListener('change', function () {
      if (this.files.length) {
        dropzoneText.textContent = `Selected: ${this.files[0].name}`;
      }
    });
 
    /* ── Geolocation ─────────────────────────────────── */
    const locationBtn = document.getElementById('use-location-btn');
 
    locationBtn.addEventListener('click', () => {
      if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
      }
      locationBtn.textContent = 'Locating…';
      navigator.geolocation.getCurrentPosition(
        (pos) => {
          const { latitude, longitude } = pos.coords;
          document.getElementById('location-input').value =
            `${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;
          locationBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polygon points="3 11 22 2 13 21 11 13 3 11"/>
            </svg>
            Use my current location`;
        },
        () => {
          alert('Unable to retrieve your location.');
          locationBtn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5"
                 stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <polygon points="3 11 22 2 13 21 11 13 3 11"/>
            </svg>
            Use my current location`;
        }
      );
    });
  </script>
 
</body>
<!-- ══════════════════════════════════════════════════════
     FOOTER
═══════════════════════════════════════════════════════ -->
<footer class="footer">

  <!-- Built by -->
  <div class="footer__col">
    <span class="footer__label">Built &amp; powered by</span>
    <span class="footer__brand">Sharp Minds Squad</span>
  </div>

  <!-- Divider -->
  <div class="footer__divider" aria-hidden="true"></div>

  <!-- Contact blurb -->
  <div class="footer__col footer__col--wide">
    <p class="footer__legal">
      <strong>SalahHomty</strong> is a community reporting platform built by
      <strong>Sharp Minds Squad</strong>. For any enquiries, reach us at
      <a href="mailto:hello@sharpmindssquad.com" class="footer__email">hello@sharpmindssquad.com</a>
      or
      <a href="mailto:support@sharpmindssquad.com" class="footer__email">support@sharpmindssquad.com</a>.
    </p>
  </div>

  <!-- Social icons -->
  <div class="footer__social" aria-label="Social media links">
    <a href="#" class="footer__social-link" aria-label="GitHub">
      <svg viewBox="0 0 24 24" fill="currentColor">
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
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68
                 l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
      </svg>
    </a>
    <a href="#" class="footer__social-link" aria-label="Facebook">
      <svg viewBox="0 0 24 24" fill="currentColor">
        <path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073c0 6.027 4.388 11.024 10.125
                 11.927v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0
                 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.887v2.267h3.328l-.532
                 3.49h-2.796V24C19.612 23.097 24 18.1 24 12.073z"/>
      </svg>
    </a>
  </div>

</footer>
</html>