<?php
/** Shared admin chrome. Include admin_top.php after auth check, admin_bottom.php at the end. */
function admin_top(string $title, string $active = ''): void
{
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> | Sundarakanda Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../../assets/css/site.css">
<link rel="stylesheet" href="../../assets/css/portal.css">
</head>
<body class="admin-shell">
<div class="admin-topbar">
  <div class="container">
    <a href="dashboard.php" style="color:#fff;font-family:var(--font-display);font-size:1.15rem">Sundarakanda Admin</a>
    <div style="display:flex;gap:18px;align-items:center">
      <a href="dashboard.php" style="color:<?= $active === 'dashboard' ? 'var(--gold-300)' : '#f0e2d3' ?>">Overview</a>
      <a href="devotionals.php" style="color:<?= $active === 'devotionals' ? 'var(--gold-300)' : '#f0e2d3' ?>">Devotionals</a>
      <a href="donations.php" style="color:<?= $active === 'donations' ? 'var(--gold-300)' : '#f0e2d3' ?>">Donations</a>
      <a href="events.php" style="color:<?= $active === 'events' ? 'var(--gold-300)' : '#f0e2d3' ?>">Events</a>
      <a href="announcement.php" style="color:<?= $active === 'announcement' ? 'var(--gold-300)' : '#f0e2d3' ?>">Send Announcement</a>
      <a class="btn btn-outline btn-sm" href="logout.php">Sign Out</a>
    </div>
  </div>
</div>
<main style="padding:40px 0 80px">
  <div class="container">
    <?php foreach (flash_all() as $flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?>" style="margin-bottom:24px"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
    <?php
}

function admin_bottom(): void
{
    ?>
  </div>
</main>
</body>
</html>
    <?php
}
