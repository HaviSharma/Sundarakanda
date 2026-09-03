<?php
/**
 * Events administration — create, edit, archive, and review the event
 * requests members submit through portal/schedule-event.php.
 *
 * Same gate as admin/campaign.php: admins and operations managers.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_role(['admin', 'operations_manager']);

const EVENT_UPLOAD_MAX_BYTES = 5 * 1024 * 1024;

/** Detected image type => the extension we will store it under. */
const EVENT_UPLOAD_TYPES = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG  => 'png',
    IMAGETYPE_WEBP => 'webp',
];

$uploadsDir = __DIR__ . '/../uploads/events/';

/**
 * Validate and store an uploaded banner.
 *
 * The browser-supplied MIME type and the original filename are both
 * attacker-controlled, so neither is trusted: the type comes from
 * getimagesize() and the extension is derived from that, never from the
 * name we were given.
 *
 * @return string|null Stored filename, or null when no file was submitted.
 * @throws RuntimeException on any validation failure.
 */
function store_event_banner(array $file, string $uploadsDir): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The banner image failed to upload. Please try again.');
    }
    if ($file['size'] > EVENT_UPLOAD_MAX_BYTES) {
        throw new RuntimeException('Banner must be under 5MB.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('The banner image failed to upload. Please try again.');
    }

    $info = @getimagesize($file['tmp_name']);
    if ($info === false || !isset(EVENT_UPLOAD_TYPES[$info[2]])) {
        throw new RuntimeException('Banner must be a JPG, PNG, or WebP image.');
    }
    $ext = EVENT_UPLOAD_TYPES[$info[2]];

    if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0755, true) && !is_dir($uploadsDir)) {
        throw new RuntimeException('Could not prepare the upload directory.');
    }

    $filename = 'event_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $uploadsDir . $filename)) {
        throw new RuntimeException('Could not save the banner image.');
    }
    @chmod($uploadsDir . $filename, 0644);

    return $filename;
}

/** Empty submitted strings mean "not set", not an empty value in the column. */
function null_if_blank(?string $v): ?string
{
    $v = trim((string)$v);
    return $v === '' ? null : $v;
}

// --- Create / edit --------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_event') {
    csrf_check();
    require_role(['admin', 'operations_manager']);

    $eventId     = (int)($_POST['event_id'] ?? 0);
    $title       = trim($_POST['title'] ?? '');
    $eventMode   = $_POST['event_mode'] ?? 'hybrid';
    $zoomLink    = null_if_blank($_POST['zoom_link'] ?? '');
    $address     = null_if_blank($_POST['address'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventDate   = null_if_blank($_POST['event_date'] ?? '');
    $startTime   = null_if_blank($_POST['start_time'] ?? '');
    $endTime     = null_if_blank($_POST['end_time'] ?? '');
    $rsvpLink    = null_if_blank($_POST['rsvp_link'] ?? '');
    $status      = $_POST['status'] ?? 'upcoming';

    $errors = [];
    if ($title === '')       { $errors[] = 'Title is required.'; }
    if ($description === '') { $errors[] = 'Description is required.'; }
    if ($eventDate === null) { $errors[] = 'Event date is required.'; }
    if (!in_array($eventMode, ['online', 'in_person', 'hybrid'], true)) {
        $errors[] = 'Choose a valid event mode.';
    }
    if (!in_array($status, ['pending', 'upcoming', 'past', 'archived', 'rejected'], true)) {
        $errors[] = 'Choose a valid status.';
    }
    if (in_array($eventMode, ['online', 'hybrid'], true) && $zoomLink === null) {
        $errors[] = 'A Zoom link is required for online and hybrid events.';
    }
    if (in_array($eventMode, ['in_person', 'hybrid'], true) && $address === null) {
        $errors[] = 'An address is required for in-person and hybrid events.';
    }

    $existing = $eventId ? get_event($eventId) : null;
    if ($eventId && !$existing) {
        $errors[] = 'That event no longer exists.';
    }

    $bannerImage = $existing['banner_image'] ?? null;
    if (!$errors) {
        try {
            $uploaded = store_event_banner($_FILES['banner_image'] ?? [], $uploadsDir);
            if ($uploaded !== null) {
                $bannerImage = $uploaded;
            }
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
        }
    }

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect($eventId ? 'events.php?edit=' . $eventId : 'events.php');
    }

    $slug = generate_event_slug($title, $eventId);
    $actorId = current_user()['id'] ?? null;

    try {
        if ($eventId) {
            $stmt = db()->prepare(
                'UPDATE events SET title=?, slug=?, banner_image=?, event_mode=?, zoom_link=?, address=?,
                        description=?, event_date=?, start_time=?, end_time=?, rsvp_link=?, status=?
                  WHERE id=?'
            );
            $stmt->bind_param('ssssssssssssi', $title, $slug, $bannerImage, $eventMode, $zoomLink, $address,
                $description, $eventDate, $startTime, $endTime, $rsvpLink, $status, $eventId);
            $stmt->execute();
            $stmt->close();

            // $existing was read BEFORE the update — reading it after would
            // record the new value as the old one.
            audit('update', 'event', $eventId, $existing, [
                'title' => $title, 'event_mode' => $eventMode,
                'event_date' => $eventDate, 'status' => $status,
            ]);
            flash_set('success', 'Event updated.');
        } else {
            if ($actorId === null) {
                flash_set('error', 'Events created here must be attributed to a signed-in account. Sign in with your portal account rather than the shared admin password.');
                redirect('events.php');
            }
            $stmt = db()->prepare(
                'INSERT INTO events (title, slug, banner_image, event_mode, zoom_link, address, description,
                                     event_date, start_time, end_time, rsvp_link, status, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );
            $stmt->bind_param('ssssssssssssi', $title, $slug, $bannerImage, $eventMode, $zoomLink, $address,
                $description, $eventDate, $startTime, $endTime, $rsvpLink, $status, $actorId);
            $stmt->execute();
            $newId = $stmt->insert_id;
            $stmt->close();

            audit('create', 'event', $newId, null, [
                'title' => $title, 'event_mode' => $eventMode,
                'event_date' => $eventDate, 'status' => $status,
            ]);
            flash_set('success', 'Event created.');
        }
    } catch (mysqli_sql_exception $e) {
        flash_set('error', 'Could not save the event: ' . $e->getMessage());
    }

    redirect('events.php');
}

