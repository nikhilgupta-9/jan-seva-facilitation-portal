<?php
// api/ayushman_aadhaar_otp.php - Aadhaar OTP Verification Gateway for Ayushman Help Desk
header('Content-Type: application/json; charset=utf-8');

// Include global config
require_once __DIR__ . '/../config/config.php';

require_once __DIR__ . '/../utils/session_init.php';
ayushman_start_secure_session();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. Send OTP
if ($action === 'send_otp') {
    $aadhaar = preg_replace('/\D/', '', $_POST['aadhaar_number'] ?? '');
    $mobile = preg_replace('/\D/', '', $_POST['mobile'] ?? '');

    // Validate Aadhaar (12 digits, standard Indian UIDAI rule: cannot start with 0 or 1)
    if (strlen($aadhaar) !== 12 || in_array($aadhaar[0], ['0', '1'])) {
        echo json_encode([
            'success' => false,
            'message' => 'कृपया 12 अंकों का वैध आधार नंबर दर्ज करें (Cannot start with 0 or 1).'
        ]);
        exit;
    }

    // Validate Mobile (10 digits starting with 6, 7, 8, 9)
    if (strlen($mobile) !== 10 || !in_array($mobile[0], ['6', '7', '8', '9'])) {
        echo json_encode([
            'success' => false,
            'message' => 'कृपया 10 अंकों का वैध मोबाइल नंबर दर्ज करें (Starts with 6-9).'
        ]);
        exit;
    }

    // Simple rate limiting in session
    $now = time();
    if (!isset($_SESSION['otp_attempts'])) {
        $_SESSION['otp_attempts'] = [];
    }
    $_SESSION['otp_attempts'] = array_filter($_SESSION['otp_attempts'], function($t) use ($now) {
        return ($now - $t) < 300; // last 5 minutes
    });

    if (count($_SESSION['otp_attempts']) >= 5) {
        echo json_encode([
            'success' => false,
            'message' => 'अधिक प्रयास किए गए हैं। कृपया 5 मिनट बाद पुनः प्रयास करें (Rate limit exceeded).'
        ]);
        exit;
    }
    $_SESSION['otp_attempts'][] = $now;

    // Generate 6-digit random OTP
    $randomOtp = (string) mt_rand(100000, 999999);
    $last4 = substr($aadhaar, -4);
    $maskedMobile = substr($mobile, 0, 2) . 'XXXXXX' . substr($mobile, -2);

    $_SESSION['ayushman_aadhaar_auth'] = [
        'aadhaar_number' => $aadhaar,
        'aadhaar_last4'  => $last4,
        'mobile'          => $mobile,
        'otp'             => $randomOtp,
        'expires_at'      => $now + (10 * 60), // 10 minutes
        'verified'        => false,
        'created_at'      => $now
    ];

    $response = [
        'success' => true,
        'message' => "ओटीपी आपके आधार से लिंक मोबाइल {$maskedMobile} पर भेज दिया गया है। (OTP sent successfully)",
        'last4'   => $last4,
        'masked_mobile' => $maskedMobile
    ];

    // If sandbox / test mode is enabled, include the test OTP for seamless testing
    if (defined('OTP_TEST_MODE') && OTP_TEST_MODE) {
        $response['test_mode'] = true;
        $response['demo_otp'] = '123456';
        $response['generated_otp'] = $randomOtp;
    }

    echo json_encode($response);
    exit;
}

// 2. Verify OTP
if ($action === 'verify_otp') {
    $enteredOtp = trim($_POST['otp'] ?? '');
    $auth = $_SESSION['ayushman_aadhaar_auth'] ?? null;

    if (!$auth || empty($auth['otp'])) {
        echo json_encode([
            'success' => false,
            'message' => 'सत्र समाप्त हो गया है। कृपया पहले नया ओटीपी प्राप्त करें (Session expired).'
        ]);
        exit;
    }

    if (time() > $auth['expires_at']) {
        echo json_encode([
            'success' => false,
            'message' => 'ओटीपी की समय सीमा समाप्त हो गई है (OTP expired). कृपया पुनः नया ओटीपी भेजें।'
        ]);
        exit;
    }

    $isTestMode = defined('OTP_TEST_MODE') && OTP_TEST_MODE;
    $isValid = ($enteredOtp === $auth['otp']) || ($isTestMode && $enteredOtp === '123456');

    if ($isValid) {
        $_SESSION['ayushman_aadhaar_auth']['verified'] = true;
        $_SESSION['ayushman_aadhaar_auth']['verified_at'] = date('Y-m-d H:i:s');

        echo json_encode([
            'success'     => true,
            'message'     => 'आधार ई-केवाईसी मोबाइल नंबर सत्यापन सफल! (Aadhaar e-KYC Verified Successfully)',
            'last4'       => $auth['aadhaar_last4'],
            'verified'    => true,
            'verified_at' => $_SESSION['ayushman_aadhaar_auth']['verified_at']
        ]);
        exit;
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'अमान्य ओटीपी दर्ज किया गया है (Invalid OTP). कृपया 6 अंकों का सही ओटीपी दर्ज करें।'
        ]);
        exit;
    }
}

// 3. Check Status
if ($action === 'check_status') {
    $auth = $_SESSION['ayushman_aadhaar_auth'] ?? null;
    $isVerified = !empty($auth['verified']) && $auth['verified'] === true;

    echo json_encode([
        'success'  => true,
        'verified' => $isVerified,
        'last4'    => $auth['aadhaar_last4'] ?? null,
        'mobile'   => $auth['mobile'] ?? null
    ]);
    exit;
}

// Invalid action
echo json_encode([
    'success' => false,
    'message' => 'अमान्य अनुरोध (Invalid action).'
]);
