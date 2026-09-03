<?php
require_once __DIR__ . '/includes/bootstrap.php';

$user = current_user();
$done = false;
$errors = [];
$existingMember = null;

// If already logged in, check whether they're already a member so we don't
// re-ask for info we already have.
if ($user) {
    $stmt = db()->prepare('SELECT id, full_name, email, phone, is_active FROM members WHERE user_id = ? OR email = ? LIMIT 1');
    $stmt->bind_param('is', $user['id'], $user['email']);
    $stmt->execute();
    $existingMember = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $fullName = trim($_POST['full_name'] ?? ($user['full_name'] ?? ''));
    $email = trim($_POST['email'] ?? ($user['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? ($user['phone'] ?? ''));
    $whatsapp = trim($_POST['whatsapp_number'] ?? '');
    $city = trim($_POST['city'] ?? '');

    if ($fullName === '') {
        $errors[] = 'Please enter your name.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        $stmt = db()->prepare('SELECT id, is_active FROM members WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($existing) {
            $upd = db()->prepare('UPDATE members SET full_name = ?, phone = ?, whatsapp_number = ?, city = ?, is_active = 1, user_id = COALESCE(user_id, ?) WHERE id = ?');
            $userId = $user['id'] ?? null;
            $upd->bind_param('ssssii', $fullName, $phone, $whatsapp, $city, $userId, $existing['id']);
            $upd->execute();
            $upd->close();
            $memberId = $existing['id'];
            audit('update', 'member', $memberId, null, ['reactivated_or_updated' => true]);
        } else {
            $token = random_token(24);
            $userId = $user['id'] ?? null;
            $ins = db()->prepare("INSERT INTO members (full_name, email, phone, whatsapp_number, city, user_id, is_active, unsubscribe_token, source) VALUES (?, ?, ?, ?, ?, ?, 1, ?, 'web')");
            $ins->bind_param('sssssis', $fullName, $email, $phone, $whatsapp, $city, $userId, $token);
            $ins->execute();
            $memberId = $ins->insert_id;
            $ins->close();
            audit('create', 'member', $memberId, null, ['email' => $email]);

            // Send welcome email (best-effort; membership still succeeds if mail fails)
            $tokenRow = db()->prepare('SELECT unsubscribe_token FROM members WHERE id = ?');
            $tokenRow->bind_param('i', $memberId);
            $tokenRow->execute();
            $tokenVal = $tokenRow->get_result()->fetch_assoc()['unsubscribe_token'] ?? $token;
            $tokenRow->close();
            $unsubUrl = SITE_URL . '/unsubscribe-member.php?token=' . urlencode($tokenVal);
            if (send_mail($email, $fullName, 'Welcome to Sundarakanda', member_welcome_email_html($fullName, $unsubUrl))) {
                $mk = db()->prepare('UPDATE members SET welcome_email_sent = 1 WHERE id = ?');
                $mk->bind_param('i', $memberId);
                $mk->execute();
                $mk->close();
            }
        }
        $done = true;
    }
}

$pageTitle = 'Become a Member';
$activeNav = 'membership';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Free Membership</p>
      <h1>Become a Sundarakanda Member</h1>
      <p>Join our community &mdash; free, always. Members receive email updates about upcoming Parayanam sessions, events, and community news.</p>
    </div>

    <div class="form-card narrow">
      <?php if ($done): ?>
        <div class="alert alert-success">Welcome! You're registered as a Sundarakanda member and we'll keep you posted by email.</div>
        <div style="display:flex;gap:12px;justify-content:center;margin-top:22px;flex-wrap:wrap">
          <a class="btn btn-primary" href="one-crore-parayanam.php">Explore the 1 Crore Parayanam</a>
          <a class="btn btn-ghost" href="events.php">See Upcoming Events</a>
        </div>
      <?php elseif ($existingMember && $existingMember['is_active']): ?>
        <div class="alert alert-success">You're already a member, <?= e($existingMember['full_name']) ?>! We have your email (<?= e($existingMember['email']) ?>) on file for updates.</div>
      <?php else: ?>
        <?php if ($errors): ?>
          <div class="alert alert-error" style="margin-bottom:18px"><?= implode('<br>', array_map('e', $errors)) ?></div>
        <?php endif; ?>
        <?php if ($user): ?>
          <p class="muted" style="margin-bottom:16px">You're signed in as <?= e($user['full_name']) ?> &mdash; we've filled in what we already have.</p>
        <?php endif; ?>
        <form method="post">
          <?= csrf_field() ?>
          <div class="field">
            <label for="full_name">Full Name <span class="req">*</span></label>
            <input id="full_name" name="full_name" type="text" required value="<?= e($_POST['full_name'] ?? ($user['full_name'] ?? '')) ?>">
          </div>
          <div class="field">
            <label for="email">Email Address <span class="req">*</span></label>
            <input id="email" name="email" type="email" required value="<?= e($_POST['email'] ?? ($user['email'] ?? '')) ?>">
          </div>
          <div class="field-row">
            <div class="field">
              <label for="phone">Phone</label>
              <input id="phone" name="phone" type="tel" value="<?= e($_POST['phone'] ?? ($user['phone'] ?? '')) ?>">
            </div>
            <div class="field">
              <label for="whatsapp_number">WhatsApp Number</label>
              <input id="whatsapp_number" name="whatsapp_number" type="tel" placeholder="Optional, for future updates" value="<?= e($_POST['whatsapp_number'] ?? '') ?>">
            </div>
          </div>
          <div class="field">
            <label for="city">City</label>
            <input id="city" name="city" type="text" value="<?= e($_POST['city'] ?? '') ?>">
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Join as a Member &mdash; Free</button>
        </form>
        <p class="muted" style="font-size:.85rem;margin-top:14px">We'll only use this to send you Sundarakanda updates by email. Unsubscribe any time.</p>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
