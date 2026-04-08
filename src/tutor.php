<?php
// tutor.php — JS Tutor chat interface
// TODO: restore require_login() once auth is built
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Read the ?topic= slug from the URL (e.g. "what-is-a-variable")
// and turn it into a readable title (e.g. "What Is A Variable")
$slug  = isset($_GET['topic']) ? $_GET['topic'] : '';
// Replace hyphens with spaces, then capitalise each word
$topic = $slug !== '' ? ucwords(str_replace('-', ' ', $slug)) : 'Unknown Topic';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JS Tutor — <?= h($topic) ?> — SocraticJS</title>

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
    <a class="nav__link nav__link--right" href="/login.php">login/SignUp</a>
  </nav>

  <!-- ── Topic title ────────────────────────────────────────────── -->
  <!--
    The topic comes from the ?topic= URL parameter set in study.php.
    h() escapes it so no user input can inject HTML.
  -->
  <p class="tutor__topic">You have selected <?= h($topic) ?></p>

  <!-- ── Chat area ──────────────────────────────────────────────── -->
  <div class="tutor__chat">
    <p class="tutor__placeholder">Conversention starts here....</p>
  </div>

  <script src="/assets/js/main.js"></script>
</body>
</html>
