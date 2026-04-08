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
    <a class="nav__link nav__link--right" href="/login.php">login/SignUp</a>
  </nav>

  <!-- ── Hero title ─────────────────────────────────────────────── -->
  <h1 class="home__title">JavaScript Socratic Tutor</h1>

  <!-- ── Two computer CTAs ──────────────────────────────────────── -->
  <!--
    Each retro computer image acts as a clickable button.
    The label text is overlaid on the dark screen area of the computer.
  -->
  <div class="home__computers">

    <div class="home__computer">
      <img src="/assets/img/computer.png" alt="Retro computer">
      <a class="home__computer-link" href="/study.php">Study</a>
    </div>

    <div class="home__computer">
      <img src="/assets/img/computer.png" alt="Retro computer">
      <a class="home__computer-link" href="/practice.php">Practice</a>
    </div>

  </div>

  <script src="/assets/js/main.js"></script>
</body>
</html>
