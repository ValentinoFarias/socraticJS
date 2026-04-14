<?php
// api/exercise.php — AI exercise generator for the JS Console.
// Called on page load by consolehtml.php (real mode) or consolenohtml.php (study mode).
// Sends the topic + mode to Claude, which generates the full exercise:
//   task title, task description (HTML), starter HTML, check rules, and a next hint.
// Returns Claude's response as JSON so the frontend can populate the UI.
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rate_limit.php';

// Prompt data — defines $exercise_base_prompt, $exercise_level_blocks, $exercise_themes.
// Lives in its own file so the prompt/persona can be edited without touching
// the API proxy logic in this file.
require_once __DIR__ . '/../includes/exercises_prompt.php';

// Only logged-in users can call this endpoint
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Enforce rate limit — 20 requests per minute per session
rate_limit(20, 60);

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body  = json_decode(file_get_contents('php://input'), true);
$topic = $body['topic'] ?? '';
$mode  = $body['mode']  ?? 'study';        // "study" or "real"
$level = $body['level'] ?? 'beginner';      // "beginner" | "intermediate" | "advanced"

// Whitelist the level — never trust values coming from the client.
// If something unexpected arrives, fall back to beginner (safest default).
$allowed_levels = ['beginner', 'intermediate', 'advanced'];
if (!in_array($level, $allowed_levels, true)) {
    $level = 'beginner';
}

if (empty($topic)) {
    http_response_code(400);
    echo json_encode(['error' => 'No topic provided']);
    exit;
}

$api_key = getenv('ANTHROPIC_API_KEY');

// ── Build the system prompt ─────────────────────────────────────────────
// The base prompt + per-level blocks + themes list all live in
// includes/exercises_prompt.php (loaded above). We just compose them here:
//   base prompt + the block that matches the chosen difficulty level.
$system_prompt = $exercise_base_prompt . $exercise_level_blocks[$level];

// ── Variety injection ───────────────────────────────────────────────────
// Without this, Claude returns almost the same exercise every time for the
// same (topic, level). Two things change on every request:
//   1. A random scenario from $exercise_themes (shopping cart, weather app,
//      game score, ...) — gives the exercise a different "flavor".
//   2. A random integer "variation seed" — even if the same theme is picked
//      twice, the seed makes the user message unique.
$theme          = $exercise_themes[array_rand($exercise_themes)];
$variation_seed = random_int(1000, 9999);

// ── User message — tells Claude WHAT to generate ────────────────────────
// We repeat `Level` in the user message (on top of the system prompt block
// above) because the system prompt is long — restating it right next to the
// topic makes the target difficulty impossible to miss.
$user_message = "Topic: \"$topic\"\nMode: \"$mode\"\nLevel: \"$level\"\nScenario: $theme\nVariation: $variation_seed";

$payload = json_encode([
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 1500,
    'system'     => $system_prompt,
    'messages'   => [
        ['role' => 'user', 'content' => $user_message],
    ],
]);

$ch = curl_init('https://api.anthropic.com/v1/messages');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $api_key,
        'anthropic-version: 2023-06-01',
    ],
]);

$result = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// If Anthropic returned an error, forward it
if ($status !== 200) {
    http_response_code($status);
    echo $result;
    exit;
}

// Parse the Anthropic response to extract the text content,
// then parse that text as JSON (since we asked Claude to return pure JSON).
$anthropic = json_decode($result, true);
$text      = $anthropic['content'][0]['text'] ?? '';

// Strip markdown code fences if Claude wrapped the JSON (e.g. ```json ... ```)
$text = preg_replace('/^```(?:json)?\s*/i', '', $text);
$text = preg_replace('/\s*```\s*$/', '', $text);

// Validate that the response is valid JSON
$exercise = json_decode($text, true);
if ($exercise === null) {
    // Claude's response wasn't valid JSON — return it as raw text for debugging
    http_response_code(500);
    echo json_encode(['error' => 'Invalid exercise JSON from AI', 'raw' => $text]);
    exit;
}

// Validate required fields — Claude sometimes omits them
$required = ['task_title', 'task_description', 'checks'];
foreach ($required as $field) {
    if (empty($exercise[$field])) {
        http_response_code(500);
        echo json_encode(['error' => "Missing field: $field", 'raw' => $text]);
        exit;
    }
}

// Real mode must include starter HTML for DOM exercises
if ($mode === 'real' && empty($exercise['starter_html'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Real mode exercise missing starter_html']);
    exit;
}

// Return the exercise JSON directly to the frontend
echo json_encode($exercise);
