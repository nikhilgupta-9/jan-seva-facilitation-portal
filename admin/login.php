<?php
// admin/login.php - Facilitator Admin Login
require_once __DIR__ . '/../utils/session_init.php';
ayushman_start_secure_session();
require_once __DIR__ . '/../config/config.php';

$error = '';

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

// Basic brute-force throttle: lock out after 5 failed attempts within 10
// minutes (per session/browser). Not a substitute for IP-level protection,
// but stops naive automated password guessing against this form.
$maxAttempts = 5;
$lockoutWindow = 10 * 60;
$now = time();
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = [];
}
$_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function ($t) use ($now, $lockoutWindow) {
    return ($now - $t) < $lockoutWindow;
});
$isLockedOut = count($_SESSION['login_attempts']) >= $maxAttempts;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isLockedOut) {
        $error = 'Too many failed attempts. Please try again in a few minutes.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (!empty($username) && !empty($password)) {
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // Password must match the stored hash — no fallback/default
            // passwords. The previous bypass here let anyone log into ANY
            // existing admin account with a guessable password regardless
            // of what that account's real password actually was.
            $valid = $user && password_verify($password, $user['password_hash']);

            if ($valid) {
                $_SESSION['login_attempts'] = [];
                // Regenerate the session ID on privilege change to prevent session fixation.
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_user'] = $user['name'] ?? $user['username'];
                $_SESSION['admin_role'] = $user['role'] ?? 'admin';
                header('Location: ' . BASE_URL . '/admin/index.php');
                exit;
            } else {
                $_SESSION['login_attempts'][] = $now;
                $error = 'Invalid administrative credentials. Please try again.';
            }
        } else {
            $error = 'Please enter both username and password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | <?php echo APP_NAME; ?></title>
<link href="<?php echo BASE_URL; ?>/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
<link href="<?php echo BASE_URL; ?>/css/gigw-theme.css" rel="stylesheet">
<style>
  body {
    background: linear-gradient(135deg, var(--gov-navy) 0%, var(--gov-navy-dark) 55%, var(--gov-navy-deep) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    padding: 20px;
  }
  .login-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 40px 32px;
    max-width: 440px;
    width: 100%;
    box-shadow: 0 20px 45px rgba(3, 20, 34, 0.45);
    position: relative;
    overflow: hidden;
  }
  .login-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #FF9933 33.3%, #ffffff 33.3%, #ffffff 66.6%, #138808 66.6%);
  }
  .login-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--gov-navy) 0%, var(--gov-navy-dark) 100%);
    color: var(--gov-gold);
    font-size: 1.8rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 14px;
  }
  .login-card .form-control {
    border-radius: 8px;
    padding: 10px 14px;
    border: 1px solid var(--gov-border);
  }
  .login-card .form-control:focus {
    border-color: var(--gov-saffron);
    box-shadow: 0 0 0 0.2rem rgba(243, 112, 33, 0.18);
  }
  .login-card label {
    font-weight: 600;
    color: var(--gov-text);
    font-size: 0.92rem;
  }
</style>
</head>
<body>

<div class="login-card">
  <div class="text-center mb-4">
    <div class="login-icon">
      <i class="fas fa-user-shield"></i>
    </div>
    <h3 class="fw-bold mb-0" style="color: var(--gov-navy); font-size: 1.35rem;">Admin Control Center</h3>
    <small class="text-muted"><?php echo htmlspecialchars(APP_NAME); ?> Facilitation Console</small>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger py-2" style="font-size: 0.88rem;"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="mb-3">
      <label for="username" class="form-label">Administrative Username</label>
      <div class="input-group">
        <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
        <input type="text" id="username" name="username" class="form-control" placeholder="admin" value="admin" required autofocus>
      </div>
    </div>

    <div class="mb-2">
      <label for="password" class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" value="admin123" required>
      </div>
      <small class="text-muted">Default demo credentials: <strong>admin</strong> / <strong>admin123</strong></small>
    </div>

    <div class="mt-4">
      <button type="submit" class="btn btn-gov-saffron w-100 py-2 fw-bold">
        <i class="fas fa-sign-in-alt"></i> Access Dashboard
      </button>
    </div>
  </form>

  <div class="text-center mt-3 pt-3" style="border-top: 1px solid var(--gov-border);">
    <a href="<?php echo BASE_URL; ?>/index.php" class="text-decoration-none" style="color: var(--gov-navy); font-size: 0.88rem;">
      <i class="fas fa-arrow-left"></i> Return to Citizen Portal
    </a>
  </div>
</div>

</body>
</html>
