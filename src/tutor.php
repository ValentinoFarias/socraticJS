<?php
// tutor.php — JS Tutor chat interface (Socratic method)
// Requires login. Reads ?topic= slug from the URL.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

// Convert the URL slug to a readable topic title.
// e.g. "what-is-a-variable" → "What Is A Variable"
$slug  = isset($_GET['topic']) ? $_GET['topic'] : '';
$topic = $slug !== '' ? ucwords(str_replace('-', ' ', $slug)) : 'Unknown Topic';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JS Tutor — <?= h($topic) ?> — SocraticJS</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="page--tutor">

  <!-- ── Navigation ─────────────────────────────────────────────── -->
  <nav class="nav">
    <a href="/index.php"><img class="nav__logo" src="/assets/img/logo.png" alt="SocraticJS logo"></a>
    <a class="nav__link" href="/about.php">about</a>
    <div class="nav__right">
      <?php if (is_logged_in()): ?>
        <span class="nav__greeting">Hi, <?= h($_SESSION["username"]) ?></span>
        <a class="nav__link nav__link--logout" href="/logout.php">logout</a>
      <?php else: ?>
        <a class="nav__link" href="/login.php">login</a>
        <a class="nav__link" href="/register.php">signUp</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ── Chat container — fills remaining viewport height ────────── -->
  <div class="tutor__container">

    <!-- Topic header: shows current phase and topic at a glance -->
    <div class="tutor__header">
      <span class="tutor__header-phase">Phase ?</span>
      <div class="tutor__header-divider"></div>
      <span class="tutor__header-topic"><?= h($topic) ?></span>
    </div>

    <!-- Messages area: scrollable list of AI and user bubbles.
         Placeholder greeting is shown until the API is wired up.
         New messages are appended here by appendMessage() in JS. -->
    <div class="tutor__messages" id="chat-messages">

      <!-- Placeholder tutor greeting — will become the first API response -->
      <div class="tutor__message tutor__message--ai">
        <div class="tutor__avatar">JS</div>
        <div class="tutor__bubble">
          Hi! I'm your Socratic JavaScript tutor. I won't just give you answers —
          I'll ask questions that help you <em>discover</em> them yourself. 🎯
          <br><br>
          What would you like to work on today?
        </div>
      </div>

      <!-- Example user message — shows the right-aligned bubble design -->
      <div class="tutor__message tutor__message--user">
        <div class="tutor__bubble">
          I want to understand <?= h($topic) ?>
        </div>
      </div>

      <!-- Example follow-up tutor message — shows multi-line bubble style -->
      <div class="tutor__message tutor__message--ai">
        <div class="tutor__avatar">JS</div>
        <div class="tutor__bubble">
          Great choice! Before I explain anything — what do you <em>already</em>
          know about <?= h($topic) ?>? Even a guess is fine.
        </div>
      </div>

    </div><!-- /#chat-messages -->

    <!-- Input area: fixed to the bottom of the container.
         Enter sends, Shift+Enter inserts a newline.
         Textarea auto-grows as the user types. -->
    <div class="tutor__input-area">
      <textarea
        id="chat-input"
        placeholder="Type your answer or question… (Enter to send, Shift+Enter for new line)"
        rows="1"
      ></textarea>
      <button id="send-btn">Send ↑</button>
    </div>

  </div><!-- /.tutor__container -->

  <script>
    // ── DOM references ──────────────────────────────────────────────
    var chatMessages = document.getElementById('chat-messages');
    var chatInput    = document.getElementById('chat-input');
    var sendBtn      = document.getElementById('send-btn');

    // ── Auto-resize textarea as the user types ──────────────────────
    // Grows up to a max of 120px, then scrolls internally.
    chatInput.addEventListener('input', function () {
      this.style.height = 'auto';
      this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // ── Keyboard shortcut: Enter sends, Shift+Enter = new line ──────
    chatInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    sendBtn.addEventListener('click', sendMessage);

    // ── sendMessage() — append user bubble and call the tutor API ───
    function sendMessage() {
      var text = chatInput.value.trim();
      if (!text) return;

      // Show the user's message immediately in the chat
      appendMessage('user', text);

      // Clear and reset the textarea height
      chatInput.value = '';
      chatInput.style.height = 'auto';

      // TODO: call the Anthropic API here and append the tutor's response.
      // The API call will send the full conversation history + the system
      // prompt (Socratic tutor persona) and stream the reply back.
      // For now, show a placeholder while the API is being wired up.
      appendMessage('ai', 'API coming soon — the Anthropic integration will be wired up here.');
    }

    // ── appendMessage() — add a bubble to the chat ──────────────────
    // role: 'ai' (left, with avatar) or 'user' (right, no avatar)
    // text: plain text — escaped before injecting into the DOM
    function appendMessage(role, text) {
      var row = document.createElement('div');
      row.className = 'tutor__message tutor__message--' + role;

      if (role === 'ai') {
        row.innerHTML =
          '<div class="tutor__avatar">JS</div>' +
          '<div class="tutor__bubble">' + esc(text) + '</div>';
      } else {
        row.innerHTML =
          '<div class="tutor__bubble">' + esc(text) + '</div>';
      }

      chatMessages.appendChild(row);

      // Scroll to the latest message
      chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // ── esc() — prevent XSS when injecting user text into innerHTML ─
    function esc(s) {
      return s
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/\n/g, '<br>');
    }

    // Scroll to bottom on initial load (in case there are placeholder messages)
    chatMessages.scrollTop = chatMessages.scrollHeight;
  </script>

</body>
</html>
