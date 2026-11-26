<?php
/**
 * UniWheels Shared Database Connection
 * Single source of truth for MySQL database access across all frontend and admin scripts.
 */

$db_host = 'localhost';
$db_name = 'uniwheels';
$db_user = 'root';
$db_pass = '';

$pdo = null;

try {
    $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    if (isset($suppress_db_die) && $suppress_db_die) {
        $db_error = $e->getMessage();
    } else {
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $e->getMessage()
        ]));
    }
}
