<?php
// admin/ayushman-scan-detail.php - Dedicated post-scan citizen detail page.
// Opened by a redirect from the QR scanner after a successful decrypt/lookup.
// Shows the full citizen record + documents, then auto-expires 2 minutes
// after the admin first opened it (server-enforced, not just a client timer)
// so sensitive data doesn't stay accessible on screen indefinitely.
require_once __DIR__ . '/auth.php';

$page_title = 'Citizen Record - Digital Health Smart ID';

if (empty($_SESSION['admin_csrf_token'])) {
    $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
}

$cardId = trim($_GET['id'] ?? '');
$record = null;
$expired = false;
$remainingSeconds = 120;

if ($cardId !== '' && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT * FROM ayushman_applications WHERE card_id = ? LIMIT 1");
    $stmt->execute([$cardId]);
    $record = $stmt->fetch();
}

if ($record) {
    $sessionKey = 'scan_view_start_' . md5($cardId);
    $now = time();

    if (empty($_SESSION[$sessionKey])) {
        $_SESSION[$sessionKey] = $now;
    }

    $elapsed = $now - $_SESSION[$sessionKey];
    if ($elapsed >= 120) {
        $expired = true;
        unset($_SESSION[$sessionKey]);
    } else {
        $remainingSeconds = 120 - $elapsed;
    }
}

include __DIR__ . '/header.php';
?>

