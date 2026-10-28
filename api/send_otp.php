<?php
// api/send_otp.php - Sends 6-digit OTP to citizen phone number
header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
$name = trim($_POST['name'] ?? 'Citizen');
$email = trim($_POST['email'] ?? '');

if (strlen($phone) !== 10) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit mobile number']);
    exit;
}

// Generate 6-digit random OTP
$otp = (string) mt_rand(100000, 999999);
$expires_at = date('Y-m-d H:i:s', strtotime('+' . OTP_EXPIRY_MINUTES . ' minutes'));

// Upsert into users table
try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user) {
        $user_id = $user['id'];
        $update = $pdo->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ?, name = COALESCE(NULLIF(?, ''), name), email = COALESCE(NULLIF(?, ''), email) WHERE id = ?");
        $update->execute([$otp, $expires_at, $name, $email, $user_id]);
    } else {
        $insert = $pdo->prepare("INSERT INTO users (name, phone, email, otp_code, otp_expires_at, is_verified) VALUES (?, ?, ?, ?, ?, 0)");
        $insert->execute([$name, $phone, $email, $otp, $expires_at]);
        $user_id = $pdo->lastInsertId();
    }

    $_SESSION['temp_phone'] = $phone;
    $_SESSION['temp_otp'] = $otp;
    $_SESSION['temp_user_id'] = $user_id;

    // In a live production environment, dispatch SMS via Fast2SMS / MSG91 API:
    // file_get_contents("https://www.fast2sms.com/dev/bulkV2?authorization=".SMS_GATEWAY_API_KEY."&variables_values=".$otp."&route=otp&numbers=".$phone);

    echo json_encode([
        'success' => true,
        'message' => "OTP sent successfully to +91-XXXXXX" . substr($phone, -4),
        'demo_otp' => $otp, // Included for instant sandbox testing
        'test_mode' => OTP_TEST_MODE
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
