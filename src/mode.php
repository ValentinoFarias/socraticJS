<?php
// mode.php — Mode picker: sits between practice.php and console.php.
// The user picks Study mode (pure JS) or Real mode (JS targeting HTML elements).
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

// Read the topic slug from the URL — passed through from practice.php
// e.g. practice.php links to mode.php?topic=variables-let-const
$slug  = isset($_GET['topic']) ? $_GET['topic'] : '';
$topic = $slug !== '' ? ucwords(str_replace('-', ' ', $slug)) : 'Unknown Topic';

// Build the destination URLs for each mode button.
// Study mode → consolenohtml.php (pure JS, no HTML panel)
// Real mode  → consolehtml.php  (JS targeting real HTML elements)
$study_url = '/consolenohtml.php?topic=' . urlencode($slug);
$real_url  = '/consolehtml.php?topic='  . urlencode($slug);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

  <!-- ── Mode picker ─────────────────────────────────────────────── -->
  <div class="mode__layout">

    <!-- Topic label — tells the user what they're about to practice -->
    <p class="mode__heading">How do you want to practice</p>
    <p class="mode__topic"><?= h($topic) ?>?</p>

    <!-- Two mode cards — clicking one navigates to console.php with that mode -->
    <div class="mode__options">

      <!-- Study mode: pure JS, no HTML. Focus on the concept itself. -->
      <a class="mode__card mode__card--study" href="<?= h($study_url) ?>">
        <span class="mode__icon">📖</span>
        <span class="mode__title">Study mode</span>
        <span class="mode__desc">
          Pure JavaScript — no HTML needed.<br>
          Focus on the concept with console.log() output.
        </span>
        <span class="mode__cta">Start studying →</span>
      </a>

      <!-- Real mode: JS targeting actual HTML elements, like a real project. -->
      <a class="mode__card mode__card--real" href="<?= h($real_url) ?>">
        <span class="mode__icon">🌐</span>
        <span class="mode__title">Real mode</span>
        <span class="mode__desc">
          Write JavaScript that targets real HTML elements.<br>
          See your changes live in the preview.
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
