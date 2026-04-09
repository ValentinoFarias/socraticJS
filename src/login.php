<?php
// login.php — User login page.
// Handles both showing the form (GET) and processing credentials (POST).

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in there's nothing to do here — send them to study
if (is_logged_in()) {
    redirect('/index.php');
}

$error    = '';   // single error string — we keep it vague on purpose (see below)
$old_email = '';  // re-fill the email field if login fails (never re-fill password)

// ── Only run the login logic when the form is submitted ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';  // do NOT trim passwords

    $old_email = $email; // remember so we can re-fill the field

    // Look up the user by email using a prepared statement.
    // The ? placeholder is filled in safely — PDO never lets $email touch the SQL string.
    $stmt = $pdo->prepare('SELECT * FROM user WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(); // returns an associative array, or false if not found

    // password_verify() takes the plain-text input and the stored hash and compares them.
    // It returns true only if they match. We never store or compare plain-text passwords.
    if ($user && password_verify($password, $user['password_hash'])) {

        // Credentials are correct — create the session.
        // login_user() is defined in auth.php: it regenerates the session ID
        // (prevents session fixation) then stores user_id and username in $_SESSION.
        login_user($user['id'], $user['username']);

        // Send the user to the study page now that they're logged in
        redirect('/index.php');

    } else {
        // Deliberately vague — we don't tell the user whether the email or
        // the password was wrong, because that would help an attacker enumerate accounts.
        $error = 'Invalid email or password.';
    }
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
      <?php if (is_logged_in()): ?>
        <span class="nav__greeting">Hi, <?= h($_SESSION["username"]) ?></span>
        <a class="nav__link nav__link--logout" href="/logout.php">logout</a>
      <?php else: ?>
        <a class="nav__link" href="/login.php">login</a>
        <a class="nav__link" href="/register.php">signUp</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ── Login card ─────────────────────────────────────────────── -->
  <div class="auth">
    <div class="auth__card">

      <h1 class="auth__title">login</h1>

      <?php if ($error !== ''): ?>
        <!-- h() escapes the error string so it can never inject HTML -->
        <p class="auth__error"><?= h($error) ?></p>
      <?php endif; ?>

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
            value="<?= h($old_email) ?>"
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
        don't have an account? <a href="/register.php">sign up</a>
      </p>

    </div>
  </div>

  <script src="/assets/js/main.js"></script>
</body>
</html>
