<?php
// ayushman-helpdesk.php - Official Ayushman Bharat Help Desk & Digital Health Smart ID Submission
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/utils/mailer.php';

$page_title = 'आयुष्मान हेल्प डेस्क (Ayushman Help Desk) - Digital Health Smart ID';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$success = false;

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ayushman'])) {
    // 1. Verify CSRF
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'सुरक्षा टोकन अमान्य है। कृपया पुनः प्रयास करें (Invalid CSRF Token).';
    }

    // 2. Validate Inputs
    $fullName = trim($_POST['full_name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? 'Male');
    $mobile = preg_replace('/\D/', '', $_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $district = trim($_POST['district'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');

    $abhaNumber = trim($_POST['abha_number'] ?? '');
    $rationCardNo = trim($_POST['ration_card_no'] ?? '');
    $bocwRegNo = trim($_POST['bocw_reg_no'] ?? '');
    $pmjayLetterNo = trim($_POST['pmjay_letter_no'] ?? '');
    $consent = isset($_POST['consent_accepted']) ? 1 : 0;

    if (empty($fullName)) {
        $errors[] = 'कृपया मरीज/नागरिक का पूरा नाम दर्ज करें (Full Name is required).';
    }
    if (strlen($mobile) !== 10) {
        $errors[] = 'कृपया 10 अंकों का वैध मोबाइल नंबर दर्ज करें (Valid 10-digit Mobile is required).';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'कृपया वैध ईमेल आईडी दर्ज करें (Valid Email ID is required for sending card).';
    }
    if (!$consent) {
        $errors[] = 'कृपया नियम, शर्तें एवं स्व-घोषणा सहमति स्वीकार करें (Consent is required).';
    }

    // 3. Identity Verification Check — either Aadhaar OTP or Email OTP must be completed
    $authSession = $_SESSION['ayushman_aadhaar_auth'] ?? null;
    $aadhaarVerified = !empty($authSession['verified']) && $authSession['verified'] === true;
    $aadhaarLast4 = $authSession['aadhaar_last4'] ?? null;

    $emailAuthSession = $_SESSION['ayushman_email_auth'] ?? null;
    $emailVerified = !empty($emailAuthSession['verified']) && $emailAuthSession['verified'] === true;

    $identityVerified = $aadhaarVerified || $emailVerified;
    $verificationMethod = $aadhaarVerified ? 'aadhaar_otp' : ($emailVerified ? 'email_otp' : null);

    if (!$identityVerified) {
        $errors[] = 'कृपया फॉर्म सबमिट करने से पहले आधार ओटीपी अथवा ईमेल ओटीपी सत्यापन पूरा करें (Aadhaar OTP or Email OTP verification is mandatory).';
    }

    // When verifying via Email OTP, Aadhaar OTP is never run, so the Aadhaar
    // number is collected as a plain record field instead (Panel B's
    // "aadhaar_number_manual" input) — it is NOT used for any OTP, only stored.
    if ($emailVerified && !$aadhaarVerified) {
        $manualAadhaar = preg_replace('/\D/', '', $_POST['aadhaar_number_manual'] ?? '');
        if (strlen($manualAadhaar) !== 12 || in_array($manualAadhaar[0] ?? '', ['0', '1'])) {
            $errors[] = 'कृपया रिकॉर्ड हेतु 12 अंकों का वैध आधार नंबर दर्ज करें (A valid 12-digit Aadhaar number is required).';
        } else {
            $aadhaarLast4 = substr($manualAadhaar, -4);
        }
    }

    // 4. File Upload Helper
    $uploadDir = __DIR__ . '/uploads/ayushman/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $uploadedFiles = [
        'photo_url'        => null,
        'abha_card_url'    => null,
        'ayushman_card_url'=> null,
        'ration_card_url'  => null,
        'bocw_card_url'    => null,
        'pmjay_letter_url' => null,
    ];

    $fileInputs = [
        'photo'          => ['col' => 'photo_url', 'required' => false, 'mimes' => ['image/jpeg', 'image/png', 'image/webp']],
        'abha_card'      => ['col' => 'abha_card_url', 'required' => false, 'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
        'ayushman_card'  => ['col' => 'ayushman_card_url', 'required' => false, 'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
        'ration_card'    => ['col' => 'ration_card_url', 'required' => false, 'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
        'bocw_card'      => ['col' => 'bocw_card_url', 'required' => false, 'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
        'pmjay_letter'   => ['col' => 'pmjay_letter_url', 'required' => false, 'mimes' => ['image/jpeg', 'image/png', 'image/webp', 'application/pdf']],
    ];

    $maxBytes = 10 * 1024 * 1024; // 10MB limit

    foreach ($fileInputs as $inputKey => $meta) {
        if (!empty($_FILES[$inputKey]['name']) && $_FILES[$inputKey]['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES[$inputKey]['tmp_name'];
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
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf',
            ];
            $ext = $extMap[$mimeType] ?? 'bin';
            $safeFileName = 'ayush_' . $inputKey . '_' . bin2hex(random_bytes(12)) . '.' . $ext;
            $destination = $uploadDir . $safeFileName;

            if (move_uploaded_file($tmpPath, $destination)) {
                $uploadedFiles[$meta['col']] = 'uploads/ayushman/' . $safeFileName;
            } else {
                $errors[] = "दस्तावेज '{$inputKey}' अपलोड करने में त्रुटि हुई।";
            }
        }
    }

    // 5. If No Errors, Generate Identifiers, Encrypt QR, Save to DB, and Send Email
    if (empty($errors)) {
        try {
            $appNo = ayushman_generate_application_no();
            $cardId = ayushman_generate_card_id();

            // Prepare cryptographic payload — kept deliberately minimal.
            // The QR only needs to carry a tamper-proof POINTER + the few
            // fields needed before the DB lookup completes; everything else
            // (name, DOB, address, documents, etc.) is fetched live from the
            // ayushman_applications table by app_no on every scan anyway
            // (see admin/api/decrypt_scanner.php and verify-id.php), so
            // cramming the full record into the QR was pure dead weight.
            // That dead weight was the actual bug: ~800 characters of payload
            // pushed the QR to a very high version/module-density, which at
            // the card's small on-screen render size (90-180px) is below
            // what most phone cameras can physically resolve — scans would
            // "never" produce a result even though the code looked fine.
            $qrData = [
                'v'                    => 1,
                'app_no'               => $appNo,
                'card_id'              => $cardId,
                'aadhaar_last4'        => $aadhaarLast4,
                'aadhaar_verified'     => $aadhaarVerified ? 1 : 0,
                'verification_method'  => $verificationMethod,
            ];

            $encryptedQrToken = ayushman_encrypt_payload($qrData);
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $stmt = $pdo->prepare("INSERT INTO ayushman_applications 
                (application_no, card_id, full_name, dob, gender, mobile, email, address, state, district, pincode, 
                 aadhaar_last4, aadhaar_verified, aadhaar_verified_at, abha_number, ration_card_no, bocw_reg_no, 
                 pmjay_letter_no, photo_url, abha_card_url, ayushman_card_url, ration_card_url, bocw_card_url, 
                 pmjay_letter_url, consent_accepted, encrypted_qr_token, status, ip_address) 
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', ?)");

            $stmt->execute([
                $appNo,
                $cardId,
                $fullName,
                !empty($dob) ? $dob : null,
                $gender,
                $mobile,
                $email,
                $address,
                $state,
                $district,
                $pincode,
                $aadhaarLast4,
                $aadhaarVerified ? 1 : 0,
                $abhaNumber,
                $rationCardNo,
                $bocwRegNo,
                $pmjayLetterNo,
                $uploadedFiles['photo_url'],
                $uploadedFiles['abha_card_url'],
                $uploadedFiles['ayushman_card_url'],
                $uploadedFiles['ration_card_url'],
                $uploadedFiles['bocw_card_url'],
                $uploadedFiles['pmjay_letter_url'],
                $consent,
                $encryptedQrToken,
                $ipAddress
            ]);

            // Clear session verification to prevent replay
            unset($_SESSION['ayushman_aadhaar_auth']);
            unset($_SESSION['ayushman_email_auth']);

            // Redirect to Card View first, then flush the response to the
            // browser immediately — the confirmation email's SMTP round trip
            // (TLS handshake + auth + send, often 1-5s) shouldn't make the
            // citizen sit and wait on the submit page before seeing their card.
            session_write_close();
            header("Location: " . BASE_URL . "/ayushman-card.php?app_no=" . urlencode($appNo) . "&status=success");

            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            } else {
                if (ob_get_level() > 0) {
                    @ob_end_flush();
                }
                flush();
            }

            // Dispatch confirmation email (best-effort, after the redirect was sent)
            $emailPayload = [
                'application_no' => $appNo,
                'card_id'        => $cardId,
                'full_name'      => $fullName,
                'email'          => $email,
                'mobile'         => $mobile,
                'abha_number'    => $abhaNumber,
                'aadhaar_last4'  => $aadhaarLast4
            ];
            @send_ayushman_confirmation_email($emailPayload);

            exit;

        } catch (Exception $e) {
            $errors[] = 'डेटाबेस प्रविष्टि में त्रुटि: ' . $e->getMessage();
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<style>
/* Ayushman Help Desk — aligned to site light theme (css/gigw-theme.css tokens) */
.ayush-hero-banner {
    background: linear-gradient(135deg, rgb(30 58 138 / 74%) 0%, rgb(30 90 135 / 65%) 60%, rgb(8 145 178 / 58%) 100%), url(/gp/image/barr.png);
    background-position: center;
    background-size: cover;
    color: #ffffff;
    padding: 26px 0 20px;
    border-bottom: 4px solid #f59e0b;
    position: relative;
    overflow: hidden;
}
.ayush-lang-switch {
    display: flex;
    justify-content: flex-end;
    gap: 6px;
    margin-bottom: 14px;
    position: relative;
    z-index: 2;
}
.lang-toggle-btn {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.35);
    color: #ffffff;
    font-weight: 700;
    font-size: 0.8rem;
    padding: 5px 14px;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.15s;
}
.lang-toggle-btn:hover {
    background: rgba(255,255,255,0.22);
}
.lang-toggle-btn.active {
    background: #f59e0b;
    border-color: #f59e0b;
    color: #1e293b;
}
.ayush-hero-banner::after {
    content: '';
    position: absolute;
    right: -40px;
    bottom: -40px;
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.ayush-hero-banner .breadcrumb {
    background: transparent;
    margin: 0 0 12px;
    padding: 0;
    font-size: 0.85rem;
}
.ayush-hero-banner .breadcrumb a {
    color: #cbd5e1;
}
.ayush-hero-banner .breadcrumb a i {
    margin-right: 4px;
}
.ayush-hero-banner .breadcrumb .active {
    color: #f8fafc;
}
.ayush-hero-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 10px;
}
.badge-gov-saffron {
    background: #f59e0b;
    color: #ffffff;
    font-weight: 700;
    font-size: 0.78rem;
    padding: 4px 10px;
    border-radius: 20px;
}
.badge-gov-saffron i {
    margin-right: 4px;
}
.ayush-hero-banner h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 0 6px;
    line-height: 1.3;
    color: #ffffff;
}
.ayush-hero-banner .hero-subtext {
    margin: 0;
    font-size: 0.86rem;
    opacity: 0.92;
    max-width: 680px;
}
.ayush-hero-side-card {
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255,255,255,0.22);
    border-radius: 12px;
    padding: 18px;
    text-align: center;
    color: #ffffff;
}
.ayush-hero-side-card i {
    color: #f59e0b;
    margin-bottom: 8px;
}

.ayush-card-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(11,60,93,0.06);
    margin-bottom: 24px;
    overflow: hidden;
    transition: transform 0.2s, box-shadow 0.2s;
}
.ayush-card-header {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    padding: 11px 16px;
    font-weight: 700;
    font-size: 0.9rem;
    color: #1e3a8a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.ayush-card-header i {
    width: 20px;
    text-align: center;
    flex-shrink: 0;
    color: #1e3a8a;
}
.ayush-card-header.header-success { background: #f0fdf4; border-bottom-color: #bbf7d0; }
.ayush-card-header.header-success i,
.ayush-card-header.header-success span { color: #047857; }
.ayush-card-header.header-warning { background: #fffbeb; }
.ayush-card-header.header-warning i { color: #f59e0b; }
.ayush-card-header.header-warning span { color: #92400e; }
.ayush-card-body {
    padding: 16px;
}
.form-label-gov {
    font-weight: 600;
    color: #334155;
    font-size: 0.82rem;
    margin-bottom: 5px;
    display: block;
}
.form-label-gov span.req {
    color: #dc2626;
    font-weight: 800;
}
.form-control-gov {
    height: 38px;
    border-radius: 6px;
    border: 1.5px solid #cbd5e1;
    font-size: 0.86rem;
    box-shadow: none !important;
    transition: all 0.2s;
}
.form-control-gov:focus {
    border-color: #1e3a8a;
    box-shadow: 0 0 0 3px rgba(11, 60, 93, 0.12) !important;
}
textarea.form-control-gov-area {
    border-radius: 6px;
    border: 1.5px solid #cbd5e1;
}
textarea.form-control-gov-area:focus {
    border-color: #1e3a8a;
    box-shadow: 0 0 0 3px rgba(11, 60, 93, 0.12) !important;
    outline: none;
}
.file-upload-tile {
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    border-radius: 8px;
    padding: 12px 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 86px;
}
.file-upload-tile:hover {
    border-color: #1e3a8a;
    background: #f0fdf4;
}
.file-upload-tile input[type="file"] {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.file-upload-tile i {
    font-size: 1.15rem;
    color: #1e3a8a;
}
.file-upload-tile .tile-title {
    font-weight: 600;
    font-size: 0.76rem;
    color: #334155;
    margin-top: 6px;
}
.file-upload-tile .tile-hint {
    font-size: 0.68rem;
    color: #64748b;
    margin-top: 2px;
}
.file-upload-tile .file-selected-name {
    font-size: 0.74rem;
    color: #047857;
    font-weight: 600;
    margin-top: 6px;
    word-break: break-all;
}
.ayush-field-group {
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
}
.ayush-field-group:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}
.aadhaar-verify-box {
    background: #f0f9ff;
    border: 1.5px solid #bae6fd;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 20px;
}
.input-group-addon-gov {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-right: none;
    color: #64748b;
    display: flex;
    align-items: center;
    padding: 0 12px;
    border-radius: 6px 0 0 6px;
}
.security-encrypted-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 700;
}
.btn-gov-outline {
    background: #ffffff;
    color: #1e3a8a;
    border: 1.5px solid #1e3a8a;
    font-weight: 600;
    font-size: 0.82rem;
    padding: 7px 14px;
    border-radius: 6px;
    transition: all 0.2s;
}
.btn-gov-outline:hover {
    background: #1e3a8a;
    color: #ffffff;
}
.verify-method-tab.active {
    background: #1e3a8a;
    color: #ffffff;
}
.btn-gov-outline i,
.btn-gov-verify i {
    margin-right: 6px;
}
.btn-gov-verify {
    font-weight: 600;
    font-size: 0.82rem;
    padding: 7px 16px;
    border-radius: 6px;
    background: #10b981;
    border-color: #10b981;
    color: #ffffff;
}
.btn-gov-verify:hover {
    background: #047857;
    border-color: #047857;
    color: #ffffff;
}
.btn-gov-submit {
    background: linear-gradient(135deg, #047857 0%, #10b981 100%);
    color: #ffffff;
    font-size: 0.92rem;
    font-weight: 700;
    padding: 11px 26px;
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35);
    transition: all 0.2s;
    letter-spacing: 0.2px;
}
.btn-gov-submit i {
    margin-right: 8px;
}
.btn-gov-submit:hover {
    background: linear-gradient(135deg, #047857 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(22, 163, 74, 0.45);
    color: #ffffff;
}
.ayush-sidebar-sticky {
    position: sticky;
    top: 20px;
}
.ayush-side-icon {
    color: #10b981;
}
.ayush-side-title {
    font-weight: 800;
    color: #047857;
    margin-top: 8px;
    margin-bottom: 4px;
}
.ayush-side-note {
    background: #f8fafc;
    border-radius: 6px;
    padding: 12px;
    font-size: 0.82rem;
    color: #475569;
    margin-bottom: 12px;
}
.ayush-side-note i {
    color: #1e3a8a;
    margin-right: 4px;
}
.ayush-helpline-card .helpline-title i {
    color: #f59e0b;
    margin-right: 6px;
}
.ayush-helpline-card .helpline-number i,
.ayush-helpline-card .helpline-email i {
    margin-right: 6px;
}
.ayush-helpline-card .helpline-number i { color: #10b981; }
.ayush-helpline-card .helpline-email i { color: #0891b2; }
.ayush-alert-errors {
    border-radius: 8px;
    border-left: 5px solid #dc2626;
    box-shadow: 0 2px 8px rgba(220,38,38,0.1);
}
.ayushman-submit-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.78);
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.ayushman-submit-box {
    background: #ffffff;
    border-radius: 14px;
    padding: 28px 24px;
    max-width: 360px;
    text-align: center;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}
.ayushman-submit-spinner {
    width: 44px;
    height: 44px;
    border: 4px solid #e2e8f0;
    border-top-color: #1e3a8a;
    border-radius: 50%;
    margin: 0 auto 16px;
    animation: ayushman-spin 0.8s linear infinite;
}
@keyframes ayushman-spin {
    to { transform: rotate(360deg); }
}
.ayushman-submit-box h4 {
    margin: 0 0 8px;
    font-weight: 800;
    color: #1e293b;
    font-size: 1rem;
}
.ayushman-submit-box p {
    margin: 0;
    font-size: 0.82rem;
    color: #b91c1c;
    font-weight: 600;
    line-height: 1.5;
}
.ayushman-submit-status {
    margin: 12px 0 10px;
    font-size: 0.86rem;
    color: #1e3a8a;
    font-weight: 700;
    min-height: 20px;
}
.ayushman-submit-progress-track {
    width: 100%;
    height: 6px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 10px;
}
.ayushman-submit-progress-bar {
    height: 100%;
    width: 8%;
    background: linear-gradient(90deg, #1e3a8a, #0891b2);
    border-radius: 10px;
    transition: width 0.6s ease;
}
.ayushman-submit-elapsed {
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 10px;
}
.ayush-about-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(11,60,93,0.06);
    padding: 18px 16px;
    margin-bottom: 20px;
}
.ayush-about-steps {
    list-style: none;
    padding: 0;
    margin: 10px 0 0;
    counter-reset: ayush-step;
}
.ayush-about-steps li {
    counter-increment: ayush-step;
    position: relative;
    padding-left: 30px;
    font-size: 0.82rem;
    color: #475569;
    margin-bottom: 8px;
    line-height: 1.45;
}
.ayush-about-steps li::before {
    content: counter(ayush-step);
    position: absolute;
    left: 0;
    top: 0;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #1e3a8a;
    color: #ffffff;
    font-size: 0.7rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}
#btn-goto-form {
    width: 100%;
    margin-top: 14px;
}
#ayushman-main-content { display: block; }
.ayush-mobile-intro { display: none; }

@media (max-width: 768px) {
    .ayush-hero-banner { padding: 18px 0 14px; }
    .ayush-hero-banner h1 { font-size: 1.2rem; }
    .ayush-hero-banner .hero-subtext { font-size: 0.8rem; }
    .ayush-card-body { padding: 14px; }
    .btn-gov-submit { width: 100%; font-size: 0.9rem; }

    /* Two-step mobile flow: show the About/How-it-works intro first,
       keep the actual long form hidden until the citizen taps "Fill Application Form". */
    .ayush-mobile-intro { display: block; }
    #ayushman-main-content { display: none; }
    #ayushman-main-content.show { display: block; }
}
</style>

<!-- Hero / Breadcrumb Section -->
<section class="ayush-hero-banner">
  <div class="container">
    <div class="ayush-lang-switch" role="group" aria-label="Language switch">
      <button type="button" class="lang-toggle-btn active" data-lang-val="en">EN</button>
      <button type="button" class="lang-toggle-btn" data-lang-val="hi">हिंदी</button>
    </div>
    <div class="row align-items-center">
      <div class="col-md-8 col-sm-12">
        <ol class="breadcrumb">
          <li><a href="<?php echo BASE_URL; ?>/index.php"><i class="fas fa-home"></i> <span data-hi="होम" data-en="Home">Home</span></a></li>
          <li class="active"><span data-hi="आयुष्मान हेल्प डेस्क" data-en="Ayushman Help Desk">Ayushman Help Desk</span></li>
        </ol>
        <div class="ayush-hero-badges">
          <span class="badge-gov-saffron">
            <i class="fas fa-shield-virus"></i> <span data-hi="राष्ट्रीय स्वास्थ्य प्राधिकरण (NHA)" data-en="National Health Authority (NHA)">National Health Authority (NHA)</span>
          </span>
          <span class="security-encrypted-badge">
            <i class="fas fa-lock"></i> 256-Bit Encrypted Health Smart ID
          </span>
        </div>
        <h1 data-hi="आयुष्मान हेल्प डेस्क एवं डिजिटल हेल्थ स्मार्ट आईडी" data-en="Ayushman Help Desk &amp; Digital Health Smart ID">Ayushman Help Desk &amp; Digital Health Smart ID</h1>
        <p class="hero-subtext" data-hi="नागरिक सुविधा केंद्र: PM-JAY स्वास्थ्य बीमा योजना के अंतर्गत अपना लाभ क्लेम करने हेतु अपनी पहचान सत्यापित करें और एक सुरक्षित एन्क्रिप्टेड डिजिटल हेल्थ स्मार्ट आईडी कार्ड प्राप्त करें। ABDM डिजिटल हेल्थ इकोसिस्टम के अंतर्गत कार्यरत।" data-en="Citizen Facilitation Centre: Verify your identity to claim your benefit under the PM-JAY health insurance scheme and get your secure, encrypted Digital Health Smart ID card. Operates under the ABDM digital health ecosystem.">
          Citizen Facilitation Centre: Verify your identity to claim your benefit under the PM-JAY health insurance scheme and get your secure, encrypted Digital Health Smart ID card. Operates under the ABDM digital health ecosystem.
        </p>
      </div>
      <div class="col-md-4 col-sm-12 text-md-end d-none d-md-block">
        <div class="ayush-hero-side-card">
          <i class="fas fa-id-card-alt fa-3x"></i>
          <div style="font-weight: 700; font-size: 1.05rem;">Digital Health Smart ID</div>
          <div style="font-size: 0.8rem; opacity: 0.88;">Aadhaar Verified • Encrypted QR</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Mobile-only: About / How it works intro, shown before the form -->
<div class="container ayush-mobile-intro" style="padding-top: 20px; <?php echo !empty($errors) ? 'display:none;' : ''; ?>">
  <div class="ayush-about-card">
    <h4 style="margin: 0 0 8px; font-weight: 800; color: #1e3a8a; font-size: 1rem;">
      <i class="fas fa-circle-info"></i> <span data-hi="आयुष्मान हेल्प डेस्क के बारे में" data-en="About Ayushman Help Desk">About Ayushman Help Desk</span>
    </h4>
    <p style="font-size: 0.84rem; color: #475569; line-height: 1.5; margin: 0;" data-hi="आयुष्मान भारत प्रधानमंत्री जन आरोग्य योजना (PM-JAY) केंद्र सरकार की विश्व की सबसे बड़ी स्वास्थ्य बीमा योजना है, जो पात्र परिवारों को प्रति वर्ष ₹5 लाख तक का कैशलेस अस्पताल उपचार कवर प्रदान करती है। यह हेल्प डेस्क नागरिकों को अपनी पहचान व दस्तावेज डिजिटल रूप से सत्यापित करने और एक सत्यापित डिजिटल हेल्थ स्मार्ट आईडी कार्ड बनाने में सहायता करता है, जिससे वे इस योजना के अंतर्गत अपने स्वास्थ्य बीमा लाभ का दावा (claim) आसानी से कर सकें। यह प्लेटफॉर्म आयुष्मान भारत डिजिटल मिशन (ABDM) — भारत के डिजिटल हेल्थ इकोसिस्टम — के अंतर्गत कार्य करता है।" data-en="Ayushman Bharat Pradhan Mantri Jan Arogya Yojana (PM-JAY) is the Central Government's flagship health insurance scheme — the world's largest — providing eligible families cashless hospitalization cover of up to ₹5 lakh per year. This Help Desk assists citizens in digitally verifying their identity and documents and generating a verified Digital Health Smart ID card, so they can more easily claim their health insurance benefits under this scheme. The platform operates under the Ayushman Bharat Digital Mission (ABDM) — India's digital health ecosystem.">
      Ayushman Bharat Pradhan Mantri Jan Arogya Yojana (PM-JAY) is the Central Government's flagship health insurance scheme — the world's largest — providing eligible families cashless hospitalization cover of up to ₹5 lakh per year. This Help Desk assists citizens in digitally verifying their identity and documents and generating a verified Digital Health Smart ID card, so they can more easily claim their health insurance benefits under this scheme. The platform operates under the Ayushman Bharat Digital Mission (ABDM) — India's digital health ecosystem.
    </p>

    <h5 style="margin: 14px 0 0; font-weight: 700; color: #1e293b; font-size: 0.86rem;" data-hi="इसका उपयोग कैसे करें" data-en="How to use this">How to use this</h5>
    <ol class="ayush-about-steps">
      <li data-hi="आधार ओटीपी अथवा ईमेल ओटीपी से अपनी पहचान सत्यापित करें।" data-en="Verify your identity using Aadhaar OTP or Email OTP.">Verify your identity using Aadhaar OTP or Email OTP.</li>
      <li data-hi="अपना व्यक्तिगत विवरण, स्वास्थ्य आईडी एवं पता भरें।" data-en="Fill in your personal details, health IDs and address.">Fill in your personal details, health IDs and address.</li>
      <li data-hi="अपने उपलब्ध दस्तावेजों (आभा, आयुष्मान, राशन, BOCW) की स्पष्ट फोटो अपलोड करें।" data-en="Upload clear photos of your available documents (ABHA, Ayushman, Ration, BOCW).">Upload clear photos of your available documents (ABHA, Ayushman, Ration, BOCW).</li>
      <li data-hi="सहमति स्वीकार करें और सबमिट करें — आपका एन्क्रिप्टेड स्मार्ट आईडी कार्ड तुरंत जनरेट होकर ईमेल पर भेजा जाएगा।" data-en="Accept the consent and submit — your encrypted Smart ID card is generated instantly and emailed to you.">Accept the consent and submit — your encrypted Smart ID card is generated instantly and emailed to you.</li>
    </ol>

    <button type="button" id="btn-goto-form" class="btn btn-gov-submit">
      <i class="fas fa-file-pen"></i> <span data-hi="आवेदन फॉर्म भरें" data-en="Fill Application Form">Fill Application Form</span>
    </button>
  </div>
</div>

<!-- Main Container -->
<div class="container <?php echo !empty($errors) ? 'show' : ''; ?>" id="ayushman-main-content" style="padding-top: 32px; padding-bottom: 50px;">

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger ayush-alert-errors">
      <h4 style="margin: 0 0 8px; font-weight: 800;"><i class="fas fa-exclamation-triangle"></i> <span data-hi="कृपया निम्नलिखित त्रुटियों को सुधारें:" data-en="Please correct the following errors:">Please correct the following errors:</span></h4>
      <ul style="margin: 0; padding-left: 20px;">
        <?php foreach ($errors as $err): ?>
          <li style="font-size: 0.92rem; margin-bottom: 4px;"><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <form method="POST" action="" enctype="multipart/form-data" id="ayushman-form" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <input type="hidden" name="submit_ayushman" value="1">

    <div class="row">
      <!-- Left Column: Form Inputs -->
      <div class="col-lg-8 col-md-8 col-sm-12">
        
        <!-- SECTION 1: Personal & Demographic Details -->
        <div class="ayush-card-box">
          <div class="ayush-card-header">
            <i class="fas fa-user-circle"></i>
            <span data-hi="१. मरीज / नागरिक का विवरण" data-en="1. Personal &amp; Patient Details">1. Personal &amp; Patient Details</span>
          </div>
          <div class="ayush-card-body">
            <div class="row">
              <div class="col-md-7 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="full_name">
                    <span data-hi="मरीज का पूरा नाम" data-en="Full Name">Full Name</span> <span class="req">*</span>
                  </label>
                  <input type="text" name="full_name" id="full_name" class="form-control form-control-gov"
                         placeholder="e.g. Ramesh Kumar" data-hi-placeholder="जैसे: रमेश कुमार" data-en-placeholder="e.g. Ramesh Kumar"
                         value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                  <small class="text-muted" data-hi="मरीज/नागरिक का पूरा नाम दर्ज करें।" data-en="Enter the full name of the patient/citizen.">Enter the full name of the patient/citizen.</small>
                </div>

                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label class="form-label-gov" for="dob" data-hi="जन्म तिथि" data-en="Date of Birth">Date of Birth</label>
                      <input type="date" name="dob" id="dob" class="form-control form-control-gov"
                             value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label class="form-label-gov" for="gender" data-hi="लिंग" data-en="Gender">Gender</label>
                      <select name="gender" id="gender" class="form-control form-control-gov">
                        <option value="Male" data-hi="पुरुष" data-en="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" data-hi="महिला" data-en="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" data-hi="अन्य" data-en="Other" <?php echo (($_POST['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>Other</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label-gov" for="email">
                    <span data-hi="ईमेल आईडी" data-en="Email ID">Email ID</span> <span class="req">*</span>
                  </label>
                  <input type="text" name="email" id="email" class="form-control form-control-gov"
                         inputmode="email" 
                         pattern="[^\s@]+@[^\s@]+\.[^\s@]+"
                         placeholder="yourname@gmail.com"
                         value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                  <small class="text-muted" data-hi="सबमिशन के बाद आपका डिजिटल स्मार्ट कार्ड इसी ईमेल पर भेजा जाएगा।" data-en="Your Digital Smart Card will be sent to this email after submission.">Your Digital Smart Card will be sent to this email after submission.</small>
                </div>
              </div>

              <!-- Photo Upload Box -->
              <div class="col-md-5 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" data-hi="फोटो अपलोड करें" data-en="Upload Your Photo">Upload Your Photo</label>
                  <div class="file-upload-tile" id="photo-tile">
                    <input type="file" name="photo" id="photo_input" accept="image/jpeg,image/png,image/webp" onchange="previewCitizenPhoto(this)">
                    <div id="photo-preview-wrap">
                      <i class="fas fa-camera-retro fa-2x" style="color: #0b3c5d;"></i>
                      <div class="tile-title" data-hi="पासपोर्ट फोटो चुनें या यहाँ खींचें" data-en="Choose a passport photo or drag it here">Choose a passport photo or drag it here</div>
                      <div class="tile-hint" data-hi="JPG, PNG या WEBP (अधिकतम 10MB)" data-en="JPG, PNG or WEBP (max 10MB)">JPG, PNG or WEBP (max 10MB)</div>
                    </div>
                    <img id="photo-img-preview" src="" alt="Photo Preview" style="display:none; max-width: 110px; max-height: 120px; border-radius: 6px; border: 2px solid #0b3c5d; margin: 8px auto 4px;">
                    <div id="photo-file-name" class="file-selected-name" style="display:none;"></div>
                  </div>

                  <!-- Explicit Upload / Live Camera Capture choice -->
                  <div style="display: flex; gap: 8px; margin-top: 8px;">
                    <button type="button" class="btn btn-gov-outline" style="flex: 1; padding: 7px 10px; font-size: 0.82rem;" onclick="document.getElementById('photo_input').click()">
                      <i class="fas fa-upload"></i> <span data-hi="अपलोड करें" data-en="Upload">Upload</span>
                    </button>
                    <button type="button" class="btn btn-gov-outline" style="flex: 1; padding: 7px 10px; font-size: 0.82rem;" onclick="document.getElementById('photo_camera_input').click()">
                      <i class="fas fa-camera"></i> <span data-hi="कैमरा से खींचें" data-en="Capture with Camera">Capture with Camera</span>
                    </button>
                  </div>
                  <!-- Hidden input that opens the device's live camera directly (mobile) -->
                  <input type="file" id="photo_camera_input" accept="image/*" capture="user" style="display:none;" onchange="captureCitizenPhotoFromCamera(this)">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 2: Aadhaar e-KYC & Mobile OTP Verification -->
        <div class="ayush-card-box">
          <div class="ayush-card-header header-success">
            <i class="fas fa-fingerprint"></i>
            <span data-hi="२. आधार ई-केवाईसी एवं मोबाइल नंबर सत्यापन" data-en="2. Aadhaar OTP Verification">2. Aadhaar OTP Verification</span>
          </div>
          <div class="ayush-card-body">

            <!-- Verification Method Toggle -->
            <div class="btn-group" role="group" style="margin-bottom: 16px; display: flex; flex-wrap: wrap; gap: 8px;">
              <button type="button" id="method-tab-aadhaar" class="btn btn-gov-outline verify-method-tab active" data-method="aadhaar">
                <i class="fas fa-fingerprint"></i> <span data-hi="आधार ओटीपी (मोबाइल)" data-en="Aadhaar OTP (Mobile)">Aadhaar OTP (Mobile)</span>
              </button>
              <button type="button" id="method-tab-email" class="btn btn-gov-outline verify-method-tab" data-method="email">
                <i class="fas fa-envelope"></i> <span data-hi="ईमेल ओटीपी" data-en="Email OTP">Email OTP</span>
              </button>
            </div>
            <p style="font-size: 0.8rem; color: #64748b; margin: -8px 0 14px;">
              <i class="fas fa-info-circle"></i>
              <span data-hi="ग्रामीण नागरिक जो ईमेल से परिचित नहीं हैं, वे आधार ओटीपी (मोबाइल) चुन सकते हैं। किसी एक विधि से सत्यापन पर्याप्त है।" data-en="Citizens unfamiliar with email can use Aadhaar OTP (mobile) instead. Verifying with either method is enough.">Citizens unfamiliar with email can use Aadhaar OTP (mobile) instead. Verifying with either method is enough.</span>
            </p>

            <!-- PANEL A: Aadhaar OTP -->
            <div class="aadhaar-verify-box" id="verify-panel-aadhaar">
              <div class="row align-items-center">
                <div class="col-md-6 col-sm-12">
                  <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label-gov" for="aadhaar_input">
                      <span data-hi="१२ अंकों का आधार नंबर" data-en="12-Digit Aadhaar No">12-Digit Aadhaar No</span> <span class="req">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text input-group-addon-gov"><i class="fas fa-id-card"></i></span>
                      <input type="text" id="aadhaar_input" class="form-control form-control-gov"
                             placeholder="XXXX XXXX XXXX" maxlength="14" autocomplete="off" required>
                    </div>
                  </div>
                </div>

                <div class="col-md-6 col-sm-12">
                  <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label-gov" for="mobile">
                      <span data-hi="मोबाइल नंबर" data-en="WhatsApp No">WhatsApp No</span> <span class="req">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text input-group-addon-gov">+91</span>
                      <input type="tel" name="mobile" id="mobile" class="form-control form-control-gov"
                             placeholder="9876543210" maxlength="10"
                             value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>" required>
                    </div>
                  </div>
                </div>
              </div>

              <!-- OTP Trigger Button -->
              <div style="margin-top: 10px;">
                <button type="button" id="btn-send-otp" class="btn btn-gov-outline">
                  <i class="fas fa-paper-plane"></i> <span data-hi="आधार ओटीपी भेजें" data-en="Send Aadhaar OTP">Send Aadhaar OTP</span>
                </button>
                <span id="otp-timer-badge" class="badge-gov-saffron" style="display:none; margin-left: 8px; font-size: 0.85rem;">60s</span>
              </div>

              <!-- OTP Input Row (Hidden initially) -->
              <div id="otp-verify-row" style="display: none; margin-top: 16px; padding-top: 14px; border-top: 1px dashed #cbd5e1;">
                <div class="row align-items-center">
                  <div class="col-md-6 col-sm-12">
                    <label class="form-label-gov" for="otp_input">
                      <span data-hi="प्राप्त ६ अंकों का ओटीपी दर्ज करें" data-en="Enter 6-Digit OTP">Enter 6-Digit OTP</span> <span class="req">*</span>
                    </label>
                    <input type="text" id="otp_input" class="form-control form-control-gov"
                           placeholder="123456" maxlength="6" style="letter-spacing: 4px; font-weight: 700; font-size: 1.1rem;">
                    <div id="otp-sandbox-hint" class="text-success" style="font-size: 0.8rem; margin-top: 4px; font-weight: 600;"></div>
                  </div>
                  <div class="col-md-6 col-sm-12" style="margin-top: 24px;">
                    <button type="button" id="btn-verify-otp" class="btn btn-gov-verify">
                      <i class="fas fa-check-circle"></i> <span data-hi="सत्यापित करें" data-en="Verify OTP">Verify OTP</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Verified Success State -->
              <div id="aadhaar-verified-badge" style="display: none; margin-top: 14px;" class="alert alert-success">
                <i class="fas fa-check-circle fa-lg"></i>
                <strong style="margin-left: 6px;" data-hi="आधार ई-केवाईसी सत्यापित (Aadhaar Verified ✓)" data-en="Aadhaar Verified ✓">Aadhaar Verified ✓</strong>
                <span id="verified-aadhaar-text" style="margin-left: 8px; font-family: monospace; font-weight: 700;"></span>
              </div>

              <div id="otp-msg-box" style="margin-top: 10px; display: none;"></div>
            </div>

            <!-- PANEL B: Email OTP -->
            <div class="aadhaar-verify-box" id="verify-panel-email" style="display: none;">
              <div class="form-group" style="margin-bottom: 10px;">
                <label class="form-label-gov" for="email_otp_input_display">
                  <span data-hi="ईमेल आईडी (ऊपर दर्ज की गई)" data-en="Email ID (entered above)">Email ID (entered above)</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text input-group-addon-gov"><i class="fas fa-envelope"></i></span>
                  <input type="text" id="email_otp_input_display" class="form-control form-control-gov" readonly
                         style="background: #f1f5f9; color: #64748b; cursor: not-allowed;"
                         placeholder="Fill the Email ID field in Section 1 first">
                  <button type="button" id="btn-edit-email" class="btn btn-gov-outline" style="border-radius: 0 6px 6px 0;">
                    <i class="fas fa-pen"></i> <span data-hi="बदलें" data-en="Edit">Edit</span>
                  </button>
                </div>
                <small class="text-muted" data-hi="यह फील्ड केवल दिखाने के लिए है और टाइप नहीं की जा सकती — कृपया पहले ऊपर सेक्शन 1 में 'ईमेल आईडी' फील्ड भरें/बदलें।" data-en="This field is read-only and cannot be typed into — please fill or edit the 'Email ID' field in Section 1 above.">This field is read-only and cannot be typed into — please fill or edit the 'Email ID' field in Section 1 above.</small>
              </div>

              <!-- Aadhaar Number (record only — not used for OTP when verifying via Email) -->
              <div class="form-group" style="margin-bottom: 10px;">
                <label class="form-label-gov" for="aadhaar_input_email">
                  <span data-hi="१२ अंकों का आधार नंबर" data-en="12-Digit Aadhaar No">12-Digit Aadhaar No</span> <span class="req">*</span>
                </label>
                <div class="input-group">
                  <span class="input-group-text input-group-addon-gov"><i class="fas fa-id-card"></i></span>
                  <input type="text" id="aadhaar_input_email" name="aadhaar_number_manual" class="form-control form-control-gov"
                         placeholder="XXXX XXXX XXXX" maxlength="14" autocomplete="off" required>
                </div>
                <small class="text-muted" data-hi="ईमेल ओटीपी से सत्यापन करते समय भी रिकॉर्ड हेतु आधार नंबर आवश्यक है (यह ओटीपी के लिए उपयोग नहीं होगा)।" data-en="Aadhaar number is required for record-keeping even when verifying via Email OTP (it won't be used for OTP).">Aadhaar number is required for record-keeping even when verifying via Email OTP (it won't be used for OTP).</small>
              </div>

              <!-- OTP Trigger Button -->
              <div style="margin-top: 10px;">
                <button type="button" id="btn-send-email-otp" class="btn btn-gov-outline">
                  <i class="fas fa-paper-plane"></i> <span data-hi="ईमेल ओटीपी भेजें" data-en="Send Email OTP">Send Email OTP</span>
                </button>
                <span id="email-otp-timer-badge" class="badge-gov-saffron" style="display:none; margin-left: 8px; font-size: 0.85rem;">60s</span>
              </div>

              <!-- OTP Input Row (Hidden initially) -->
              <div id="email-otp-verify-row" style="display: none; margin-top: 16px; padding-top: 14px; border-top: 1px dashed #cbd5e1;">
                <div class="row align-items-center">
                  <div class="col-md-6 col-sm-12">
                    <label class="form-label-gov" for="email_otp_input">
                      <span data-hi="प्राप्त ६ अंकों का ओटीपी दर्ज करें" data-en="Enter 6-Digit OTP">Enter 6-Digit OTP</span> <span class="req">*</span>
                    </label>
                    <input type="text" id="email_otp_input" class="form-control form-control-gov"
                           placeholder="123456" maxlength="6" style="letter-spacing: 4px; font-weight: 700; font-size: 1.1rem;">
                    <div id="email-otp-sandbox-hint" class="text-success" style="font-size: 0.8rem; margin-top: 4px; font-weight: 600;"></div>
                  </div>
                  <div class="col-md-6 col-sm-12" style="margin-top: 24px;">
                    <button type="button" id="btn-verify-email-otp" class="btn btn-gov-verify">
                      <i class="fas fa-check-circle"></i> <span data-hi="सत्यापित करें" data-en="Verify OTP">Verify OTP</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Verified Success State -->
              <div id="email-verified-badge" style="display: none; margin-top: 14px;" class="alert alert-success">
                <i class="fas fa-check-circle fa-lg"></i>
                <strong style="margin-left: 6px;" data-hi="ईमेल सत्यापित (Email Verified ✓)" data-en="Email Verified ✓">Email Verified ✓</strong>
                <span id="verified-email-text" style="margin-left: 8px; font-family: monospace; font-weight: 700;"></span>
              </div>

              <div id="email-otp-msg-box" style="margin-top: 10px; display: none;"></div>
            </div>
          </div>
        </div>

        <!-- SECTION 3: Cards, Health IDs & Document Uploads -->
        <div class="ayush-card-box">
          <div class="ayush-card-header">
            <i class="fas fa-file-medical-alt"></i>
            <span data-hi="३. स्वास्थ्य आईडी एवं सरकारी दस्तावेज" data-en="3. Health IDs &amp; Document Uploads">3. Health IDs &amp; Document Uploads</span>
          </div>
          <div class="ayush-card-body">

            <!-- Row A: ABHA Number & ABHA Card Upload -->
            <div class="row ayush-field-group">
              <div class="col-md-6 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="abha_number" data-hi="१४ अंकों का आभा नंबर" data-en="14-Digit ABHA Number">14-Digit ABHA Number</label>
                  <input type="text" name="abha_number" id="abha_number" class="form-control form-control-gov"
                         placeholder="12-3456-7890-1234" maxlength="17"
                         value="<?php echo htmlspecialchars($_POST['abha_number'] ?? ''); ?>">
                  <small class="text-muted" data-hi="Ayushman Bharat Health Account (ABHA) संख्या।" data-en="Ayushman Bharat Health Account (ABHA) number.">Ayushman Bharat Health Account (ABHA) number.</small>
                </div>
              </div>
              <div class="col-md-6 col-sm-12">
                <label class="form-label-gov" data-hi="आभा कार्ड अपलोड करें" data-en="Upload ABHA Card">Upload ABHA Card</label>
                <div class="file-upload-tile">
                  <input type="file" name="abha_card" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'abha-file-name')">
                  <i class="fas fa-id-card"></i>
                  <div class="tile-title" data-hi="आभा कार्ड चुनें (Image / PDF)" data-en="Choose ABHA Card (Image / PDF)">Choose ABHA Card (Image / PDF)</div>
                  <div class="tile-hint" data-hi="अधिकतम 10MB" data-en="Max 10MB">Max 10MB</div>
                  <div id="abha-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row B: Ayushman Bharat Card Upload -->
            <div class="row ayush-field-group">
              <div class="col-md-12">
                <label class="form-label-gov" data-hi="आयुष्मान भारत कार्ड अपलोड करें" data-en="Upload Ayushman Bharat Card">Upload Ayushman Bharat Card</label>
                <div class="file-upload-tile">
                  <input type="file" name="ayushman_card" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'ayush-card-file-name')">
                  <i class="fas fa-heartbeat"></i>
                  <div class="tile-title" data-hi="आयुष्मान कार्ड (PMJAY Golden Card) अपलोड करें" data-en="Upload Ayushman Card (PMJAY Golden Card)">Upload Ayushman Card (PMJAY Golden Card)</div>
                  <div class="tile-hint" data-hi="JPG, PNG या PDF (अधिकतम 10MB)" data-en="JPG, PNG or PDF (max 10MB)">JPG, PNG or PDF (max 10MB)</div>
                  <div id="ayush-card-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row C: Ration Card Number & Ration Card Upload -->
            <div class="row ayush-field-group">
              <div class="col-md-6 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="ration_card_no" data-hi="राशन कार्ड नंबर" data-en="Ration Card Number">Ration Card Number</label>
                  <input type="text" name="ration_card_no" id="ration_card_no" class="form-control form-control-gov"
                         placeholder="e.g. 072001839210" data-hi-placeholder="जैसे: 072001839210" data-en-placeholder="e.g. 072001839210"
                         value="<?php echo htmlspecialchars($_POST['ration_card_no'] ?? ''); ?>">
                  <small class="text-muted" data-hi="NFSA / राज्य खाद्य एवं आपूर्ति राशन कार्ड संख्या।" data-en="NFSA / State Food &amp; Supplies Ration Card number.">NFSA / State Food &amp; Supplies Ration Card number.</small>
                </div>
              </div>
              <div class="col-md-6 col-sm-12">
                <label class="form-label-gov" data-hi="राशन कार्ड अपलोड करें" data-en="Upload Ration Card">Upload Ration Card</label>
                <div class="file-upload-tile">
                  <input type="file" name="ration_card" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'ration-file-name')">
                  <i class="fas fa-wheat-awn"></i>
                  <div class="tile-title" data-hi="राशन कार्ड चुनें (Image / PDF)" data-en="Choose Ration Card (Image / PDF)">Choose Ration Card (Image / PDF)</div>
                  <div class="tile-hint" data-hi="अधिकतम 10MB" data-en="Max 10MB">Max 10MB</div>
                  <div id="ration-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row D: BOCW Registration Number & BOCW Card Upload -->
            <div class="row ayush-field-group">
              <div class="col-md-6 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="bocw_reg_no" data-hi="BOCW पंजीकरण संख्या" data-en="BOCW Registration Number">BOCW Registration Number</label>
                  <input type="text" name="bocw_reg_no" id="bocw_reg_no" class="form-control form-control-gov"
                         placeholder="e.g. BOCW/DL/2024/91823" data-hi-placeholder="जैसे: BOCW/DL/2024/91823" data-en-placeholder="e.g. BOCW/DL/2024/91823"
                         value="<?php echo htmlspecialchars($_POST['bocw_reg_no'] ?? ''); ?>">
                  <small class="text-muted" data-hi="भवन एवं अन्य सन्निर्माण कर्मकार कल्याण बोर्ड / श्रमिक कार्ड संख्या।" data-en="Building &amp; Other Construction Workers Welfare Board / Labour Card number.">Building &amp; Other Construction Workers Welfare Board / Labour Card number.</small>
                </div>
              </div>
              <div class="col-md-6 col-sm-12">
                <label class="form-label-gov" data-hi="BOCW / श्रमिक कार्ड अपलोड करें" data-en="Upload Labour Card">Upload Labour Card</label>
                <div class="file-upload-tile">
                  <input type="file" name="bocw_card" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'bocw-file-name')">
                  <i class="fas fa-hard-hat"></i>
                  <div class="tile-title" data-hi="BOCW / श्रमिक कार्ड चुनें (Image / PDF)" data-en="Choose BOCW / Labour Card (Image / PDF)">Choose BOCW / Labour Card (Image / PDF)</div>
                  <div class="tile-hint" data-hi="अधिकतम 10MB" data-en="Max 10MB">Max 10MB</div>
                  <div id="bocw-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row E: PMJAY Letter Number & PMJAY Letter Upload -->
            <div class="row ayush-field-group">
              <div class="col-md-6 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="pmjay_letter_no" data-hi="PMJAY / आयुष्मान भारत पत्र संख्या" data-en="PMJAY Letter Number">PMJAY Letter Number</label>
                  <input type="text" name="pmjay_letter_no" id="pmjay_letter_no" class="form-control form-control-gov"
                         placeholder="e.g. PMJAY-LTR-2024-8841" data-hi-placeholder="जैसे: PMJAY-LTR-2024-8841" data-en-placeholder="e.g. PMJAY-LTR-2024-8841"
                         value="<?php echo htmlspecialchars($_POST['pmjay_letter_no'] ?? ''); ?>">
                  <small class="text-muted" data-hi="प्रधानमंत्री जन आरोग्य योजना पत्र / संदर्भ संख्या।" data-en="Pradhan Mantri Jan Arogya Yojana letter / reference number.">Pradhan Mantri Jan Arogya Yojana letter / reference number.</small>
                </div>
              </div>
              <div class="col-md-6 col-sm-12">
                <label class="form-label-gov" data-hi="PMJAY पत्र अपलोड करें" data-en="Upload Ayushman Bharat Letter">Upload Ayushman Bharat Letter</label>
                <div class="file-upload-tile">
                  <input type="file" name="pmjay_letter" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'pmjay-file-name')">
                  <i class="fas fa-envelope-open-text"></i>
                  <div class="tile-title" data-hi="PMJAY पत्र चुनें (Image / PDF)" data-en="Choose PMJAY Letter (Image / PDF)">Choose PMJAY Letter (Image / PDF)</div>
                  <div class="tile-hint" data-hi="अधिकतम 10MB" data-en="Max 10MB">Max 10MB</div>
                  <div id="pmjay-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- SECTION 4: Address Details -->
        <div class="ayush-card-box">
          <div class="ayush-card-header">
            <i class="fas fa-map-marker-alt"></i>
            <span data-hi="४. नागरिक का स्थायी / वर्तमान पता" data-en="4. Residential Address">4. Residential Address</span>
          </div>
          <div class="ayush-card-body">
            <div class="form-group">
              <label class="form-label-gov" for="address" data-hi="मकान / ग्राम / गली का पूरा पता" data-en="Full Address">Full Address</label>
              <textarea name="address" id="address" rows="2" class="form-control form-control-gov-area" placeholder="House No, Street / Mohalla, Ward / Gram Panchayat..." data-hi-placeholder="मकान नं, गली / मोहल्ला, वार्ड / ग्राम पंचायत..." data-en-placeholder="House No, Street / Mohalla, Ward / Gram Panchayat..."><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>
            <div class="row">
              <div class="col-md-4 col-sm-4">
                <div class="form-group">
                  <label class="form-label-gov" for="state" data-hi="राज्य" data-en="State">State</label>
                  <input type="text" name="state" id="state" class="form-control form-control-gov" placeholder="e.g. Delhi / Uttar Pradesh" value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
                </div>
              </div>
              <div class="col-md-4 col-sm-4">
                <div class="form-group">
                  <label class="form-label-gov" for="district" data-hi="जिला" data-en="District">District</label>
                  <input type="text" name="district" id="district" class="form-control form-control-gov" placeholder="e.g. Central Delhi" value="<?php echo htmlspecialchars($_POST['district'] ?? ''); ?>">
                </div>
              </div>
              <div class="col-md-4 col-sm-4">
                <div class="form-group">
                  <label class="form-label-gov" for="pincode" data-hi="पिनकोड" data-en="Pincode">Pincode</label>
                  <input type="text" name="pincode" id="pincode" maxlength="6" class="form-control form-control-gov" placeholder="110001" value="<?php echo htmlspecialchars($_POST['pincode'] ?? ''); ?>">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 5: Terms, Conditions & Consent -->
        <div class="ayush-card-box" style="border-left: 5px solid #f59e0b;">
          <div class="ayush-card-header header-warning">
            <i class="fas fa-file-contract"></i>
            <span data-hi="५. नियम, शर्तें एवं स्व-घोषणा सहमति" data-en="5. Terms &amp; Consent">5. Terms &amp; Consent</span>
          </div>
          <div class="ayush-card-body">
            <div class="form-check" style="margin-top: 0; padding-left: 1.6em;">
              <input type="checkbox" class="form-check-input" name="consent_accepted" id="consent_accepted" value="1" <?php echo (!empty($_POST['consent_accepted']) || !isset($_POST['submit_ayushman'])) ? 'checked' : ''; ?> required>
              <label class="form-check-label" for="consent_accepted" style="font-weight: 600; color: #1e293b; font-size: 0.94rem; line-height: 1.6;" data-hi="मैं स्वेच्छा से अपनी दी गई जानकारी और अपलोड किए गए दस्तावेज़—आभा कार्ड, आयुष्मान भारत कार्ड, राशन कार्ड, BOCW कार्ड और PMJAY / आयुष्मान भारत पत्र—की प्रतियों को विवरण मिलान और Digital Health Smart ID तैयार करने के लिए उपयोग करने की सहमति देता/देती हूँ। मुझे ज्ञात है कि यह एक निजी डेटा फॉर्मेटिंग सेवा है। मेरे द्वारा दी गई जानकारी की सत्यता की जिम्मेदारी मेरी है।" data-en="I voluntarily consent to the use of my submitted information and uploaded documents—ABHA Card, Ayushman Bharat Card, Ration Card, BOCW Card and PMJAY / Ayushman Bharat Letter—for checking the submitted details and preparing my Digital Health Smart ID. I understand that this is a private data formatting service. I am responsible for the accuracy of the information I provide.">
                I voluntarily consent to the use of my submitted information and uploaded documents—ABHA Card, Ayushman Bharat Card, Ration Card, BOCW Card and PMJAY / Ayushman Bharat Letter—for checking the submitted details and preparing my Digital Health Smart ID. I understand that this is a private data formatting service. I am responsible for the accuracy of the information I provide.
              </label>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center" style="margin-top: 28px; margin-bottom: 20px;">
          <button type="submit" id="btn-submit-main" class="btn btn-gov-submit">
            <i class="fas fa-check-double"></i> <span data-hi="सबमिट करें एवं डिजिटल हेल्थ स्मार्ट आईडी कार्ड जनरेट करें" data-en="Submit &amp; Generate Digital Health Smart ID Card">Submit &amp; Generate Digital Health Smart ID Card</span>
          </button>
          <div style="font-size: 0.82rem; color: #64748b; margin-top: 8px;">
            <i class="fas fa-lock" style="color: #10b981;"></i> 256-Bit Military Grade Encrypted • UIDAI Aadhaar Verification • NHA Facilitation
          </div>
        </div>

      </div>

      <!-- Right Column: Security Guidance & Info Box -->
      <div class="col-lg-4 col-md-4 col-sm-12">
        <div class="ayush-sidebar-sticky">

          <!-- About Card -->
          <div class="ayush-card-box" style="border-top: 4px solid #10b981;">
            <div class="ayush-card-body">
              <div style="text-align: center; margin-bottom: 10px;">
                <i class="fas fa-circle-info fa-2x ayush-side-icon"></i>
                <h4 class="ayush-side-title" style="font-size: 0.95rem;" data-hi="आयुष्मान हेल्प डेस्क के बारे में" data-en="About Ayushman Help Desk">About Ayushman Help Desk</h4>
                <span class="security-encrypted-badge">DPDP Act 2023 Compliant</span>
              </div>
              <p style="font-size: 0.8rem; color: #334155; line-height: 1.5; margin-bottom: 10px;" data-hi="PM-JAY केंद्र सरकार की विश्व की सबसे बड़ी स्वास्थ्य बीमा योजना है — पात्र परिवारों को ₹5 लाख/वर्ष तक कैशलेस अस्पताल कवर। यह हेल्प डेस्क आपकी पहचान सत्यापित कर एक डिजिटल स्मार्ट आईडी बनाता है, जिससे आप इस योजना का लाभ आसानी से क्लेम कर सकें। यह ABDM डिजिटल हेल्थ इकोसिस्टम के अंतर्गत कार्य करता है।" data-en="PM-JAY is the Central Government's flagship health insurance scheme — the world's largest — giving eligible families cashless hospital cover up to ₹5 lakh/year. This Help Desk verifies your identity and generates a Digital Smart ID so you can claim that benefit easily. Operates under the ABDM digital health ecosystem.">
                PM-JAY is the Central Government's flagship health insurance scheme — the world's largest — giving eligible families cashless hospital cover up to ₹5 lakh/year. This Help Desk verifies your identity and generates a Digital Smart ID so you can claim that benefit easily. Operates under the ABDM digital health ecosystem.
              </p>
              <h5 style="margin: 10px 0 0; font-weight: 700; color: #1e293b; font-size: 0.8rem;" data-hi="इसका उपयोग कैसे करें" data-en="How to use this">How to use this</h5>
              <ol class="ayush-about-steps" style="margin-top: 8px;">
                <li data-hi="आधार ओटीपी अथवा ईमेल ओटीपी से अपनी पहचान सत्यापित करें।" data-en="Verify your identity using Aadhaar OTP or Email OTP.">Verify your identity using Aadhaar OTP or Email OTP.</li>
                <li data-hi="अपना व्यक्तिगत विवरण, स्वास्थ्य आईडी एवं पता भरें।" data-en="Fill in your personal details, health IDs and address.">Fill in your personal details, health IDs and address.</li>
                <li data-hi="अपने उपलब्ध दस्तावेजों की स्पष्ट फोटो अपलोड करें (अधिकतम 10MB)।" data-en="Upload clear photos of your available documents (max 10MB).">Upload clear photos of your available documents (max 10MB).</li>
                <li data-hi="सहमति स्वीकार करें और सबमिट करें — कार्ड तुरंत ईमेल पर भेजा जाएगा।" data-en="Accept the consent and submit — the card is emailed to you instantly.">Accept the consent and submit — the card is emailed to you instantly.</li>
              </ol>
            </div>
          </div>

          <!-- Helpline Card -->
          <div class="ayush-card-box ayush-helpline-card" style="background: #f8fafc;">
            <div class="ayush-card-body">
              <h5 class="helpline-title" style="font-weight: 700; color: #1e3a8a; margin-top: 0;">
                <i class="fas fa-headset"></i> <span data-hi="सहायता एवं संपर्क केंद्र" data-en="Help &amp; Contact Centre">Help &amp; Contact Centre</span>
              </h5>
              <p style="font-size: 0.84rem; color: #64748b; margin-bottom: 10px;" data-hi="किसी भी तकनीकी सहायता या जानकारी के लिए हमारे आधिकारिक हेल्पलाइन पर संपर्क करें:" data-en="For any technical assistance or information, contact our official helpline:">
                For any technical assistance or information, contact our official helpline:
              </p>
              <div class="helpline-number" style="font-size: 1rem; font-weight: 800; color: #1e3a8a;">
                <i class="fas fa-phone-alt"></i> <?php echo defined('HELPLINE_NUMBER') ? HELPLINE_NUMBER : '1800-11-2026'; ?>
              </div>
              <div class="helpline-email" style="font-size: 0.85rem; color: #475569; margin-top: 4px;">
                <i class="fas fa-envelope"></i> <?php echo defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@citizenportal.facilitator.in'; ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </form>
</div>

<!-- Submission Loader Overlay: shown from form submit until the server redirects to the card page -->
<div id="ayushman-submit-overlay" class="ayushman-submit-overlay" style="display:none;">
  <div class="ayushman-submit-box">
    <div class="ayushman-submit-spinner"></div>
    <h4 data-hi="आवेदन सबमिट किया जा रहा है..." data-en="Submitting your application...">Submitting your application...</h4>
    <div class="ayushman-submit-progress-track">
      <div class="ayushman-submit-progress-bar" id="ayushman-submit-progress-bar"></div>
    </div>
    <div class="ayushman-submit-status" id="ayushman-submit-status">Uploading your details...</div>
    <div class="ayushman-submit-elapsed" id="ayushman-submit-elapsed">0s</div>
    <p data-hi="कृपया प्रतीक्षा करें, इसमें कुछ समय लग सकता है। पेज को बंद न करें, रीफ्रेश न करें और 'वापस' बटन न दबाएं।" data-en="Please wait, this may take a moment. Do not close this page, refresh, or press the back button.">Please wait, this may take a moment. Do not close this page, refresh, or press the back button.</p>
  </div>
</div>

<!-- Scripts for Dynamic File Preview and Aadhaar OTP Flow -->
<script>
// Show the submission loader as soon as the form is actually being sent to the
// server, and keep it up (with a "don't go back" warning) until the server's
// redirect replaces this page — covers the DB write + file uploads + email send.
(function() {
  const form = document.getElementById('ayushman-form');
  const overlay = document.getElementById('ayushman-submit-overlay');
  const submitBtn = document.getElementById('btn-submit-main');
  const progressBar = document.getElementById('ayushman-submit-progress-bar');
  const statusText = document.getElementById('ayushman-submit-status');
  const elapsedText = document.getElementById('ayushman-submit-elapsed');

  // Rotating reassurance messages so the citizen can see the submission is
  // actively progressing through real steps, not just a frozen spinner —
  // timed roughly to how long each phase (upload, verify, encrypt, email)
  // typically takes, but keeps cycling if it runs long.
  const steps = [
    { at: 0,  pct: 15, en: 'Uploading your details...', hi: 'आपका विवरण अपलोड हो रहा है...' },
    { at: 4,  pct: 40, en: 'Verifying documents...', hi: 'दस्तावेज सत्यापित हो रहे हैं...' },
    { at: 9,  pct: 65, en: 'Generating your encrypted Smart ID...', hi: 'आपकी एन्क्रिप्टेड स्मार्ट आईडी बनाई जा रही है...' },
    { at: 15, pct: 85, en: 'Almost done, finalizing your card...', hi: 'लगभग पूर्ण, आपका कार्ड अंतिम रूप लिया जा रहा है...' },
    { at: 25, pct: 92, en: 'This is taking a little longer than usual, please keep waiting...', hi: 'इसमें सामान्य से थोड़ा अधिक समय लग रहा है, कृपया प्रतीक्षा करें...' }
  ];
  let elapsedSeconds = 0;
  let progressTimer = null;

  function startProgressSimulation() {
    const currentLang = (document.documentElement.getAttribute('lang') === 'hi') ? 'hi' : 'en';
    progressTimer = setInterval(function() {
      elapsedSeconds++;
      if (elapsedText) elapsedText.innerText = elapsedSeconds + 's';
      let active = steps[0];
      for (const step of steps) {
        if (elapsedSeconds >= step.at) active = step;
      }
      if (progressBar) progressBar.style.width = active.pct + '%';
      if (statusText) statusText.innerText = active[currentLang] || active.en;
    }, 1000);
  }

  if (form && overlay && submitBtn) {
    form.addEventListener('submit', function() {
      // Let the browser's own required-field validation run first; only show
      // the loader (and lock the button) once the form actually passes and submits.
      if (!form.checkValidity()) {
        return;
      }
      overlay.style.display = 'flex';
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
      startProgressSimulation();
    });
  }

  // Warn if the citizen tries to leave/refresh while a submission is in flight
  window.addEventListener('beforeunload', function(e) {
    if (overlay && overlay.style.display === 'flex') {
      e.preventDefault();
      e.returnValue = '';
    }
  });
})();

// Mobile two-step flow: "Fill Application Form" reveals the real form and scrolls to it
(function() {
  const btnGotoForm = document.getElementById('btn-goto-form');
  const mainContent = document.getElementById('ayushman-main-content');
  if (btnGotoForm && mainContent) {
    btnGotoForm.addEventListener('click', function() {
      mainContent.classList.add('show');
      mainContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }
})();

// Live Image Preview for Citizen Photo
function previewCitizenPhoto(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    if (file.size > 10 * 1024 * 1024) {
      alert("फोटो का आकार 10MB से अधिक नहीं होना चाहिए!");
      input.value = "";
      return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('photo-img-preview').src = e.target.result;
      document.getElementById('photo-img-preview').style.display = 'block';
      document.getElementById('photo-preview-wrap').style.display = 'none';
      document.getElementById('photo-file-name').innerText = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
      document.getElementById('photo-file-name').style.display = 'block';
    }
    reader.readAsDataURL(file);
  }
}

// Live Camera Capture: move the captured photo into the real #photo_input
// (which is what actually submits with the form) and reuse the same preview logic.
function captureCitizenPhotoFromCamera(cameraInput) {
  if (!cameraInput.files || !cameraInput.files[0]) return;
  const capturedFile = cameraInput.files[0];
  const photoInput = document.getElementById('photo_input');

  try {
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(capturedFile);
    photoInput.files = dataTransfer.files;
  } catch (e) {
    alert("इस ब्राउज़र में लाइव कैमरा कैप्चर समर्थित नहीं है। कृपया 'Upload' बटन से फोटो चुनें। (Live camera capture isn't supported in this browser — please use 'Upload' instead.)");
    return;
  }

  previewCitizenPhoto(photoInput);
  cameraInput.value = ""; // reset so the same camera button can be used again
}

// Show selected file name for document inputs
function showFileName(input, targetId) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    if (file.size > 10 * 1024 * 1024) {
      alert("दस्तावेज का आकार 10MB से अधिक नहीं होना चाहिए!");
      input.value = "";
      return;
    }
    const target = document.getElementById(targetId);
    if (target) {
      target.innerText = '✓ ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
      target.style.display = 'block';
    }
  }
}

// Aadhaar OTP Verification AJAX
(function() {
  const aadhaarInput = document.getElementById('aadhaar_input');
  const mobileInput = document.getElementById('mobile');
  const btnSendOtp = document.getElementById('btn-send-otp');
  const otpTimerBadge = document.getElementById('otp-timer-badge');
  const otpVerifyRow = document.getElementById('otp-verify-row');
  const otpInput = document.getElementById('otp_input');
  const btnVerifyOtp = document.getElementById('btn-verify-otp');
  const aadhaarVerifiedBadge = document.getElementById('aadhaar-verified-badge');
  const verifiedAadhaarText = document.getElementById('verified-aadhaar-text');
  const otpMsgBox = document.getElementById('otp-msg-box');
  const otpSandboxHint = document.getElementById('otp-sandbox-hint');

  let countdownInterval = null;

  // Format Aadhaar input with spaces: XXXX XXXX XXXX
  function formatAadhaarInput(el) {
    el.addEventListener('input', function() {
      let val = this.value.replace(/\D/g, '').substring(0, 12);
      let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
      this.value = formatted;
    });
  }
  if (aadhaarInput) {
    formatAadhaarInput(aadhaarInput);
  }
  // Same formatting for the Aadhaar number field shown in the Email OTP panel
  const aadhaarInputEmail = document.getElementById('aadhaar_input_email');
  if (aadhaarInputEmail) {
    formatAadhaarInput(aadhaarInputEmail);
  }

  // 1. Send OTP Click
  if (btnSendOtp) {
    btnSendOtp.addEventListener('click', function() {
      const rawAadhaar = aadhaarInput.value.replace(/\s+/g, '');
      const mobile = mobileInput.value.trim();

      if (rawAadhaar.length !== 12) {
        alert('कृपया 12 अंकों का वैध आधार नंबर दर्ज करें।');
        aadhaarInput.focus();
        return;
      }
      if (mobile.length !== 10) {
        alert('कृपया 10 अंकों का वैध मोबाइल नंबर दर्ज करें।');
        mobileInput.focus();
        return;
      }

      btnSendOtp.disabled = true;
      btnSendOtp.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ओटीपी भेजा जा रहा है...';
      otpMsgBox.style.display = 'none';

      const formData = new FormData();
      formData.append('action', 'send_otp');
      formData.append('aadhaar_number', rawAadhaar);
      formData.append('mobile', mobile);

      fetch('<?php echo BASE_URL; ?>/api/ayushman_aadhaar_otp.php?action=send_otp', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        btnSendOtp.disabled = false;
        btnSendOtp.innerHTML = '<i class="fas fa-redo"></i> पुनः ओटीपी भेजें (Resend OTP)';

        if (data.success) {
          otpVerifyRow.style.display = 'block';
          otpMsgBox.className = 'alert alert-info';
          otpMsgBox.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
          otpMsgBox.style.display = 'block';

          if (data.test_mode && data.demo_otp) {
            otpSandboxHint.innerHTML = '<i class="fas fa-vial"></i> <strong>UIDAI Sandbox OTP:</strong> ' + data.demo_otp + ' (दर्ज करें)';
          }

          // Start 60s countdown
          let seconds = 60;
          otpTimerBadge.style.display = 'inline-block';
          otpTimerBadge.innerText = seconds + 's';
          clearInterval(countdownInterval);
          countdownInterval = setInterval(() => {
            seconds--;
            otpTimerBadge.innerText = seconds + 's';
            if (seconds <= 0) {
              clearInterval(countdownInterval);
              otpTimerBadge.style.display = 'none';
            }
          }, 1000);

        } else {
          otpMsgBox.className = 'alert alert-danger';
          otpMsgBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
          otpMsgBox.style.display = 'block';
        }
      })
      .catch(err => {
        btnSendOtp.disabled = false;
        btnSendOtp.innerHTML = '<i class="fas fa-paper-plane"></i> आधार ओटीपी भेजें';
        alert('सर्वर से संपर्क करने में त्रुटि: ' + err);
      });
    });
  }

  // 2. Verify OTP Click
  if (btnVerifyOtp) {
    btnVerifyOtp.addEventListener('click', function() {
      const otpVal = otpInput.value.trim();
      if (otpVal.length !== 6) {
        alert('कृपया 6 अंकों का ओटीपी दर्ज करें।');
        otpInput.focus();
        return;
      }

      btnVerifyOtp.disabled = true;
      btnVerifyOtp.innerHTML = '<i class="fas fa-spinner fa-spin"></i> सत्यापन हो रहा है...';

      const formData = new FormData();
      formData.append('action', 'verify_otp');
      formData.append('otp', otpVal);

      fetch('<?php echo BASE_URL; ?>/api/ayushman_aadhaar_otp.php?action=verify_otp', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        btnVerifyOtp.disabled = false;
        btnVerifyOtp.innerHTML = '<i class="fas fa-check-circle"></i> सत्यापित करें';

        if (data.success) {
          otpVerifyRow.style.display = 'none';
          btnSendOtp.style.display = 'none';
          otpTimerBadge.style.display = 'none';
          otpMsgBox.style.display = 'none';

          // Lock inputs
          aadhaarInput.readOnly = true;
          mobileInput.readOnly = true;

          aadhaarVerifiedBadge.style.display = 'block';
          verifiedAadhaarText.innerText = 'XXXX-XXXX-' + data.last4;
        } else {
          otpMsgBox.className = 'alert alert-danger';
          otpMsgBox.innerHTML = '<i class="fas fa-times-circle"></i> ' + data.message;
          otpMsgBox.style.display = 'block';
        }
      })
      .catch(err => {
        btnVerifyOtp.disabled = false;
        btnVerifyOtp.innerHTML = '<i class="fas fa-check-circle"></i> सत्यापित करें';
        alert('सत्यापन त्रुटि: ' + err);
      });
    });
  }

  // Check if already verified in session
  fetch('<?php echo BASE_URL; ?>/api/ayushman_aadhaar_otp.php?action=check_status')
    .then(r => r.json())
    .then(status => {
      if (status.verified && status.last4) {
        aadhaarInput.value = 'XXXX XXXX ' + status.last4;
        aadhaarInput.readOnly = true;
        if (status.mobile) {
          mobileInput.value = status.mobile;
          mobileInput.readOnly = true;
        }
        btnSendOtp.style.display = 'none';
        aadhaarVerifiedBadge.style.display = 'block';
        verifiedAadhaarText.innerText = 'XXXX-XXXX-' + status.last4;
      }
    });

})();
</script>

<!-- Verification Method Toggle (Aadhaar OTP vs Email OTP) -->
<script>
(function() {
  const tabAadhaar = document.getElementById('method-tab-aadhaar');
  const tabEmail = document.getElementById('method-tab-email');
  const panelAadhaar = document.getElementById('verify-panel-aadhaar');
  const panelEmail = document.getElementById('verify-panel-email');
  const emailField = document.getElementById('email');
  const emailDisplay = document.getElementById('email_otp_input_display');

  function showMethod(method) {
    const isEmail = method === 'email';
    panelAadhaar.style.display = isEmail ? 'none' : 'block';
    panelEmail.style.display = isEmail ? 'block' : 'none';
    tabAadhaar.classList.toggle('active', !isEmail);
    tabEmail.classList.toggle('active', isEmail);
    if (isEmail && emailField && emailDisplay) {
      emailDisplay.value = emailField.value || '';
    }
  }

  if (tabAadhaar) tabAadhaar.addEventListener('click', function() { showMethod('aadhaar'); });
  if (tabEmail) tabEmail.addEventListener('click', function() { showMethod('email'); });

  // Keep the read-only email display in sync with the real Email ID field in Section 1
  if (emailField && emailDisplay) {
    emailField.addEventListener('input', function() {
      emailDisplay.value = emailField.value;
    });
  }

  // "Edit" button next to the read-only display jumps back to the real, typable field
  const btnEditEmail = document.getElementById('btn-edit-email');
  if (btnEditEmail && emailField) {
    btnEditEmail.addEventListener('click', function() {
      emailField.scrollIntoView({ behavior: 'smooth', block: 'center' });
      emailField.focus();
    });
  }
})();
</script>

<!-- Email OTP Verification AJAX -->
<script>
(function() {
  const emailField = document.getElementById('email');
  const btnSendEmailOtp = document.getElementById('btn-send-email-otp');
  const emailOtpTimerBadge = document.getElementById('email-otp-timer-badge');
  const emailOtpVerifyRow = document.getElementById('email-otp-verify-row');
  const emailOtpInput = document.getElementById('email_otp_input');
  const btnVerifyEmailOtp = document.getElementById('btn-verify-email-otp');
  const emailVerifiedBadge = document.getElementById('email-verified-badge');
  const verifiedEmailText = document.getElementById('verified-email-text');
  const emailOtpMsgBox = document.getElementById('email-otp-msg-box');
  const emailOtpSandboxHint = document.getElementById('email-otp-sandbox-hint');

  let emailCountdownInterval = null;

  // 1. Send Email OTP Click
  if (btnSendEmailOtp) {
    btnSendEmailOtp.addEventListener('click', function() {
      const email = (emailField ? emailField.value : '').trim();

      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Please enter a valid Email ID in Section 1 above first.');
        if (emailField) emailField.focus();
        return;
      }

      btnSendEmailOtp.disabled = true;
      btnSendEmailOtp.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending OTP...';
      emailOtpMsgBox.style.display = 'none';

      const formData = new FormData();
      formData.append('action', 'send_otp');
      formData.append('email', email);

      fetch('<?php echo BASE_URL; ?>/api/ayushman_email_otp.php?action=send_otp', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        btnSendEmailOtp.disabled = false;
        btnSendEmailOtp.innerHTML = '<i class="fas fa-redo"></i> Resend OTP';

        if (data.success) {
          emailOtpVerifyRow.style.display = 'block';
          emailOtpMsgBox.className = 'alert alert-info';
          emailOtpMsgBox.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
          emailOtpMsgBox.style.display = 'block';

          if (data.test_mode && data.demo_otp) {
            emailOtpSandboxHint.innerHTML = '<i class="fas fa-vial"></i> <strong>Sandbox OTP:</strong> ' + data.demo_otp + ' (enter this)';
          }

          let seconds = 60;
          emailOtpTimerBadge.style.display = 'inline-block';
          emailOtpTimerBadge.innerText = seconds + 's';
          clearInterval(emailCountdownInterval);
          emailCountdownInterval = setInterval(() => {
            seconds--;
            emailOtpTimerBadge.innerText = seconds + 's';
            if (seconds <= 0) {
              clearInterval(emailCountdownInterval);
              emailOtpTimerBadge.style.display = 'none';
            }
          }, 1000);
        } else {
          emailOtpMsgBox.className = 'alert alert-danger';
          emailOtpMsgBox.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
          emailOtpMsgBox.style.display = 'block';
        }
      })
      .catch(err => {
        btnSendEmailOtp.disabled = false;
        btnSendEmailOtp.innerHTML = '<i class="fas fa-paper-plane"></i> Send Email OTP';
        alert('Error contacting server: ' + err);
      });
    });
  }

  // 2. Verify Email OTP Click
  if (btnVerifyEmailOtp) {
    btnVerifyEmailOtp.addEventListener('click', function() {
      const otpVal = emailOtpInput.value.trim();
      if (otpVal.length !== 6) {
        alert('Please enter the 6-digit OTP.');
        emailOtpInput.focus();
        return;
      }

      btnVerifyEmailOtp.disabled = true;
      btnVerifyEmailOtp.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';

      const formData = new FormData();
      formData.append('action', 'verify_otp');
      formData.append('otp', otpVal);

      fetch('<?php echo BASE_URL; ?>/api/ayushman_email_otp.php?action=verify_otp', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        btnVerifyEmailOtp.disabled = false;
        btnVerifyEmailOtp.innerHTML = '<i class="fas fa-check-circle"></i> Verify OTP';

        if (data.success) {
          emailOtpVerifyRow.style.display = 'none';
          btnSendEmailOtp.style.display = 'none';
          emailOtpTimerBadge.style.display = 'none';
          emailOtpMsgBox.style.display = 'none';

          if (emailField) emailField.readOnly = true;

          emailVerifiedBadge.style.display = 'block';
          verifiedEmailText.innerText = data.email || '';
        } else {
          emailOtpMsgBox.className = 'alert alert-danger';
          emailOtpMsgBox.innerHTML = '<i class="fas fa-times-circle"></i> ' + data.message;
          emailOtpMsgBox.style.display = 'block';
        }
      })
      .catch(err => {
        btnVerifyEmailOtp.disabled = false;
        btnVerifyEmailOtp.innerHTML = '<i class="fas fa-check-circle"></i> Verify OTP';
        alert('Verification error: ' + err);
      });
    });
  }

  // Check if already verified in session
  fetch('<?php echo BASE_URL; ?>/api/ayushman_email_otp.php?action=check_status')
    .then(r => r.json())
    .then(status => {
      if (status.verified && status.email) {
        if (emailField) emailField.readOnly = true;
        btnSendEmailOtp.style.display = 'none';
        emailVerifiedBadge.style.display = 'block';
        verifiedEmailText.innerText = status.email;
      }
    });

})();
</script>

<!-- Language Switcher (default: English) -->
<script>
(function () {
  function applyLang(lang) {
    document.querySelectorAll('[data-hi][data-en]').forEach(function (el) {
      el.textContent = (lang === 'hi') ? el.getAttribute('data-hi') : el.getAttribute('data-en');
    });
    document.querySelectorAll('[data-hi-placeholder][data-en-placeholder]').forEach(function (el) {
      el.setAttribute('placeholder', (lang === 'hi') ? el.getAttribute('data-hi-placeholder') : el.getAttribute('data-en-placeholder'));
    });
    document.querySelectorAll('.lang-toggle-btn').forEach(function (btn) {
      btn.classList.toggle('active', btn.getAttribute('data-lang-val') === lang);
    });
    document.documentElement.setAttribute('lang', (lang === 'hi') ? 'hi' : 'en');
    try { localStorage.setItem('ayush_lang', lang); } catch (e) {}
  }

  var savedLang = 'en';
  try { savedLang = localStorage.getItem('ayush_lang') || 'en'; } catch (e) {}

  document.addEventListener('DOMContentLoaded', function () {
    if (savedLang === 'hi') {
      applyLang('hi');
    }
    document.querySelectorAll('.lang-toggle-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        applyLang(btn.getAttribute('data-lang-val'));
      });
    });
  });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
