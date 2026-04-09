-- =============================================================================
-- SocraticJS — Database Schema
-- Run once on first setup. Docker auto-runs files placed in docker/mysql/init/
--
-- Hierarchy:
--   phase  (7 phases of the JS roadmap)
--     └── topic   (collapsible groups, e.g. "Variables", "Data Types")
--           └── lesson  (individual checkable items, e.g. "What is a variable?")
--                 ├── [study mode]    → user_progress (studied checkbox)
--                 └── [practice mode] → exercise → attempt → ai_review
-- =============================================================================

USE jstutor;

-- =============================================================================
-- USER
-- Stores registered learner accounts.
-- =============================================================================
CREATE TABLE IF NOT EXISTS user (
  id            CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,         -- bcrypt hash via password_hash()
  created_at    DATETIME     NOT NULL DEFAULT NOW(),
  last_login_at DATETIME     NULL              -- updated on each successful login
);

-- =============================================================================
-- PHASE
-- The 7 phases of the JS learning roadmap.
-- =============================================================================
CREATE TABLE IF NOT EXISTS phase (
  id         CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  number     INT          NOT NULL UNIQUE,    -- 1–7, used for ordering
  title      VARCHAR(100) NOT NULL,
  color_code VARCHAR(20)  NOT NULL            -- hex color for the UI, e.g. "#7C3AED"
);

-- =============================================================================
-- TOPIC
-- Collapsible groups within a phase — the <details> accordion sections.
-- Examples: "Variables", "Data Types", "Operators" (all inside Phase 1).
-- A topic is just a container — it has no slug and no mode.
-- =============================================================================
CREATE TABLE IF NOT EXISTS topic (
  id          CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  phase_id    CHAR(36)     NOT NULL,
  title       VARCHAR(150) NOT NULL,
  order_index INT          NOT NULL DEFAULT 0,  -- order within the phase
  FOREIGN KEY (phase_id) REFERENCES phase(id)
);

-- =============================================================================
-- LESSON
-- Individual learnable items — the <li> checkboxes inside each topic.
-- Examples: "What is a variable?", "let — block-scoped, reassignable".
--
-- slug: used in the URL, e.g. tutor.php?topic=what-is-a-variable
-- mode: 'study'    → opens the Socratic tutor chat    (tutor.php)
--       'practice' → opens a coding exercise           (console.php)
-- =============================================================================
CREATE TABLE IF NOT EXISTS lesson (
  id          CHAR(36)                  NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  topic_id    CHAR(36)                  NOT NULL,
  slug        VARCHAR(100)              NOT NULL UNIQUE,
  title       VARCHAR(200)              NOT NULL,
  order_index INT                       NOT NULL DEFAULT 0,
  mode        ENUM('study', 'practice') NOT NULL DEFAULT 'study',
  FOREIGN KEY (topic_id) REFERENCES topic(id)
);

-- =============================================================================
-- EXERCISE
-- A coding challenge for a practice-mode lesson.
-- starter_html / starter_js: pre-filled code shown in the editor on load.
-- next_exercise_hint: hint shown after the learner passes all checks.
-- =============================================================================
CREATE TABLE IF NOT EXISTS exercise (
  id                 CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  lesson_id          CHAR(36)     NOT NULL,
  title              VARCHAR(150) NOT NULL,
  task_description   TEXT         NOT NULL,
  starter_html       TEXT         NULL,
  starter_js         TEXT         NULL,
  difficulty         VARCHAR(50)  NOT NULL DEFAULT 'beginner',
  next_exercise_hint TEXT         NULL,
  FOREIGN KEY (lesson_id) REFERENCES lesson(id)
);

