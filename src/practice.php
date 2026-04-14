<?php
// practice.php — JS Console topic picker
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login(); // Redirect to login.php if the user is not logged in
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include __DIR__ . '/includes/favicons.php'; ?>
  <title>Practice — SocraticJS</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Coming+Soon&family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

  <!-- ── Navigation ─────────────────────────────────────────────── -->
  <nav class="nav">
    <a href="/index.php"><img class="nav__logo" src="/assets/img/logo.png" alt="SocraticJS logo"></a>
    <a class="nav__link" href="/about.php">about</a>
    <div class="nav__right">
      <?php if (is_logged_in()): ?>
        <!-- Logged-in state: greet the user and offer a logout link -->
        <span class="nav__greeting">Hi, <?= h($_SESSION["username"]) ?></span>
        <a class="nav__link nav__link--logout" href="/logout.php">logout</a>
      <?php else: ?>
        <!-- Logged-out state: show login and register links -->
        <a class="nav__link" href="/login.php">login</a>
        <a class="nav__link" href="/register.php">signUp</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ── Page subtitle ──────────────────────────────────────────── -->
  <p class="practice__subtitle">What do you want to practice today?</p>

  <!-- ── Challenge list — all 7 phases ─────────────────────────── -->
  <!--
    Each phase is a collapsible <details> block.
    Items link to console.php?topic=<slug>.
    Numbered lists match the Figma design.
  -->
  <main class="practice__roadmap">

    <!-- Phase 1 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 1 — The Very Basics</summary>
      <ol class="practice-list">
        <li><a href="/mode.php?topic=variables-let-const">Variables — let/const</a></li>
        <li><a href="/mode.php?topic=typeof">typeof</a></li>
        <li><a href="/mode.php?topic=type-coercion">Type coercion</a></li>
        <li><a href="/mode.php?topic=template-literals">Template literals</a></li>
        <li><a href="/mode.php?topic=equality">== vs ===</a></li>
        <li><a href="/mode.php?topic=nullish-coalescing">Nullish coalescing ??</a></li>
      </ol>
    </details>

    <!-- Phase 2 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 2 — Control Flow</summary>
      <ol class="practice-list">
        <li><a href="/mode.php?topic=if-else">if / else</a></li>
        <li><a href="/mode.php?topic=switch">switch</a></li>
        <li><a href="/mode.php?topic=ternary">Ternary operator</a></li>
        <li><a href="/mode.php?topic=for-loop">for loop</a></li>
        <li><a href="/mode.php?topic=while-loop">while loop</a></li>
        <li><a href="/mode.php?topic=for-of">for...of</a></li>
        <li><a href="/mode.php?topic=for-in">for...in</a></li>
      </ol>
    </details>

    <!-- Phase 3 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 3 — Functions</summary>
      <ol class="practice-list">
        <li><a href="/mode.php?topic=function-declaration">Function declaration</a></li>
        <li><a href="/mode.php?topic=function-expression">Function expression</a></li>
        <li><a href="/mode.php?topic=arrow-functions">Arrow functions</a></li>
        <li><a href="/mode.php?topic=default-parameters">Default parameters</a></li>
        <li><a href="/mode.php?topic=return">Return statement</a></li>
        <li><a href="/mode.php?topic=callbacks">Callback functions</a></li>
        <li><a href="/mode.php?topic=scope">Scope</a></li>
      </ol>
    </details>

    <!-- Phase 4 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 4 — Arrays &amp; Objects</summary>
      <ol class="practice-list">
        <li><a href="/mode.php?topic=arrays-basics">Creating arrays</a></li>
        <li><a href="/mode.php?topic=push-pop">push / pop / shift / unshift</a></li>
        <li><a href="/mode.php?topic=foreach">forEach</a></li>
        <li><a href="/mode.php?topic=map">map</a></li>
        <li><a href="/mode.php?topic=filter">filter</a></li>
        <li><a href="/mode.php?topic=reduce">reduce</a></li>
        <li><a href="/mode.php?topic=objects-key-value">Objects — key/value</a></li>
        <li><a href="/mode.php?topic=object-methods">Object.keys/values/entries</a></li>
        <li><a href="/mode.php?topic=destructuring">Destructuring</a></li>
        <li><a href="/mode.php?topic=spread">Spread operator</a></li>
      </ol>
    </details>

    <!-- Phase 5 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 5 — The DOM &amp; Events</summary>
      <ol class="practice-list">
        <li><a href="/mode.php?topic=getelementbyid">getElementById</a></li>
        <li><a href="/mode.php?topic=queryselector">querySelector</a></li>
        <li><a href="/mode.php?topic=textcontent">textContent</a></li>
        <li><a href="/mode.php?topic=changing-styles">Changing styles</a></li>
        <li><a href="/mode.php?topic=classlist">classList</a></li>
        <li><a href="/mode.php?topic=createelement">createElement</a></li>
        <li><a href="/mode.php?topic=input-event">input event</a></li>
        <li><a href="/mode.php?topic=event-object">Event object</a></li>
        <li><a href="/mode.php?topic=preventdefault">preventDefault</a></li>
      </ol>
    </details>

    <!-- Phase 6 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 6 — Async JavaScript</summary>
      <ol class="practice-list">
        <li><a href="/mode.php?topic=settimeout">setTimeout</a></li>
        <li><a href="/mode.php?topic=setinterval">setInterval</a></li>
        <li><a href="/mode.php?topic=promises">Promises</a></li>
        <li><a href="/mode.php?topic=async-await">async/await</a></li>
        <li><a href="/mode.php?topic=fetch-json">fetch + JSON</a></li>
      </ol>
    </details>

    <!-- Phase 7 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 7 — Advanced &amp; Modern JS</summary>
      <ol class="practice-list">
        <li><a href="/mode.php?topic=closures">Closures</a></li>
        <li><a href="/mode.php?topic=this">this keyword</a></li>
        <li><a href="/mode.php?topic=classes">Classes</a></li>
        <li><a href="/mode.php?topic=inheritance">Inheritance</a></li>
        <li><a href="/mode.php?topic=es-modules">ES Modules</a></li>
        <li><a href="/mode.php?topic=error-handling">Error handling</a></li>
        <li><a href="/mode.php?topic=event-loop">Event loop</a></li>
      </ol>
    </details>

  </main>

  <!-- Sticky note reminder -->
  <div class="study__sticky-note">
    Remember to check off the topics you have practiced!
  </div>

  <script src="/assets/js/main.js"></script>

  <script>
    // ── Topic checkboxes — "mark as already practiced" ─────────────
    //
    // Same pattern as study.php: checkboxes are injected dynamically,
    // state is persisted to MySQL via /api/progress.php.
    // Each checkbox is keyed by the lesson slug (the ?topic= URL value).

    // Step 1 — inject a checkbox before every topic link and wire up changes.
    document.querySelectorAll('.practice-list a').forEach(function (link) {
      // Extract the slug from the href, e.g. "/mode.php?topic=for-loop" → "for-loop"
      var slug = new URL(link.href, location.origin).searchParams.get('topic');
      if (!slug) return;

      var checkbox          = document.createElement('input');
      checkbox.type         = 'checkbox';
      checkbox.className    = 'topic-check';
      checkbox.title        = 'Mark as practiced';
      checkbox.dataset.slug = slug;   // used by loadProgress() to find this checkbox

      // Update UI instantly on change, then persist to the DB.
      checkbox.addEventListener('change', function () {
        var isChecked = this.checked;

        if (isChecked) {
          link.classList.add('topic--studied');
        } else {
          link.classList.remove('topic--studied');
        }

        // Save the new state to the DB — fire and forget
        saveProgress(slug, isChecked);
      });

      link.parentElement.insertBefore(checkbox, link);
    });

    // Step 2 — fetch the user's existing progress and tick the matching boxes.
    loadProgress();

    // ── loadProgress() ──────────────────────────────────────────────
    // GET /api/progress.php → { studied: ["slug-a", "slug-b", ...] }
    // Ticks every checkbox whose slug appears in the response.
    async function loadProgress() {
      try {
        // ?mode=practice tells the API to query the practice_progress table
        var res  = await fetch('/api/progress.php?mode=practice');
        var data = await res.json();
        var studied = data.studied || [];

        studied.forEach(function (slug) {
          var checkbox = document.querySelector('.topic-check[data-slug="' + slug + '"]');
          if (!checkbox) return;

          checkbox.checked = true;

          var link = checkbox.nextElementSibling;
          if (link) link.classList.add('topic--studied');
        });

      } catch (e) {
        console.warn('Could not load progress from server:', e);
      }
    }

    // ── saveProgress() ──────────────────────────────────────────────
    // POST /api/progress.php with { slug, studied: true|false }
    // Called on every checkbox change.
    async function saveProgress(slug, studied) {
      try {
        await fetch('/api/progress.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ slug: slug, studied: studied, mode: 'practice' }),
        });
      } catch (e) {
        console.warn('Could not save progress to server:', e);
      }
    }
  </script>
</body>
</html>
