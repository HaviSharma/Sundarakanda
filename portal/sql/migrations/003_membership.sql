-- Free membership registration: a public, no-cost sign-up for community
-- members who want website/email updates on events and Parayanam
-- activities. Separate from donor accounts (users) and devotional-only
-- subscribers (subscribers) since a member may be neither.
-- Additive-only: safe to run once, safe to re-run.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  phone VARCHAR(30) NULL,
  whatsapp_number VARCHAR(30) NULL,
  city VARCHAR(120) NULL,
  user_id INT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  welcome_email_sent TINYINT(1) NOT NULL DEFAULT 0,
  unsubscribe_token VARCHAR(64) NOT NULL,
  source VARCHAR(50) NOT NULL DEFAULT 'web',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_members_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_members_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