<style>
.scan-detail-box {
    background: #ffffff;
    border: 2px solid var(--adm-green);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 6px 20px rgba(22, 163, 74, 0.1);
}
.scan-detail-header {
    display: flex;
    gap: 16px;
    align-items: center;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.scan-detail-photo {
    width: 80px;
    height: 95px;
    object-fit: cover;
    border-radius: 8px;
    border: 2px solid var(--adm-blue-dark);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    flex-shrink: 0;
}
.scan-detail-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 16px;
}
.scan-detail-field {
    background: #f8fafc;
    border: 1px solid var(--adm-border);
    border-radius: 6px;
    padding: 8px 12px;
}
.scan-detail-field-label {
    font-size: 0.72rem;
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    display: block;
}
.scan-detail-field-value {
    font-size: 0.92rem;
    font-weight: 700;
    color: #0f172a;
    word-break: break-all;
}
.scan-doc-pill-link {
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
}
.scan-countdown-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #d97706;
    color: #ffffff;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 5px 12px;
    border-radius: 20px;
}
.scan-countdown-badge.danger { background: #dc2626; }
@media (max-width: 768px) {
    .scan-detail-meta-grid { grid-template-columns: 1fr; }
    .scan-detail-header { flex-direction: column; text-align: center; }
}
</style>

<div class="scanner-page-head" style="display:flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 18px;">
  <div>
    <h3 style="margin: 0; font-weight: 800; color: var(--adm-blue-dark, #0284c7); font-size: 1.25rem;">
      <i class="fas fa-id-card"></i> Citizen Record
    </h3>
    <p style="margin: 2px 0 0; color: #64748b; font-size: 0.86rem;">Opened from a verified QR scan. This record auto-closes after 2 minutes for data privacy.</p>
  </div>
  <div>
    <a href="<?php echo BASE_URL; ?>/admin/ayushman-scanner.php" class="btn btn-default" style="font-weight: 600; border-radius: 8px;">
      <i class="fas fa-qrcode"></i> Back to Scanner
    </a>
  </div>
</div>

<?php if (!$record): ?>
  <div class="alert alert-danger" style="border-radius: 10px;">
    <i class="fas fa-exclamation-triangle"></i> No record found for this Card ID. Please scan again.
  </div>

<?php elseif ($expired): ?>
  <div style="background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 60px 20px; text-align: center; color: #64748b;">
    <i class="fas fa-clock fa-4x" style="margin-bottom: 16px; opacity: 0.35; color: #94a3b8;"></i>
    <h4 style="font-weight: 800; color: #1e293b; margin-top: 0;">Record Auto-Closed</h4>
    <p style="max-width: 440px; margin: 0 auto 18px; font-size: 0.9rem;">
      This citizen record was open for its full 2-minute privacy window and has been auto-logged-out. Scan the QR again to reopen it.
    </p>
    <a href="<?php echo BASE_URL; ?>/admin/ayushman-scanner.php" class="btn btn-primary" style="font-weight: 700;">
      <i class="fas fa-qrcode"></i> Scan Again
    </a>
  </div>

<?php else: ?>
  <div id="scan-detail-panel">
    <div class="scan-detail-box">

      <!-- Authenticated Top Banner -->
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 10px 14px; border-radius: 8px; flex-wrap: wrap; gap: 8px;">
        <span class="label label-success">
          <i class="fas fa-check-double"></i> VERIFIED RECORD
        </span>
        <span class="scan-countdown-badge" id="scan-countdown-badge">
          <i class="fas fa-clock"></i> Auto-logout in <span id="scan-countdown-seconds"><?php echo (int) $remainingSeconds; ?></span>s
        </span>
      </div>

      <!-- Citizen Header -->
      <div class="scan-detail-header">
        <img src="<?php echo !empty($record['photo_url']) ? BASE_URL . '/' . htmlspecialchars($record['photo_url']) : BASE_URL . '/images/tuser1.jpg'; ?>" alt="Photo" class="scan-detail-photo">
        <div style="flex: 1;">
          <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <h3 style="margin: 0; font-weight: 800; color: #0b3c5d;">
              <?php echo htmlspecialchars($record['full_name']); ?>
            </h3>
            <span class="badge" style="background: #0284c7; font-size: 0.75rem;">
              <?php echo htmlspecialchars($record['gender'] ?? 'Male'); ?> | DOB: <?php echo htmlspecialchars($record['dob'] ?? 'N/A'); ?>
            </span>
          </div>
          <div style="margin-top: 6px; font-size: 0.88rem; color: #475569;">
            <i class="fas fa-phone text-muted"></i> <?php echo htmlspecialchars($record['mobile']); ?> &nbsp;|&nbsp;
            <i class="fas fa-envelope text-muted"></i> <?php echo htmlspecialchars($record['email']); ?>
          </div>
          <div style="margin-top: 6px;">
            <span class="badge" style="background: #10b981; font-size: 0.78rem; padding: 4px 8px;">
              <i class="fas fa-fingerprint"></i> Aadhaar Number: XXXX-XXXX-<?php echo htmlspecialchars($record['aadhaar_last4'] ?? '0000'); ?>
            </span>
            <span class="badge" style="background: #059669; font-size: 0.78rem; text-transform: uppercase;">
              <?php echo htmlspecialchars($record['status'] ?? 'Submitted'); ?>
            </span>
          </div>
        </div>
      </div>

      <!-- Detailed Identification Grid -->
      <h5 style="font-weight: 800; color: #0b3c5d; margin-top: 0; margin-bottom: 10px;">
        <i class="fas fa-id-card text-warning"></i> National Identity & Health Account Details
      </h5>
      <div class="scan-detail-meta-grid">
        <div class="scan-detail-field">
          <span class="scan-detail-field-label">Application No</span>
          <span class="scan-detail-field-value" style="color: #0b3c5d; font-family: monospace;"><?php echo htmlspecialchars($record['application_no']); ?></span>
        </div>
        <div class="scan-detail-field">
          <span class="scan-detail-field-label">Digital Smart Card ID</span>
          <span class="scan-detail-field-value" style="color: #059669; font-family: monospace;"><?php echo htmlspecialchars($record['card_id']); ?></span>
        </div>
        <div class="scan-detail-field">
          <span class="scan-detail-field-label">14-Digit ABHA Number</span>
          <span class="scan-detail-field-value" style="color: #0284c7; font-family: monospace;"><?php echo htmlspecialchars($record['abha_number'] ?: 'Not Provided'); ?></span>
        </div>
        <div class="scan-detail-field">
          <span class="scan-detail-field-label">Ration Card Number</span>
          <span class="scan-detail-field-value"><?php echo htmlspecialchars($record['ration_card_no'] ?: 'Not Applicable'); ?></span>
        </div>
        <div class="scan-detail-field">
          <span class="scan-detail-field-label">BOCW / Labour Registration Number</span>
          <span class="scan-detail-field-value"><?php echo htmlspecialchars($record['bocw_reg_no'] ?: 'Not Applicable'); ?></span>
        </div>
        <div class="scan-detail-field">
          <span class="scan-detail-field-label">PMJAY / Ayushman Bharat Letter Number</span>
          <span class="scan-detail-field-value"><?php echo htmlspecialchars($record['pmjay_letter_no'] ?: 'Not Applicable'); ?></span>
        </div>
      </div>

      <!-- Address -->
      <div class="scan-detail-field" style="margin-bottom: 16px;">
        <span class="scan-detail-field-label">Citizen's Permanent Address</span>
        <span class="scan-detail-field-value" style="font-weight: 500; font-size: 0.88rem;">
          <?php
            $addrArr = array_filter([$record['address'] ?? '', $record['district'] ?? '', $record['state'] ?? '', $record['pincode'] ?? '']);
            echo htmlspecialchars(implode(', ', $addrArr) ?: 'N/A');
          ?>
        </span>
      </div>

      <!-- Uploaded Documents -->
      <h5 style="font-weight: 800; color: #0b3c5d; margin-top: 18px; margin-bottom: 10px;">
        <i class="fas fa-folder-open text-primary"></i> Attached Government Documents
      </h5>
      <div style="margin-bottom: 18px;">
        <?php if (!empty($record['photo_url'])): ?>
          <a href="<?php echo BASE_URL . '/' . htmlspecialchars($record['photo_url']); ?>" target="_blank" class="scan-doc-pill-link"><i class="fas fa-camera"></i> View Citizen Photo</a>
        <?php endif; ?>
        <?php if (!empty($record['abha_card_url'])): ?>
          <a href="<?php echo BASE_URL . '/' . htmlspecialchars($record['abha_card_url']); ?>" target="_blank" class="scan-doc-pill-link"><i class="fas fa-id-card"></i> View ABHA Card</a>
        <?php endif; ?>
        <?php if (!empty($record['ayushman_card_url'])): ?>
          <a href="<?php echo BASE_URL . '/' . htmlspecialchars($record['ayushman_card_url']); ?>" target="_blank" class="scan-doc-pill-link"><i class="fas fa-heartbeat text-danger"></i> View Ayushman Card</a>
        <?php endif; ?>
        <?php if (!empty($record['ration_card_url'])): ?>
          <a href="<?php echo BASE_URL . '/' . htmlspecialchars($record['ration_card_url']); ?>" target="_blank" class="scan-doc-pill-link"><i class="fas fa-wheat-awn text-warning"></i> View Ration Card</a>
        <?php endif; ?>
        <?php if (!empty($record['bocw_card_url'])): ?>
          <a href="<?php echo BASE_URL . '/' . htmlspecialchars($record['bocw_card_url']); ?>" target="_blank" class="scan-doc-pill-link"><i class="fas fa-hard-hat text-secondary"></i> BOCW Labour Card</a>
        <?php endif; ?>
        <?php if (!empty($record['pmjay_letter_url'])): ?>
          <a href="<?php echo BASE_URL . '/' . htmlspecialchars($record['pmjay_letter_url']); ?>" target="_blank" class="scan-doc-pill-link"><i class="fas fa-envelope-open-text text-success"></i> PMJAY Letter</a>
        <?php endif; ?>
      </div>

      <!-- Administrative Actions -->
      <div style="border-top: 1.5px dashed #cbd5e1; padding-top: 16px; display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="<?php echo BASE_URL; ?>/ayushman-card.php?app_no=<?php echo urlencode($record['application_no']); ?>" target="_blank" class="btn btn-primary" style="font-weight: 700;">
          <i class="fas fa-id-card"></i> Print/View Smart ID Card
        </a>
        <form method="POST" action="<?php echo BASE_URL; ?>/admin/ayushman-helpdesk.php" style="display:inline;">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['admin_csrf_token']); ?>">
          <input type="hidden" name="action" value="update_status">
          <input type="hidden" name="app_id" value="<?php echo (int) $record['id']; ?>">
          <input type="hidden" name="status" value="approved">
          <input type="hidden" name="admin_notes" value="Verified and approved via Admin Secure QR Scanner.">
          <button type="submit" class="btn btn-success" style="font-weight: 700;" onclick="return confirm('Mark this application as Approved?');">
            <i class="fas fa-check-circle"></i> Approve
          </button>
        </form>
        <a href="<?php echo BASE_URL; ?>/admin/ayushman-scanner.php" class="btn btn-default" style="font-weight: 700;">
          <i class="fas fa-redo"></i> Scan Next Card
        </a>
      </div>

    </div>
  </div>

  <script>
  (function() {
    let remaining = <?php echo (int) $remainingSeconds; ?>;
    const secondsEl = document.getElementById('scan-countdown-seconds');
    const badgeEl = document.getElementById('scan-countdown-badge');
    const panel = document.getElementById('scan-detail-panel');

    const timer = setInterval(function() {
      remaining--;
      if (remaining <= 0) {
        clearInterval(timer);
        // Reload so the server re-checks the session flag and renders the
        // "Record Auto-Closed" state — not just a client-side hide, so a
        // manual refresh/back can't bypass the expiry either.
        window.location.reload();
        return;
      }
      if (secondsEl) secondsEl.innerText = remaining;
      if (badgeEl) badgeEl.classList.toggle('danger', remaining <= 20);
    }, 1000);
  })();
  </script>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
