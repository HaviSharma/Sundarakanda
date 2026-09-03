<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_role(['admin', 'operations_manager']);

$campaign = get_campaign('one-crore-parayanam');
if (!$campaign) {
    http_response_code(404);
    die('Campaign not found. Run sql/migrations/002_campaign_platform.sql first.');
}
$campaignId = (int)$campaign['id'];

// --- Export (CSV) -----------------------------------------------------
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query("SELECT p.id, u.full_name, u.email, p.participation_date, p.count, p.status, p.notes, p.created_at
        FROM participation_entries p JOIN users u ON u.id = p.user_id
        WHERE p.campaign_id = $campaignId ORDER BY p.participation_date DESC")->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="parayanam-contributions.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Email', 'Date', 'Count', 'Status', 'Notes', 'Submitted At']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['full_name'], $r['email'], $r['participation_date'], $r['count'], $r['status'], $r['notes'], $r['created_at']]);
    }
    fclose($out);
    exit;
}

// --- Historical adjustment ----------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_adjustment') {
    csrf_check();
    require_role(['admin', 'operations_manager']);
    $count = (int)($_POST['adjustment_count'] ?? 0);
    $effectiveDate = $_POST['effective_date'] ?? date('Y-m-d');
    $reason = trim($_POST['reason'] ?? '');

    if ($count <= 0 || $reason === '') {
        flash_set('error', 'Please provide a positive count and a reason/source.');
    } else {
        $enteredBy = current_user()['id'] ?? null;
        if (!$enteredBy) {
            flash_set('error', 'Historical adjustments require a signed-in staff account (not the shared admin password), so the record has an owner. Please sign in with your donor account first, then have an admin grant it the admin/operations_manager role.');
        } else {
            $stmt = db()->prepare('INSERT INTO campaign_adjustments (campaign_id, adjustment_count, effective_date, reason, entered_by) VALUES (?, ?, ?, ?, ?)');
            $stmt->bind_param('iissi', $campaignId, $count, $effectiveDate, $reason, $enteredBy);
            $stmt->execute();
            $adjId = $stmt->insert_id;
            $stmt->close();
            audit('create', 'campaign_adjustment', $adjId, null, ['count' => $count, 'date' => $effectiveDate, 'reason' => $reason]);
            flash_set('success', 'Historical count of ' . number_format($count) . ' recorded and logged in the audit trail.');
        }
    }
    redirect('campaign.php');
}

