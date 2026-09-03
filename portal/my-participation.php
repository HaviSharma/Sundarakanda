<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$campaign = get_campaign('one-crore-parayanam');
if (!$campaign) {
    http_response_code(404);
    die('Campaign not found.');
}

$mySummary = user_campaign_summary((int)$campaign['id'], (int)$user['id']);
$history = user_participation_history((int)$campaign['id'], (int)$user['id']);
$stats = campaign_stats((int)$campaign['id']);
$target = (int)$campaign['target_count'];
$percent = $target > 0 ? min(100, round(($stats['completed'] / $target) * 100, 2)) : 0;

$pageTitle = 'My Participation';
$activeNav = 'campaign';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="dash-header">
      <div>
        <h1>My Participation</h1>
        <p class="muted" style="margin:0">1 Crore Hanuman Jayanti Parayanam for Universal Peace</p>
      </div>
      <a class="btn btn-primary" href="add-participation.php">Add Today's Parayanam</a>
    </div>

    <h2 style="font-size:1.2rem">My Contribution</h2>
    <div class="stat-tiles">
      <div class="stat-tile"><b><?= number_format($mySummary['total']) ?></b><span>Total Parayanams</span></div>
      <div class="stat-tile"><b><?= number_format($mySummary['month_total']) ?></b><span>This month</span></div>
      <div class="stat-tile"><b><?= number_format($mySummary['year_total']) ?></b><span>This year</span></div>
      <div class="stat-tile"><b><?= $mySummary['last_date'] ? e(format_date($mySummary['last_date'])) : '&mdash;' ?></b><span>Latest submission</span></div>
    </div>

    <div class="grid grid-2" style="align-items:start;margin-top:36px">
      <div>
        <h2 style="font-size:1.2rem">My History</h2>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Date</th><th>Count</th><th>Status</th></tr></thead>
            <tbody>
              <?php if (!$history): ?>
                <tr><td colspan="3" class="muted">No entries yet. <a href="add-participation.php" style="color:var(--saffron-600);font-weight:800">Add your first Parayanam</a>.</td></tr>
              <?php endif; ?>
              <?php foreach ($history as $h): ?>
                <tr>
                  <td><?= e(format_date($h['participation_date'])) ?></td>
                  <td><?= (int)$h['count'] ?></td>
                  <td><span class="pill <?= $h['status'] === 'approved' ? 'pill-active' : 'pill-inactive' ?>"><?= e(ucfirst($h['status'])) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h2 style="font-size:1.2rem">Community Progress</h2>
        <div class="card">
          <div style="display:flex;justify-content:space-between;margin-bottom:10px">
            <b><?= number_format($stats['completed']) ?></b>
            <span class="muted">of <?= number_format($target) ?></span>
          </div>
          <div style="background:var(--cream-300);border-radius:999px;height:12px;overflow:hidden">
            <div style="background:linear-gradient(90deg,var(--saffron-600),var(--gold-500));height:100%;width:<?= e((string)$percent) ?>%"></div>
          </div>
          <p class="muted" style="margin-top:14px;margin-bottom:0"><?= e((string)$percent) ?>% complete &middot; <?= number_format(max(0, $target - $stats['completed'])) ?> remaining</p>
          <a class="btn btn-ghost btn-sm" style="margin-top:16px" href="one-crore-parayanam.php">View Campaign Page</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
