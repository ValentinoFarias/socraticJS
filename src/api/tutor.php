<?php
// api/tutor.php — Anthropic API proxy for the JS Tutor chat.
// Receives the full conversation history as JSON, forwards it to Claude,
// and returns the raw Anthropic response so the frontend can read the reply.
header('Content-Type: application/json');

// auth.php lives one level up in includes/
require_once __DIR__ . '/../includes/auth.php';

// Only logged-in users can call this endpoint
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// Only accept POST — reject everything else
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Read and decode the JSON body sent by the frontend
// php://input is the raw POST body — correct for application/json requests
$body     = json_decode(file_get_contents('php://input'), true);
$messages = $body['messages'] ?? [];

if (empty($messages)) {
    http_response_code(400);
    echo json_encode(['error' => 'No messages provided']);
    exit;
}

// System prompt — defines the tutor's persona and teaching rules.
// Kept here (server-side) so the client never sees or can tamper with it.
$system_prompt = "You are a patient, encouraging JavaScript tutor for complete beginners using the Socratic method. Never give answers directly — always guide the learner to discover answers through questions. Follow a 7-phase JS roadmap (Basics → Control Flow → Functions → Arrays & Objects → DOM & Events → Async → Advanced). Ask one question at a time. Celebrate small wins. Keep code examples under 10 lines and always use console.log(). When the topic is provided, focus your questions on that specific topic.";

// Read the API key from the environment — injected by Docker via .env
$api_key = getenv('ANTHROPIC_API_KEY');

// Build the request payload for the Anthropic Messages API
$payload = json_encode([
    'model'      => 'claude-sonnet-4-20250514',
    'max_tokens' => 1000,
    'system'     => $system_prompt,
    'messages'   => $messages,   // full conversation history from the frontend
]);

// Send the request to Anthropic using cURL
// cURL is a PHP extension for making HTTP requests to external APIs
$ch = curl_init('https://api.anthropic.com/v1/messages');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,    // return the response as a string
    CURLOPT_POST           => true,    // use POST method
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $api_key,       // Anthropic auth header
        'anthropic-version: 2023-06-01', // required version header
    ],
]);

$result = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE); // e.g. 200, 401, 500
curl_close($ch);

// Forward Anthropic's status code and response body to the frontend unchanged
http_response_code($status);
echo $result;
