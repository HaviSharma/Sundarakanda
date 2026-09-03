-- Migration 002: One Crore Parayanam campaign platform + RBAC + audit log
-- Additive only — does not alter or drop any existing table/column.
-- Safe to run once against the existing `donors` database.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---- RBAC: add a role to the existing users table (default keeps every
-- existing donor exactly as they are today) --------------------------------
ALTER TABLE users
  ADD COLUMN IF NOT EXISTS role ENUM('participant','operations_manager','admin') NOT NULL DEFAULT 'participant' AFTER email_verified;

-- ---- Site-editable settings (contact info, hero copy, verified baselines) -
CREATE TABLE IF NOT EXISTS site_settings (
  setting_key VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NULL,
  updated_by INT NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_settings_user FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Campaigns --------------------------------------------------------
CREATE TABLE IF NOT EXISTS campaigns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  description TEXT NULL,
  target_count BIGINT NOT NULL,
  start_date DATE NULL,
  end_date DATE NULL,
  status ENUM('draft','active','completed','archived') NOT NULL DEFAULT 'active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Participation entries (individual digital submissions) -----------
CREATE TABLE IF NOT EXISTS participation_entries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  user_id INT NOT NULL,
  participation_date DATE NOT NULL,
  count INT NOT NULL,
  group_id INT NULL,
  notes TEXT NULL,
  source VARCHAR(50) NOT NULL DEFAULT 'web',
  status ENUM('approved','pending','rejected') NOT NULL DEFAULT 'approved',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_participation_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_participation_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT chk_participation_count CHECK (count > 0),
  INDEX idx_participation_campaign (campaign_id, status),
  INDEX idx_participation_user (user_id),
  INDEX idx_participation_date (participation_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Historical / admin adjustments (never a raw editable total) ------
CREATE TABLE IF NOT EXISTS campaign_adjustments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  campaign_id INT NOT NULL,
  adjustment_count BIGINT NOT NULL,
  effective_date DATE NOT NULL,
  reason VARCHAR(255) NOT NULL,
  internal_notes TEXT NULL,
  entered_by INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_adjustment_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
  CONSTRAINT fk_adjustment_user FOREIGN KEY (entered_by) REFERENCES users(id),
  INDEX idx_adjustment_campaign (campaign_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Audit log (every sensitive/admin write) ---------------------------
CREATE TABLE IF NOT EXISTS audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  action VARCHAR(100) NOT NULL,
  record_type VARCHAR(100) NOT NULL,
  record_id INT NULL,
  old_value TEXT NULL,
  new_value TEXT NULL,
  performed_by INT NULL,
  performed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_user FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_audit_record (record_type, record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---- Seed the campaign itself ------------------------------------------
INSERT INTO campaigns (name, slug, description, target_count, status)
VALUES (
  '1 Crore Hanuman Jayanti Parayanam',
  'one-crore-parayanam',
  'A collective prayer for universal peace — 1,00,00,000 Parayanams offered together by the Sundarakanda community.',
  100000000,
  'active'
)
ON DUPLICATE KEY UPDATE name = VALUES(name);

SET FOREIGN_KEY_CHECKS = 1;
