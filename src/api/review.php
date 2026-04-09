<?php
// api/review.php — AI code review for the JS Console "Review my code" button.
// Receives the learner's code + topic, asks Claude for Socratic feedback,
// and returns the response as JSON.
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

if (empty($code)) {
    http_response_code(400);
    echo json_encode(['error' => 'No code provided']);
    exit;
}

$api_key = getenv('ANTHROPIC_API_KEY');

// Build a single-turn message that gives Claude the context it needs.
// We embed the code in a fenced code block so Claude reads it as code.
// Using heredoc avoids messy string concatenation with quotes inside.
$user_message = <<<MSG
The learner is practicing the topic: "$topic".

Here is their code:

```javascript
$code
```

Give brief Socratic feedback — don't fix their code, ask 1-2 guiding questions that help them spot issues or improve it themselves. Keep your response under 150 words.
MSG;

$payload = json_encode([
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 400,
    'system'     => 'You are a Socratic JavaScript tutor. Never give direct answers. Ask short, guiding questions that help beginners discover improvements themselves.',
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
