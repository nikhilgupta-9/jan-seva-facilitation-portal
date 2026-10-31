<?php
// admin/auth.php - Session Authentication Guard
require_once __DIR__ . '/../utils/session_init.php';
ayushman_start_secure_session();

require_once __DIR__ . '/../config/config.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . BASE_URL . '/admin/login.php');
    exit;
}

// Idle-session timeout: 30 minutes of inactivity logs the admin out, so a
// forgotten/unattended logged-in browser tab doesn't stay a standing risk.
$adminIdleLimit = 30 * 60;
if (!empty($_SESSION['admin_last_activity']) && (time() - $_SESSION['admin_last_activity']) > $adminIdleLimit) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . BASE_URL . '/admin/login.php?expired=1');
    exit;
}
$_SESSION['admin_last_activity'] = time();

$admin_user = $_SESSION['admin_user'] ?? 'Administrator';
