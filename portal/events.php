<?php
/**
 * Public events page: upcoming list, past archive, RSVP, and .ics download.
 *
 * Every handler that sets a header — the post-RSVP redirect and the
 * calendar download — must run BEFORE includes/header.php emits any HTML.
 */
require_once __DIR__ . '/includes/bootstrap.php';

$user = current_user();

// --- Calendar download (must precede all output) --------------------------
if (($_GET['action'] ?? '') === 'download_ics' && ($slug = $_GET['event'] ?? '')) {
    $event = get_event_by_slug($slug);
    if (!$event || !event_is_public($event)) {
        http_response_code(404);
        die('That event could not be found.');
    }
    $ics = generate_ics_content($event);
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . ics_filename($event['title']) . '.ics"');
    header('Content-Length: ' . strlen($ics));
    echo $ics;
    exit;
}

// --- RSVP (must precede all output) ---------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'rsvp') {
    csrf_check();
    $eventId = (int)($_POST['event_id'] ?? 0);
    $event = get_event($eventId);

    if (!$event || !event_is_public($event)) {
        flash_set('error', 'That event could not be found.');
        redirect('events.php');
    }

    // Honeypot: a real browser leaves this empty; bots fill every field.
    if (trim($_POST['website'] ?? '') !== '') {
        flash_set('success', 'Thank you for your RSVP.');
        redirect('events.php');
    }

    // Light per-session throttle on the unauthenticated path.
    if (!$user) {
        $last = $_SESSION['last_rsvp_at'] ?? 0;
        if (time() - $last < 20) {
            flash_set('error', 'Please wait a moment before submitting another RSVP.');
            redirect('events.php');
        }
    }

    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    if ($user) {
        $fullName = $user['full_name'];
        $email = $user['email'];
    }

    $errors = [];
    if ($fullName === '') {
        $errors[] = 'Name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect('events.php');
    }

    // NULL user_id is distinct in a UNIQUE key, so anonymous repeats have to
    // be caught here rather than by the schema.
    if (email_has_rsvped($eventId, $email)) {
        flash_set('info', 'You have already RSVP\'d to this event.');
        redirect('events.php');
    }

    $userId = $user['id'] ?? null;
    $stmt = db()->prepare('INSERT INTO event_rsvps (event_id, user_id, full_name, email) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('iiss', $eventId, $userId, $fullName, $email);
    $stmt->execute();
    $stmt->close();

    $_SESSION['last_rsvp_at'] = time();
    audit('rsvp', 'event', $eventId, null, ['name' => $fullName, 'email' => $email]);
    flash_set('success', 'Thank you for your RSVP — we look forward to seeing you.');
    redirect('events.php');
}

// --- Reads ----------------------------------------------------------------
$upcomingEvents = get_upcoming_events(50);
$pastEvents = get_past_events(50);

$pageTitle = 'Events';
$activeNav = 'events';
include __DIR__ . '/includes/header.php';

