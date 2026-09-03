<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect('dashboard.php');
}

$errors = [];
$old = ['full_name' => '', 'email' => '', 'phone' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['phone'] = trim($_POST['phone'] ?? '');
    $old['address'] = trim($_POST['address'] ?? '');
    $password = $_POST['password'] ?? '';
    $subscribe = isset($_POST['email_subscribed']) ? 1 : 0;

    if ($old['full_name'] === '') {
        $errors[] = 'Please enter your full name.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            $errors[] = 'An account with that email already exists. Try signing in instead.';
        }
        $stmt->close();
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $verifyToken = random_token(16);
        $stmt = db()->prepare('INSERT INTO users (full_name, email, password_hash, phone, address, email_subscribed, email_verified, verify_token) VALUES (?, ?, ?, ?, ?, ?, 0, ?)');
        $stmt->bind_param('sssssis', $old['full_name'], $old['email'], $hash, $old['phone'], $old['address'], $subscribe, $verifyToken);
        $stmt->execute();
        $userId = $stmt->insert_id;
        $stmt->close();

        // "Become a Member" and "Register" are now one action, so every new
        // account also gets the free membership record that drives event and
        // Parayanam email updates. An email already on the members list is
        // linked to the new account rather than duplicated.
        $memberToken = random_token(16);
        $ms = db()->prepare(
            'INSERT INTO members (full_name, email, phone, user_id, unsubscribe_token, source)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), full_name = VALUES(full_name), is_active = 1'
        );
        $source = 'signup';
        $ms->bind_param('sssiss', $old['full_name'], $old['email'], $old['phone'], $userId, $memberToken, $source);
        $ms->execute();
        $ms->close();

        login_user($userId);
        flash_set('success', 'Welcome to Sundarakanda! Your membership is active.');
        redirect(safe_redirect_path($_POST['redirect'] ?? '', 'dashboard.php'));
    }
}

$pageTitle = 'Become a Member';
$activeNav = 'membership';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Join Us</p>
      <h1>Become a member</h1>
      <p>Membership is free. Sign up to see events, propose your own, track your donations, and receive updates from Sundarakanda.</p>
    </div>

    <?php if ($errors): ?>
      <div class="narrow" style="margin-bottom:20px">
        <div class="alert alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
      </div>
    <?php endif; ?>

    <div class="form-card narrow">
      <form method="post" novalidate>
        <?= csrf_field() ?>
        <div class="field">
          <label for="full_name">Full Name <span class="req">*</span></label>
          <input id="full_name" name="full_name" type="text" required value="<?= e($old['full_name']) ?>">
        </div>
        <div class="field">
          <label for="email">Email Address <span class="req">*</span></label>
          <input id="email" name="email" type="email" required value="<?= e($old['email']) ?>">
        </div>
        <div class="field-row">
          <div class="field">
            <label for="phone">Phone</label>
            <input id="phone" name="phone" type="tel" value="<?= e($old['phone']) ?>">
          </div>
          <div class="field">
            <label for="password">Password <span class="req">*</span></label>
            <input id="password" name="password" type="password" minlength="8" required>
          </div>
        </div>
        <div class="field">
          <label for="address">Address</label>
          <input id="address" name="address" type="text" value="<?= e($old['address']) ?>">
        </div>
        <div class="field">
          <label class="checkbox-row" style="font-weight:600;color:var(--ink-700)">
            <input type="checkbox" name="email_subscribed" checked>
            Send me event updates and the daily devotional by email
          </label>
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Create Account</button>
        <p class="muted" style="text-align:center;margin-top:16px">Already have an account? <a href="login.php" style="color:var(--saffron-600);font-weight:800">Sign in</a></p>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
