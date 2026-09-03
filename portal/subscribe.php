<?php
require_once __DIR__ . '/includes/bootstrap.php';

$done = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $name = trim($_POST['name'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT id, is_active FROM subscribers WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            $upd = db()->prepare('UPDATE subscribers SET is_active = 1, name = ? WHERE id = ?');
            $upd->bind_param('si', $name, $existing['id']);
            $upd->execute();
            $upd->close();
        } else {
            $token = random_token(20);
            $ins = db()->prepare('INSERT INTO subscribers (email, name, is_active, unsubscribe_token) VALUES (?, ?, 1, ?)');
            $ins->bind_param('sss', $email, $name, $token);
            $ins->execute();
            $ins->close();
        }
        $done = true;
    }
}

$pageTitle = 'Daily Devotional';
$activeNav = 'subscribe';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Daily Devotional</p>
      <h1>Subscribe to a daily verse</h1>
      <p>Receive a short daily verse from Hanuman Chalisa or Sundarakanda, with its meaning and a brief reflection &mdash; no account required.</p>
    </div>

    <div class="form-card narrow">
      <?php if ($done): ?>
        <div class="alert alert-success">You're subscribed! Look out for tomorrow's devotional in your inbox.</div>
      <?php else: ?>
        <?php if ($errors): ?>
          <div class="alert alert-error" style="margin-bottom:18px"><?= implode('<br>', array_map('e', $errors)) ?></div>
        <?php endif; ?>
        <form method="post">
          <?= csrf_field() ?>
          <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text">
          </div>
          <div class="field">
            <label for="email">Email Address <span class="req">*</span></label>
            <input id="email" name="email" type="email" required>
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Subscribe</button>
        </form>
      <?php endif; ?>
    </div>
    <p class="muted text-center" style="margin-top:18px">Already a donor? <a href="dashboard.php" style="color:var(--saffron-600);font-weight:800">Manage your email preferences from your dashboard</a> instead.</p>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
