<?php
/**
 * Sundarakanda Donor Portal — configuration
 * -----------------------------------------
 * Fill in your real values below, then upload this whole "portal" folder
 * to your cPanel host (e.g. as /portal on sundarakanda.com).
 *
 * IMPORTANT: after uploading, make sure this file is NOT publicly
 * downloadable as plain text — cPanel/Apache with the included .htaccess
 * already blocks direct access to .php source, but always confirm.
 */

// A local, untracked override (config.local.php) wins over everything
// below — that is how the dev database is pointed at without editing the
// production values in this file. It is absent on the live host.
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// ---- Database (cPanel MySQL) -------------------------------------------
// Database name, per your setup: donors
defined('DB_HOST') || define('DB_HOST', 'localhost');
defined('DB_NAME') || define('DB_NAME', 'donors');
defined('DB_USER') || define('DB_USER', 'donors_app');      // the MySQL user you created
defined('DB_PASS') || define('DB_PASS', 'REPLACE_WITH_YOUR_DB_PASSWORD');

// ---- Site -----------------------------------------------------------------
defined('SITE_NAME') || define('SITE_NAME', 'Sundarakanda');
defined('SITE_URL') || define('SITE_URL', 'https://www.sundarakanda.com/portal'); // no trailing slash
defined('ORG_EMAIL') || define('ORG_EMAIL', 'info@sundarakanda.com');
defined('ORG_PHONE') || define('ORG_PHONE', '+1 (510) 877-2424');

// ---- Admin ------------------------------------------------------------
// Single shared password for /admin — change this before going live.
defined('ADMIN_PASSWORD') || define('ADMIN_PASSWORD', 'REPLACE_WITH_A_STRONG_ADMIN_PASSWORD');

// ---- Outgoing mail -----------------------------------------------------
// Used for the daily devotional cron and admin announcements.
// cPanel's built-in mail() usually works out of the box once the site is
// live on the host; if your host requires SMTP instead, ask your host for
// SMTP credentials and adapt includes/mailer.php accordingly.
defined('MAIL_FROM_ADDRESS') || define('MAIL_FROM_ADDRESS', 'info@sundarakanda.com');
defined('MAIL_FROM_NAME') || define('MAIL_FROM_NAME', 'Sundarakanda');

// ---- Security -----------------------------------------------------------
// Random long string used to sign session cookies / tokens. Generate your
// own (e.g. `php -r "echo bin2hex(random_bytes(32));"`) before going live.
defined('APP_SECRET') || define('APP_SECRET', 'REPLACE_WITH_A_RANDOM_64_CHAR_STRING');

// Set to true only while diagnosing a problem — never leave on in production.
defined('APP_DEBUG') || define('APP_DEBUG', false);

date_default_timezone_set('America/Los_Angeles');
