<?php
// functions.php — Shared utility functions
// Add small helper functions here that are used across multiple pages.

/**
 * Safely output a string in HTML — prevents XSS (cross-site scripting).
 * Always use this when printing user-supplied data into a page.
 *
 * Example: echo h($user['username']);
 */
function h(string $value): string {
    // htmlspecialchars converts characters like < > & " into safe HTML entities
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL and stop execution.
 * Cleaner than writing header() + exit everywhere.
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}
