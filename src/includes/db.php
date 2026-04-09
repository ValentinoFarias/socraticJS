<?php
// db.php — Database connection helper
// Include this file wherever you need to talk to MySQL.
// Usage: require_once __DIR__ . '/includes/db.php';
// After including it, $pdo is available.
 
// On Heroku, JawsDB provides a JAWSDB_URL environment variable in the format:
// mysql://user:password@host:3306/dbname
// Locally, we fall back to the Docker Compose credentials.
$url = getenv('JAWSDB_MARIA_URL') ?: getenv('JAWSDB_URL');
 
if ($url) {
    // ── Heroku / JawsDB ───────────────────────────────────────────
    $db     = parse_url($url);
    $host   = $db['host'];
    $dbname = ltrim($db['path'], '/');
    $user   = $db['user'];
    $pass   = $db['pass'];
} else {
    // ── Local Docker Compose fallback ─────────────────────────────
    $host   = 'db';
    $dbname = 'jstutor';
    $user   = 'jstutor_user';
    $pass   = 'jstutor_pass';
}
 
// PDO = PHP Data Objects — a safe, standard way to talk to databases.
// We pass options to turn on error reporting and use associative arrays for results.
$pdo = new PDO(
    // DSN (Data Source Name): tells PDO which database driver, host, and DB to use
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $user,
    $pass,
    [
        // Throw exceptions on SQL errors so we catch problems immediately
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Return rows as associative arrays: ['id' => 1, 'username' => 'alice']
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
 