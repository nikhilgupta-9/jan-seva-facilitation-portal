<?php
// admin/ayushman-scanner.php - Official In-built Encrypted QR Scanner & Citizen Verification Terminal
require_once __DIR__ . '/auth.php';

$page_title = 'Official Admin Encrypted QR Scanner & Dossier Terminal';

$initialToken = trim($_GET['token'] ?? '');
$initialDecrypted = null;
$initialDbRecord = null;

if (!empty($initialToken)) {
    // Clean token if full URL was passed
    $tokenToDecrypt = $initialToken;
    if (strpos($initialToken, 'token=') !== false) {
        $parsed = parse_url($initialToken);
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $q);
            if (!empty($q['token'])) {
                $tokenToDecrypt = $q['token'];
            }
        }
    }
    $initialDecrypted = ayushman_decrypt_payload($tokenToDecrypt);
    if ($initialDecrypted && !empty($initialDecrypted['app_no']) && isset($pdo)) {
        $stmt = $pdo->prepare("SELECT * FROM ayushman_applications WHERE application_no = ? LIMIT 1");
        $stmt->execute([$initialDecrypted['app_no']]);
        $initialDbRecord = $stmt->fetch();
    }
}

include __DIR__ . '/header.php';
?>

<style>
.scanner-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(11,60,93,0.06);
    overflow: hidden;
    margin-bottom: 24px;
}
.scanner-header {
    background: #0b3c5d;
    color: #ffffff;
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.camera-viewport-box {
    position: relative;
    background: #0f172a;
    border-radius: 10px;
    overflow: hidden;
    min-height: 280px;
    display: flex;
    align-items: center;
    justify-content: center;
}
#qr-reader {
    width: 100% !important;
    border: none !important;
}
#qr-reader__dashboard {
    padding: 10px;
    background: #f8fafc;
}
.scan-laser-line {
    position: absolute;
    top: 15%;
    left: 10%;
    right: 10%;
    height: 3px;
    background: #ef4444;
    box-shadow: 0 0 12px #ef4444;
    border-radius: 2px;
    animation: scannerSweep 2.2s infinite ease-in-out;
    pointer-events: none;
    z-index: 10;
}
@keyframes scannerSweep {
    0%, 100% { top: 15%; opacity: 0.8; }
    50% { top: 85%; opacity: 1; }
}
.citizen-dossier-box {
    background: #ffffff;
    border: 2px solid #10b981;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.12);
}
.dossier-header {
    display: flex;
    gap: 16px;
    align-items: center;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 18px;
}
.dossier-photo {
    width: 80px;
    height: 95px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid #0b3c5d;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.dossier-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.dossier-field {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
}
.dossier-field-label {
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    display: block;
}
.dossier-field-value {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f172a;
    word-break: break-all;
}
.doc-pill-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    color: #1d4ed8;
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    margin-right: 6px;
    margin-bottom: 6px;
    transition: all 0.15s;
}
.doc-pill-link:hover {
    background: #dbeafe;
    color: #1e40af;
}
@media (max-width: 768px) {
    .dossier-meta-grid { grid-template-columns: 1fr; }
    .dossier-header { flex-direction: column; text-align: center; }
}
</style>

<div class="row" style="margin-bottom: 18px; align-items: center;">
  <div class="col-md-8 col-sm-12">
    <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 4px;">
      <span class="badge" style="background: #dc2626; font-size: 0.75rem; padding: 4px 10px;">
        <i class="fas fa-lock"></i> AES-256 RESTRICTED SCANNER
      </span>
      <span class="badge" style="background: #059669; font-size: 0.75rem; padding: 4px 10px;">
        <i class="fas fa-shield-alt"></i> NHA & DPDP COMPLIANT
      </span>
    </div>
    <h3 style="margin: 0; font-weight: 800; color: #0b3c5d;">
      अधिकृत प्रशासनिक क्यूआर स्कैनर एवं डिक्रिप्शन टर्मिनल
    </h3>
    <p style="margin: 2px 0 0; color: #64748b; font-size: 0.88rem;">
      केवल अधिकृत प्रशासनिक अधिकारी ही डिजिटल हेल्थ स्मार्ट कार्ड के एन्क्रिप्टेड क्यूआर कोड को डिक्रिप्ट कर सकते हैं।
    </p>
  </div>
  <div class="col-md-4 col-sm-12 text-right">
    <a href="<?php echo BASE_URL; ?>/admin/ayushman-helpdesk.php" class="btn btn-default" style="font-weight: 600; border-radius: 8px;">
      <i class="fas fa-arrow-left"></i> आवेदन सूची पर वापस जाएं
    </a>
  </div>