/** One event card. $past drops the RSVP controls. */
function render_event_card(array $event, bool $past, ?array $user): void
{
    $desc = trim(strip_tags($event['description'] ?? ''));
    if (mb_strlen($desc) > 220) {
        $desc = mb_substr($desc, 0, 220) . '…';
    }
    ?>
    <article class="card event-card<?= $past ? ' is-past' : '' ?>">
      <?php if (!empty($event['banner_image'])): ?>
        <img class="event-banner" src="uploads/events/<?= e($event['banner_image']) ?>" alt="">
      <?php endif; ?>

      <div class="event-head">
        <h3><?= e($event['title']) ?></h3>
        <span class="pill"><?= e(event_mode_label($event['event_mode'])) ?></span>
      </div>

      <p class="event-when">
        <strong><?= e(format_date($event['event_date'])) ?></strong>
        <?php if (!empty($event['start_time'])): ?>
          · <?= e(date('g:i A', strtotime($event['start_time']))) ?>
          <?php if (!empty($event['end_time'])): ?>
            – <?= e(date('g:i A', strtotime($event['end_time']))) ?>
          <?php endif; ?>
        <?php endif; ?>
      </p>

      <?php if ($desc !== ''): ?><p class="event-desc"><?= e($desc) ?></p><?php endif; ?>

      <?php if (!$past): ?>
        <?php if (!empty($event['zoom_link']) && in_array($event['event_mode'], ['online','hybrid'], true)): ?>
          <p class="event-meta"><strong>Online:</strong> <a href="<?= e($event['zoom_link']) ?>" target="_blank" rel="noopener">Join the meeting</a></p>
        <?php endif; ?>
        <?php if (!empty($event['address']) && in_array($event['event_mode'], ['in_person','hybrid'], true)): ?>
          <p class="event-meta"><strong>Location:</strong> <?= e($event['address']) ?></p>
        <?php endif; ?>
      <?php endif; ?>

      <div class="event-actions">
        <?php if (!$past): ?>
          <?php if (!empty($event['rsvp_link'])): ?>
            <a class="btn btn-primary btn-sm" href="<?= e($event['rsvp_link']) ?>" target="_blank" rel="noopener">RSVP</a>
          <?php elseif ($user && user_has_rsvped((int)$event['id'], (int)$user['id'])): ?>
            <span class="pill pill-active">You're going</span>
          <?php elseif ($user): ?>
            <form method="post" class="inline-form">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="rsvp">
              <input type="hidden" name="event_id" value="<?= (int)$event['id'] ?>">
              <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
              <button class="btn btn-primary btn-sm" type="submit">RSVP</button>
            </form>
          <?php else: ?>
            <button class="btn btn-primary btn-sm" type="button"
                    data-rsvp-open data-event-id="<?= (int)$event['id'] ?>"
                    data-event-title="<?= e($event['title']) ?>">RSVP</button>
          <?php endif; ?>
        <?php endif; ?>

        <a class="btn btn-ghost btn-sm" href="?action=download_ics&amp;event=<?= e(rawurlencode($event['slug'])) ?>">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
          Add to calendar
        </a>
      </div>
    </article>
    <?php
}
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Calendar</p>
      <h1>Sundarakanda Events</h1>
      <p>Parayanam sessions, Zoom satsangs, and community gatherings — join us in person or online.</p>
      <?php if ($user): ?>
        <p style="margin-top:14px"><a class="btn btn-ghost btn-sm" href="schedule-event.php">Propose an event</a></p>
      <?php endif; ?>
    </div>

    <?php if (!$upcomingEvents && !$pastEvents): ?>
      <div class="card medium center" style="margin:0 auto;max-width:520px">
        <p>No events are scheduled just yet. Subscribe and we'll let you know as soon as one is announced.</p>
        <a class="btn btn-primary" href="subscribe.php">Subscribe for updates</a>
      </div>
    <?php endif; ?>

    <?php if ($upcomingEvents): ?>
      <h2 class="events-heading">Upcoming Events</h2>
      <div class="events-grid">
        <?php foreach ($upcomingEvents as $ev) { render_event_card($ev, false, $user); } ?>
      </div>
    <?php endif; ?>

    <?php if ($pastEvents): ?>
      <h2 class="events-heading">Past Events</h2>
      <p class="muted" style="margin:-10px 0 22px">A record of gatherings already held.</p>
      <div class="events-grid">
        <?php foreach ($pastEvents as $ev) { render_event_card($ev, true, $user); } ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php if (!$user): ?>
<div class="modal" id="rsvp-modal" hidden>
  <div class="modal-card">
    <button class="modal-close" type="button" data-rsvp-close aria-label="Close">&times;</button>
    <h2 style="font-size:1.2rem">RSVP</h2>
    <p class="muted" id="rsvp-modal-event" style="margin-bottom:16px"></p>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="rsvp">
      <input type="hidden" name="event_id" id="rsvp-event-id" value="">
      <input type="text" name="website" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
      <div class="field">
        <label for="rsvp-name">Your name <span class="req">*</span></label>
        <input id="rsvp-name" name="full_name" type="text" required>
      </div>
      <div class="field">
        <label for="rsvp-email">Your email <span class="req">*</span></label>
        <input id="rsvp-email" name="email" type="email" required>
      </div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-primary" type="submit" style="flex:1;justify-content:center">Confirm RSVP</button>
        <button class="btn btn-ghost" type="button" data-rsvp-close style="flex:1;justify-content:center">Cancel</button>
      </div>
    </form>
  </div>
</div>
<script>
(function () {
  var modal = document.getElementById('rsvp-modal');
  if (!modal) return;
  var idField = document.getElementById('rsvp-event-id');
  var label = document.getElementById('rsvp-modal-event');
  function open(btn) {
    idField.value = btn.getAttribute('data-event-id');
    label.textContent = btn.getAttribute('data-event-title') || '';
    modal.hidden = false;
    document.getElementById('rsvp-name').focus();
  }
  function close() { modal.hidden = true; }
  document.querySelectorAll('[data-rsvp-open]').forEach(function (b) {
    b.addEventListener('click', function () { open(b); });
  });
  document.querySelectorAll('[data-rsvp-close]').forEach(function (b) {
    b.addEventListener('click', close);
  });
  modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
