<?php
/**
 * ayushman-helpdesk.php
 * Ayushman Bharat Help Desk & Digital Health Smart ID Application Form
 * Modular Architecture: Pure Semantic View, Externalized CSS & Fast AJAX Form Flow
 */

require_once __DIR__ . '/config/config.php';

// Generate CSRF token if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// SEO & Breadcrumb Meta
$pageTitle = 'आयुष्मान हेल्प डेस्क एवं डिजिटल हेल्थ स्मार्ट आईडी कार्ड | ' . APP_NAME;
$metaDescription = 'PM-JAY आयुष्मान भारत डिजिटल हेल्थ स्मार्ट आईडी कार्ड आवेदन फॉर्म। 256-बिट एन्क्रिप्टेड क्यूआर एवं त्वरित डिजिटल हेल्थ पहचान पत्र प्राप्त करें।';
$metaKeywords = 'ayushman help desk, ayushman smart id, abha health card, pmjay registration, digital health card, ayushman form online';
$canonicalUrl = BASE_URL . '/ayushman-helpdesk.php';
$activeNav = 'ayushman';

include __DIR__ . '/includes/header.php';
?>

<!-- Dedicated Modular Stylesheet -->
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/ayushman-helpdesk.css?v=<?php echo @filemtime(__DIR__ . '/css/ayushman-helpdesk.css') ?: time(); ?>">

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
          <span class="badge-gov-saffron" style="background: #0073cb; color: #ffffff;">
            <i class="fas fa-heartbeat"></i> <span data-hi="रीजूवेनेट डिजिटल हेल्थ (Rejuvenate Digital Health)" data-en="Rejuvenate Digital Health">Rejuvenate Digital Health</span>
          </span>
          <span class="security-encrypted-badge">
            <i class="fas fa-lock"></i> 256-Bit Encrypted Health Smart ID
          </span>
        </div>
        <h1 data-hi="आयुष्मान हेल्प डेस्क एवं डिजिटल हेल्थ स्मार्ट आईडी" data-en="Ayushman Help Desk &amp; Digital Health Smart ID">Ayushman Help Desk &amp; Digital Health Smart ID</h1>
        <p class="hero-subtext" data-hi="Rejuvenate Digital Health नागरिक सुविधा केंद्र: डिजिटल हेल्थ इकोसिस्टम के अंतर्गत अपनी पहचान सत्यापित करें और एक सुरक्षित एन्क्रिप्टेड डिजिटल हेल्थ स्मार्ट आईडी कार्ड प्राप्त करें। Powered by rejuvenatedigitalhealth.com" data-en="Rejuvenate Digital Health Citizen Facilitation Centre: Verify your identity and get your secure, encrypted Digital Health Smart ID card powered by rejuvenatedigitalhealth.com.">
          Rejuvenate Digital Health Citizen Facilitation Centre: Verify your identity and get your secure, encrypted Digital Health Smart ID card powered by rejuvenatedigitalhealth.com.
        </p>
      </div>
      <div class="col-md-4 col-sm-12 text-md-end d-none d-md-block">
        <div class="ayush-hero-side-card">
          <i class="fas fa-id-card-alt fa-3x"></i>
          <div style="font-weight: 700; font-size: 1.05rem; margin-top: 6px;">Digital Health Smart ID</div>
          <div style="font-size: 0.8rem; opacity: 0.88;">Aadhaar Verified • Encrypted QR</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Main Container -->
