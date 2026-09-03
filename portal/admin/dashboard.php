<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_layout.php';
// Operations managers review and approve event requests here, so this page
// admits both roles; the tiles below are gated individually.
require_role(['admin', 'operations_manager']);
$isFullAdmin = current_role() === 'admin';

$conn = db();
$totalDonors = $conn->query('SELECT COUNT(*) c FROM users')->fetch_assoc()['c'];
$totalDonations = $conn->query('SELECT COUNT(*) c, COALESCE(SUM(amount),0) s FROM donations')->fetch_assoc();
$totalSubscribers = $conn->query('SELECT COUNT(*) c FROM subscribers WHERE is_active = 1')->fetch_assoc()['c'];
$emailSubscribedDonors = $conn->query('SELECT COUNT(*) c FROM users WHERE email_subscribed = 1')->fetch_assoc()['c'];
$recentDonations = $conn->query('SELECT d.amount, d.currency, d.purpose, d.donated_at, u.full_name FROM donations d JOIN users u ON u.id = d.user_id ORDER BY d.donated_at DESC LIMIT 8')->fetch_all(MYSQLI_ASSOC);
$nextDevotional = $conn->query("SELECT scheduled_date, sent FROM daily_devotionals WHERE scheduled_date >= CURDATE() ORDER BY scheduled_date ASC LIMIT 1")->fetch_assoc();

admin_top('Overview', 'dashboard');
?>
<h1 style="margin-bottom:28px">Overview</h1>
<div class="stat-tiles">
  <div class="stat-tile"><b><?= (int)$totalDonors ?></b><span>Registered donors</span></div>
  <div class="stat-tile"><b><?= e(format_money((float)$totalDonations['s'])) ?></b><span>Total recorded (<?= (int)$totalDonations['c'] ?> gifts)</span></div>
  <div class="stat-tile"><b><?= (int)$emailSubscribedDonors + (int)$totalSubscribers ?></b><span>Total email subscribers</span></div>
  <div class="stat-tile"><b><?= $nextDevotional ? e(format_date($nextDevotional['scheduled_date'])) : 'None queued' ?></b><span>Next daily devotional</span></div>
  <?php $evStats = event_stats(); ?>
  <div class="stat-tile<?= $evStats['pending'] ? ' stat-tile-alert' : '' ?>"><b><?= (int)$evStats['pending'] ?></b><span>Event requests awaiting review</span></div>
</div>

<div class="grid grid-2" style="align-items:start">
  <div>
    <h2 style="font-size:1.2rem">Recent Donations</h2>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Donor</th><th>Amount</th><th>Purpose</th></tr></thead>
        <tbody>
          <?php if (!$recentDonations): ?>
            <tr><td colspan="4" class="muted">No donations recorded yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($recentDonations as $d): ?>
            <tr>
              <td><?= e(format_date($d['donated_at'])) ?></td>
              <td><?= e($d['full_name']) ?></td>
              <td><?= e(format_money((float)$d['amount'], $d['currency'])) ?></td>
              <td><?= e($d['purpose']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <a class="btn btn-ghost btn-sm" style="margin-top:14px" href="donations.php">View &amp; record all donations</a>
  </div>
  <div>
    <h2 style="font-size:1.2rem">Quick Actions</h2>
    <div class="grid" style="gap:14px">
      <?php if ($isFullAdmin): ?>
      <a class="card" href="devotionals.php"><h3 style="margin-bottom:4px">Queue a Daily Devotional</h3><p class="muted" style="margin:0">Add tomorrow's verse, translation, and reflection.</p></a>
      <a class="card" href="donations.php"><h3 style="margin-bottom:4px">Record a Donation</h3><p class="muted" style="margin:0">Log a gift received offline (check, cash, bank transfer).</p></a>
      <a class="card" href="announcement.php"><h3 style="margin-bottom:4px">Send an Announcement</h3><p class="muted" style="margin:0">Email all subscribed donors and devotional subscribers about a new event.</p></a>
      <?php endif; ?>
      <?php if (has_role(['admin', 'operations_manager'])): ?>
      <a class="card" href="events.php"><h3 style="margin-bottom:4px">Events</h3><p class="muted" style="margin:0">Create and manage Sundarakanda events, track RSVPs and attendance.</p></a>
      <a class="card" href="campaign.php"><h3 style="margin-bottom:4px">1 Crore Parayanam Campaign</h3><p class="muted" style="margin:0">Review contributions, add verified historical counts, export data.</p></a>
      <a class="card" href="members.php"><h3 style="margin-bottom:4px">Members</h3><p class="muted" style="margin:0">View free community members, export the list, manage subscriptions.</p></a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php admin_bottom(); ?>
