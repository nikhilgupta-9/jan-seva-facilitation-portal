<?php
// api/verify_otp.php - Verifies 6-digit OTP
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
$otp = trim($_POST['otp'] ?? '');

if (strlen($phone) !== 10 || strlen($otp) < 4) {
    echo json_encode(['success' => false, 'message' => 'Please provide phone and OTP']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found. Please request OTP again.']);
        exit;
    }

    $is_valid = false;
    // Allow master test OTP if enabled or match generated code before expiry
    if (OTP_TEST_MODE && $otp === '123456') {
        $is_valid = true;
    } elseif ($user['otp_code'] === $otp && strtotime($user['otp_expires_at']) >= time()) {
        $is_valid = true;
    }

    if ($is_valid) {
        $update = $pdo->prepare("UPDATE users SET is_verified = 1, otp_code = NULL WHERE id = ?");
        $update->execute([$user['id']]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_phone'] = $user['phone'];
        $_SESSION['user_name'] = $user['name'];

        echo json_encode([
            'success' => true,
            'message' => 'Mobile number verified successfully!',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'phone' => $user['phone']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP. Please try again.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
