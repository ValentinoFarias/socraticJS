<?php
// console.php — JS Console coding challenge
// Requires login. Reads ?topic= slug from the URL and renders the 3-panel widget.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

// Convert the URL slug to a readable title.
// e.g. "variables-let-const" → "Variables Let Const"
$slug  = isset($_GET['topic']) ? $_GET['topic'] : '';
$topic = $slug !== '' ? ucwords(str_replace('-', ' ', $slug)) : 'Unknown Topic';

// ------------------------------------------------------------------
// Placeholder content — these strings will come from the exercise
// and topic tables (via the DB) once the API layer is wired up.
// ------------------------------------------------------------------

// HTML shown in the read-only editor panel (source code view).
// Note: json_encode() is used in the JS below to safely embed this
// as a JavaScript string literal — no manual escaping needed.
$starter_html = implode("\n", [
    '<!DOCTYPE html>',
    '<html lang="en">',
    '<head>',
    '  <meta charset="UTF-8">',
    '  <title>' . $topic . '</title>',
    '</head>',
    '<body>',
    '  <h1>' . $topic . '</h1>',
    '  <script src="study.js"><\/script>',
    '</body>',
    '</html>',
]);

// Self-contained HTML loaded into the preview iframe.
// No <script src> here — the widget injects the learner's JS directly
// using new win.Function() after the iframe finishes loading (onload).
$iframe_template = implode("\n", [
    '<!DOCTYPE html>',
    '<html lang="en">',
    '<head>',
    '  <meta charset="UTF-8">',
    '  <style>body{font-family:sans-serif;padding:14px;font-size:15px}</style>',
    '</head>',
    '<body>',
    '  <h2>' . $topic . '</h2>',
    '</body>',
    '</html>',
]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JS Console — <?= h($topic) ?> — SocraticJS</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">

  <!-- CodeMirror 5 — VS Code-style syntax highlighting for HTML and JS editors -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/dracula.min.css">

  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="page--console">

  <!-- ── Navigation ─────────────────────────────────────────────── -->
  <nav class="nav">
    <a href="/index.php"><img class="nav__logo" src="/assets/img/logo.png" alt="SocraticJS logo"></a>
    <a class="nav__link" href="/about.php">about</a>
    <div class="nav__right">
      <?php if (is_logged_in()): ?>
        <!-- Logged-in state: greet the user and offer a logout link -->
        <span class="nav__greeting">Hi, <?= h($_SESSION["username"]) ?></span>
        <a class="nav__link nav__link--logout" href="/logout.php">logout</a>
      <?php else: ?>
        <!-- Logged-out state: show login and register links -->
        <a class="nav__link" href="/login.php">login</a>
        <a class="nav__link" href="/register.php">signUp</a>
      <?php endif; ?>
    </div>
  </nav>

  <!-- ── Three-panel coding challenge widget ─────────────────────── -->
  <div class="console__layout">

    <!-- Task card: shows phase, topic, mode badge, and step-by-step instructions.
         These will be populated from the DB once the exercise API is ready. -->
    <div class="c-task-card">
      <div class="c-task-title">
        Phase ? — <?= h($topic) ?>
        <span class="c-mode-badge c-mode-study">📖 Study</span>
      </div>
      <div class="c-task-body">
        <!-- Placeholder — exercise description will come from the exercise table -->
        <p>Your challenge for this topic will appear here once the exercise is loaded.</p>
        <ol>
          <li>Read the challenge description above</li>
          <li>Write your JavaScript in the editor below</li>
          <li>Click <strong>Run</strong> to test — then <strong>Review</strong> for AI feedback</li>
        </ol>
      </div>
    </div>

    <!-- Main toolbar: context hint on the left, Run and Reset on the right -->
    <div class="c-toolbar">
      <span class="c-toolbar__hint">Write your JS below — hit Run or Ctrl+Enter</span>
      <div class="c-spacer"></div>
      <button class="c-btn c-btn--reset" id="reset-btn">Reset</button>
      <button class="c-btn c-btn--run"   id="run-btn">Run ▶</button>
    </div>

    <!-- HTML panel: read-only view of the HTML the learner is working with.
         In Real mode this would be editable so learners can experiment with structure. -->
    <div class="c-panel">
      <div class="c-panel-header">
        <span class="c-dot c-dot--html"></span>
        index.html (read only)
      </div>
      <textarea id="html-editor"></textarea>
    </div>

    <!-- JS panel: the main editable code editor.
         Ctrl+Enter runs the code. The Review button sends code to the AI (coming soon). -->
    <div class="c-panel" id="js-panel">
      <div class="c-panel-header">
        <span class="c-dot c-dot--js"></span>
        study.js — write here
      </div>
      <textarea id="js-editor"></textarea>
      <!-- Footer toolbar: label on the left, Review button on the right -->
      <div class="c-js-toolbar">
        <span class="c-toolbar__hint">Done writing?</span>
        <div class="c-spacer"></div>
        <button class="c-btn c-btn--review" id="review-btn">Review my code ↗</button>
      </div>
    </div>

    <!-- Preview iframe: renders the learner's HTML + JS live.
         Height is kept small (70px) in Study mode — more useful in Real/DOM mode. -->
    <div class="c-panel">
      <div class="c-panel-header">Preview</div>
      <div class="c-preview-wrap">
        <iframe id="preview" title="Challenge preview"></iframe>
      </div>
    </div>

    <!-- Output panel: console.log lines + auto-check results.
         After every Run, this panel refreshes to show what happened. -->
    <div class="c-panel">
      <div class="c-panel-header">
        <span class="c-dot c-dot--out"></span>
        Output &amp; checks
      </div>
      <div class="c-output" id="output">
        <span class="c-hint">Run your code to see results.</span>
      </div>
    </div>

  </div><!-- /.console__layout -->


  <!-- ── CodeMirror 5 ──────────────────────────────────────────────
       Loaded in this order: core → language modes → addons.
       Using version 5.65.16 from cdnjs (stable, widely cached). -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
  <!-- Language modes: XML first (htmlmixed depends on it) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
  <!-- Addons: auto-close and match brackets for better DX -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>

  <script>
    // ── Initialise CodeMirror editors ───────────────────────────────

    // HTML editor — read only in Study mode.
    // (Real mode: set readOnly: false and add an "Apply HTML" button)
    var htmlEditor = CodeMirror.fromTextArea(
      document.getElementById('html-editor'),
      {
        mode:        'htmlmixed',
        theme:       'dracula',
        lineNumbers: true,
        readOnly:    true   // Study mode: HTML is fixed, learner only writes JS
      }
    );

    // Populate the HTML editor with the starter code from PHP.
    // json_encode() safely escapes the PHP string into a JS string literal.
    htmlEditor.setValue(<?= json_encode($starter_html) ?>);

    // JS editor — fully editable.
    // Ctrl+Enter / Cmd+Enter triggers Run so learners can stay on the keyboard.
    var jsEditor = CodeMirror.fromTextArea(
      document.getElementById('js-editor'),
      {
        mode:              'javascript',
        theme:             'dracula',
        lineNumbers:       true,
        autoCloseBrackets: true,  // auto-close (), [], {}, "", ''
        matchBrackets:     true,  // highlight the matching bracket
        indentUnit:        2,
        tabSize:           2,
        indentWithTabs:    false,
        extraKeys: {
          'Ctrl-Enter': run,  // run on Ctrl+Enter
          'Cmd-Enter':  run,  // run on Cmd+Enter (macOS)
          // Insert two spaces instead of a hard tab
          Tab: function (cm) { cm.replaceSelection('  '); }
        }
      }
    );

    // ── DOM references ──────────────────────────────────────────────
    var output  = document.getElementById('output');
    var preview = document.getElementById('preview');

    // Self-contained HTML used as the iframe content.
    // PHP has already built this string; json_encode() makes it JS-safe.
    var HTML_TEMPLATE = <?= json_encode($iframe_template) ?>;

    // ── run() — execute the learner's JS inside the preview iframe ──
    function run() {
      var userJS = jsEditor.getValue().trim();

      if (!userJS) {
        output.innerHTML = '<span class="c-hint">Write some JavaScript first, then hit Run.</span>';
        return;
      }

      output.innerHTML = '<span class="c-hint">Running…</span>';

      // KEY PATTERN: srcdoc + onload — never doc.open/write/close + setTimeout.
      // Setting srcdoc fires onload only after the iframe DOM is fully ready,
      // which eliminates the timing bugs that setTimeout causes.
      preview.srcdoc = HTML_TEMPLATE;

      preview.onload = function () {
        var win = preview.contentWindow;
        var doc = preview.contentDocument;

        // Intercept console.log so we can display output in the panel.
        // We still call the original so DevTools also receives the logs.
        var logs = [];
        var originalLog = win.console.log.bind(win.console);
        win.console.log = function () {
          var args = Array.prototype.slice.call(arguments);
          logs.push(args.map(function (v) { return String(v); }).join(' '));
          originalLog.apply(null, args);
        };

        // Execute the learner's code inside the iframe window.
        // new win.Function() scopes the code to the iframe, not this page.
        var runError = null;
        try {
          var fn = new win.Function(userJS);
          fn();
        } catch (e) {
          runError = e.message;
        }

        // ── Build the output HTML ───────────────────────────────────
        var lines = [];

        // console.log output lines
        if (logs.length > 0) {
          logs.forEach(function (line) {
            lines.push('<div class="c-log c-log--plain">&#9656; ' + esc(line) + '</div>');
          });
        }

        // Runtime or syntax error
        if (runError) {
          lines.push('<div class="c-log c-log--err">&#10005; Error: ' + esc(runError) + '</div>');
        }

        // Divider between raw output and auto-check results
        lines.push('<hr class="c-divider">');

        // Run auto-checks for this challenge
        var checks = runChecks(doc, userJS, logs);

        // Render each check row with a pass/fail icon
        checks.forEach(function (c) {
          var cls  = c.pass ? 'c-log--ok'  : 'c-log--err';
          var icon = c.pass ? '&#10003;'   : '&#10005;';
          lines.push(
            '<div class="c-check-row">' +
              '<span class="c-check-icon ' + cls + '">' + icon + '</span>' +
              '<span class="' + cls + '">' + esc(c.label) + '</span>' +
            '</div>'
          );
        });

        // Success banner — shown only when every check passes
        if (checks.length > 0 && checks.every(function (c) { return c.pass; })) {
          lines.push('<div class="c-success">All checks passed! Great work.</div>');
        }

        output.innerHTML = lines.join('') || '<span class="c-hint">No output.</span>';
      };
    }

    // ── runChecks() — auto-grade the learner's code ─────────────────
    // Returns an array of { label: string, pass: boolean } objects.
    //
    // Parameters:
    //   doc  — the iframe document  (use for DOM queries in Real mode)
    //   code — the learner's JS string (use regex for pattern checks)
    //   logs — array of console.log strings (use for Study mode output checks)
    //
    // TODO: replace with real checks once exercises are loaded from the DB.
    function runChecks(doc, code, logs) {
      // Placeholder — returns empty until challenge data is wired up
      return [];
    }

    // ── esc() — sanitise strings before injecting into innerHTML ────
    // Prevents XSS when displaying learner code or error messages in the output panel.
    function esc(s) {
      return s
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    }

    // ── Button listeners ────────────────────────────────────────────
    document.getElementById('run-btn').addEventListener('click', run);

    document.getElementById('reset-btn').addEventListener('click', function () {
      jsEditor.setValue('');
      output.innerHTML = '<span class="c-hint">Run your code to see results.</span>';
      preview.srcdoc = '';
    });

    // Review button — will call the Anthropic API for Socratic feedback.
    // TODO: replace placeholder with actual API fetch once implemented.
    document.getElementById('review-btn').addEventListener('click', function () {
      var code = jsEditor.getValue().trim();
      if (!code) {
        output.innerHTML = '<span class="c-hint">Write some code first, then hit Review!</span>';
        return;
      }
      // Placeholder response — API wired up in the next session
      output.innerHTML = '<span class="c-hint">AI review coming soon — the Anthropic API will be wired up here.</span>';
    });
  </script>

</body>
</html>
