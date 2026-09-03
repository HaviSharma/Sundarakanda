-- Sundarakanda Donor Portal — database schema
-- Matches the "donors" database / "donors_app" user already set up on cPanel.
-- Run this once against an EMPTY `donors` database (phpMyAdmin: Import tab).
-- If your tables already exist as described, you do not need to run this.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  phone VARCHAR(30) NULL,
  address VARCHAR(255) NULL,
  email_subscribed TINYINT(1) NOT NULL DEFAULT 1,
  email_verified TINYINT(1) NOT NULL DEFAULT 0,
  verify_token VARCHAR(64) NULL,
  reset_token VARCHAR(64) NULL,
  reset_expires DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS donations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  currency VARCHAR(3) NOT NULL DEFAULT 'USD',
  purpose VARCHAR(150) NULL,
  payment_method VARCHAR(50) NULL,
  is_recurring TINYINT(1) NOT NULL DEFAULT 0,
  receipt_number VARCHAR(50) NOT NULL UNIQUE,
  notes TEXT NULL,
  donated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_donations_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_donations_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS subscribers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) NOT NULL UNIQUE,
  name VARCHAR(150) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  unsubscribe_token VARCHAR(64) NULL,
  subscribed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS daily_devotionals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  scheduled_date DATE NOT NULL UNIQUE,
  verse_text TEXT NOT NULL,
  translation TEXT NULL,
  reflection TEXT NULL,
  sent TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS email_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  devotional_id INT NULL,               -- NULL for one-off admin announcements (not tied to a daily devotional)
  recipient_email VARCHAR(190) NOT NULL,
  status VARCHAR(50) NOT NULL,
  sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_email_log_devotional FOREIGN KEY (devotional_id) REFERENCES daily_devotionals(id) ON DELETE SET NULL,
  INDEX idx_email_log_devotional (devotional_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
