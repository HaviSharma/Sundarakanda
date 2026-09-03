<?php
/**
 * Role-based access. Additive on top of includes/auth.php — donor accounts
 * are unaffected; this only matters for the 'role' column added in
 * migration 002 (default 'participant' for every existing user).
 *
 * The pre-existing single-password admin login (ADMIN_PASSWORD, see
 * includes/auth.php is_admin()/require_admin()) is kept as a break-glass
 * fallback and is always treated as the 'admin' role.
 */
require_once __DIR__ . '/auth.php';

function current_role(): string
{
    if (is_admin()) {
        return 'admin'; // break-glass shared-password login
    }
    $user = current_user();
    return $user['role'] ?? 'participant';
}

function has_role(array $allowed): bool
{
    return in_array(current_role(), $allowed, true);
}

/** Require the current session to be one of $allowed roles, or die/redirect. */
function require_role(array $allowed): void
{
    if (!has_role($allowed)) {
        if (!current_user() && !is_admin()) {
            flash_set('error', 'Please sign in to continue.');
            redirect('login.php');
        }
        http_response_code(403);
        die('You do not have permission to view this page.');
    }
}

/**
 * Write an audit_log row. Call this from every sensitive/admin write path.
 */
function audit(string $action, string $recordType, ?int $recordId, $oldValue, $newValue): void
{
    $performedBy = current_user()['id'] ?? null;
    $old = $oldValue === null ? null : (is_string($oldValue) ? $oldValue : json_encode($oldValue));
    $new = $newValue === null ? null : (is_string($newValue) ? $newValue : json_encode($newValue));
    $stmt = db()->prepare('INSERT INTO audit_log (action, record_type, record_id, old_value, new_value, performed_by) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssissi', $action, $recordType, $recordId, $old, $new, $performedBy);
    $stmt->execute();
    $stmt->close();
}
