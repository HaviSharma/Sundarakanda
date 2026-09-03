-- Sundarakanda Events Platform
-- Adds events management and RSVP tracking

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Events table
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  slug VARCHAR(200) NOT NULL UNIQUE,
  banner_image VARCHAR(255) NULL,
  event_mode ENUM('online', 'in_person', 'hybrid') NOT NULL DEFAULT 'hybrid',
  zoom_link VARCHAR(500) NULL,
  address VARCHAR(500) NULL,
  description LONGTEXT NOT NULL,
  event_date DATE NOT NULL,
  start_time TIME NULL,
  end_time TIME NULL,
  rsvp_link VARCHAR(500) NULL,
  status ENUM('upcoming', 'past', 'archived') NOT NULL DEFAULT 'upcoming',
  created_by INT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_events_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT,
  INDEX idx_events_date (event_date),
  INDEX idx_events_status (status),
  INDEX idx_events_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Event RSVPs table
CREATE TABLE IF NOT EXISTS event_rsvps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  user_id INT NULL,
  full_name VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_event_rsvps_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  CONSTRAINT fk_event_rsvps_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_event_rsvps_event (event_id),
  INDEX idx_event_rsvps_user (user_id),
  UNIQUE KEY uk_event_rsvps_user_event (event_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
