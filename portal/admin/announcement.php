<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_admin();

$conn = db();
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($subject === '' || $message === '') {
        flash_set('error', 'Please fill in both a subject and a message.');
        redirect('announcement.php');
    }

    $recipients = [];
    $donorRes = $conn->query('SELECT full_name AS name, email FROM users WHERE email_subscribed = 1');
    while ($row = $donorRes->fetch_assoc()) {
        $recipients[$row['email']] = $row['name'];
    }
    $subRes = $conn->query('SELECT COALESCE(name, "") AS name, email FROM subscribers WHERE is_active = 1');
    while ($row = $subRes->fetch_assoc()) {
        if (!isset($recipients[$row['email']])) {
            $recipients[$row['email']] = $row['name'];
        }
    }

    $sentCount = 0;
    $logStmt = $conn->prepare('INSERT INTO email_log (devotional_id, recipient_email, status) VALUES (NULL, ?, ?)');
    foreach ($recipients as $email => $name) {
        $html = '<div style="font-family:Georgia,serif;background:#faf5ec;padding:32px"><div style="max-width:560px;margin:0 auto;background:#fff;border-radius:14px;padding:32px;border:1px solid #e8dcc6">'
              . '<p style="color:#d9711c;font-weight:bold;text-transform:uppercase;font-size:12px;letter-spacing:.06em">Sundarakanda Announcement</p>'
              . '<p>Namaste ' . htmlspecialchars($name ?: 'friend', ENT_QUOTES) . ',</p>'
              . '<div>' . nl2br(htmlspecialchars($message, ENT_QUOTES)) . '</div>'
              . '</div></div>';
        $ok = send_mail($email, $name, $subject, $html);
        $status = $ok ? 'sent' : 'failed';
        $logStmt->bind_param('ss', $email, $status);
        $logStmt->execute();
        if ($ok) {
            $sentCount++;
        }
    }
    $logStmt->close();

    flash_set('success', "Announcement sent to {$sentCount} of " . count($recipients) . ' subscribers.');
    redirect('announcement.php');
}

$donorSubCount = $conn->query('SELECT COUNT(*) c FROM users WHERE email_subscribed = 1')->fetch_assoc()['c'];
$subCount = $conn->query('SELECT COUNT(*) c FROM subscribers WHERE is_active = 1')->fetch_assoc()['c'];

admin_top('Send Announcement', 'announcement');
?>
<h1 style="margin-bottom:10px">Send an Announcement</h1>
<p class="muted" style="margin-bottom:28px">This goes to every subscribed donor and active devotional subscriber &mdash; <?= (int)$donorSubCount + (int)$subCount ?> recipients total (<?= (int)$donorSubCount ?> donors, <?= (int)$subCount ?> devotional subscribers). Use it for new event announcements or important updates.</p>

<div class="form-card medium">
  <form method="post">
    <?= csrf_field() ?>
    <div class="field">
      <label for="subject">Subject <span class="req">*</span></label>
      <input id="subject" name="subject" type="text" required placeholder="New Parayanam session — Saturday, Sept 14">
    </div>
    <div class="field">
      <label for="message">Message <span class="req">*</span></label>
      <textarea id="message" name="message" rows="8" required placeholder="Write your announcement here..."></textarea>
    </div>
    <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Send to All Subscribers</button>
  </form>
</div>
<?php admin_bottom(); ?>
