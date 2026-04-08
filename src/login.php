<?php
// login.php — User login page
// No auth required — this is how users authenticate.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';

// Only run when the form is submitted (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: validate credentials against the database and call login_user()
    // For now, just show a placeholder error so the form is testable
    $error = 'Login not yet implemented.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — SocraticJS</title>

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
      <a class="nav__link" href="/login.php">login</a>
      <a class="nav__link" href="/register.php">signUp</a>
    </div>
  </nav>

  <!-- ── Login card ─────────────────────────────────────────────── -->
  <div class="auth">
    <div class="auth__card">

      <h1 class="auth__title">login</h1>

      <?php if ($error !== ''): ?>
        <!-- Show error message if login failed -->
        <p class="auth__error"><?= h($error) ?></p>
      <?php endif; ?>

      <!--
        action="" posts to the same page.
        method="post" so credentials are not visible in the URL.
      -->
      <form action="" method="post">

        <div class="auth__group">
          <label class="auth__label" for="email">email</label>
          <input
            class="auth__input"
            type="email"
            id="email"
            name="email"
            required
            autocomplete="email"
            placeholder="you@example.com"
          >
        </div>

        <div class="auth__group">
          <label class="auth__label" for="password">password</label>
          <input
            class="auth__input"
            type="password"
            id="password"
            name="password"
            required
            autocomplete="current-password"
            placeholder="••••••••"
          >
        </div>

        <button class="auth__submit" type="submit">log in</button>

      </form>

      <p class="auth__footer">
        no account? <a href="/register.php">sign up</a>
      </p>

    </div>
  </div>

  <script src="/assets/js/main.js"></script>
</body>
</html>
