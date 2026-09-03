<?php
/**
 * Included at the top of every public-facing page: starts a hardened
 * session and pulls in the shared helpers.
 */
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
    $secure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/rbac.php';
require_once __DIR__ . '/campaign.php';
require_once __DIR__ . '/events.php';
require_once __DIR__ . '/mailer.php';
