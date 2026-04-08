-- =============================================================================
-- SocraticJS — Database Schema
-- Run once on first setup. Docker will auto-run this on container creation
-- if placed in docker/mysql/init/
-- =============================================================================

-- Users table — stores account credentials
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50)  NOT NULL UNIQUE,
  email         VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Progress table — tracks which topics each user has completed
-- phase: 1–7 (JS Roadmap phases)
-- topic: short slug like "variables", "if-else"
CREATE TABLE IF NOT EXISTS progress (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT          NOT NULL,
  phase        INT          NOT NULL,
  topic        VARCHAR(100) NOT NULL,
  completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);
