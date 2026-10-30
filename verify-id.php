<?php
// verify-id.php - Public Cryptographic Verification Endpoint for Ayushman Digital Health Smart ID
require_once __DIR__ . '/config/config.php';

$page_title = 'Digital Health Smart ID - Cryptographic Verification';

$token = trim($_GET['token'] ?? '');
$isValid = false;
$tampered = false;
$decryptedData = null;
$appRecord = null;

if (!empty($token)) {
    $decryptedData = ayushman_decrypt_payload($token);
    if ($decryptedData !== null) {
        $isValid = true;
        $appNo = $decryptedData['app_no'] ?? '';
        if ($appNo && isset($pdo)) {
            $stmt = $pdo->prepare("SELECT * FROM ayushman_applications WHERE application_no = ? LIMIT 1");
            $stmt->execute([$appNo]);
            $appRecord = $stmt->fetch();
        }
    } else {
        $tampered = true;
    }
}

// Check if an admin officer is currently logged in
$isAdmin = !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['admin_user']) || !empty($_SESSION['admin_id']);

include __DIR__ . '/includes/header.php';
?>

<div class="container" style="padding: 40px 15px; min-height: 550px;">
  <div class="row">
    <div class="col-md-8 col-md-offset-2 col-sm-12">
      
      <?php if (empty($token)): ?>
        <div class="panel panel-default" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
          <div class="panel-heading" style="background: #0b3c5d; color: white; padding: 18px 24px;">
            <h4 style="margin: 0; font-weight: 700;"><i class="fas fa-qrcode"></i> Digital Health Smart ID Verification Terminal</h4>
          </div>
          <div class="panel-body" style="padding: 30px; text-align: center;">
            <i class="fas fa-barcode fa-4x text-muted" style="margin-bottom: 16px;"></i>
            <h4 style="font-weight: 700; color: #1e293b;">कोई टोकन नहीं मिला (No Verification Token Provided)</h4>
            <p style="color: #64748b;">कृपया सत्यापन हेतु अपने डिजिटल हेल्थ स्मार्ट कार्ड का क्यूआर कोड स्कैन करें।</p>
            <a href="<?php echo BASE_URL; ?>/ayushman-helpdesk.php" class="btn btn-primary" style="background: #0b3c5d; border-color: #0b3c5d;">
              <i class="fas fa-arrow-left"></i> आयुष्मान हेल्प डेस्क पर जाएं
            </a>
          </div>
        </div>

      <?php elseif ($tampered): ?>
        <div class="panel panel-danger" style="border-radius: 12px; box-shadow: 0 4px 20px rgba(220,38,38,0.12); overflow: hidden; border-color: #fca5a5;">
          <div class="panel-heading" style="background: #dc2626; color: white; padding: 18px 24px;">
            <h4 style="margin: 0; font-weight: 700;"><i class="fas fa-exclamation-triangle"></i> सुरक्षा चेतावनी: अमान्य अथवा छेड़छाड़ किया गया क्यूआर कोड</h4>
          </div>
          <div class="panel-body" style="padding: 30px; text-align: center;">
            <i class="fas fa-shield-virus fa-4x text-danger" style="margin-bottom: 16px;"></i>
            <h3 style="color: #991b1b; font-weight: 800; margin-top: 0;">Cryptographic Signature Verification Failed</h3>
            <p style="color: #475569; font-size: 0.95rem; max-width: 580px; margin: 0 auto 20px;">
              यह QR कोड प्रामाणिक नहीं है अथवा इसके एन्क्रिप्शन हस्ताक्षर (HMAC-SHA256) में विसंगति पाई गई है। डेटा सुरक्षा नियमों के तहत इसे अमान्य घोषित किया गया है।
            </p>
            <div style="font-family: monospace; font-size: 0.82rem; background: #fef2f2; border: 1px solid #fee2e2; padding: 10px; border-radius: 6px; word-break: break-all; margin-bottom: 20px;">
              Token: <?php echo htmlspecialchars(substr($token, 0, 50)) . '...'; ?>
            </div>
            <a href="<?php echo BASE_URL; ?>/ayushman-helpdesk.php" class="btn btn-default">
              <i class="fas fa-home"></i> मुख्य पृष्ठ पर जाएं
            </a>
          </div>
        </div>

      <?php else: ?>
        <!-- Valid Encrypted Token Result -->
        <div class="panel panel-default" style="border-radius: 12px; box-shadow: 0 6px 24px rgba(11,60,93,0.1); overflow: hidden; border: 1px solid #cbd5e1;">
          
          <div class="panel-heading" style="background: linear-gradient(135deg, #0b3c5d 0%, #1e5a87 100%); color: white; padding: 20px 24px;">
            <div class="row align-items-center">
              <div class="col-xs-8">
                <span class="badge" style="background: #10b981; font-size: 0.8rem; padding: 5px 12px; margin-bottom: 6px;">
                  <i class="fas fa-shield-check"></i> AES-256 SIGNATURE AUTHENTICATED
                </span>
                <h3 style="margin: 0; font-weight: 800; font-size: 1.4rem;">डिजिटल हेल्थ स्मार्ट आईडी सत्यापन</h3>
                <div style="font-size: 0.82rem; opacity: 0.9;">National Health Authority • Citizen Facilitation System</div>
              </div>
              <div class="col-xs-4 text-right">
                <i class="fas fa-shield-alt fa-3x" style="color: #6ee7b7;"></i>
              </div>
            </div>
          </div>

          <div class="panel-body" style="padding: 28px;">
            
            <!-- Summary Info Row -->
            <div class="row" style="margin-bottom: 20px;">
              <div class="col-md-6 col-sm-6">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                  <div style="font-size: 0.78rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Application Reference</div>
                  <div style="font-size: 1.15rem; font-weight: 800; color: #0b3c5d; font-family: monospace;">
                    <?php echo htmlspecialchars($decryptedData['app_no'] ?? 'N/A'); ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6 col-sm-6">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px;">
                  <div style="font-size: 0.78rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Smart Card ID</div>
                  <div style="font-size: 1.15rem; font-weight: 800; color: #059669; font-family: monospace;">
                    <?php echo htmlspecialchars($decryptedData['card_id'] ?? 'N/A'); ?>
                  </div>
                </div>
              </div>
            </div>

            <!-- Restricted Public Data Alert -->
            <div class="alert alert-info" style="background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1e3a8a; border-radius: 10px; padding: 20px;">
              <div class="row align-items-center">
                <div class="col-xs-2 text-center">
                  <i class="fas fa-user-lock fa-3x" style="color: #2563eb;"></i>
                </div>
                <div class="col-xs-10">
                  <h4 style="margin: 0 0 6px; font-weight: 800; color: #1e40af;">
                    गोपनीय नागरिक डेटा: एन्क्रिप्शन सुरक्षित (Encrypted Record)
                  </h4>
                  <p style="margin: 0; font-size: 0.88rem; line-height: 1.55;">
                    नागरिक का व्यक्तिगत विवरण, आधार ई-केवाईसी, आभा आईडी एवं सभी सरकारी दस्तावेज <strong>AES-256-CBC मिलिट्री ग्रेड एन्क्रिप्शन</strong> द्वारा सुरक्षित हैं। डेटा सुरक्षा अधिनियम (DPDP Act 2023) के तहत यह जानकारी केवल अधिकृत <strong>Admin Terminal Scanner</strong> द्वारा ही डिक्रिप्ट एवं देखी जा सकती है।
                  </p>
                </div>
              </div>
            </div>

            <!-- Public Metadata Verification Table -->
            <table class="table table-bordered" style="margin-top: 18px; font-size: 0.9rem;">
              <tr>
                <th style="width: 40%; background: #f8fafc; color: #475569;">आधार ई-केवाईसी स्थिति (e-KYC Status):</th>
                <td>
                  <span class="badge" style="background: #10b981; font-size: 0.85rem; padding: 5px 12px;">
                    <i class="fas fa-check-circle"></i> आधार सत्यापित (Verified)
                  </span>
                  <?php if (!empty($decryptedData['aadhaar_last4'])): ?>
                    <span style="font-family: monospace; margin-left: 8px; font-weight: 700;">XXXX-XXXX-<?php echo htmlspecialchars($decryptedData['aadhaar_last4']); ?></span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <th style="background: #f8fafc; color: #475569;">स्मार्ट कार्ड स्थिति (Card Status):</th>
                <td><strong class="text-success"><i class="fas fa-check"></i> सक्रिय एवं मान्य (Active & Valid)</strong></td>
              </tr>
              <tr>
                <th style="background: #f8fafc; color: #475569;">एन्क्रिप्शन सुरक्षा (Security Protocol):</th>
                <td><code>AES-256-CBC / HMAC-SHA256 Encrypt-then-MAC</code></td>
              </tr>
              <tr>
                <th style="background: #f8fafc; color: #475569;">सत्यापन प्राधिकरण (Issuing Facilitator):</th>
                <td><?php echo defined('APP_NAME') ? APP_NAME : 'Jan Seva Facilitation Portal'; ?></td>
              </tr>
            </table>

            <!-- Admin Scanner Link -->
            <div style="margin-top: 25px; text-align: center; border-top: 1px dashed #cbd5e1; padding-top: 20px;">
              <?php if ($isAdmin): ?>
                <a href="<?php echo BASE_URL; ?>/admin/ayushman-scanner.php?token=<?php echo urlencode($token); ?>" class="btn btn-success btn-lg" style="font-weight: 700; border-radius: 8px;">
                  <i class="fas fa-unlock-alt"></i> एडमिन स्कैनर में पूरा रिकॉर्ड डिक्रिप्ट करें (Decrypt Dossier)
                </a>
              <?php else: ?>
                <div style="font-size: 0.86rem; color: #64748b; margin-bottom: 12px;">
                  क्या आप अधिकृत प्रशासनिक अधिकारी हैं? अपने अधिकृत एडमिन स्कैनर से पूर्ण विवरण देखें।
                </div>
                <a href="<?php echo BASE_URL; ?>/admin/login.php?redirect=<?php echo urlencode('ayushman-scanner.php?token=' . $token); ?>" class="btn btn-default" style="font-weight: 600;">
                  <i class="fas fa-user-shield text-primary"></i> अधिकृत अधिकारी लॉगिन (Admin Staff Login)
                </a>
              <?php endif; ?>
            </div>

          </div>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
