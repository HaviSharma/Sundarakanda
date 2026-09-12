<?php
/**
 * Events helper functions — mirrors campaign.php pattern.
 *
 * Reads are live-computed. `status` is authoritative for visibility:
 * only 'upcoming' and 'past' are public. 'pending' (a member request
 * awaiting review), 'rejected' and 'archived' never reach the public page.
 */

/** Statuses that may be shown to the public. */
const EVENT_PUBLIC_STATUSES = ['upcoming', 'past'];

/**
 * Get a single event by ID
 */
function get_event(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM events WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/**
 * Get a single event by slug. Public callers must check the status
 * themselves — see event_is_public().
 */
function get_event_by_slug(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM events WHERE slug = ? LIMIT 1');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row ?: null;
}

/** Is this event visible to a non-admin visitor? */
function event_is_public(array $event): bool
{
    return in_array($event['status'], EVENT_PUBLIC_STATUSES, true);
}

/**
 * Upcoming events — today onwards, soonest first.
 * Excludes pending/rejected/archived.
 */
function get_upcoming_events(int $limit = 50): array
{
    $stmt = db()->prepare(
        "SELECT * FROM events
          WHERE event_date >= CURDATE()
            AND status IN ('upcoming','past')
          ORDER BY event_date ASC, start_time ASC
          LIMIT ?"
    );
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * Past events — before today, most recent first.
 * Excludes pending/rejected/archived.
 */
function get_past_events(int $limit = 50): array
{
    $stmt = db()->prepare(
        "SELECT * FROM events
          WHERE event_date < CURDATE()
            AND status IN ('upcoming','past')
          ORDER BY event_date DESC, start_time DESC
          LIMIT ?"
    );
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * Member-submitted events awaiting an admin/operations decision.
 */
function get_pending_events(int $limit = 100): array
{
    $stmt = db()->prepare(
        "SELECT e.*, u.full_name AS requester_name, u.email AS requester_email
           FROM events e
           LEFT JOIN users u ON u.id = e.requested_by
          WHERE e.status = 'pending'
          ORDER BY e.created_at ASC
          LIMIT ?"
    );
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * Every event a given member has submitted, newest first.
 */
function get_user_event_requests(int $userId, int $limit = 50): array
{
    $stmt = db()->prepare(
        'SELECT id, title, slug, event_date, event_mode, status, created_at, reviewed_at
           FROM events
          WHERE requested_by = ?
          ORDER BY created_at DESC
          LIMIT ?'
    );
    $stmt->bind_param('ii', $userId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * Get events stats for the admin dashboard
 */
function event_stats(): array
{
    $conn = db();

    $upcoming = (int)$conn->query(
        "SELECT COUNT(*) c FROM events
          WHERE event_date >= CURDATE() AND status IN ('upcoming','past')"
    )->fetch_assoc()['c'];

    $totalRsvps = (int)$conn->query('SELECT COUNT(*) c FROM event_rsvps')->fetch_assoc()['c'];

    $thisMonth = (int)$conn->query(
        "SELECT COUNT(*) c FROM events
          WHERE YEAR(event_date) = YEAR(CURDATE())
            AND MONTH(event_date) = MONTH(CURDATE())
            AND status IN ('upcoming','past')"
    )->fetch_assoc()['c'];

    $pending = (int)$conn->query(
        "SELECT COUNT(*) c FROM events WHERE status = 'pending'"
    )->fetch_assoc()['c'];

    return [
        'upcoming' => $upcoming,
        'total_rsvps' => $totalRsvps,
        'this_month' => $thisMonth,
        'pending' => $pending,
    ];
}

/**
 * Get RSVPs for an event
 */
function get_event_rsvps(int $eventId, int $limit = 200): array
{
    $stmt = db()->prepare('SELECT r.id, r.user_id, r.full_name, r.email, r.created_at FROM event_rsvps r WHERE r.event_id = ? ORDER BY r.created_at DESC LIMIT ?');
    $stmt->bind_param('ii', $eventId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

/**
 * Count RSVPs for an event
 */
function count_event_rsvps(int $eventId): int
{
    $stmt = db()->prepare('SELECT COUNT(*) c FROM event_rsvps WHERE event_id = ?');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $c = (int)$stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();
    return $c;
}

/**
 * Has this signed-in user already RSVP'd?
 */
function user_has_rsvped(int $eventId, int $userId): bool
{
    $stmt = db()->prepare('SELECT id FROM event_rsvps WHERE event_id = ? AND user_id = ? LIMIT 1');
    $stmt->bind_param('ii', $eventId, $userId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (bool)$result;
}

/**
 * Has this email already RSVP'd? Anonymous RSVPs carry a NULL user_id, and
 * MySQL treats NULLs as distinct in a UNIQUE key, so the schema constraint
 * cannot catch a repeat submission on its own.
 */
function email_has_rsvped(int $eventId, string $email): bool
{
    $stmt = db()->prepare('SELECT id FROM event_rsvps WHERE event_id = ? AND email = ? LIMIT 1');
    $stmt->bind_param('is', $eventId, $email);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (bool)$result;
}

/**
 * Slugify a title, then make it unique. `events.slug` is UNIQUE and db.php
 * runs mysqli in exception mode, so a collision would otherwise be a fatal
 * error rather than a validation message.
 *
 * @param int $excludeId Row being edited — its own slug is not a collision.
 */
function generate_event_slug(string $title, int $excludeId = 0): string
{
    $base = strtolower(trim($title));
    $base = preg_replace('/[^a-z0-9]+/', '-', $base);
    $base = trim($base, '-');
    if ($base === '') {
        $base = 'event';
    }
    $base = substr($base, 0, 180);

    $slug = $base;
    $suffix = 1;
    while (slug_taken($slug, $excludeId)) {
        $suffix++;
        $slug = $base . '-' . $suffix;
    }
    return $slug;
}

function slug_taken(string $slug, int $excludeId = 0): bool
{
    $stmt = db()->prepare('SELECT id FROM events WHERE slug = ? AND id <> ? LIMIT 1');
    $stmt->bind_param('si', $slug, $excludeId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (bool)$row;
}

/** Human label for an event_mode enum value. */
function event_mode_label(string $mode): string
{
    return [
        'online' => 'Online',
        'in_person' => 'In Person',
        'hybrid' => 'Hybrid',
    ][$mode] ?? ucfirst(str_replace('_', ' ', $mode));
}

/**
 * Escape a value for an iCalendar text field, per RFC 5545 §3.3.11.
 * Note this is NOT htmlspecialchars — a calendar file is not HTML, and
 * HTML-escaping leaks "&amp;" into calendar entries.
 */
function ics_escape(string $value): string
{
    $value = str_replace('\\', '\\\\', $value);
    $value = str_replace(["\r\n", "\r", "\n"], '\\n', $value);
    $value = str_replace(';', '\\;', $value);
    $value = str_replace(',', '\\,', $value);
    return $value;
}

/**
 * Fold a content line to 75 octets, per RFC 5545 §3.1. Continuation lines
 * begin with a single space.
 */
function ics_fold(string $line): string
{
    if (strlen($line) <= 75) {
        return $line . "\r\n";
    }
    $out = substr($line, 0, 75) . "\r\n";
    $rest = substr($line, 75);
    foreach (str_split($rest, 74) as $chunk) {
        $out .= ' ' . $chunk . "\r\n";
    }
    return $out;
}

/**
 * Build the .ics body for an event.
 *
 * Times are stored as local wall-clock in the site's timezone (set in
 * config.php). They are converted to UTC before being stamped with the
 * trailing "Z" — writing a local time with a Z suffix silently shifts
 * every event by the UTC offset.
 *
 * When end_time is unset, no DTEND is written rather than guessing a
 * duration — per RFC 5545 §3.6.1, a VEVENT with only DTSTART is a valid
 * point-in-time event, which is the honest representation of "we know
 * when it starts, not when it ends."
 */
function generate_ics_content(array $event): string
{
    $siteTz = new DateTimeZone(date_default_timezone_get());
    $utc = new DateTimeZone('UTC');

    $date = $event['event_date'];
    $start = $event['start_time'] ?: '08:00:00';

    $dtStart = DateTime::createFromFormat('Y-m-d H:i:s', "$date $start", $siteTz)
        ?: new DateTime("$date 08:00:00", $siteTz);

    $dtEnd = null;
    if (!empty($event['end_time'])) {
        $dtEnd = DateTime::createFromFormat('Y-m-d H:i:s', "$date {$event['end_time']}", $siteTz);
        if ($dtEnd) {
            // An end time earlier than the start means the event runs past midnight.
            if ($dtEnd <= $dtStart) {
                $dtEnd->modify('+1 day');
            }
            $dtEnd->setTimezone($utc);
        }
    }

    $dtStart->setTimezone($utc);

    $location = '';
    if (in_array($event['event_mode'], ['in_person', 'hybrid'], true) && !empty($event['address'])) {
        $location = $event['address'];
    } elseif ($event['event_mode'] === 'online' && !empty($event['zoom_link'])) {
        $location = $event['zoom_link'];
    }

    $description = trim(strip_tags($event['description'] ?? ''));
    if (mb_strlen($description) > 500) {
        $description = mb_substr($description, 0, 500) . '…';
    }
    if (!empty($event['zoom_link']) && $event['event_mode'] === 'hybrid') {
        $description .= "\n\nJoin online: " . $event['zoom_link'];
    }

    $uid = md5($event['id'] . '|' . $event['slug']) . '@sundarakanda.com';
    $url = rtrim(SITE_URL, '/') . '/events.php?event=' . rawurlencode($event['slug']);

    $ics = "BEGIN:VCALENDAR\r\n";
    $ics .= "VERSION:2.0\r\n";
    $ics .= "PRODID:-//Sundarakanda//Events//EN\r\n";
    $ics .= "CALSCALE:GREGORIAN\r\n";
    $ics .= "METHOD:PUBLISH\r\n";
    $ics .= "BEGIN:VEVENT\r\n";
    $ics .= ics_fold('UID:' . $uid);
    $ics .= ics_fold('DTSTAMP:' . gmdate('Ymd\THis\Z'));
    $ics .= ics_fold('DTSTART:' . $dtStart->format('Ymd\THis\Z'));
    if ($dtEnd) {
        $ics .= ics_fold('DTEND:' . $dtEnd->format('Ymd\THis\Z'));
    }
    $ics .= ics_fold('SUMMARY:' . ics_escape($event['title']));
    if ($description !== '') {
        $ics .= ics_fold('DESCRIPTION:' . ics_escape($description));
    }
    if ($location !== '') {
        $ics .= ics_fold('LOCATION:' . ics_escape($location));
    }
    $ics .= ics_fold('URL:' . ics_escape($url));
    $ics .= "END:VEVENT\r\n";
    $ics .= "END:VCALENDAR\r\n";

    return $ics;
}

/**
 * Filesystem-safe basename for the downloaded .ics (no extension — the
 * caller adds it).
 */
function ics_filename(string $title): string
{
    $name = preg_replace('/[^A-Za-z0-9_-]+/', '_', $title);
    $name = trim($name, '_');
    return substr($name ?: 'event', 0, 80);
}
