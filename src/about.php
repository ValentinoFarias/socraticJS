<?php
// about.php — About page
// No auth required — visible to everyone.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>About — SocraticJS</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

  <!-- ── Navigation ─────────────────────────────────────────────── -->
  <nav class="nav">
    <a href = "/index.php"><img class="nav__logo" src="/assets/img/logo.png" alt="SocraticJS logo"></a>
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

  <!-- ── About text block ───────────────────────────────────────── -->
  <!--
    Four paragraphs taken directly from the Figma design.
    All white, bold, Source Code Pro — same style as the Figma text node.
  -->
  <main class="about__content">
    <p>Learn JavaScript by thinking and memorising.</p>

    <p>SocraticJS is a beginner-friendly platform that teaches JavaScript the way understanding actually forms — through questions, experimentation, and reflection. Instead of sitting through explanations and hoping something sticks, you're guided to figure things out yourself. That's the Socratic method, and it works.</p>

    <p>The platform combines two tools. The JS Tutor is an AI-powered companion that walks you through a structured 7-phase roadmap — from your very first variable to modern async patterns — always nudging you to think before it explains. The JS Console is where that thinking becomes real skill: write JavaScript in an interactive editor, run it instantly, and get immediate feedback in either a focused study environment or against real HTML elements, just like an actual project.</p>

    <p>No prior experience needed. Just curiosity, and a willingness to think out loud.</p>
  </main>

  <script src="/assets/js/main.js"></script>
</body>
</html>
