<?php
// csrf.php — CSRF (Cross-Site Request Forgery) protection helpers.
// CSRF attacks trick a logged-in user's browser into submitting a form
// on a malicious site that targets YOUR site. The token ensures the form
// submission actually came from a page YOU served.

/**
 * Get (or create) the CSRF token for the current session.
 * Embed this in every HTML form as a hidden input.
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        // random_bytes(32) = 32 cryptographically random bytes → 64 hex chars
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify the CSRF token from a POST request.
 * Call this at the top of every POST handler before processing the form.
 * Sends a 403 and stops execution if the token is missing or wrong.
 */
function csrf_verify(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}
