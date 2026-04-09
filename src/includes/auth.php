<?php
// auth.php — Session helpers
// Provides functions to log in, log out, and protect pages.
// Usage: require_once __DIR__ . '/includes/auth.php';

// Start the session if it hasn't been started yet.
// Sessions let PHP remember who a user is across page requests.
if (session_status() === PHP_SESSION_NONE) {
    // Set secure cookie flags BEFORE starting the session.
    // - secure:   cookie only sent over HTTPS (prevents sniffing on HTTP)
    // - httponly:  cookie invisible to JavaScript (prevents XSS cookie theft)
    // - samesite:  Lax = cookie sent on same-site requests + top-level navigations
    session_set_cookie_params([
        'lifetime' => 0,        // session cookie — expires when browser closes
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),  // true on Heroku, false locally
        'httponly'  => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Check if a user is currently logged in.
 * Returns true if a user_id is stored in the session.
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']);
}

/**
 * Protect a page — redirect to login if the user is not logged in.
 * Call this at the top of any page that requires authentication.
 */
function require_login(): void {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Log in a user by storing their ID and username in the session.
 * Call this after verifying credentials in login.php.
 * $id is a UUID string (CHAR(36)) from the user table.
 */
function login_user(string $id, string $username): void {
    // Regenerate session ID after login — prevents session fixation attacks
    session_regenerate_id(true);
    $_SESSION['user_id']  = $id;
    $_SESSION['username'] = $username;
}

/**
 * Log out the current user — destroy the session entirely.
 */
function logout_user(): void {
    session_unset();
    session_destroy();
}
