<?php
require_once __DIR__ . '/includes/bootstrap.php';

if (current_user()) {
    redirect('dashboard.php');
}

$errors = [];
$emailOld = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $emailOld = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = db()->prepare('SELECT id, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $emailOld);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row && password_verify($password, $row['password_hash'])) {
        login_user((int)$row['id']);
        flash_set('success', 'Welcome back!');
        redirect(safe_redirect_path($_POST['redirect'] ?? ''));
    }
    $errors[] = 'That email and password don\'t match. Please try again.';
}

$pageTitle = 'Sign In';
$activeNav = '';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Welcome Back</p>
      <h1>Sign in to your account</h1>
    </div>

    <?php if ($errors): ?>
      <div class="narrow" style="margin-bottom:20px">
        <div class="alert alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div>
      </div>
    <?php endif; ?>

    <div class="form-card narrow">
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= e($_GET['redirect'] ?? '') ?>">
        <div class="field">
          <label for="email">Email</label>
          <input id="email" name="email" type="email" required value="<?= e($emailOld) ?>">
        </div>
        <div class="field">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" required>
        </div>
        <div class="field" style="text-align:right">
          <a href="forgot-password.php" style="color:var(--saffron-600);font-weight:800;font-size:.88rem">Forgot Password?</a>
        </div>
        <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Login</button>
        <p class="muted" style="text-align:center;margin-top:18px">Don't have an account? <a href="signup.php" style="color:var(--saffron-600);font-weight:800">Sign up</a></p>
      </form>
    </div>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
