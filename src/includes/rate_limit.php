<?php
// rate_limit.php — Simple session-based API rate limiter.
// Prevents a single user from hammering the Anthropic API endpoints.
// Tracks request timestamps in the session and rejects if too many in the window.

/**
 * Enforce a rate limit on the current session.
 * @param int $max_requests  Maximum requests allowed in the time window
 * @param int $window_seconds  Time window in seconds (default: 60 = 1 minute)
 */
function rate_limit(int $max_requests = 20, int $window_seconds = 60): void {
    $now = time();

    // Clean out timestamps older than the window
    $_SESSION['api_calls'] = array_values(array_filter(
        $_SESSION['api_calls'] ?? [],
        function ($t) use ($now, $window_seconds) {
            return ($now - $t) < $window_seconds;
        }
    ));

    // Check if over the limit
    if (count($_SESSION['api_calls']) >= $max_requests) {
        http_response_code(429);
        echo json_encode(['error' => 'Rate limit exceeded. Please wait a minute.']);
        exit;
    }

    // Record this request
    $_SESSION['api_calls'][] = $now;
}
