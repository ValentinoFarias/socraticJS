<?php
// api/review.php — AI code review for the JS Console "Review my code" button.
// Receives the learner's code + full exercise context (task, HTML, checks),
// asks Claude for Socratic feedback with full context, and returns the response.
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/auth.php';

// Only logged-in users can call this endpoint
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

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

if (empty($code)) {
    http_response_code(400);
    echo json_encode(['error' => 'No code provided']);
    exit;
}

$api_key = getenv('ANTHROPIC_API_KEY');

// ── Build the system prompt with full context ──────────────────────────────
// This prompt tells Claude what its role is and what to focus on.
$system_prompt = <<<'PROMPT'
You are a patient, encouraging JavaScript tutor using the Socratic method.
Review the learner's code in the context of the task they were given.

Your job:
1. Check if the code actually solves the task (does it work?)
2. Point out any logic errors or misconceptions
3. Ask 1-2 guiding questions to help them discover improvements themselves

NEVER give direct fixes or complete the code for them. Keep your response
under 150 words. Be encouraging — celebrate what they did right!
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
