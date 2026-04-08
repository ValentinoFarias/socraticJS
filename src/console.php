<?php
// console.php — JS Console coding challenge
// TODO: restore require_login() once auth is built
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// Read the ?topic= slug from the URL (e.g. "variables-let-const")
// and turn it into a readable title (e.g. "Variables Let Const")
$slug  = isset($_GET['topic']) ? $_GET['topic'] : '';
$topic = $slug !== '' ? ucwords(str_replace('-', ' ', $slug)) : 'Unknown Topic';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JS Console — <?= h($topic) ?> — SocraticJS</title>

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
  <p class="console__topic">You have selected <?= h($topic) ?></p>

  <!-- ── Exercise area ──────────────────────────────────────────── -->
  <div class="console__area">
    <p class="console__placeholder">Exercises start here...</p>
  </div>

  <script src="/assets/js/main.js"></script>
</body>
</html>
