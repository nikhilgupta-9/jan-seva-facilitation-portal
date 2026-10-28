<?php
// admin/banners.php - Manage Homepage Carousel Hero Banners
require_once __DIR__ . '/auth.php';

$page_title = 'Homepage Hero Banners';

// Image processing helper: resize/crop to standard 1920x913 px (2.10:1 ratio) using GD
function process_banner_upload($file_tmp, $dest_path) {
    list($orig_w, $orig_h, $type) = getimagesize($file_tmp);
    if (!$orig_w || !$orig_h) return false;

    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($file_tmp);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($file_tmp);
            break;
        case IMAGETYPE_WEBP:
            $src = imagecreatefromwebp($file_tmp);
            break;
        default:
            return false;
    }

    $target_w = 1920;
    $target_h = 913;

    // Calculate crop coordinates for aspect ratio 1920:913 (2.10:1)
    $target_ratio = $target_w / $target_h;
    $orig_ratio = $orig_w / $orig_h;

    if ($orig_ratio > $target_ratio) {
        // Wider than target: crop left/right
        $crop_h = $orig_h;
        $crop_w = (int)($orig_h * $target_ratio);
        $crop_x = (int)(($orig_w - $crop_w) / 2);
        $crop_y = 0;
    } else {
        // Taller than target: crop top/bottom
        $crop_w = $orig_w;
        $crop_h = (int)($orig_w / $target_ratio);
        $crop_x = 0;
        $crop_y = (int)(($orig_h - $crop_h) / 2);
    }

    $dst = imagecreatetruecolor($target_w, $target_h);
    imagecopyresampled($dst, $src, 0, 0, $crop_x, $crop_y, $target_w, $target_h, $crop_w, $crop_h);

    // Save as JPEG 90% quality
    imagejpeg($dst, $dest_path, 90);

    imagedestroy($src);
    imagedestroy($dst);
    return true;
}

// 1. Handle POST Actions (Add, Edit, Toggle, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = trim($_POST['action'] ?? '');

    // Add or Edit Banner
    if ($action === 'save_banner') {
        $id = (int) ($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? '');
        $link_url = trim($_POST['link_url'] ?? '');
        $target = ($_POST['target'] ?? '_self') === '_blank' ? '_blank' : '_self';
        $badge_text = trim($_POST['badge_text'] ?? '');
        $button_text = trim($_POST['button_text'] ?? 'Apply Now');
        $sort_order = (int) ($_POST['sort_order'] ?? 0);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $image_url = trim($_POST['image_url_text'] ?? '');

        // Validation
        if (empty($title)) {
            set_flash('error', 'Banner title cannot be empty.');
            header('Location: ' . BASE_URL . '/admin/banners.php');
            exit;
        }

        // Handle Image Upload
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['banner_image']['tmp_name'];
            $file_ext = strtolower(pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed)) {
                $upload_dir = dirname(__DIR__) . '/image/banners/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $safe_name = preg_replace('/[^a-z0-9_-]/', '', strtolower(substr($title, 0, 30)));
                $dest_filename = 'banner_' . ($safe_name ?: 'custom') . '_' . time() . '.jpg';
                $dest_full = $upload_dir . $dest_filename;

                if (process_banner_upload($file_tmp, $dest_full)) {
                    $image_url = 'image/banners/' . $dest_filename;
                } else {
                    // Fallback move
                    if (move_uploaded_file($file_tmp, $dest_full)) {
                        $image_url = 'image/banners/' . $dest_filename;
                    }
                }
            }
        }

        if (empty($image_url) && $id === 0) {
            set_flash('error', 'Please upload a banner image or provide an image path.');
            header('Location: ' . BASE_URL . '/admin/banners.php');
            exit;
        }

        if ($id > 0) {
            // Update
            $sql = "UPDATE banners SET 
                title = ?, subtitle = ?, link_url = ?, target = ?, badge_text = ?, button_text = ?, sort_order = ?, is_active = ?" .
                (!empty($image_url) ? ", image_url = ?" : "") . 
                " WHERE id = ?";
            $params = [$title, $subtitle, $link_url, $target, $badge_text, $button_text, $sort_order, $is_active];
            if (!empty($image_url)) {
                $params[] = $image_url;
            }
            $params[] = $id;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            set_flash('success', "Banner #{$id} updated successfully.");
        } else {
            // Insert
            $stmt = $pdo->prepare("INSERT INTO banners 
                (title, subtitle, image_url, link_url, target, badge_text, button_text, sort_order, is_active, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$title, $subtitle, $image_url, $link_url, $target, $badge_text, $button_text, $sort_order, $is_active]);
            $new_id = $pdo->lastInsertId();
            set_flash('success', "New banner #{$new_id} added successfully.");
        }

        header('Location: ' . BASE_URL . '/admin/banners.php');
        exit;
    }

    // Toggle Active Status
    if ($action === 'toggle_status') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("UPDATE banners SET is_active = (CASE WHEN is_active = 1 THEN 0 ELSE 1 END) WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', "Banner status toggled.");
        header('Location: ' . BASE_URL . '/admin/banners.php');
        exit;
    }

    // Delete Banner
    if ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM banners WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', "Banner #{$id} deleted successfully.");
        header('Location: ' . BASE_URL . '/admin/banners.php');
        exit;
    }
}

