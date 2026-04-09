<?php
// migrate.php — One-time migration to fix Heroku database.
// Adds missing status column and inserts all lesson rows.
// Visit once at /migrate.php, then DELETE this file.

require_once __DIR__ . '/includes/db.php';

header('Content-Type: text/html; charset=utf-8');
echo '<pre style="font-family:monospace;padding:20px;background:#1a1a1a;color:#50fa7b">';

try {
    // 1. Add missing status column to progress tables
    try {
        $pdo->exec("ALTER TABLE study_progress ADD COLUMN status ENUM('not_started','in_progress','complete') NOT NULL DEFAULT 'not_started'");
        echo "Added status column to study_progress\n";
    } catch (Exception $e) {
        echo "study_progress status column: " . $e->getMessage() . " (may already exist)\n";
    }

    try {
        $pdo->exec("ALTER TABLE practice_progress ADD COLUMN status ENUM('not_started','in_progress','complete') NOT NULL DEFAULT 'not_started'");
        echo "Added status column to practice_progress\n";
    } catch (Exception $e) {
        echo "practice_progress status column: " . $e->getMessage() . " (may already exist)\n";
    }

    // 2. Insert all lesson rows
    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO lesson (id, slug, mode, phase, title) VALUES (UUID(), ?, ?, ?, ?)'
    );

    $lessons = [
        ['what-is-a-variable', 'study', 1, 'What is a variable?'],
        ['let', 'study', 1, 'let — block-scoped, reassignable'],
        ['const', 'study', 1, 'const — block-scoped, fixed binding'],
        ['var', 'study', 1, 'var — function-scoped, hoisted (legacy)'],
        ['hoisting', 'study', 1, 'Hoisting — what it means & why it matters'],
        ['naming-rules', 'study', 1, 'Naming rules & conventions'],
        ['primitives', 'study', 1, 'Primitives: string, number, boolean'],
        ['null-vs-undefined', 'study', 1, 'null vs undefined — the key difference'],
        ['typeof', 'study', 1, 'typeof — checking a value\'s type'],
        ['type-coercion', 'study', 1, 'Type coercion — when JS changes types silently'],
        ['template-literals', 'study', 1, 'Template literals — backtick strings'],
        ['symbol-bigint', 'study', 1, 'Symbol & BigInt (bonus primitives)'],
        ['arithmetic', 'study', 1, 'Arithmetic: + - * / % **'],
        ['assignment', 'study', 1, 'Assignment: = += -= *= /='],
        ['equality', 'study', 1, '== vs === — loose vs strict equality'],
        ['logical', 'study', 1, 'Logical: && || ! (AND, OR, NOT)'],
        ['nullish-optional', 'study', 1, 'Nullish coalescing ?? and optional chaining ?.'],
        ['increment', 'study', 1, 'Increment / decrement: ++ --'],
        ['if-else', 'study', 2, 'if / else statements'],
        ['else-if', 'study', 2, 'else if — chaining conditions'],
        ['switch', 'study', 2, 'switch statements'],
        ['ternary', 'study', 2, 'Ternary operator: condition ? a : b'],
        ['for-loop', 'study', 2, 'for loop — counting iterations'],
        ['while-loop', 'study', 2, 'while loop — run while true'],
        ['do-while', 'study', 2, 'do...while loop'],
        ['for-of', 'study', 2, 'for...of — iterating arrays'],
        ['for-in', 'study', 2, 'for...in — iterating object keys'],
        ['break', 'study', 2, 'break — stop the loop early'],
        ['continue', 'study', 2, 'continue — skip to next iteration'],
        ['function-declarations', 'study', 3, 'Function declarations'],
        ['function-expressions', 'study', 3, 'Function expressions'],
        ['arrow-functions', 'study', 3, 'Arrow functions: () =&gt; {}'],
        ['parameters', 'study', 3, 'Parameters and arguments'],
        ['default-parameters', 'study', 3, 'Default parameters'],
        ['return', 'study', 3, 'The return statement'],
        ['scope-local-global', 'study', 3, 'Local vs global scope'],
        ['block-scope', 'study', 3, 'Block scope with let & const'],
        ['callbacks', 'study', 3, 'Callback functions'],
        ['higher-order', 'study', 3, 'Higher-order functions'],
        ['pure-functions', 'study', 3, 'Pure functions — no side effects'],
        ['iife', 'study', 3, 'IIFE — immediately invoked function expression'],
        ['arrays-basics', 'study', 4, 'Creating arrays and accessing items'],
        ['push-pop', 'study', 4, 'push, pop, shift, unshift'],
        ['foreach', 'study', 4, 'forEach — loop over every item'],
        ['map', 'study', 4, 'map — transform every item'],
        ['filter', 'study', 4, 'filter — keep matching items'],
        ['reduce', 'study', 4, 'reduce — collapse to a single value'],
        ['find', 'study', 4, 'find & findIndex'],
        ['includes-indexof', 'study', 4, 'includes, indexOf, some, every'],
        ['key-value', 'study', 4, 'Key-value pairs'],
        ['dot-bracket', 'study', 4, 'Dot notation vs bracket notation'],
        ['add-delete-props', 'study', 4, 'Adding and deleting properties'],
        ['for-in-objects', 'study', 4, 'Looping with for...in'],
        ['object-methods', 'study', 4, 'Object.keys(), Object.values(), Object.entries()'],
        ['destructuring', 'study', 4, 'Destructuring arrays and objects'],
        ['spread', 'study', 4, 'Spread operator ...'],
        ['rest', 'study', 4, 'Rest operator in functions'],
        ['what-is-dom', 'study', 5, 'What is the DOM?'],
        ['getelementbyid', 'study', 5, 'document.getElementById()'],
        ['queryselector', 'study', 5, 'querySelector & querySelectorAll'],
        ['textcontent', 'study', 5, 'Changing textContent'],
        ['innerhtml', 'study', 5, 'Changing innerHTML'],
        ['style-prop', 'study', 5, 'Changing CSS styles with .style'],
        ['classlist', 'study', 5, 'Adding & removing CSS classes'],
        ['createelement', 'study', 5, 'Creating elements: createElement'],
        ['appendchild', 'study', 5, 'Adding to the page: appendChild, append'],
        ['remove', 'study', 5, 'Removing elements: remove()'],
        ['addeventlistener', 'study', 5, 'addEventListener basics'],
        ['click-input-submit', 'study', 5, 'click, input, submit events'],
        ['event-object', 'study', 5, 'The event object'],
        ['preventdefault', 'study', 5, 'preventDefault()'],
        ['event-delegation', 'study', 5, 'Event delegation'],
        ['remove-listener', 'study', 5, 'Removing event listeners'],
        ['what-is-async', 'study', 6, 'What is asynchronous JavaScript?'],
        ['call-stack', 'study', 6, 'The call stack — how JS executes code'],
        ['settimeout', 'study', 6, 'setTimeout — delay a function'],
        ['setinterval', 'study', 6, 'setInterval — repeat a function'],
        ['cleartimer', 'study', 6, 'clearTimeout & clearInterval'],
        ['what-is-promise', 'study', 6, 'What is a Promise?'],
        ['then-catch', 'study', 6, '.then() and .catch()'],
        ['promise-all', 'study', 6, 'Promise.all() — wait for many'],
        ['promise-race', 'study', 6, 'Promise.race()'],
        ['async-functions', 'study', 6, 'async functions'],
        ['await', 'study', 6, 'await — pause until resolved'],
        ['try-catch-async', 'study', 6, 'try / catch with async/await'],
        ['fetch', 'study', 6, 'fetch API — get data from a URL'],
        ['json', 'study', 6, 'Parsing JSON responses'],
        ['closures', 'study', 7, 'Closures — functions remembering their scope'],
        ['this', 'study', 7, 'The this keyword'],
        ['call-apply-bind', 'study', 7, 'call, apply, bind'],
        ['prototypes', 'study', 7, 'Prototypes & the prototype chain'],
        ['classes', 'study', 7, 'Classes — OOP in JavaScript'],
        ['inheritance', 'study', 7, 'Inheritance with extends & super'],
        ['event-loop', 'study', 7, 'The event loop — how JS really works'],
        ['microtasks', 'study', 7, 'Microtasks vs macrotasks'],
        ['es-modules', 'study', 7, 'ES Modules: import & export'],
        ['default-named-exports', 'study', 7, 'Default vs named exports'],
        ['npm', 'study', 7, 'npm basics'],
        ['bundlers', 'study', 7, 'Bundlers: what is Webpack / Vite?'],
        ['generators', 'study', 7, 'Generators & iterators'],
        ['weakmap', 'study', 7, 'WeakMap & WeakRef'],
        ['proxy', 'study', 7, 'Proxy & Reflect'],
        ['try-catch-finally', 'study', 7, 'try / catch / finally'],
        ['design-patterns', 'study', 7, 'Design patterns in JavaScript'],
        ['memory', 'study', 7, 'Memory management & garbage collection'],
        ['what-is-a-variable', 'study', 7, 'what-is-a-variable'],
        ['variables-let-const', 'practice', 1, 'Variables — let/const'],
        ['typeof', 'practice', 1, 'typeof'],
        ['type-coercion', 'practice', 1, 'Type coercion'],
        ['template-literals', 'practice', 1, 'Template literals'],
        ['equality', 'practice', 1, '== vs ==='],
        ['nullish-coalescing', 'practice', 1, 'Nullish coalescing ??'],
        ['if-else', 'practice', 2, 'if / else'],
        ['switch', 'practice', 2, 'switch'],
        ['ternary', 'practice', 2, 'Ternary operator'],
        ['for-loop', 'practice', 2, 'for loop'],
        ['while-loop', 'practice', 2, 'while loop'],
        ['for-of', 'practice', 2, 'for...of'],
        ['for-in', 'practice', 2, 'for...in'],
        ['function-declaration', 'practice', 3, 'Function declaration'],
        ['function-expression', 'practice', 3, 'Function expression'],
        ['arrow-functions', 'practice', 3, 'Arrow functions'],
        ['default-parameters', 'practice', 3, 'Default parameters'],
        ['return', 'practice', 3, 'Return statement'],
        ['callbacks', 'practice', 3, 'Callback functions'],
        ['scope', 'practice', 3, 'Scope'],
        ['arrays-basics', 'practice', 4, 'Creating arrays'],
        ['push-pop', 'practice', 4, 'push / pop / shift / unshift'],
        ['foreach', 'practice', 4, 'forEach'],
        ['map', 'practice', 4, 'map'],
        ['filter', 'practice', 4, 'filter'],
        ['reduce', 'practice', 4, 'reduce'],
        ['objects-key-value', 'practice', 4, 'Objects — key/value'],
        ['object-methods', 'practice', 4, 'Object.keys/values/entries'],
        ['destructuring', 'practice', 4, 'Destructuring'],
        ['spread', 'practice', 4, 'Spread operator'],
        ['getelementbyid', 'practice', 5, 'getElementById'],
        ['queryselector', 'practice', 5, 'querySelector'],
        ['textcontent', 'practice', 5, 'textContent'],
        ['changing-styles', 'practice', 5, 'Changing styles'],
        ['classlist', 'practice', 5, 'classList'],
        ['createelement', 'practice', 5, 'createElement'],
        ['input-event', 'practice', 5, 'input event'],
        ['event-object', 'practice', 5, 'Event object'],
        ['preventdefault', 'practice', 5, 'preventDefault'],
        ['settimeout', 'practice', 6, 'setTimeout'],
        ['setinterval', 'practice', 6, 'setInterval'],
        ['promises', 'practice', 6, 'Promises'],
        ['async-await', 'practice', 6, 'async/await'],
        ['fetch-json', 'practice', 6, 'fetch + JSON'],
        ['closures', 'practice', 7, 'Closures'],
        ['this', 'practice', 7, 'this keyword'],
        ['classes', 'practice', 7, 'Classes'],
        ['inheritance', 'practice', 7, 'Inheritance'],
        ['es-modules', 'practice', 7, 'ES Modules'],
        ['error-handling', 'practice', 7, 'Error handling'],
        ['event-loop', 'practice', 7, 'Event loop'],
        ['for-loop', 'practice', 7, 'for-loop']
    ];

    $count = 0;
    foreach ($lessons as $row) {
        $stmt->execute($row);
        $count++;
    }
    echo "Inserted $count lessons\n";

    echo "\n✓ Migration complete!\n";
} catch (Exception $e) {
    echo "\n✗ Error: " . $e->getMessage() . "\n";
}
echo '</pre>';