-- =============================================================================
-- CHECK_RULE
-- Auto-grading rules for an exercise.
-- js_expression is evaluated as a boolean inside the preview iframe sandbox.
-- Example: /console\.log\s*\(/.test(code)
-- =============================================================================
CREATE TABLE IF NOT EXISTS check_rule (
  id            CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  exercise_id   CHAR(36)     NOT NULL,
  order_index   INT          NOT NULL DEFAULT 0,
  label         VARCHAR(200) NOT NULL,   -- shown to the learner, e.g. "Used console.log"
  js_expression TEXT         NOT NULL,   -- evaluated to true/false
  FOREIGN KEY (exercise_id) REFERENCES exercise(id)
);

-- =============================================================================
-- USER_PROGRESS
-- Tracks which lessons a user has studied — one row per (user, lesson).
-- status lifecycle: not_started → in_progress → complete
-- studied_at: set when the user checks the "mark as studied" checkbox.
-- =============================================================================
CREATE TABLE IF NOT EXISTS user_progress (
  id         CHAR(36)                                       NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  user_id    CHAR(36)                                       NOT NULL,
  lesson_id  CHAR(36)                                       NOT NULL,
  status     ENUM('not_started', 'in_progress', 'complete') NOT NULL DEFAULT 'not_started',
  studied_at DATETIME NULL,
  UNIQUE KEY uq_user_lesson (user_id, lesson_id),
  FOREIGN KEY (user_id)   REFERENCES user(id),
  FOREIGN KEY (lesson_id) REFERENCES lesson(id)
);

-- =============================================================================
-- ATTEMPT
-- A learner's code submission for a practice exercise.
-- passed = TRUE when checks_passed === checks_total (all rules green).
-- =============================================================================
CREATE TABLE IF NOT EXISTS attempt (
  id             CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  user_id        CHAR(36) NOT NULL,
  exercise_id    CHAR(36) NOT NULL,
  submitted_js   TEXT     NOT NULL,
  submitted_html TEXT     NULL,
  passed         BOOLEAN  NOT NULL DEFAULT FALSE,
  checks_passed  INT      NOT NULL DEFAULT 0,
  checks_total   INT      NOT NULL DEFAULT 0,
  submitted_at   DATETIME NOT NULL DEFAULT NOW(),
  FOREIGN KEY (user_id)     REFERENCES user(id),
  FOREIGN KEY (exercise_id) REFERENCES exercise(id)
);

-- =============================================================================
-- AI_REVIEW
-- Claude's Socratic feedback on a specific attempt.
-- One review per attempt (UNIQUE constraint on attempt_id).
-- score: 0–100 quality score from the model.
-- =============================================================================
CREATE TABLE IF NOT EXISTS ai_review (
  id                CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  attempt_id        CHAR(36) NOT NULL UNIQUE,
  feedback_markdown TEXT     NOT NULL,
  score             INT      NOT NULL DEFAULT 0,
  generated_at      DATETIME NOT NULL DEFAULT NOW(),
  FOREIGN KEY (attempt_id) REFERENCES attempt(id)
);


-- =============================================================================
-- SEED DATA
-- Phases, topics, and lessons matching study.php exactly.
-- Uses SET @variable to hold UUIDs so foreign keys resolve correctly.
-- =============================================================================

-- ── Phases ────────────────────────────────────────────────────────────────────
SET @p1 = UUID(); SET @p2 = UUID(); SET @p3 = UUID(); SET @p4 = UUID();
SET @p5 = UUID(); SET @p6 = UUID(); SET @p7 = UUID();

INSERT INTO phase (id, number, title, color_code) VALUES
  (@p1, 1, 'The Very Basics',      '#7C3AED'),
  (@p2, 2, 'Control Flow',         '#6D28D9'),
  (@p3, 3, 'Functions',            '#5B21B6'),
  (@p4, 4, 'Arrays & Objects',     '#4C1D95'),
  (@p5, 5, 'The DOM & Events',     '#0EA5E9'),
  (@p6, 6, 'Async JavaScript',     '#0284C7'),
  (@p7, 7, 'Advanced & Modern JS', '#0369A1');


-- ── Topics (the collapsible <details> groups) ──────────────────────────────────

-- Phase 1
SET @t1 = UUID(); SET @t2 = UUID(); SET @t3 = UUID();
INSERT INTO topic (id, phase_id, title, order_index) VALUES
  (@t1, @p1, 'Variables',  1),
  (@t2, @p1, 'Data Types', 2),
  (@t3, @p1, 'Operators',  3);

-- Phase 2
SET @t4 = UUID(); SET @t5 = UUID();
INSERT INTO topic (id, phase_id, title, order_index) VALUES
  (@t4, @p2, 'Conditionals', 1),
  (@t5, @p2, 'Loops',        2);

-- Phase 3
SET @t6 = UUID(); SET @t7 = UUID();
INSERT INTO topic (id, phase_id, title, order_index) VALUES
  (@t6, @p3, 'Defining Functions', 1),
  (@t7, @p3, 'Scope & Advanced',   2);

-- Phase 4
SET @t8 = UUID(); SET @t9 = UUID();
INSERT INTO topic (id, phase_id, title, order_index) VALUES
  (@t8, @p4, 'Arrays',  1),
  (@t9, @p4, 'Objects', 2);

-- Phase 5
SET @t10 = UUID(); SET @t11 = UUID();
INSERT INTO topic (id, phase_id, title, order_index) VALUES
  (@t10, @p5, 'The DOM', 1),
  (@t11, @p5, 'Events',  2);

-- Phase 6
SET @t12 = UUID(); SET @t13 = UUID(); SET @t14 = UUID();
INSERT INTO topic (id, phase_id, title, order_index) VALUES
  (@t12, @p6, 'Timers & Concepts', 1),
  (@t13, @p6, 'Promises',          2),
  (@t14, @p6, 'async / await',     3);

-- Phase 7
SET @t15 = UUID(); SET @t16 = UUID(); SET @t17 = UUID();
INSERT INTO topic (id, phase_id, title, order_index) VALUES
  (@t15, @p7, 'Core Advanced Concepts', 1),
  (@t16, @p7, 'Modules & Tooling',      2),
  (@t17, @p7, 'Deep Dives',             3);


-- ── Lessons (the individual <li> items — all mode='study' for now) ─────────────

-- Variables
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t1, 'what-is-a-variable', 'What is a variable?',                        1, 'study'),
  (UUID(), @t1, 'let',                'let — block-scoped, reassignable',            2, 'study'),
  (UUID(), @t1, 'const',              'const — block-scoped, fixed binding',         3, 'study'),
  (UUID(), @t1, 'var',                'var — function-scoped, hoisted (legacy)',      4, 'study'),
  (UUID(), @t1, 'hoisting',           'Hoisting — what it means & why it matters',   5, 'study'),
  (UUID(), @t1, 'naming-rules',       'Naming rules & conventions',                  6, 'study');

