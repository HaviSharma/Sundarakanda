<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Donor Portal';
$activeNav = 'home';
$user = current_user();
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="breadcrumb"><a href="../index.html">Main Site</a> <span>/</span> <span>Donor Portal</span></div>
    <p class="kicker" style="color:#f0c987">Your Account</p>
    <h1>Welcome to the Sundarakanda Donor Portal</h1>
    <p class="lead">Give to a cause you believe in, track your giving history, request a personal blessing or Parayanam, and stay close to Sundarakanda's community &mdash; all in one place.</p>
  </div>
</section>

<section>
  <div class="container">
    <?php if ($user): ?>
      <div class="cta-band">
        <div>
          <h2>Welcome back, <?= e(explode(' ', $user['full_name'])[0]) ?></h2>
          <p>Head to your dashboard to see your giving history or make a new donation.</p>
        </div>
        <a class="btn" style="background:#fff;color:var(--maroon-800)" href="dashboard.php">Go to Dashboard</a>
      </div>
    <?php else: ?>
      <div class="grid grid-3">
        <div class="card">
          <div class="icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13M5 12v9h14v-9"/><path d="M12 8c-1.7 0-3-1.1-3-2.5S10.3 3 12 3s3 1.1 3 2.5S13.7 8 12 8Z"/></svg></div>
          <h3>Give with an account</h3>
          <p>Create an account to keep a record of every gift, download receipts, and manage recurring support.</p>
        </div>
        <div class="card">
          <div class="icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg></div>
          <h3>Request a blessing</h3>
          <p>Logged-in donors can request a personal Parayanam, Graha Santhi, or blessing from Sundarakanda.</p>
        </div>
        <div class="card">
          <div class="icon-badge"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
          <h3>Stay in the loop</h3>
          <p>Subscribed donors receive updates about upcoming Parayanam sessions, temple events, and community news.</p>
        </div>
      </div>
      <div class="text-center" style="margin-top:40px;display:flex;gap:14px;justify-content:center;flex-wrap:wrap">
        <a class="btn btn-primary" href="signup.php">Create Your Account</a>
        <a class="btn btn-ghost" href="login.php">Sign In</a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
