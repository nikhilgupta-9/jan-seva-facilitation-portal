<?php
// admin/logout.php - Admin Logout
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';

unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_user']);
unset($_SESSION['admin_role']);

session_destroy();
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
