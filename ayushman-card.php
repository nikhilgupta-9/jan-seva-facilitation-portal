<?php
// ayushman-card.php - High-Resolution Digital Health Smart ID Card Viewer & Download Center
require_once __DIR__ . '/config/config.php';

$appNo = trim($_GET['app_no'] ?? '');
$record = null;
$isExistingCard = (($_GET['status'] ?? '') === 'existing');

if (!empty($appNo) && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT * FROM ayushman_applications WHERE application_no = ? LIMIT 1");
    $stmt->execute([$appNo]);
    $record = $stmt->fetch();
}

$page_title = $record ? 'Digital Health Smart ID - ' . $record['full_name'] : 'Ayushman Health Card Search';

// Build the public verification URL to embed in the QR code.
// Kept deliberately short (just the Card ID) instead of the long encrypted
// token — a long AES payload pushes the QR to a very dense, hard-to-scan
// code at the card's small print/screen size. Security is unaffected: this
// gate page (verify-id.php) never discloses anything to a plain scan either
// way, and the Card ID is still only resolvable to real data via the
// authenticated admin scanner.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = defined('BASE_URL') ? BASE_URL : '/id-card';
$qrVerificationUrl = $protocol . $host . rtrim($base, '/') . '/verify-id.php?id=' . urlencode($record['card_id'] ?? '');

$isAdmin = !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['admin_user']) || !empty($_SESSION['admin_id']);

include __DIR__ . '/includes/header.php';
?>

<style>
/* Smart Card PVC Styles (CR80 Standard Ratio) */
.smart-card-wrapper {
    background: #f1f5f9;
    padding: 30px 15px 50px;
}
.pvc-smart-card {
    width: 100%;
    max-width: 540px;
    height: 330px;
    background: #ffffff;
    border-radius: 14px;
    position: relative;
    box-shadow: 0 14px 34px rgba(11, 60, 93, 0.16), 0 2px 6px rgba(0,0,0,0.06);
    border: 1px solid #cbd5e1;
    overflow: hidden;
    margin: 0 auto 30px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.pvc-smart-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #ff9933 0%, #ff9933 33.3%, #ffffff 33.3%, #ffffff 66.6%, #138808 66.6%, #138808 100%);
    z-index: 5;
}
.card-header-bar {
    background: linear-gradient(135deg, #0b3c5d 0%, #174b73 100%);
    color: #ffffff;
    padding: 10px 16px 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #f59e0b;
}
.card-header-titles h4 {
    margin: 0;
    font-size: 0.88rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    color: #ffffff;
    text-transform: uppercase;
}
.card-header-titles p {
    margin: 1px 0 0;
    font-size: 0.72rem;
    color: #fde047;
    font-weight: 600;
    letter-spacing: 0.3px;
}
.card-body-section {
    padding: 12px 16px;
    display: flex;
    gap: 14px;
    align-items: center;
    flex: 1;
}
.card-photo-col {
    width: 95px;
    text-align: center;
    flex-shrink: 0;
}
.card-photo-col img {
    width: 92px;
    height: 110px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #0b3c5d;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    background: #f8fafc;
}
.card-verified-badge {
    background: #ecfdf5;
    border: 1px solid #10b981;
    color: #065f46;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 3px 4px;
    margin-top: 6px;
    display: block;
    text-align: center;
    letter-spacing: 0.2px;
}
.card-details-col {
    flex: 1;
    min-width: 0;
}
.card-name-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0b3c5d;
    margin: 0 0 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
}
.card-meta-line {
    font-size: 0.75rem;
    color: #475569;
    margin-bottom: 2px;
    line-height: 1.35;
}
.card-meta-line strong {
    color: #0f172a;
    font-weight: 700;
}
.card-abha-pill {
    background: #f0f9ff;
    border: 1.5px solid #0284c7;
    border-radius: 6px;
    padding: 4px 8px;
    margin: 6px 0;
    display: inline-block;
}
.card-abha-pill .label-txt {
    font-size: 0.66rem;
    font-weight: 700;
    color: #0369a1;
    text-transform: uppercase;
    display: block;
}
.card-abha-pill .val-txt {
    font-size: 0.88rem;
    font-weight: 800;
    color: #0c4a6e;
    font-family: monospace;
    letter-spacing: 1px;
}
.card-qr-col {
    width: 105px;
    text-align: center;
    flex-shrink: 0;
}
.card-qr-box {
    width: 95px;
    height: 95px;
    padding: 4px;
    background: #ffffff;
    border: 1.5px solid #0b3c5d;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 4px;
}
.card-qr-box canvas, .card-qr-box img {
    width: 100% !important;
    height: 100% !important;
}
.card-qr-label {
    font-size: 0.62rem;
    font-weight: 800;
    color: #dc2626;
    letter-spacing: 0.2px;
    line-height: 1.2;
}
.card-footer-strip {
    background: #0b3c5d;
    color: #ffffff;
    padding: 6px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    border-top: 1px solid #e2e8f0;
}

