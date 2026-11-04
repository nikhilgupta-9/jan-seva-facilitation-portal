<?php
/**
 * api/submit_ayushman.php
 * Dedicated AJAX API Endpoint for Ayushman Bharat Help Desk Applications
 * Provides sub-second response, non-blocking email queue, and full production DB resiliency.
 */

// Output JSON only
header('Content-Type: application/json; charset=utf-8');

// Disable output buffering interference
while (ob_get_level()) {
    ob_end_clean();
}

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/ayushman_crypto.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only POST is accepted.'
    ]);
    exit;
}

// 1. Validate CSRF Token
$token = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';
if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, $token)) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'सत्र सुरक्षा टोकन समाप्त या अमान्य है (Session expired or invalid). कृपया पेज रीफ्रेश करके पुनः प्रयास करें।'
    ]);
    exit;
}

$errors = [];

// 2. Extract & Sanitize Form Data
$fullName       = trim($_POST['full_name'] ?? '');
$dob            = trim($_POST['dob'] ?? '');
$gender         = in_array($_POST['gender'] ?? '', ['Male', 'Female', 'Other']) ? $_POST['gender'] : 'Male';
$email          = trim($_POST['email'] ?? '');
$rawAadhaar     = preg_replace('/\s+/', '', $_POST['aadhaar_number'] ?? '');
$mobile         = preg_replace('/\D/', '', $_POST['mobile'] ?? '');
$address        = trim($_POST['address'] ?? '');
$state          = trim($_POST['state'] ?? '');
$district       = trim($_POST['district'] ?? '');
$pincode        = trim($_POST['pincode'] ?? '');
$abhaNumber     = trim($_POST['abha_number'] ?? '');
$rationCardNo   = trim($_POST['ration_card_no'] ?? '');
$bocwRegNo      = trim($_POST['bocw_reg_no'] ?? '');
$pmjayLetterNo  = trim($_POST['pmjay_letter_no'] ?? '');
$consent        = !empty($_POST['consent_accepted']);

// 3. Server-Side Validation
if ($fullName === '') {
    $errors[] = "कृपया नागरिक / मरीज का पूरा नाम दर्ज करें (Full Name is required).";
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "कृपया एक मान्य ईमेल आईडी दर्ज करें (Valid Email ID is required).";
}
if (!preg_match('/^[2-9][0-9]{11}$/', $rawAadhaar)) {
    $errors[] = "आधार नंबर 12 अंकों का होना चाहिए और 2-9 से शुरू होना चाहिए (Valid 12-digit Aadhaar required).";
}
if (!preg_match('/^[6-9][0-9]{9}$/', $mobile)) {
    $errors[] = "मोबाइल नंबर 10 अंकों का होना चाहिए और 6-9 से शुरू होना चाहिए (Valid 10-digit Indian Mobile required).";
}
if (!$consent) {
    $errors[] = "नियम, शर्तें एवं स्व-घोषणा सहमति स्वीकार करना अनिवार्य है (Terms & Consent must be accepted).";
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'errors'  => $errors,
        'message' => 'कृपया फॉर्म में दी गई त्रुटियों को ठीक करें।'
    ]);
    exit;
}

$aadhaarLast4 = substr($rawAadhaar, -4);

// 4. Duplicate Check: Redirect immediately if mobile already has an application
try {
    $checkStmt = $pdo->prepare("SELECT application_no FROM ayushman_applications WHERE mobile = ? ORDER BY id DESC LIMIT 1");
    $checkStmt->execute([$mobile]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existing && !empty($existing['application_no'])) {
        $existingAppNo = $existing['application_no'];
        echo json_encode([
            'success'        => true,
            'is_existing'    => true,
            'application_no' => $existingAppNo,
            'message'        => 'इस मोबाइल नंबर पर पहले से एक डिजिटल हेल्थ स्मार्ट आईडी कार्ड बना हुआ है। आपको आपके कार्ड पर रीडायरेक्ट किया जा रहा है...',
            'redirect_url'   => BASE_URL . "/ayushman-card.php?app_no=" . urlencode($existingAppNo) . "&status=existing"
        ]);
        exit;
    }
} catch (\Throwable $e) {
    // If DB check fails, continue with submission attempt
}

