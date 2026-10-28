<?php
// admin/schemes.php - Government Schemes Master Management Console
require_once __DIR__ . '/auth.php';

$page_title = 'Government Schemes Management';

// Handle quick inline status toggle or delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $scheme_id = (int) ($_POST['scheme_id'] ?? 0);

    if ($action === 'toggle_status' && $scheme_id > 0) {
        $stmt = $pdo->prepare("UPDATE `schemes` SET `is_active` = NOT `is_active` WHERE `id` = ?");
        $stmt->execute([$scheme_id]);
        set_flash('success', "Scheme status toggled successfully!");
        header('Location: ' . BASE_URL . '/admin/schemes.php');
        exit;
    }

    if ($action === 'delete_scheme' && $scheme_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM `schemes` WHERE `id` = ?");
        $stmt->execute([$scheme_id]);
        set_flash('success', "Scheme deleted successfully from database!");
        header('Location: ' . BASE_URL . '/admin/schemes.php');
        exit;
    }
}

// Fetch filter parameters
$filter_funding = $_GET['funding'] ?? '';
$filter_category = $_GET['category'] ?? '';
$search_query = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM `schemes` WHERE 1=1";
$params = [];

if ($filter_funding === 'central_sector' || $filter_funding === 'centrally_sponsored') {
    $sql .= " AND `funding_type` = ?";
    $params[] = $filter_funding;
}

if (!empty($filter_category)) {
    $sql .= " AND `category` = ?";
    $params[] = $filter_category;
}

