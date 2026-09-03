-- Migration 005: member-submitted event requests + admin/operations review
--
-- Additive only. Widening the `status` ENUM preserves every existing row,
-- and the default stays 'upcoming' so events created directly by an admin
-- continue to publish immediately, exactly as they do today.
--
-- 'pending'  — a member submitted it via portal/schedule-event.php
-- 'rejected' — reviewed and declined; never shown publicly
--
-- Safe to run once, safe to re-run.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE events
  MODIFY COLUMN status ENUM('pending','upcoming','past','archived','rejected')
    NOT NULL DEFAULT 'upcoming';

ALTER TABLE events
  ADD COLUMN IF NOT EXISTS requested_by INT NULL AFTER created_by,
  ADD COLUMN IF NOT EXISTS reviewed_by  INT NULL AFTER requested_by,
  ADD COLUMN IF NOT EXISTS reviewed_at  DATETIME NULL AFTER reviewed_by,
  ADD COLUMN IF NOT EXISTS review_note  VARCHAR(500) NULL AFTER reviewed_at;

-- MariaDB spells this `ADD FOREIGN KEY IF NOT EXISTS <name> (col)` —
-- `ADD CONSTRAINT IF NOT EXISTS ... FOREIGN KEY` is not accepted.
ALTER TABLE events
  ADD FOREIGN KEY IF NOT EXISTS fk_events_requested_by (requested_by)
    REFERENCES users(id) ON DELETE SET NULL,
  ADD FOREIGN KEY IF NOT EXISTS fk_events_reviewed_by (reviewed_by)
    REFERENCES users(id) ON DELETE SET NULL;

-- Pending queues are read on every admin dashboard load.
ALTER TABLE events
  ADD INDEX IF NOT EXISTS idx_events_status_date (status, event_date),
  ADD INDEX IF NOT EXISTS idx_events_requested_by (requested_by);

-- `created_by` is NOT NULL with ON DELETE RESTRICT from 004. A member's
-- request is authored by that member, so no change is needed there.

SET FOREIGN_KEY_CHECKS = 1;
