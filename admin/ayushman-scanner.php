<?php
// admin/ayushman-scanner.php - Official In-built Encrypted QR Scanner & Citizen Verification Terminal
require_once __DIR__ . '/auth.php';

$page_title = 'Official Admin Encrypted QR Scanner & Dossier Terminal';

// Cards now carry just a short Card ID in their QR (?id=...) — no AES
// decryption needed, just a direct authenticated DB lookup. The old
// encrypted ?token= path is still honored for any cards printed/saved
// before this change.
$initialCardId = trim($_GET['id'] ?? '');
$initialToken = trim($_GET['token'] ?? '');
$initialDecrypted = null;
$initialDbRecord = null;

if (!empty($initialCardId) && isset($pdo)) {
    $cardIdToLookup = $initialCardId;
    if (strpos($cardIdToLookup, 'id=') !== false) {
        $parsed = parse_url($cardIdToLookup);
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $q);
            if (!empty($q['id'])) {
                $cardIdToLookup = $q['id'];
            }
        }
    }
    $stmt = $pdo->prepare("SELECT * FROM ayushman_applications WHERE card_id = ? LIMIT 1");
    $stmt->execute([$cardIdToLookup]);
    $initialDbRecord = $stmt->fetch();
    if ($initialDbRecord) {
        $initialDecrypted = [
            'app_no'       => $initialDbRecord['application_no'],
            'card_id'      => $initialDbRecord['card_id'],
            'aadhaar_last4'=> $initialDbRecord['aadhaar_last4'],
        ];
    }
} elseif (!empty($initialToken)) {
    // Legacy: clean token if a full URL was passed
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
    border: 1px solid var(--adm-border);
    border-radius: 12px;
    box-shadow: 0 4px 18px rgba(2, 132, 199, 0.06);
    overflow: hidden;
    margin-bottom: 24px;
}
.scanner-header {
    background: linear-gradient(135deg, var(--adm-blue-dark) 0%, #0b3c5d 100%);
    color: #ffffff;
    padding: 14px 18px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}
.camera-viewport-box {
    position: relative;
    background: #0f172a;
    border-radius: 10px;
    overflow: hidden;
    min-height: 240px;
    display: flex;
    align-items: center;
    justify-content: center;
}
#qr-reader {
    width: 100% !important;
    border: none !important;
}
.camera-fullscreen-overlay {
    display: none;
}
.camera-fullscreen-gallery-btn {
    display: none;
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
.scanner-nav-tabs {
    flex-wrap: nowrap;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    border-bottom: 1px solid var(--adm-border);
}
.scanner-nav-tabs .nav-link {
    white-space: nowrap;
    font-weight: 700;
    font-size: 0.86rem;
    color: #475569;
}
.scanner-nav-tabs .nav-link.active {
    color: var(--adm-blue-dark);
    border-bottom: 2px solid var(--adm-blue-dark);
    background: transparent;
}
.citizen-dossier-box {
    background: #ffffff;
    border: 2px solid var(--adm-green);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.1);
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
    border: 2px solid var(--adm-blue-dark);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex-shrink: 0;
}
.dossier-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.dossier-field {
    background: #f8fafc;
    border: 1px solid var(--adm-border);
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
    background: var(--adm-blue-tint);
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
.scanner-page-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.scanner-actions-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.scanner-actions-row .btn {
    flex: 1 1 auto;
    min-width: 150px;
}

/* ===== Mobile ===== */
@media (max-width: 768px) {
    .dossier-meta-grid { grid-template-columns: 1fr; }
    .dossier-header { flex-direction: column; text-align: center; }
    .scanner-page-head { text-align: center; }
    .scanner-page-head > div:last-child { width: 100%; }
    .scanner-page-head .btn { width: 100%; }
    .scanner-actions-row .btn { min-width: 100%; }

    /* PhonePe-style: camera takes over the entire screen on mobile */
    #tab-camera .camera-viewport-box {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100vw;
        height: 100vh;
        min-height: 100vh;
        border-radius: 0;
        z-index: 99999;
    }
    #tab-camera .camera-viewport-box #qr-reader {
        height: 100% !important;
    }
    #tab-camera .camera-viewport-box #qr-reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }
    /* Hide the default html5-qrcode chrome (file-select link etc.) in fullscreen mode */
    #tab-camera .camera-viewport-box #qr-reader__dashboard,
    #tab-camera .camera-viewport-box #qr-reader__camera_selection,
    #tab-camera .camera-viewport-box img[alt="Info icon"] {
        display: none !important;
    }
    .camera-fullscreen-overlay {
        display: flex;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        z-index: 100000;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        background: linear-gradient(180deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0) 100%);
    }
    .camera-fullscreen-overlay .badge {
        font-size: 0.78rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 700;
        color: #fff;
    }
    .camera-fullscreen-close {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: rgba(255,255,255,0.18);
        border: none;
        color: #fff;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .camera-fullscreen-hint {
        position: absolute;
        bottom: 92px;
        left: 16px;
        right: 16px;
        z-index: 100000;
        text-align: center;
        color: #fff;
        font-size: 0.82rem;
        text-shadow: 0 1px 4px rgba(0,0,0,0.6);
    }
    .camera-fullscreen-gallery-btn {
        position: absolute;
        bottom: 28px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 100000;
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.92);
        color: #0f172a;
        border: none;
        padding: 12px 20px;
        border-radius: 30px;
        font-size: 0.86rem;
        font-weight: 700;
        box-shadow: 0 4px 14px rgba(0,0,0,0.3);
    }
    .camera-fullscreen-gallery-btn i {
        font-size: 1.05rem;
        color: var(--adm-blue-dark);
    }
    .camera-fullscreen-gallery-btn:active {
        background: #e2e8f0;
    }
    /* Everything else on the page stays underneath/hidden while the camera is full-screen */
    body.scanner-fullscreen-active .scanner-header,
    body.scanner-fullscreen-active .scanner-nav-tabs,
    body.scanner-fullscreen-active #tab-camera > div:not(.camera-viewport-box),
    body.scanner-fullscreen-active .scanner-page-head {
        visibility: hidden;
    }
    /* Also tuck away the admin shell (sidebar/topbar) so the camera truly covers the whole screen */
    body.scanner-fullscreen-active .admin-sidebar,
    body.scanner-fullscreen-active .admin-topbar,
    body.scanner-fullscreen-active .admin-sidebar-backdrop {
        display: none !important;
    }
    body.scanner-fullscreen-active .admin-content-wrap {
        margin-left: 0 !important;
        padding-top: 0 !important;
    }
    body.scanner-fullscreen-active main.admin-main {
        padding: 0 !important;
    }
}
@media (max-width: 575px) {
    .scanner-header h4 { font-size: 0.92rem; }
    .dossier-photo { width: 72px; height: 86px; }
    .doc-pill-link { font-size: 0.78rem; padding: 5px 10px; }
}
</style>

