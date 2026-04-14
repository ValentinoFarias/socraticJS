<?php
// index.php — Landing / home page
// No auth required — visible to everyone.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php include __DIR__ . '/includes/favicons.php'; ?>
  <title>SocraticJS — JavaScript Socratic Tutor</title>

  <!-- Source Code Pro: the monospace font used throughout the design -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="home">

  <!-- ── Navigation ─────────────────────────────────────────────── -->
  <nav class="nav">
    <a href="/index.php"><img class="nav__logo" src="/assets/img/logo.png" alt="SocraticJS logo"></a>
    <a class="nav__link" href="/about.php">about</a>
    <div class="nav__right">
      <?php if (is_logged_in()): ?>
        <span class="nav__greeting">Hi, <?= h($_SESSION["username"]) ?></span>
        <a class="nav__link nav__link--logout" href="/logout.php">logOut</a>
      <?php else: ?>
        <a class="nav__link" href="/login.php">logIn</a>
        <a class="nav__link" href="/register.php">signUp</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ── Hero title ─────────────────────────────────────────────── -->
  <h1 class="home__title">IA-Powered JavaScript Socratic Tutor</h1>

  <!-- ── Two computer CTAs ──────────────────────────────────────── -->
  <!--
    Each retro computer image acts as a clickable button.
    The label text is overlaid on the dark screen area of the computer.
  -->
  <div class="home__computers">

    <div class="home__computer home__computer--study" style="--computer-label-top: 38%; --computer-label-left: 50%;">
      <a class="home__computer-image-link" href="/study.php" aria-label="Go to study mode">
        <img src="/assets/img/studyPC.webp" alt="Study computer">
      </a>
    </div>

    <div class="home__computer home__computer--practice" style="--computer-label-top: 38%; --computer-label-left: 50%;">
      <a class="home__computer-image-link" href="/practice.php" aria-label="Go to practice mode">
        <img src="/assets/img/practicePC.webp" alt="Practice computer">
      </a>
    </div>

  </div>

  <!-- Custom cursor images (follow mouse over each computer) -->
  <img class="custom-cursor custom-cursor--study" src="/assets/img/redpill.png" alt="">
  <img class="custom-cursor custom-cursor--practice" src="/assets/img/bluepill.png" alt="">

  <script src="/assets/js/main.js"></script>
</body>
</html>
