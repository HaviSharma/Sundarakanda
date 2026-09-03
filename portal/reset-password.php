<?php
require_once __DIR__ . '/includes/bootstrap.php';

$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$errors = [];
$done = false;

$stmt = db()->prepare('SELECT id, reset_expires FROM users WHERE reset_token = ? LIMIT 1');
$stmt->bind_param('s', $token);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

$valid = $row && $row['reset_expires'] && strtotime($row['reset_expires']) > time();

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd = db()->prepare('UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?');
        $upd->bind_param('si', $hash, $row['id']);
        $upd->execute();
        $upd->close();
        $done = true;
    }
}

$pageTitle = 'Reset Password';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Account Recovery</p>
      <h1>Choose a new password</h1>
    </div>

    <div class="form-card narrow">
      <?php if (!$valid): ?>
        <div class="alert alert-error">This reset link is invalid or has expired. Please request a new one.</div>
        <p style="text-align:center;margin-top:18px"><a href="forgot-password.php" style="color:var(--saffron-600);font-weight:800">Request a new link</a></p>
      <?php elseif ($done): ?>
        <div class="alert alert-success">Your password has been updated.</div>
        <p style="text-align:center;margin-top:18px"><a href="login.php" style="color:var(--saffron-600);font-weight:800">Sign in now</a></p>
      <?php else: ?>
        <?php if ($errors): ?>
          <div class="alert alert-error" style="margin-bottom:18px"><?= implode('<br>', array_map('e', $errors)) ?></div>
        <?php endif; ?>
        <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="token" value="<?= e($token) ?>">
          <div class="field">
            <label for="password">New Password</label>
            <input id="password" name="password" type="password" minlength="8" required>
          </div>
          <div class="field">
            <label for="confirm">Confirm New Password</label>
            <input id="confirm" name="confirm" type="password" minlength="8" required>
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Update Password</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
