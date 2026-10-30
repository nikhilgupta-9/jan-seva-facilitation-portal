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
/* Modern Government Ayushman Help Desk Styling */
.ayush-hero-banner {
    background: linear-gradient(135deg, #0b3c5d 0%, #1e5a87 60%, #0891b2 100%);
    color: #ffffff;
    padding: 38px 0 28px;
    border-bottom: 4px solid var(--gov-saffron, #f59e0b);
    position: relative;
    overflow: hidden;
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
    color: #0b3c5d;
    display: flex;
    align-items: center;
    gap: 10px;
}
.ayush-card-body {
    padding: 22px;
}
.form-label-gov {
    font-weight: 600;
    color: #1e293b;
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
    border-color: #0b3c5d;
    box-shadow: 0 0 0 3px rgba(11, 60, 93, 0.12) !important;
}
.file-upload-tile {
    border: 2px dashed #cbd5e1;
    background: #f8fafc;
    border-radius: 8px;
    padding: 14px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
}
.file-upload-tile:hover {
    border-color: #0b3c5d;
    background: #f0fdf4;
}
.file-upload-tile input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}
.file-upload-tile .tile-title {
    font-weight: 600;
    font-size: 0.88rem;
    color: #334155;
    margin-top: 6px;
}
.file-upload-tile .tile-hint {
    font-size: 0.75rem;
    color: #64748b;
}
.file-upload-tile .file-selected-name {
    font-size: 0.82rem;
    color: #059669;
    font-weight: 600;
    margin-top: 6px;
    word-break: break-all;
}
.aadhaar-verify-box {
    background: #f0f9ff;
    border: 1.5px solid #bae6fd;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 20px;
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
.btn-gov-submit {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    color: #ffffff;
    font-size: 1.12rem;
    font-weight: 800;
    padding: 14px 32px;
    border: none;
    border-radius: 8px;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: all 0.2s;
    letter-spacing: 0.3px;
}
.btn-gov-submit:hover {
    background: linear-gradient(135deg, #047857 0%, #059669 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.45);
    color: #ffffff;
}
@media (max-width: 768px) {
    .ayush-hero-banner { padding: 25px 0 18px; }
    .ayush-card-body { padding: 16px; }
    .btn-gov-submit { width: 100%; font-size: 1rem; }
}
</style>

<!-- Hero / Breadcrumb Section -->
<section class="ayush-hero-banner">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-8 col-sm-12">
        <ol class="breadcrumb" style="background: transparent; margin: 0 0 10px; padding: 0; font-size: 0.85rem;">
          <li><a href="<?php echo BASE_URL; ?>/index.php" style="color: #cbd5e1;"><i class="fas fa-home"></i> होम</a></li>
          <li style="color: #f8fafc;" class="active">आयुष्मान हेल्प डेस्क (Ayushman Help Desk)</li>
        </ol>
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
          <span class="badge" style="background: #f59e0b; color: #1e293b; font-weight: 700; font-size: 0.78rem;">
            <i class="fas fa-shield-virus"></i> राष्ट्रीय स्वास्थ्य प्राधिकरण (NHA)
          </span>
          <span class="security-encrypted-badge">
            <i class="fas fa-lock"></i> 256-Bit Encrypted Health Smart ID
          </span>
        </div>
        <h1 style="font-size: 2.1rem; font-weight: 800; margin: 0 0 8px; line-height: 1.25;">
          आयुष्मान हेल्प डेस्क एवं डिजिटल हेल्थ स्मार्ट आईडी
        </h1>
        <p style="margin: 0; font-size: 0.98rem; opacity: 0.92; max-width: 680px;">
          नागरिक सुविधा केंद्र: आयुष्मान भारत, आभा आईडी, राशन कार्ड व BOCW कार्ड विवरण दर्ज करें और अपना सुरक्षित एन्क्रिप्टेड डिजिटल हेल्थ स्मार्ट आईडी कार्ड प्राप्त करें।
        </p>
      </div>
      <div class="col-md-4 col-sm-12 text-right hidden-xs">
        <div style="background: rgba(255,255,255,0.12); backdrop-filter: blur(4px); border: 1px solid rgba(255,255,255,0.22); border-radius: 12px; padding: 18px; text-align: center; color: white;">
          <i class="fas fa-id-card-alt fa-3x" style="color: #fde047; margin-bottom: 8px;"></i>
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
    <div class="alert alert-danger" style="border-radius: 8px; border-left: 5px solid #dc2626; box-shadow: 0 2px 8px rgba(220,38,38,0.1);">
      <h4 style="margin: 0 0 8px; font-weight: 800;"><i class="fas fa-exclamation-triangle"></i> कृपया निम्नलिखित त्रुटियों को सुधारें:</h4>
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
            <i class="fas fa-user-circle text-primary"></i>
            <span>१. मरीज / नागरिक का विवरण (Personal & Patient Details)</span>
          </div>
          <div class="ayush-card-body">
            <div class="row">
              <div class="col-md-7 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="full_name">
                    मरीज का पूरा नाम / Full Name <span class="req">*</span>
                  </label>
                  <input type="text" name="full_name" id="full_name" class="form-control form-control-gov" 
                         placeholder="जैसे: Ramesh Kumar / रमेश कुमार" 
                         value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required>
                  <small class="text-muted">Google Form फील्ड: मरीज का पूरा नाम दर्ज करें।</small>
                </div>

                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label class="form-label-gov" for="dob">जन्म तिथि / Date of Birth</label>
                      <input type="date" name="dob" id="dob" class="form-control form-control-gov" 
                             value="<?php echo htmlspecialchars($_POST['dob'] ?? ''); ?>">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group">
                      <label class="form-label-gov" for="gender">लिंग / Gender</label>
                      <select name="gender" id="gender" class="form-control form-control-gov">
                        <option value="Male" <?php echo (($_POST['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>>पुरुष (Male)</option>
                        <option value="Female" <?php echo (($_POST['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>>महिला (Female)</option>
                        <option value="Other" <?php echo (($_POST['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>>अन्य (Other)</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="form-group">
                  <label class="form-label-gov" for="email">
                    ईमेल आईडी / Email ID <span class="req">*</span>
                  </label>
                  <input type="email" name="email" id="email" class="form-control form-control-gov" 
                         placeholder="yourname@gmail.com" 
                         value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                  <small class="text-muted">सबमिशन के बाद आपका डिजिटल स्मार्ट कार्ड इसी ईमेल पर भेजा जाएगा।</small>
                </div>
              </div>

              <!-- Photo Upload Box -->
              <div class="col-md-5 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov">
                    फोटो अपलोड करें / Upload Your Photo
                  </label>
                  <div class="file-upload-tile" id="photo-tile">
                    <input type="file" name="photo" id="photo_input" accept="image/jpeg,image/png,image/webp" onchange="previewCitizenPhoto(this)">
                    <div id="photo-preview-wrap">
                      <i class="fas fa-camera-retro fa-2x" style="color: #0b3c5d;"></i>
                      <div class="tile-title">पासपोर्ट फोटो चुनें या यहाँ खींचें</div>
                      <div class="tile-hint">JPG, PNG या WEBP (अधिकतम 10MB)</div>
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
          <div class="ayush-card-header" style="background: #f0fdf4; border-bottom-color: #bbf7d0;">
            <i class="fas fa-fingerprint text-success"></i>
            <span style="color: #166534;">२. आधार ई-केवाईसी एवं मोबाइल नंबर सत्यापन (Aadhaar OTP Verification)</span>
          </div>
          <div class="ayush-card-body">
            <div class="aadhaar-verify-box">
              <div class="row align-items-center">
                <div class="col-md-6 col-sm-12">
                  <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label-gov" for="aadhaar_input">
                      १२ अंकों का आधार नंबर / 12-Digit Aadhaar No <span class="req">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-addon" style="background: #ffffff; border-color: #cbd5e1;"><i class="fas fa-id-card text-muted"></i></span>
                      <input type="text" id="aadhaar_input" class="form-control form-control-gov" 
                             placeholder="XXXX XXXX XXXX" maxlength="14" autocomplete="off">
                    </div>
                  </div>
                </div>

                <div class="col-md-6 col-sm-12">
                  <div class="form-group" style="margin-bottom: 10px;">
                    <label class="form-label-gov" for="mobile">
                      मोबाइल नंबर / WhatsApp No <span class="req">*</span>
                    </label>
                    <div class="input-group">
                      <span class="input-group-addon" style="background: #ffffff; border-color: #cbd5e1;">+91</span>
                      <input type="tel" name="mobile" id="mobile" class="form-control form-control-gov" 
                             placeholder="9876543210" maxlength="10" 
                             value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>" required>
                    </div>
                  </div>
                </div>
              </div>

              <!-- OTP Trigger Button -->
              <div style="margin-top: 10px;">
                <button type="button" id="btn-send-otp" class="btn btn-primary" style="background: #0b3c5d; border-color: #0b3c5d; font-weight: 700; padding: 8px 18px; border-radius: 6px;">
                  <i class="fas fa-paper-plane"></i> आधार ओटीपी भेजें (Send Aadhaar OTP)
                </button>
                <span id="otp-timer-badge" class="badge" style="display:none; background: #f59e0b; margin-left: 8px; font-size: 0.85rem;">60s</span>
              </div>

              <!-- OTP Input Row (Hidden initially) -->
              <div id="otp-verify-row" style="display: none; margin-top: 16px; padding-top: 14px; border-top: 1px dashed #cbd5e1;">
                <div class="row align-items-center">
                  <div class="col-md-6 col-sm-12">
                    <label class="form-label-gov" for="otp_input">
                      प्राप्त ६ अंकों का ओटीपी दर्ज करें / Enter 6-Digit OTP <span class="req">*</span>
                    </label>
                    <input type="text" id="otp_input" class="form-control form-control-gov" 
                           placeholder="123456" maxlength="6" style="letter-spacing: 4px; font-weight: 700; font-size: 1.1rem;">
                    <div id="otp-sandbox-hint" class="text-success" style="font-size: 0.8rem; margin-top: 4px; font-weight: 600;"></div>
                  </div>
                  <div class="col-md-6 col-sm-12" style="margin-top: 24px;">
                    <button type="button" id="btn-verify-otp" class="btn btn-success" style="font-weight: 700; padding: 10px 22px; border-radius: 6px;">
                      <i class="fas fa-check-circle"></i> सत्यापित करें (Verify OTP)
                    </button>
                  </div>
                </div>
              </div>

              <!-- Verified Success State -->
              <div id="aadhaar-verified-badge" style="display: none; margin-top: 14px;" class="alert alert-success">
                <i class="fas fa-check-circle fa-lg"></i>
                <strong style="margin-left: 6px;">आधार ई-केवाईसी सत्यापित (Aadhaar Verified ✓)</strong>
                <span id="verified-aadhaar-text" style="margin-left: 8px; font-family: monospace; font-weight: 700;"></span>
              </div>

              <div id="otp-msg-box" style="margin-top: 10px; display: none;"></div>
            </div>
          </div>
        </div>

        <!-- SECTION 3: Cards, Health IDs & Document Uploads -->
        <div class="ayush-card-box">
          <div class="ayush-card-header">
            <i class="fas fa-file-medical-alt text-info"></i>
            <span>३. स्वास्थ्य आईडी एवं सरकारी दस्तावेज (Health IDs & Document Uploads)</span>
          </div>
          <div class="ayush-card-body">
            
            <!-- Row A: ABHA Number & ABHA Card Upload -->
            <div class="row" style="margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
              <div class="col-md-6 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="abha_number">
                    १४ अंकों का आभा नंबर / 14-Digit ABHA Number
                  </label>
                  <input type="text" name="abha_number" id="abha_number" class="form-control form-control-gov" 
                         placeholder="12-3456-7890-1234" maxlength="17" 
                         value="<?php echo htmlspecialchars($_POST['abha_number'] ?? ''); ?>">
                  <small class="text-muted">Ayushman Bharat Health Account (ABHA) संख्या।</small>
                </div>
              </div>
              <div class="col-md-6 col-sm-12">
                <label class="form-label-gov">
                  आभा कार्ड अपलोड करें / Upload ABHA Card
                </label>
                <div class="file-upload-tile">
                  <input type="file" name="abha_card" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'abha-file-name')">
                  <i class="fas fa-id-card text-muted"></i>
                  <div class="tile-title">आभा कार्ड चुनें (Image / PDF)</div>
                  <div class="tile-hint">अधिकतम 10MB</div>
                  <div id="abha-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row B: Ayushman Bharat Card Upload -->
            <div class="row" style="margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
              <div class="col-md-12">
                <label class="form-label-gov">
                  आयुष्मान भारत कार्ड अपलोड करें / Upload Ayushman Bharat Card
                </label>
                <div class="file-upload-tile">
                  <input type="file" name="ayushman_card" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'ayush-card-file-name')">
                  <i class="fas fa-heartbeat text-danger"></i>
                  <div class="tile-title">आयुष्मान कार्ड (PMJAY Golden Card) अपलोड करें</div>
                  <div class="tile-hint">JPG, PNG या PDF (अधिकतम 10MB)</div>
                  <div id="ayush-card-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row C: Ration Card Number & Ration Card Upload -->
            <div class="row" style="margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
              <div class="col-md-6 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="ration_card_no">
                    राशन कार्ड नंबर / Ration Card Number
                  </label>
                  <input type="text" name="ration_card_no" id="ration_card_no" class="form-control form-control-gov" 
                         placeholder="जैसे: 072001839210" 
                         value="<?php echo htmlspecialchars($_POST['ration_card_no'] ?? ''); ?>">
                  <small class="text-muted">NFSA / राज्य खाद्य एवं आपूर्ति राशन कार्ड संख्या।</small>
                </div>
              </div>
              <div class="col-md-6 col-sm-12">
                <label class="form-label-gov">
                  राशन कार्ड अपलोड करें / Upload Ration Card
                </label>
                <div class="file-upload-tile">
                  <input type="file" name="ration_card" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'ration-file-name')">
                  <i class="fas fa-wheat-awn text-warning"></i>
                  <div class="tile-title">राशन कार्ड चुनें (Image / PDF)</div>
                  <div class="tile-hint">अधिकतम 10MB</div>
                  <div id="ration-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row D: BOCW Registration Number & BOCW Card Upload -->
            <div class="row" style="margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
              <div class="col-md-6 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="bocw_reg_no">
                    BOCW पंजीकरण संख्या / BOCW Registration Number
                  </label>
                  <input type="text" name="bocw_reg_no" id="bocw_reg_no" class="form-control form-control-gov" 
                         placeholder="जैसे: BOCW/DL/2024/91823" 
                         value="<?php echo htmlspecialchars($_POST['bocw_reg_no'] ?? ''); ?>">
                  <small class="text-muted">भवन एवं अन्य सन्निर्माण कर्मकार कल्याण बोर्ड / श्रमिक कार्ड संख्या।</small>
                </div>
              </div>
              <div class="col-md-6 col-sm-12">
                <label class="form-label-gov">
                  BOCW / श्रमिक कार्ड अपलोड करें / Upload Labour Card
                </label>
                <div class="file-upload-tile">
                  <input type="file" name="bocw_card" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'bocw-file-name')">
                  <i class="fas fa-hard-hat text-secondary"></i>
                  <div class="tile-title">BOCW / श्रमिक कार्ड चुनें (Image / PDF)</div>
                  <div class="tile-hint">अधिकतम 10MB</div>
                  <div id="bocw-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row E: PMJAY Letter Number & PMJAY Letter Upload -->
            <div class="row">
              <div class="col-md-6 col-sm-12">
                <div class="form-group">
                  <label class="form-label-gov" for="pmjay_letter_no">
                    PMJAY / आयुष्मान भारत पत्र संख्या / Letter Number
                  </label>
                  <input type="text" name="pmjay_letter_no" id="pmjay_letter_no" class="form-control form-control-gov" 
                         placeholder="जैसे: PMJAY-LTR-2024-8841" 
                         value="<?php echo htmlspecialchars($_POST['pmjay_letter_no'] ?? ''); ?>">
                  <small class="text-muted">प्रधानमंत्री जन आरोग्य योजना पत्र / संदर्भ संख्या।</small>
                </div>
              </div>
              <div class="col-md-6 col-sm-12">
                <label class="form-label-gov">
                  PMJAY पत्र अपलोड करें / Upload Ayushman Bharat Letter
                </label>
                <div class="file-upload-tile">
                  <input type="file" name="pmjay_letter" accept="image/jpeg,image/png,image/webp,application/pdf" onchange="showFileName(this, 'pmjay-file-name')">
                  <i class="fas fa-envelope-open-text text-primary"></i>
                  <div class="tile-title">PMJAY पत्र चुनें (Image / PDF)</div>
                  <div class="tile-hint">अधिकतम 10MB</div>
                  <div id="pmjay-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

          </div>
        </div>

        <!-- SECTION 4: Address Details -->
        <div class="ayush-card-box">
          <div class="ayush-card-header">
            <i class="fas fa-map-marker-alt text-danger"></i>
            <span>४. नागरिक का स्थायी / वर्तमान पता (Residential Address)</span>
          </div>
          <div class="ayush-card-body">
            <div class="form-group">
              <label class="form-label-gov" for="address">मकान / ग्राम / गली का पूरा पता / Full Address</label>
              <textarea name="address" id="address" rows="2" class="form-control" style="border-radius: 6px; border: 1.5px solid #cbd5e1;" placeholder="मकान नं, गली / मोहल्ला, वार्ड / ग्राम पंचायत..."><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
            </div>
            <div class="row">
              <div class="col-md-4 col-sm-4">
                <div class="form-group">
                  <label class="form-label-gov" for="state">राज्य / State</label>
                  <input type="text" name="state" id="state" class="form-control form-control-gov" placeholder="जैसे: Delhi / Uttar Pradesh" value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>">
                </div>
              </div>
              <div class="col-md-4 col-sm-4">
                <div class="form-group">
                  <label class="form-label-gov" for="district">जिला / District</label>
                  <input type="text" name="district" id="district" class="form-control form-control-gov" placeholder="जैसे: Central Delhi" value="<?php echo htmlspecialchars($_POST['district'] ?? ''); ?>">
                </div>
              </div>
              <div class="col-md-4 col-sm-4">
                <div class="form-group">
                  <label class="form-label-gov" for="pincode">पिनकोड / Pincode</label>
                  <input type="text" name="pincode" id="pincode" maxlength="6" class="form-control form-control-gov" placeholder="110001" value="<?php echo htmlspecialchars($_POST['pincode'] ?? ''); ?>">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 5: Terms, Conditions & Consent -->
        <div class="ayush-card-box" style="border-left: 5px solid #f59e0b;">
          <div class="ayush-card-header" style="background: #fffbeb;">
            <i class="fas fa-file-contract text-warning"></i>
            <span style="color: #92400e;">५. नियम, शर्तें एवं स्व-घोषणा सहमति (Terms & Consent)</span>
          </div>
          <div class="ayush-card-body">
            <div class="checkbox" style="margin-top: 0;">
              <label style="font-weight: 600; color: #1e293b; font-size: 0.94rem; line-height: 1.6;">
                <input type="checkbox" name="consent_accepted" id="consent_accepted" value="1" <?php echo (!empty($_POST['consent_accepted']) || !isset($_POST['submit_ayushman'])) ? 'checked' : ''; ?> required>
                मैं स्वेच्छा से अपनी दी गई जानकारी और अपलोड किए गए दस्तावेज़ डिजिटल हेल्थ स्मार्ट आईडी बनाने और सत्यापन के लिए प्रस्तुत कर रहा हूँ। मेरे द्वारा दी गई सभी जानकारी पूर्णतया सत्य और वैध है।
              </label>
            </div>
            <div style="font-size: 0.8rem; color: #64748b; margin-top: 8px;">
              (I voluntarily submit my provided information and uploaded documents for generating and verifying Digital Health Smart ID. All information provided by me is completely true and valid.)
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center" style="margin-top: 28px; margin-bottom: 20px;">
          <button type="submit" id="btn-submit-main" class="btn btn-gov-submit">
            <i class="fas fa-check-double"></i> सबमिट करें एवं डिजिटल हेल्थ स्मार्ट आईडी कार्ड जनरेट करें
          </button>
          <div style="font-size: 0.82rem; color: #64748b; margin-top: 8px;">
            <i class="fas fa-lock text-success"></i> 256-Bit Military Grade Encrypted • UIDAI Aadhaar Verification • NHA Facilitation
          </div>
        </div>

      </div>

      <!-- Right Column: Security Guidance & Info Box -->
      <div class="col-lg-4 col-md-4 col-sm-12">
        <div style="position: sticky; top: 20px;">
          
          <!-- Security Notice Card -->
          <div class="ayush-card-box" style="border-top: 4px solid #059669;">
            <div class="ayush-card-body">
              <div style="text-align: center; margin-bottom: 12px;">
                <i class="fas fa-shield-alt fa-3x" style="color: #059669;"></i>
                <h4 style="font-weight: 800; color: #065f46; margin-top: 8px; margin-bottom: 4px;">एन्क्रिप्टेड क्यूआर कोड सुरक्षा</h4>
                <span class="security-encrypted-badge">DPDP Act 2023 Compliant</span>
              </div>
              <p style="font-size: 0.86rem; color: #334155; line-height: 1.55; margin-bottom: 12px;">
                आपके डिजिटल हेल्थ स्मार्ट आईडी कार्ड पर उत्पन्न होने वाला QR कोड <strong>AES-256-CBC एवं HMAC-SHA256</strong> द्वारा एन्क्रिप्टेड होगा। 
              </p>
              <div style="background: #f1f5f9; border-radius: 6px; padding: 12px; font-size: 0.82rem; color: #475569; margin-bottom: 12px;">
                <i class="fas fa-info-circle text-primary"></i> <strong>सुरक्षा सूचना:</strong> आम पब्लिक स्कैनर से स्कैन करने पर व्यक्तिगत जानकारी नहीं दिखेगी। केवल अधिकृत प्रशासनिक अधिकारी अपने <strong>Admin Scanner</strong> से ही डेटा डिक्रिप्ट और सत्यापित कर सकते हैं।
              </div>
              <ul style="padding-left: 18px; margin: 0; font-size: 0.84rem; color: #475569; line-height: 1.6;">
                <li>आधार ई-केवाईसी सत्यापन आवश्यक है।</li>
                <li>दस्तावेज स्पष्ट एवं पठनीय होने चाहिए (अधिकतम 10MB)।</li>
                <li>कार्ड की डिजिटल प्रति तुरंत ईमेल पर भेजी जाएगी।</li>
              </ul>
            </div>
          </div>

          <!-- Helpline Card -->
          <div class="ayush-card-box" style="background: #f8fafc;">
            <div class="ayush-card-body">
              <h5 style="font-weight: 700; color: #0b3c5d; margin-top: 0;">
                <i class="fas fa-headset text-warning"></i> सहायता एवं संपर्क केंद्र
              </h5>
              <p style="font-size: 0.84rem; color: #64748b; margin-bottom: 10px;">
                किसी भी तकनीकी सहायता या जानकारी के लिए हमारे आधिकारिक हेल्पलाइन पर संपर्क करें:
              </p>
              <div style="font-size: 1rem; font-weight: 800; color: #0b3c5d;">
                <i class="fas fa-phone-alt text-success"></i> <?php echo defined('HELPLINE_NUMBER') ? HELPLINE_NUMBER : '1800-11-2026'; ?>
              </div>
              <div style="font-size: 0.85rem; color: #475569; margin-top: 4px;">
                <i class="fas fa-envelope text-info"></i> <?php echo defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@citizenportal.facilitator.in'; ?>
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

<?php include __DIR__ . '/includes/footer.php'; ?>
