<?php
// logout.php — Destroys the user session and redirects to the home page.
// No HTML needed — this page just processes and redirects.

// Load the session helpers (starts the session and defines logout_user / redirect)
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

// logout_user() does two things:
//   1. session_unset()  — clears all variables stored in $_SESSION
//   2. session_destroy() — deletes the session file on the server
// After this call the user is no longer recognised by PHP
logout_user();

// Send the user back to the home page.
// redirect() calls header('Location: ...') + exit so nothing else runs.
redirect('/index.php');
