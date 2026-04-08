<?php
// practice.php — JS Console topic picker
// TODO: restore require_login() once auth is built
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Practice — SocraticJS</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

  <!-- ── Navigation ─────────────────────────────────────────────── -->
  <nav class="nav">
    <a href="/index.php"><img class="nav__logo" src="/assets/img/logo.png" alt="SocraticJS logo"></a>
    <a class="nav__link" href="/about.php">about</a>
    <div class="nav__right">
      <a class="nav__link" href="/login.php">login</a>
      <a class="nav__link" href="/register.php">signUp</a>
    </div>
  </nav>

  <!-- ── Page subtitle ──────────────────────────────────────────── -->
  <p class="practice__subtitle">What do you want to practice today?</p>

  <!-- ── Challenge list — all 7 phases ─────────────────────────── -->
  <!--
    Each phase is a collapsible <details> block.
    Items link to console.php?topic=<slug>.
    Numbered lists match the Figma design.
  -->
  <main class="practice__roadmap">

    <!-- Phase 1 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 1 — The Very Basics</summary>
      <ol class="practice-list">
        <li><a href="/console.php?topic=variables-let-const">Variables — let/const</a></li>
        <li><a href="/console.php?topic=typeof">typeof</a></li>
        <li><a href="/console.php?topic=type-coercion">Type coercion</a></li>
        <li><a href="/console.php?topic=template-literals">Template literals</a></li>
        <li><a href="/console.php?topic=equality">== vs ===</a></li>
        <li><a href="/console.php?topic=nullish-coalescing">Nullish coalescing ??</a></li>
      </ol>
    </details>

    <!-- Phase 2 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 2 — Control Flow</summary>
      <ol class="practice-list">
        <li><a href="/console.php?topic=if-else">if / else</a></li>
        <li><a href="/console.php?topic=switch">switch</a></li>
        <li><a href="/console.php?topic=ternary">Ternary operator</a></li>
        <li><a href="/console.php?topic=for-loop">for loop</a></li>
        <li><a href="/console.php?topic=while-loop">while loop</a></li>
        <li><a href="/console.php?topic=for-of">for...of</a></li>
        <li><a href="/console.php?topic=for-in">for...in</a></li>
      </ol>
    </details>

    <!-- Phase 3 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 3 — Functions</summary>
      <ol class="practice-list">
        <li><a href="/console.php?topic=function-declaration">Function declaration</a></li>
        <li><a href="/console.php?topic=function-expression">Function expression</a></li>
        <li><a href="/console.php?topic=arrow-functions">Arrow functions</a></li>
        <li><a href="/console.php?topic=default-parameters">Default parameters</a></li>
        <li><a href="/console.php?topic=return">Return statement</a></li>
        <li><a href="/console.php?topic=callbacks">Callback functions</a></li>
        <li><a href="/console.php?topic=scope">Scope</a></li>
      </ol>
    </details>

    <!-- Phase 4 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 4 — Arrays &amp; Objects</summary>
      <ol class="practice-list">
        <li><a href="/console.php?topic=arrays-basics">Creating arrays</a></li>
        <li><a href="/console.php?topic=push-pop">push / pop / shift / unshift</a></li>
        <li><a href="/console.php?topic=foreach">forEach</a></li>
        <li><a href="/console.php?topic=map">map</a></li>
        <li><a href="/console.php?topic=filter">filter</a></li>
        <li><a href="/console.php?topic=reduce">reduce</a></li>
        <li><a href="/console.php?topic=objects-key-value">Objects — key/value</a></li>
        <li><a href="/console.php?topic=object-methods">Object.keys/values/entries</a></li>
        <li><a href="/console.php?topic=destructuring">Destructuring</a></li>
        <li><a href="/console.php?topic=spread">Spread operator</a></li>
      </ol>
    </details>

    <!-- Phase 5 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 5 — The DOM &amp; Events</summary>
      <ol class="practice-list">
        <li><a href="/console.php?topic=getelementbyid">getElementById</a></li>
        <li><a href="/console.php?topic=queryselector">querySelector</a></li>
        <li><a href="/console.php?topic=textcontent">textContent</a></li>
        <li><a href="/console.php?topic=changing-styles">Changing styles</a></li>
        <li><a href="/console.php?topic=classlist">classList</a></li>
        <li><a href="/console.php?topic=createelement">createElement</a></li>
        <li><a href="/console.php?topic=input-event">input event</a></li>
        <li><a href="/console.php?topic=event-object">Event object</a></li>
        <li><a href="/console.php?topic=preventdefault">preventDefault</a></li>
      </ol>
    </details>

    <!-- Phase 6 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 6 — Async JavaScript</summary>
      <ol class="practice-list">
        <li><a href="/console.php?topic=settimeout">setTimeout</a></li>
        <li><a href="/console.php?topic=setinterval">setInterval</a></li>
        <li><a href="/console.php?topic=promises">Promises</a></li>
        <li><a href="/console.php?topic=async-await">async/await</a></li>
        <li><a href="/console.php?topic=fetch-json">fetch + JSON</a></li>
      </ol>
    </details>

    <!-- Phase 7 -->
    <details class="roadmap-topic">
      <summary class="practice-title">Phase 7 — Advanced &amp; Modern JS</summary>
      <ol class="practice-list">
        <li><a href="/console.php?topic=closures">Closures</a></li>
        <li><a href="/console.php?topic=this">this keyword</a></li>
        <li><a href="/console.php?topic=classes">Classes</a></li>
        <li><a href="/console.php?topic=inheritance">Inheritance</a></li>
        <li><a href="/console.php?topic=es-modules">ES Modules</a></li>
        <li><a href="/console.php?topic=error-handling">Error handling</a></li>
        <li><a href="/console.php?topic=event-loop">Event loop</a></li>
      </ol>
    </details>

  </main>

  <script src="/assets/js/main.js"></script>
</body>
</html>
