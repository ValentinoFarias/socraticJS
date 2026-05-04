<?php
// api/review_chat.php — follow-up chat with the AI tutor inside the JS Console.
// After the initial /api/review.php gives Socratic feedback, the learner can
// keep the conversation going here. Unlike the initial review (which prefers
// guiding questions), this endpoint is allowed to answer syntax questions
// directly so the learner is never stuck waiting on a hint that never comes.
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/rate_limit.php';

// Only logged-in users can call this endpoint
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Same rate limit as the initial review — 20 requests / minute / session
rate_limit(20, 60);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

// Conversation history — array of { role: 'user'|'assistant', content: string }.
// The frontend includes EVERY past turn so Claude has full context.
$messages = $body['messages'] ?? [];

// Exercise context — the same context we send to the initial review.
// Repeated here because each chat call is stateless on the server side.
$code             = $body['code']             ?? '';
$topic            = $body['topic']            ?? 'JavaScript';
$task_title       = $body['task_title']       ?? '';
$task_description = $body['task_description'] ?? '';
$starter_html     = $body['starter_html']     ?? '';
$checks           = $body['checks']           ?? [];
$mode             = $body['mode']             ?? 'study';

$task_description = strip_tags($task_description);

if (empty($messages) || !is_array($messages)) {
    http_response_code(400);
    echo json_encode(['error' => 'No messages provided']);
    exit;
}

$api_key = getenv('ANTHROPIC_API_KEY');

// ── System prompt — follow-up chat tone ─────────────────────────────────────
// The initial review keeps things Socratic. This follow-up prompt is the
// "unblock me" channel: when the learner asks how to write something, we
// SHOW them how, then ask how they'd apply it. Without this, beginners hit
// dead-ends when the Socratic hint references a feature they have not seen.
$system_prompt = <<<'PROMPT'
You are a patient, encouraging JavaScript tutor in a follow-up chat with a
beginner who just received Socratic feedback on their code. Your job here is
DIFFERENT from the initial review: you are the channel where the learner asks
direct questions when they are stuck, and you give them the syntax they need
to keep moving.

CORE RULES:
1. If the learner asks "how do I write X" / "what is the syntax for Y" /
   "can you show me how to do Z" → SHOW the syntax with a short, focused
   example (≤6 lines). Then ask one short question that nudges them to apply
   it to THEIR specific exercise. Do NOT withhold language features.
   Example:
   "Sure — to pick a value from an array by position you use the index in
   square brackets:
   ```js
   let fruits = ["apple", "pear", "kiwi"];
   console.log(fruits[0]); // "apple"
   ```
   How could you use the questionNumber variable to pick the right question
   from a similar array?"

2. If the learner asks a CONCEPTUAL question ("why doesn't my loop stop?",
   "what's wrong with this line?") → answer the question directly, then ask
   one short question to check understanding.

3. If the learner is just chatting ("thanks!", "got it") → reply briefly and
   warmly, then optionally suggest the next small step.

4. If the learner asks for "the full solution" or "just give me the answer" →
   politely decline and offer to walk through it piece by piece instead. The
   "Show answer" button is the only place where the full solution lives.

STYLE:
- Be warm, concise (under 150 words), and use the learner's variable names.
- Use short code blocks (≤6 lines) — never paste the whole solution.
- Plain English. No condescension. No filler praise.
- The exercise context (task, their code, checks) is given below so you can
  tailor every answer to their specific challenge — do not give generic
  examples when a tailored one is possible.
PROMPT;

// ── Exercise context block — prepended to the conversation as a system note ─
$checks_text = '';
if (!empty($checks)) {
    $checks_text = "Checks the code should pass:\n";
    foreach ($checks as $check) {
        $checks_text .= "  - " . htmlspecialchars($check['label'] ?? '') . "\n";
    }
}

$context_html = '';
if ($mode === 'real' && !empty($starter_html)) {
    $context_html = "\nHTML the learner is working with:\n```html\n" .
                    htmlspecialchars($starter_html) . "\n```\n";
}

// We append the exercise context to the FIRST system message so Claude always
// has the task in mind, regardless of how long the conversation gets.
$context_block = <<<CTX

EXERCISE CONTEXT (the learner is working on this right now):
Topic: {$topic}
Task: {$task_title}

{$task_description}
{$context_html}
{$checks_text}

Their current code:
```javascript
{$code}
```
CTX;

$full_system = $system_prompt . "\n" . $context_block;

// ── Sanitise the messages array ─────────────────────────────────────────────
// Anthropic requires alternating user/assistant turns starting with user.
// We trust the frontend to send them in order but coerce shapes defensively.
$clean_messages = [];
foreach ($messages as $m) {
    $role    = ($m['role'] ?? '') === 'assistant' ? 'assistant' : 'user';
    $content = (string)($m['content'] ?? '');
    if ($content === '') continue;
    $clean_messages[] = ['role' => $role, 'content' => $content];
}

if (empty($clean_messages)) {
    http_response_code(400);
    echo json_encode(['error' => 'Messages array is empty']);
    exit;
}

$payload = json_encode([
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 500,
    'system'     => $full_system,
    'messages'   => $clean_messages,
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
