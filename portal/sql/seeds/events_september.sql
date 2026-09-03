-- Two upcoming Sundarakanda events, September 2026.
--
-- Titles, times, Zoom links and addresses here are starting values — set the
-- real ones through admin/events.php. `created_by` resolves to the first
-- admin account rather than a hard-coded id.
--
-- Safe to re-run: matched on the unique slug.

SET NAMES utf8mb4;

INSERT INTO events
  (title, slug, event_mode, zoom_link, address, description,
   event_date, start_time, end_time, status, created_by)
VALUES
  ('Sundarakanda Parayanam — September Session',
   'sundarakanda-parayanam-september-session',
   'hybrid',
   'https://zoom.us/j/0000000000',
   'Sundarakanda Foundation, 4425 Bidwell Dr #4104, Fremont, CA 94538',
   'Our monthly Sundarakanda Parayanam, chanted together in the Telugu tradition of Shri M.S. Rama Rao. Newcomers are welcome — texts and transliteration are provided, and you are welcome to simply listen. Join us in Fremont or online over Zoom.',
   '2026-09-05', '08:00:00', '10:00:00', 'upcoming',
   (SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1)),

  ('Hanuman Chalisa Parayanam & Community Gathering',
   'hanuman-chalisa-parayanam-community-gathering',
   'hybrid',
   'https://zoom.us/j/0000000001',
   'Sundarakanda Foundation, 4425 Bidwell Dr #4104, Fremont, CA 94538',
   'A recitation of the Telugu Hanuman Chalisa followed by community prasadam and a short satsang. Families are welcome. If you would like to contribute to the prasadam offering, please let an organiser know in advance.',
   '2026-09-12', '17:30:00', '19:30:00', 'upcoming',
   (SELECT id FROM users WHERE role = 'admin' ORDER BY id LIMIT 1))
ON DUPLICATE KEY UPDATE
  title = VALUES(title),
  description = VALUES(description),
  event_date = VALUES(event_date),
  start_time = VALUES(start_time),
  end_time = VALUES(end_time),
  status = VALUES(status);
