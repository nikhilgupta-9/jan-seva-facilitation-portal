<?php
// admin/logout.php - Admin Logout
require_once __DIR__ . '/../utils/session_init.php';
ayushman_start_secure_session();
require_once __DIR__ . '/../config/config.php';

unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_user']);
unset($_SESSION['admin_role']);

session_destroy();
header('Location: ' . BASE_URL . '/admin/login.php');
exit;
