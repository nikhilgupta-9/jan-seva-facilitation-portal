<?php
// admin/auth.php - Session Authentication Guard
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

$admin_user = $_SESSION['admin_user'] ?? 'Administrator';
