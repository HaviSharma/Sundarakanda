<?php
/**
 * Member-facing event proposal. Submissions land in `events` with
 * status='pending' and are invisible on the public page until an admin or
 * operations manager approves them in admin/events.php.
 */
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$old = [
    'title' => '', 'event_mode' => 'hybrid', 'zoom_link' => '', 'address' => '',
    'description' => '', 'event_date' => '', 'start_time' => '08:00', 'end_time' => '09:00',
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    foreach ($old as $k => $_) {
        $old[$k] = trim($_POST[$k] ?? '');
    }

    $title       = $old['title'];
    $eventMode   = $old['event_mode'];
    $description = $old['description'];
    $eventDate   = $old['event_date'] !== '' ? $old['event_date'] : null;
    $zoomLink    = $old['zoom_link'] !== '' ? $old['zoom_link'] : null;
    $address     = $old['address'] !== '' ? $old['address'] : null;
    $startTime   = $old['start_time'] !== '' ? $old['start_time'] : null;
    $endTime     = $old['end_time'] !== '' ? $old['end_time'] : null;

    if ($title === '')       { $errors[] = 'Please give your event a title.'; }
    if ($description === '') { $errors[] = 'Please describe the event.'; }
    if ($eventDate === null) { $errors[] = 'Please choose a date.'; }
    elseif ($eventDate < date('Y-m-d')) { $errors[] = 'Please choose a date in the future.'; }
    if (!in_array($eventMode, ['online', 'in_person', 'hybrid'], true)) {
        $errors[] = 'Please choose how the event will be held.';
    }
    if (in_array($eventMode, ['online', 'hybrid'], true) && $zoomLink === null) {
        $errors[] = 'A Zoom or meeting link is required for online and hybrid events.';
    }
    if (in_array($eventMode, ['in_person', 'hybrid'], true) && $address === null) {
        $errors[] = 'An address is required for in-person and hybrid events.';
    }

    if (!$errors) {
        $slug = generate_event_slug($title);
        $uid = (int)$user['id'];
        try {
            $stmt = db()->prepare(
                "INSERT INTO events (title, slug, event_mode, zoom_link, address, description,
                                     event_date, start_time, end_time, status, created_by, requested_by)
                 VALUES (?,?,?,?,?,?,?,?,?,'pending',?,?)"
            );
            $stmt->bind_param('sssssssssii', $title, $slug, $eventMode, $zoomLink, $address,
                $description, $eventDate, $startTime, $endTime, $uid, $uid);
            $stmt->execute();
            $newId = $stmt->insert_id;
            $stmt->close();

            audit('request', 'event', $newId, null, [
                'title' => $title, 'event_date' => $eventDate, 'requested_by' => $uid,
            ]);

            flash_set('success', 'Thank you — your event has been sent for review. You will see it on the events page once it is approved.');
            redirect('dashboard.php');
        } catch (mysqli_sql_exception $e) {
            $errors[] = 'We could not save your request. Please try again.';
        }
    }
}

$myRequests = get_user_event_requests((int)$user['id']);

$pageTitle = 'Schedule an Event';
$activeNav = 'schedule';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Members</p>
      <h1>Schedule an event</h1>
      <p>Propose a Parayanam session, satsang, or gathering. An organiser will review it before it appears on the events page.</p>
    </div>

    <?php if ($errors): ?>
      <div class="narrow" style="margin-bottom:20px">
        <div class="alert alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
      </div>
    <?php endif; ?>

    <div class="grid grid-2" style="align-items:start">
      <div class="form-card">
        <form method="post">
          <?= csrf_field() ?>

          <div class="field">
            <label for="title">Event title <span class="req">*</span></label>
            <input id="title" name="title" type="text" required value="<?= e($old['title']) ?>" placeholder="e.g. Saturday morning Sundarakanda Parayanam">
          </div>

          <div class="field">
            <label for="event_mode">How will it be held? <span class="req">*</span></label>
            <select id="event_mode" name="event_mode" required>
              <?php foreach (['hybrid' => 'Hybrid (online + in person)', 'online' => 'Online only', 'in_person' => 'In person only'] as $val => $label): ?>
                <option value="<?= $val ?>" <?= $old['event_mode'] === $val ? 'selected' : '' ?>><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="field" id="zoom_link_field">
            <label for="zoom_link">Zoom or meeting link</label>
            <input id="zoom_link" name="zoom_link" type="url" value="<?= e($old['zoom_link']) ?>" placeholder="https://zoom.us/j/...">
          </div>

          <div class="field" id="address_field">
            <label for="address">Address</label>
            <input id="address" name="address" type="text" value="<?= e($old['address']) ?>" placeholder="Venue name and street address">
          </div>

          <div class="field-row">
            <div class="field">
              <label for="event_date">Date <span class="req">*</span></label>
              <input id="event_date" name="event_date" type="date" required min="<?= date('Y-m-d') ?>" value="<?= e($old['event_date']) ?>">
            </div>
            <div class="field">
              <label for="start_time">Starts</label>
              <input id="start_time" name="start_time" type="time" value="<?= e($old['start_time']) ?>">
            </div>
            <div class="field">
              <label for="end_time">Ends</label>
              <input id="end_time" name="end_time" type="time" value="<?= e($old['end_time']) ?>">
            </div>
          </div>

          <div class="field">
            <label for="description">What is the event? <span class="req">*</span></label>
            <textarea id="description" name="description" required rows="5" placeholder="Who it is for, what will happen, anything to bring."><?= e($old['description']) ?></textarea>
          </div>

          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Send for review</button>
        </form>
      </div>

      <div>
        <h2 style="font-size:1.2rem">Your proposals</h2>
        <?php if (!$myRequests): ?>
          <div class="card"><p class="muted" style="margin:0">You haven't proposed an event yet.</p></div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="data-table">
              <thead><tr><th>Event</th><th>Date</th><th>Status</th></tr></thead>
              <tbody>
                <?php foreach ($myRequests as $r): ?>
                  <tr>
                    <td><?= e($r['title']) ?></td>
                    <td><?= e(format_date($r['event_date'])) ?></td>
                    <td>
                      <?php
                        $pillClass = match ($r['status']) {
                            'pending' => 'pill-alert',
                            'rejected', 'archived' => 'pill-inactive',
                            default => 'pill-active',
                        };
                        $label = match ($r['status']) {
                            'pending' => 'Awaiting review',
                            'rejected' => 'Declined',
                            'upcoming' => 'Approved',
                            'past' => 'Held',
                            default => ucfirst($r['status']),
                        };
                      ?>
                      <span class="pill <?= $pillClass ?>"><?= e($label) ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

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
<?php include __DIR__ . '/includes/footer.php'; ?>
