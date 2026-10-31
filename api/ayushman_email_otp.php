<?php
// api/ayushman_email_otp.php - Email OTP Verification Gateway for Ayushman Help Desk
// Alternative to Aadhaar OTP for citizens unfamiliar with email/Aadhaar flows is the
// mobile-based Aadhaar OTP; this is the alternative for citizens who prefer/only have email.
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/mailer.php';

require_once __DIR__ . '/../utils/session_init.php';
ayushman_start_secure_session();

$action = $_GET['action'] ?? $_POST['action'] ?? '';

function ayushman_send_email_otp(string $to, string $otp): bool
{
    $appName = defined('APP_NAME') ? APP_NAME : 'Citizen Portal';
    $subject = "{$appName} - Your Ayushman Help Desk OTP";
    $body = "Your One-Time Password (OTP) for Ayushman Help Desk verification is:\n\n"
          . "    {$otp}\n\n"
          . "This OTP is valid for 10 minutes. Do not share it with anyone.\n\n"
          . "If you did not request this, please ignore this email.";

    $smtpHost = env('SMTP_HOST');
    if ($smtpHost) {
        try {
            return smtp_send_mail($to, $subject, $body);
        } catch (Exception $e) {
            error_log('Ayushman email OTP SMTP send failed: ' . $e->getMessage());
            return false;
        }
    }

    $headers = "From: {$appName} <no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">\r\n";
    return @mail($to, $subject, $body, $headers);
}

// 1. Send OTP
if ($action === 'send_otp') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Please enter a valid email address.'
        ]);
        exit;
    }

    // Simple rate limiting in session
    $now = time();
    if (!isset($_SESSION['email_otp_attempts'])) {
        $_SESSION['email_otp_attempts'] = [];
    }
    $_SESSION['email_otp_attempts'] = array_filter($_SESSION['email_otp_attempts'], function ($t) use ($now) {
        return ($now - $t) < 300; // last 5 minutes
    });

    if (count($_SESSION['email_otp_attempts']) >= 5) {
        echo json_encode([
            'success' => false,
            'message' => 'Too many attempts. Please try again after 5 minutes.'
        ]);
        exit;
    }
    $_SESSION['email_otp_attempts'][] = $now;

    $randomOtp = (string) mt_rand(100000, 999999);
    $maskedEmail = preg_replace('/^(.).*(@.*)$/', '$1***$2', $email);

    $_SESSION['ayushman_email_auth'] = [
        'email'      => $email,
        'otp'        => $randomOtp,
        'expires_at' => $now + (10 * 60), // 10 minutes
        'verified'   => false,
        'created_at' => $now
    ];

    $sent = ayushman_send_email_otp($email, $randomOtp);

    $response = [
        'success'       => true,
        'message'       => "OTP sent to {$maskedEmail}. Please check your inbox (and spam folder).",
        'masked_email'  => $maskedEmail,
        'mail_delivered' => $sent
    ];

    // Sandbox / test mode: also reveal the OTP on-screen in case mail delivery isn't configured locally
    if (defined('OTP_TEST_MODE') && OTP_TEST_MODE) {
        $response['test_mode'] = true;
        $response['demo_otp'] = $randomOtp;
    }

    echo json_encode($response);
    exit;
}

// 2. Verify OTP
if ($action === 'verify_otp') {
    $enteredOtp = trim($_POST['otp'] ?? '');
    $auth = $_SESSION['ayushman_email_auth'] ?? null;

    if (!$auth || empty($auth['otp'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Session expired. Please request a new OTP first.'
        ]);
        exit;
    }

    if (time() > $auth['expires_at']) {
        echo json_encode([
            'success' => false,
            'message' => 'OTP has expired. Please request a new OTP.'
        ]);
        exit;
    }

    $isValid = ($enteredOtp === $auth['otp']);

    if ($isValid) {
        $_SESSION['ayushman_email_auth']['verified'] = true;
        $_SESSION['ayushman_email_auth']['verified_at'] = date('Y-m-d H:i:s');

        echo json_encode([
            'success'     => true,
            'message'     => 'Email verified successfully!',
            'email'       => $auth['email'],
            'verified'    => true,
            'verified_at' => $_SESSION['ayushman_email_auth']['verified_at']
        ]);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Invalid OTP entered. Please enter the correct 6-digit OTP.'
    ]);
    exit;
}

// 3. Check Status
if ($action === 'check_status') {
    $auth = $_SESSION['ayushman_email_auth'] ?? null;
    $isVerified = !empty($auth['verified']) && $auth['verified'] === true;

    echo json_encode([
        'success'  => true,
        'verified' => $isVerified,
        'email'    => $auth['email'] ?? null
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Invalid action.'
]);