// Fetch all banners ordered by sort_order
$stmt = $pdo->query("SELECT * FROM banners ORDER BY sort_order ASC, id ASC");
$banners = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If editing a specific banner
$edit_banner = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    foreach ($banners as $b) {
        if ($b['id'] == $edit_id) {
            $edit_banner = $b;
            break;
        }
    }
}

include __DIR__ . '/header.php';
?>

<!-- Title Row -->
<div class="row" style="margin-bottom: 24px;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-navy); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-images text-primary"></i> Homepage Hero Carousel Banners
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.9rem;">
      Standardize banner sizes (1920x913 px, 2.10:1 ratio), manage carousel order, update slide titles, and set redirect links.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <a href="<?php echo BASE_URL; ?>/index.php" target="_blank" class="btn btn-default" style="font-weight: 600;">
      <i class="fas fa-external-link-alt"></i> View Live Homepage
    </a>
  </div>
</div>

<div class="row">
  
  <!-- Left Column: Add / Edit Banner Form -->
  <div class="col-md-5 col-xs-12">
    <div class="adm-card" style="margin-bottom: 25px;">
      <div class="adm-card-header">
        <h4>
          <i class="fas fa-<?php echo $edit_banner ? 'edit text-warning' : 'plus-circle text-success'; ?>"></i>
          <?php echo $edit_banner ? 'Edit Banner #' . $edit_banner['id'] : 'Add New Hero Banner'; ?>
        </h4>
        <?php if ($edit_banner): ?>
          <a href="banners.php" class="btn btn-xs btn-default">Cancel Edit</a>
        <?php endif; ?>
      </div>
      <div class="adm-card-body">
        <form action="banners.php" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="action" value="save_banner">
          <input type="hidden" name="id" value="<?php echo $edit_banner['id'] ?? 0; ?>">

          <!-- Title -->
          <div class="form-group">
            <label class="control-label" style="font-weight: 700;">
              Banner Title / Headline <span class="text-danger">*</span>
            </label>
            <input type="text" name="title" class="form-control" required
                   placeholder="e.g. Ayushman Bharat Health Account (ABHA)"
                   value="<?php echo htmlspecialchars($edit_banner['title'] ?? ''); ?>">
            <small class="text-muted">Short primary headline for the slide or alt text.</small>
          </div>

          <!-- Subtitle -->
          <div class="form-group">
            <label class="control-label" style="font-weight: 700;">Subtitle / Description</label>
            <input type="text" name="subtitle" class="form-control"
                   placeholder="e.g. 14-digit Digital Health Identification & Instant PVC Pass"
                   value="<?php echo htmlspecialchars($edit_banner['subtitle'] ?? ''); ?>">
          </div>

          <!-- Redirect Link & Target -->
          <div class="row">
            <div class="col-xs-8">
              <div class="form-group">
                <label class="control-label" style="font-weight: 700;">
                  Redirect Link URL <span class="text-danger">*</span>
                </label>
                <input type="text" name="link_url" class="form-control" required
                       placeholder="e.g. service/abha-card or all-scheme.php"
                       value="<?php echo htmlspecialchars($edit_banner['link_url'] ?? ''); ?>">
                <small class="text-muted">Relative URL (e.g. <code>service/abha-card</code>) or full <code>https://...</code></small>
              </div>
            </div>
            <div class="col-xs-4">
              <div class="form-group">
                <label class="control-label" style="font-weight: 700;">Target</label>
                <select name="target" class="form-control">
                  <option value="_self" <?php echo ($edit_banner['target'] ?? '') === '_self' ? 'selected' : ''; ?>>Same Tab</option>
                  <option value="_blank" <?php echo ($edit_banner['target'] ?? '') === '_blank' ? 'selected' : ''; ?>>New Tab ↗</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Badge Text & Button Text -->
          <div class="row">
            <div class="col-xs-6">
              <div class="form-group">
                <label class="control-label" style="font-weight: 700;">Badge Label</label>
                <input type="text" name="badge_text" class="form-control"
                       placeholder="e.g. HEALTHCARE"
                       value="<?php echo htmlspecialchars($edit_banner['badge_text'] ?? ''); ?>">
              </div>
            </div>
            <div class="col-xs-6">
              <div class="form-group">
                <label class="control-label" style="font-weight: 700;">CTA Button Text</label>
                <input type="text" name="button_text" class="form-control"
                       placeholder="e.g. Apply Now"
                       value="<?php echo htmlspecialchars($edit_banner['button_text'] ?? 'Apply Now'); ?>">
              </div>
            </div>
          </div>

          <!-- Image Upload / Path -->
          <div class="form-group">
            <label class="control-label" style="font-weight: 700;">
              Banner Image (Standard Size: 1920 &times; 600 px)
            </label>
            <input type="file" name="banner_image" class="form-control" accept="image/*">
            <small class="text-muted d-block" style="margin-top: 4px;">
              Uploaded images are automatically scaled to standard 1920 &times; 600 widescreen ratio.
            </small>

            <?php if (!empty($edit_banner['image_url'])): ?>
              <div style="margin-top: 8px;">
                <label style="font-size: 0.78rem; color: #64748b;">Current Image Path:</label>
                <input type="text" name="image_url_text" class="form-control input-sm" 
                       value="<?php echo htmlspecialchars($edit_banner['image_url']); ?>">
                <div style="margin-top: 6px; border: 1px solid #cbd5e1; border-radius: 6px; overflow: hidden; max-height: 90px; background: #0f172a;">
                  <img src="<?php echo BASE_URL . '/' . htmlspecialchars($edit_banner['image_url']); ?>" style="width: 100%; height: 90px; object-fit: cover;">
                </div>
              </div>
            <?php else: ?>
              <input type="text" name="image_url_text" class="form-control input-sm" style="margin-top: 6px;"
                     placeholder="Or enter existing path, e.g. image/banners/banner-abha-card.jpg">
            <?php endif; ?>
          </div>

          <!-- Sort Order & Active -->
          <div class="row align-items-center" style="margin-top: 10px;">
            <div class="col-xs-6">
              <div class="form-group">
                <label class="control-label" style="font-weight: 700;">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" min="0" step="1"
                       value="<?php echo htmlspecialchars($edit_banner['sort_order'] ?? 1); ?>">
              </div>
            </div>
            <div class="col-xs-6">
              <div class="checkbox" style="margin-top: 26px;">
                <label style="font-weight: 700; color: #0f172a;">
                  <input type="checkbox" name="is_active" value="1" <?php echo (!isset($edit_banner) || $edit_banner['is_active']) ? 'checked' : ''; ?>>
                  Active in Carousel
                </label>
              </div>
            </div>
          </div>

          <div style="margin-top: 15px;">
            <button type="submit" class="btn btn-primary btn-block" style="font-weight: 700; padding: 10px;">
              <i class="fas fa-save me-1"></i> <?php echo $edit_banner ? 'Save Changes' : 'Create Banner Slide'; ?>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Right Column: Banners List & Reorder -->
  <div class="col-md-7 col-xs-12">
    <div class="adm-card">
      <div class="adm-card-header">
        <h4><i class="fas fa-sliders-h text-primary"></i> Active Carousel Slides (<?php echo count($banners); ?>)</h4>
        <span class="badge" style="background: #10b981; color: #fff;">
          Standard Size: 1920 &times; 600 px
        </span>
      </div>
      <div class="adm-card-body" style="padding: 0;">
        <?php if (!empty($banners)): ?>
          <div class="table-responsive">
            <table class="table table-hover table-striped" style="font-size: 0.88rem; margin-bottom: 0;">
              <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <tr>
                  <th style="width: 50px;">Order</th>
                  <th style="width: 170px;">Banner Preview</th>
                  <th>Title &amp; Destination Link</th>
                  <th style="width: 80px;">Status</th>
                  <th style="width: 120px; text-align: right;">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($banners as $b): 
                    $isActive = (bool) $b['is_active'];
                    $imgSrc = BASE_URL . '/' . htmlspecialchars($b['image_url']);
                ?>
                  <tr style="<?php echo !$isActive ? 'opacity: 0.6; background: #f8fafc;' : ''; ?>">
                    <td style="vertical-align: middle; text-align: center;">
                      <span class="badge" style="background: #3b82f6; font-size: 0.85rem; padding: 4px 8px;">
                        #<?php echo $b['sort_order']; ?>
                      </span>
                    </td>

                    <td style="vertical-align: middle;">
                      <div style="width: 160px; height: 50px; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; background: #0f172a; position: relative;">
                        <img src="<?php echo $imgSrc; ?>" alt="<?php echo htmlspecialchars($b['title']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php if (!empty($b['badge_text'])): ?>
                          <span style="position: absolute; bottom: 2px; left: 2px; background: rgba(0,0,0,0.7); color: #fff; font-size: 0.6rem; padding: 1px 4px; border-radius: 3px;">
                            <?php echo htmlspecialchars($b['badge_text']); ?>
                          </span>
                        <?php endif; ?>
                      </div>
                    </td>

                    <td style="vertical-align: middle;">
                      <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                        <?php echo htmlspecialchars($b['title']); ?>
                      </div>
                      <?php if (!empty($b['subtitle'])): ?>
                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                          <?php echo htmlspecialchars(mb_substr($b['subtitle'], 0, 75)); ?>...
                        </div>
                      <?php endif; ?>
                      <div style="margin-top: 4px;">
                        <span class="text-muted" style="font-size: 0.75rem;">Redirects to:</span>
                        <a href="<?php echo (strpos($b['link_url'], 'http') === 0 ? '' : BASE_URL . '/') . htmlspecialchars($b['link_url']); ?>" 
                           target="_blank" style="font-size: 0.78rem; color: #2563eb; font-weight: 600; text-decoration: underline;">
                          <?php echo htmlspecialchars($b['link_url']); ?> ↗
                        </a>
                      </div>
                    </td>

                    <td style="vertical-align: middle;">
                      <form action="banners.php" method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                        <button type="submit" class="btn btn-xs <?php echo $isActive ? 'btn-success' : 'btn-default'; ?>" title="Click to toggle status">
                          <i class="fas fa-<?php echo $isActive ? 'check-circle' : 'times-circle'; ?>"></i>
                          <?php echo $isActive ? 'Active' : 'Hidden'; ?>
                        </button>
                      </form>
                    </td>

                    <td style="vertical-align: middle; text-align: right;">
                      <div class="btn-group" role="group">
                        <a href="banners.php?edit=<?php echo $b['id']; ?>" class="btn btn-default btn-xs" title="Edit Banner">
                          <i class="fas fa-pencil-alt"></i>
                        </a>
                        <form action="banners.php" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this banner?');">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                          <button type="submit" class="btn btn-danger btn-xs" title="Delete Banner">
                            <i class="fas fa-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div style="padding: 40px; text-align: center; color: #64748b;">
            <i class="fas fa-images" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 10px;"></i>
            <h4>No banners configured</h4>
            <p>Use the form on the left to create your first standard 1920x913 hero banner.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<?php include __DIR__ . '/footer.php'; ?>