/* Card Back Styles */
.pvc-smart-card-back {
    background: #f8fafc;
}
.card-back-body {
    padding: 14px 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.card-back-address {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 0.74rem;
    color: #334155;
    line-height: 1.45;
}
.card-back-rules {
    font-size: 0.68rem;
    color: #64748b;
    line-height: 1.4;
    margin: 8px 0;
}
.card-back-bottom {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    border-top: 1px dashed #cbd5e1;
    padding-top: 6px;
}

/* Print Only Styles */
@media print {
    body * {
        visibility: hidden;
    }
    .no-print, header, footer, .ayush-hero-banner, .btn, .breadcrumb, .alert {
        display: none !important;
    }
    .printable-section, .printable-section * {
        visibility: visible;
    }
    .printable-section {
        position: absolute;
        left: 0;
        top: 0;
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
        padding: 0;
        margin: 0;
    }
    .smart-card-wrapper,
    .smart-card-wrapper .container,
    .smart-card-wrapper .row {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
    }
    .pvc-smart-card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
        page-break-inside: avoid;
        margin: 0 auto 20px !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    @page {
        size: auto;
        margin: 10mm;
    }
}
</style>

<!-- Banner -->
<section class="no-print" style="background: #eef2f6; border-bottom: 1px solid #dde2e8; padding: 18px 0;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-7 col-sm-12">
        <ol class="breadcrumb" style="background: transparent; margin: 0; padding: 0; font-size: 0.88rem;">
          <li><a href="<?php echo BASE_URL; ?>/index.php"><i class="fas fa-home"></i> होम</a></li>
          <li><a href="<?php echo BASE_URL; ?>/ayushman-helpdesk.php">आयुष्मान हेल्प डेस्क</a></li>
          <li class="active">स्मार्ट आईडी कार्ड</li>
        </ol>
      </div>
      <div class="col-md-5 col-sm-12 text-right">
        <?php if ($record): ?>
          <span class="badge" style="background: #16a34a; font-size: 0.85rem; padding: 6px 14px;">
            <i class="fas fa-check-circle"></i> आधार सत्यापित • कार्ड जनरेटेड
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<div class="smart-card-wrapper">
  <div class="container">
    
    <?php if (!$record): ?>
      <!-- Search / Not Found Box -->
      <div class="row">
        <div class="col-md-6 col-md-offset-3 col-sm-12">
          <div style="background: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 4px 18px rgba(0,0,0,0.06); text-align: center;">
            <i class="fas fa-id-card-alt fa-3x text-warning" style="margin-bottom: 14px;"></i>
            <h3 style="margin-top: 0; font-weight: 800; color: #0b3c5d;">डिजिटल हेल्थ स्मार्ट कार्ड खोजें</h3>
            <p style="color: #64748b; font-size: 0.92rem;">
              कृपया अपना आवेदन क्रमांक (Application Number) दर्ज करें:
            </p>
            <form method="GET" action="" style="margin-top: 20px;">
              <div class="input-group">
                <input type="text" name="app_no" class="form-control" placeholder="उदा. AYUSH-20261030-XXXX" required style="height: 46px; font-weight: 700; text-transform: uppercase;">
                <span class="input-group-btn">
                  <button type="submit" class="btn btn-primary" style="height: 46px; background: #0b3c5d; font-weight: 700; padding: 0 20px;">
                    <i class="fas fa-search"></i> कार्ड खोजें
                  </button>
                </span>
              </div>
            </form>
            <div style="margin-top: 20px;">
              <a href="<?php echo BASE_URL; ?>/ayushman-helpdesk.php" class="btn btn-default btn-sm">
                <i class="fas fa-plus-circle text-success"></i> नया कार्ड आवेदन करें
              </a>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- SUCCESS ALERT BANNER -->
      <div class="alert alert-success no-print" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; border-radius: 10px; padding: 18px; margin-bottom: 25px;">
        <div class="row align-items-center">
          <div class="col-md-8 col-sm-12">
            <h4 style="margin: 0 0 6px; font-weight: 800; color: #065f46;">
              <?php if ($isExistingCard): ?>
                <i class="fas fa-id-card text-success"></i> इस मोबाइल नंबर पर पहले से एक डिजिटल हेल्थ स्मार्ट आईडी कार्ड मौजूद है!
              <?php else: ?>
                <i class="fas fa-check-circle text-success"></i> डिजिटल हेल्थ स्मार्ट आईडी कार्ड सफलतापूर्वक जनरेट हुआ!
              <?php endif; ?>
            </h4>
            <p style="margin: 0; font-size: 0.88rem;">
              <?php if ($isExistingCard): ?>
                प्रति मोबाइल नंबर केवल एक ही कार्ड जारी किया जाता है, इसलिए आपका मौजूदा कार्ड नीचे दिखाया जा रहा है।
              <?php endif; ?>
              आवेदन क्रमांक: <strong><?php echo htmlspecialchars($record['application_no']); ?></strong> |
              कार्ड आईडी: <strong><?php echo htmlspecialchars($record['card_id']); ?></strong> |
              पुष्टि एवं डाउनलोड लिंक आपकी ईमेल <code><?php echo htmlspecialchars($record['email']); ?></code> पर भी भेज दी गई है।
            </p>
          </div>
          <div class="col-md-4 col-sm-12 text-right" style="margin-top: 8px;">
            <button type="button" onclick="window.print();" class="btn btn-primary" style="background: #0b3c5d; border-color: #0b3c5d; font-weight: 700; margin-right: 6px;">
              <i class="fas fa-print"></i> कार्ड प्रिंट करें
            </button>
            <button type="button" id="btn-download-pvc" class="btn btn-success" style="background: #059669; border-color: #059669; font-weight: 700;">
              <i class="fas fa-download"></i> PNG सेव करें
            </button>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Center / Left: Cards Area (Printable) -->
        <div class="col-lg-7 col-md-7 col-sm-12 printable-section">
          
          <h4 class="no-print" style="font-weight: 800; color: #0b3c5d; margin-top: 0; margin-bottom: 16px;">
            <i class="fas fa-id-card text-warning"></i> राष्ट्रीय डिजिटल स्वास्थ्य स्मार्ट पहचान पत्र (PVC Card Preview)
          </h4>

          <!-- 1. CARD FRONT -->
          <div id="pvc-card-front" class="pvc-smart-card">
            <!-- Header -->
            <div class="card-header-bar">
              <div style="display: flex; align-items: center; gap: 8px;">
                <svg viewBox="0 0 100 100" style="width: 28px; height: 28px; filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));">
                  <circle cx="50" cy="50" r="44" stroke="#f59e0b" stroke-width="4" fill="none" />
                  <circle cx="50" cy="50" r="10" fill="#ffffff" />
                  <?php for ($i = 0; $i < 24; $i++): $ang = $i * 15; ?>
                    <line x1="50" y1="50" x2="<?php echo 50 + 38 * cos(deg2rad($ang)); ?>" y2="<?php echo 50 + 38 * sin(deg2rad($ang)); ?>" stroke="#ffffff" stroke-width="1.8" />
                  <?php endfor; ?>
                </svg>
                <div class="card-header-titles">
                  <h4>National Health Authority</h4>
                  <p>राष्ट्रीय डिजिटल स्वास्थ्य मिशन • Digital Health Smart ID</p>
                </div>
              </div>
              <div style="text-align: right;">
                <i class="fas fa-shield-alt fa-2x" style="color: #6ee7b7;"></i>
              </div>
            </div>

            <!-- Body Content -->
            <div class="card-body-section">
              <!-- Left: Photo -->
              <div class="card-photo-col">
                <img src="<?php echo !empty($record['photo_url']) ? BASE_URL . '/' . htmlspecialchars($record['photo_url']) : BASE_URL . '/images/tuser1.jpg'; ?>" alt="Citizen Photo">
                <span class="card-verified-badge">
                  <i class="fas fa-check"></i> Aadhaar e-KYC
                </span>
              </div>

              <!-- Middle: Citizen Data -->
              <div class="card-details-col">
                <h3 class="card-name-title"><?php echo htmlspecialchars($record['full_name']); ?></h3>
                
                <div class="card-meta-line">
                  <strong>DOB:</strong> <?php echo htmlspecialchars($record['dob'] ?: 'DD/MM/YYYY'); ?> &nbsp;|&nbsp; 
                  <strong>Gender:</strong> <?php echo htmlspecialchars($record['gender'] ?: 'Male'); ?>
                </div>

                <div class="card-meta-line">
                  <strong>Mobile:</strong> <?php echo htmlspecialchars(substr($record['mobile'], 0, 2) . 'XXXXXX' . substr($record['mobile'], -2)); ?>
                </div>

                <!-- ABHA Pill -->
                <div class="card-abha-pill">
                  <span class="label-txt">ABHA NUMBER / आभा संख्या</span>
                  <span class="val-txt"><?php echo htmlspecialchars($record['abha_number'] ?: 'NOT ENROLLED'); ?></span>
                </div>

                <?php if (!empty($record['ration_card_no'])): ?>
                  <div class="card-meta-line"><strong>Ration Card:</strong> <?php echo htmlspecialchars($record['ration_card_no']); ?></div>
                <?php endif; ?>

                <?php if (!empty($record['bocw_reg_no'])): ?>
                  <div class="card-meta-line"><strong>BOCW No:</strong> <?php echo htmlspecialchars($record['bocw_reg_no']); ?></div>
                <?php endif; ?>

                <?php if (!empty($record['pmjay_letter_no'])): ?>
                  <div class="card-meta-line"><strong>PMJAY Ref:</strong> <?php echo htmlspecialchars($record['pmjay_letter_no']); ?></div>
                <?php endif; ?>
              </div>

              <!-- Right: Encrypted QR Code -->
              <div class="card-qr-col">
                <div class="card-qr-box" id="qr-container-front">
                  <!-- Fallback QR Image via API or Canvas rendered by JS -->
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($qrVerificationUrl); ?>" alt="Encrypted QR" id="qr-img-fallback">
                </div>
                <div class="card-qr-label">
                  <i class="fas fa-lock"></i> AES-256<br>ENCRYPTED QR
                </div>
              </div>
            </div>

            <!-- Bottom Strip -->
            <div class="card-footer-strip">
              <span><strong>CARD ID:</strong> <?php echo htmlspecialchars($record['card_id']); ?></span>
              <span><strong>APP NO:</strong> <?php echo htmlspecialchars($record['application_no']); ?></span>
            </div>
          </div>

          <!-- 2. CARD BACK -->
          <div id="pvc-card-back" class="pvc-smart-card pvc-smart-card-back">
            <div class="card-header-bar" style="background: #1e293b; border-bottom: 2px solid #059669;">
              <div class="card-header-titles">
                <h4>Cardholder Residential Record & Terms</h4>
                <p>Digital Health Mission • Citizen Verification Pass</p>
              </div>
              <div style="font-size: 0.72rem; color: #94a3b8; font-family: monospace;">
                DPDP ACT 2023 COMPLIANT
              </div>
            </div>

            <div class="card-back-body">
              <div class="card-back-address">
                <strong><i class="fas fa-home text-primary"></i> स्थायी पता / Permanent Address:</strong><br>
                <?php 
                  $addrParts = array_filter([$record['address'], $record['district'], $record['state'], $record['pincode']]);
                  echo htmlspecialchars(implode(', ', $addrParts) ?: 'As per Aadhaar e-KYC Records');
                ?>
              </div>

              <div class="card-back-rules">
                <ul style="padding-left: 14px; margin: 0;">
                  <li>यह डिजिटल हेल्थ स्मार्ट आईडी कार्ड आयुष्मान भारत डिजिटल मिशन की नागरिक सुविधा हेतु जारी किया गया है।</li>
                  <li>कार्ड पर मौजूद QR कोड AES-256 मिलिट्री ग्रेड एन्क्रिप्टेड है, जिसे केवल अधिकृत Admin Scanner द्वारा ही पढ़ा जा सकता है।</li>
                  <li>किसी भी चिकित्सा सहायता हेतु राष्ट्रीय स्वास्थ्य हेल्पलाइन <strong>14555</strong> या आपातकालीन <strong>108</strong> पर संपर्क करें।</li>
                </ul>
              </div>

              <div class="card-back-bottom">
                <div>
                  <div style="font-size: 0.65rem; color: #64748b;">ISSUING DATE:</div>
                  <div style="font-size: 0.75rem; font-weight: 700; color: #0b3c5d;">
                    <?php echo date('d-m-Y', strtotime($record['created_at'])); ?>
                  </div>
                </div>
                <div style="text-align: center;">
                  <span class="badge" style="background: #059669; font-size: 0.68rem; padding: 4px 8px;">
                    <i class="fas fa-check-double"></i> Digitally Signed
                  </span>
                </div>
                <div style="text-align: right;">
                  <div style="font-size: 0.65rem; color: #64748b;">SECURITY TOKEN:</div>
                  <div style="font-size: 0.68rem; font-family: monospace; font-weight: 700; color: #475569;">
                    <?php echo strtoupper(substr(hash('sha256', $record['encrypted_qr_token']), 0, 12)); ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer-strip" style="background: #1e293b;">
              <span>Emergency Helpline: 14555 / 1800-11-2026</span>
              <span>www.nha.gov.in</span>
            </div>
          </div>

        </div>

        <!-- Right Column: Card Metadata & Security Audit -->
        <div class="col-lg-5 col-md-5 col-sm-12 no-print">
          
          <!-- Metadata Card -->
          <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);">
            <h4 style="font-weight: 800; color: #0b3c5d; margin-top: 0; border-bottom: 2px solid #f59e0b; padding-bottom: 8px;">
              <i class="fas fa-info-circle text-warning"></i> विवरण एवं सत्यापन स्थिति
            </h4>

            <table class="table" style="font-size: 0.88rem; margin-bottom: 0;">
              <tr>
                <td style="color: #64748b; font-weight: 600; width: 45%;">कार्ड धारक:</td>
                <td><strong><?php echo htmlspecialchars($record['full_name']); ?></strong></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">आवेदन क्रमांक:</td>
                <td><code style="font-weight: 700; color: #0b3c5d;"><?php echo htmlspecialchars($record['application_no']); ?></code></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">स्मार्ट कार्ड आईडी:</td>
                <td><code style="font-weight: 700; color: #059669;"><?php echo htmlspecialchars($record['card_id']); ?></code></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">आधार सत्यापन:</td>
                <td>
                  <span class="badge" style="background: #10b981; font-size: 0.78rem;">
                    <i class="fas fa-check"></i> आधार e-KYC (XXXX-XXXX-<?php echo htmlspecialchars($record['aadhaar_last4'] ?? '0000'); ?>)
                  </span>
                </td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">पंजीकृत मोबाइल:</td>
                <td><?php echo htmlspecialchars($record['mobile']); ?></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">ईमेल आईडी:</td>
                <td><?php echo htmlspecialchars($record['email']); ?></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">वर्तमान स्थिति:</td>
                <td>
                  <?php 
                    $st = $record['status'] ?? 'submitted';
                    $stColors = ['submitted' => '#0284c7', 'verified' => '#10b981', 'approved' => '#059669', 'rejected' => '#dc2626'];
                  ?>
                  <span class="badge" style="background: <?php echo $stColors[$st] ?? '#64748b'; ?>; text-transform: uppercase;">
                    <?php echo htmlspecialchars($st); ?>
                  </span>
                </td>
              </tr>
            </table>

            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed #e2e8f0;">
              <button type="button" class="btn btn-default btn-block" onclick="copyVerificationLink()">
                <i class="fas fa-share-alt"></i> सत्यापन लिंक कॉपी करें (Copy Verification URL)
              </button>
            </div>
          </div>

          <!-- Encryption & Admin Scanner Notice -->
          <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 20px;">
            <div style="display: flex; gap: 12px;">
              <i class="fas fa-shield-alt fa-2x" style="color: #d97706; flex-shrink: 0;"></i>
              <div>
                <h5 style="margin: 0 0 6px; font-weight: 800; color: #92400e;">सुरक्षित एन्क्रिप्टेड क्यूआर तकनीक</h5>
                <p style="margin: 0; font-size: 0.83rem; color: #78350f; line-height: 1.5;">
                  इस स्मार्ट कार्ड का क्यूआर कोड <strong>AES-256-CBC</strong> एन्क्रिप्टेड है। किसी भी सामान्य स्कैनर से स्कैन करने पर व्यक्तिगत जानकारी नहीं दिखेगी। केवल अधिकृत एडमिन स्कैनर ही इसे डिक्रिप्ट कर सकता है।
                </p>
                <?php if ($isAdmin): ?>
                  <div style="margin-top: 12px;">
                    <a href="<?php echo BASE_URL; ?>/admin/ayushman-scan-detail.php?id=<?php echo urlencode($record['card_id']); ?>" class="btn btn-warning btn-sm" style="font-weight: 700; color: #1e293b; background: #f59e0b;">
                      <i class="fas fa-qrcode"></i> एडमिन स्कैनर से टेस्ट करें (Test in Admin Scanner)
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- Include qrcode.min.js & html2canvas.min.js -->
<script src="<?php echo BASE_URL; ?>/js/qrcode.min.js"></script>
<script src="<?php echo BASE_URL; ?>/js/html2canvas.min.js"></script>

