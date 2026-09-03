<?php
require_once __DIR__ . '/../includes/bootstrap.php';

if (is_admin()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $password = $_POST['password'] ?? '';
    if (hash_equals(ADMIN_PASSWORD, $password) && ADMIN_PASSWORD !== 'REPLACE_WITH_A_STRONG_ADMIN_PASSWORD') {
        session_regenerate_id(true);
        $_SESSION['is_admin'] = true;
        redirect('dashboard.php');
    }
    $error = 'Incorrect password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Sign In | Sundarakanda</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/site.css">
<link rel="stylesheet" href="../../assets/css/portal.css">
</head>
<body class="admin-shell">
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px">
  <div class="form-card narrow" style="width:100%">
    <p class="kicker">Sundarakanda</p>
    <h1 style="font-size:1.4rem">Admin Sign In</h1>
    <?php if ($error): ?><div class="alert alert-error" style="margin-bottom:16px"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field">
        <label for="password">Admin Password</label>
        <input id="password" name="password" type="password" required autofocus>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Sign In</button>
    </form>
    <p class="muted" style="text-align:center;margin-top:16px"><a href="../index.php" style="color:var(--saffron-600);font-weight:800">Back to portal</a></p>
  </div>
</div>
</body>
</html>
