<?php
// admin/services.php - Citizen Services Master Management Console
require_once __DIR__ . '/auth.php';

$page_title = 'Citizen Services Catalog Management';

// Handle quick inline status toggle, fee update, or delete
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $action = $_POST['action'] ?? '';
    $service_id = (int) ($_POST['service_id'] ?? 0);

    if ($action === 'toggle_status' && $service_id > 0) {
        $stmt = $pdo->prepare("UPDATE `services` SET `is_active` = NOT `is_active` WHERE `id` = ?");
        $stmt->execute([$service_id]);
        set_flash('success', "Service status toggled successfully!");
        header('Location: ' . BASE_URL . '/admin/services.php');
        exit;
    }

    if ($action === 'update_quick' && $service_id > 0) {
        $fee = (float) ($_POST['fee'] ?? 0);
        $official_url = trim($_POST['official_url'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $stmt = $pdo->prepare("UPDATE `services` SET `fee` = ?, `official_url` = ?, `is_active` = ? WHERE `id` = ?");
        $stmt->execute([$fee, $official_url, $is_active, $service_id]);
        set_flash('success', "Service updated successfully!");
        header('Location: ' . BASE_URL . '/admin/services.php');
        exit;
    }

    if ($action === 'delete_service' && $service_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM `services` WHERE `id` = ?");
        $stmt->execute([$service_id]);
        set_flash('success', "Service deleted successfully from catalog!");
        header('Location: ' . BASE_URL . '/admin/services.php');
        exit;
    }
}

// Fetch filter parameters
$filter_type = $_GET['type'] ?? '';
$filter_category = $_GET['category'] ?? '';
$search_query = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM `services` WHERE 1=1";
$params = [];

if ($filter_type === 'api' || $filter_type === 'redirect') {
    $sql .= " AND `service_type` = ?";
    $params[] = $filter_type;
}

if (!empty($filter_category)) {
    $sql .= " AND `category` = ?";
    $params[] = $filter_category;
}

if (!empty($search_query)) {
    $sql .= " AND (`service_name` LIKE ? OR `service_name_hi` LIKE ? OR `short_desc` LIKE ? OR `slug` LIKE ?)";
    $like = '%' . $search_query . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY `sort_order` ASC, `id` ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary Statistics
$total_count = (int) $pdo->query("SELECT COUNT(*) FROM `services`")->fetchColumn();
$api_count = (int) $pdo->query("SELECT COUNT(*) FROM `services` WHERE `service_type` = 'api'")->fetchColumn();
$redirect_count = (int) $pdo->query("SELECT COUNT(*) FROM `services` WHERE `service_type` = 'redirect'")->fetchColumn();
$active_count = (int) $pdo->query("SELECT COUNT(*) FROM `services` WHERE `is_active` = 1")->fetchColumn();

// Unique categories
$cat_stmt = $pdo->query("SELECT DISTINCT `category` FROM `services` WHERE `category` IS NOT NULL AND `category` != '' ORDER BY `category` ASC");
$all_categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/header.php';
?>

<!-- Title & Action Bar -->
<div class="row" style="margin-bottom: 24px; align-items: center;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-text); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-cogs" style="color: var(--adm-saffron); margin-right: 8px;"></i> Citizen Services Master Catalog
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.92rem; margin-bottom: 0;">
      Manage digital ID cards, official government facilitation services, fees, SEO metadata, and verification guidelines.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <a href="<?php echo BASE_URL; ?>/admin/service-edit.php" class="btn btn-primary" style="background: var(--adm-saffron); border-color: var(--adm-saffron); font-weight: 700; border-radius: 8px; padding: 9px 18px;">
      <i class="fas fa-plus-circle me-1"></i> Add New Service
    </a>
  </div>
</div>

<!-- Stat Cards -->
<div class="row" style="margin-bottom: 24px;">
  <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 12px;">
    <div class="adm-card" style="padding: 18px 20px; border-left: 4px solid #3b82f6;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total Services</div>
      <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin-top: 4px;"><?php echo $total_count; ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">In Citizen Database</div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 12px;">
    <div class="adm-card" style="padding: 18px 20px; border-left: 4px solid #10b981;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #047857; text-transform: uppercase;">Instant API Cards</div>
      <div style="font-size: 1.85rem; font-weight: 800; color: #065f46; margin-top: 4px;"><?php echo $api_count; ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Direct Digital PVC (₹50)</div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 12px;">
    <div class="adm-card" style="padding: 18px 20px; border-left: 4px solid #f59e0b;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #b45309; text-transform: uppercase;">Assisted Guides</div>
      <div style="font-size: 1.85rem; font-weight: 800; color: #92400e; margin-top: 4px;"><?php echo $redirect_count; ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Official Dept Guidance</div>
    </div>
  </div>

  <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 12px;">
    <div class="adm-card" style="padding: 18px 20px; border-left: 4px solid #6366f1;">
      <div style="font-size: 0.8rem; font-weight: 700; color: #4338ca; text-transform: uppercase;">Active on Website</div>
      <div style="font-size: 1.85rem; font-weight: 800; color: #3730a3; margin-top: 4px;"><?php echo $active_count; ?></div>
      <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Visible to Public</div>
    </div>
  </div>
</div>

<!-- Search & Filter Bar -->
<div class="adm-card" style="margin-bottom: 24px; padding: 18px 20px;">
  <form method="GET" action="<?php echo BASE_URL; ?>/admin/services.php" class="row align-items-center g-2">
    <div class="col-md-4 col-sm-6">
      <div class="input-group">
        <span class="input-group-addon" style="background: #f8fafc; border-color: #cbd5e1;"><i class="fas fa-search text-muted"></i></span>
        <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" class="form-control" placeholder="Search service name, slug, keywords...">
      </div>
    </div>

    <div class="col-md-3 col-sm-6">
      <select name="type" class="form-control" onchange="this.form.submit()">
        <option value="">All Service Types</option>
        <option value="api" <?php echo ($filter_type === 'api') ? 'selected' : ''; ?>>Instant Direct API (₹50)</option>
        <option value="redirect" <?php echo ($filter_type === 'redirect') ? 'selected' : ''; ?>>Assisted Department Guide (Free)</option>
      </select>
    </div>

    <div class="col-md-3 col-sm-6">
      <select name="category" class="form-control" onchange="this.form.submit()">
        <option value="">All Categories</option>
        <?php foreach ($all_categories as $c): ?>
          <option value="<?php echo htmlspecialchars($c); ?>" <?php echo ($filter_category === $c) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($c); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2 col-sm-6 text-right">
      <a href="<?php echo BASE_URL; ?>/admin/services.php" class="btn btn-default w-100" style="font-weight: 600;">
        <i class="fas fa-undo"></i> Reset
      </a>
    </div>
  </form>
</div>

<!-- Services Table -->
<div class="adm-card" style="padding: 0; overflow: hidden;">
  <div class="table-responsive">
    <table class="table table-hover align-middle" style="margin-bottom: 0; font-size: 0.9rem;">
      <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; font-weight: 700;">
        <tr>
          <th style="width: 60px; text-align: center;">Order</th>
          <th style="width: 75px;">Image</th>
          <th>Service Name &amp; Department</th>
          <th style="width: 130px;">Type &amp; Category</th>
          <th style="width: 100px;">Fee (₹)</th>
          <th style="width: 100px; text-align: center;">Status</th>
          <th style="width: 170px; text-align: right;">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($services)): ?>
          <tr>
            <td colspan="7" class="text-center py-5 text-muted">
              <i class="fas fa-inbox fa-3x mb-3" style="color: #cbd5e1;"></i>
              <p style="font-size: 1rem; margin: 0;">No citizen services matched your search criteria.</p>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($services as $srv): 
            $is_api = ($srv['service_type'] === 'api');
            $img = !empty($srv['image_url']) ? $srv['image_url'] : 'image/services/1.jpg';
          ?>
            <tr style="border-bottom: 1px solid #f1f5f9;">
              <td style="text-align: center; color: #64748b; font-weight: 700;">
                #<?php echo (int)$srv['sort_order']; ?>
              </td>

              <!-- Photo Thumbnail -->
              <td>
                <div style="width: 55px; height: 42px; border-radius: 6px; overflow: hidden; background: #e2e8f0; border: 1px solid #cbd5e1;">
                  <img src="<?php echo BASE_URL . '/' . htmlspecialchars($img); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
              </td>

              <!-- Name & Department -->
              <td>
                <div style="font-weight: 700; color: #0f172a; font-size: 0.96rem;">
                  <i class="<?php echo htmlspecialchars($srv['icon'] ?? 'fas fa-id-card'); ?>" style="color: var(--adm-saffron); margin-right: 6px;"></i>
                  <?php echo htmlspecialchars($srv['service_name']); ?>
                </div>
                <?php if (!empty($srv['service_name_hi'])): ?>
                  <div style="font-size: 0.82rem; color: #64748b;">
                    <?php echo htmlspecialchars($srv['service_name_hi']); ?>
                  </div>
                <?php endif; ?>
                <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;">
                  <i class="fas fa-landmark me-1"></i> <?php echo htmlspecialchars($srv['department_en'] ?? 'Government of India'); ?>
                </div>
              </td>

              <!-- Type & Category -->
              <td>
                <?php if ($is_api): ?>
                  <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.72rem; padding: 4px 8px; border-radius: 12px; display: inline-block; margin-bottom: 4px;">
                    <i class="fas fa-bolt me-1"></i> Instant API
                  </span>
                <?php else: ?>
                  <span class="badge" style="background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; font-size: 0.72rem; padding: 4px 8px; border-radius: 12px; display: inline-block; margin-bottom: 4px;">
                    <i class="fas fa-compass me-1"></i> Assisted Guide
                  </span>
                <?php endif; ?>
                <div>
                  <span class="badge" style="background: #f1f5f9; color: #475569; font-size: 0.72rem; border-radius: 8px;">
                    <?php echo htmlspecialchars($srv['category']); ?>
                  </span>
                </div>
              </td>

              <!-- Fee -->
              <td>
                <span style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                  ₹<?php echo number_format((float)$srv['fee'], 0); ?>
                </span>
                <?php if ($srv['fee'] == 0): ?>
                  <div style="font-size: 0.72rem; color: #16a34a; font-weight: 600;">Free Assistance</div>
                <?php else: ?>
                  <div style="font-size: 0.72rem; color: #64748b;">PVC Card Fee</div>
                <?php endif; ?>
              </td>

              <!-- Status Toggle -->
              <td style="text-align: center;">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/services.php" style="display: inline;">
                  <input type="hidden" name="action" value="toggle_status">
                  <input type="hidden" name="service_id" value="<?php echo $srv['id']; ?>">
                  <?php if ($srv['is_active']): ?>
                    <button type="submit" class="btn btn-xs btn-success" style="border-radius: 20px; font-weight: 700; padding: 3px 10px;" title="Click to Deactivate">
                      <i class="fas fa-check-circle me-1"></i> Active
                    </button>
                  <?php else: ?>
                    <button type="submit" class="btn btn-xs btn-default" style="border-radius: 20px; font-weight: 700; padding: 3px 10px; color: #94a3b8;" title="Click to Activate">
                      <i class="fas fa-pause-circle me-1"></i> Paused
                    </button>
                  <?php endif; ?>
                </form>
              </td>

              <!-- Action Buttons -->
              <td style="text-align: right;">
                <div class="btn-group" role="group">
                  <a href="<?php echo BASE_URL; ?>/admin/service-edit.php?id=<?php echo $srv['id']; ?>" class="btn btn-xs btn-primary" style="font-weight: 700;" title="Edit Service">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <a href="<?php echo BASE_URL; ?>/service/<?php echo urlencode($srv['slug']); ?>" target="_blank" class="btn btn-xs btn-default" title="View Live Page">
                    <i class="fas fa-external-link-alt"></i>
                  </a>
                  <form method="POST" action="<?php echo BASE_URL; ?>/admin/services.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to permanently delete this service?');">
                    <input type="hidden" name="action" value="delete_service">
                    <input type="hidden" name="service_id" value="<?php echo $srv['id']; ?>">
                    <button type="submit" class="btn btn-xs btn-danger" title="Delete Service">
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </form>
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
