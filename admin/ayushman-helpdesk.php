<?php
// admin/ayushman-helpdesk.php - Official Ayushman Help Desk Applications Management
require_once __DIR__ . '/auth.php';

$page_title = 'Ayushman Help Desk Applications & Health IDs';

// Status Update Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $appId = (int) ($_POST['app_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $adminNotes = trim($_POST['admin_notes'] ?? '');

    $allowedStatuses = ['submitted', 'verified', 'approved', 'rejected'];
    if ($appId > 0 && in_array($newStatus, $allowedStatuses)) {
        $stmt = $pdo->prepare("UPDATE ayushman_applications SET status = ?, admin_notes = ? WHERE id = ?");
        $stmt->execute([$newStatus, $adminNotes, $appId]);
        set_flash('success', "आवेदन स्थिति सफलतापूर्वक अद्यतन की गई (Status updated to {$newStatus}).");
    } else {
        set_flash('error', "अमान्य अनुरोध या स्थिति मान (Invalid status update request).");
    }
    header('Location: ' . BASE_URL . '/admin/ayushman-helpdesk.php');
    exit;
}

// Filters & Query
$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT * FROM ayushman_applications WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (application_no LIKE ? OR card_id LIKE ? OR full_name LIKE ? OR mobile LIKE ? OR email LIKE ? OR abha_number LIKE ?)";
    $searchParam = "%{$search}%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
}

if ($statusFilter !== '' && in_array($statusFilter, ['submitted', 'verified', 'approved', 'rejected'])) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY id DESC LIMIT 200";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$applications = $stmt->fetchAll();

// Statistics
$statTotal = (int) $pdo->query("SELECT COUNT(*) FROM ayushman_applications")->fetchColumn();
$statSubmitted = (int) $pdo->query("SELECT COUNT(*) FROM ayushman_applications WHERE status = 'submitted'")->fetchColumn();
$statVerified = (int) $pdo->query("SELECT COUNT(*) FROM ayushman_applications WHERE status = 'verified'")->fetchColumn();
$statApproved = (int) $pdo->query("SELECT COUNT(*) FROM ayushman_applications WHERE status = 'approved'")->fetchColumn();

include __DIR__ . '/header.php';
?>

<style>
.stat-widget {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 18px 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 20px;
}
.stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}
.stat-val {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
}
.stat-label {
    font-size: 0.8rem;
    color: #64748b;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.doc-thumb-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    color: #334155;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 0.74rem;
    font-weight: 600;
    margin: 2px 2px 2px 0;
    text-decoration: none;
}
.doc-thumb-badge:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.table td {
    vertical-align: middle !important;
}
</style>

<!-- Top Title & Scanner CTA -->
<div class="row" style="margin-bottom: 20px; align-items: center;">
  <div class="col-md-7 col-sm-12">
    <h3 style="margin: 0; font-weight: 800; color: #0b3c5d;">
      <i class="fas fa-hand-holding-medical text-warning"></i> आयुष्मान हेल्प डेस्क - डिजिटल हेल्थ स्मार्ट आईडी
    </h3>
    <p style="margin: 4px 0 0; color: #64748b; font-size: 0.9rem;">
      नागरिकों द्वारा सबमिट किए गए आवेदन, आधार ई-केवाईसी स्थिति, दस्तावेज व 256-बिट एन्क्रिप्टेड स्मार्ट कार्ड।
    </p>
  </div>
  <div class="col-md-5 col-sm-12 text-right">
    <a href="<?php echo BASE_URL; ?>/admin/ayushman-scanner.php" class="btn btn-success" style="background: #059669; border-color: #059669; font-weight: 700; padding: 10px 18px; border-radius: 8px;">
      <i class="fas fa-qrcode"></i> ओपन अधिकृत क्यूआर स्कैनर (Live Scanner)
    </a>
    <a href="<?php echo BASE_URL; ?>/ayushman-helpdesk.php" target="_blank" class="btn btn-default" style="font-weight: 600; border-radius: 8px;">
      <i class="fas fa-external-link-alt"></i> पब्लिक फॉर्म
    </a>
  </div>
</div>