-- Data Types
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t2, 'primitives',        'Primitives: string, number, boolean',             1, 'study'),
  (UUID(), @t2, 'null-vs-undefined', 'null vs undefined — the key difference',          2, 'study'),
  (UUID(), @t2, 'typeof',            'typeof — checking a value\'s type',               3, 'study'),
  (UUID(), @t2, 'type-coercion',     'Type coercion — when JS changes types silently',  4, 'study'),
  (UUID(), @t2, 'template-literals', 'Template literals — backtick strings',            5, 'study'),
  (UUID(), @t2, 'symbol-bigint',     'Symbol & BigInt (bonus primitives)',               6, 'study');

-- Operators
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t3, 'arithmetic',       'Arithmetic: + - * / % **',                       1, 'study'),
  (UUID(), @t3, 'assignment',       'Assignment: = += -= *= /=',                      2, 'study'),
  (UUID(), @t3, 'equality',         '== vs === — loose vs strict equality',            3, 'study'),
  (UUID(), @t3, 'logical',          'Logical: && || ! (AND, OR, NOT)',                 4, 'study'),
  (UUID(), @t3, 'nullish-optional', 'Nullish coalescing ?? and optional chaining ?.', 5, 'study'),
  (UUID(), @t3, 'increment',        'Increment / decrement: ++ --',                   6, 'study');

-- Conditionals
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t4, 'if-else', 'if / else statements',                1, 'study'),
  (UUID(), @t4, 'else-if', 'else if — chaining conditions',        2, 'study'),
  (UUID(), @t4, 'switch',  'switch statements',                    3, 'study'),
  (UUID(), @t4, 'ternary', 'Ternary operator: condition ? a : b',  4, 'study');

