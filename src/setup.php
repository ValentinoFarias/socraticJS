<?php
// setup.php — One-time database schema setup for Heroku deployment
// After visiting this page once, DELETE this file and push to GitHub.
// This is a temporary setup file used only during initial Heroku deployment.

require_once __DIR__ . '/includes/db.php';

try {
    // Create all 6 tables needed for SocraticJS
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user (
            id CHAR(36) PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS phase (
            id INT PRIMARY KEY,
            title VARCHAR(100) NOT NULL
        );

        CREATE TABLE IF NOT EXISTS topic (
            id CHAR(36) PRIMARY KEY,
            phase_id INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            FOREIGN KEY (phase_id) REFERENCES phase(id)
        );

        CREATE TABLE IF NOT EXISTS lesson (
            id CHAR(36) PRIMARY KEY,
            slug VARCHAR(100) NOT NULL,
            mode ENUM('study', 'practice') NOT NULL,
            phase INT NOT NULL,
            title VARCHAR(255),
            UNIQUE KEY composite_slug (slug, mode)
        );

        CREATE TABLE IF NOT EXISTS study_progress (
            id CHAR(36) PRIMARY KEY,
            user_id CHAR(36) NOT NULL,
            lesson_id CHAR(36) NOT NULL,
            studied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id),
            FOREIGN KEY (lesson_id) REFERENCES lesson(id),
            UNIQUE KEY user_lesson (user_id, lesson_id)
        );

        CREATE TABLE IF NOT EXISTS practice_progress (
            id CHAR(36) PRIMARY KEY,
            user_id CHAR(36) NOT NULL,
            lesson_id CHAR(36) NOT NULL,
            practiced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id),
            FOREIGN KEY (lesson_id) REFERENCES lesson(id),
            UNIQUE KEY user_lesson (user_id, lesson_id)
        );
    ");

    echo '<h1 style="color: #22C55E; font-family: monospace; padding: 20px;">✓ Database tables created successfully!</h1>';
    echo '<p style="font-family: monospace; padding: 0 20px; color: #888;">Now delete <code>src/setup.php</code> from your repo and push to GitHub.</p>';
    echo '<p style="font-family: monospace; padding: 0 20px; color: #888;">Heroku will auto-deploy and your app will be ready.</p>';

} catch (Exception $e) {
    http_response_code(500);
    echo '<h1 style="color: #EF4444; font-family: monospace; padding: 20px;">✗ Database setup failed</h1>';
    echo '<pre style="font-family: monospace; padding: 20px; color: #EF4444; background: #1a1a1a;">';
    echo htmlspecialchars($e->getMessage());
    echo '</pre>';
}