<!-- Stat Widgets Row -->
<div class="row">
  <div class="col-md-3 col-sm-6">
    <div class="stat-widget">
      <div class="stat-icon" style="background: #eff6ff; color: #2563eb;">
        <i class="fas fa-file-invoice"></i>
      </div>
      <div>
        <div class="stat-val"><?php echo number_format($statTotal); ?></div>
        <div class="stat-label">कुल आवेदन (Total)</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="stat-widget">
      <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
        <i class="fas fa-clock"></i>
      </div>
      <div>
        <div class="stat-val"><?php echo number_format($statSubmitted); ?></div>
        <div class="stat-label">लंबित सबमिशन (Pending)</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="stat-widget">
      <div class="stat-icon" style="background: #ecfdf5; color: #059669;">
        <i class="fas fa-user-check"></i>
      </div>
      <div>
        <div class="stat-val"><?php echo number_format($statVerified); ?></div>
        <div class="stat-label">सत्यापित (Verified)</div>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="stat-widget">
      <div class="stat-icon" style="background: #f0fdf4; color: #16a34a;">
        <i class="fas fa-id-card"></i>
      </div>
      <div>
        <div class="stat-val"><?php echo number_format($statApproved); ?></div>
        <div class="stat-label">स्वीकृत (Approved)</div>
      </div>
    </div>
  </div>
</div>

<!-- Search & Filter Card -->
<div class="panel panel-default" style="border-radius: 10px; margin-bottom: 24px; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
  <div class="panel-body" style="padding: 16px;">
    <form method="GET" action="" class="form-inline" style="display: flex; gap: 12px; flex-wrap: wrap;">
      <div class="form-group" style="flex: 1; min-width: 240px; margin-bottom: 0;">
        <div class="input-group" style="width: 100%;">
          <span class="input-group-addon"><i class="fas fa-search"></i></span>
          <input type="text" name="search" class="form-control" placeholder="नाम, आवेदन क्र., कार्ड आईडी, मोबाइल, ईमेल, आभा नंबर खोजें..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
      </div>
      <div class="form-group" style="margin-bottom: 0;">
        <select name="status" class="form-control" onchange="this.form.submit()">
          <option value="">सभी स्थितियां (All Statuses)</option>
          <option value="submitted" <?php echo ($statusFilter === 'submitted') ? 'selected' : ''; ?>>Submitted (लंबित)</option>
          <option value="verified" <?php echo ($statusFilter === 'verified') ? 'selected' : ''; ?>>Verified (सत्यापित)</option>
          <option value="approved" <?php echo ($statusFilter === 'approved') ? 'selected' : ''; ?>>Approved (स्वीकृत)</option>
          <option value="rejected" <?php echo ($statusFilter === 'rejected') ? 'selected' : ''; ?>>Rejected (अस्वीकृत)</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary" style="background: #0b3c5d; border-color: #0b3c5d; font-weight: 700;">
        <i class="fas fa-filter"></i> फ़िल्टर करें
      </button>
      <?php if ($search !== '' || $statusFilter !== ''): ?>
        <a href="<?php echo BASE_URL; ?>/admin/ayushman-helpdesk.php" class="btn btn-default">
          <i class="fas fa-times"></i> रीसेट
        </a>
      <?php endif; ?>
    </form>
  </div>
</div>

