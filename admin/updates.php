<?php
// admin/updates.php - Manage "Schemes & Updates" Notice Board
require_once __DIR__ . '/auth.php';

$page_title = 'Schemes & Updates Notice Board';
$edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

// Handle Form Submission (create or update)
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'save_update') {
    $id = (int) ($_POST['id'] ?? 0);
    $title_en = trim($_POST['title_en'] ?? '');
    $title_hi = trim($_POST['title_hi'] ?? '');
    $source_url = trim($_POST['source_url'] ?? '');
    $category = trim($_POST['category'] ?? 'general');
    $published_at = trim($_POST['published_at'] ?? date('Y-m-d'));
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($title_en === '') {
        set_flash('error', 'Title (English) is required.');
        header('Location: ' . BASE_URL . '/admin/updates.php');
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE portal_updates SET title_en=?, title_hi=?, source_url=?, category=?, published_at=?, is_active=? WHERE id=? AND created_via='admin'");
        $stmt->execute([$title_en, $title_hi, $source_url, $category, $published_at, $is_active, $id]);
        set_flash('success', 'Update edited successfully.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO portal_updates (title_en, title_hi, source_name, source_url, category, published_at, is_active, created_via) VALUES (?, ?, 'Admin', ?, ?, ?, ?, 'admin')");
        $stmt->execute([$title_en, $title_hi, $source_url, $category, $published_at, $is_active]);
        set_flash('success', 'New update published to the homepage notice board.');
    }

    header('Location: ' . BASE_URL . '/admin/updates.php');
    exit;
}

// Handle Manual One-Click PIB Sync
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'sync_pib') {
    ob_start();
    include __DIR__ . '/../scripts/fetch_pib_updates.php';
    $logOutput = ob_get_clean();
    set_flash('success', 'PIB Feed synchronization completed! New updates have been fetched and updated.');
    header('Location: ' . BASE_URL . '/admin/updates.php');
    exit;
}

