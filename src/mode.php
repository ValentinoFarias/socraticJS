<?php
// mode.php — Difficulty picker: sits between practice.php and the console pages.
// The user picks one of three difficulty levels for the chosen topic:
//   • Beginner     — pure JS, no HTML, console.log only       → consolenohtml.php
//   • Intermediate — HTML given, JS runs once, no events      → consolehtml.php
//   • Advanced     — HTML given, interaction required         → consolehtml.php
// The chosen level is forwarded via the `level` query param so the console
// pages can pass it to the exercise generator API.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

// Read the topic slug from the URL — passed through from practice.php
// e.g. practice.php links to mode.php?topic=variables-let-const
$slug  = isset($_GET['topic']) ? $_GET['topic'] : '';
$topic = $slug !== '' ? ucwords(str_replace('-', ' ', $slug)) : 'Unknown Topic';

// Build the destination URLs for each level button.
// We URL-encode the slug because topic names may contain characters that
// need escaping in a query string. Every link carries both `topic` and `level`.
// Beginner    → consolenohtml.php (no HTML panel — pure JS)
// Intermediate → consolehtml.php  (HTML given — no events)
// Advanced    → consolehtml.php  (HTML given — event-driven)
$beginner_url     = '/consolenohtml.php?topic=' . urlencode($slug) . '&level=beginner';
$intermediate_url = '/consolehtml.php?topic='   . urlencode($slug) . '&level=intermediate';
$advanced_url     = '/consolehtml.php?topic='   . urlencode($slug) . '&level=advanced';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include __DIR__ . '/includes/favicons.php'; ?>
  <title>Choose Mode — <?= h($topic) ?> — SocraticJS</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

  <!-- ── Navigation ─────────────────────────────────────────────── -->
  <nav class="nav">
    <a href="/index.php"><img class="nav__logo" src="/assets/img/logo.png" alt="SocraticJS logo"></a>
    <a class="nav__link" href="/about.php">about</a>
    <div class="nav__right">
      <?php if (is_logged_in()): ?>
        <span class="nav__greeting">Hi, <?= h($_SESSION["username"]) ?></span>
        <a class="nav__link nav__link--logout" href="/logout.php">logout</a>
      <?php else: ?>
        <a class="nav__link" href="/login.php">login</a>
        <a class="nav__link" href="/register.php">signUp</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ── Difficulty picker ───────────────────────────────────────── -->
  <div class="mode__layout">

    <!-- Topic label — tells the user what they're about to practice -->
    <p class="mode__heading">Pick a difficulty for</p>
    <p class="mode__topic"><?= h($topic) ?></p>

    <!-- Three level cards — clicking one navigates to the matching console
         page with both `topic` and `level` in the query string. -->
    <div class="mode__options mode__options--three">

      <!-- BEGINNER: pure JS only, no HTML/DOM. console.log exercises. -->
      <a class="mode__card mode__card--beginner" href="<?= h($beginner_url) ?>">
        <span class="mode__icon">🌱</span>
        <span class="mode__title">Beginner</span>
        <span class="mode__desc">
          Pure JavaScript — no HTML.<br>
          One concept, one console.log().
        </span>
        <span class="mode__cta">Start learning →</span>
      </a>

      <!-- INTERMEDIATE: HTML given, JS runs once on page load, no events.
           The learner touches the DOM (getElementById, textContent) but
           never wires up a click handler. -->
      <a class="mode__card mode__card--intermediate" href="<?= h($intermediate_url) ?>">
        <span class="mode__icon">🌿</span>
        <span class="mode__title">Intermediate</span>
        <span class="mode__desc">
          HTML is given. JS runs once on load.<br>
          Read or change elements — no click events.
        </span>
        <span class="mode__cta">Start practicing →</span>
      </a>

      <!-- ADVANCED: HTML given with buttons. Learner must wire up click
           events, update the DOM, and clear/re-render output. -->
      <a class="mode__card mode__card--advanced" href="<?= h($advanced_url) ?>">
        <span class="mode__icon">🌳</span>
        <span class="mode__title">Advanced</span>
        <span class="mode__desc">
          Interactive: buttons, click events,<br>
          DOM updates, clearing and re-rendering.
        </span>
        <span class="mode__cta">Start building →</span>
      </a>

    </div><!-- /.mode__options -->

    <!-- Back link — lets the user go back to the topic list -->
    <a class="mode__back" href="/practice.php">← back to topics</a>

  </div><!-- /.mode__layout -->

  <script src="/assets/js/main.js"></script>
</body>
</html>