// --- Archive --------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'archive_event') {
    csrf_check();
    require_role(['admin', 'operations_manager']);
    $eventId = (int)($_POST['event_id'] ?? 0);

    if ($event = get_event($eventId)) {
        $stmt = db()->prepare("UPDATE events SET status='archived' WHERE id=?");
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $stmt->close();
        audit('archive', 'event', $eventId, $event['status'], 'archived');
        flash_set('success', 'Event archived.');
    }
    redirect('events.php');
}

// --- Approve / reject a member request ------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && in_array($_POST['action'] ?? '', ['approve_event', 'reject_event'], true)) {
    csrf_check();
    require_role(['admin', 'operations_manager']);

    $eventId = (int)($_POST['event_id'] ?? 0);
    $approve = $_POST['action'] === 'approve_event';
    $event = get_event($eventId);

    if (!$event) {
        flash_set('error', 'That request no longer exists.');
        redirect('events.php');
    }
    if ($event['status'] !== 'pending') {
        flash_set('info', 'That request has already been reviewed.');
        redirect('events.php');
    }

    $newStatus = $approve ? 'upcoming' : 'rejected';
    $note = null_if_blank($_POST['review_note'] ?? '');
    $reviewerId = current_user()['id'] ?? null;

    $stmt = db()->prepare('UPDATE events SET status=?, reviewed_by=?, reviewed_at=NOW(), review_note=? WHERE id=?');
    $stmt->bind_param('sisi', $newStatus, $reviewerId, $note, $eventId);
    $stmt->execute();
    $stmt->close();

    audit($approve ? 'approve' : 'reject', 'event', $eventId, $event['status'],
        ['status' => $newStatus, 'note' => $note]);

    flash_set('success', $approve
        ? 'Event approved — it is now live on the events page.'
        : 'Request declined. It stays hidden from the public page.');
    redirect('events.php');
}

// --- Reads ----------------------------------------------------------------
$stats = event_stats();
$pending = get_pending_events();
$editing = null;
if (isset($_GET['edit'])) {
    $editing = get_event((int)$_GET['edit']);
}

$allEvents = db()->query(
    "SELECT e.id, e.title, e.event_date, e.event_mode, e.status, e.requested_by,
            u.full_name AS requester_name
       FROM events e
       LEFT JOIN users u ON u.id = e.requested_by
      WHERE e.status <> 'pending'
      ORDER BY e.event_date DESC"
)->fetch_all(MYSQLI_ASSOC);

/** Value helper for the create/edit form. */
function ev(?array $editing, string $key, string $default = ''): string
{
    return e($editing[$key] ?? $default);
}

