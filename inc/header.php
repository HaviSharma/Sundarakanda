<?php
/**
 * Shared public-site chrome. One header for every page — set $pageTitle,
 * $pageDesc, and $activeNav before including.
 *
 * The portal bootstrap gives us the session (so the header can greet a
 * signed-in member) and the event helpers (for the homepage pipeline).
 */
require_once __DIR__ . '/../portal/includes/bootstrap.php';

$pageTitle = $pageTitle ?? 'Sundarakanda';
$pageDesc  = $pageDesc  ?? 'Sundarakanda USA — MS Rama Rao Memorial Foundation. Telugu Hanuman Chalisa and Sundarakanda Parayanam, community service, and devotional heritage.';
$activeNav = $activeNav ?? '';
$siteUser  = current_user();

/** class="active" for the current nav item. */
function nav_active(string $key, string $current): string
{
    return $key === $current ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> | Sundarakanda</title>
<meta name="description" content="<?= e($pageDesc) ?>">
<link rel="icon" href="img/favicon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&family=Playfair+Display:wght@600;700&family=Noto+Sans+Telugu:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/site.css">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>

<div class="topbar">
  <div class="container">
    <div class="topbar-links">
      <a href="tel:+15108772424"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.12.9.34 1.77.65 2.6a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.48-1.22a2 2 0 0 1 2.11-.45c.83.31 1.7.53 2.6.65A2 2 0 0 1 22 16.92z"/></svg> <span class="tl-full">+1 (510) 877-2424</span></a>
      <a href="mailto:info@sundarakanda.com"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M22 6c0-1.1-.9-2-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6Z"/><path d="m2 7 10 6 10-6"/></svg> <span class="tl-full">info@sundarakanda.com</span></a>
      <a href="contact.php"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg> <span class="tl-full">4425 Bidwell Dr #4104, Fremont, CA 94538, USA</span></a>
    </div>
    <div class="topbar-social">
      <a href="https://www.youtube.com/@Jhoney786" aria-label="Subscribe on YouTube" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M23 12s0-3.6-.5-5.3a3 3 0 0 0-2.1-2.1C18.7 4 12 4 12 4s-6.7 0-8.4.6A3 3 0 0 0 1.5 6.7C1 8.4 1 12 1 12s0 3.6.5 5.3a3 3 0 0 0 2.1 2.1C5.3 20 12 20 12 20s6.7 0 8.4-.6a3 3 0 0 0 2.1-2.1C23 15.6 23 12 23 12Z"/><path d="M10 15.5v-7l6 3.5-6 3.5Z" fill="#fff"/></svg></a>
      <a href="https://www.facebook.com/" aria-label="Facebook" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path d="M13.5 22v-8.5H16l.5-3.5h-3V7.7c0-1 .3-1.7 1.8-1.7H16V2.8C15.6 2.8 14.5 2.7 13.3 2.7c-2.6 0-4.4 1.6-4.4 4.5V10H6v3.5h2.9V22h4.6Z"/></svg></a>
    </div>
  </div>
</div>

<header class="site-header">
  <div class="container nav-row">
    <a class="brand" href="index.php">
      <img src="img/logo_n_2.png" alt="Sundarakanda logo">
    </a>
    <nav class="main-nav" aria-label="Primary">
      <button class="nav-close" aria-label="Close menu"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
      <ul>
        <li<?= nav_active('home', $activeNav) ?>><a href="index.php" class="nav-link">Home</a></li>
        <li class="has-dropdown<?= $activeNav === 'about' ? ' active' : '' ?>">
          <a href="msramarao.php" class="nav-link">About <svg class="chev" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></a>
          <ul class="dropdown">
            <li><a href="msramarao.php">Shri M.S. Rama Rao</a></li>
            <li><a href="janardhana.php">Janardhana Polapragada</a></li>
            <li><a href="aluri.php">Aluri</a></li>
          </ul>
        </li>
        <li<?= nav_active('services', $activeNav) ?>><a href="parayanam.php" class="nav-link">Services</a></li>
        <li class="has-dropdown<?= $activeNav === 'gallery' ? ' active' : '' ?>">
          <a href="videogallery.php" class="nav-link">Gallery <svg class="chev" style="width:14px;height:14px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></a>
          <ul class="dropdown">
            <li><a href="videogallery.php">Video Gallery</a></li>
            <li><a href="photogallery.php">Photo Gallery</a></li>
          </ul>
        </li>
        <li<?= nav_active('events', $activeNav) ?>><a href="portal/events.php" class="nav-link">Events</a></li>
        <li<?= nav_active('downloads', $activeNav) ?>><a href="downloads.php" class="nav-link">Downloads</a></li>
        <li<?= nav_active('contact', $activeNav) ?>><a href="contact.php" class="nav-link">Contact</a></li>
        <?php if (!$siteUser): ?>
          <li class="nav-drawer-only"><a href="portal/signup.php" class="nav-link">Become a Member</a></li>
        <?php else: ?>
          <li class="nav-drawer-only"><a href="portal/dashboard.php" class="nav-link">My Dashboard</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <div class="nav-cta">
      <?php if ($siteUser): ?>
        <a class="btn btn-ghost btn-sm" href="portal/dashboard.php"><span><?= e(explode(' ', $siteUser['full_name'])[0]) ?></span></a>
        <a class="btn btn-primary btn-sm" href="portal/logout.php"><span>Sign Out</span></a>
      <?php else: ?>
        <a class="btn btn-ghost btn-sm" href="portal/signup.php"><span>Become a Member</span></a>
        <a class="btn btn-primary btn-sm" href="portal/login.php"><span>Login</span></a>
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
