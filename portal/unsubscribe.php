<?php
require_once __DIR__ . '/includes/bootstrap.php';

$token = $_GET['token'] ?? '';
$result = null;

if ($token !== '') {
    $stmt = db()->prepare('UPDATE subscribers SET is_active = 0 WHERE unsubscribe_token = ?');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $result = $stmt->affected_rows > 0;
    $stmt->close();
}

$pageTitle = 'Unsubscribe';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="form-card narrow text-center">
      <?php if ($result): ?>
        <div class="alert alert-info">You've been unsubscribed from the daily devotional. You're welcome back any time.</div>
      <?php else: ?>
        <div class="alert alert-error">We couldn't find that subscription. It may already be unsubscribed.</div>
      <?php endif; ?>
      <a class="btn btn-ghost" style="margin-top:20px" href="../index.html">Back to Sundarakanda</a>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
