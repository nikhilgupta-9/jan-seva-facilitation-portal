<?php
// admin/service-edit.php - Create or Edit Citizen Service
require_once __DIR__ . '/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = ($id > 0);
$page_title = $is_edit ? 'Edit Citizen Service' : 'Add New Citizen Service';

$service = [
    'slug' => '',
    'service_name' => '',
    'service_name_hi' => '',
    'service_type' => 'api',
    'category' => 'Identity',
    'department_en' => '',
    'department_hi' => '',
    'fee' => 50.00,
    'turnaround_time' => 'Instant Digital Pass (2-5 Mins)',
    'icon' => 'fas fa-id-card',
    'image_url' => 'image/services/1.jpg',
    'badge_text' => 'Popular',
    'short_desc' => '',
    'full_desc' => '',
    'benefits' => [],
    'eligibility' => [],
    'documents' => [],
    'steps' => [],
    'faqs' => [],
    'official_url' => '',
    'meta_title' => '',
    'meta_desc' => '',
    'meta_keywords' => '',
    'sort_order' => 10,
    'is_active' => 1
];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM `services` WHERE `id` = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        set_flash('error', "Citizen service not found!");
        header('Location: ' . BASE_URL . '/admin/services.php');
        exit;
    }
    $service = array_merge($service, $row);

    // Decode JSON columns
    foreach (['benefits_json' => 'benefits', 'eligibility_json' => 'eligibility', 'documents_json' => 'documents', 'steps_json' => 'steps', 'faqs_json' => 'faqs'] as $col => $key) {
        if (!empty($service[$col])) {
            $decoded = json_decode($service[$col], true);
            $service[$key] = is_array($decoded) ? $decoded : [];
        }
    }
}

