<?php
require_once __DIR__ . '/includes/bootstrap.php';

$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = db()->prepare('SELECT id, full_name FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $token = random_token(24);
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
            $upd = db()->prepare('UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?');
            $upd->bind_param('ssi', $token, $expires, $row['id']);
            $upd->execute();
            $upd->close();

            $link = rtrim(SITE_URL, '/') . '/reset-password.php?token=' . $token;
            $html = '<p>Namaste ' . htmlspecialchars($row['full_name'], ENT_QUOTES) . ',</p>'
                  . '<p>Click the link below to reset your Sundarakanda account password. This link expires in one hour.</p>'
                  . '<p><a href="' . htmlspecialchars($link, ENT_QUOTES) . '">' . htmlspecialchars($link, ENT_QUOTES) . '</a></p>'
                  . '<p>If you did not request this, you can safely ignore this email.</p>';
            send_mail($email, $row['full_name'], 'Reset your Sundarakanda password', $html);
        }
        // Always show the same confirmation, whether or not the email exists,
        // so we don't reveal which addresses have accounts.
        $sent = true;
    }
}

$pageTitle = 'Forgot Password';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Account Recovery</p>
      <h1>Reset your password</h1>
    </div>

    <div class="form-card narrow">
      <?php if ($sent): ?>
        <div class="alert alert-info">If that email has an account, we've sent a password reset link to it. Please check your inbox.</div>
        <p style="text-align:center;margin-top:18px"><a href="login.php" style="color:var(--saffron-600);font-weight:800">Back to sign in</a></p>
      <?php else: ?>
        <form method="post">
          <?= csrf_field() ?>
          <div class="field">
            <label for="email">Email Address</label>
            <input id="email" name="email" type="email" required>
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Send Reset Link</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