// Handle quick toggle / delete
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['quick_action'])) {
    $id = (int) ($_POST['update_id'] ?? 0);
    if ($_POST['quick_action'] === 'toggle_status' && $id > 0) {
        $pdo->prepare("UPDATE portal_updates SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
        set_flash('success', 'Status toggled.');
    } elseif ($_POST['quick_action'] === 'delete' && $id > 0) {
        $pdo->prepare("DELETE FROM portal_updates WHERE id = ?")->execute([$id]);
        set_flash('success', 'Update deleted.');
    }
    header('Location: ' . BASE_URL . '/admin/updates.php');
    exit;
}

// Fetch record being edited
$editing = null;
if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM portal_updates WHERE id = ? AND created_via = 'admin'");
    $stmt->execute([$edit_id]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch all updates (admin + cron-ingested)
$updates = $pdo->query("SELECT * FROM portal_updates ORDER BY published_at DESC, id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
$cron_count = (int) $pdo->query("SELECT COUNT(*) FROM portal_updates WHERE created_via = 'cron'")->fetchColumn();
$admin_count = (int) $pdo->query("SELECT COUNT(*) FROM portal_updates WHERE created_via = 'admin'")->fetchColumn();
$last_cron_run = $pdo->query("SELECT MAX(created_at) FROM portal_updates WHERE created_via = 'cron'")->fetchColumn();

include __DIR__ . '/header.php';
?>

<style>
  .upd-label { font-weight: 700; color: #1e293b; font-size: 0.86rem; margin-bottom: 6px; display: block; }
  .upd-source-badge { font-size: 0.7rem; font-weight: 700; padding: 3px 9px; border-radius: 20px; }
  .upd-source-admin { background: var(--adm-blue-tint); color: var(--adm-blue-dark); }
  .upd-source-cron { background: var(--adm-saffron-tint); color: var(--adm-saffron-dark); }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div>
    <h2 class="fw-bold mb-1" style="color: var(--adm-blue-dark); font-size: 1.5rem;">
      <i class="fas fa-bullhorn text-warning"></i> Schemes & Updates Notice Board
    </h2>
    <p class="text-muted mb-0" style="font-size: 0.9rem; max-width: 640px;">
      Manage the scrolling notice board shown on the homepage. Entries come from two sources: manual admin posts and the automated PIB update fetcher.
    </p>
  </div>
  <a href="<?php echo BASE_URL; ?>/index.php#schemes-updates" target="_blank" class="btn btn-primary fw-bold flex-shrink-0">
    <i class="fas fa-eye"></i> View on Homepage
  </a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4 col-sm-6">
    <div class="stat-card mb-0">
      <div class="stat-icon" style="background: var(--adm-blue-tint); color: var(--adm-blue-dark);"><i class="fas fa-user-edit"></i></div>
      <div>
        <div class="stat-label">Admin Posted</div>
        <div class="stat-value" style="color: var(--adm-blue-dark);"><?php echo $admin_count; ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="stat-card mb-0">
      <div class="stat-icon" style="background: var(--adm-saffron-tint); color: var(--adm-saffron-dark);"><i class="fas fa-robot"></i></div>
      <div>
        <div class="stat-label">Auto-Fetched (PIB)</div>
        <div class="stat-value" style="color: var(--adm-saffron-dark);"><?php echo $cron_count; ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="stat-card mb-0">
      <div class="stat-icon" style="background: #dcfce7; color: #16a34a;"><i class="fas fa-clock"></i></div>
      <div>
        <div class="stat-label">Last Auto-Fetch</div>
        <div class="stat-value" style="font-size: 1.05rem; color: #16a34a;"><?php echo $last_cron_run ? date('d M, h:i A', strtotime($last_cron_run)) : 'Never run yet'; ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Add / Edit Form -->
  <div class="col-lg-4">
    <div class="adm-card">
      <div class="adm-card-header" style="border-left: 4px solid var(--adm-saffron);">
        <h4 style="margin:0; font-weight:800; color: var(--adm-blue-dark); font-size: 1rem;">
          <i class="fas <?php echo $editing ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
          <?php echo $editing ? 'Edit Update' : 'Post New Update'; ?>
        </h4>
      </div>
      <div class="adm-card-body">
        <form method="POST" action="<?php echo BASE_URL; ?>/admin/updates.php">
          <input type="hidden" name="action" value="save_update">
          <input type="hidden" name="id" value="<?php echo $editing ? (int)$editing['id'] : 0; ?>">

          <div class="mb-3">
            <label class="upd-label">Title (English) <span style="color:#dc2626;">*</span></label>
            <input type="text" name="title_en" class="form-control" required
                   value="<?php echo htmlspecialchars($editing['title_en'] ?? ''); ?>"
                   placeholder="e.g. PM-KISAN 18th installment released">
          </div>

          <div class="mb-3">
            <label class="upd-label">Title (Hindi)</label>
            <input type="text" name="title_hi" class="form-control"
                   value="<?php echo htmlspecialchars($editing['title_hi'] ?? ''); ?>">
          </div>

          <div class="mb-3">
            <label class="upd-label">Link (optional)</label>
            <input type="url" name="source_url" class="form-control"
                   value="<?php echo htmlspecialchars($editing['source_url'] ?? ''); ?>"
                   placeholder="https://pib.gov.in/...">
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="upd-label">Category</label>
              <select name="category" class="form-control">
                <?php foreach (['general' => 'General', 'scheme' => 'Scheme', 'service' => 'Citizen Service', 'notice' => 'Notice', 'deadline' => 'Deadline'] as $val => $lbl): ?>
                  <option value="<?php echo $val; ?>" <?php echo (($editing['category'] ?? 'general') === $val) ? 'selected' : ''; ?>><?php echo $lbl; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6">
              <label class="upd-label">Date</label>
              <input type="date" name="published_at" class="form-control"
                     value="<?php echo htmlspecialchars($editing['published_at'] ?? date('Y-m-d')); ?>">
            </div>
          </div>

          <div class="mb-3">
            <label style="font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px;">
              <input type="checkbox" name="is_active" value="1" style="width:18px;height:18px;"
                     <?php echo (!$editing || $editing['is_active'] == 1) ? 'checked' : ''; ?>>
              Show on homepage
            </label>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-gov-saffron fw-bold flex-grow-1">
              <i class="fas fa-save"></i> <?php echo $editing ? 'Save Changes' : 'Publish Update'; ?>
            </button>
            <?php if ($editing): ?>
              <a href="<?php echo BASE_URL; ?>/admin/updates.php" class="btn btn-default">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <div class="adm-card">
      <div class="adm-card-header"><h4 style="margin:0; font-weight:800; font-size: 0.95rem;"><i class="fas fa-robot text-warning"></i> Automated PIB Fetcher</h4></div>
      <div class="adm-card-body" style="font-size: 0.84rem; color: #475569;">
        <p>A scheduled script (<code>scripts/fetch_pib_updates.php</code>) pulls the latest releases from the official <strong>Press Information Bureau (PIB)</strong> RSS feed once a day and adds them here automatically, tagged <span class="upd-source-badge upd-source-cron">PIB</span>.</p>
        <p class="mb-0">Run PIB fetch right now from the admin panel:</p>
        <form method="POST" action="<?php echo BASE_URL; ?>/admin/updates.php" style="margin-top: 10px;">
          <input type="hidden" name="action" value="sync_pib">
          <button type="submit" class="btn btn-warning btn-sm w-100" style="font-weight: 700;">
            <i class="fas fa-sync-alt me-1"></i> Sync Latest PIB Releases Now
          </button>
        </form>
        <small class="text-muted" style="display:block; margin-top:8px;">CLI command: <code>php scripts/fetch_pib_updates.php</code></small>
      </div>
    </div>
  </div>

  <!-- List -->
  <div class="col-lg-8">
    <div class="adm-card">
      <div class="adm-card-header">
        <h4 style="margin:0; font-weight:800; font-size: 1rem;"><i class="fas fa-list-alt text-primary"></i> All Updates (<?php echo count($updates); ?>)</h4>
      </div>
      <div class="adm-card-body" style="padding: 0;">
        <div class="table-responsive">
          <table class="table table-hover table-striped" style="font-size: 0.86rem; margin-bottom: 0;">
            <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
              <tr>
                <th>Title</th>
                <th style="width: 100px;">Source</th>
                <th style="width: 100px;">Date</th>
                <th style="width: 80px; text-align:center;">Status</th>
                <th style="width: 110px; text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($updates)): ?>
                <tr><td colspan="5" class="text-center py-4 text-muted">No updates yet. Post one on the left, or run the PIB fetcher.</td></tr>
              <?php else: ?>
                <?php foreach ($updates as $u): ?>
                  <tr>
                    <td>
                      <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($u['title_en']); ?></div>
                      <?php if (!empty($u['source_url'])): ?>
                        <a href="<?php echo htmlspecialchars($u['source_url']); ?>" target="_blank" style="font-size: 0.74rem; color: #64748b;">
                          <i class="fas fa-link"></i> <?php echo htmlspecialchars(parse_url($u['source_url'], PHP_URL_HOST) ?? ''); ?>
                        </a>
                      <?php endif; ?>
                    </td>
                    <td>
                      <?php if ($u['created_via'] === 'cron'): ?>
                        <span class="upd-source-badge upd-source-cron"><i class="fas fa-robot"></i> <?php echo htmlspecialchars($u['source_name'] ?: 'PIB'); ?></span>
                      <?php else: ?>
                        <span class="upd-source-badge upd-source-admin"><i class="fas fa-user"></i> Admin</span>
                      <?php endif; ?>
                    </td>
                    <td><?php echo date('d M Y', strtotime($u['published_at'])); ?></td>
                    <td style="text-align:center;">
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="quick_action" value="toggle_status">
                        <input type="hidden" name="update_id" value="<?php echo $u['id']; ?>">
                        <button type="submit" class="btn btn-xs <?php echo $u['is_active'] ? 'btn-success' : 'btn-default'; ?>" style="border-radius:12px;">
                          <?php echo $u['is_active'] ? 'Live' : 'Hidden'; ?>
                        </button>
                      </form>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                      <?php if ($u['created_via'] === 'admin'): ?>
                        <a href="?edit=<?php echo $u['id']; ?>" class="btn btn-xs btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                      <?php endif; ?>
                      <form method="POST" class="d-inline" onsubmit="return confirm('Delete this update?');">
                        <input type="hidden" name="quick_action" value="delete">
                        <input type="hidden" name="update_id" value="<?php echo $u['id']; ?>">
                        <button type="submit" class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
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
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
