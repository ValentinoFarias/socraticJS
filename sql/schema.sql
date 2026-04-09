-- =============================================================================
-- SocraticJS — Database Schema (Full ERD)
-- Run once on first setup. Docker will auto-run files in docker/mysql/init/
--
-- All primary keys use UUID (CHAR(36)) for globally unique IDs.
-- UUID() generates a new unique string like "550e8400-e29b-41d4-a716-446655440000"
-- =============================================================================

-- Use the correct database
USE jstutor;

-- =============================================================================
-- USER
-- Stores registered learner accounts.
-- =============================================================================
CREATE TABLE IF NOT EXISTS user (
  id            CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,               -- bcrypt hash via password_hash()
  created_at    DATETIME     NOT NULL DEFAULT NOW(),
  last_login_at DATETIME     NULL                    -- updated on each successful login
);

-- =============================================================================
-- PHASE
-- The 7 phases of the JS learning roadmap (e.g. "The Very Basics").
-- Seeded with INSERT statements below.
-- =============================================================================
CREATE TABLE IF NOT EXISTS phase (
  id           CHAR(36)    NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  number       INT         NOT NULL UNIQUE,          -- 1–7, used for ordering
  title        VARCHAR(100) NOT NULL,
  color_code   VARCHAR(20) NOT NULL,                 -- hex color, e.g. "#7C3AED"
  total_topics INT         NOT NULL DEFAULT 0        -- denormalized count for quick display
);

-- =============================================================================
-- TOPIC
-- A specific subject within a phase (e.g. "variables", "if-else").
-- mode: "study" = Socratic tutor chat | "real" = coding exercise (console)
-- =============================================================================
CREATE TABLE IF NOT EXISTS topic (
  id          CHAR(36)              NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  phase_id    CHAR(36)              NOT NULL,
  slug        VARCHAR(100)          NOT NULL UNIQUE,  -- URL-safe name, e.g. "variables"
  title       VARCHAR(150)          NOT NULL,
  order_index INT                   NOT NULL DEFAULT 0, -- order within the phase
  mode        ENUM('study', 'real') NOT NULL DEFAULT 'study',
  FOREIGN KEY (phase_id) REFERENCES phase(id)
);

-- =============================================================================
-- EXERCISE
-- A coding challenge inside a topic (console/practice mode).
-- starter_html / starter_js: pre-filled code the learner sees on load.
-- next_exercise_hint: a nudge shown after completing this exercise.
-- =============================================================================
CREATE TABLE IF NOT EXISTS exercise (
  id                 CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  topic_id           CHAR(36)     NOT NULL,
  number             INT          NOT NULL,            -- order within the topic
  title              VARCHAR(150) NOT NULL,
  task_description   TEXT         NOT NULL,            -- what the learner needs to do
  starter_html       TEXT         NULL,                -- optional HTML scaffold
  starter_js         TEXT         NULL,                -- optional JS scaffold
  difficulty         VARCHAR(50)  NOT NULL DEFAULT 'beginner',
  next_exercise_hint TEXT         NULL,                -- hint shown after completion
  FOREIGN KEY (topic_id) REFERENCES topic(id)
);

-- =============================================================================
-- CHECK_RULE
-- Auto-grading rules for an exercise. Each rule runs a JS expression
-- against the learner's submission to check correctness.
-- js_expression: evaluated in a sandbox, e.g. "typeof myVar !== 'undefined'"
-- =============================================================================
CREATE TABLE IF NOT EXISTS check_rule (
  id          CHAR(36)     NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  exercise_id CHAR(36)     NOT NULL,
  order_index INT          NOT NULL DEFAULT 0,         -- which check runs first
  label       VARCHAR(200) NOT NULL,                   -- human-readable description
  js_expression TEXT       NOT NULL,                   -- expression evaluated to true/false
  FOREIGN KEY (exercise_id) REFERENCES exercise(id)
);

-- =============================================================================
-- USER_PROGRESS
-- Tracks where each user is within the curriculum.
-- One row per (user, phase, topic) combination.
-- status lifecycle: not_started → in_progress → complete
-- =============================================================================
CREATE TABLE IF NOT EXISTS user_progress (
  id           CHAR(36)                                    NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  user_id      CHAR(36)                                    NOT NULL,
  phase_id     CHAR(36)                                    NOT NULL,
  topic_id     CHAR(36)                                    NOT NULL,
  status       ENUM('not_started', 'in_progress', 'complete') NOT NULL DEFAULT 'not_started',
  started_at   DATETIME NULL,                              -- set when status → in_progress
  completed_at DATETIME NULL,                              -- set when status → complete
  UNIQUE KEY uq_user_topic (user_id, topic_id),            -- one row per user+topic
  FOREIGN KEY (user_id)  REFERENCES user(id),
  FOREIGN KEY (phase_id) REFERENCES phase(id),
  FOREIGN KEY (topic_id) REFERENCES topic(id)
);

-- =============================================================================
-- ATTEMPT
-- A learner's code submission for a specific exercise.
-- checks_passed / checks_total: how many CHECK_RULEs the code satisfied.
-- passed: true if checks_passed === checks_total (all rules green).
-- =============================================================================
CREATE TABLE IF NOT EXISTS attempt (
  id             CHAR(36)  NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  user_id        CHAR(36)  NOT NULL,
  exercise_id    CHAR(36)  NOT NULL,
  submitted_js   TEXT      NOT NULL,                   -- the learner's JS code
  submitted_html TEXT      NULL,                       -- optional HTML (if exercise uses it)
  passed         BOOLEAN   NOT NULL DEFAULT FALSE,
  checks_passed  INT       NOT NULL DEFAULT 0,
  checks_total   INT       NOT NULL DEFAULT 0,
  submitted_at   DATETIME  NOT NULL DEFAULT NOW(),
  FOREIGN KEY (user_id)     REFERENCES user(id),
  FOREIGN KEY (exercise_id) REFERENCES exercise(id)
);

-- =============================================================================
-- AI_REVIEW
-- Claude's feedback on a specific attempt.
-- One review per attempt (UNIQUE on attempt_id).
-- score: 0–100 quality score returned by the model.
-- feedback_markdown: full Socratic feedback rendered in the UI.
-- =============================================================================
CREATE TABLE IF NOT EXISTS ai_review (
  id                CHAR(36) NOT NULL DEFAULT (UUID()) PRIMARY KEY,
  attempt_id        CHAR(36) NOT NULL UNIQUE,           -- one review per attempt
  feedback_markdown TEXT     NOT NULL,
  score             INT      NOT NULL DEFAULT 0,        -- 0–100
  generated_at      DATETIME NOT NULL DEFAULT NOW(),
  FOREIGN KEY (attempt_id) REFERENCES attempt(id)
);

-- =============================================================================
-- SEED DATA — Phases
-- These match the 7-phase JS Roadmap defined in CLAUDE.md.
-- INSERT IGNORE skips the row silently if it already exists (idempotent).
-- =============================================================================
INSERT IGNORE INTO phase (id, number, title, color_code, total_topics) VALUES
  (UUID(), 1, 'The Very Basics',      '#7C3AED', 0),
  (UUID(), 2, 'Control Flow',         '#6D28D9', 0),
  (UUID(), 3, 'Functions',            '#5B21B6', 0),
  (UUID(), 4, 'Arrays & Objects',     '#4C1D95', 0),
  (UUID(), 5, 'The DOM & Events',     '#0EA5E9', 0),
  (UUID(), 6, 'Async JavaScript',     '#0284C7', 0),
  (UUID(), 7, 'Advanced & Modern JS', '#0369A1', 0);
