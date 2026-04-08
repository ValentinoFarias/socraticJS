<?php
// register.php — New user registration page.
// Handles both showing the form (GET) and processing it (POST).

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// If the user is already logged in, no point being here — send them to study
if (is_logged_in()) {
    redirect('/study.php');
}

// $errors collects every validation problem found so we can show them all at once
$errors = [];

// $old holds submitted values so we can re-fill the form after a failed attempt
// (we never re-fill password fields — that would be a security bad practice)
$old = ['username' => '', 'email' => ''];

// ── Only run the registration logic when the form is submitted ─────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Collect raw input from the form
    // trim() removes accidental leading/trailing spaces from text fields
    $username         = trim($_POST['username']         ?? '');
    $email            = trim($_POST['email']            ?? '');
    $password         = $_POST['password']              ?? '';  // do NOT trim passwords
    $password_confirm = $_POST['password_confirm']      ?? '';

    // Keep these so we can re-populate the form if validation fails
    $old['username'] = $username;
    $old['email']    = $email;

    // ── Validation ────────────────────────────────────────────────
    // We collect ALL errors before stopping, so the user sees everything at once

    if ($username === '') {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) > 50) {
        // The DB column is VARCHAR(50) — enforce the same limit here
        $errors[] = 'Username must be 50 characters or fewer.';
    }

    if ($email === '') {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // FILTER_VALIDATE_EMAIL checks the email format (e.g. user@example.com)
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if ($password !== $password_confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // ── Database uniqueness checks (only if basic validation passed) ──────
    if (empty($errors)) {

        // Check if this username is already taken.
        // We use a prepared statement — NEVER put $username directly in the SQL string.
        // Prepared statements separate the query from the data, preventing SQL injection.
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $errors[] = 'That username is already taken.';
        }

        // Check if this email is already registered
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    // ── Create the account (only if everything passed) ────────────────────
    if (empty($errors)) {

        // password_hash() runs the password through bcrypt — a slow hashing algorithm
        // designed to make brute-force attacks expensive.
        // PASSWORD_DEFAULT always picks the strongest algorithm PHP supports.
        // We NEVER store the plain-text password.
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // INSERT the new user. The ? placeholders are filled in safely by PDO.
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)'
        );
        $stmt->execute([$username, $email, $password_hash]);

        // Account created — send the user to login so they can sign in
        redirect('/login.php');
    }
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
      <?php if (is_logged_in()): ?>
        <span class="nav__greeting">Hi, <?= h($_SESSION["username"]) ?></span>
        <a class="nav__link nav__link--logout" href="/logout.php">logout</a>
      <?php else: ?>
        <a class="nav__link" href="/login.php">login</a>
        <a class="nav__link" href="/register.php">signUp</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ── Register card ──────────────────────────────────────────── -->
  <div class="auth">
    <div class="auth__card">

      <h1 class="auth__title">sign up</h1>

      <?php if (!empty($errors)): ?>
        <!-- Show every validation error — PHP populated $errors above -->
        <ul class="auth__errors">
          <?php foreach ($errors as $err): ?>
            <li><?= h($err) ?></li>
          <?php endforeach; ?>
        </ul>
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
            maxlength="50"
            value="<?= h($old['username']) ?>"
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
            value="<?= h($old['email']) ?>"
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
            placeholder="min 8 characters"
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
