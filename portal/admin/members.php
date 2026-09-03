<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_role(['admin', 'operations_manager']);

// --- Export (CSV) -----------------------------------------------------
if (($_GET['export'] ?? '') === 'csv') {
    $rows = db()->query('SELECT id, full_name, email, phone, whatsapp_number, city, is_active, created_at FROM members ORDER BY created_at DESC')->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sundarakanda-members.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'Name', 'Email', 'Phone', 'WhatsApp', 'City', 'Active', 'Joined']);
    foreach ($rows as $r) {
        fputcsv($out, [$r['id'], $r['full_name'], $r['email'], $r['phone'], $r['whatsapp_number'], $r['city'], $r['is_active'] ? 'Yes' : 'No', $r['created_at']]);
    }
    fclose($out);
    exit;
}

// --- Toggle active/inactive ---------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_active') {
    csrf_check();
    require_role(['admin', 'operations_manager']);
    $memberId = (int)($_POST['member_id'] ?? 0);
    $stmt = db()->prepare('SELECT is_active FROM members WHERE id = ?');
    $stmt->bind_param('i', $memberId);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($old) {
        $newVal = $old['is_active'] ? 0 : 1;
        $upd = db()->prepare('UPDATE members SET is_active = ? WHERE id = ?');
        $upd->bind_param('ii', $newVal, $memberId);
        $upd->execute();
        $upd->close();
        audit('update_status', 'member', $memberId, $old['is_active'], $newVal);
        flash_set('success', 'Member #' . $memberId . ' updated.');
    }
    redirect('members.php');
}

$totalMembers = db()->query('SELECT COUNT(*) c FROM members WHERE is_active = 1')->fetch_assoc()['c'];
$totalInactive = db()->query('SELECT COUNT(*) c FROM members WHERE is_active = 0')->fetch_assoc()['c'];
$thisMonth = db()->query("SELECT COUNT(*) c FROM members WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')")->fetch_assoc()['c'];
$members = db()->query('SELECT id, full_name, email, phone, whatsapp_number, city, is_active, created_at FROM members ORDER BY created_at DESC LIMIT 200')->fetch_all(MYSQLI_ASSOC);

admin_top('Members', '');
?>
<h1 style="margin-bottom:6px">Members</h1>
<p class="muted" style="margin-bottom:28px">Free community members who registered for email updates on events and Parayanam activities.</p>

<div class="stat-tiles">
  <div class="stat-tile"><b><?= number_format($totalMembers) ?></b><span>Active members</span></div>
  <div class="stat-tile"><b><?= number_format($thisMonth) ?></b><span>Joined this month</span></div>
  <div class="stat-tile"><b><?= number_format($totalInactive) ?></b><span>Unsubscribed</span></div>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin:28px 0 12px">
  <h2 style="font-size:1.1rem;margin:0">All Members</h2>
  <a class="btn btn-ghost btn-sm" href="members.php?export=csv">Export CSV</a>
</div>
<div class="table-wrap" style="max-height:600px;overflow-y:auto">
  <table class="data-table">
    <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>City</th><th>Joined</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($members as $m): ?>
        <tr>
          <td><?= e($m['full_name']) ?></td>
          <td><?= e($m['email']) ?></td>
          <td><?= e($m['phone'] ?: '&mdash;') ?></td>
          <td><?= e($m['city'] ?: '&mdash;') ?></td>
          <td><?= e(format_date($m['created_at'])) ?></td>
          <td><span class="pill <?= $m['is_active'] ? 'pill-active' : 'pill-inactive' ?>"><?= $m['is_active'] ? 'Active' : 'Unsubscribed' ?></span></td>
          <td>
            <form method="post" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle_active">
              <input type="hidden" name="member_id" value="<?= (int)$m['id'] ?>">
              <button class="btn btn-ghost btn-sm" type="submit"><?= $m['is_active'] ? 'Deactivate' : 'Reactivate' ?></button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$members): ?><tr><td colspan="7" class="muted">No members yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php admin_bottom(); ?>
