<?php
// admin/schemes.php - Government Schemes Master Management Console
require_once __DIR__ . '/auth.php';

$page_title = 'Government Schemes Management';

// Handle quick inline status toggle or delete
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
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

<style>
  .sch-filter-card { padding: 16px 18px; margin-bottom: 22px; }
  .sch-table td { vertical-align: middle; }
  .sch-thumb { width: 54px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid var(--adm-border); }
  .sch-title { font-weight: 700; color: var(--adm-blue-dark); font-size: 0.95rem; }
  .sch-title-hi { font-size: 0.82rem; color: #475569; }
  .sch-slug { color: #94a3b8; font-family: monospace; font-size: 0.72rem; }
  .sch-chip {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.72rem; font-weight: 600; padding: 3px 9px;
    border-radius: 20px; white-space: nowrap;
  }
  .sch-chip-central { background: var(--adm-blue-tint); color: var(--adm-blue-dark); }
  .sch-chip-sponsored { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
  .sch-chip-category { background: #f1f5f9; color: #334155; }
  .sch-actions .btn { margin-left: 4px; }

  @media (max-width: 575.98px) {
    .sch-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 4px; }
    .sch-actions .btn { margin-left: 0; }
  }
</style>

<!-- Title & Action Bar -->
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div>
    <h2 class="fw-bold mb-1" style="color: var(--adm-blue-dark); font-size: 1.5rem;">
      <i class="fas fa-landmark text-warning"></i> Government Schemes Directory
    </h2>
    <p class="text-muted mb-0" style="font-size: 0.9rem; max-width: 640px;">
      Manage all flagship government schemes, bilingual Hindi/English details, eligibility rules, and official portal links.
    </p>
  </div>
  <a href="<?php echo BASE_URL; ?>/admin/scheme-edit.php" class="btn btn-gov-saffron fw-bold flex-shrink-0">
    <i class="fas fa-plus-circle"></i> Add New Scheme
  </a>
</div>

<!-- 4 Key Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-3 col-sm-6">
    <div class="stat-card mb-0">
      <div class="stat-icon" style="background: var(--adm-blue-tint); color: var(--adm-blue-dark);">
        <i class="fas fa-landmark"></i>
      </div>
      <div>
        <div class="stat-label">Total Schemes</div>
        <div class="stat-value" style="color: var(--adm-blue-dark);"><?php echo $total_count; ?></div>
        <small class="text-muted">In Master Database</small>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="stat-card mb-0">
      <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
        <i class="fas fa-university"></i>
      </div>
      <div>
        <div class="stat-label">Central Sector (100%)</div>
        <div class="stat-value" style="color: #0284c7;"><?php echo $central_sector_count; ?></div>
        <small class="text-muted">Direct Central DBT</small>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="stat-card mb-0">
      <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
        <i class="fas fa-handshake"></i>
      </div>
      <div>
        <div class="stat-label">Centrally Sponsored</div>
        <div class="stat-value" style="color: #16a34a;"><?php echo $centrally_sponsored_count; ?></div>
        <small class="text-muted">Shared 60:40 with States</small>
      </div>
    </div>
  </div>
  <div class="col-md-3 col-sm-6">
    <div class="stat-card mb-0">
      <div class="stat-icon" style="background: var(--adm-saffron-tint); color: var(--adm-saffron-dark);">
        <i class="fas fa-check-circle"></i>
      </div>
      <div>
        <div class="stat-label">Active on Website</div>
        <div class="stat-value" style="color: var(--adm-saffron-dark);"><?php echo $active_count; ?></div>
        <small class="text-muted">Publicly Accessible</small>
      </div>
    </div>
  </div>
</div>

<!-- Filter & Search Controls -->
<div class="adm-card sch-filter-card">
  <form method="GET" action="<?php echo BASE_URL; ?>/admin/schemes.php" class="row g-2 align-items-center">
    <div class="col-md-4">
      <div class="input-group">
        <span class="input-group-text"><i class="fas fa-search text-muted"></i></span>
        <input type="text" name="q" value="<?php echo htmlspecialchars($search_query); ?>" class="form-control" placeholder="Search by scheme name, ministry, or slug...">
      </div>
    </div>

    <div class="col-md-3 col-sm-6">
      <select name="funding" class="form-control" onchange="this.form.submit();">
        <option value="">All Funding Types</option>
        <option value="central_sector" <?php echo ($filter_funding === 'central_sector') ? 'selected' : ''; ?>>Central Sector (100% Central)</option>
        <option value="centrally_sponsored" <?php echo ($filter_funding === 'centrally_sponsored') ? 'selected' : ''; ?>>Centrally Sponsored (CSS)</option>
      </select>
    </div>

    <div class="col-md-3 col-sm-6">
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

    <div class="col-md-2 col-sm-6 d-flex gap-2">
      <button type="submit" class="btn btn-sm btn-primary flex-fill">
        <i class="fas fa-filter"></i> Filter
      </button>
      <?php if (!empty($search_query) || !empty($filter_funding) || !empty($filter_category)): ?>
        <a href="<?php echo BASE_URL; ?>/admin/schemes.php" class="btn btn-sm btn-default" title="Reset Filters">
          <i class="fas fa-undo"></i>
        </a>
      <?php endif; ?>
    </div>
  </form>
</div>

<!-- Schemes Data Table -->
<div class="adm-card">
  <div class="adm-card-header">
    <h4><i class="fas fa-list-alt text-primary"></i> Schemes List (<?php echo count($schemes); ?>)</h4>
  </div>

  <div class="adm-card-body" style="padding: 0;">
    <div class="table-responsive">
      <table class="table table-hover table-striped sch-table" style="font-size: 0.88rem; margin-bottom: 0;">
        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th style="width: 45px;">#ID</th>
            <th style="width: 65px;">Image</th>
            <th>Scheme Title &amp; Hindi Name</th>
            <th>Funding Type</th>
            <th>Category &amp; Ministry</th>
            <th>Launch &amp; Budget</th>
            <th style="width: 90px; text-align: center;">Status</th>
            <th style="width: 150px; text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($schemes)): ?>
            <tr>
              <td colspan="8" class="text-center py-4 text-muted">
                No government schemes matched your filter criteria.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($schemes as $s):
              $is_central = ($s['funding_type'] === 'central_sector');
              $img_src = !empty($s['image']) ? (BASE_URL . '/' . $s['image']) : (BASE_URL . '/image/schemes/default-scheme-banner.jpg');
            ?>
              <tr>
                <td><code>#<?php echo $s['id']; ?></code></td>
                <td>
                  <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Scheme" class="sch-thumb">
                </td>
                <td>
                  <div class="sch-title"><?php echo htmlspecialchars($s['title_en']); ?></div>
                  <div class="sch-title-hi"><?php echo htmlspecialchars($s['title_hi']); ?></div>
                  <span class="sch-slug">Slug: <?php echo htmlspecialchars($s['slug']); ?></span>
                </td>
                <td>
                  <?php if ($is_central): ?>
                    <span class="sch-chip sch-chip-central"><i class="fas fa-landmark"></i> Central Sector</span>
                  <?php else: ?>
                    <span class="sch-chip sch-chip-sponsored"><i class="fas fa-handshake"></i> Centrally Sponsored</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="sch-chip sch-chip-category d-block mb-1" style="width: fit-content;">
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
                  <form method="POST" action="<?php echo BASE_URL; ?>/admin/schemes.php" class="d-inline">
                    <input type="hidden" name="action" value="toggle_status">
                    <input type="hidden" name="scheme_id" value="<?php echo $s['id']; ?>">
                    <button type="submit" class="btn btn-xs <?php echo ($s['is_active'] == 1) ? 'btn-success' : 'btn-default'; ?>" style="border-radius: 12px; font-weight: 700; padding: 3px 10px;">
                      <?php echo ($s['is_active'] == 1) ? 'Active' : 'Inactive'; ?>
                    </button>
                  </form>
                </td>
                <td style="text-align: right; white-space: nowrap;" class="sch-actions">
                  <a href="<?php echo scheme_url($s['slug']); ?>" target="_blank" class="btn btn-xs btn-default" title="View on Live Website">
                    <i class="fas fa-external-link-alt text-muted"></i>
                  </a>
                  <a href="<?php echo BASE_URL; ?>/admin/scheme-edit.php?id=<?php echo $s['id']; ?>" class="btn btn-xs btn-primary" title="Edit Scheme">
                    <i class="fas fa-edit"></i> Edit
                  </a>
                  <form method="POST" action="<?php echo BASE_URL; ?>/admin/schemes.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this government scheme?');">
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