admin_top('Events', 'events');
?>
<h1 style="margin-bottom:6px">Events</h1>
<p class="muted" style="margin-bottom:28px">Create and manage events, and review the ones members propose.</p>

<div class="stat-tiles">
  <div class="stat-tile"><b><?= (int)$stats['upcoming'] ?></b><span>Upcoming events</span></div>
  <div class="stat-tile"><b><?= (int)$stats['total_rsvps'] ?></b><span>Total RSVPs</span></div>
  <div class="stat-tile"><b><?= (int)$stats['this_month'] ?></b><span>This month</span></div>
  <div class="stat-tile<?= $stats['pending'] ? ' stat-tile-alert' : '' ?>"><b><?= (int)$stats['pending'] ?></b><span>Awaiting review</span></div>
</div>

<?php if ($pending): ?>
<section style="margin:36px 0">
  <h2 style="font-size:1.15rem">Requests awaiting review <span class="pill pill-alert"><?= count($pending) ?></span></h2>
  <p class="muted" style="margin-bottom:16px">Submitted by members. Nothing here is visible on the public site until it is approved.</p>

  <?php foreach ($pending as $req): ?>
    <div class="card" style="margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap;align-items:flex-start">
        <div style="flex:1;min-width:240px">
          <h3 style="margin:0 0 4px;font-size:1.05rem"><?= e($req['title']) ?></h3>
          <p class="muted" style="margin:0 0 8px;font-size:.88rem">
            Requested by <strong><?= e($req['requester_name'] ?? 'Unknown') ?></strong>
            <?php if (!empty($req['requester_email'])): ?>(<?= e($req['requester_email']) ?>)<?php endif; ?>
            · <?= e(format_date($req['created_at'])) ?>
          </p>
          <p style="margin:0 0 8px;font-size:.92rem">
            <strong><?= e(format_date($req['event_date'])) ?></strong>
            <?php if ($req['start_time']): ?> · <?= e(date('g:i A', strtotime($req['start_time']))) ?><?php endif; ?>
            · <span class="pill"><?= e(event_mode_label($req['event_mode'])) ?></span>
          </p>
          <?php if ($req['zoom_link']): ?><p class="muted" style="margin:0 0 4px;font-size:.85rem">Zoom: <?= e($req['zoom_link']) ?></p><?php endif; ?>
          <?php if ($req['address']): ?><p class="muted" style="margin:0 0 4px;font-size:.85rem">Where: <?= e($req['address']) ?></p><?php endif; ?>
          <p style="margin:8px 0 0;font-size:.9rem;color:var(--ink-500)"><?= nl2br(e($req['description'])) ?></p>
        </div>
        <div style="display:flex;flex-direction:column;gap:8px;min-width:190px">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="approve_event">
            <input type="hidden" name="event_id" value="<?= (int)$req['id'] ?>">
            <button class="btn btn-primary btn-sm" type="submit" style="width:100%;justify-content:center">Approve &amp; publish</button>
          </form>
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="reject_event">
            <input type="hidden" name="event_id" value="<?= (int)$req['id'] ?>">
            <input type="text" name="review_note" placeholder="Reason (optional)" style="width:100%;margin-bottom:6px;font-size:.85rem">
            <button class="btn btn-ghost btn-sm" type="submit" style="width:100%;justify-content:center">Decline</button>
          </form>
          <a class="btn btn-ghost btn-sm" style="justify-content:center" href="events.php?edit=<?= (int)$req['id'] ?>">Edit first</a>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<div class="grid grid-2" style="align-items:start;margin:36px 0">
  <div class="form-card">
    <h2 style="font-size:1.1rem"><?= $editing ? 'Edit event' : 'Add a new event' ?></h2>
    <?php if ($editing): ?>
      <p class="muted" style="margin-bottom:14px">Editing <strong><?= e($editing['title']) ?></strong> · <a href="events.php">cancel</a></p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save_event">
      <input type="hidden" name="event_id" value="<?= $editing ? (int)$editing['id'] : 0 ?>">

      <div class="field">
        <label for="title">Event title <span class="req">*</span></label>
        <input id="title" name="title" type="text" required value="<?= ev($editing, 'title') ?>" placeholder="e.g. Hanuman Jayanti Celebration">
      </div>

      <div class="field">
        <label for="banner_image">Banner image (JPG, PNG, or WebP · max 5MB)</label>
        <input id="banner_image" name="banner_image" type="file" accept="image/jpeg,image/png,image/webp">
        <?php if ($editing && $editing['banner_image']): ?>
          <p class="muted" style="font-size:.82rem;margin-top:6px">Current: <?= e($editing['banner_image']) ?> — uploading replaces it.</p>
        <?php endif; ?>
      </div>

      <div class="field">
        <label for="event_mode">Event mode <span class="req">*</span></label>
        <select id="event_mode" name="event_mode" required>
          <?php foreach (['hybrid' => 'Hybrid (online + in person)', 'online' => 'Online only', 'in_person' => 'In person only'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($editing['event_mode'] ?? 'hybrid') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="field" id="zoom_link_field">
        <label for="zoom_link">Zoom link</label>
        <input id="zoom_link" name="zoom_link" type="url" value="<?= ev($editing, 'zoom_link') ?>" placeholder="https://zoom.us/j/...">
      </div>

      <div class="field" id="address_field">
        <label for="address">Location address</label>
        <input id="address" name="address" type="text" value="<?= ev($editing, 'address') ?>" placeholder="Venue name and street address">
      </div>

      <div class="field-row">
        <div class="field">
          <label for="event_date">Date <span class="req">*</span></label>
          <input id="event_date" name="event_date" type="date" required value="<?= ev($editing, 'event_date') ?>">
        </div>
        <div class="field">
          <label for="start_time">Starts</label>
          <input id="start_time" name="start_time" type="time" value="<?= $editing && $editing['start_time'] ? e(substr($editing['start_time'], 0, 5)) : '08:00' ?>">
        </div>
        <div class="field">
          <label for="end_time">Ends</label>
          <input id="end_time" name="end_time" type="time" value="<?= $editing && $editing['end_time'] ? e(substr($editing['end_time'], 0, 5)) : '09:00' ?>">
        </div>
      </div>

      <div class="field">
        <label for="description">Description <span class="req">*</span></label>
        <textarea id="description" name="description" required rows="5" placeholder="Details, agenda, what to bring..."><?= ev($editing, 'description') ?></textarea>
      </div>

      <div class="field">
        <label for="rsvp_link">External RSVP link (optional)</label>
        <input id="rsvp_link" name="rsvp_link" type="url" value="<?= ev($editing, 'rsvp_link') ?>" placeholder="Leave blank to collect RSVPs here">
      </div>

      <div class="field">
        <label for="status">Status</label>
        <select id="status" name="status">
          <?php foreach (['upcoming' => 'Published', 'pending' => 'Pending review', 'past' => 'Past', 'archived' => 'Archived', 'rejected' => 'Declined'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= ($editing['status'] ?? 'upcoming') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
        <p class="muted" style="font-size:.82rem;margin-top:6px">Only Published and Past appear on the public events page.</p>
      </div>

      <button class="btn btn-maroon" type="submit" style="width:100%;justify-content:center"><?= $editing ? 'Save changes' : 'Create event' ?></button>
    </form>
  </div>

  <div>
    <h2 style="font-size:1.1rem;margin-bottom:12px">All events</h2>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Event</th><th>Date</th><th>Mode</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($allEvents as $row): ?>
            <tr>
              <td>
                <?= e($row['title']) ?>
                <?php if ($row['requester_name']): ?><br><span class="muted" style="font-size:.78rem">proposed by <?= e($row['requester_name']) ?></span><?php endif; ?>
              </td>
              <td><?= e(format_date($row['event_date'])) ?></td>
              <td><span class="pill"><?= e(event_mode_label($row['event_mode'])) ?></span></td>
              <td><span class="pill <?= in_array($row['status'], ['archived','rejected'], true) ? 'pill-inactive' : 'pill-active' ?>"><?= e(ucfirst($row['status'])) ?></span></td>
              <td style="white-space:nowrap">
                <a class="btn btn-ghost btn-sm" href="events.php?edit=<?= (int)$row['id'] ?>">Edit</a>
                <?php if ($row['status'] !== 'archived'): ?>
                  <form method="post" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="archive_event">
                    <input type="hidden" name="event_id" value="<?= (int)$row['id'] ?>">
                    <button class="btn btn-ghost btn-sm" type="submit">Archive</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$allEvents): ?><tr><td colspan="5" class="muted">No events yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
(function () {
  var mode = document.getElementById('event_mode');
  var zoom = document.getElementById('zoom_link_field');
  var addr = document.getElementById('address_field');
  function sync() {
    zoom.style.display = (mode.value === 'in_person') ? 'none' : '';
    addr.style.display = (mode.value === 'online') ? 'none' : '';
  }
  mode.addEventListener('change', sync);
  sync();
})();
</script>
<?php admin_bottom(); ?>
