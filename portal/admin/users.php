<?php
/**
 * User role management — admin-only (not operations_manager). Granting
 * admin/operations_manager access is more sensitive than the day-to-day
 * work those roles do, so the bar to change a role is higher than the
 * bar to use one.
 */
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/includes/admin_layout.php';
require_role(['admin']);

const USER_ROLES = ['participant', 'operations_manager', 'admin'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_role') {
    csrf_check();
    require_role(['admin']);

    $userId = (int)($_POST['user_id'] ?? 0);
    $newRole = $_POST['role'] ?? '';
    $actor = current_user();

    if (!in_array($newRole, USER_ROLES, true)) {
        flash_set('error', 'Choose a valid role.');
        redirect('users.php');
    }
    if ($actor && $userId === (int)$actor['id']) {
        flash_set('error', 'You cannot change your own role — have another admin do it.');
        redirect('users.php');
    }

    $stmt = db()->prepare('SELECT id, full_name, role FROM users WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $target = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$target) {
        flash_set('error', 'That user no longer exists.');
        redirect('users.php');
    }

    $upd = db()->prepare('UPDATE users SET role = ? WHERE id = ?');
    $upd->bind_param('si', $newRole, $userId);
    $upd->execute();
    $upd->close();

    audit('update_role', 'user', $userId, $target['role'], $newRole);
    flash_set('success', $target['full_name'] . '\'s role is now ' . $newRole . '.');
    redirect('users.php');
}

$stats = db()->query(
    "SELECT
        COUNT(*) total,
        SUM(role = 'admin') admins,
        SUM(role = 'operations_manager') ops
     FROM users"
)->fetch_assoc();

$users = db()->query('SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 500')->fetch_all(MYSQLI_ASSOC);

admin_top('Users', 'users');
?>
<h1 style="margin-bottom:6px">Users</h1>
<p class="muted" style="margin-bottom:28px">Every registered account, and the role it holds. Roles control what a signed-in account can do in this admin area.</p>

<div class="stat-tiles">
  <div class="stat-tile"><b><?= (int)$stats['total'] ?></b><span>Total accounts</span></div>
  <div class="stat-tile"><b><?= (int)$stats['admins'] ?></b><span>Admins</span></div>
  <div class="stat-tile"><b><?= (int)$stats['ops'] ?></b><span>Operations managers</span></div>
</div>

<h2 style="font-size:1.1rem;margin:28px 0 12px">All Users</h2>
<div class="table-wrap" style="max-height:600px;overflow-y:auto">
  <table class="data-table">
    <thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Role</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= e($u['full_name']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td><?= e(format_date($u['created_at'])) ?></td>
          <td><span class="pill <?= $u['role'] === 'admin' ? 'pill-active' : ($u['role'] === 'operations_manager' ? 'pill-recurring' : '') ?>"><?= e(ucwords(str_replace('_', ' ', $u['role']))) ?></span></td>
          <td>
            <form method="post" style="display:flex;gap:8px;align-items:center">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update_role">
              <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
              <select name="role" style="font-size:.85rem;padding:6px 8px;border-radius:8px;border:1px solid var(--line)">
                <?php foreach (USER_ROLES as $r): ?>
                  <option value="<?= e($r) ?>" <?= $u['role'] === $r ? 'selected' : '' ?>><?= e(ucwords(str_replace('_', ' ', $r))) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-ghost btn-sm" type="submit">Save</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$users): ?><tr><td colspan="5" class="muted">No accounts yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php admin_bottom(); ?>