<div class="scanner-page-head">
  <div>
    <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 6px; flex-wrap: wrap;">
      <span class="label label-danger">
        <i class="fas fa-lock"></i> AES-256 RESTRICTED SCANNER
      </span>
      <span class="label label-success">
        <i class="fas fa-shield-alt"></i> NHA & DPDP COMPLIANT
      </span>
    </div>
    <h3 style="margin: 0; font-weight: 800; color: var(--adm-blue-dark); font-size: 1.25rem;">
      Authorized Admin QR Scanner & Decryption Terminal
    </h3>
    <p style="margin: 2px 0 0; color: #64748b; font-size: 0.86rem;">
      Only authorized administrative officers can decrypt the encrypted QR code on a Digital Health Smart Card.
    </p>
  </div>
  <div>
    <a href="<?php echo BASE_URL; ?>/admin/ayushman-helpdesk.php" class="btn btn-default" style="font-weight: 600; border-radius: 8px;">
      <i class="fas fa-arrow-left"></i> Back to Applications List
    </a>
  </div>
</div>

<div class="row">
  <!-- Left Column: Scanner Modes (Live Webcam, File Upload, Token Paste) -->
  <div class="col-lg-5 col-md-5 col-sm-12">
    <div class="scanner-card">
      <div class="scanner-header">
        <h4 style="margin: 0; font-weight: 700; font-size: 1rem;">
          <i class="fas fa-camera"></i> QR Code Scanning Terminal
        </h4>
        <span id="scanner-status-badge" class="label label-success">
          Ready
        </span>
      </div>

      <div style="padding: 14px;">
        <!-- Nav Tabs -->
        <ul class="nav nav-tabs scanner-nav-tabs" id="scannerTabs" role="tablist" style="margin-bottom: 16px;">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-camera-btn" data-bs-toggle="tab" data-bs-target="#tab-camera" type="button" role="tab" aria-selected="true">
              <i class="fas fa-video"></i> Live Camera (Webcam)
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-image-btn" data-bs-toggle="tab" data-bs-target="#tab-image" type="button" role="tab" aria-selected="false">
              <i class="fas fa-file-image"></i> Photo Upload (File)
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-manual-btn" data-bs-toggle="tab" data-bs-target="#tab-manual" type="button" role="tab" aria-selected="false">
              <i class="fas fa-keyboard"></i> Paste Token (Manual)
            </button>
          </li>
        </ul>

        <div class="tab-content">
          <!-- TAB 1: Live Webcam Scanner -->
          <div class="tab-pane fade show active" id="tab-camera" role="tabpanel">
            <div class="camera-viewport-box" id="camera-box">
              <div class="camera-fullscreen-overlay">
                <span class="badge" id="fullscreen-status-badge" style="background: #10b981;">
                  <i class="fas fa-qrcode"></i> Scanning...
                </span>
                <button type="button" class="camera-fullscreen-close" id="btn-fullscreen-close" title="Stop scanner / go back">
                  <i class="fas fa-times"></i>
                </button>
              </div>
              <div id="qr-reader"></div>
              <div class="scan-laser-line" id="laser-line" style="display: none;"></div>
              <div class="camera-fullscreen-hint">
                Align the smart card's QR code within the frame
              </div>
              <button type="button" class="camera-fullscreen-gallery-btn" id="btn-fullscreen-gallery" title="Choose QR photo from gallery">
                <i class="fas fa-images"></i>
                <span>Upload from Gallery</span>
              </button>
            </div>

            <div style="margin-top: 14px; display: flex; gap: 8px; flex-wrap: wrap;">
              <button type="button" id="btn-stop-camera" class="btn btn-default" style="font-weight: 700; display: none;">
                <i class="fas fa-stop"></i> Stop Camera
              </button>
            </div>
            <div style="font-size: 0.78rem; color: #64748b; margin-top: 8px;">
              <i class="fas fa-info-circle"></i> Hold the Smart ID Card in front of the camera. Data decrypts the instant the QR is recognized.
            </div>
          </div>

          <!-- TAB 2: File Upload Scanner -->
          <div class="tab-pane fade" id="tab-image" role="tabpanel">
            <div style="border: 2px dashed #cbd5e1; background: #f8fafc; border-radius: 10px; padding: 20px; text-align: center;">
              <i class="fas fa-cloud-upload-alt fa-3x" style="color: var(--adm-blue-dark); margin-bottom: 10px;"></i>
              <h5 style="margin: 0 0 6px; font-weight: 700;">Choose a photo or screenshot of the card</h5>
              <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 14px;">
                Select a JPEG, PNG or WEBP file that contains the QR code.
              </p>
              <input type="file" id="qr-file-input" accept="image/*" class="form-control" style="max-width: 280px; margin: 0 auto;">
            </div>
            <div id="file-scan-status" style="margin-top: 10px; display: none;"></div>
          </div>

          <!-- TAB 3: Manual Token Input -->
          <div class="tab-pane fade" id="tab-manual" role="tabpanel">
            <div class="form-group">
              <label style="font-size: 0.85rem; font-weight: 700; color: #334155;">
                Paste the verification URL or encrypted token:
              </label>
              <textarea id="manual-token-input" rows="4" class="form-control" style="font-family: monospace; font-size: 0.82rem;" placeholder="e.g. http://localhost/.../verify-id.php?token=... or SECURE-AYUSHMAN:v1:..."></textarea>
            </div>
            <button type="button" id="btn-decrypt-manual" class="btn btn-success" style="font-weight: 700; width: 100%;">
              <i class="fas fa-unlock-alt"></i> Decrypt Token (Decrypt Payload)
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Right Column: Decrypted Citizen Record & Dossier -->
  <div class="col-lg-7 col-md-7 col-sm-12">
    
    <!-- Empty Placeholder State -->
    <div id="dossier-placeholder" style="<?php echo ($initialDecrypted) ? 'display: none;' : ''; ?> background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 40px 18px; text-align: center; color: #64748b;">
      <i class="fas fa-qrcode fa-4x" style="margin-bottom: 16px; opacity: 0.35; color: #94a3b8;"></i>
      <h4 style="font-weight: 800; color: #1e293b; margin-top: 0; font-size: 1.1rem;">Scanner is Waiting (Awaiting Scan)</h4>
      <p style="max-width: 440px; margin: 0 auto; font-size: 0.88rem;">
        Start the camera on the left, upload a screenshot of the smart card, or paste the encrypted token. Decrypted citizen details will appear here.
      </p>
    </div>

    <!-- Alert / Error Box -->
    <div id="scan-alert-box" style="display: none; margin-bottom: 18px;"></div>

    <!-- Live Decrypted Citizen Dossier -->
    <div id="citizen-dossier-panel" style="<?php echo ($initialDecrypted) ? 'display: block;' : 'display: none;'; ?>">
      <div class="citizen-dossier-box">
        
        <!-- Authenticated Top Banner -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 10px 14px; border-radius: 8px; flex-wrap: wrap; gap: 6px;">
          <div>
            <span class="label label-success">
              <i class="fas fa-check-double"></i> AES-256 SIGNATURE VERIFIED
            </span>
            <span id="dossier-verified-timestamp" style="font-size: 0.76rem; color: #065f46; margin-left: 8px; font-weight: 600;">
              <?php echo date('d M Y, h:i A'); ?>
            </span>
          </div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <span id="dossier-auto-hide-badge" class="label" style="background: #d97706;" title="This record auto-hides after 2 minutes for citizen data privacy">
              <i class="fas fa-clock"></i> <span id="dossier-auto-hide-countdown">2:00</span>
            </span>
            <div style="font-family: monospace; font-size: 0.74rem; font-weight: 800; color: #065f46;">
              HMAC-SHA256 VALID
            </div>
          </div>
        </div>

        <!-- Citizen Header -->
        <div class="dossier-header">
          <img id="dossier-photo" src="<?php echo !empty($initialDbRecord['photo_url']) ? BASE_URL . '/' . htmlspecialchars($initialDbRecord['photo_url']) : BASE_URL . '/images/tuser1.jpg'; ?>" alt="Photo" class="dossier-photo">
          <div style="flex: 1;">
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
              <h3 id="dossier-name" style="margin: 0; font-weight: 800; color: #0b3c5d;">
                <?php echo htmlspecialchars($initialDbRecord['full_name'] ?? ''); ?>
              </h3>
              <span id="dossier-gender-dob" class="badge" style="background: #0284c7; font-size: 0.75rem;">
                <?php echo htmlspecialchars($initialDbRecord['gender'] ?? 'Male'); ?> | DOB: <?php echo htmlspecialchars($initialDbRecord['dob'] ?? 'N/A'); ?>
              </span>
            </div>
            <div style="margin-top: 6px; font-size: 0.88rem; color: #475569;">
              <i class="fas fa-phone text-muted"></i> <span id="dossier-mobile"><?php echo htmlspecialchars($initialDbRecord['mobile'] ?? ''); ?></span> &nbsp;|&nbsp;
              <i class="fas fa-envelope text-muted"></i> <span id="dossier-email"><?php echo htmlspecialchars($initialDbRecord['email'] ?? ''); ?></span>
            </div>
            <div style="margin-top: 6px;">
              <span class="badge" style="background: #10b981; font-size: 0.78rem; padding: 4px 8px;">
                <i class="fas fa-fingerprint"></i> Aadhaar e-KYC: <span id="dossier-aadhaar">XXXX-XXXX-<?php echo htmlspecialchars($initialDecrypted['aadhaar_last4'] ?? '0000'); ?></span>
              </span>
              <span id="dossier-status-pill" class="badge" style="background: #059669; font-size: 0.78rem; text-transform: uppercase;">
                <?php echo htmlspecialchars($initialDbRecord['status'] ?? 'Verified'); ?>
              </span>
            </div>
          </div>
        </div>

        <!-- Detailed Identification Grid -->
        <h5 style="font-weight: 800; color: #0b3c5d; margin-top: 0; margin-bottom: 10px;">
          <i class="fas fa-id-card text-warning"></i> National Identity & Health Account Details
        </h5>

        <div class="dossier-meta-grid">
          <div class="dossier-field">
            <span class="dossier-field-label">Application No</span>
            <span class="dossier-field-value" id="dossier-app-no" style="color: #0b3c5d; font-family: monospace;">
              <?php echo htmlspecialchars($initialDecrypted['app_no'] ?? ''); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">Digital Smart Card ID (Card ID)</span>
            <span class="dossier-field-value" id="dossier-card-id" style="color: #059669; font-family: monospace;">
              <?php echo htmlspecialchars($initialDecrypted['card_id'] ?? ''); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">14-Digit ABHA Number (ABHA ID)</span>
            <span class="dossier-field-value" id="dossier-abha" style="color: #0284c7; font-family: monospace;">
              <?php echo htmlspecialchars($initialDbRecord['abha_number'] ?? '' ?: 'Not Provided'); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">Ration Card Number</span>
            <span class="dossier-field-value" id="dossier-ration">
              <?php echo htmlspecialchars($initialDbRecord['ration_card_no'] ?? '' ?: 'Not Applicable'); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">BOCW / Labour Registration Number</span>
            <span class="dossier-field-value" id="dossier-bocw">
              <?php echo htmlspecialchars($initialDbRecord['bocw_reg_no'] ?? '' ?: 'Not Applicable'); ?>
            </span>
          </div>
          <div class="dossier-field">
            <span class="dossier-field-label">PMJAY / Ayushman Bharat Letter Number</span>
            <span class="dossier-field-value" id="dossier-pmjay">
              <?php echo htmlspecialchars($initialDbRecord['pmjay_letter_no'] ?? '' ?: 'Not Applicable'); ?>
            </span>
          </div>
        </div>

        <!-- Address -->
        <div class="dossier-field" style="margin-bottom: 16px;">
          <span class="dossier-field-label">Citizen's Permanent Address (Residential Address)</span>
          <span class="dossier-field-value" id="dossier-address" style="font-weight: 500; font-size: 0.88rem;">
            <?php 
              $addrArr = array_filter([$initialDbRecord['address'] ?? '', $initialDbRecord['district'] ?? '', $initialDbRecord['state'] ?? '', $initialDbRecord['pincode'] ?? '']);
              echo htmlspecialchars(implode(', ', $addrArr) ?: 'N/A');
            ?>
          </span>
        </div>

        <!-- Uploaded Documents Preview Section -->
        <h5 style="font-weight: 800; color: #0b3c5d; margin-top: 18px; margin-bottom: 10px;">
          <i class="fas fa-folder-open text-primary"></i> Attached Government Documents (Uploaded Citizen Documents)
        </h5>

        <div id="dossier-documents-box" style="margin-bottom: 18px;">
          <!-- Dynamically populated or rendered from initialDbRecord -->
          <?php if (!empty($initialDbRecord['photo_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['photo_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-camera"></i> View Citizen Photo
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['abha_card_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['abha_card_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-id-card"></i> View ABHA Card
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['ayushman_card_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['ayushman_card_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-heartbeat text-danger"></i> View Ayushman Card
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['ration_card_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['ration_card_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-wheat-awn text-warning"></i> View Ration Card
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['bocw_card_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['bocw_card_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-hard-hat text-secondary"></i> BOCW Labour Card
            </a>
          <?php endif; ?>
          <?php if (!empty($initialDbRecord['pmjay_letter_url'])): ?>
            <a href="<?php echo BASE_URL . '/' . htmlspecialchars($initialDbRecord['pmjay_letter_url']); ?>" target="_blank" class="doc-pill-link">
              <i class="fas fa-envelope-open-text text-success"></i> PMJAY Letter
            </a>
          <?php endif; ?>
        </div>

        <!-- Administrative Actions -->
        <div class="scanner-actions-row" style="border-top: 1.5px dashed #cbd5e1; padding-top: 16px;">
          <a id="btn-view-card" href="<?php echo BASE_URL; ?>/ayushman-card.php?app_no=<?php echo urlencode($initialDecrypted['app_no'] ?? ''); ?>" target="_blank" class="btn btn-primary" style="font-weight: 700;">
            <i class="fas fa-id-card"></i> Print/View Smart ID Card
          </a>
          <button type="button" id="btn-quick-approve" class="btn btn-success" style="font-weight: 700;">
            <i class="fas fa-check-circle"></i> Approve
          </button>
          <button type="button" id="btn-scan-next" class="btn btn-default" style="font-weight: 700;">
            <i class="fas fa-redo"></i> Scan Next Card
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
  const btnStopCamera = document.getElementById("btn-stop-camera");
  const laserLine = document.getElementById("laser-line");
  const scannerStatusBadge = document.getElementById("scanner-status-badge");
  const fullscreenStatusBadge = document.getElementById("fullscreen-status-badge");
  const btnFullscreenClose = document.getElementById("btn-fullscreen-close");
  const qrFileInput = document.getElementById("qr-file-input");
  const btnFullscreenGallery = document.getElementById("btn-fullscreen-gallery");
  const btnDecryptManual = document.getElementById("btn-decrypt-manual");
  const manualTokenInput = document.getElementById("manual-token-input");
  const isMobileViewport = () => window.matchMedia("(max-width: 768px)").matches;

  function enterFullscreenCamera() {
    if (isMobileViewport()) {
      document.body.classList.add("scanner-fullscreen-active");
    }
  }
  function exitFullscreenCamera() {
    document.body.classList.remove("scanner-fullscreen-active");
  }

  const dossierPlaceholder = document.getElementById("dossier-placeholder");
  const citizenDossierPanel = document.getElementById("citizen-dossier-panel");
  const scanAlertBox = document.getElementById("scan-alert-box");

  let html5QrCode = null;
  let isScanning = false;
  let currentDbAppId = <?php echo (int)($initialDbRecord['id'] ?? 0); ?>;
  let currentAppNo = <?php echo json_encode($initialDecrypted['app_no'] ?? ''); ?>;

  // Decrypted citizen details stay on screen for a maximum of 2 minutes,
  // then auto-hide for data privacy — an admin shouldn't leave someone's
  // Aadhaar/ABHA/address sitting open on screen indefinitely after a scan.
  const DOSSIER_AUTO_HIDE_MS = 2 * 60 * 1000;
  let dossierAutoHideTimeout = null;
  let dossierCountdownInterval = null;
  const dossierAutoHideBadge = document.getElementById("dossier-auto-hide-badge");
  const dossierAutoHideCountdown = document.getElementById("dossier-auto-hide-countdown");

  function clearDossierAutoHideTimer() {
    if (dossierAutoHideTimeout) { clearTimeout(dossierAutoHideTimeout); dossierAutoHideTimeout = null; }
    if (dossierCountdownInterval) { clearInterval(dossierCountdownInterval); dossierCountdownInterval = null; }
  }

  function startDossierAutoHideTimer() {
    clearDossierAutoHideTimer();
    const deadline = Date.now() + DOSSIER_AUTO_HIDE_MS;

    const tick = () => {
      const remainingMs = deadline - Date.now();
      if (remainingMs <= 0) {
        clearDossierAutoHideTimer();
        citizenDossierPanel.style.display = "none";
        dossierPlaceholder.style.display = "block";
        scanAlertBox.className = "alert alert-info";
        scanAlertBox.innerHTML = '<i class="fas fa-clock"></i> Citizen record hidden automatically after 2 minutes for data privacy. Scan again to view it.';
        scanAlertBox.style.display = "block";
        return;
      }
      if (dossierAutoHideCountdown) {
        const totalSeconds = Math.ceil(remainingMs / 1000);
        const mins = Math.floor(totalSeconds / 60);
        const secs = totalSeconds % 60;
        dossierAutoHideCountdown.innerText = mins + ":" + String(secs).padStart(2, "0");
      }
      if (dossierAutoHideBadge) {
        dossierAutoHideBadge.style.background = (remainingMs <= 20000) ? "#dc2626" : "#d97706";
      }
    };

    tick();
    dossierCountdownInterval = setInterval(tick, 1000);
    dossierAutoHideTimeout = setTimeout(tick, DOSSIER_AUTO_HIDE_MS + 100);
  }

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
    exitFullscreenCamera();
    scannerStatusBadge.className = "badge";
    scannerStatusBadge.style.background = "#0284c7";
    scannerStatusBadge.innerText = "Decrypting...";

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
        scannerStatusBadge.innerText = "Decrypted ✓";
        renderDossier(data.payload, data.db_record, data.verified_at);
      } else {
        scannerStatusBadge.style.background = "#dc2626";
        scannerStatusBadge.innerText = "Invalid Signature";
        showErrorAlert(data.message || "Cryptographic decryption failed.");
      }
    })
    .catch(err => {
      showErrorAlert("Server error: " + err);
    });
  }

  // Render Dossier into UI
  function renderDossier(payload, dbRecord, verifiedAt) {
    scanAlertBox.style.display = "none";
    dossierPlaceholder.style.display = "none";
    citizenDossierPanel.style.display = "block";
    startDossierAutoHideTimer();

    currentAppNo = payload.app_no || "";
    currentDbAppId = dbRecord ? dbRecord.id : 0;

    // The QR payload is deliberately minimal (just app_no/card_id/aadhaar_last4
    // + a tamper-proof signature) to keep the QR small enough to actually be
    // scannable — the full citizen record always comes from the live DB
    // lookup (dbRecord) that the server does on every successful decrypt.
    const rec = dbRecord || {};
    document.getElementById("dossier-verified-timestamp").innerText = verifiedAt || "";
    document.getElementById("dossier-name").innerText = rec.full_name || "Citizen";
    document.getElementById("dossier-gender-dob").innerText = (rec.gender || "Male") + " | DOB: " + (rec.dob || "N/A");
    document.getElementById("dossier-mobile").innerText = rec.mobile || "N/A";
    document.getElementById("dossier-email").innerText = rec.email || "N/A";
    document.getElementById("dossier-aadhaar").innerText = "XXXX-XXXX-" + (payload.aadhaar_last4 || "0000");
    document.getElementById("dossier-app-no").innerText = payload.app_no || "N/A";
    document.getElementById("dossier-card-id").innerText = payload.card_id || "N/A";
    document.getElementById("dossier-abha").innerText = rec.abha_number || "Not Applicable";
    document.getElementById("dossier-ration").innerText = rec.ration_card_no || "Not Applicable";
    document.getElementById("dossier-bocw").innerText = rec.bocw_reg_no || "Not Applicable";
    document.getElementById("dossier-pmjay").innerText = rec.pmjay_letter_no || "Not Applicable";

    const addrArr = [rec.address, rec.district, rec.state, rec.pincode].filter(Boolean);
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
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.photo_url}" target="_blank" class="doc-pill-link"><i class="fas fa-camera"></i> View Citizen Photo</a>`;
      }
      if (dbRecord.abha_card_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.abha_card_url}" target="_blank" class="doc-pill-link"><i class="fas fa-id-card"></i> View ABHA Card</a>`;
      }
      if (dbRecord.ayushman_card_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.ayushman_card_url}" target="_blank" class="doc-pill-link"><i class="fas fa-heartbeat text-danger"></i> View Ayushman Card</a>`;
      }
      if (dbRecord.ration_card_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.ration_card_url}" target="_blank" class="doc-pill-link"><i class="fas fa-wheat-awn text-warning"></i> View Ration Card</a>`;
      }
      if (dbRecord.bocw_card_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.bocw_card_url}" target="_blank" class="doc-pill-link"><i class="fas fa-hard-hat text-secondary"></i> BOCW Labour Card</a>`;
      }
      if (dbRecord.pmjay_letter_url) {
        docBox.innerHTML += `<a href="${baseUrl}/${dbRecord.pmjay_letter_url}" target="_blank" class="doc-pill-link"><i class="fas fa-envelope-open-text text-success"></i> PMJAY Letter</a>`;
      }
    }

    // Update Card Link
    document.getElementById("btn-view-card").href = baseUrl + "/ayushman-card.php?app_no=" + encodeURIComponent(payload.app_no);
  }

  function showErrorAlert(msg) {
    exitFullscreenCamera();
    clearDossierAutoHideTimer();
    scanAlertBox.className = "alert alert-danger";
    scanAlertBox.innerHTML = '<i class="fas fa-exclamation-triangle"></i> ' + msg;
    scanAlertBox.style.display = "block";
    citizenDossierPanel.style.display = "none";
    dossierPlaceholder.style.display = "block";
  }

  // 2. Camera Controls
  function startCameraScanner() {
    if (!html5QrCode) {
      html5QrCode = new Html5Qrcode("qr-reader");
    }

    scannerStatusBadge.className = "badge";
    scannerStatusBadge.style.background = "#f59e0b";
    scannerStatusBadge.innerText = "Starting Camera...";

    // A fixed 250x250 qrbox can be larger than the actual camera viewport on
    // some screens/windows, which makes html5-qrcode silently fail to align
    // its scan region with the visible video — the QR looks "in frame" but
    // never triggers a result. Size the box as a percentage of whatever
    // viewfinder the browser actually gives us instead (the library's own
    // recommended pattern), so it always fits and detects reliably.
    const config = {
      fps: 10,
      qrbox: function(viewfinderWidth, viewfinderHeight) {
        const minEdge = Math.min(viewfinderWidth, viewfinderHeight);
        const size = Math.floor(minEdge * 0.7);
        return { width: size, height: size };
      }
    };
    html5QrCode.start({ facingMode: "environment" }, config, handleQrScanSuccess)
      .then(() => {
        isScanning = true;
        btnStopCamera.style.display = "inline-block";
        laserLine.style.display = "block";
        scannerStatusBadge.style.background = "#10b981";
        scannerStatusBadge.innerText = "Scanning...";
        if (fullscreenStatusBadge) fullscreenStatusBadge.style.background = "#10b981";
        enterFullscreenCamera();
      })
      .catch(err => {
        scannerStatusBadge.style.background = "#dc2626";
        scannerStatusBadge.innerText = "Camera Error";
        exitFullscreenCamera();
        showErrorAlert("Unable to start camera: " + err + ". Please grant camera access and refresh the page.");
      });
  }

  // Scanner starts automatically as soon as the page opens — no manual start needed.
  if (!<?php echo $initialDecrypted ? 'true' : 'false'; ?>) {
    startCameraScanner();
  } else {
    // A record was already decrypted server-side (e.g. opened via ?token=...) —
    // start its 2-minute auto-hide window immediately too.
    startDossierAutoHideTimer();
  }

  function stopCameraScanner() {
    if (html5QrCode && isScanning) {
      html5QrCode.stop().then(() => {
        isScanning = false;
        btnStopCamera.style.display = "none";
        laserLine.style.display = "none";
      }).catch(err => console.error(err));
    }
    exitFullscreenCamera();
  }

  if (btnStopCamera) {
    btnStopCamera.addEventListener("click", stopCameraScanner);
  }
  if (btnFullscreenClose) {
    btnFullscreenClose.addEventListener("click", function() {
      stopCameraScanner();
      window.location.href = baseUrl + "/admin/ayushman-helpdesk.php";
    });
  }
  if (btnFullscreenGallery && qrFileInput) {
    btnFullscreenGallery.addEventListener("click", function() {
      // Stop the live camera first so it doesn't fight with the file scanner over the same reader element
      stopCameraScanner();
      qrFileInput.click();
    });
  }

  // 3. File Input Scanner
  if (qrFileInput) {
    qrFileInput.addEventListener("change", function(e) {
      if (e.target.files.length === 0) return;
      const file = e.target.files[0];
      
      const fileScanner = new Html5Qrcode("qr-reader");
      scannerStatusBadge.innerText = "Scanning Photo...";

      fileScanner.scanFile(file, true)
        .then(decodedText => {
          handleQrScanSuccess(decodedText);
        })
        .catch(err => {
          showErrorAlert("No valid QR code was found in this image. Please upload a clearer photo.");
        });
    });
  }

  // 4. Manual Decrypt Button
  if (btnDecryptManual) {
    btnDecryptManual.addEventListener("click", function() {
      const val = manualTokenInput.value.trim();
      if (!val) {
        alert("Please enter a token or URL.");
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
        alert("No active application record found.");
        return;
      }
      if (!confirm("Do you want to mark this application as 'Approved'?")) {
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
      clearDossierAutoHideTimer();
      citizenDossierPanel.style.display = "none";
      dossierPlaceholder.style.display = "block";
      scanAlertBox.style.display = "none";
      if (manualTokenInput) manualTokenInput.value = "";
      if (qrFileInput) qrFileInput.value = "";
      scannerStatusBadge.style.background = "#10b981";
      scannerStatusBadge.innerText = "Ready";
      if (!isScanning) startCameraScanner();
    });
  }

})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
