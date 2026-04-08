<?php
// auth.php — Session helpers
// Provides functions to log in, log out, and protect pages.
// Usage: require_once __DIR__ . '/includes/auth.php';

// Start the session if it hasn't been started yet.
// Sessions let PHP remember who a user is across page requests.
if (session_status() === PHP_SESSION_NONE) {
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
 */
function login_user(int $id, string $username): void {
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
