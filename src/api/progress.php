<?php
// api/progress.php — Read and write lesson progress for the logged-in user.
//
// Supports TWO separate tracking systems:
//   mode=study    → study_progress table   (study.php checkboxes)
//   mode=practice → practice_progress table (practice.php checkboxes)
//
// GET  /api/progress.php?mode=study
//   → returns { "studied": ["slug-a", "slug-b", ...] }
//     (all lesson slugs the user has completed in that mode)
//
// POST /api/progress.php
//   body: { "slug": "what-is-a-variable", "studied": true|false, "mode": "study" }
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

// ── GET — return all slugs the user has completed for the given mode ─────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // mode comes from the query string: ?mode=study or ?mode=practice
    $mode = isset($_GET['mode']) ? $_GET['mode'] : 'study';

    // Pick the right progress table based on mode
    // study  → study_progress
    // practice → practice_progress
    if ($mode === 'practice') {
        $table    = 'practice_progress';
        $date_col = 'practiced_at';
    } else {
        $table    = 'study_progress';
        $date_col = 'studied_at';
    }

    // JOIN lesson so we can return the slug (the URL-safe identifier)
    // instead of the internal UUID — the frontend works with slugs.
    // We also filter by lesson.mode to only get slugs for the right mode.
    $stmt = $pdo->prepare("
        SELECT l.slug
        FROM   $table p
        JOIN   lesson l ON l.id = p.lesson_id
        WHERE  p.user_id = ?
          AND  p.status  = 'complete'
          AND  l.mode    = ?
    ");
    $stmt->execute([$user_id, $mode]);

    // fetchAll returns an array of rows; we pluck just the slug column
    $rows  = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $slugs = array_column($rows, 'slug');

    echo json_encode(['studied' => $slugs]);
    exit;
}

// ── POST — mark a lesson as studied/practiced or remove its progress ─────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $body    = json_decode(file_get_contents('php://input'), true);
    $slug    = $body['slug']    ?? '';
    $studied = $body['studied'] ?? false;   // true = check, false = uncheck
    $mode    = $body['mode']    ?? 'study'; // "study" or "practice"

    if (empty($slug)) {
        http_response_code(400);
        echo json_encode(['error' => 'No slug provided']);
        exit;
    }

    // Pick the right progress table and date column based on mode
    if ($mode === 'practice') {
        $table    = 'practice_progress';
        $date_col = 'practiced_at';
    } else {
        $table    = 'study_progress';
        $date_col = 'studied_at';
    }

    if ($studied) {
        // ── Mark as complete ─────────────────────────────────────────────────
        //
        // Step 1: look up the lesson ID from the slug AND mode.
        // The same slug can exist in both modes — we need the right one.
        $stmt = $pdo->prepare('SELECT id FROM lesson WHERE slug = ? AND mode = ?');
        $stmt->execute([$slug, $mode]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lesson) {
            http_response_code(404);
            echo json_encode(['error' => 'Lesson not found: ' . $slug . ' (mode: ' . $mode . ')']);
            exit;
        }

        // Step 2: INSERT a new progress row, or UPDATE if one already exists.
        // ON DUPLICATE KEY UPDATE handles the UNIQUE constraint on (user_id, lesson_id).
        $stmt = $pdo->prepare("
            INSERT INTO $table (id, user_id, lesson_id, status, $date_col)
            VALUES (UUID(), ?, ?, 'complete', NOW())
            ON DUPLICATE KEY UPDATE
                status     = 'complete',
                $date_col  = NOW()
        ");
        $stmt->execute([$user_id, $lesson['id']]);

    } else {
        // ── Remove progress (user unchecked the box) ─────────────────────────
        //
        // DELETE the row entirely — no row means not_started.
        // JOIN lets us filter by slug + mode without a separate lookup query.
        $stmt = $pdo->prepare("
            DELETE p
            FROM   $table p
            JOIN   lesson l ON l.id = p.lesson_id
            WHERE  p.user_id = ?
              AND  l.slug    = ?
              AND  l.mode    = ?
        ");
        $stmt->execute([$user_id, $slug, $mode]);
    }

    echo json_encode(['ok' => true]);
    exit;
}

// Any other HTTP method (PUT, DELETE, etc.) is not allowed
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
