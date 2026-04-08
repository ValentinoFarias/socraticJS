<?php
// register.php — New user registration page
// No auth required — this is how new users create an account.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// If already logged in, redirect away
if (is_logged_in()) {
    redirect('/study.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — SocraticJS</title>
  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
  <h1>Register</h1>
  <p>Registration form — coming soon.</p>
  <script src="/assets/js/main.js"></script>
</body>
</html>
