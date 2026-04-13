<?php
// api/exercise.php — AI exercise generator for the JS Console.
// Called on page load by consolehtml.php (real mode) or consolenohtml.php (study mode).
// Sends the topic + mode to Claude, which generates the full exercise:
//   task title, task description (HTML), starter HTML, check rules, and a next hint.
// Returns Claude's response as JSON so the frontend can populate the UI.
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rate_limit.php';

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

// ── System prompt — tells Claude HOW to generate exercises ──────────────
// This is the "brain" of the exercise generator. It contains:
//   - The 7-phase JS roadmap so Claude knows which phase a topic belongs to
//   - The two modes (study vs real) and how they differ
//   - The exact JSON format the frontend expects
//   - Check rule patterns that work with the runChecks() evaluator
$system_prompt = <<<'PROMPT'
You are the exercise generator for SocraticJS, a JavaScript learning platform for complete beginners.

Generate ONE coding challenge for the given topic and mode. Return ONLY valid JSON — no markdown, no code fences, no extra text outside the JSON object.

RESPONSE FORMAT (strict — follow exactly):
{
  "task_title": "Phase X — Topic Name",
  "task_description": "<p>One sentence describing the task.</p><ol><li>Step 1</li><li>Step 2</li><li>Step 3</li></ol>",
  "starter_html": "<!DOCTYPE html><html>...</html>",
  "checks": [
    { "label": "Human-readable check description", "js_expression": "JS boolean expression" }
  ],
  "solution_code": "JavaScript code that solves the task — clean, educational, ES5 syntax",
  "next_hint": "A suggestion for what to try next"
}

FIELD RULES:
- task_title: Include the phase number and topic name (e.g. "Phase 2 — if / else")
- task_description: Use HTML tags: <p>, <ol>, <li>, <code>, <strong>. Keep it brief — 1 sentence + 3 numbered steps.
- starter_html: For REAL mode only — include 3-5 meaningful HTML elements the learner will target. For STUDY mode set this to null.
  STARTER_HTML MUST BE BARE: NO <style> tags, NO inline style="..." attributes, NO <link rel="stylesheet">, NO <title>, NO extra <meta> beyond charset. The preview has its own styling injected at runtime — adding CSS here is wasted tokens AND overrides the dark theme. Keep <head> empty (or just <meta charset="UTF-8">) and put everything inside <body>. Only include elements the learner will actually read or manipulate — no decorative wrappers, no class="container", no id="app".
- checks: 3-5 per exercise. At least one behavioral check (does it actually produce the right result?), not just code patterns.
- solution_code: Clean, educational JavaScript code that solves the task. Use ES5 syntax. Include comments explaining the approach. Keep it concise.
- next_hint: One sentence — what the learner could try next after solving this.

MODES:
📖 Study mode (mode="study"):
  - Pure JavaScript — no HTML needed. Focus on the concept with console.log() output.
  - starter_html must be null.
  - Checks use regex on code and/or logs array.

🌐 Real mode (mode="real"):
  - Write JS that targets real HTML elements — like a real project.
  - starter_html must contain a meaningful HTML structure with elements the learner will select and manipulate.
  - Checks can query doc (the iframe document) after code runs.

CHECK RULE EVALUATION:
js_expression is evaluated as: new Function('doc', 'code', 'logs', 'return (' + expr + ')')
- doc:  the iframe document (for DOM checks in Real mode)
- code: the learner's JS source string (for regex pattern checks)
- logs: array of console.log() output strings

COMMON CHECK PATTERNS (copy/adapt — use ES5 syntax, not ES6):
  Pattern check:    /\blet\b/.test(code)
  Log contains:     logs.some(function(l){ return l.indexOf("Hello") >= 0 })
  Log count:        logs.length >= 3
  DOM text changed: doc.getElementById("text").textContent !== "Original text"
  Click + verify:   (function(){ var b=doc.getElementById("btn"); if(b) b.click(); return doc.getElementById("output").textContent==="Hello" })()
  Style changed:    (function(){ var b=doc.getElementById("btn"); if(b) b.click(); return doc.getElementById("output").style.color !== "" })()
  Class toggled:    (function(){ var b=doc.getElementById("btn"); if(b) b.click(); return doc.getElementById("box").classList.contains("highlight") })()

IMPORTANT:
- Use ES5 syntax in js_expression (function(){}, not ()=>{}) — the sandbox uses var, not let/const.
- All IDs in checks MUST match IDs in starter_html exactly.
- Escape backslashes in regex: \\b not \b, \\s not \s (the expression is inside a JSON string).
- Keep challenges focused and achievable for a complete beginner.

