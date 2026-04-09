<?php
// api/progress.php — Read and write lesson progress for the logged-in user.
//
// GET  /api/progress.php
//   → returns { "studied": ["slug-a", "slug-b", ...] }
//     (all lesson slugs the user has marked complete)
//
// POST /api/progress.php
//   body: { "slug": "what-is-a-variable", "studied": true|false }
//   → marks the lesson as complete (true) or removes progress (false)
//   → returns { "ok": true }

header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

// Only logged-in users can read or write progress
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// $user_id comes from the session — set during login
$user_id = $_SESSION['user_id'];

// ── GET — return all slugs the user has completed ────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // JOIN lesson so we can return the slug (the URL-safe identifier)
    // instead of the internal UUID — the frontend works with slugs
    $stmt = $pdo->prepare('
        SELECT l.slug
        FROM   user_progress up
        JOIN   lesson l ON l.id = up.lesson_id
        WHERE  up.user_id = ?
          AND  up.status  = \'complete\'
    ');
    $stmt->execute([$user_id]);

    // fetchAll returns an array of rows; we pluck just the slug column
    $rows   = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $slugs  = array_column($rows, 'slug');

    echo json_encode(['studied' => $slugs]);
    exit;
}

// ── POST — mark a lesson as studied or remove its progress ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $body    = json_decode(file_get_contents('php://input'), true);
    $slug    = $body['slug']    ?? '';
    $studied = $body['studied'] ?? false;   // true = check, false = uncheck

    if (empty($slug)) {
        http_response_code(400);
        echo json_encode(['error' => 'No slug provided']);
        exit;
    }

    if ($studied) {
        // ── Mark as complete ─────────────────────────────────────────────────
        //
        // Step 1: look up the lesson ID from the slug.
        // We look up the lesson first because user_progress stores lesson_id (UUID),
        // not the slug — slugs live in the lesson table.
        $stmt = $pdo->prepare('SELECT id FROM lesson WHERE slug = ?');
        $stmt->execute([$slug]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lesson) {
            http_response_code(404);
            echo json_encode(['error' => 'Lesson not found: ' . $slug]);
            exit;
        }

        // Step 2: INSERT a new progress row, or UPDATE if one already exists.
        // ON DUPLICATE KEY UPDATE handles the UNIQUE constraint on (user_id, lesson_id).
        $stmt = $pdo->prepare('
            INSERT INTO user_progress (id, user_id, lesson_id, status, studied_at)
            VALUES (UUID(), ?, ?, \'complete\', NOW())
            ON DUPLICATE KEY UPDATE
                status     = \'complete\',
                studied_at = NOW()
        ');
        $stmt->execute([$user_id, $lesson['id']]);

    } else {
        // ── Remove progress (user unchecked the box) ─────────────────────────
        //
        // DELETE the row entirely — no row means not_started.
        // JOIN lets us filter by slug without a separate lookup query.
        $stmt = $pdo->prepare('
            DELETE up
            FROM   user_progress up
            JOIN   lesson l ON l.id = up.lesson_id
            WHERE  up.user_id = ?
              AND  l.slug     = ?
        ');
        $stmt->execute([$user_id, $slug]);
    }

    echo json_encode(['ok' => true]);
    exit;
}

// Any other HTTP method (PUT, DELETE, etc.) is not allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
