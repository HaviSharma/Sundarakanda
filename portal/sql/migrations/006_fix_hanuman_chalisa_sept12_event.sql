-- Corrects the September 12, 2026 event using the details on the actual
-- printed poster: it is in-person only, at a different address than the
-- placeholder seed, starting 3:30 PM with no published end time.
--
-- Updates the existing row by slug rather than inserting a new one, so RSVPs
-- and the event's identity (id, slug, calendar UID) are preserved. end_time
-- is cleared (not defaulted) — the poster does not state one, and
-- generate_ics_content() (see includes/events.php) now omits DTEND rather
-- than inventing a duration when end_time is NULL.
--
-- The banner image itself is not part of this migration: copy the poster
-- into portal/uploads/events/ and set banner_image separately (the local
-- dev copy is event_20260911_225128_5548a6e8.jpg — deploy environments
-- should upload the same source file and use their own generated filename).
--
-- Safe to re-run.

SET NAMES utf8mb4;

UPDATE events
   SET title = 'Hanuman Chalisa & Sundarakanda Parayanam',
       event_mode = 'in_person',
       zoom_link = NULL,
       address = '15387 Hume Dr, Saratoga, CA 95070',
       description = 'A recitation of the Telugu Hanuman Chalisa and Sundarakanda Parayanam, hosted by Anil & Sravanthi. Families are welcome to join us in person for chanting, prasadam, and community satsang.',
       start_time = '15:30:00',
       end_time = NULL
 WHERE slug = 'hanuman-chalisa-parayanam-community-gathering';

INSERT INTO audit_log (action, record_type, record_id, old_value, new_value, performed_by)
SELECT 'update', 'event', e.id,
       'Fremont hybrid, 5:30-7:30 PM, placeholder Zoom link',
       'Corrected from poster: in-person Saratoga gathering hosted by Anil & Sravanthi, 3:30 PM, no Zoom/end time.',
       NULL
  FROM events e
 WHERE e.slug = 'hanuman-chalisa-parayanam-community-gathering'
   AND NOT EXISTS (
     SELECT 1 FROM audit_log a
      WHERE a.record_type = 'event' AND a.record_id = e.id
        AND a.new_value LIKE 'Corrected from poster%'
   );