VARIETY (IMPORTANT — avoid repeating yourself):
- Each request includes a "Scenario" hint and a "Variation" number. Use them.
- Build the exercise around the given Scenario (characters, context, variable names, story) so two requests for the same topic feel different.
- Never reuse the same example sentences, variable names, or log outputs across requests — treat the Variation number as a signal that this MUST be a fresh challenge.

7-PHASE JS ROADMAP (use to determine phase number):
Phase 1 — The Very Basics: variables, let/const, typeof, type coercion, template literals, ==vs===, nullish coalescing
Phase 2 — Control Flow: if/else, switch, ternary, for loop, while loop, for...of, for...in
Phase 3 — Functions: function declaration, function expression, arrow functions, default parameters, return, callbacks, scope
Phase 4 — Arrays & Objects: creating arrays, push/pop, forEach, map, filter, reduce, objects key/value, Object.keys/values/entries, destructuring, spread
Phase 5 — The DOM & Events: getElementById, querySelector, textContent, changing styles, classList, createElement, input event, event object, preventDefault
Phase 6 — Async JavaScript: setTimeout, setInterval, Promises, async/await, fetch+JSON
Phase 7 — Advanced & Modern JS: closures, this keyword, classes, inheritance, ES modules, error handling, event loop
PROMPT;

// ── Level-specific instructions ─────────────────────────────────────────
// The base system prompt (above) describes the general exercise format.
// The instructions below refine it based on the difficulty level chosen on
// mode.php. We append one of these blocks to the system prompt on every
// request so the generator knows exactly what kind of challenge to produce.
// Using a match expression keeps the mapping compact and readable.
$level_instructions = match ($level) {
    'beginner' =>
        "\n\nLEVEL: BEGINNER\n" .
        "Generate a pure JavaScript exercise. No HTML, no DOM. The solution " .
        "uses only console.log(). One concept only. No event listeners, no " .
        "getElementById, no innerHTML. starter_html MUST be null.",

    'intermediate' =>
        "\n\nLEVEL: INTERMEDIATE\n" .
        "Generate a JavaScript exercise that targets a given HTML structure. " .
        "The JS must run once on page load — no button clicks, no event " .
        "listeners. The learner uses getElementById, querySelector, " .
        "textContent, or innerHTML to put something on the page. " .
        "starter_html MUST contain 3-5 meaningful HTML elements. " .
        "starter_html MUST be BARE: no <style> tags, no inline style " .
        "attributes, no <title>, no <link>. Empty <head> (or just " .
        "<meta charset=\"UTF-8\">) and meaningful elements inside <body>.",

    'advanced' =>
        "\n\nLEVEL: ADVANCED\n" .
        "Generate a JavaScript exercise that requires user interaction. " .
        "HTML is provided with at least one button. The learner must wire " .
        "up a click event listener, handle DOM updates, and clear/re-render " .
        "output. Multiple synchronous concepts working together. " .
        "starter_html MUST include at least one <button> the learner will " .
        "attach a listener to. " .
        "starter_html MUST be BARE: no <style> tags, no inline style " .
        "attributes, no <title>, no <link>. Empty <head> (or just " .
        "<meta charset=\"UTF-8\">) and meaningful elements inside <body>.",
};

// Append the level-specific block to the base system prompt. String
// concatenation is fine here because the nowdoc above is already a
// regular PHP string at this point.
$system_prompt .= $level_instructions;

// ── Variety injection ───────────────────────────────────────────────────
// Without this, Claude returns almost the same exercise every time for the
// same (topic, mode). We pass two things that change on every request:
//   1. A random scenario theme from the list below (gives the exercise a
//      different "flavor" — shopping cart, weather app, game score, etc.)
//   2. A random integer "variation seed" — even if the same theme is picked
//      twice, the seed makes the user message unique, nudging the model
//      toward a different answer.
$themes = [
    'shopping cart / product list',
    'weather forecast',
    'music playlist',
    'video game score and lives',
    'to-do list',
    'recipe ingredients',
    'movie ratings',
    'sports team standings',
    'chat messages',
    'fitness tracker steps',
    'bank account transactions',
    'library books',
    'pet adoption app',
    'travel destinations',
    'coffee shop order',
    'classroom attendance',
    'quiz questions and answers',
    'plant watering schedule',
    'calendar events',
    'photo gallery',
];
$theme = $themes[array_rand($themes)];
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