// Process Form Submit
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    $service_name = trim($_POST['service_name'] ?? '');
    $service_name_hi = trim($_POST['service_name_hi'] ?? '');
    $service_type = $_POST['service_type'] ?? 'api';
    $category = trim($_POST['category'] ?? 'Identity');
    $department_en = trim($_POST['department_en'] ?? '');
    $department_hi = trim($_POST['department_hi'] ?? '');
    $fee = (float) ($_POST['fee'] ?? 0);
    $turnaround_time = trim($_POST['turnaround_time'] ?? 'Instant Digital Pass (2-5 Mins)');
    $icon = trim($_POST['icon'] ?? 'fas fa-id-card');
    $badge_text = trim($_POST['badge_text'] ?? 'Popular');
    $short_desc = trim($_POST['short_desc'] ?? '');
    $full_desc = trim($_POST['full_desc'] ?? '');
    $official_url = trim($_POST['official_url'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_desc = trim($_POST['meta_desc'] ?? '');
    $meta_keywords = trim($_POST['meta_keywords'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 10);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Convert Multiline text to JSON arrays
    $benefits = array_values(array_filter(array_map('trim', explode("\n", $_POST['benefits'] ?? ''))));
    $eligibility = array_values(array_filter(array_map('trim', explode("\n", $_POST['eligibility'] ?? ''))));
    $documents = array_values(array_filter(array_map('trim', explode("\n", $_POST['documents'] ?? ''))));

    // Steps (Title | Description)
    $steps_raw = explode("\n", $_POST['steps'] ?? '');
    $steps = [];
    foreach ($steps_raw as $s_line) {
        $s_line = trim($s_line);
        if (empty($s_line)) continue;
        $parts = explode("|", $s_line, 2);
        $steps[] = [
            'title' => trim($parts[0] ?? ''),
            'desc' => trim($parts[1] ?? '')
        ];
    }

    // FAQs (Question | Answer)
    $faqs_raw = explode("\n", $_POST['faqs'] ?? '');
    $faqs = [];
    foreach ($faqs_raw as $f_line) {
        $f_line = trim($f_line);
        if (empty($f_line)) continue;
        $parts = explode("|", $f_line, 2);
        $faqs[] = [
            'q' => trim($parts[0] ?? ''),
            'a' => trim($parts[1] ?? '')
        ];
    }

    // Image handling: File upload or text path
    $image_url = trim($_POST['image_url_text'] ?? $service['image_url']);
    if (isset($_FILES['service_image']) && $_FILES['service_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['service_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['service_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed)) {
            $dest_name = preg_replace('/[^a-z0-9_-]/', '', $slug) . '_' . time() . '.' . $file_ext;
            $upload_dir = dirname(__DIR__) . '/image/services/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($file_tmp, $upload_dir . $dest_name)) {
                $image_url = 'image/services/' . $dest_name;
            }
        }
    }

    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $service_name), '-'));
    }

    if ($is_edit) {
        $updateSql = "
        UPDATE `services` SET
            `slug` = :slug,
            `service_name` = :service_name,
            `service_name_hi` = :service_name_hi,
            `service_type` = :service_type,
            `category` = :category,
            `department_en` = :department_en,
            `department_hi` = :department_hi,
            `fee` = :fee,
            `turnaround_time` = :turnaround_time,
            `icon` = :icon,
            `image_url` = :image_url,
            `badge_text` = :badge_text,
            `short_desc` = :short_desc,
            `full_desc` = :full_desc,
            `benefits_json` = :benefits_json,
            `eligibility_json` = :eligibility_json,
            `documents_json` = :documents_json,
            `steps_json` = :steps_json,
            `faqs_json` = :faqs_json,
            `official_url` = :official_url,
            `meta_title` = :meta_title,
            `meta_desc` = :meta_desc,
            `meta_keywords` = :meta_keywords,
            `sort_order` = :sort_order,
            `is_active` = :is_active
        WHERE `id` = :id
        ";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([
            ':slug' => $slug,
            ':service_name' => $service_name,
            ':service_name_hi' => $service_name_hi,
            ':service_type' => $service_type,
            ':category' => $category,
            ':department_en' => $department_en,
            ':department_hi' => $department_hi,
            ':fee' => $fee,
            ':turnaround_time' => $turnaround_time,
            ':icon' => $icon,
            ':image_url' => $image_url,
            ':badge_text' => $badge_text,
            ':short_desc' => $short_desc,
            ':full_desc' => $full_desc,
            ':benefits_json' => json_encode($benefits, JSON_UNESCAPED_UNICODE),
            ':eligibility_json' => json_encode($eligibility, JSON_UNESCAPED_UNICODE),
            ':documents_json' => json_encode($documents, JSON_UNESCAPED_UNICODE),
            ':steps_json' => json_encode($steps, JSON_UNESCAPED_UNICODE),
            ':faqs_json' => json_encode($faqs, JSON_UNESCAPED_UNICODE),
            ':official_url' => $official_url,
            ':meta_title' => $meta_title,
            ':meta_desc' => $meta_desc,
            ':meta_keywords' => $meta_keywords,
            ':sort_order' => $sort_order,
            ':is_active' => $is_active,
            ':id' => $id
        ]);
        set_flash('success', "Service updated successfully!");
    } else {
        $insertSql = "
        INSERT INTO `services` (
            `slug`, `service_name`, `service_name_hi`, `service_type`, `category`,
            `department_en`, `department_hi`, `fee`, `turnaround_time`, `icon`,
            `image_url`, `badge_text`, `short_desc`, `full_desc`, `benefits_json`,
            `eligibility_json`, `documents_json`, `steps_json`, `faqs_json`,
            `official_url`, `meta_title`, `meta_desc`, `meta_keywords`, `sort_order`, `is_active`
        ) VALUES (
            :slug, :service_name, :service_name_hi, :service_type, :category,
            :department_en, :department_hi, :fee, :turnaround_time, :icon,
            :image_url, :badge_text, :short_desc, :full_desc, :benefits_json,
            :eligibility_json, :documents_json, :steps_json, :faqs_json,
            :official_url, :meta_title, :meta_desc, :meta_keywords, :sort_order, :is_active
        )
        ";
        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([
            ':slug' => $slug,
            ':service_name' => $service_name,
            ':service_name_hi' => $service_name_hi,
            ':service_type' => $service_type,
            ':category' => $category,
            ':department_en' => $department_en,
            ':department_hi' => $department_hi,
            ':fee' => $fee,
            ':turnaround_time' => $turnaround_time,
            ':icon' => $icon,
            ':image_url' => $image_url,
            ':badge_text' => $badge_text,
            ':short_desc' => $short_desc,
            ':full_desc' => $full_desc,
            ':benefits_json' => json_encode($benefits, JSON_UNESCAPED_UNICODE),
            ':eligibility_json' => json_encode($eligibility, JSON_UNESCAPED_UNICODE),
            ':documents_json' => json_encode($documents, JSON_UNESCAPED_UNICODE),
            ':steps_json' => json_encode($steps, JSON_UNESCAPED_UNICODE),
            ':faqs_json' => json_encode($faqs, JSON_UNESCAPED_UNICODE),
            ':official_url' => $official_url,
            ':meta_title' => $meta_title,
            ':meta_desc' => $meta_desc,
            ':meta_keywords' => $meta_keywords,
            ':sort_order' => $sort_order,
            ':is_active' => $is_active
        ]);
        set_flash('success', "New citizen service added successfully!");
    }

    header('Location: ' . BASE_URL . '/admin/services.php');
    exit;
}

