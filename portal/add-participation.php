<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$campaign = get_campaign('one-crore-parayanam');
if (!$campaign) {
    http_response_code(404);
    die('Campaign not found.');
}

$errors = [];
$submitted = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $date = $_POST['participation_date'] ?? date('Y-m-d');
    $count = (int)($_POST['count'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');

    if ($count <= 0) {
        $errors[] = 'Please enter a count greater than zero.';
    }
    $ts = strtotime($date);
    if (!$ts || $ts > time() + 86400) {
        $errors[] = 'Please choose a valid date (not in the future).';
    }

    if (!$errors) {
        $stmt = db()->prepare("INSERT INTO participation_entries (campaign_id, user_id, participation_date, count, notes, source, status) VALUES (?, ?, ?, ?, ?, 'web', 'approved')");
        $stmt->bind_param('iisis', $campaign['id'], $user['id'], $date, $count, $notes);
        $stmt->execute();
        $entryId = $stmt->insert_id;
        $stmt->close();

        audit('create', 'participation_entry', $entryId, null, ['count' => $count, 'date' => $date]);
        $submitted = $count;
    }
}

$stats = campaign_stats((int)$campaign['id']);
$mySummary = user_campaign_summary((int)$campaign['id'], (int)$user['id']);

$pageTitle = 'Add My Parayanam';
$activeNav = 'campaign';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">1 Crore Parayanam</p>
      <h1>Add my Parayanam</h1>
    </div>

    <?php if ($submitted !== null): ?>
      <div class="form-card narrow text-center">
        <div class="alert alert-success">Thank you for contributing to the 1 Crore Parayanam for Universal Peace.</div>
        <div class="stat-tiles" style="grid-template-columns:repeat(3,1fr);margin-top:24px">
          <div class="stat-tile"><b><?= (int)$submitted ?></b><span>Your contribution</span></div>
          <div class="stat-tile"><b><?= number_format($stats['completed'] + $submitted) ?></b><span>Community total</span></div>
          <div class="stat-tile"><b><?= min(100, round((($stats['completed'] + $submitted) / max(1,(int)$campaign['target_count'])) * 100, 1)) ?>%</b><span>Toward 1 Crore</span></div>
        </div>
        <div style="display:flex;gap:12px;justify-content:center;margin-top:26px;flex-wrap:wrap">
          <a class="btn btn-primary" href="add-participation.php">Add Another</a>
          <a class="btn btn-ghost" href="my-participation.php">View My Participation</a>
        </div>
      </div>
    <?php else: ?>
      <?php if ($errors): ?>
        <div class="narrow" style="margin-bottom:18px"><div class="alert alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div></div>
      <?php endif; ?>
      <div class="form-card narrow">
        <form method="post">
          <?= csrf_field() ?>
          <div class="field">
            <label for="participation_date">Date <span class="req">*</span></label>
            <input id="participation_date" name="participation_date" type="date" required value="<?= e(date('Y-m-d')) ?>" max="<?= e(date('Y-m-d')) ?>">
          </div>
          <div class="field">
            <label for="count">Number of Parayanams <span class="req">*</span></label>
            <input id="count" name="count" type="number" min="1" step="1" required autofocus inputmode="numeric">
          </div>
          <div class="field">
            <label for="notes">Note (optional)</label>
            <textarea id="notes" name="notes" rows="2" placeholder="e.g. group session, dedication..."></textarea>
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center;padding:16px">Submit</button>
        </form>
      </div>
      <p class="muted text-center" style="margin-top:14px">You're signed in as <?= e($user['full_name']) ?>, so we already have your name and contact details.</p>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