// 5. Process File Uploads Safely
$uploadDir = dirname(__DIR__) . '/uploads/ayushman/';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0777, true);
}

$uploadedFiles = [
    'photo_url'        => null,
    'abha_card_url'    => null,
    'ayushman_card_url'=> null,
    'ration_card_url'  => null,
    'bocw_card_url'    => null,
    'pmjay_letter_url' => null,
];

$fileFields = [
    'photo'         => ['col' => 'photo_url',         'mimes' => ['image/jpeg', 'image/png', 'image/webp']],
    'abha_card'     => ['col' => 'abha_card_url',     'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
    'ayushman_card' => ['col' => 'ayushman_card_url', 'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
    'ration_card'   => ['col' => 'ration_card_url',   'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
    'bocw_card'     => ['col' => 'bocw_card_url',     'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
    'pmjay_letter'  => ['col' => 'pmjay_letter_url',  'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
];

$maxBytes = 10 * 1024 * 1024; // 10MB per file

foreach ($fileFields as $inputKey => $meta) {
    if (isset($_FILES[$inputKey]) && $_FILES[$inputKey]['error'] === UPLOAD_ERR_OK) {
        $tmpPath  = $_FILES[$inputKey]['tmp_name'];
        $fileSize = $_FILES[$inputKey]['size'];

        if ($fileSize > $maxBytes) {
            $errors[] = "दस्तावेज '{$inputKey}' का आकार 10MB से अधिक नहीं होना चाहिए (File exceeds 10MB).";
            continue;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!in_array($mimeType, $meta['mimes'])) {
            $errors[] = "दस्तावेज '{$inputKey}' का प्रारूप मान्य नहीं है। केवल JPG, PNG, WEBP या PDF ही स्वीकार्य हैं।";
            continue;
        }

        $extMap = [
            'image/jpeg'      => 'jpg',
            'image/png'       => 'png',
            'image/webp'      => 'webp',
            'application/pdf' => 'pdf',
        ];
        $ext = $extMap[$mimeType] ?? 'bin';
        $safeFileName = 'ayush_' . $inputKey . '_' . bin2hex(random_bytes(10)) . '.' . $ext;
        $destination = $uploadDir . $safeFileName;

        if (move_uploaded_file($tmpPath, $destination)) {
            $uploadedFiles[$meta['col']] = 'uploads/ayushman/' . $safeFileName;
        } else {
            $errors[] = "दस्तावेज '{$inputKey}' अपलोड करने में त्रुटि हुई।";
        }
    }
}

if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'errors'  => $errors,
        'message' => 'अपलोड किए गए दस्तावेजों में त्रुटि है।'
    ]);
    exit;
}

// 6. Generate Identifiers & Encrypted QR Token
try {
    $appNo  = ayushman_generate_application_no();
    $cardId = ayushman_generate_card_id();

    $qrData = [
        'v'             => 1,
        'app_no'        => $appNo,
        'card_id'       => $cardId,
        'aadhaar_last4' => $aadhaarLast4,
    ];
    $encryptedQrToken = ayushman_encrypt_payload($qrData);
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    // 7. Resilient Schema Insertion (Works seamlessly on both production & updated schemas)
    $hasEmailSentCol = false;
    try {
        $colCheck = $pdo->query("SHOW COLUMNS FROM `ayushman_applications` LIKE 'email_sent'");
        $hasEmailSentCol = ($colCheck && $colCheck->rowCount() > 0);
    } catch (\Throwable $t) {
        $hasEmailSentCol = false;
    }

    // Auto-heal missing email_sent column if allowed
    if (!$hasEmailSentCol) {
        try {
            $pdo->exec("ALTER TABLE `ayushman_applications` 
                ADD COLUMN `email_sent` TINYINT(1) DEFAULT 0,
                ADD COLUMN `email_sent_at` DATETIME NULL,
                ADD COLUMN `email_error` TEXT NULL,
                ADD COLUMN `email_attempts` INT DEFAULT 0,
                ADD INDEX `idx_email_sent` (`email_sent`)");
            $hasEmailSentCol = true;
        } catch (\Throwable $migErr) {
            $hasEmailSentCol = false; // Fall back gracefully to standard columns
        }
    }

    if ($hasEmailSentCol) {
        $stmt = $pdo->prepare("INSERT INTO `ayushman_applications` 
            (`application_no`, `card_id`, `full_name`, `dob`, `gender`, `mobile`, `email`, `address`, `state`, `district`, `pincode`, 
             `aadhaar_last4`, `aadhaar_verified`, `aadhaar_verified_at`, `abha_number`, `ration_card_no`, `bocw_reg_no`, 
             `pmjay_letter_no`, `photo_url`, `abha_card_url`, `ayushman_card_url`, `ration_card_url`, `bocw_card_url`, 
             `pmjay_letter_url`, `consent_accepted`, `email_sent`, `encrypted_qr_token`, `status`, `ip_address`) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, ?, 'submitted', ?)");

        $stmt->execute([
            $appNo, $cardId, $fullName, !empty($dob) ? $dob : null, $gender, $mobile, $email,
            $address, $state, $district, $pincode, $aadhaarLast4, $abhaNumber, $rationCardNo,
            $bocwRegNo, $pmjayLetterNo, $uploadedFiles['photo_url'], $uploadedFiles['abha_card_url'],
            $uploadedFiles['ayushman_card_url'], $uploadedFiles['ration_card_url'], $uploadedFiles['bocw_card_url'],
            $uploadedFiles['pmjay_letter_url'], $encryptedQrToken, $ipAddress
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO `ayushman_applications` 
            (`application_no`, `card_id`, `full_name`, `dob`, `gender`, `mobile`, `email`, `address`, `state`, `district`, `pincode`, 
             `aadhaar_last4`, `aadhaar_verified`, `aadhaar_verified_at`, `abha_number`, `ration_card_no`, `bocw_reg_no`, 
             `pmjay_letter_no`, `photo_url`, `abha_card_url`, `ayushman_card_url`, `ration_card_url`, `bocw_card_url`, 
             `pmjay_letter_url`, `consent_accepted`, `encrypted_qr_token`, `status`, `ip_address`) 
            VALUES 
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 'submitted', ?)");

        $stmt->execute([
            $appNo, $cardId, $fullName, !empty($dob) ? $dob : null, $gender, $mobile, $email,
            $address, $state, $district, $pincode, $aadhaarLast4, $abhaNumber, $rationCardNo,
            $bocwRegNo, $pmjayLetterNo, $uploadedFiles['photo_url'], $uploadedFiles['abha_card_url'],
            $uploadedFiles['ayushman_card_url'], $uploadedFiles['ration_card_url'], $uploadedFiles['bocw_card_url'],
            $uploadedFiles['pmjay_letter_url'], $encryptedQrToken, $ipAddress
        ]);
    }

    // 8. Non-Blocking Background Email Trigger
    // Form responds in < 50ms without waiting for SMTP connection
    if (function_exists('exec')) {
        $cronScript = dirname(__DIR__) . '/cron/send_ayushman_emails.php';
        $phpBin = is_file('/Applications/XAMPP/xamppfiles/bin/php') ? '/Applications/XAMPP/xamppfiles/bin/php' : 'php';
        @exec(escapeshellcmd($phpBin) . ' ' . escapeshellarg($cronScript) . ' > /dev/null 2>&1 &');
    }

    $redirectUrl = BASE_URL . "/ayushman-card.php?app_no=" . urlencode($appNo) . "&status=success";

    echo json_encode([
        'success'        => true,
        'application_no' => $appNo,
        'card_id'        => $cardId,
        'message'        => 'आवेदन सफलतापूर्वक सबमिट हुआ एवं डिजिटल हेल्थ स्मार्ट आईडी कार्ड जनरेट हो गया है!',
        'redirect_url'   => $redirectUrl
    ]);
    exit;

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'डेटाबेस में सुरक्षित करने में त्रुटि हुई: ' . $e->getMessage()
    ]);
    exit;
}