include __DIR__ . '/header.php';
?>

<!-- Title & Back Bar -->
<div class="row" style="margin-bottom: 24px; align-items: center;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-text); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-edit" style="color: var(--adm-saffron); margin-right: 8px;"></i>
      <?php echo $page_title; ?>
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.92rem; margin-bottom: 0;">
      Configure bilingual titles, fee models, turnaround schedules, document checklists, and SEO metadata.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <a href="<?php echo BASE_URL; ?>/admin/services.php" class="btn btn-default" style="font-weight: 600;">
      <i class="fas fa-arrow-left me-1"></i> Back to Catalog
    </a>
  </div>
</div>

<form method="POST" enctype="multipart/form-data" action="<?php echo BASE_URL; ?>/admin/service-edit.php<?php echo $is_edit ? '?id=' . $id : ''; ?>">

  <!-- Section 1: Basic Identity & Classification -->
  <div class="adm-card" style="margin-bottom: 24px; padding: 24px;">
    <h4 style="font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1.5px solid #f1f5f9;">
      <i class="fas fa-id-card text-primary me-2"></i> 1. Service Identity &amp; Classification
    </h4>

    <div class="row g-3">
      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Service Name (English) <span class="text-danger">*</span></label>
        <input type="text" name="service_name" value="<?php echo htmlspecialchars($service['service_name']); ?>" class="form-control" placeholder="e.g. ABHA Health ID Card" required>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Service Name (Hindi / हिन्दी)</label>
        <input type="text" name="service_name_hi" value="<?php echo htmlspecialchars($service['service_name_hi']); ?>" class="form-control" placeholder="e.g. आभा हेल्थ आईडी कार्ड">
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">URL Slug (SEO Path) <span class="text-danger">*</span></label>
        <input type="text" name="slug" value="<?php echo htmlspecialchars($service['slug']); ?>" class="form-control" placeholder="e.g. abha-card" required>
        <small class="text-muted">Live URL: <code>/service/<strong>slug</strong></code></small>
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Service Type <span class="text-danger">*</span></label>
        <select name="service_type" class="form-control" required>
          <option value="api" <?php echo ($service['service_type'] === 'api') ? 'selected' : ''; ?>>Instant Direct API (₹50 Digital PVC Generation)</option>
          <option value="redirect" <?php echo ($service['service_type'] === 'redirect') ? 'selected' : ''; ?>>Assisted Department Guide (Free Guidance + Deep Link)</option>
        </select>
      </div>

      <div class="col-md-4 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Category <span class="text-danger">*</span></label>
        <input type="text" name="category" value="<?php echo htmlspecialchars($service['category']); ?>" class="form-control" placeholder="e.g. Health, Education, Identity, Welfare, Transport" required>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Department / Ministry (English)</label>
        <input type="text" name="department_en" value="<?php echo htmlspecialchars($service['department_en'] ?? ''); ?>" class="form-control" placeholder="e.g. National Health Authority (NHA)">
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Department / Ministry (Hindi)</label>
        <input type="text" name="department_hi" value="<?php echo htmlspecialchars($service['department_hi'] ?? ''); ?>" class="form-control" placeholder="e.g. राष्ट्रीय स्वास्थ्य प्राधिकरण">
      </div>
    </div>
  </div>

  <!-- Section 2: Fee, Badges, Turnaround & Imagery -->
  <div class="adm-card" style="margin-bottom: 24px; padding: 24px;">
    <h4 style="font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1.5px solid #f1f5f9;">
      <i class="fas fa-rupee-sign text-success me-2"></i> 2. Fee, Badges &amp; Imagery
    </h4>

    <div class="row g-3">
      <div class="col-md-3 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Facilitation Fee (₹ INR) <span class="text-danger">*</span></label>
        <div class="input-group">
          <span class="input-group-addon">₹</span>
          <input type="number" step="1.00" name="fee" value="<?php echo (float)$service['fee']; ?>" class="form-control" required>
        </div>
        <small class="text-muted">Enter 0 for free assisted services.</small>
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Turnaround Schedule</label>
        <input type="text" name="turnaround_time" value="<?php echo htmlspecialchars($service['turnaround_time'] ?? 'Instant (2-5 Mins)'); ?>" class="form-control" placeholder="e.g. Instant (2-5 Mins)">
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Badge Highlight Text</label>
        <input type="text" name="badge_text" value="<?php echo htmlspecialchars($service['badge_text'] ?? 'Instant PDF'); ?>" class="form-control" placeholder="e.g. Instant Digital PVC">
      </div>

      <div class="col-md-3 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">FontAwesome Icon Class</label>
        <input type="text" name="icon" value="<?php echo htmlspecialchars($service['icon'] ?? 'fas fa-id-card'); ?>" class="form-control" placeholder="e.g. fas fa-heartbeat">
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Upload Service Photo</label>
        <input type="file" name="service_image" class="form-control" accept="image/*">
        <small class="text-muted">Upload high-res JPG, PNG, or WebP photo.</small>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Existing Photo File Path</label>
        <input type="text" name="image_url_text" value="<?php echo htmlspecialchars($service['image_url'] ?? ''); ?>" class="form-control" placeholder="image/services/abha-card.jpg">
      </div>
    </div>
  </div>

  <!-- Section 3: Descriptions & Content -->
  <div class="adm-card" style="margin-bottom: 24px; padding: 24px;">
    <h4 style="font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1.5px solid #f1f5f9;">
      <i class="fas fa-align-left text-warning me-2"></i> 3. Descriptions &amp; Portal Links
    </h4>

    <div class="row g-3">
      <div class="col-12 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Short Description (1-2 sentences for card grid) <span class="text-danger">*</span></label>
        <textarea name="short_desc" rows="2" class="form-control" required><?php echo htmlspecialchars($service['short_desc']); ?></textarea>
      </div>

      <div class="col-12 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Full Overview / Purpose (for detail page) <span class="text-danger">*</span></label>
        <textarea name="full_desc" rows="4" class="form-control" required><?php echo htmlspecialchars($service['full_desc']); ?></textarea>
      </div>

      <div class="col-12 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Official Government Portal URL <span class="text-danger">*</span></label>
        <input type="url" name="official_url" value="<?php echo htmlspecialchars($service['official_url']); ?>" class="form-control" placeholder="https://official.gov.in" required>
      </div>
    </div>
  </div>

  <!-- Section 4: Benefits, Eligibility, Documents, Steps & FAQs -->
  <div class="adm-card" style="margin-bottom: 24px; padding: 24px;">
    <h4 style="font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1.5px solid #f1f5f9;">
      <i class="fas fa-list-check text-info me-2"></i> 4. Detail Page Content (Benefits, Eligibility, Docs, Steps &amp; FAQs)
    </h4>

    <div class="row g-3">
      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Key Benefits (One benefit per line)</label>
        <textarea name="benefits" rows="5" class="form-control"><?php 
          if (!empty($service['benefits']) && is_array($service['benefits'])) {
            echo htmlspecialchars(implode("\n", $service['benefits']));
          }
        ?></textarea>
        <small class="text-muted">Displays with green checkmarks on detail page.</small>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Eligibility Criteria (One rule per line)</label>
        <textarea name="eligibility" rows="5" class="form-control"><?php 
          if (!empty($service['eligibility']) && is_array($service['eligibility'])) {
            echo htmlspecialchars(implode("\n", $service['eligibility']));
          }
        ?></textarea>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Required Documents (One document per line)</label>
        <textarea name="documents" rows="5" class="form-control"><?php 
          if (!empty($service['documents']) && is_array($service['documents'])) {
            echo htmlspecialchars(implode("\n", $service['documents']));
          }
        ?></textarea>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Application Process Steps (Title | Description per line)</label>
        <textarea name="steps" rows="5" class="form-control"><?php 
          if (!empty($service['steps']) && is_array($service['steps'])) {
            $lines = [];
            foreach ($service['steps'] as $st) {
              if (is_array($st)) {
                $lines[] = ($st['title'] ?? '') . ' | ' . ($st['desc'] ?? '');
              } else {
                $lines[] = $st;
              }
            }
            echo htmlspecialchars(implode("\n", $lines));
          }
        ?></textarea>
        <small class="text-muted">Format: Step Title | Detailed description</small>
      </div>

      <div class="col-12 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Frequently Asked Questions (Question | Answer per line)</label>
        <textarea name="faqs" rows="4" class="form-control"><?php 
          if (!empty($service['faqs']) && is_array($service['faqs'])) {
            $lines = [];
            foreach ($service['faqs'] as $fq) {
              if (is_array($fq)) {
                $lines[] = ($fq['q'] ?? '') . ' | ' . ($fq['a'] ?? '');
              }
            }
            echo htmlspecialchars(implode("\n", $lines));
          }
        ?></textarea>
        <small class="text-muted">Format: Question? | Answer text (automatically renders Accordion and Schema.org FAQPage JSON-LD)</small>
      </div>
    </div>
  </div>

  <!-- Section 5: SEO Metadata & Settings -->
  <div class="adm-card" style="margin-bottom: 24px; padding: 24px;">
    <h4 style="font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 1.5px solid #f1f5f9;">
      <i class="fas fa-globe text-primary me-2"></i> 5. Search Engine Optimization (SEO) &amp; Display Order
    </h4>

    <div class="row g-3">
      <div class="col-12 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Custom SEO Title Tag</label>
        <input type="text" name="meta_title" value="<?php echo htmlspecialchars($service['meta_title'] ?? ''); ?>" class="form-control" placeholder="e.g. ABHA Health ID Card Online Apply & Instant PVC Card Download">
        <small class="text-muted">Target 55–65 characters for top Google ranking.</small>
      </div>

      <div class="col-12 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Custom Meta Description</label>
        <textarea name="meta_desc" rows="2" class="form-control" placeholder="Concise summary for search engine snippets (140-160 characters)"><?php echo htmlspecialchars($service['meta_desc'] ?? ''); ?></textarea>
      </div>

      <div class="col-md-8 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Meta Keywords (comma separated)</label>
        <input type="text" name="meta_keywords" value="<?php echo htmlspecialchars($service['meta_keywords'] ?? ''); ?>" class="form-control" placeholder="abha card apply, download health id, pvc card print">
      </div>

      <div class="col-md-2 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Sort Order</label>
        <input type="number" name="sort_order" value="<?php echo (int) $service['sort_order']; ?>" class="form-control">
      </div>

      <div class="col-md-2 mb-3">
        <label class="form-label" style="font-weight: 700; color: #334155;">Active Status</label>
        <div style="padding-top: 8px;">
          <label style="cursor: pointer; font-weight: 600; color: #16a34a;">
            <input type="checkbox" name="is_active" value="1" <?php echo ($service['is_active'] == 1) ? 'checked' : ''; ?> style="width: 18px; height: 18px; vertical-align: middle; margin-right: 6px;">
            Visible to Public
          </label>
        </div>
      </div>
    </div>
  </div>

  <!-- Submit Buttons -->
  <div style="margin-bottom: 40px; display: flex; gap: 12px;">
    <button type="submit" class="btn btn-primary btn-lg" style="background: var(--adm-saffron); border-color: var(--adm-saffron); font-weight: 800; border-radius: 8px; padding: 12px 30px;">
      <i class="fas fa-save me-2"></i> Save Citizen Service
    </button>
    <a href="<?php echo BASE_URL; ?>/admin/services.php" class="btn btn-default btn-lg" style="font-weight: 600; border-radius: 8px;">
      Cancel
    </a>
  </div>

</form>

<?php include __DIR__ . '/footer.php'; ?>
