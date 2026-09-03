<?php
/**
 * Small shared helpers: sanitizing output, flash messages, CSRF, tokens.
 */

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function random_token(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

function generate_receipt_number(): string
{
    return 'SK-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
}

/** Flash messages (one-time notices shown after a redirect). */
function flash_set(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flash_all(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/** CSRF token: one per session, checked on every POST. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = random_token(24);
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(400);
        die('Your session expired or this form was submitted incorrectly. Please go back and try again.');
    }
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function format_money(float $amount, string $currency = 'USD'): string
{
    $symbols = ['USD' => '$'];
    $symbol = $symbols[$currency] ?? ($currency . ' ');
    return $symbol . number_format($amount, 2);
}

function format_date(string $datetime): string
{
    $ts = strtotime($datetime);
    return $ts ? date('M j, Y', $ts) : $datetime;
}

/**
 * Constrain a user-supplied redirect target to a page inside the portal.
 *
 * Anything absolute, scheme-relative, or containing a traversal segment is
 * discarded — otherwise ?redirect= is an open redirect that sends a
 * freshly-authenticated visitor to another site.
 */
function safe_redirect_path(?string $target, string $fallback = 'dashboard.php'): string
{
    $target = trim((string)$target);
    if ($target === '') {
        return $fallback;
    }
    // Reject absolute URLs, protocol-relative URLs, and backslash tricks.
    if (preg_match('#^(?:[a-z][a-z0-9+.-]*:|//|\\\\)#i', $target)) {
        return $fallback;
    }
    // Reject anything that climbs out of the portal directory.
    if (str_contains($target, '..') || str_starts_with($target, '/')) {
        return $fallback;
    }
    // Keep only a bare page name plus an optional query string.
    if (!preg_match('#^[A-Za-z0-9_-]+\.php(?:\?[A-Za-z0-9_\-=&%.]*)?$#', $target)) {
        return $fallback;
    }
    return $target;
}

/** The current request path, as a value safe to hand to safe_redirect_path(). */
function current_redirect_target(): string
{
    $page = basename((string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    $query = (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    if ($page === '' || !str_ends_with($page, '.php')) {
        return '';
    }
    return safe_redirect_path($page . ($query !== '' ? '?' . $query : ''), '');
}
