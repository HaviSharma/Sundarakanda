<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$PURPOSES = ['General Fund', 'Sundarakanda Parayanam', 'Hanuman Chalisa Parayanam', 'Graha Santhi / Navagraha Santhi', 'Laptop / Student Support Program', 'Temple Event Sponsorship'];
$METHODS = [
    'check' => ['label' => 'Check by mail', 'detail' => 'Mail a check payable to Sundarakanda to our address below.'],
    'zelle' => ['label' => 'Zelle', 'detail' => 'Send via Zelle to ' . ORG_EMAIL . '.'],
    'bank_transfer' => ['label' => 'Bank transfer', 'detail' => 'Contact us for our bank routing details.'],
    'cash_in_person' => ['label' => 'Cash / in person', 'detail' => 'Bring your gift to any Sundarakanda gathering or Parayanam session.'],
];

$errors = [];
$receipt = null;
$chosenMethod = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $amount = (float)($_POST['amount'] ?? 0);
    $purpose = trim($_POST['purpose'] ?? '');
    $method = $_POST['payment_method'] ?? '';
    $recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $notes = trim($_POST['notes'] ?? '');

    if ($amount <= 0) {
        $errors[] = 'Please enter a donation amount greater than zero.';
    }
    if (!isset($METHODS[$method])) {
        $errors[] = 'Please choose how you plan to send your gift.';
    }

    if (!$errors) {
        $receiptNumber = generate_receipt_number();
        $fullNotes = '[Pledged — awaiting confirmation by staff] ' . $notes;
        $stmt = db()->prepare('INSERT INTO donations (user_id, amount, currency, purpose, payment_method, is_recurring, receipt_number, notes) VALUES (?, ?, "USD", ?, ?, ?, ?, ?)');
        $methodLabel = $METHODS[$method]['label'];
        $stmt->bind_param('idssiss', $user['id'], $amount, $purpose, $methodLabel, $recurring, $receiptNumber, $fullNotes);
        $stmt->execute();
        $stmt->close();

        $receipt = $receiptNumber;
        $chosenMethod = $method;
    }
}

$pageTitle = 'Donate';
$activeNav = 'donate';
include __DIR__ . '/includes/header.php';
?>
<section class="tight">
  <div class="container">
    <div class="section-head center">
      <p class="kicker" style="justify-content:center">Support Sundarakanda</p>
      <h1>Make a donation</h1>
      <p>Sundarakanda is entirely community-supported. Record your gift here, then send it using the method you choose &mdash; our team will confirm receipt.</p>
    </div>

    <?php if ($receipt): ?>
      <div class="form-card medium">
        <div class="alert alert-success" style="margin-bottom:20px">Thank you! Your pledge has been recorded (receipt #<?= e($receipt) ?>).</div>
        <div class="instructions-box">
          <h4>Next step &mdash; send your gift via <?= e($METHODS[$chosenMethod]['label']) ?></h4>
          <p><?= e($METHODS[$chosenMethod]['detail']) ?></p>
          <p>Mailing address: <strong>Sundarakanda, 4425 Bidwell Dr #4104, Fremont, CA 94538, USA</strong></p>
          <p class="muted" style="margin-top:14px;margin-bottom:0">Once received, our team will confirm your gift and it will remain in your donation history. Questions? Reach us at <?= e(ORG_EMAIL) ?> or <?= e(ORG_PHONE) ?>.</p>
        </div>
        <div style="display:flex;gap:12px;margin-top:24px">
          <a class="btn btn-primary" href="dashboard.php">View My Donations</a>
          <a class="btn btn-ghost" href="donate.php">Make Another Pledge</a>
        </div>
      </div>
    <?php else: ?>
      <?php if ($errors): ?>
        <div class="medium" style="margin-bottom:20px"><div class="alert alert-error"><?= implode('<br>', array_map('e', $errors)) ?></div></div>
      <?php endif; ?>

      <div class="form-card medium">
        <form method="post">
          <?= csrf_field() ?>
          <div class="field">
            <label for="amount">Amount (USD) <span class="req">*</span></label>
            <input id="amount" name="amount" type="number" min="1" step="0.01" required placeholder="108.00" value="<?= e($_POST['amount'] ?? '') ?>">
          </div>
          <div class="field">
            <label for="purpose">Purpose</label>
            <select id="purpose" name="purpose" style="width:100%;padding:12px 14px;border:1.5px solid var(--line);border-radius:10px;font:inherit;background:var(--cream-100)">
              <?php foreach ($PURPOSES as $p): ?>
                <option <?= (($_POST['purpose'] ?? '') === $p) ? 'selected' : '' ?>><?= e($p) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field">
            <label>How will you send your gift? <span class="req">*</span></label>
            <div class="method-grid">
              <?php foreach ($METHODS as $key => $m): ?>
                <label class="method-option">
                  <input type="radio" name="payment_method" value="<?= e($key) ?>" <?= (($_POST['payment_method'] ?? '') === $key) ? 'checked' : '' ?> required>
                  <span><b><?= e($m['label']) ?></b><span><?= e($m['detail']) ?></span></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
          <div class="field">
            <label class="checkbox-row" style="font-weight:600;color:var(--ink-700)">
              <input type="checkbox" name="is_recurring" <?= isset($_POST['is_recurring']) ? 'checked' : '' ?>>
              This is a recurring gift
            </label>
          </div>
          <div class="field">
            <label for="notes">Notes (optional)</label>
            <textarea id="notes" name="notes" rows="3" placeholder="In memory of... / dedication / anything we should know"><?= e($_POST['notes'] ?? '') ?></textarea>
          </div>
          <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Record My Pledge</button>
          <p class="muted" style="text-align:center;margin-top:14px;font-size:.82rem">This records your intent to give &mdash; no card details are collected here. You'll get instructions to send your gift on the next screen.</p>
        </form>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
