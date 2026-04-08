# CLAUDE.md — SocraticJS

## About This Project

SocraticJS is a JavaScript learning platform for **complete beginners**.
It is being built as a **learning project** — the primary goal is to learn **PHP and MySQL** by building something real.

The platform teaches JavaScript using the **Socratic method**: never hand out answers, always guide learners to discover them through questions, experimentation, and reflection.

---

## Code Style

- I am a junior developer — always add **clear comments** explaining what the code does
- Explain **why**, not just what
- Keep code **readable and educational** — avoid clever one-liners that obscure meaning
- When introducing a new PHP or MySQL concept, briefly explain it inline

---

## Git Commits

At the end of each session, summarize what was done and suggest a commit message:

```
type(scope): short description
```

Examples:
```
feat(auth): add login form and session handling
feat(db): create users and progress tables
fix(tutor): correct API fetch error handling
```

---

## Tech Stack

| Layer | Technology | Rules |
|---|---|---|
| **Backend** | PHP 8.3 | Plain PHP only — no Laravel, no Symfony |
| **Database** | MySQL 8.0 | Raw SQL + PDO — no ORMs |
| **Frontend** | Vanilla JS + HTML + CSS | No React, no Vue, no bundlers, no npm |
| **Infrastructure** | Docker (macOS) | `docker-compose.yml` already configured |
| **Auth** | PHP sessions | Custom — no auth libraries |
| **Progress tracking** | MySQL | Per-user phase/topic completion |

---

## Docker Setup

Already configured and running. Do not change `docker-compose.yml` without asking.

| Service | Container | Port |
|---|---|---|
| PHP 8.3 + Apache | `jstutor_php` | `http://localhost:8080` |
| MySQL 8.0 | `jstutor_db` | `3306` |
| phpMyAdmin | `jstutor_phpmyadmin` | `http://localhost:8081` |

### Database credentials
```
Host:     db  (inside Docker network) / localhost (outside)
Database: jstutor
User:     jstutor_user
Password: jstutor_pass
```

### PHP Dockerfile (docker/php/Dockerfile)
- Base image: `php:8.3-apache`
- Extensions: `pdo`, `pdo_mysql`
- Apache: `mod_rewrite` enabled
- Webroot: `/var/www/html` → mapped to `./src`

---

## Project Structure

```
socraticjs/
├── docker-compose.yml
├── docker/
│   ├── php/
│   │   └── Dockerfile
│   └── mysql/
│       └── init/               # SQL files auto-run on first container start
├── src/                        # Apache webroot → http://localhost:8080
│   ├── index.php               # Landing / home page
│   ├── about.php               # About page
│   ├── study.php               # JS Tutor — list of topics to start a conversation
│   ├── tutor.php               # JS Tutor — chat interface
│   ├── practice.php            # JS Console — list of topics to select
│   ├── console.php             # JS Console — coding challenge
│   ├── login.php               # Login page
│   ├── register.php            # Register page
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   └── js/
│   │       └── main.js
│   └── includes/
│       ├── db.php              # PDO connection helper
│       ├── auth.php            # Session helpers (login, logout, guard)
│       └── functions.php       # Shared utility functions
└── sql/
    └── schema.sql              # Full database schema
```

---

## CSS Organisation

All styles live in `src/assets/css/style.css`. Always respect this section order:

1. **CSS Custom Properties / Design Tokens** — `:root` variables
2. **Base / Global Reset** — `html`, `body`, element resets
3. **Navigation** — shared nav styles
4. **Landing Page** — `index.php`
5. **About Page** — `about.php`
6. **Study Page** — `study.php` (topic picker)
7. **Tutor Page** — `tutor.php` (chat UI)
8. **Practice Page** — `practice.php` (topic picker)
9. **Console Page** — `console.php` (coding challenge)
10. **Login / Register Pages** — `login.php`, `register.php`
11. **New pages** — insert here, before media queries
12. **All Media Queries** — collected at the very bottom, smallest → largest breakpoint

