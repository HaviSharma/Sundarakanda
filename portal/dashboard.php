<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    csrf_check();
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $subscribed = isset($_POST['email_subscribed']) ? 1 : 0;

    if ($fullName === '') {
        flash_set('error', 'Full name cannot be empty.');
    } else {
        $stmt = db()->prepare('UPDATE users SET full_name = ?, phone = ?, address = ?, email_subscribed = ? WHERE id = ?');
        $stmt->bind_param('sssii', $fullName, $phone, $address, $subscribed, $user['id']);
        $stmt->execute();
        $stmt->close();
        flash_set('success', 'Your profile has been updated.');
    }
    redirect('dashboard.php');
}

// Refresh user after a possible update
$user = current_user();

$stmt = db()->prepare('SELECT amount, currency, purpose, payment_method, is_recurring, receipt_number, donated_at FROM donations WHERE user_id = ? ORDER BY donated_at DESC');
$stmt->bind_param('i', $user['id']);
$stmt->execute();
$donations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalGiven = array_sum(array_column($donations, 'amount'));
$giftCount = count($donations);
$recurringCount = count(array_filter($donations, fn($d) => (int)$d['is_recurring'] === 1));
$lastGift = $donations[0]['donated_at'] ?? null;

$pageTitle = 'Your Dashboard';
$activeNav = 'dashboard';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="dash-header">
      <div>
        <h1>Namaste, <?= e($user['full_name']) ?></h1>
        <p class="muted" style="margin:0">Thank you for being part of Sundarakanda's community.</p>
      </div>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a class="btn btn-primary" href="donate.php">Make a Donation</a>
        <a class="btn btn-ghost" href="schedule-event.php">Schedule an Event</a>
        <a class="btn btn-ghost" href="blessings.php">Request a Blessing</a>
      </div>
    </div>

    <div class="stat-tiles">
      <div class="stat-tile"><b><?= e(format_money($totalGiven)) ?></b><span>Total given</span></div>
      <div class="stat-tile"><b><?= e((string)$giftCount) ?></b><span>Gifts recorded</span></div>
      <div class="stat-tile"><b><?= e((string)$recurringCount) ?></b><span>Recurring gifts</span></div>
      <div class="stat-tile"><b><?= $lastGift ? e(format_date($lastGift)) : '&mdash;' ?></b><span>Most recent gift</span></div>
    </div>

    <?php $campaign = get_campaign('one-crore-parayanam'); if ($campaign): $mySummary = user_campaign_summary((int)$campaign['id'], (int)$user['id']); ?>
    <div class="card" style="margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
      <div>
        <h2 style="font-size:1.1rem;margin:0 0 4px">1 Crore Hanuman Jayanti Parayanam</h2>
        <p class="muted" style="margin:0">You've contributed <b><?= number_format($mySummary['total']) ?></b> Parayanam<?= $mySummary['total'] === 1 ? '' : 's' ?> so far.</p>
      </div>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="btn btn-primary btn-sm" href="add-participation.php">Add My Parayanam</a>
        <a class="btn btn-ghost btn-sm" href="my-participation.php">My Participation</a>
      </div>
    </div>
    <?php endif; ?>

    <?php
    $memberCheck = db()->prepare('SELECT is_active FROM members WHERE user_id = ? OR email = ? LIMIT 1');
    $memberCheck->bind_param('is', $user['id'], $user['email']);
    $memberCheck->execute();
    $memberRow = $memberCheck->get_result()->fetch_assoc();
    $memberCheck->close();
    $isMember = $memberRow && $memberRow['is_active'];
    ?>
    <?php if (!$isMember): ?>
    <div class="card" style="margin-bottom:40px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
      <div>
        <h2 style="font-size:1.1rem;margin:0 0 4px">Become a Free Member</h2>
        <p class="muted" style="margin:0">Get email updates on events and Parayanam activities &mdash; free, always.</p>
      </div>
      <a class="btn btn-primary btn-sm" href="membership.php">Join Now</a>
    </div>
    <?php endif; ?>

    <?php $myRequests = get_user_event_requests((int)$user['id'], 10); $nextUp = get_upcoming_events(3); ?>
    <div class="grid grid-2" style="align-items:start;margin-bottom:24px">
      <div>
        <h2 style="font-size:1.3rem">Upcoming Events</h2>
        <?php if (!$nextUp): ?>
          <div class="card"><p class="muted" style="margin:0">Nothing scheduled yet. <a href="schedule-event.php" style="color:var(--saffron-600);font-weight:800">Propose an event</a>.</p></div>
        <?php else: ?>
          <div class="events-pipeline">
            <?php foreach ($nextUp as $ev): $ts = strtotime($ev['event_date']); ?>
              <div class="pipeline-item">
                <div class="pipeline-date"><span class="d"><?= e(date('j', $ts)) ?></span><span class="m"><?= e(date('M', $ts)) ?></span></div>
                <div class="pipeline-body">
                  <h3><?= e($ev['title']) ?></h3>
                  <p><?php if ($ev['start_time']): ?><?= e(date('g:i A', strtotime($ev['start_time']))) ?> &middot; <?php endif; ?><?= e(event_mode_label($ev['event_mode'])) ?></p>
                </div>
                <a class="btn btn-ghost btn-sm" href="events.php">View</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <div>
        <h2 style="font-size:1.3rem">Your Event Proposals</h2>
        <?php if (!$myRequests): ?>
          <div class="card"><p class="muted" style="margin:0">You haven't proposed an event yet. <a href="schedule-event.php" style="color:var(--saffron-600);font-weight:800">Schedule one</a>.</p></div>
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

    <div class="grid grid-2" style="align-items:start;margin-bottom:40px">
      <div>
        <h2 style="font-size:1.3rem">Donation History</h2>
        <?php if (!$donations): ?>
          <div class="card"><p class="muted" style="margin:0">You haven't made a donation yet. <a href="donate.php" style="color:var(--saffron-600);font-weight:800">Make your first gift</a>.</p></div>
        <?php else: ?>
          <div class="table-wrap">
            <table class="data-table">
              <thead><tr><th>Date</th><th>Amount</th><th>Purpose</th><th>Method</th><th>Receipt #</th></tr></thead>
              <tbody>
                <?php foreach ($donations as $d): ?>
                  <tr>
                    <td><?= e(format_date($d['donated_at'])) ?></td>
                    <td><?= e(format_money((float)$d['amount'], $d['currency'])) ?> <?php if ($d['is_recurring']): ?><span class="pill pill-recurring">Recurring</span><?php endif; ?></td>
                    <td><?= e($d['purpose'] ?: '&mdash;') ?></td>
                    <td><?= e($d['payment_method'] ?: '&mdash;') ?></td>
                    <td><?= e($d['receipt_number'] ?: '&mdash;') ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <div>
        <h2 style="font-size:1.3rem">Your Profile</h2>
        <div class="form-card">
          <form method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_profile">
            <div class="field">
              <label for="full_name">Full Name</label>
              <input id="full_name" name="full_name" type="text" required value="<?= e($user['full_name']) ?>">
            </div>
            <div class="field">
              <label>Email</label>
              <input type="email" value="<?= e($user['email']) ?>" disabled style="background:var(--cream-300)">
            </div>
            <div class="field">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="tel" value="<?= e($user['phone']) ?>">
            </div>
            <div class="field">
              <label for="address">Address</label>
              <input id="address" name="address" type="text" value="<?= e($user['address']) ?>">
            </div>
            <div class="field">
              <label class="checkbox-row" style="font-weight:600;color:var(--ink-700)">
                <input type="checkbox" name="email_subscribed" <?= $user['email_subscribed'] ? 'checked' : '' ?>>
                Send me event updates and the daily devotional by email
              </label>
            </div>
            <button class="btn btn-maroon" type="submit" style="width:100%;justify-content:center">Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
