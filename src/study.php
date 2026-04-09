<?php
// study.php — JS Tutor topic picker
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login(); // Redirect to login.php if the user is not logged in
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Study — SocraticJS</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Coming+Soon&family=Source+Code+Pro:wght@400;700&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

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

  <!-- ── Page subtitle ──────────────────────────────────────────── -->
  <p class="study__subtitle">Select a topic to start a discussion</p>

  <!-- ── Roadmap — all 7 phases ─────────────────────────────────── -->
  <!--
    Each topic uses a <details> element — clicking the <summary> (the topic name)
    toggles the sub-topic list open/closed. No JavaScript needed.
  -->
  <main class="study__roadmap">

    <!-- Phase 1 -->
    <p class="roadmap-phase">Phase 1 — The Very Basics</p>

    <details class="roadmap-topic">
      <summary>Variables</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=what-is-a-variable">What is a variable?</a></li>
        <li><a href="/tutor.php?topic=let">let — block-scoped, reassignable</a></li>
        <li><a href="/tutor.php?topic=const">const — block-scoped, fixed binding</a></li>
        <li><a href="/tutor.php?topic=var">var — function-scoped, hoisted (legacy)</a></li>
        <li><a href="/tutor.php?topic=hoisting">Hoisting — what it means &amp; why it matters</a></li>
        <li><a href="/tutor.php?topic=naming-rules">Naming rules &amp; conventions</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>Data Types</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=primitives">Primitives: string, number, boolean</a></li>
        <li><a href="/tutor.php?topic=null-vs-undefined">null vs undefined — the key difference</a></li>
        <li><a href="/tutor.php?topic=typeof">typeof — checking a value's type</a></li>
        <li><a href="/tutor.php?topic=type-coercion">Type coercion — when JS changes types silently</a></li>
        <li><a href="/tutor.php?topic=template-literals">Template literals — backtick strings</a></li>
        <li><a href="/tutor.php?topic=symbol-bigint">Symbol &amp; BigInt (bonus primitives)</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>Operators</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=arithmetic">Arithmetic: + - * / % **</a></li>
        <li><a href="/tutor.php?topic=assignment">Assignment: = += -= *= /=</a></li>
        <li><a href="/tutor.php?topic=equality">== vs === — loose vs strict equality</a></li>
        <li><a href="/tutor.php?topic=logical">Logical: &amp;&amp; || ! (AND, OR, NOT)</a></li>
        <li><a href="/tutor.php?topic=nullish-optional">Nullish coalescing ?? and optional chaining ?.</a></li>
        <li><a href="/tutor.php?topic=increment">Increment / decrement: ++ --</a></li>
      </ul>
    </details>

    <!-- Phase 2 -->
    <p class="roadmap-phase">Phase 2 — Control Flow</p>

    <details class="roadmap-topic">
      <summary>Conditionals</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=if-else">if / else statements</a></li>
        <li><a href="/tutor.php?topic=else-if">else if — chaining conditions</a></li>
        <li><a href="/tutor.php?topic=switch">switch statements</a></li>
        <li><a href="/tutor.php?topic=ternary">Ternary operator: condition ? a : b</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>Loops</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=for-loop">for loop — counting iterations</a></li>
        <li><a href="/tutor.php?topic=while-loop">while loop — run while true</a></li>
        <li><a href="/tutor.php?topic=do-while">do...while loop</a></li>
        <li><a href="/tutor.php?topic=for-of">for...of — iterating arrays</a></li>
        <li><a href="/tutor.php?topic=for-in">for...in — iterating object keys</a></li>
        <li><a href="/tutor.php?topic=break">break — stop the loop early</a></li>
        <li><a href="/tutor.php?topic=continue">continue — skip to next iteration</a></li>
      </ul>
    </details>

    <!-- Phase 3 -->
    <p class="roadmap-phase">Phase 3 — Functions</p>

    <details class="roadmap-topic">
      <summary>Defining Functions</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=function-declarations">Function declarations</a></li>
        <li><a href="/tutor.php?topic=function-expressions">Function expressions</a></li>
        <li><a href="/tutor.php?topic=arrow-functions">Arrow functions: () =&gt; {}</a></li>
        <li><a href="/tutor.php?topic=parameters">Parameters and arguments</a></li>
        <li><a href="/tutor.php?topic=default-parameters">Default parameters</a></li>
        <li><a href="/tutor.php?topic=return">The return statement</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>Scope &amp; Advanced</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=scope-local-global">Local vs global scope</a></li>
        <li><a href="/tutor.php?topic=block-scope">Block scope with let &amp; const</a></li>
        <li><a href="/tutor.php?topic=callbacks">Callback functions</a></li>
        <li><a href="/tutor.php?topic=higher-order">Higher-order functions</a></li>
        <li><a href="/tutor.php?topic=pure-functions">Pure functions — no side effects</a></li>
        <li><a href="/tutor.php?topic=iife">IIFE — immediately invoked function expression</a></li>
      </ul>
    </details>

    <!-- Phase 4 -->
    <p class="roadmap-phase">Phase 4 — Arrays &amp; Objects</p>

    <details class="roadmap-topic">
      <summary>Arrays</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=arrays-basics">Creating arrays and accessing items</a></li>
        <li><a href="/tutor.php?topic=push-pop">push, pop, shift, unshift</a></li>
        <li><a href="/tutor.php?topic=foreach">forEach — loop over every item</a></li>
        <li><a href="/tutor.php?topic=map">map — transform every item</a></li>
        <li><a href="/tutor.php?topic=filter">filter — keep matching items</a></li>
        <li><a href="/tutor.php?topic=reduce">reduce — collapse to a single value</a></li>
        <li><a href="/tutor.php?topic=find">find &amp; findIndex</a></li>
        <li><a href="/tutor.php?topic=includes-indexof">includes, indexOf, some, every</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>Objects</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=key-value">Key-value pairs</a></li>
        <li><a href="/tutor.php?topic=dot-bracket">Dot notation vs bracket notation</a></li>
        <li><a href="/tutor.php?topic=add-delete-props">Adding and deleting properties</a></li>
        <li><a href="/tutor.php?topic=for-in-objects">Looping with for...in</a></li>
        <li><a href="/tutor.php?topic=object-methods">Object.keys(), Object.values(), Object.entries()</a></li>
        <li><a href="/tutor.php?topic=destructuring">Destructuring arrays and objects</a></li>
        <li><a href="/tutor.php?topic=spread">Spread operator ...</a></li>
        <li><a href="/tutor.php?topic=rest">Rest operator in functions</a></li>
      </ul>
    </details>

    <!-- Phase 5 -->
    <p class="roadmap-phase">Phase 5 — The DOM &amp; Events</p>

    <details class="roadmap-topic">
      <summary>The DOM</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=what-is-dom">What is the DOM?</a></li>
        <li><a href="/tutor.php?topic=getelementbyid">document.getElementById()</a></li>
        <li><a href="/tutor.php?topic=queryselector">querySelector &amp; querySelectorAll</a></li>
        <li><a href="/tutor.php?topic=textcontent">Changing textContent</a></li>
        <li><a href="/tutor.php?topic=innerhtml">Changing innerHTML</a></li>
        <li><a href="/tutor.php?topic=style-prop">Changing CSS styles with .style</a></li>
        <li><a href="/tutor.php?topic=classlist">Adding &amp; removing CSS classes</a></li>
        <li><a href="/tutor.php?topic=createelement">Creating elements: createElement</a></li>
        <li><a href="/tutor.php?topic=appendchild">Adding to the page: appendChild, append</a></li>
        <li><a href="/tutor.php?topic=remove">Removing elements: remove()</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>Events</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=addeventlistener">addEventListener basics</a></li>
        <li><a href="/tutor.php?topic=click-input-submit">click, input, submit events</a></li>
        <li><a href="/tutor.php?topic=event-object">The event object</a></li>
        <li><a href="/tutor.php?topic=preventdefault">preventDefault()</a></li>
        <li><a href="/tutor.php?topic=event-delegation">Event delegation</a></li>
        <li><a href="/tutor.php?topic=remove-listener">Removing event listeners</a></li>
      </ul>
    </details>

    <!-- Phase 6 -->
    <p class="roadmap-phase">Phase 6 — Async JavaScript</p>

    <details class="roadmap-topic">
      <summary>Timers &amp; Concepts</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=what-is-async">What is asynchronous JavaScript?</a></li>
        <li><a href="/tutor.php?topic=call-stack">The call stack — how JS executes code</a></li>
        <li><a href="/tutor.php?topic=settimeout">setTimeout — delay a function</a></li>
        <li><a href="/tutor.php?topic=setinterval">setInterval — repeat a function</a></li>
        <li><a href="/tutor.php?topic=cleartimer">clearTimeout &amp; clearInterval</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>Promises</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=what-is-promise">What is a Promise?</a></li>
        <li><a href="/tutor.php?topic=then-catch">.then() and .catch()</a></li>
        <li><a href="/tutor.php?topic=promise-all">Promise.all() — wait for many</a></li>
        <li><a href="/tutor.php?topic=promise-race">Promise.race()</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>async / await</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=async-functions">async functions</a></li>
        <li><a href="/tutor.php?topic=await">await — pause until resolved</a></li>
        <li><a href="/tutor.php?topic=try-catch-async">try / catch with async/await</a></li>
        <li><a href="/tutor.php?topic=fetch">fetch API — get data from a URL</a></li>
        <li><a href="/tutor.php?topic=json">Parsing JSON responses</a></li>
      </ul>
    </details>

    <!-- Phase 7 -->
    <p class="roadmap-phase">Phase 7 — Advanced &amp; Modern JS</p>

    <details class="roadmap-topic">
      <summary>Core Advanced Concepts</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=closures">Closures — functions remembering their scope</a></li>
        <li><a href="/tutor.php?topic=this">The this keyword</a></li>
        <li><a href="/tutor.php?topic=call-apply-bind">call, apply, bind</a></li>
        <li><a href="/tutor.php?topic=prototypes">Prototypes &amp; the prototype chain</a></li>
        <li><a href="/tutor.php?topic=classes">Classes — OOP in JavaScript</a></li>
        <li><a href="/tutor.php?topic=inheritance">Inheritance with extends &amp; super</a></li>
        <li><a href="/tutor.php?topic=event-loop">The event loop — how JS really works</a></li>
        <li><a href="/tutor.php?topic=microtasks">Microtasks vs macrotasks</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>Modules &amp; Tooling</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=es-modules">ES Modules: import &amp; export</a></li>
        <li><a href="/tutor.php?topic=default-named-exports">Default vs named exports</a></li>
        <li><a href="/tutor.php?topic=npm">npm basics</a></li>
        <li><a href="/tutor.php?topic=bundlers">Bundlers: what is Webpack / Vite?</a></li>
      </ul>
    </details>

    <details class="roadmap-topic">
      <summary>Deep Dives</summary>
      <ul class="roadmap-list">
        <li><a href="/tutor.php?topic=generators">Generators &amp; iterators</a></li>
        <li><a href="/tutor.php?topic=weakmap">WeakMap &amp; WeakRef</a></li>
        <li><a href="/tutor.php?topic=proxy">Proxy &amp; Reflect</a></li>
        <li><a href="/tutor.php?topic=try-catch-finally">try / catch / finally</a></li>
        <li><a href="/tutor.php?topic=design-patterns">Design patterns in JavaScript</a></li>
        <li><a href="/tutor.php?topic=memory">Memory management &amp; garbage collection</a></li>
      </ul>
    </details>

  </main>

  <!-- Sticky note reminder -->
  <div class="study__sticky-note">
    Remember to check off the topics you've learned!
  </div>

  <script src="/assets/js/main.js"></script>

  <script>
    // ── Topic checkboxes — "mark as already studied" ────────────────
    //
    // Checkboxes are injected dynamically so we don't touch 60+ <li> items.
    // State is saved to and loaded from the DB via /api/progress.php.
    // Each checkbox is keyed by the lesson slug (the ?topic= URL value).

    // Step 1 — build all the checkboxes and wire up their change handlers.
    // We do this first so the DOM is ready before we load progress from the DB.
    document.querySelectorAll('.roadmap-list a').forEach(function (link) {
      // Extract the slug from the link href
      // e.g. "/tutor.php?topic=what-is-a-variable" → "what-is-a-variable"
      var slug = new URL(link.href, location.origin).searchParams.get('topic');
      if (!slug) return;

      var checkbox          = document.createElement('input');
      checkbox.type         = 'checkbox';
      checkbox.className    = 'topic-check';
      checkbox.title        = 'Mark as studied';
      checkbox.dataset.slug = slug;   // stored so loadProgress() can find it

      // On change: immediately update the visual state, then persist to the DB.
      // We update the UI first so the response feels instant — no waiting for fetch.
      checkbox.addEventListener('change', function () {
        var isChecked = this.checked;

        if (isChecked) {
          link.classList.add('topic--studied');
        } else {
          link.classList.remove('topic--studied');
        }

        // Save the new state to the DB — fire and forget (no await needed here)
        saveProgress(slug, isChecked);
      });

      link.parentElement.insertBefore(checkbox, link);
    });

    // Step 2 — load the user's progress from the DB and tick the right boxes.
    loadProgress();

    // ── loadProgress() ──────────────────────────────────────────────
    // GET /api/progress.php → { studied: ["slug-a", "slug-b", ...] }
    // Ticks every checkbox whose slug appears in the response.
    async function loadProgress() {
      try {
        // ?mode=study tells the API to query the study_progress table
        var res  = await fetch('/api/progress.php?mode=study');
        var data = await res.json();
        var studied = data.studied || [];   // array of slugs the user completed

        studied.forEach(function (slug) {
          // Find the checkbox by its data-slug attribute
          var checkbox = document.querySelector('.topic-check[data-slug="' + slug + '"]');
          if (!checkbox) return;

          checkbox.checked = true;

          // The link is the next sibling of the checkbox inside the <li>
          var link = checkbox.nextElementSibling;
          if (link) link.classList.add('topic--studied');
        });

      } catch (e) {
        // Progress failed to load — not critical, page still works
        console.warn('Could not load progress from server:', e);
      }
    }

    // ── saveProgress() ──────────────────────────────────────────────
    // POST /api/progress.php with { slug, studied: true|false }
    // Called on every checkbox change — updates the DB record.
    async function saveProgress(slug, studied) {
      try {
        await fetch('/api/progress.php', {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ slug: slug, studied: studied, mode: 'study' }),
        });
      } catch (e) {
        console.warn('Could not save progress to server:', e);
      }
    }
  </script>
</body>
</html>