<!-- Applications Table -->
<div class="panel panel-default" style="border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden;">
  <div class="panel-heading" style="background: #ffffff; border-bottom: 2px solid #e2e8f0; padding: 14px 20px;">
    <h4 style="margin: 0; font-weight: 700; color: #1e293b;">
      <i class="fas fa-list text-primary"></i> सभी आवेदन सूची (<?php echo count($applications); ?> रिकॉर्ड मिले)
    </h4>
  </div>

  <div class="table-responsive">
    <table class="table table-hover" style="margin-bottom: 0;">
      <thead style="background: #f8fafc; font-size: 0.82rem; text-transform: uppercase; color: #475569;">
        <tr>
          <th style="width: 50px;">#</th>
          <th>आवेदन क्रमांक / आईडी</th>
          <th>नागरिक विवरण</th>
          <th>संपर्क (Mobile/Email)</th>
          <th>आधार e-KYC</th>
          <th>आभा / स्वास्थ्य विवरण</th>
          <th>अपलोड किए गए दस्तावेज</th>
          <th>स्थिति</th>
          <th style="text-align: right; width: 140px;">कार्यवाही (Actions)</th>
        </tr>
      </thead>
      <tbody style="font-size: 0.88rem;">
        <?php if (empty($applications)): ?>
          <tr>
            <td colspan="9" style="text-align: center; padding: 40px; color: #64748b;">
              <i class="fas fa-folder-open fa-3x" style="opacity: 0.4; margin-bottom: 10px;"></i>
              <p>कोई आवेदन प्राप्त नहीं हुआ (No applications found).</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($applications as $idx => $row): ?>
            <tr>
              <td><?php echo $idx + 1; ?></td>
              <td>
                <div style="font-weight: 800; color: #0b3c5d; font-family: monospace;"><?php echo htmlspecialchars($row['application_no']); ?></div>
                <div style="font-size: 0.74rem; color: #059669; font-family: monospace; font-weight: 700;"><?php echo htmlspecialchars($row['card_id']); ?></div>
                <div style="font-size: 0.72rem; color: #94a3b8;"><?php echo date('d M Y, h:i A', strtotime($row['created_at'])); ?></div>
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                  <img src="<?php echo !empty($row['photo_url']) ? BASE_URL . '/' . htmlspecialchars($row['photo_url']) : BASE_URL . '/images/tuser1.jpg'; ?>" 
                       alt="Photo" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; border: 1.5px solid #cbd5e1;">
                  <div>
                    <div style="font-weight: 700; color: #0f172a;"><?php echo htmlspecialchars($row['full_name']); ?></div>
                    <div style="font-size: 0.76rem; color: #64748b;">
                      <?php echo htmlspecialchars($row['gender'] ?? 'N/A'); ?> | DOB: <?php echo htmlspecialchars($row['dob'] ?? 'N/A'); ?>
                    </div>
                  </div>
                </div>
              </td>
              <td>
                <div><i class="fas fa-phone text-muted" style="font-size: 0.75rem;"></i> <?php echo htmlspecialchars($row['mobile']); ?></div>
                <div style="font-size: 0.78rem; color: #64748b;"><?php echo htmlspecialchars($row['email']); ?></div>
              </td>
              <td>
                <?php if ($row['aadhaar_verified']): ?>
                  <span class="badge" style="background: #10b981; font-size: 0.75rem; padding: 4px 8px;">
                    <i class="fas fa-check"></i> आधार Verified
                  </span>
                  <div style="font-family: monospace; font-size: 0.75rem; color: #334155; margin-top: 2px;">
                    XXXX-XXXX-<?php echo htmlspecialchars($row['aadhaar_last4'] ?? '0000'); ?>
                  </div>
                <?php else: ?>
                  <span class="badge" style="background: #ef4444; font-size: 0.75rem;">Unverified</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!empty($row['abha_number'])): ?>
                  <div style="font-weight: 700; color: #0284c7; font-family: monospace; font-size: 0.8rem;">
                    ABHA: <?php echo htmlspecialchars($row['abha_number']); ?>
                  </div>
                <?php endif; ?>
                <?php if (!empty($row['ration_card_no'])): ?>
                  <div style="font-size: 0.74rem; color: #475569;">राशन: <?php echo htmlspecialchars($row['ration_card_no']); ?></div>
                <?php endif; ?>
                <?php if (!empty($row['bocw_reg_no'])): ?>
                  <div style="font-size: 0.74rem; color: #475569;">BOCW: <?php echo htmlspecialchars($row['bocw_reg_no']); ?></div>
                <?php endif; ?>
              </td>
              <td>
                <div style="display: flex; flex-wrap: wrap; max-width: 180px;">
                  <?php if (!empty($row['photo_url'])): ?>
                    <a href="<?php echo BASE_URL . '/' . htmlspecialchars($row['photo_url']); ?>" target="_blank" class="doc-thumb-badge" title="Citizen Photo">
                      <i class="fas fa-camera text-primary"></i> फोटो
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($row['abha_card_url'])): ?>
                    <a href="<?php echo BASE_URL . '/' . htmlspecialchars($row['abha_card_url']); ?>" target="_blank" class="doc-thumb-badge" title="ABHA Card">
                      <i class="fas fa-id-card text-info"></i> आभा
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($row['ayushman_card_url'])): ?>
                    <a href="<?php echo BASE_URL . '/' . htmlspecialchars($row['ayushman_card_url']); ?>" target="_blank" class="doc-thumb-badge" title="Ayushman Card">
                      <i class="fas fa-heartbeat text-danger"></i> आयुष्मान
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($row['ration_card_url'])): ?>
                    <a href="<?php echo BASE_URL . '/' . htmlspecialchars($row['ration_card_url']); ?>" target="_blank" class="doc-thumb-badge" title="Ration Card">
                      <i class="fas fa-wheat-awn text-warning"></i> राशन
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($row['bocw_card_url'])): ?>
                    <a href="<?php echo BASE_URL . '/' . htmlspecialchars($row['bocw_card_url']); ?>" target="_blank" class="doc-thumb-badge" title="BOCW Card">
                      <i class="fas fa-hard-hat text-secondary"></i> BOCW
                    </a>
                  <?php endif; ?>
                  <?php if (!empty($row['pmjay_letter_url'])): ?>
                    <a href="<?php echo BASE_URL . '/' . htmlspecialchars($row['pmjay_letter_url']); ?>" target="_blank" class="doc-thumb-badge" title="PMJAY Letter">
                      <i class="fas fa-envelope-open-text text-success"></i> PMJAY
                    </a>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <?php 
                  $statusMap = [
                    'submitted' => ['color' => '#0284c7', 'label' => 'Submitted'],
                    'verified'  => ['color' => '#10b981', 'label' => 'Verified'],
                    'approved'  => ['color' => '#059669', 'label' => 'Approved'],
                    'rejected'  => ['color' => '#dc2626', 'label' => 'Rejected'],
                  ];
                  $currSt = $statusMap[$row['status']] ?? ['color' => '#64748b', 'label' => $row['status']];
                ?>
                <span class="badge" style="background: <?php echo $currSt['color']; ?>; text-transform: uppercase; font-size: 0.72rem;">
                  <?php echo $currSt['label']; ?>
                </span>
              </td>
              <td style="text-align: right; white-space: nowrap;">
                <div class="btn-group">
                  <!-- View Card -->
                  <a href="<?php echo BASE_URL; ?>/ayushman-card.php?app_no=<?php echo urlencode($row['application_no']); ?>" target="_blank" class="btn btn-default btn-xs" title="स्मार्ट कार्ड देखें">
                    <i class="fas fa-id-card text-warning"></i>
                  </a>
                  <!-- Open in Scanner -->
                  <a href="<?php echo BASE_URL; ?>/admin/ayushman-scanner.php?token=<?php echo urlencode($row['encrypted_qr_token']); ?>" class="btn btn-default btn-xs" title="डिक्रिप्ट एवं स्कैन करें">
                    <i class="fas fa-qrcode text-success"></i>
                  </a>
                  <!-- Update Status Trigger -->
                  <button type="button" class="btn btn-default btn-xs" title="स्थिति बदलें" data-toggle="modal" data-target="#statusModal<?php echo $row['id']; ?>">
                    <i class="fas fa-edit text-primary"></i>
                  </button>
                </div>

                <!-- Status Update Modal -->
                <div class="modal fade" id="statusModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" style="text-align: left;">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content" style="border-radius: 10px; overflow: hidden;">
                      <form method="POST" action="">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                        
                        <div class="modal-header" style="background: #0b3c5d; color: white;">
                          <button type="button" class="close" data-dismiss="modal" style="color: white;">&times;</button>
                          <h4 class="modal-title" style="font-weight: 700;">
                            आवेदन स्थिति बदलें - <?php echo htmlspecialchars($row['application_no']); ?>
                          </h4>
                        </div>
                        <div class="modal-body" style="padding: 22px;">
                          <div class="form-group">
                            <label>नागरिक का नाम:</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['full_name']); ?>" disabled>
                          </div>
                          <div class="form-group">
                            <label>नई स्थिति (New Status):</label>
                            <select name="status" class="form-control" style="font-weight: 700;">
                              <option value="submitted" <?php echo ($row['status'] === 'submitted') ? 'selected' : ''; ?>>Submitted (लंबित समीक्षा)</option>
                              <option value="verified" <?php echo ($row['status'] === 'verified') ? 'selected' : ''; ?>>Verified (दस्तावेज एवं आधार सत्यापित)</option>
                              <option value="approved" <?php echo ($row['status'] === 'approved') ? 'selected' : ''; ?>>Approved (स्मार्ट कार्ड जारी एवं स्वीकृत)</option>
                              <option value="rejected" <?php echo ($row['status'] === 'rejected') ? 'selected' : ''; ?>>Rejected (अस्वीकृत)</option>
                            </select>
                          </div>
                          <div class="form-group">
                            <label>प्रशासनिक टिप्पणी (Admin Notes):</label>
                            <textarea name="admin_notes" rows="3" class="form-control" placeholder="नागरिक को सूचना अथवा आंतरिक सत्यापन टिप्पणी..."><?php echo htmlspecialchars($row['admin_notes'] ?? ''); ?></textarea>
                          </div>
                        </div>
                        <div class="modal-footer" style="background: #f8fafc;">
                          <button type="button" class="btn btn-default" data-dismiss="modal">रद्द करें</button>
                          <button type="submit" class="btn btn-success" style="font-weight: 700;">
                            <i class="fas fa-save"></i> स्थिति सुरक्षित करें
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
