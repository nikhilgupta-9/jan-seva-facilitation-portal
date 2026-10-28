<?php
// config/db.php - PDO Database Connection (credentials from .env)

require_once __DIR__ . '/../utils/bootstrap.php';

$db_host = env('DB_HOST', '127.0.0.1');
$db_port = env('DB_PORT', '3306');
$db_name = env('DB_NAME', 'citizen_portal_db');
$db_user = env('DB_USER', 'root');
$db_pass = env('DB_PASS', '');

try {
    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    $appDebug = env('APP_DEBUG', 'false') === 'true';
    die($appDebug ? "Database Connection Error: " . $e->getMessage() : "Database connection failed. Please try again later.");
}
