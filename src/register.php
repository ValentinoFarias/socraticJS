<?php
// register.php — New user registration page
// No auth required — this is how new users create an account.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$error = '';

// Only run when the form is submitted (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // TODO: validate inputs, check username/email not taken, hash password, insert into DB
    // For now, just show a placeholder message so the form is testable
    $error = 'Registration not yet implemented.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign Up — SocraticJS</title>

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

  <!-- ── Register card ──────────────────────────────────────────── -->
  <div class="auth">
    <div class="auth__card">

      <h1 class="auth__title">sign up</h1>

      <?php if ($error !== ''): ?>
        <p class="auth__error"><?= h($error) ?></p>
      <?php endif; ?>

      <form action="" method="post">

        <div class="auth__group">
          <label class="auth__label" for="username">username</label>
          <input
            class="auth__input"
            type="text"
            id="username"
            name="username"
            required
            autocomplete="username"
            placeholder="socrates"
          >
        </div>

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
            autocomplete="new-password"
            placeholder="••••••••"
          >
        </div>

        <div class="auth__group">
          <label class="auth__label" for="password_confirm">confirm password</label>
          <input
            class="auth__input"
            type="password"
            id="password_confirm"
            name="password_confirm"
            required
            autocomplete="new-password"
            placeholder="••••••••"
          >
        </div>

        <button class="auth__submit" type="submit">create account</button>

      </form>

      <p class="auth__footer">
        already have an account? <a href="/login.php">log in</a>
      </p>

    </div>
  </div>

  <script src="/assets/js/main.js"></script>
</body>
</html>
