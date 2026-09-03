<?php
/**
 * Donor session/auth helpers. Session cookies are set with secure flags in
 * bootstrap.php before this is used.
 */
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $stmt = db()->prepare('SELECT id, full_name, email, phone, address, email_subscribed, email_verified, role, created_at FROM users WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$result) {
        unset($_SESSION['user_id']);
        return null;
    }
    $cached = $result;
    return $cached;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash_set('error', 'Please sign in to continue.');
        // Carry the page they asked for so login can return them to it.
        $target = current_redirect_target();
        redirect('login.php' . ($target !== '' ? '?redirect=' . urlencode($target) : ''));
    }
    return $user;
}

function login_user(int $userId): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

/** --- Admin (single shared password, per site owner's choice) --- */
function is_admin(): bool
{
    return !empty($_SESSION['is_admin']);
}

function require_admin(): void
{
    if (!is_admin()) {
        redirect('login.php');
    }
}