// --- Correct/void an entry ------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_entry_status') {
    csrf_check();
    require_role(['admin', 'operations_manager']);
    $entryId = (int)($_POST['entry_id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';
    if (in_array($newStatus, ['approved', 'pending', 'rejected'], true) && $entryId) {
        $stmt = db()->prepare('SELECT status FROM participation_entries WHERE id = ?');
        $stmt->bind_param('i', $entryId);
        $stmt->execute();
        $old = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($old) {
            $stmt = db()->prepare('UPDATE participation_entries SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $newStatus, $entryId);
            $stmt->execute();
            $stmt->close();
            audit('update_status', 'participation_entry', $entryId, $old['status'], $newStatus);
            flash_set('success', 'Entry #' . $entryId . ' updated to ' . $newStatus . '.');
        }
    }
    redirect('campaign.php');
}

$stats = campaign_stats($campaignId);
$target = (int)$campaign['target_count'];
$percent = $target > 0 ? min(100, round(($stats['completed'] / $target) * 100, 2)) : 0;

$entries = db()->query("SELECT p.id, u.full_name, u.email, p.participation_date, p.count, p.status
    FROM participation_entries p JOIN users u ON u.id = p.user_id
    WHERE p.campaign_id = $campaignId ORDER BY p.created_at DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);

$adjustments = db()->query("SELECT a.adjustment_count, a.effective_date, a.reason, a.created_at, u.full_name
    FROM campaign_adjustments a LEFT JOIN users u ON u.id = a.entered_by
    WHERE a.campaign_id = $campaignId ORDER BY a.created_at DESC LIMIT 20")->fetch_all(MYSQLI_ASSOC);

admin_top('Campaign — 1 Crore Parayanam', '');
?>
<h1 style="margin-bottom:6px">1 Crore Hanuman Jayanti Parayanam</h1>
<p class="muted" style="margin-bottom:28px">Campaign management &mdash; every total below is computed live from participation entries and historical adjustments; nothing here is a manually-set number.</p>

<div class="stat-tiles">
  <div class="stat-tile"><b><?= number_format($target) ?></b><span>Goal</span></div>
  <div class="stat-tile"><b><?= number_format($stats['completed']) ?></b><span>Completed (<?= $percent ?>%)</span></div>
  <div class="stat-tile"><b><?= number_format(max(0, $target - $stats['completed'])) ?></b><span>Remaining</span></div>
  <div class="stat-tile"><b><?= number_format($stats['participants']) ?></b><span>Participants</span></div>
</div>
<div class="stat-tiles">
  <div class="stat-tile"><b><?= number_format($stats['today']) ?></b><span>Today</span></div>
  <div class="stat-tile"><b><?= number_format($stats['week']) ?></b><span>Last 7 days</span></div>
  <div class="stat-tile"><b><?= number_format($stats['month']) ?></b><span>Last 30 days</span></div>
  <div class="stat-tile"><b><?= number_format($stats['historical_total']) ?></b><span>Historical verified count</span></div>
</div>

<div class="grid grid-2" style="align-items:start;margin:36px 0">
  <div class="form-card">
    <h2 style="font-size:1.1rem">Add historical count</h2>
    <p class="muted" style="font-size:.85rem">For Parayanams completed before digital tracking began. Creates an auditable record &mdash; never silently edits the total.</p>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="add_adjustment">
      <div class="field-row">
        <div class="field">
          <label for="adjustment_count">Count <span class="req">*</span></label>
          <input id="adjustment_count" name="adjustment_count" type="number" min="1" required>
        </div>
        <div class="field">
          <label for="effective_date">Effective Date</label>
          <input id="effective_date" name="effective_date" type="date" value="<?= e(date('Y-m-d')) ?>">
        </div>
      </div>
      <div class="field">
        <label for="reason">Reason / Source <span class="req">*</span></label>
        <input id="reason" name="reason" type="text" required placeholder="e.g. Pre-2024 temple registers, verified by committee">
      </div>
      <button class="btn btn-maroon" type="submit" style="width:100%;justify-content:center">Add Historical Count</button>
    </form>
    <h3 style="font-size:.95rem;margin-top:24px">Recent adjustments</h3>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Count</th><th>Reason</th><th>By</th></tr></thead>
        <tbody>
          <?php foreach ($adjustments as $a): ?>
            <tr><td><?= e(format_date($a['effective_date'])) ?></td><td><?= number_format((int)$a['adjustment_count']) ?></td><td><?= e($a['reason']) ?></td><td><?= e($a['full_name'] ?: '—') ?></td></tr>
          <?php endforeach; ?>
          <?php if (!$adjustments): ?><tr><td colspan="4" class="muted">None yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
      <h2 style="font-size:1.1rem;margin:0">Contributions</h2>
      <a class="btn btn-ghost btn-sm" href="campaign.php?export=csv">Export CSV</a>
    </div>
    <div class="table-wrap" style="max-height:520px;overflow-y:auto">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Participant</th><th>Count</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($entries as $en): ?>
            <tr>
              <td><?= e(format_date($en['participation_date'])) ?></td>
              <td><?= e($en['full_name']) ?></td>
              <td><?= (int)$en['count'] ?></td>
              <td><span class="pill <?= $en['status'] === 'approved' ? 'pill-active' : 'pill-inactive' ?>"><?= e(ucfirst($en['status'])) ?></span></td>
              <td>
                <form method="post" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="update_entry_status">
                  <input type="hidden" name="entry_id" value="<?= (int)$en['id'] ?>">
                  <input type="hidden" name="status" value="<?= $en['status'] === 'approved' ? 'rejected' : 'approved' ?>">
                  <button class="btn btn-ghost btn-sm" type="submit"><?= $en['status'] === 'approved' ? 'Reject' : 'Approve' ?></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$entries): ?><tr><td colspan="5" class="muted">No contributions yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php admin_bottom(); ?>