<div class="container" id="ayushman-main-content" style="padding-top: 24px; padding-bottom: 50px;">

  <!-- Quick Info & Instructions Guide Card (Collapsible, visible on all viewports) -->
  <div class="ayush-about-card" style="border-left: 4px solid #1e3a8a; margin-bottom: 22px;">
    <div style="display: flex; justify-content: space-between; align-items: center; cursor: pointer;" onclick="toggleAyushGuide()" id="ayush-guide-header">
      <div style="display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-circle-info fa-lg" style="color: #1e3a8a;"></i>
        <strong style="color: #1e3a8a; font-size: 0.95rem;">
          <span data-hi="आयुष्मान हेल्प डेस्क के बारे में एवं ४-चरणीय प्रक्रिया" data-en="About Ayushman Help Desk &amp; 4-Step Process">About Ayushman Help Desk &amp; 4-Step Process</span>
        </strong>
      </div>
      <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" style="font-size: 0.76rem; padding: 3px 12px;" aria-expanded="false">
        <span id="guide-toggle-txt" data-hi="विवरण देखें" data-en="View Details">View Details</span> <i class="fas fa-chevron-down ms-1" id="guide-toggle-icon"></i>
      </button>
    </div>
    <div id="ayush-guide-body" style="display: none; margin-top: 14px; padding-top: 12px; border-top: 1px dashed #cbd5e1;">
      <p style="font-size: 0.84rem; color: #475569; line-height: 1.55; margin: 0 0 10px;" data-hi="आयुष्मान भारत प्रधानमंत्री जन आरोग्य योजना (PM-JAY) केंद्र सरकार की विश्व की सबसे बड़ी स्वास्थ्य बीमा योजना है, जो पात्र परिवारों को प्रति वर्ष ₹5 लाख तक का कैशलेस अस्पताल उपचार कवर प्रदान करती है। यह हेल्प डेस्क नागरिकों को अपनी पहचान व दस्तावेज डिजिटल रूप से सत्यापित करने और एक सत्यापित डिजिटल हेल्थ स्मार्ट आईडी कार्ड बनाने में सहायता करता है। यह प्लेटफॉर्म आयुष्मान भारत डिजिटल मिशन (ABDM) के अंतर्गत कार्य करता है।" data-en="Ayushman Bharat Pradhan Mantri Jan Arogya Yojana (PM-JAY) provides eligible families cashless hospitalization cover of up to ₹5 lakh per year. This Help Desk assists citizens in digitally verifying their identity and documents and generating an encrypted Digital Health Smart ID card under the ABDM digital health ecosystem.">
        Ayushman Bharat Pradhan Mantri Jan Arogya Yojana (PM-JAY) provides eligible families cashless hospitalization cover of up to ₹5 lakh per year. This Help Desk assists citizens in digitally verifying their identity and documents and generating an encrypted Digital Health Smart ID card under the ABDM digital health ecosystem.
      </p>
      <h5 style="margin: 10px 0 6px; font-weight: 700; color: #1e293b; font-size: 0.86rem;" data-hi="सरल ४-चरणीय प्रक्रिया" data-en="Simple 4-Step Process">Simple 4-Step Process</h5>
      <ol class="ayush-about-steps" style="margin: 0;">
        <li data-hi="अपना १२ अंकों का आधार नंबर एवं मोबाइल नंबर दर्ज करें।" data-en="Enter your 12-digit Aadhaar number and mobile number.">Enter your 12-digit Aadhaar number and mobile number.</li>
        <li data-hi="अपना व्यक्तिगत विवरण, स्वास्थ्य आईडी एवं पता भरें।" data-en="Fill in your personal details, health IDs and address.">Fill in your personal details, health IDs and address.</li>
        <li data-hi="अपने उपलब्ध दस्तावेजों (आभा, आयुष्मान, राशन, BOCW) की स्पष्ट फोटो अपलोड करें।" data-en="Upload clear photos of your available documents (ABHA, Ayushman, Ration, BOCW).">Upload clear photos of your available documents (ABHA, Ayushman, Ration, BOCW).</li>
        <li data-hi="सहमति स्वीकार करें और सबमिट करें — आपका एन्क्रिप्टेड स्मार्ट आईडी कार्ड तुरंत तैयार होकर खुलेगा।" data-en="Accept consent and submit — your encrypted Smart ID card is generated instantly within seconds.">Accept consent and submit — your encrypted Smart ID card is generated instantly within seconds.</li>
      </ol>
    </div>
  </div>

  <!-- Dynamic Error & Alert Container (Handled by JS without reload) -->
  <div id="ayush-alert-container" style="display: none;"></div>

  <!-- Main Application Form (AJAX target) -->
  <form id="ayushman-form" action="<?php echo BASE_URL; ?>/api/submit_ayushman.php" method="POST" enctype="multipart/form-data" novalidate>
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

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
                <div class="form-group mb-3">
                  <label class="form-label-gov" for="full_name">
                    <span data-hi="मरीज का पूरा नाम" data-en="Full Name">Full Name</span> <span class="req">*</span>
                  </label>
                  <input type="text" name="full_name" id="full_name" class="form-control form-control-gov"
                         placeholder="e.g. Ramesh Kumar" data-hi-placeholder="जैसे: रमेश कुमार" data-en-placeholder="e.g. Ramesh Kumar" required>
                  <small class="text-muted" data-hi="मरीज/नागरिक का पूरा नाम दर्ज करें।" data-en="Enter the full name of the patient/citizen.">Enter the full name of the patient/citizen.</small>
                </div>

                <div class="row">
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group mb-3">
                      <label class="form-label-gov" for="dob" data-hi="जन्म तिथि" data-en="Date of Birth">Date of Birth</label>
                      <input type="date" name="dob" id="dob" class="form-control form-control-gov">
                    </div>
                  </div>
                  <div class="col-md-6 col-sm-6">
                    <div class="form-group mb-3">
                      <label class="form-label-gov" for="gender" data-hi="लिंग" data-en="Gender">Gender</label>
                      <select name="gender" id="gender" class="form-control form-control-gov">
                        <option value="Male" data-hi="पुरुष" data-en="Male">Male</option>
                        <option value="Female" data-hi="महिला" data-en="Female">Female</option>
                        <option value="Other" data-hi="अन्य" data-en="Other">Other</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="form-group mb-3">
                  <label class="form-label-gov" for="email">
                    <span data-hi="ईमेल आईडी" data-en="Email ID">Email ID</span> <span class="req">*</span>
                  </label>
                  <input type="email" name="email" id="email" class="form-control form-control-gov"
                         inputmode="email"
                         placeholder="yourname@gmail.com" required>
                  <small class="text-muted" data-hi="सबमिशन के बाद आपका डिजिटल स्मार्ट कार्ड इसी ईमेल पर भेजा जाएगा।" data-en="Your Digital Smart Card will be sent to this email after submission.">Your Digital Smart Card will be sent to this email after submission.</small>
                </div>
              </div>

              <!-- Photo Upload & Camera Box -->
              <div class="col-md-5 col-sm-12">
                <div class="form-group mb-3">
                  <label class="form-label-gov" data-hi="फोटो अपलोड करें" data-en="Upload Your Photo">Upload Your Photo</label>
                  <div class="file-upload-tile" id="photo-tile">
                    <input type="file" name="photo" id="photo_input" accept="image/jpeg,image/png,image/webp" onchange="previewCitizenPhoto(this)">
                    <div id="photo-preview-wrap">
                      <i class="fas fa-camera-retro fa-2x" style="color: #0b3c5d;"></i>
                      <div class="tile-title" data-hi="पासपोर्ट फोटो चुनें या यहाँ खींचें" data-en="Choose a passport photo or drag it here">Choose a passport photo or drag it here</div>
                      <div class="tile-hint" data-hi="JPG, PNG या WEBP (अधिकतम 10MB)" data-en="JPG, PNG or WEBP (max 10MB)">JPG, PNG or WEBP (max 10MB)</div>
                    </div>
                    <img id="photo-img-preview" src="" alt="Photo Preview" style="display:none; max-width: 110px; max-height: 120px; border-radius: 6px; border: 2px solid #0b3c5d; margin: 8px auto 4px; object-fit: cover;">
                    <div id="photo-file-name" class="file-selected-name" style="display:none;"></div>
                  </div>

                  <!-- Explicit Upload / Live Camera Buttons -->
                  <div style="display: flex; gap: 8px; margin-top: 8px;">
                    <button type="button" class="btn btn-gov-outline" style="flex: 1; padding: 7px 10px; font-size: 0.82rem;" onclick="document.getElementById('photo_input').click()">
                      <i class="fas fa-upload me-1"></i> <span data-hi="अपलोड करें" data-en="Upload">Upload</span>
                    </button>
                    <button type="button" class="btn btn-gov-outline" style="flex: 1; padding: 7px 10px; font-size: 0.82rem;" onclick="document.getElementById('photo_camera_input').click()">
                      <i class="fas fa-camera me-1"></i> <span data-hi="कैमरा से खींचें" data-en="Camera">Camera</span>
                    </button>
                  </div>
                  <!-- Camera Input -->
                  <input type="file" id="photo_camera_input" accept="image/*" capture="user" style="display:none;" onchange="captureCitizenPhotoFromCamera(this)">
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- SECTION 2: Aadhaar & Mobile Number -->
        <div class="ayush-card-box">
          <div class="ayush-card-header header-success">
            <i class="fas fa-fingerprint"></i>
            <span data-hi="२. आधार एवं मोबाइल नंबर" data-en="2. Aadhaar &amp; Mobile Number">2. Aadhaar &amp; Mobile Number</span>
          </div>
          <div class="ayush-card-body">
            <div class="row align-items-center">
              <div class="col-md-6 col-sm-12">
                <div class="form-group mb-3">
                  <label class="form-label-gov" for="aadhaar_input">
                    <span data-hi="१२ अंकों का आधार नंबर" data-en="12-Digit Aadhaar No">12-Digit Aadhaar No</span> <span class="req">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text input-group-addon-gov"><i class="fas fa-id-card"></i></span>
                    <input type="text" name="aadhaar_number" id="aadhaar_input" class="form-control form-control-gov"
                           placeholder="XXXX XXXX XXXX" maxlength="14" autocomplete="off" required>
                  </div>
                  <small class="text-muted" data-hi="वैध 12 अंकों का आधार नंबर दर्ज करें।" data-en="Enter a valid 12-digit Aadhaar number.">Enter a valid 12-digit Aadhaar number.</small>
                </div>
              </div>

              <div class="col-md-6 col-sm-12">
                <div class="form-group mb-3">
                  <label class="form-label-gov" for="mobile">
                    <span data-hi="मोबाइल नंबर" data-en="Mobile Number">Mobile Number</span> <span class="req">*</span>
                  </label>
                  <div class="input-group">
                    <span class="input-group-text input-group-addon-gov">+91</span>
                    <input type="tel" name="mobile" id="mobile" class="form-control form-control-gov"
                           placeholder="9876543210" maxlength="10" required>
                  </div>
                  <small class="text-muted" data-hi="10 अंकों का वैध मोबाइल नंबर दर्ज करें।" data-en="Enter a valid 10-digit mobile number.">Enter a valid 10-digit mobile number.</small>
                </div>
              </div>
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
            <!-- Row A: ABHA Number & ABHA Card -->
            <div class="row align-items-center mb-3">
              <div class="col-md-7 col-sm-12">
                <label class="form-label-gov" for="abha_number" data-hi="आभा नंबर (ABHA ID)" data-en="ABHA Number (ABHA ID)">ABHA Number (ABHA ID)</label>
                <input type="text" name="abha_number" id="abha_number" class="form-control form-control-gov" placeholder="XX-XXXX-XXXX-XXXX">
                <small class="text-muted" data-hi="१४ अंकों का राष्ट्रीय स्वास्थ्य खाता संख्या (यदि उपलब्ध हो)।" data-en="14-digit National Health Account number (if available).">14-digit National Health Account number (if available).</small>
              </div>
              <div class="col-md-5 col-sm-12 mt-2 mt-md-0">
                <div class="file-upload-tile" id="abha-tile">
                  <input type="file" name="abha_card" id="abha_input" accept="image/jpeg,image/png,image/webp,application/pdf">
                  <i class="fas fa-id-badge fa-lg" style="color: #0b3c5d;"></i>
                  <div class="tile-title" data-hi="आभा कार्ड अपलोड करें" data-en="Upload ABHA Card">Upload ABHA Card</div>
                  <div class="tile-hint" data-hi="अधिकतम 10MB" data-en="Max 10MB">Max 10MB</div>
                  <div id="abha-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row B: Ayushman Card No & Card -->
            <div class="row align-items-center mb-3">
              <div class="col-md-7 col-sm-12">
                <label class="form-label-gov" for="pmjay_letter_no" data-hi="आयुष्मान कार्ड संख्या / PM-JAY आईडी" data-en="Ayushman Card No / PM-JAY ID">Ayushman Card No / PM-JAY ID</label>
                <input type="text" name="pmjay_letter_no" id="pmjay_letter_no" class="form-control form-control-gov" placeholder="e.g. PMJAY-XXXX-XXXX">
                <small class="text-muted" data-hi="आयुष्मान भारत कार्ड नंबर या परिवार पहचान पत्र आईडी।" data-en="Ayushman Bharat card number or Family ID.">Ayushman Bharat card number or Family ID.</small>
              </div>
              <div class="col-md-5 col-sm-12 mt-2 mt-md-0">
                <div class="file-upload-tile" id="ayush-tile">
                  <input type="file" name="ayushman_card" id="ayush_card_input" accept="image/jpeg,image/png,image/webp,application/pdf">
                  <i class="fas fa-heartbeat fa-lg" style="color: #047857;"></i>
                  <div class="tile-title" data-hi="आयुष्मान कार्ड अपलोड करें" data-en="Upload Ayushman Card">Upload Ayushman Card</div>
                  <div class="tile-hint" data-hi="अधिकतम 10MB" data-en="Max 10MB">Max 10MB</div>
                  <div id="ayush-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row C: Ration Card -->
            <div class="row align-items-center mb-3">
              <div class="col-md-7 col-sm-12">
                <label class="form-label-gov" for="ration_card_no" data-hi="राशन कार्ड संख्या" data-en="Ration Card Number">Ration Card Number</label>
                <input type="text" name="ration_card_no" id="ration_card_no" class="form-control form-control-gov" placeholder="e.g. 07100XXXXXXX">
                <small class="text-muted" data-hi="NFSA या राज्य खाद्य सुरक्षा राशन कार्ड संख्या।" data-en="NFSA or State Food Security ration card number.">NFSA or State Food Security ration card number.</small>
              </div>
              <div class="col-md-5 col-sm-12 mt-2 mt-md-0">
                <div class="file-upload-tile" id="ration-tile">
                  <input type="file" name="ration_card" id="ration_input" accept="image/jpeg,image/png,image/webp,application/pdf">
                  <i class="fas fa-utensils fa-lg" style="color: #b45309;"></i>
                  <div class="tile-title" data-hi="राशन कार्ड अपलोड करें" data-en="Upload Ration Card">Upload Ration Card</div>
                  <div class="tile-hint" data-hi="अधिकतम 10MB" data-en="Max 10MB">Max 10MB</div>
                  <div id="ration-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row D: BOCW Card -->
            <div class="row align-items-center mb-3">
              <div class="col-md-7 col-sm-12">
                <label class="form-label-gov" for="bocw_reg_no" data-hi="BOCW श्रमिक पंजीकरण संख्या" data-en="BOCW Worker Registration No">BOCW Worker Registration No</label>
                <input type="text" name="bocw_reg_no" id="bocw_reg_no" class="form-control form-control-gov" placeholder="e.g. BOCW-DL-2024-XXXX">
                <small class="text-muted" data-hi="भवन एवं सन्निर्माण कर्मकार कल्याण बोर्ड पंजीकरण नंबर।" data-en="Building &amp; Other Construction Workers Board number.">Building &amp; Other Construction Workers Board number.</small>
              </div>
              <div class="col-md-5 col-sm-12 mt-2 mt-md-0">
                <div class="file-upload-tile" id="bocw-tile">
                  <input type="file" name="bocw_card" id="bocw_input" accept="image/jpeg,image/png,image/webp,application/pdf">
                  <i class="fas fa-hard-hat fa-lg" style="color: #6d28d9;"></i>
                  <div class="tile-title" data-hi="BOCW कार्ड अपलोड करें" data-en="Upload BOCW Card">Upload BOCW Card</div>
                  <div class="tile-hint" data-hi="अधिकतम 10MB" data-en="Max 10MB">Max 10MB</div>
                  <div id="bocw-file-name" class="file-selected-name" style="display:none;"></div>
                </div>
              </div>
            </div>

            <!-- Row E: PM-JAY Family Letter -->
            <div class="row align-items-center">
              <div class="col-md-7 col-sm-12">
                <label class="form-label-gov" data-hi="PM-JAY सरकारी पत्र / HH ID" data-en="PM-JAY Family Letter / HH ID">PM-JAY Family Letter / HH ID</label>
                <p class="text-muted small mb-0" data-hi="प्रधानमंत्री का पत्र या परिवार पहचान पत्र संलग्न करें।" data-en="Attach Prime Minister letter or Ayushman identification letter.">Attach Prime Minister letter or Ayushman identification letter.</p>
              </div>
              <div class="col-md-5 col-sm-12 mt-2 mt-md-0">
                <div class="file-upload-tile" id="pmjay-tile">
                  <input type="file" name="pmjay_letter" id="pmjay_input" accept="image/jpeg,image/png,image/webp,application/pdf">
                  <i class="fas fa-envelope-open-text fa-lg" style="color: #0369a1;"></i>
                  <div class="tile-title" data-hi="सरकारी पत्र अपलोड करें" data-en="Upload PMJAY Letter">Upload PMJAY Letter</div>
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
            <div class="form-group mb-3">
              <label class="form-label-gov" for="address" data-hi="मकान / ग्राम / गली का पूरा पता" data-en="Full Address">Full Address</label>
              <textarea name="address" id="address" rows="2" class="form-control form-control-gov-area" placeholder="House No, Street / Mohalla, Ward / Gram Panchayat..." data-hi-placeholder="मकान नं, गली / मोहल्ला, वार्ड / ग्राम पंचायत..." data-en-placeholder="House No, Street / Mohalla, Ward / Gram Panchayat..."></textarea>
            </div>
            <div class="row">
              <div class="col-md-4 col-sm-4">
                <div class="form-group mb-2">
                  <label class="form-label-gov" for="state" data-hi="राज्य" data-en="State">State</label>
                  <input type="text" name="state" id="state" class="form-control form-control-gov" placeholder="e.g. Delhi / Uttar Pradesh">
                </div>
              </div>
              <div class="col-md-4 col-sm-4">
                <div class="form-group mb-2">
                  <label class="form-label-gov" for="district" data-hi="जिला" data-en="District">District</label>
                  <input type="text" name="district" id="district" class="form-control form-control-gov" placeholder="e.g. Central Delhi">
                </div>
              </div>
              <div class="col-md-4 col-sm-4">
                <div class="form-group mb-2">
                  <label class="form-label-gov" for="pincode" data-hi="पिनकोड" data-en="Pincode">Pincode</label>
                  <input type="text" name="pincode" id="pincode" maxlength="6" class="form-control form-control-gov" placeholder="110001">
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
              <input type="checkbox" class="form-check-input" name="consent_accepted" id="consent_accepted" value="1" checked required>
              <label class="form-check-label" for="consent_accepted" style="font-weight: 600; color: #1e293b; font-size: 0.92rem; line-height: 1.6;" data-hi="मैं स्वेच्छा से अपनी दी गई जानकारी और अपलोड किए गए दस्तावेज़—आभा कार्ड, आयुष्मान भारत कार्ड, राशन कार्ड, BOCW कार्ड और PMJAY पत्र—की प्रतियों को विवरण मिलान और Digital Health Smart ID तैयार करने के लिए उपयोग करने की सहमति देता/देती हूँ। मुझे ज्ञात है कि यह एक निजी डेटा फॉर्मेटिंग सेवा है। मेरे द्वारा दी गई जानकारी की सत्यता की जिम्मेदारी मेरी है।" data-en="I voluntarily consent to the use of my submitted information and uploaded documents—ABHA Card, Ayushman Bharat Card, Ration Card, BOCW Card and PMJAY Letter—for checking the submitted details and preparing my Digital Health Smart ID. I understand that this is a private data formatting service. I am responsible for the accuracy of the information I provide.">
                I voluntarily consent to the use of my submitted information and uploaded documents—ABHA Card, Ayushman Bharat Card, Ration Card, BOCW Card and PMJAY Letter—for checking the submitted details and preparing my Digital Health Smart ID. I understand that this is a private data formatting service. I am responsible for the accuracy of the information I provide.
              </label>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center" style="margin-top: 24px; margin-bottom: 20px;">
          <button type="submit" id="btn-submit-main" class="btn btn-gov-submit">
            <i class="fas fa-check-double me-2"></i> <span data-hi="सबमिट करें एवं डिजिटल हेल्थ स्मार्ट आईडी कार्ड जनरेट करें" data-en="Submit &amp; Generate Digital Health Smart ID Card">Submit &amp; Generate Digital Health Smart ID Card</span>
          </button>
          <div style="font-size: 0.82rem; color: #64748b; margin-top: 10px;">
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
                <i class="fas fa-circle-info fa-2x" style="color: #10b981;"></i>
                <h4 class="ayush-side-title" style="font-size: 0.95rem;" data-hi="आयुष्मान हेल्प डेस्क के बारे में" data-en="About Ayushman Help Desk">About Ayushman Help Desk</h4>
                <span class="security-encrypted-badge">DPDP Act 2023 Compliant</span>
              </div>
              <p style="font-size: 0.8rem; color: #334155; line-height: 1.5; margin-bottom: 10px;" data-hi="PM-JAY केंद्र सरकार की विश्व की सबसे बड़ी स्वास्थ्य बीमा योजना है — पात्र परिवारों को ₹5 लाख/वर्ष तक कैशलेस अस्पताल कवर। यह हेल्प डेस्क आपकी पहचान सत्यापित कर एक डिजिटल स्मार्ट आईडी बनाता है, जिससे आप इस योजना का लाभ आसानी से क्लेम कर सकें। यह ABDM डिजिटल हेल्थ इकोसिस्टम के अंतर्गत कार्य करता है।" data-en="PM-JAY is the Central Government's flagship health insurance scheme — the world's largest — giving eligible families cashless hospital cover up to ₹5 lakh/year. This Help Desk verifies your identity and generates a Digital Smart ID so you can claim that benefit easily. Operates under the ABDM digital health ecosystem.">
                PM-JAY is the Central Government's flagship health insurance scheme — the world's largest — giving eligible families cashless hospital cover up to ₹5 lakh/year. This Help Desk verifies your identity and generates a Digital Smart ID so you can claim that benefit easily. Operates under the ABDM digital health ecosystem.
              </p>
              <h5 style="margin: 10px 0 0; font-weight: 700; color: #1e293b; font-size: 0.8rem;" data-hi="इसका उपयोग कैसे करें" data-en="How to use this">How to use this</h5>
              <ol class="ayush-about-steps" style="margin-top: 8px;">
                <li data-hi="अपना १२ अंकों का आधार नंबर एवं मोबाइल नंबर दर्ज करें।" data-en="Enter your 12-digit Aadhaar number and mobile number.">Enter your 12-digit Aadhaar number and mobile number.</li>
                <li data-hi="अपना व्यक्तिगत विवरण, स्वास्थ्य आईडी एवं पता भरें।" data-en="Fill in your personal details, health IDs and address.">Fill in your personal details, health IDs and address.</li>
                <li data-hi="अपने उपलब्ध दस्तावेजों की स्पष्ट फोटो अपलोड करें (अधिकतम 10MB)।" data-en="Upload clear photos of your available documents (max 10MB).">Upload clear photos of your available documents (max 10MB).</li>
                <li data-hi="सहमति स्वीकार करें और सबमिट करें — कार्ड तुरंत तैयार होकर ईमेल पर भेजा जाएगा।" data-en="Accept the consent and submit — the card is generated instantly and emailed.">Accept the consent and submit — the card is generated instantly and emailed.</li>
              </ol>
            </div>
          </div>

          <!-- Helpline Card -->
          <div class="ayush-card-box ayush-helpline-card">
            <div class="ayush-card-body">
              <h5 class="helpline-title" style="font-weight: 700; color: #1e3a8a; margin-top: 0;">
                <i class="fas fa-headset me-2 text-warning"></i><span data-hi="सहायता एवं संपर्क केंद्र" data-en="Help &amp; Contact Centre">Help &amp; Contact Centre</span>
              </h5>
              <p style="font-size: 0.84rem; color: #64748b; margin-bottom: 10px;" data-hi="किसी भी तकनीकी सहायता या जानकारी के लिए हमारे आधिकारिक हेल्पलाइन पर संपर्क करें:" data-en="For any technical assistance or information, contact our official helpline:">
                For any technical assistance or information, contact our official helpline:
              </p>
              <div class="helpline-number">
                <i class="fas fa-phone-alt me-2 text-success"></i><?php echo defined('HELPLINE_NUMBER') ? HELPLINE_NUMBER : '1800-11-2026'; ?>
              </div>
              <div class="helpline-email" style="font-size: 0.85rem; color: #475569; margin-top: 6px;">
                <i class="fas fa-envelope me-2 text-info"></i><?php echo defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'support@citizenportal.facilitator.in'; ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </form>
