<?php
/**
 * Shared page header. Expects (optionally) $pageTitle and $activeNav to be
 * set before including this file.
 */
$pageTitle = $pageTitle ?? 'Donor Portal';
$activeNav = $activeNav ?? '';
$loggedInUser = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | <?= e(SITE_NAME) ?></title>
<link rel="icon" href="../img/favicon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/site.css">
<link rel="stylesheet" href="../assets/css/portal.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header">
  <div class="container nav-row">
    <a class="brand" href="index.php">
      <img src="../img/logo_n_2.png" alt="Sundarakanda logo">
    </a>
    <nav class="main-nav" aria-label="Primary">
      <button class="nav-close" aria-label="Close menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
      <ul>
        <li class="<?= $activeNav === 'home' ? 'active' : '' ?>"><a class="nav-link" href="../index.php">Main Site</a></li>
        <li class="<?= $activeNav === 'events' ? 'active' : '' ?>"><a class="nav-link" href="events.php">Events</a></li>
        <?php if ($loggedInUser): ?>
          <li class="<?= $activeNav === 'donate' ? 'active' : '' ?>"><a class="nav-link" href="donate.php">Donations</a></li>
          <li class="<?= $activeNav === 'schedule' ? 'active' : '' ?>"><a class="nav-link" href="schedule-event.php">Schedule an Event</a></li>
          <li class="<?= $activeNav === 'campaign' ? 'active' : '' ?>"><a class="nav-link" href="one-crore-parayanam.php">1 Crore Parayanam</a></li>
          <li class="<?= $activeNav === 'dashboard' ? 'active' : '' ?>"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <?php else: ?>
          <li class="<?= $activeNav === 'campaign' ? 'active' : '' ?>"><a class="nav-link" href="one-crore-parayanam.php">1 Crore Parayanam</a></li>
          <li class="<?= $activeNav === 'membership' ? 'active' : '' ?>"><a class="nav-link" href="signup.php">Become a Member</a></li>
          <li class="<?= $activeNav === 'subscribe' ? 'active' : '' ?>"><a class="nav-link" href="subscribe.php">Daily Devotional</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <div class="nav-cta">
      <?php if ($loggedInUser): ?>
        <a class="btn btn-ghost btn-sm" href="dashboard.php"><span><?= e(explode(' ', $loggedInUser['full_name'])[0]) ?></span></a>
        <a class="btn btn-primary btn-sm" href="logout.php"><span>Sign Out</span></a>
      <?php else: ?>
        <a class="btn btn-ghost btn-sm" href="signup.php"><span>Become a Member</span></a>
        <a class="btn btn-primary btn-sm" href="login.php"><span>Login</span></a>
      <?php endif; ?>
      <button class="nav-toggle" aria-label="Toggle menu" aria-expanded="false"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
    </div>
  </div>
  <div class="nav-scrim"></div>
</header>

<main id="main">
<?php foreach (flash_all() as $flash): ?>
  <div class="container" style="padding-top:20px">
    <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
  </div>
<?php endforeach; ?>
