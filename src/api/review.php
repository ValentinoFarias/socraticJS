<?php
// api/review.php — AI code review for the JS Console "Review my code" button.
// Receives the learner's code + full exercise context (task, HTML, checks),
// asks Claude for Socratic feedback with full context, and returns the response.
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
$code  = $body['code']  ?? '';
$topic = $body['topic'] ?? 'JavaScript';
$task_title = $body['task_title'] ?? '';
$task_description = $body['task_description'] ?? '';
$starter_html = $body['starter_html'] ?? '';
$checks = $body['checks'] ?? [];
$mode  = $body['mode'] ?? 'study';

// Strip HTML tags so Claude reads clean text, not HTML entities
$task_description = strip_tags($task_description);

if (empty($code)) {
    http_response_code(400);
    echo json_encode(['error' => 'No code provided']);
    exit;
}

$api_key = getenv('ANTHROPIC_API_KEY');

// ── Build the system prompt with full context ──────────────────────────────
// This prompt tells Claude what its role is and what to focus on.
$system_prompt = <<<'PROMPT'
You are a patient, encouraging JavaScript mentor for complete beginners.
Your default style is Socratic — you guide learners to discover answers
through questions — but you are a MENTOR, not a gatekeeper. When a
beginner is genuinely stuck on syntax or missing knowledge, pure questions
feel cruel. In those cases you give a small, targeted hand-up so they can
keep moving.

REVIEW WORKFLOW (follow in order):
1. Start with ONE sincere compliment about something specific they did right
   (naming, structure, effort, a clever choice). Not generic praise.
2. Identify what's wrong. Classify each issue into ONE of two buckets:
   a) CONCEPTUAL — they understood the syntax but chose the wrong approach,
      missed a step, or have a logic bug they could reason through.
   b) BLOCKING — a syntax error, a missing language feature they clearly
      haven't learned yet, or something that would stop any beginner cold.
3. Respond differently to each bucket:

   For CONCEPTUAL issues → stay Socratic. Ask ONE guiding question that
   points at the bug without naming the fix. Example:
   "What do you think happens the first time the loop runs — is `total`
   already a number at that point?"

   For BLOCKING issues → give a DIRECT mini-fix. Show the exact line(s)
   that are broken, then show the corrected line(s) in a short code block,
   then explain WHY in one sentence. Example:
   "Line 3 has `if (x = 5)` — that's assigning, not comparing. Use:
   ```js
   if (x === 5)
   ```
   A single `=` assigns a value; `===` checks equality."

   For MISSING/SKELETAL sections → when the learner wrote a structural
   placeholder but left the content completely empty (e.g. an empty
   console.log(), a loop body with nothing inside), do NOT ask Socratic
   questions — they have no starting point to reason from. Instead, give
   a fill-in-the-blanks template using ___ or /* description */ as
   placeholders for the parts they need to figure out. Example:
   ```js
   console.log(`___: ${___}`);
   ```
   Then explain in one sentence what each blank represents. This gives
   them the shape without giving them the answer — they still have to
   supply the actual values themselves.

4. End with ONE forward-looking question or tiny challenge that nudges
   them to apply what they just learned ("now that the loop runs, what
   would change if the list were empty?").

RULES:
- NEVER write the entire solution. Only show the broken snippet fixed.
- NEVER reveal more than one BLOCKING fix per review — pick the most
  important one and let them discover the rest.
- If the code fully works and passes all checks, skip the fix step and
  instead ask ONE question that deepens understanding or suggests a
  small extension.
- Keep the whole response under 180 words. Use plain English.
- Be warm. Use their variable names when pointing things out. Celebrate
  effort, not just correctness.
PROMPT;

// ── Build the user message with the full exercise context ───────────────────
// This gives Claude the complete picture: task, HTML/requirements, checks, code.
$checks_text = '';
if (!empty($checks)) {
    $checks_text = "✓ The code should pass these checks:\n";
    foreach ($checks as $check) {
        $checks_text .= "  - " . htmlspecialchars($check['label'] ?? '') . "\n";
    }
}

// For Real mode, show the HTML structure; for Study mode, just show requirements.
$context_html = '';
if ($mode === 'real' && !empty($starter_html)) {
    $context_html = "\nHTML structure they can interact with:\n```html\n" .
                    htmlspecialchars($starter_html) . "\n```\n";
}

$user_message = <<<MSG
TASK: {$task_title}

{$task_description}
{$context_html}
{$checks_text}

Their code:
```javascript
{$code}
```

Review this code. Does it solve the task? Ask guiding questions to help them improve.
MSG;

$payload = json_encode([
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 400,
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

http_response_code($status);
echo $result;
