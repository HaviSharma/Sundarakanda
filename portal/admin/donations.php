<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['donor_email'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $purpose = trim($_POST['purpose'] ?? '');
    $method = trim($_POST['payment_method'] ?? '');
    $recurring = isset($_POST['is_recurring']) ? 1 : 0;
    $notes = trim($_POST['notes'] ?? '');

    $stmt = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $donor = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$donor) {
        flash_set('error', 'No donor account found with that email. Ask them to register first, or double-check the address.');
    } elseif ($amount <= 0) {
        flash_set('error', 'Enter an amount greater than zero.');
    } else {
        $receipt = generate_receipt_number();
        $stmt = db()->prepare('INSERT INTO donations (user_id, amount, currency, purpose, payment_method, is_recurring, receipt_number, notes) VALUES (?, ?, "USD", ?, ?, ?, ?, ?)');
        $stmt->bind_param('idssiss', $donor['id'], $amount, $purpose, $method, $recurring, $receipt, $notes);
        $stmt->execute();
        $stmt->close();
        flash_set('success', 'Donation of ' . format_money($amount) . ' recorded (receipt #' . $receipt . ').');
    }
    redirect('donations.php');
}

$donations = db()->query('SELECT d.*, u.full_name, u.email FROM donations d JOIN users u ON u.id = d.user_id ORDER BY d.donated_at DESC LIMIT 200')->fetch_all(MYSQLI_ASSOC);

admin_top('Donations', 'donations');
?>
<h1 style="margin-bottom:28px">Donations</h1>

<div class="grid grid-2" style="align-items:start;margin-bottom:36px">
  <div class="form-card">
    <h2 style="font-size:1.1rem">Record a donation received offline</h2>
    <p class="muted" style="font-size:.85rem">Use this for a check, cash, Zelle, or bank transfer you've received &mdash; the donor must already have an account.</p>
    <form method="post">
      <?= csrf_field() ?>
      <div class="field">
        <label for="donor_email">Donor email <span class="req">*</span></label>
        <input id="donor_email" name="donor_email" type="email" required>
      </div>
      <div class="field-row">
        <div class="field">
          <label for="amount">Amount (USD) <span class="req">*</span></label>
          <input id="amount" name="amount" type="number" step="0.01" min="0.01" required>
        </div>
        <div class="field">
          <label for="payment_method">Method</label>
          <input id="payment_method" name="payment_method" type="text" placeholder="Check, Zelle, Cash...">
        </div>
      </div>
      <div class="field">
        <label for="purpose">Purpose</label>
        <input id="purpose" name="purpose" type="text" placeholder="General Fund">
      </div>
      <div class="field">
        <label class="checkbox-row"><input type="checkbox" name="is_recurring"> Recurring gift</label>
      </div>
      <div class="field">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="2"></textarea>
      </div>
      <button class="btn btn-primary" type="submit" style="width:100%;justify-content:center">Record Donation</button>
    </form>
  </div>

  <div>
    <h2 style="font-size:1.1rem">All donations</h2>
    <div class="table-wrap" style="max-height:520px;overflow-y:auto">
      <table class="data-table">
        <thead><tr><th>Date</th><th>Donor</th><th>Amount</th><th>Purpose</th><th>Receipt</th></tr></thead>
        <tbody>
          <?php foreach ($donations as $d): ?>
            <tr>
              <td><?= e(format_date($d['donated_at'])) ?></td>
              <td><?= e($d['full_name']) ?><br><span class="muted" style="font-size:.8rem"><?= e($d['email']) ?></span></td>
              <td><?= e(format_money((float)$d['amount'], $d['currency'])) ?></td>
              <td><?= e($d['purpose']) ?></td>
              <td><?= e($d['receipt_number']) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$donations): ?><tr><td colspan="5" class="muted">No donations yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php admin_bottom(); ?>