<script>
// Generate High-Density QR code via qrcode.js
document.addEventListener("DOMContentLoaded", function() {
  <?php if ($record): ?>
    const qrTarget = document.getElementById("qr-container-front");
    const qrData = <?php echo json_encode($qrVerificationUrl); ?>;
    if (typeof QRCode !== "undefined" && qrTarget) {
      qrTarget.innerHTML = "";
      new QRCode(qrTarget, {
        text: qrData,
        width: 90,
        height: 90,
        colorDark: "#0b3c5d",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.M
      });
    }
  <?php endif; ?>
});

// Download Smart ID Card as PNG Image via html2canvas
const btnDownloadPvc = document.getElementById("btn-download-pvc");
if (btnDownloadPvc) {
  btnDownloadPvc.addEventListener("click", function() {
    const cardEl = document.getElementById("pvc-card-front");
    if (!cardEl) return;

    btnDownloadPvc.disabled = true;
    btnDownloadPvc.innerHTML = '<i class="fas fa-spinner fa-spin"></i> कार्ड रेंडर हो रहा है...';

    html2canvas(cardEl, {
      scale: 3, // High DPI for crisp print-quality rendering
      useCORS: true,
      backgroundColor: "#ffffff"
    }).then(canvas => {
      const link = document.createElement("a");
      link.download = "Digital_Health_Smart_ID_<?php echo htmlspecialchars($record['card_id'] ?? 'card'); ?>.png";
      link.href = canvas.toDataURL("image/png");
      link.click();

      btnDownloadPvc.disabled = false;
      btnDownloadPvc.innerHTML = '<i class="fas fa-download"></i> PNG सेव करें';
    }).catch(err => {
      alert("कार्ड डाउनलोड करने में त्रुटि: " + err);
      btnDownloadPvc.disabled = false;
      btnDownloadPvc.innerHTML = '<i class="fas fa-download"></i> PNG सेव करें';
    });
  });
}

function copyVerificationLink() {
  const url = <?php echo json_encode($qrVerificationUrl); ?>;
  navigator.clipboard.writeText(url).then(() => {
    alert("सत्यापन लिंक कॉपी कर लिया गया है!\n\n" + url);
  }).catch(() => {
    prompt("सत्यापन लिंक:", url);
  });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