-- Loops
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t5, 'for-loop',  'for loop — counting iterations',    1, 'study'),
  (UUID(), @t5, 'while-loop','while loop — run while true',       2, 'study'),
  (UUID(), @t5, 'do-while',  'do...while loop',                   3, 'study'),
  (UUID(), @t5, 'for-of',    'for...of — iterating arrays',       4, 'study'),
  (UUID(), @t5, 'for-in',    'for...in — iterating object keys',  5, 'study'),
  (UUID(), @t5, 'break',     'break — stop the loop early',       6, 'study'),
  (UUID(), @t5, 'continue',  'continue — skip to next iteration', 7, 'study');

-- Defining Functions
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t6, 'function-declarations', 'Function declarations',     1, 'study'),
  (UUID(), @t6, 'function-expressions',  'Function expressions',      2, 'study'),
  (UUID(), @t6, 'arrow-functions',       'Arrow functions: () => {}', 3, 'study'),
  (UUID(), @t6, 'parameters',            'Parameters and arguments',  4, 'study'),
  (UUID(), @t6, 'default-parameters',    'Default parameters',        5, 'study'),
  (UUID(), @t6, 'return',                'The return statement',      6, 'study');

-- Scope & Advanced
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t7, 'scope-local-global', 'Local vs global scope',                          1, 'study'),
  (UUID(), @t7, 'block-scope',        'Block scope with let & const',                   2, 'study'),
  (UUID(), @t7, 'callbacks',          'Callback functions',                             3, 'study'),
  (UUID(), @t7, 'higher-order',       'Higher-order functions',                         4, 'study'),
  (UUID(), @t7, 'pure-functions',     'Pure functions — no side effects',               5, 'study'),
  (UUID(), @t7, 'iife',               'IIFE — immediately invoked function expression', 6, 'study');

-- Arrays
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t8, 'arrays-basics',    'Creating arrays and accessing items', 1, 'study'),
  (UUID(), @t8, 'push-pop',         'push, pop, shift, unshift',           2, 'study'),
  (UUID(), @t8, 'foreach',          'forEach — loop over every item',      3, 'study'),
  (UUID(), @t8, 'map',              'map — transform every item',          4, 'study'),
  (UUID(), @t8, 'filter',           'filter — keep matching items',        5, 'study'),
  (UUID(), @t8, 'reduce',           'reduce — collapse to a single value', 6, 'study'),
  (UUID(), @t8, 'find',             'find & findIndex',                    7, 'study'),
  (UUID(), @t8, 'includes-indexof', 'includes, indexOf, some, every',      8, 'study');

-- Objects
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t9, 'key-value',        'Key-value pairs',                                   1, 'study'),
  (UUID(), @t9, 'dot-bracket',      'Dot notation vs bracket notation',                  2, 'study'),
  (UUID(), @t9, 'add-delete-props', 'Adding and deleting properties',                    3, 'study'),
  (UUID(), @t9, 'for-in-objects',   'Looping with for...in',                            4, 'study'),
  (UUID(), @t9, 'object-methods',   'Object.keys(), Object.values(), Object.entries()',  5, 'study'),
  (UUID(), @t9, 'destructuring',    'Destructuring arrays and objects',                  6, 'study'),
  (UUID(), @t9, 'spread',           'Spread operator ...',                               7, 'study'),
  (UUID(), @t9, 'rest',             'Rest operator in functions',                        8, 'study');

-- The DOM
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t10, 'what-is-dom',    'What is the DOM?',                          1, 'study'),
  (UUID(), @t10, 'getelementbyid', 'document.getElementById()',                 2, 'study'),
  (UUID(), @t10, 'queryselector',  'querySelector & querySelectorAll',          3, 'study'),
  (UUID(), @t10, 'textcontent',    'Changing textContent',                      4, 'study'),
  (UUID(), @t10, 'innerhtml',      'Changing innerHTML',                        5, 'study'),
  (UUID(), @t10, 'style-prop',     'Changing CSS styles with .style',           6, 'study'),
  (UUID(), @t10, 'classlist',      'Adding & removing CSS classes',             7, 'study'),
  (UUID(), @t10, 'createelement',  'Creating elements: createElement',           8, 'study'),
  (UUID(), @t10, 'appendchild',    'Adding to the page: appendChild, append',   9, 'study'),
  (UUID(), @t10, 'remove',         'Removing elements: remove()',              10, 'study');

