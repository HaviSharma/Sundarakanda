<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$SERVICES = ['Hanuman Chalisa Parayanam', 'Sundarakanda Parayanam', 'Graha Santhi', 'Navagraha Santhi', 'Blessings', 'Hanumanth Raksha Kavach', 'Balarista Raksha', 'Gruha Santhi'];

$sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $service = $_POST['service'] ?? '';
    $preferredDate = trim($_POST['preferred_date'] ?? '');
    $details = trim($_POST['details'] ?? '');

    if (!in_array($service, $SERVICES, true)) {
        $errors[] = 'Please choose a service.';
    }

    if (!$errors) {
        $html = '<p>A logged-in donor has requested a service:</p>'
              . '<p><strong>Name:</strong> ' . htmlspecialchars($user['full_name'], ENT_QUOTES) . '<br>'
              . '<strong>Email:</strong> ' . htmlspecialchars($user['email'], ENT_QUOTES) . '<br>'
              . '<strong>Phone:</strong> ' . htmlspecialchars($user['phone'], ENT_QUOTES) . '</p>'
              . '<p><strong>Service requested:</strong> ' . htmlspecialchars($service, ENT_QUOTES) . '<br>'
              . '<strong>Preferred date:</strong> ' . htmlspecialchars($preferredDate ?: 'Not specified', ENT_QUOTES) . '</p>'
              . '<p><strong>Details:</strong><br>' . nl2br(htmlspecialchars($details, ENT_QUOTES)) . '</p>';
        send_mail(ORG_EMAIL, 'Sundarakanda', 'Blessing / Service Request — ' . $service, $html);
        $sent = true;
    }
}

$pageTitle = 'Request a Blessing';
$activeNav = 'blessings';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Personal Service</p>
      <h1>Request a blessing or Parayanam</h1>
      <p>Ask Sundarakanda to perform a personal Parayanam, Santhi, or blessing for you or your family. We'll follow up by email or phone to confirm timing.</p>
    </div>

    <?php if ($sent): ?>
      <div class="form-card narrow">
        <div class="alert alert-success">Your request has been sent. Our team will reach out to <?= e($user['email']) ?> or <?= e($user['phone'] ?: 'your phone on file') ?> to confirm.</div>
        <a class="btn btn-ghost" style="margin-top:20px;width:100%;justify-content:center" href="dashboard.php">Back to Dashboard</a>
      </div>
    <?php else: ?>
      <?php if ($errors): ?>
        <div class="narrow" style="margin-bottom:20px"><div class="alert alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div></div>
      <?php endif; ?>
      <div class="form-card narrow">
        <form method="post">
          <?= csrf_field() ?>
          <div class="field">
            <label for="service">Service <span class="req">*</span></label>
            <select id="service" name="service" required style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:10px;font:inherit;background:var(--cream-100)">
              <option value="">Choose a service&hellip;</option>
              <?php foreach ($SERVICES as $s): ?><option><?= e($s) ?></option><?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label for="preferred_date">Preferred Date</label>
            <input id="preferred_date" name="preferred_date" type="date">
          </div>
          <div class="field">
            <label for="details">Details</label>
            <textarea id="details" name="details" rows="4" placeholder="Names, occasion, or anything else we should know"></textarea>
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Send Request</button>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