</div>

<div class="row">
  <!-- Left Column: Scanner Modes (Live Webcam, File Upload, Token Paste) -->
  <div class="col-lg-5 col-md-5 col-sm-12">
    <div class="scanner-card">
      <div class="scanner-header">
        <h4 style="margin: 0; font-weight: 700; font-size: 1rem;">
          <i class="fas fa-camera"></i> क्यूआर कोड स्कैनिंग टर्मिनल
        </h4>
        <span id="scanner-status-badge" class="badge" style="background: #10b981; font-size: 0.75rem;">
          Ready
        </span>
      </div>

      <div style="padding: 16px;">
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs" style="margin-bottom: 16px;">
          <li class="active">
            <a href="#tab-camera" data-toggle="tab" style="font-weight: 700; font-size: 0.86rem;">
              <i class="fas fa-video text-primary"></i> लाइव कैमरा (Webcam)
            </a>
          </li>
          <li>
            <a href="#tab-image" data-toggle="tab" style="font-weight: 700; font-size: 0.86rem;">
              <i class="fas fa-file-image text-warning"></i> फोटो अपलोड (File)
            </a>
          </li>
          <li>
            <a href="#tab-manual" data-toggle="tab" style="font-weight: 700; font-size: 0.86rem;">
              <i class="fas fa-keyboard text-info"></i> टोकन पेस्ट (Manual)
            </a>
          </li>
        </ul>

        <div class="tab-content">
          <!-- TAB 1: Live Webcam Scanner -->
          <div class="tab-pane active" id="tab-camera">
            <div class="camera-viewport-box" id="camera-box">
              <div id="qr-reader"></div>
              <div class="scan-laser-line" id="laser-line" style="display: none;"></div>
            </div>

            <div style="margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap;">
              <button type="button" id="btn-start-camera" class="btn btn-primary" style="background: #0b3c5d; border-color: #0b3c5d; font-weight: 700;">
                <i class="fas fa-play"></i> कैमरा शुरू करें (Start Camera)
              </button>
              <button type="button" id="btn-stop-camera" class="btn btn-default" style="font-weight: 700; display: none;">
                <i class="fas fa-stop"></i> कैमरा बंद करें
              </button>
            </div>
            <div style="font-size: 0.78rem; color: #64748b; margin-top: 8px;">
              <i class="fas fa-info-circle"></i> स्मार्ट आईडी कार्ड को कैमरे के सामने रखें। क्यूआर पहचानते ही डेटा तुरंत डिक्रिप्ट हो जाएगा।
            </div>
          </div>

          <!-- TAB 2: File Upload Scanner -->
          <div class="tab-pane" id="tab-image">
            <div style="border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 10px; padding: 24px; text-align: center;">
              <i class="fas fa-cloud-upload-alt fa-3x" style="color: #0b3c5d; margin-bottom: 10px;"></i>
              <h5 style="margin: 0 0 6px; font-weight: 700;">कार्ड की फोटो या स्क्रीनशॉट चुनें</h5>
              <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 14px;">
                JPEG, PNG या WEBP फाइल चुनें जिसमें क्यूआर कोड मौजूद हो।
              </p>
              <input type="file" id="qr-file-input" accept="image/*" class="form-control" style="max-width: 280px; margin: 0 auto;">
            </div>
            <div id="file-scan-status" style="margin-top: 10px; display: none;"></div>
          </div>

          <!-- TAB 3: Manual Token Input -->
          <div class="tab-pane" id="tab-manual">
            <div class="form-group">
              <label style="font-size: 0.85rem; font-weight: 700; color: #334155;">
                सत्यापन URL अथवा एन्क्रिप्टेड टोकन पेस्ट करें:
              </label>
              <textarea id="manual-token-input" rows="4" class="form-control" style="font-family: monospace; font-size: 0.82rem;" placeholder="उदा. http://localhost/.../verify-id.php?token=... अथवा SECURE-AYUSHMAN:v1:..."></textarea>
            </div>
            <button type="button" id="btn-decrypt-manual" class="btn btn-success btn-block" style="font-weight: 700;">
              <i class="fas fa-unlock-alt"></i> टोकन डिक्रिप्ट करें (Decrypt Payload)
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Right Column: Decrypted Citizen Record & Dossier -->
  <div class="col-lg-7 col-md-7 col-sm-12">
    
    <!-- Empty Placeholder State -->
    <div id="dossier-placeholder" style="<?php echo ($initialDecrypted) ? 'display: none;' : ''; ?> background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 60px 20px; text-align: center; color: #64748b;">
      <i class="fas fa-qrcode fa-5x text-muted" style="margin-bottom: 18px; opacity: 0.35;"></i>
      <h4 style="font-weight: 800; color: #1e293b; margin-top: 0;">स्कैनर प्रतीक्षा कर रहा है (Awaiting Scan)</h4>
      <p style="max-width: 440px; margin: 0 auto; font-size: 0.9rem;">
        बाईं ओर से कैमरा शुरू करें, स्मार्ट कार्ड का स्क्रीनशॉट अपलोड करें अथवा एन्क्रिप्टेड टोकन पेस्ट करें। डिक्रिप्टेड नागरिक विवरण यहाँ प्रदर्शित होगा।
      </p>
    </div>

    <!-- Alert / Error Box -->
    <div id="scan-alert-box" style="display: none; margin-bottom: 18px;"></div>

    <!-- Live Decrypted Citizen Dossier -->
    <div id="citizen-dossier-panel" style="<?php echo ($initialDecrypted) ? 'display: block;' : 'display: none;'; ?>">
      <div class="citizen-dossier-box">
        
        <!-- Authenticated Top Banner -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 10px 14px; border-radius: 8px;">
          <div>
            <span class="badge" style="background: #059669; font-size: 0.8rem; padding: 5px 10px;">
              <i class="fas fa-check-double"></i> AES-256 SIGNATURE VERIFIED
            </span>
            <span id="dossier-verified-timestamp" style="font-size: 0.78rem; color: #065f46; margin-left: 8px; font-weight: 600;">
              <?php echo date('d M Y, h:i A'); ?>
            </span>
          </div>
          <div style="font-family: monospace; font-size: 0.78rem; font-weight: 800; color: #065f46;">
            HMAC-SHA256 VALID
          </div>
        </div>

        <!-- Citizen Header -->
        <div class="dossier-header">
          <img id="dossier-photo" src="<?php echo !empty($initialDbRecord['photo_url']) ? BASE_URL . '/' . htmlspecialchars($initialDbRecord['photo_url']) : BASE_URL . '/images/tuser1.jpg'; ?>" alt="Photo" class="dossier-photo">
          <div style="flex: 1;">
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
              <h3 id="dossier-name" style="margin: 0; font-weight: 800; color: #0b3c5d;">
                <?php echo htmlspecialchars($initialDecrypted['full_name'] ?? ''); ?>
              </h3>
              <span id="dossier-gender-dob" class="badge" style="background: #0284c7; font-size: 0.75rem;">
                <?php echo htmlspecialchars($initialDecrypted['gender'] ?? 'Male'); ?> | DOB: <?php echo htmlspecialchars($initialDecrypted['dob'] ?? 'N/A'); ?>
              </span>
            </div>
            <div style="margin-top: 6px; font-size: 0.88rem; color: #475569;">
              <i class="fas fa-phone text-muted"></i> <span id="dossier-mobile"><?php echo htmlspecialchars($initialDecrypted['mobile'] ?? ''); ?></span> &nbsp;|&nbsp; 
              <i class="fas fa-envelope text-muted"></i> <span id="dossier-email"><?php echo htmlspecialchars($initialDecrypted['email'] ?? ''); ?></span>
            </div>
            <div style="margin-top: 6px;">
              <span class="badge" style="background: #10b981; font-size: 0.78rem; padding: 4px 8px;">
                <i class="fas fa-fingerprint"></i> आधार e-KYC: <span id="dossier-aadhaar">XXXX-XXXX-<?php echo htmlspecialchars($initialDecrypted['aadhaar_last4'] ?? '0000'); ?></span>
              </span>
              <span id="dossier-status-pill" class="badge" style="background: #059669; font-size: 0.78rem; text-transform: uppercase;">
                <?php echo htmlspecialchars($initialDbRecord['status'] ?? 'Verified'); ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Detailed Identification Grid -->
        <h5 style="font-weight: 800; color: #0b3c5d; margin-top: 0; margin-bottom: 10px;">
          <i class="fas fa-id-card text-warning"></i> राष्ट्रीय पहचान एवं स्वास्थ्य खाता विवरण
        </h5>
        
        <div class="dossier-meta-grid">
          <div class="dossier-field">
            <span class="dossier-field-label">आवेदन क्रमांक (Application No)</span>
            <span class="dossier-field-value" id="dossier-app-no" style="color: #0b3c5d; font-family: monospace;">
              <?php echo htmlspecialchars($initialDecrypted['app_no'] ?? ''); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">डिजिटल स्मार्ट कार्ड आईडी (Card ID)</span>
            <span class="dossier-field-value" id="dossier-card-id" style="color: #059669; font-family: monospace;">
              <?php echo htmlspecialchars($initialDecrypted['card_id'] ?? ''); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">१४ अंकों का आभा नंबर (ABHA ID)</span>
            <span class="dossier-field-value" id="dossier-abha" style="color: #0284c7; font-family: monospace;">
              <?php echo htmlspecialchars($initialDecrypted['abha_number'] ?: 'लागू नहीं (Not Provided)'); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">राशन कार्ड नंबर (Ration Card)</span>
            <span class="dossier-field-value" id="dossier-ration">
              <?php echo htmlspecialchars($initialDecrypted['ration_card_no'] ?: 'लागू नहीं'); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">BOCW / श्रमिक पंजीकरण संख्या</span>
            <span class="dossier-field-value" id="dossier-bocw">
              <?php echo htmlspecialchars($initialDecrypted['bocw_reg_no'] ?: 'लागू नहीं'); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">PMJAY / आयुष्मान भारत पत्र संख्या</span>
            <span class="dossier-field-value" id="dossier-pmjay">
              <?php echo htmlspecialchars($initialDecrypted['pmjay_letter_no'] ?: 'लागू नहीं'); ?>
            </span>
          </div>
        </div>

        <!-- Address -->
        <div class="dossier-field" style="margin-bottom: 16px;">
          <span class="dossier-field-label">नागरिक का स्थायी पता (Residential Address)</span>
          <span class="dossier-field-value" id="dossier-address" style="font-weight: 500; font-size: 0.88rem;">
            <?php 
              $addrArr = array_filter([$initialDecrypted['address'] ?? '', $initialDecrypted['district'] ?? '', $initialDecrypted['state'] ?? '', $initialDecrypted['pincode'] ?? '']);
              echo htmlspecialchars(implode(', ', $addrArr) ?: 'N/A');
            ?>
          </span>
        </div>

        <!-- Uploaded Documents Preview Section -->
        <h5 style="font-weight: 800; color: #0b3c5d; margin-top: 18px; margin-bottom: 10px;">
          <i class="fas fa-folder-open text-primary"></i> संलग्न सरकारी दस्तावेज (Uploaded Citizen Documents)
        </h5>
        
        <div id="dossier-documents-box" style="margin-bottom: 18px;">
          <!-- Dynamically populated or rendered from initialDbRecord -->
          <?php if (!empty($initialDbRecord['photo_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['photo_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-camera"></i> नागरिक फोटो देखें
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['abha_card_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['abha_card_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-id-card"></i> आभा कार्ड देखें
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['ayushman_card_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['ayushman_card_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-heartbeat text-danger"></i> आयुष्मान कार्ड देखें
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['ration_card_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['ration_card_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-wheat-awn text-warning"></i> राशन कार्ड देखें
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['bocw_card_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['bocw_card_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-hard-hat text-secondary"></i> BOCW श्रमिक कार्ड
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['pmjay_letter_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['pmjay_letter_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-envelope-open-text text-success"></i> PMJAY पत्र
            </a>
          <?php endif; ?>
        </div>

        <!-- Administrative Actions -->
        <div style="border-top: 1.5px dashed #cbd5e1; padding-top: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
          <a id="btn-view-card" href="<?php echo BASE_URL; ?>/ayushman-card.php?app_no=<?php echo urlencode($initialDecrypted['app_no'] ?? ''); ?>" target="_blank" class="btn btn-primary" style="background: #0b3c5d; border-color: #0b3c5d; font-weight: 700;">
            <i class="fas fa-id-card"></i> स्मार्ट आईडी कार्ड प्रिंट/देखें
          </a>
          <button type="button" id="btn-quick-approve" class="btn btn-success" style="background: #059669; border-color: #059669; font-weight: 700;">
            <i class="fas fa-check-circle"></i> स्वीकृत करें (Approve)
          </button>
          <button type="button" id="btn-scan-next" class="btn btn-default" style="font-weight: 700;">
            <i class="fas fa-redo"></i> अगला कार्ड स्कैन करें
          </button>
        </div>

      </div>
    </div>

  </div>
</div>

<!-- Include html5-qrcode.min.js -->
<script src="<?php echo BASE_URL; ?>/js/html5-qrcode.min.js"></script>

<script>
(function() {
  const baseUrl = "<?php echo BASE_URL; ?>";
  const btnStartCamera = document.getElementById("btn-start-camera");
  const btnStopCamera = document.getElementById("btn-stop-camera");
  const laserLine = document.getElementById("laser-line");
  const scannerStatusBadge = document.getElementById("scanner-status-badge");
  const qrFileInput = document.getElementById("qr-file-input");
  const btnDecryptManual = document.getElementById("btn-decrypt-manual");
  const manualTokenInput = document.getElementById("manual-token-input");
  
  const dossierPlaceholder = document.getElementById("dossier-placeholder");
  const citizenDossierPanel = document.getElementById("citizen-dossier-panel");
  const scanAlertBox = document.getElementById("scan-alert-box");

  let html5QrCode = null;
  let isScanning = false;
  let currentDbAppId = <?php echo (int)($initialDbRecord['id'] ?? 0); ?>;
  let currentAppNo = <?php echo json_encode($initialDecrypted['app_no'] ?? ''); ?>;

  // Sound Effect via Web Audio API
  function playSuccessBeep() {
    try {
      const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = audioCtx.createOscillator();
      const gain = audioCtx.createGain();
      osc.type = "sine";
      osc.frequency.setValueAtTime(880, audioCtx.currentTime); // A5 note
      gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.01, audioCtx.currentTime + 0.25);
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start();
      osc.stop(audioCtx.currentTime + 0.25);
    } catch(e) {}
  }

  // 1. Process QR Scan Result
  function handleQrScanSuccess(decodedText) {
    playSuccessBeep();
    if (html5QrCode && isScanning) {
      stopCameraScanner();
    }
    scannerStatusBadge.className = "badge";
    scannerStatusBadge.style.background = "#0284c7";
    scannerStatusBadge.innerText = "डिक्रिप्ट हो रहा है...";

    // AJAX Call to Decrypt Endpoint
    const formData = new FormData();
    formData.append("token_or_url", decodedText);

    fetch(baseUrl + "/admin/api/decrypt_scanner.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success && data.payload) {
        scannerStatusBadge.style.background = "#059669";
        scannerStatusBadge.innerText = "डिक्रिप्टेड ✓";
        renderDossier(data.payload, data.db_record, data.verified_at);
      } else {
        scannerStatusBadge.style.background = "#dc2626";
        scannerStatusBadge.innerText = "अमान्य हस्ताक्षर";
        showErrorAlert(data.message || "क्रिप्टोग्राफिक डिक्रिप्शन विफल रहा।");
      }
    })
    .catch(err => {
      showErrorAlert("सर्वर त्रुटि: " + err);
    });
  }

  // Render Dossier into UI
  function renderDossier(payload, dbRecord, verifiedAt) {
    scanAlertBox.style.display = "none";
    dossierPlaceholder.style.display = "none";
    citizenDossierPanel.style.display = "block";

    currentAppNo = payload.app_no || "";
    currentDbAppId = dbRecord ? dbRecord.id : 0;

    document.getElementById("dossier-verified-timestamp").innerText = verifiedAt || "";
    document.getElementById("dossier-name").innerText = payload.full_name || "नागरिक";
    document.getElementById("dossier-gender-dob").innerText = (payload.gender || "Male") + " | DOB: " + (payload.dob || "N/A");
    document.getElementById("dossier-mobile").innerText = payload.mobile || "N/A";
    document.getElementById("dossier-email").innerText = payload.email || "N/A";
    document.getElementById("dossier-aadhaar").innerText = "XXXX-XXXX-" + (payload.aadhaar_last4 || "0000");
    document.getElementById("dossier-app-no").innerText = payload.app_no || "N/A";
    document.getElementById("dossier-card-id").innerText = payload.card_id || "N/A";
    document.getElementById("dossier-abha").innerText = payload.abha_number || "लागू नहीं";
    document.getElementById("dossier-ration").innerText = payload.ration_card_no || "लागू नहीं";
    document.getElementById("dossier-bocw").innerText = payload.bocw_reg_no || "लागू नहीं";
    document.getElementById("dossier-pmjay").innerText = payload.pmjay_letter_no || "लागू नहीं";

    const addrArr = [payload.address, payload.district, payload.state, payload.pincode].filter(Boolean);
    document.getElementById("dossier-address").innerText = addrArr.join(", ") || "N/A";

    // Photo
    const photoEl = document.getElementById("dossier-photo");
    if (dbRecord && dbRecord.photo_url) {
      photoEl.src = baseUrl + "/" + dbRecord.photo_url;
    } else {
      photoEl.src = baseUrl + "/images/tuser1.jpg";
    }

    // Status Pill
    const statusPill = document.getElementById("dossier-status-pill");
    if (dbRecord && dbRecord.status) {
      statusPill.innerText = dbRecord.status;
      statusPill.style.background = (dbRecord.status === "approved") ? "#059669" : ((dbRecord.status === "verified") ? "#10b981" : "#0284c7");
    }

    // Documents Links
    const docBox = document.getElementById("dossier-documents-box");
    docBox.innerHTML = "";
    if (dbRecord) {
      if (dbRecord.photo_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.photo_url}" target="_blank" class="doc-pill-link"><i class="fas fa-camera"></i> नागरिक फोटो देखें</a>`;
      }
      if (dbRecord.abha_card_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.abha_card_url}" target="_blank" class="doc-pill-link"><i class="fas fa-id-card"></i> आभा कार्ड देखें</a>`;
      }
      if (dbRecord.ayushman_card_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.ayushman_card_url}" target="_blank" class="doc-pill-link"><i class="fas fa-heartbeat text-danger"></i> आयुष्मान कार्ड देखें</a>`;
      }
      if (dbRecord.ration_card_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.ration_card_url}" target="_blank" class="doc-pill-link"><i class="fas fa-wheat-awn text-warning"></i> राशन कार्ड देखें</a>`;
      }
      if (dbRecord.bocw_card_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.bocw_card_url}" target="_blank" class="doc-pill-link"><i class="fas fa-hard-hat text-secondary"></i> BOCW श्रमिक कार्ड</a>`;
      }
      if (dbRecord.pmjay_letter_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.pmjay_letter_url}" target="_blank" class="doc-pill-link"><i class="fas fa-envelope-open-text text-success"></i> PMJAY पत्र</a>`;
      }
    }

    // Update Card Link
    document.getElementById("btn-view-card").href = baseUrl + "/ayushman-card.php?app_no=" + encodeURIComponent(payload.app_no);
  }

  function showErrorAlert(msg) {
    scanAlertBox.className = "alert alert-danger";
    scanAlertBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + msg;
    scanAlertBox.style.display = "block";
    citizenDossierPanel.style.display = "none";
    dossierPlaceholder.style.display = "block";
  }

  // 2. Camera Controls
  if (btnStartCamera) {
    btnStartCamera.addEventListener("click", function() {
      if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("qr-reader");
      }

      scannerStatusBadge.className = "badge";
      scannerStatusBadge.style.background = "#f59e0b";
      scannerStatusBadge.innerText = "कैमरा चालू...";

      const config = { fps: 10, qrbox: { width: 250, height: 250 } };
      html5QrCode.start({ facingMode: "environment" }, config, handleQrScanSuccess)
        .then(() => {
          isScanning = true;
          btnStartCamera.style.display = "none";
          btnStopCamera.style.display = "inline-block";
          laserLine.style.display = "block";
          scannerStatusBadge.style.background = "#10b981";
          scannerStatusBadge.innerText = "स्कैनिंग जारी...";
        })
        .catch(err => {
          alert("कैमरा शुरू करने में असमर्थ: " + err);
          scannerStatusBadge.style.background = "#dc2626";
          scannerStatusBadge.innerText = "Camera Error";
        });
    });
  }

  function stopCameraScanner() {
    if (html5QrCode && isScanning) {
      html5QrCode.stop().then(() => {
        isScanning = false;
        btnStartCamera.style.display = "inline-block";
        btnStopCamera.style.display = "none";
        laserLine.style.display = "none";
      }).catch(err => console.error(err));
    }
  }

  if (btnStopCamera) {
    btnStopCamera.addEventListener("click", stopCameraScanner);
  }

  // 3. File Input Scanner
  if (qrFileInput) {
    qrFileInput.addEventListener("change", function(e) {
      if (e.target.files.length === 0) return;
      const file = e.target.files[0];
      
      const fileScanner = new Html5Qrcode("qr-reader");
      scannerStatusBadge.innerText = "फोटो स्कैनिंग...";
      
      fileScanner.scanFile(file, true)
        .then(decodedText => {
          handleQrScanSuccess(decodedText);
        })
        .catch(err => {
          showErrorAlert("इस चित्र में कोई वैध क्यूआर कोड नहीं पाया गया। कृपया स्पष्ट फोटो अपलोड करें।");
        });
    });
  }

  // 4. Manual Decrypt Button
  if (btnDecryptManual) {
    btnDecryptManual.addEventListener("click", function() {
      const val = manualTokenInput.value.trim();
      if (!val) {
        alert("कृपया टोकन अथवा URL दर्ज करें।");
        return;
      }
      handleQrScanSuccess(val);
    });
  }

  // 5. Quick Approve Action
  const btnQuickApprove = document.getElementById("btn-quick-approve");
  if (btnQuickApprove) {
    btnQuickApprove.addEventListener("click", function() {
      if (!currentDbAppId) {
        alert("कोई सक्रिय आवेदन रिकॉर्ड नहीं मिला।");
        return;
      }
      if (!confirm("क्या आप इस आवेदन को 'Approved' (स्वीकृत) के रूप में चिह्नित करना चाहते हैं?")) {
        return;
      }

      const form = document.createElement("form");
      form.method = "POST";
      form.action = baseUrl + "/admin/ayushman-helpdesk.php";
      form.innerHTML = `
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="app_id" value="${currentDbAppId}">
        <input type="hidden" name="status" value="approved">
        <input type="hidden" name="admin_notes" value="Verified and approved via Admin Secure QR Scanner.">
      `;
      document.body.appendChild(form);
      form.submit();
    });
  }

  // 6. Scan Next Button
  const btnScanNext = document.getElementById("btn-scan-next");
  if (btnScanNext) {
    btnScanNext.addEventListener("click", function() {
      citizenDossierPanel.style.display = "none";
      dossierPlaceholder.style.display = "block";
      scanAlertBox.style.display = "none";
      if (manualTokenInput) manualTokenInput.value = "";
      if (qrFileInput) qrFileInput.value = "";
      scannerStatusBadge.style.background = "#10b981";
      scannerStatusBadge.innerText = "Ready";
    });
  }

})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