-- Events
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t11, 'addeventlistener',   'addEventListener basics',         1, 'study'),
  (UUID(), @t11, 'click-input-submit', 'click, input, submit events',     2, 'study'),
  (UUID(), @t11, 'event-object',       'The event object',                3, 'study'),
  (UUID(), @t11, 'preventdefault',     'preventDefault()',                4, 'study'),
  (UUID(), @t11, 'event-delegation',   'Event delegation',                5, 'study'),
  (UUID(), @t11, 'remove-listener',    'Removing event listeners',        6, 'study');

-- Timers & Concepts
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t12, 'what-is-async', 'What is asynchronous JavaScript?',      1, 'study'),
  (UUID(), @t12, 'call-stack',    'The call stack — how JS executes code', 2, 'study'),
  (UUID(), @t12, 'settimeout',    'setTimeout — delay a function',          3, 'study'),
  (UUID(), @t12, 'setinterval',   'setInterval — repeat a function',        4, 'study'),
  (UUID(), @t12, 'cleartimer',    'clearTimeout & clearInterval',           5, 'study');

-- Promises
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t13, 'what-is-promise', 'What is a Promise?',            1, 'study'),
  (UUID(), @t13, 'then-catch',      '.then() and .catch()',           2, 'study'),
  (UUID(), @t13, 'promise-all',     'Promise.all() — wait for many', 3, 'study'),
  (UUID(), @t13, 'promise-race',    'Promise.race()',                 4, 'study');

-- async / await
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t14, 'async-functions', 'async functions',                  1, 'study'),
  (UUID(), @t14, 'await',           'await — pause until resolved',     2, 'study'),
  (UUID(), @t14, 'try-catch-async', 'try / catch with async/await',     3, 'study'),
  (UUID(), @t14, 'fetch',           'fetch API — get data from a URL',  4, 'study'),
  (UUID(), @t14, 'json',            'Parsing JSON responses',           5, 'study');

-- Core Advanced Concepts
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t15, 'closures',        'Closures — functions remembering their scope', 1, 'study'),
  (UUID(), @t15, 'this',            'The this keyword',                             2, 'study'),
  (UUID(), @t15, 'call-apply-bind', 'call, apply, bind',                            3, 'study'),
  (UUID(), @t15, 'prototypes',      'Prototypes & the prototype chain',              4, 'study'),
  (UUID(), @t15, 'classes',         'Classes — OOP in JavaScript',                  5, 'study'),
  (UUID(), @t15, 'inheritance',     'Inheritance with extends & super',              6, 'study'),
  (UUID(), @t15, 'event-loop',      'The event loop — how JS really works',         7, 'study'),
  (UUID(), @t15, 'microtasks',      'Microtasks vs macrotasks',                     8, 'study');

-- Modules & Tooling
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t16, 'es-modules',            'ES Modules: import & export',       1, 'study'),
  (UUID(), @t16, 'default-named-exports', 'Default vs named exports',          2, 'study'),
  (UUID(), @t16, 'npm',                   'npm basics',                        3, 'study'),
  (UUID(), @t16, 'bundlers',              'Bundlers: what is Webpack / Vite?', 4, 'study');

-- Deep Dives
INSERT INTO lesson (id, topic_id, slug, title, order_index, mode) VALUES
  (UUID(), @t17, 'generators',       'Generators & iterators',                  1, 'study'),
  (UUID(), @t17, 'weakmap',          'WeakMap & WeakRef',                       2, 'study'),
  (UUID(), @t17, 'proxy',            'Proxy & Reflect',                         3, 'study'),
  (UUID(), @t17, 'try-catch-finally','try / catch / finally',                   4, 'study'),
  (UUID(), @t17, 'design-patterns',  'Design patterns in JavaScript',           5, 'study'),
  (UUID(), @t17, 'memory',           'Memory management & garbage collection',  6, 'study');
