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
$mode  = $body['mode']  ?? 'study';   // "study" or "real"

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

7-PHASE JS ROADMAP (use to determine phase number):
Phase 1 — The Very Basics: variables, let/const, typeof, type coercion, template literals, ==vs===, nullish coalescing
Phase 2 — Control Flow: if/else, switch, ternary, for loop, while loop, for...of, for...in
Phase 3 — Functions: function declaration, function expression, arrow functions, default parameters, return, callbacks, scope
Phase 4 — Arrays & Objects: creating arrays, push/pop, forEach, map, filter, reduce, objects key/value, Object.keys/values/entries, destructuring, spread
Phase 5 — The DOM & Events: getElementById, querySelector, textContent, changing styles, classList, createElement, input event, event object, preventDefault
Phase 6 — Async JavaScript: setTimeout, setInterval, Promises, async/await, fetch+JSON
Phase 7 — Advanced & Modern JS: closures, this keyword, classes, inheritance, ES modules, error handling, event loop
PROMPT;

// ── User message — tells Claude WHAT to generate ────────────────────────
$user_message = "Topic: \"$topic\", Mode: \"$mode\"";

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
