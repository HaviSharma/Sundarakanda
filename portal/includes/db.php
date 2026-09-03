<?php
/**
 * Database connection (mysqli, prepared statements throughout the app).
 */
require_once __DIR__ . '/../config.php';

function db(): mysqli
{
    static $conn = null;
    if ($conn !== null) {
        return $conn;
    }
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->set_charset('utf8mb4');
    } catch (mysqli_sql_exception $e) {
        if (APP_DEBUG) {
            die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
        die('We\'re having trouble connecting right now. Please try again shortly.');
    }
    return $conn;
}