if (!empty($search_query)) {
    $sql .= " AND (`title_en` LIKE ? OR `title_hi` LIKE ? OR `ministry_en` LIKE ? OR `slug` LIKE ?)";
    $like = '%' . $search_query . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY `sort_order` ASC, `id` ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$schemes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Summary stats
$total_count = (int) $pdo->query("SELECT COUNT(*) FROM `schemes`")->fetchColumn();
$central_sector_count = (int) $pdo->query("SELECT COUNT(*) FROM `schemes` WHERE `funding_type` = 'central_sector'")->fetchColumn();
$centrally_sponsored_count = (int) $pdo->query("SELECT COUNT(*) FROM `schemes` WHERE `funding_type` = 'centrally_sponsored'")->fetchColumn();
$active_count = (int) $pdo->query("SELECT COUNT(*) FROM `schemes` WHERE `is_active` = 1")->fetchColumn();

include __DIR__ . '/header.php';
?>

<!-- Title & Action Bar -->
<div class="row" style="margin-bottom: 24px; align-items: center;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-navy); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-landmark text-warning"></i> Government Schemes Directory
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.9rem;">
      Manage all 20+ flagship government schemes, bilingual Hindi/English details, eligibility rules, and official portal deep-links directly from the database.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <a href="<?php echo BASE_URL; ?>/admin/scheme-edit.php" class="btn btn-primary" style="background: var(--adm-navy); border: none; font-weight: 700; border-radius: 8px; padding: 10px 18px;">
      <i class="fas fa-plus-circle mr-1"></i> Add New Scheme
    </a>
  </div>
</div>

<!-- 4 Key Stat Badges -->
<div class="row" style="margin-bottom: 24px;">
  <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 12px;">
    <div class="adm-card" style="padding: 18px; border-left: 4px solid var(--adm-navy);">
      <div class="text-muted" style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase;">Total Flagship Schemes</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: var(--adm-navy);"><?php echo $total_count; ?></div>
      <small style="color: #64748b;">In Master Database</small>
    </div>
  </div>
  <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 12px;">
    <div class="adm-card" style="padding: 18px; border-left: 4px solid #2563eb;">
      <div class="text-muted" style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase;">Central Sector (100%)</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #2563eb;"><?php echo $central_sector_count; ?></div>
      <small style="color: #64748b;">Direct Central DBT</small>
    </div>
  </div>
  <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 12px;">
    <div class="adm-card" style="padding: 18px; border-left: 4px solid #059669;">
      <div class="text-muted" style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase;">Centrally Sponsored</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #059669;"><?php echo $centrally_sponsored_count; ?></div>
      <small style="color: #64748b;">Shared 60:40 with States</small>
    </div>
  </div>
  <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 12px;">
    <div class="adm-card" style="padding: 18px; border-left: 4px solid #16a34a;">
      <div class="text-muted" style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase;">Active on Website</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #16a34a;"><?php echo $active_count; ?></div>
      <small style="color: #64748b;">Publicly Accessible</small>
    </div>
  </div>
</div>

<!-- Filter & Search Controls -->
<div class="adm-card" style="padding: 18px 20px; margin-bottom: 24px;">
  <form method="GET" action="<?php echo BASE_URL; ?>/admin/schemes.php" class="row" style="align-items: center;">
    <div class="col-md-4 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
      <div class="input-group">
        <span class="input-group-addon"><i class="fas fa-search"></i></span>
        <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" class="form-control" placeholder="Search by scheme name, ministry, or slug...">
      </div>
    </div>
    
    <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
      <select name="funding" class="form-control" onchange="this.form.submit();">
        <option value="">All Funding Types</option>
        <option value="central_sector" <?php echo ($filter_funding === 'central_sector') ? 'selected' : ''; ?>>Central Sector (100% Central)</option>
        <option value="centrally_sponsored" <?php echo ($filter_funding === 'centrally_sponsored') ? 'selected' : ''; ?>>Centrally Sponsored (CSS)</option>
      </select>
    </div>

    <div class="col-md-3 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
      <select name="category" class="form-control" onchange="this.form.submit();">
        <option value="">All Categories</option>
        <option value="agriculture" <?php echo ($filter_category === 'agriculture') ? 'selected' : ''; ?>>Agriculture &amp; Farmers</option>
        <option value="energy_utilities" <?php echo ($filter_category === 'energy_utilities') ? 'selected' : ''; ?>>Renewable Energy &amp; Solar</option>
        <option value="healthcare" <?php echo ($filter_category === 'healthcare') ? 'selected' : ''; ?>>Healthcare &amp; Wellness</option>
        <option value="housing" <?php echo ($filter_category === 'housing') ? 'selected' : ''; ?>>Housing &amp; Infrastructure</option>
        <option value="employment" <?php echo ($filter_category === 'employment') ? 'selected' : ''; ?>>Employment &amp; Skills</option>
        <option value="social_security" <?php echo ($filter_category === 'social_security') ? 'selected' : ''; ?>>Social Security &amp; Pension</option>
        <option value="financial_services" <?php echo ($filter_category === 'financial_services') ? 'selected' : ''; ?>>Financial Inclusion</option>
        <option value="water_sanitation" <?php echo ($filter_category === 'water_sanitation') ? 'selected' : ''; ?>>Water &amp; Sanitation</option>
        <option value="women_child" <?php echo ($filter_category === 'women_child') ? 'selected' : ''; ?>>Women &amp; Child</option>
      </select>
    </div>

    <div class="col-md-2 col-sm-6 col-xs-12" style="margin-bottom: 10px; display: flex; gap: 6px;">
      <button type="submit" class="btn btn-sm btn-primary" style="flex: 1; height: 34px;">
        <i class="fas fa-filter"></i> Filter
      </button>
      <?php if (!empty($search_query) || !empty($filter_funding) || !empty($filter_category)): ?>
        <a href="<?php echo BASE_URL; ?>/admin/schemes.php" class="btn btn-sm btn-default" style="height: 34px;" title="Reset Filters">
          <i class="fas fa-undo"></i>
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Schemes Data Table -->
<div class="adm-card">
  <div class="adm-card-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h4 style="margin: 0; font-weight: 700;">
      <i class="fas fa-list-alt text-primary mr-1"></i> Schemes List (<?php echo count($schemes); ?> Schemes)
    </h4>
  </div>

  <div class="adm-card-body" style="padding: 0;">
    <div class="table-responsive">
      <table class="table table-hover table-striped" style="font-size: 0.88rem; margin-bottom: 0; vertical-align: middle;">
        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th style="width: 45px;">#ID</th>
            <th style="width: 65px;">Image</th>
            <th>Scheme Title &amp; Hindi Name</th>
            <th>Funding Type</th>
            <th>Category &amp; Ministry</th>
            <th>Launch &amp; Budget</th>
            <th style="width: 90px; text-align: center;">Status</th>
            <th style="width: 130px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($schemes)): ?>
            <tr>
              <td colspan="8" class="text-center py-4" style="color: #64748b;">
                No government schemes matched your filter criteria.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($schemes as $s): 
              $is_central = ($s['funding_type'] === 'central_sector');
              $img_src = !empty($s['image']) ? (BASE_URL . '/' . $s['image']) : (BASE_URL . '/image/schemes/vajiram-schemes-banner.webp');
            ?>
              <tr>
                <td><code>#<?php echo $s['id']; ?></code></td>
                <td>
                  <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Scheme" style="width: 52px; height: 38px; object-fit: cover; border-radius: 6px; border: 1px solid #cbd5e1;">
                </td>
                <td>
                  <div style="font-weight: 700; color: var(--adm-navy); font-size: 0.95rem;">
                    <?php echo htmlspecialchars($s['title_en']); ?>
                  </div>
                  <div style="font-size: 0.82rem; color: #475569;">
                    <?php echo htmlspecialchars($s['title_hi']); ?>
                  </div>
                  <small style="color: #94a3b8; font-family: monospace;">Slug: <?php echo htmlspecialchars($s['slug']); ?></small>
                </td>
                <td>
                  <?php if ($is_central): ?>
                    <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.74rem;">
                      <i class="fas fa-landmark"></i> Central Sector (100%)
                    </span>
                  <?php else: ?>
                    <span class="badge" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.74rem;">
                      <i class="fas fa-handshake"></i> Centrally Sponsored
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge" style="background: #f1f5f9; color: #334155; font-size: 0.72rem; margin-bottom: 2px;">
                    <?php echo htmlspecialchars($s['category_name_en'] ?? $s['category']); ?>
                  </span>
                  <div style="font-size: 0.75rem; color: #64748b;">
                    <?php echo htmlspecialchars($s['ministry_en']); ?>
                  </div>
                </td>
                <td>
                  <div style="font-weight: 600; color: #1e293b;">
                    <?php echo htmlspecialchars($s['launch_year']); ?>
                  </div>
                  <div style="font-size: 0.75rem; color: #64748b;">
                    <?php echo htmlspecialchars($s['budget'] ?? 'N/A'); ?>
                  </div>
                </td>
                <td style="text-align: center;">
                  <form method="POST" action="<?php echo BASE_URL; ?>/admin/schemes.php" style="display: inline;">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="scheme_id" value="<?php echo $s['id']; ?>">
                    <button type="submit" class="btn btn-xs <?php echo ($s['is_active'] == 1) ? 'btn-success' : 'btn-default'; ?>" style="border-radius: 12px; font-weight: 700; padding: 3px 10px;">
                      <?php echo ($s['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                    </button>
                  </form>
                </td>
                <td style="text-align: right; white-space: nowrap;">
                  <a href="<?php echo BASE_URL; ?>/scheme_details.php?slug=<?php echo urlencode($s['slug']); ?>" target="_blank" class="btn btn-xs btn-default" title="View on Live Website" style="margin-right: 4px;">
                    <i class="fas fa-external-link-alt text-muted"></i>
                  </a>
                  <a href="<?php echo BASE_URL; ?>/admin/scheme-edit.php?id=<?php echo $s['id']; ?>" class="btn btn-xs btn-primary" title="Edit Scheme" style="background: #1e3a8a; border: none; margin-right: 4px;">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <form method="POST" action="<?php echo BASE_URL; ?>/admin/schemes.php" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this government scheme?');">
                    <input type="hidden" name="action" value="delete_scheme">
                    <input type="hidden" name="scheme_id" value="<?php echo $s['id']; ?>">
                    <button type="submit" class="btn btn-xs btn-danger" title="Delete Scheme">
                      <i class="fas fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