</div>

<!-- Submission Progress Modal Overlay -->
<div id="ayushman-submit-overlay" class="ayushman-submit-overlay">
  <div class="ayushman-submit-box">
    <div class="ayushman-submit-spinner"></div>
    <h4 data-hi="आवेदन सबमिट किया जा रहा है..." data-en="Submitting your application...">Submitting your application...</h4>
    <div class="ayushman-submit-progress-track">
      <div class="ayushman-submit-progress-bar" id="ayushman-submit-progress-bar"></div>
    </div>
    <div class="ayushman-submit-status" id="ayushman-submit-status">Uploading your details...</div>
    <div class="ayushman-submit-elapsed" id="ayushman-submit-elapsed">0s</div>
    <p data-hi="कृपया प्रतीक्षा करें, आपका स्मार्ट कार्ड तैयार हो रहा है। पेज को बंद न करें।" data-en="Please wait, your Smart ID Card is being generated. Do not close this page.">Please wait, your Smart ID Card is being generated. Do not close this page.</p>
  </div>
</div>

<!-- Global JS Variable for Base URL -->
<script>
window.BASE_URL = <?php echo json_encode(BASE_URL); ?>;
</script>

<!-- Dedicated Modular Client Script -->
<script src="<?php echo BASE_URL; ?>/js/ayushman-helpdesk.js?v=<?php echo @filemtime(__DIR__ . '/js/ayushman-helpdesk.js') ?: time(); ?>"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
