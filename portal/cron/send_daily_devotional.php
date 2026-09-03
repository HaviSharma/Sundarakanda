<?php
/**
 * Run this once a day via cPanel's Cron Jobs (e.g. 6:00 AM):
 *   php /home/USERNAME/public_html/portal/cron/send_daily_devotional.php
 *
 * It sends today's queued devotional (added in /admin/devotionals.php) to
 * every subscribed donor and active devotional subscriber, then marks it
 * as sent so it never goes out twice.
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';

$conn = db();

$stmt = $conn->prepare('SELECT id, verse_text, translation, reflection FROM daily_devotionals WHERE scheduled_date = CURDATE() AND sent = 0 LIMIT 1');
$stmt->execute();
$devotional = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$devotional) {
    echo "No unsent devotional queued for today. Nothing to do.\n";
    exit(0);
}

$dateLabel = date('F j, Y');
$recipients = []; // email => name

$res = $conn->query('SELECT full_name AS name, email FROM users WHERE email_subscribed = 1');
while ($row = $res->fetch_assoc()) {
    $recipients[$row['email']] = $row['name'];
}
$res = $conn->query('SELECT COALESCE(name, "") AS name, email FROM subscribers WHERE is_active = 1');
while ($row = $res->fetch_assoc()) {
    if (!isset($recipients[$row['email']])) {
        $recipients[$row['email']] = $row['name'];
    }
}

$logStmt = $conn->prepare('INSERT INTO email_log (devotional_id, recipient_email, status) VALUES (?, ?, ?)');
$sentCount = 0;

foreach ($recipients as $email => $name) {
    $html = devotional_email_html($name, $devotional['verse_text'], $devotional['translation'] ?? '', $devotional['reflection'] ?? '', $dateLabel);
    $ok = send_mail($email, $name, 'Today\'s Devotional — ' . $dateLabel, $html);
    $status = $ok ? 'sent' : 'failed';
    $logStmt->bind_param('iss', $devotional['id'], $email, $status);
    $logStmt->execute();
    if ($ok) {
        $sentCount++;
    }
}
$logStmt->close();

$upd = $conn->prepare('UPDATE daily_devotionals SET sent = 1 WHERE id = ?');
$upd->bind_param('i', $devotional['id']);
$upd->execute();
$upd->close();

echo "Sent today's devotional to {$sentCount} of " . count($recipients) . " recipients.\n";
