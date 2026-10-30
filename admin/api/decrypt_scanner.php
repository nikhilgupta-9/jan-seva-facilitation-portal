<?php
// admin/api/decrypt_scanner.php - AJAX decryption endpoint for Admin Secure QR Scanner
header('Content-Type: application/json; charset=utf-8');

// Ensure admin authentication
require_once __DIR__ . '/../auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$rawInput = trim($_POST['token_or_url'] ?? '');

if (empty($rawInput)) {
    echo json_encode(['success' => false, 'message' => 'स्कैन किया गया डेटा रिक्त है (Empty QR scan data).']);
    exit;
}

// Extract token if user scanned a full URL
$token = $rawInput;
if (strpos($rawInput, 'token=') !== false) {
    $parsed = parse_url($rawInput);
    if (!empty($parsed['query'])) {
        parse_str($parsed['query'], $queryParams);
        if (!empty($queryParams['token'])) {
            $token = $queryParams['token'];
        }
    }
}

// Decrypt via cryptographic helper
$decrypted = ayushman_decrypt_payload($token);

if ($decrypted === null) {
    echo json_encode([
        'success' => false,
        'tampered' => true,
        'message' => 'सुरक्षा चेतावनी: यह QR कोड अमान्य है अथवा क्रिप्टोग्राफ़िक हस्ताक्षर (HMAC-SHA256) में विसंगति पाई गई है।'
    ]);
    exit;
}

// Query live DB record for document links and live status
$appNo = $decrypted['app_no'] ?? '';
$dbRecord = null;
if ($appNo && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT * FROM ayushman_applications WHERE application_no = ? LIMIT 1");
    $stmt->execute([$appNo]);
    $dbRecord = $stmt->fetch();
}

echo json_encode([
    'success' => true,
    'message' => 'क्रिप्टोग्राफ़िक हस्ताक्षर सत्यापित एवं डेटा डिक्रिप्टेड!',
    'payload' => $decrypted,
    'db_record' => $dbRecord,
    'token' => $token,
    'verified_at' => date('d M Y, h:i A')
]);
