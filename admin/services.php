<?php
// admin/services.php - Service Catalog & Fee Configuration
require_once __DIR__ . '/auth.php';

$page_title = 'Service Catalog Configuration';

// Handle service fee or status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
    $sid = (int) $_POST['service_id'];
    $fee = (float) ($_POST['fee'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $official_url = trim($_POST['official_url'] ?? '');

    $stmt = $pdo->prepare("UPDATE services SET fee = ?, is_active = ?, official_url = ? WHERE id = ?");
    $stmt->execute([$fee, $is_active, $official_url, $sid]);
    set_flash('success', "Service configuration updated successfully!");
    header('Location: ' . BASE_URL . '/admin/services.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC");
$services = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>

<!-- Title Row -->
<div class="row" style="margin-bottom: 24px;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-navy); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-cogs text-warning"></i> Service Catalog &amp; Digital Fee Controls
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.9rem;">
      Toggle services active/inactive, adjust nominal facilitation fees (₹0 for assisted or ₹50 standard), and configure official portal deep-links.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.9rem; padding: 7px 14px;">
      Total: <?php echo count($services); ?> Services
    </span>
  </div>
</div>

<div class="adm-card">
  <div class="adm-card-header">
    <h4><i class="fas fa-list-alt text-primary"></i> Citizen Services Configuration Table</h4>
  </div>

  <div class="adm-card-body" style="padding: 0;">
    <div class="table-responsive">
      <table class="table table-hover table-striped" style="font-size: 0.88rem; margin-bottom: 0;">
        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th style="width: 50px;">#ID</th>
            <th>Service Name &amp; Category</th>
            <th>Type</th>
            <th style="width: 140px;">Fee (₹ INR)</th>
            <th>Official Portal Deep-Link</th>
            <th style="width: 90px; text-align: center;">Active</th>
            <th style="width: 110px; text-align: right;">Save</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($services as $srv): 
            $is_api = ($srv['service_type'] === 'api');
          ?>
            <tr>
              <form method="POST" action="<?php echo BASE_URL; ?>/admin/services.php">
                <input type="hidden" name="service_id" value="<?php echo $srv['id']; ?>">
                
                <td><code>#<?php echo $srv['id']; ?></code></td>
                <td>
                  <div style="font-weight: 700; color: var(--adm-navy); font-size: 0.95rem;">
                    <i class="<?php echo $srv['icon']; ?>" style="margin-right: 6px; color: var(--adm-saffron);"></i>
                    <?php echo htmlspecialchars($srv['service_name']); ?>
                  </div>
                  <span class="badge" style="background: #e2e8f0; color: #475569; font-size: 0.7rem;">
                    <?php echo htmlspecialchars($srv['category']); ?>
                  </span>
                </td>
                <td>
                  <?php if ($is_api): ?>
                    <span class="label label-success" style="font-size: 0.75rem;"><i class="fas fa-bolt"></i> Direct API</span>
                  <?php else: ?>
                    <span class="label label-info" style="font-size: 0.75rem;"><i class="fas fa-compass"></i> Assisted Guide</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="input-group input-group-sm">
                    <span class="input-group-addon">₹</span>
                    <input type="number" step="1.00" name="fee" value="<?php echo (float)$srv['fee']; ?>" class="form-control text-right" style="font-weight: 700; height: 32px;" required>
                  </div>
                </td>
                <td>
                  <input type="url" name="official_url" value="<?php echo htmlspecialchars($srv['official_url'] ?? ''); ?>" class="form-control input-sm" placeholder="https://official.gov.in" style="height: 32px; font-size: 0.8rem;">
                </td>
                <td style="text-align: center;">
                  <input type="checkbox" name="is_active" value="1" <?php echo ($srv['is_active'] == 1) ? 'checked' : ''; ?> style="width: 18px; height: 18px; cursor: pointer;">
                </td>
                <td style="text-align: right;">
                  <button type="submit" class="btn btn-primary btn-xs" style="padding: 5px 12px; font-weight: 700;">
                    <i class="fas fa-save"></i> Update
                  </button>
                </td>
              </form>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
