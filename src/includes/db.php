<?php
// db.php — Database connection helper
// Include this file wherever you need to talk to MySQL.
// Usage: require_once __DIR__ . '/includes/db.php';
// After including it, $pdo is available.

// PDO = PHP Data Objects — a safe, standard way to talk to databases.
// We pass options to turn on error reporting and use associative arrays for results.
$pdo = new PDO(
    // DSN (Data Source Name): tells PDO which database driver, host, and DB to use
    'mysql:host=db;dbname=jstutor;charset=utf8mb4',
    'jstutor_user',   // database username
    'jstutor_pass',   // database password
    [
        // Throw exceptions on SQL errors so we catch problems immediately
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Return rows as associative arrays: ['id' => 1, 'username' => 'alice']
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);
