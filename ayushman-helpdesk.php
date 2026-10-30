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

    // 3. Aadhaar Verification Check
    $authSession = $_SESSION['ayushman_aadhaar_auth'] ?? null;
    $aadhaarVerified = !empty($authSession['verified']) && $authSession['verified'] === true;
    $aadhaarLast4 = $authSession['aadhaar_last4'] ?? null;

    if (!$aadhaarVerified) {
        $errors[] = 'कृपया फॉर्म सबमिट करने से पहले आधार ओटीपी सत्यापन पूरा करें (Aadhaar OTP verification is mandatory).';
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

            // Prepare cryptographic payload
            $qrData = [
                'v'              => 1,
                'app_no'         => $appNo,
                'card_id'        => $cardId,
                'full_name'      => $fullName,
                'dob'            => $dob,
                'gender'         => $gender,
                'mobile'         => $mobile,
                'email'          => $email,
                'address'        => $address,
                'state'          => $state,
                'district'       => $district,
                'pincode'        => $pincode,
                'aadhaar_last4'  => $aadhaarLast4,
                'aadhaar_verified'=> 1,
                'abha_number'    => $abhaNumber,
                'ration_card_no' => $rationCardNo,
                'bocw_reg_no'    => $bocwRegNo,
                'pmjay_letter_no'=> $pmjayLetterNo,
                'created_at'     => date('Y-m-d H:i:s')
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
                1,
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

            // Dispatch confirmation email
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

            // Clear session verification to prevent replay
            unset($_SESSION['ayushman_aadhaar_auth']);

            // Redirect to Card View
            header("Location: " . BASE_URL . "/ayushman-card.php?app_no=" . urlencode($appNo) . "&status=success");
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
    background: linear-gradient(135deg, #1e3a8a 0%, #1e5a87 60%, #0891b2 100%);
    color: #ffffff;
    padding: 36px 0 26px;
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
    font-size: 2.05rem;
    font-weight: 800;
    margin: 0 0 8px;
    line-height: 1.25;
    color: #ffffff;
}
.ayush-hero-banner .hero-subtext {
    margin: 0;
    font-size: 0.98rem;
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
    padding: 14px 20px;
    font-weight: 700;
    font-size: 1.05rem;
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
    padding: 22px;
}
.form-label-gov {
    font-weight: 600;
    color: #334155;
    font-size: 0.92rem;
    margin-bottom: 6px;
    display: block;
}
.form-label-gov span.req {
    color: #dc2626;
    font-weight: 800;
}
.form-control-gov {
    height: 44px;
    border-radius: 6px;
    border: 1.5px solid #cbd5e1;
    font-size: 0.94rem;
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
    padding: 16px 14px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 110px;
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
    font-size: 1.5rem;
    color: #1e3a8a;
}
.file-upload-tile .tile-title {
    font-weight: 600;
    font-size: 0.88rem;
    color: #334155;
    margin-top: 8px;
}
.file-upload-tile .tile-hint {
    font-size: 0.75rem;
    color: #64748b;
    margin-top: 2px;
}
.file-upload-tile .file-selected-name {
    font-size: 0.82rem;
    color: #047857;
    font-weight: 600;
    margin-top: 8px;
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
    font-weight: 700;
    padding: 9px 18px;
    border-radius: 6px;
    transition: all 0.2s;
}
.btn-gov-outline:hover {
    background: #1e3a8a;
    color: #ffffff;
}
.btn-gov-outline i,
.btn-gov-verify i {
    margin-right: 6px;
}
.btn-gov-verify {
    font-weight: 700;
    padding: 10px 22px;
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
    font-size: 1.12rem;
    font-weight: 800;
    padding: 14px 32px;
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(22, 163, 74, 0.35);
    transition: all 0.2s;
    letter-spacing: 0.3px;
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
@media (max-width: 768px) {
    .ayush-hero-banner { padding: 24px 0 18px; }
    .ayush-card-body { padding: 16px; }
    .btn-gov-submit { width: 100%; font-size: 1rem; }
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
        <p class="hero-subtext" data-hi="नागरिक सुविधा केंद्र: आयुष्मान भारत, आभा आईडी, राशन कार्ड व BOCW कार्ड विवरण दर्ज करें और अपना सुरक्षित एन्क्रिप्टेड डिजिटल हेल्थ स्मार्ट आईडी कार्ड प्राप्त करें।" data-en="Citizen Facilitation Centre: Enter your Ayushman Bharat, ABHA ID, Ration Card and BOCW Card details to get your secure, encrypted Digital Health Smart ID card.">
          Citizen Facilitation Centre: Enter your Ayushman Bharat, ABHA ID, Ration Card and BOCW Card details to get your secure, encrypted Digital Health Smart ID card.
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

<!-- Main Container -->
<div class="container" style="padding-top: 32px; padding-bottom: 50px;">
  
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
                  <input type="email" name="email" id="email" class="form-control form-control-gov"
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
            <div class="aadhaar-verify-box">
              <div class="row align-items-center">
                <div class="col-md-6 col-sm-12">
                  <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label-gov" for="aadhaar_input">
                      <span data-hi="१२ अंकों का आधार नंबर" data-en="12-Digit Aadhaar No">12-Digit Aadhaar No</span> <span class="req">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-text input-group-addon-gov"><i class="fas fa-id-card"></i></span>
                      <input type="text" id="aadhaar_input" class="form-control form-control-gov"
                             placeholder="XXXX XXXX XXXX" maxlength="14" autocomplete="off">
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
              <label class="form-check-label" for="consent_accepted" style="font-weight: 600; color: #1e293b; font-size: 0.94rem; line-height: 1.6;" data-hi="मैं स्वेच्छा से अपनी दी गई जानकारी और अपलोड किए गए दस्तावेज़ डिजिटल हेल्थ स्मार्ट आईडी बनाने और सत्यापन के लिए प्रस्तुत कर रहा हूँ। मेरे द्वारा दी गई सभी जानकारी पूर्णतया सत्य और वैध है।" data-en="I voluntarily submit my provided information and uploaded documents for generating and verifying the Digital Health Smart ID. All information provided by me is completely true and valid.">
                I voluntarily submit my provided information and uploaded documents for generating and verifying the Digital Health Smart ID. All information provided by me is completely true and valid.
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

          <!-- Security Notice Card -->
          <div class="ayush-card-box" style="border-top: 4px solid #10b981;">
            <div class="ayush-card-body">
              <div style="text-align: center; margin-bottom: 12px;">
                <i class="fas fa-shield-alt fa-3x ayush-side-icon"></i>
                <h4 class="ayush-side-title" data-hi="एन्क्रिप्टेड क्यूआर कोड सुरक्षा" data-en="Encrypted QR Code Security">Encrypted QR Code Security</h4>
                <span class="security-encrypted-badge">DPDP Act 2023 Compliant</span>
              </div>
              <p style="font-size: 0.86rem; color: #334155; line-height: 1.55; margin-bottom: 12px;" data-hi="आपके डिजिटल हेल्थ स्मार्ट आईडी कार्ड पर उत्पन्न होने वाला QR कोड AES-256-CBC एवं HMAC-SHA256 द्वारा एन्क्रिप्टेड होगा।" data-en="The QR code generated on your Digital Health Smart ID card will be encrypted using AES-256-CBC and HMAC-SHA256.">
                The QR code generated on your Digital Health Smart ID card will be encrypted using AES-256-CBC and HMAC-SHA256.
              </p>
              <div class="ayush-side-note">
                <i class="fas fa-info-circle"></i> <strong data-hi="सुरक्षा सूचना:" data-en="Security Notice:">Security Notice:</strong> <span data-hi="आम पब्लिक स्कैनर से स्कैन करने पर व्यक्तिगत जानकारी नहीं दिखेगी। केवल अधिकृत प्रशासनिक अधिकारी अपने Admin Scanner से ही डेटा डिक्रिप्ट और सत्यापित कर सकते हैं।" data-en="Scanning with a public scanner will not reveal any personal information. Only authorized administrative officers can decrypt and verify the data using the Admin Scanner.">Scanning with a public scanner will not reveal any personal information. Only authorized administrative officers can decrypt and verify the data using the Admin Scanner.</span>
              </div>
              <ul style="padding-left: 18px; margin: 0; font-size: 0.84rem; color: #475569; line-height: 1.6;">
                <li data-hi="आधार ई-केवाईसी सत्यापन आवश्यक है।" data-en="Aadhaar e-KYC verification is mandatory.">Aadhaar e-KYC verification is mandatory.</li>
                <li data-hi="दस्तावेज स्पष्ट एवं पठनीय होने चाहिए (अधिकतम 10MB)।" data-en="Documents must be clear and legible (max 10MB).">Documents must be clear and legible (max 10MB).</li>
                <li data-hi="कार्ड की डिजिटल प्रति तुरंत ईमेल पर भेजी जाएगी।" data-en="A digital copy of the card will be emailed to you instantly.">A digital copy of the card will be emailed to you instantly.</li>
              </ul>
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

<!-- Scripts for Dynamic File Preview and Aadhaar OTP Flow -->
<script>
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
  if (aadhaarInput) {
    aadhaarInput.addEventListener('input', function(e) {
      let val = this.value.replace(/\D/g, '').substring(0, 12);
      let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
      this.value = formatted;
    });
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