### Rules
- **Never scatter `@media` blocks** throughout the file — all go at the bottom
- Each section opens with a comment header:
```css
/* =============================================================================
   SECTION NAME  (filename.php)
   Brief description of what this section covers.
   ============================================================================= */
```
- Only add tokens to `:root` if they are reused in 2+ places — no one-off variables

---

## Design System

```css
:root {
  /* Brand */
  --color-tutor: #7C3AED;         /* Purple — JS Tutor */
  --color-tutor-light: #A78BFA;
  --color-console: #0EA5E9;       /* Blue — JS Console */
  --color-console-light: #38BDF8;

  /* Neutrals (dark mode base) */
  --color-bg: #0F0F0F;
  --color-surface: #1A1A1A;
  --color-surface-2: #242424;
  --color-border: #2E2E2E;
  --color-text: #F0F0F0;
  --color-text-muted: #888888;

  /* Semantic */
  --color-success: #22C55E;
  --color-error: #EF4444;
  --color-warning: #F59E0B;

  /* Typography */
  --font-sans: 'Inter', system-ui, sans-serif;
  --font-mono: 'JetBrains Mono', 'Fira Code', monospace;
}
```

- **Dark mode by default**
- Purple = JS Tutor, Blue = JS Console
- UI font: Inter — Code font: JetBrains Mono

---

## Database Schema

```sql
-- Users
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Progress tracking (per user, per phase/topic)
CREATE TABLE progress (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  phase INT NOT NULL,             -- 1–7
  topic VARCHAR(100) NOT NULL,    -- e.g. "variables", "if-else"
  completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
```

Always use **prepared statements** with PDO. Never concatenate user input into SQL.

---

## PHP Guidelines

```php
<?php
// db.php — always connect like this
$pdo = new PDO(
    'mysql:host=db;dbname=jstutor;charset=utf8mb4',
    'jstutor_user',
    'jstutor_pass',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Passwords — always hash, never store plain text
$hash = password_hash($password, PASSWORD_DEFAULT);
$valid = password_verify($input, $hash);

// Prepared statement example
$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
```

- Use `require_once` for includes, not `include`
- Start every protected page with a session check (via `auth.php`)
- Keep files small and focused — one responsibility per file

---

## JS Tutor — Anthropic API

The Tutor chat calls the Anthropic API from the frontend via `fetch`.

**Model:** `claude-sonnet-4-20250514`

**System prompt:**
```
You are a patient, encouraging JavaScript tutor for complete beginners using the
Socratic method. Never give answers directly — always guide the learner to discover
answers through questions. Follow the 7-phase JS roadmap. Ask one question at a time.
Celebrate small wins. Keep code examples under 10 lines and always use console.log().
```

**7-Phase JS Roadmap:**

| Phase | Title |
|---|---|
| 1 | The Very Basics |
| 2 | Control Flow |
| 3 | Functions |
| 4 | Arrays & Objects |
| 5 | The DOM & Events |
| 6 | Async JavaScript |
| 7 | Advanced & Modern JS |

---

## Pages & Auth

| Page | File | Auth required |
|---|---|---|
| Landing | `index.php` | No |
| About | `about.php` | No |
| Topic picker (Tutor) | `study.php` | Yes |
| Chat (Tutor) | `tutor.php` | Yes |
| Topic picker (Console) | `practice.php` | Yes |
| Coding challenge | `console.php` | Yes |
| Login | `login.php` | No |
| Register | `register.php` | No |

---

## Rules — Always Ask Before

- Changing the database schema
- Restructuring files or folders
- Adding new dependencies or Docker services
- Making assumptions about what's already been built — ask to see the code first

---

## Build Checklist

- [x] Docker setup (PHP + MySQL + phpMyAdmin)
- [ ] `sql/schema.sql` — users + progress tables
- [ ] `includes/db.php` — PDO connection
- [ ] `includes/auth.php` — session helpers
- [ ] `register.php` — user signup
- [ ] `login.php` — user login
- [ ] `index.php` — landing page
- [ ] `about.php` — about page
- [ ] `study.php` — tutor topic picker
- [ ] `tutor.php` — chat interface
- [ ] `practice.php` — console topic picker
- [ ] `console.php` — coding challenge