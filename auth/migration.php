-- ─────────────────────────────────────────────────────────────
-- SalahHomty — DB migrations for auth features
-- Run once in phpMyAdmin or via MySQL CLI
-- ─────────────────────────────────────────────────────────────

-- 1. Add google_id column to users (for Google OAuth)
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS google_id VARCHAR(128) NULL DEFAULT NULL AFTER email;

-- 2. Password reset tokens table
CREATE TABLE IF NOT EXISTS password_resets (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email      VARCHAR(255)  NOT NULL,
  token      VARCHAR(64)   NOT NULL UNIQUE,
  expires_at DATETIME      NOT NULL,
  created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. (Optional) Add status column to reports if not already there
ALTER TABLE reports
  ADD COLUMN IF NOT EXISTS status VARCHAR(20) NOT NULL DEFAULT 'Open';