<?php
// admin/scheme-edit.php - Create or Edit Government Scheme
require_once __DIR__ . '/auth.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$is_edit = ($id > 0);
$page_title = $is_edit ? 'Edit Government Scheme' : 'Add New Government Scheme';

$scheme = [
    'slug' => '',
    'title_en' => '',
    'title_hi' => '',
    'tagline_en' => '',
    'tagline_hi' => '',
    'icon' => 'fa-landmark',
    'image' => 'image/schemes/default-scheme-banner.jpg',
    'category' => 'agriculture',
    'category_name_en' => 'Agriculture & Farmers Welfare',
    'category_name_hi' => 'कृषि एवं किसान कल्याण',
    'funding_type' => 'central_sector',
    'funding_type_label_en' => 'Central Sector Scheme (100% Central Funded)',
    'funding_type_label_hi' => 'केंद्रीय क्षेत्र योजना (100% केंद्र सरकार द्वारा वित्तपोषित)',
    'ministry_en' => '',
    'ministry_hi' => '',
    'launch_year' => date('Y'),
    'budget' => '',
    'applicable_scope' => 'all_india',
    'applicable_states' => ['All States & UTs'],
    'overview_en' => '',
    'overview_hi' => '',
    'benefits_en' => [],
    'benefits_hi' => [],
    'eligibility_en' => [],
    'eligibility_hi' => [],
    'documents_en' => [],
    'documents_hi' => [],
    'how_to_apply_en' => [],
    'how_to_apply_hi' => [],
    'official_url' => '',
    'helpline' => '',
    'audio_text_en' => '',
    'audio_text_hi' => '',
    'eligibility_checker' => [
        'q1_en' => '', 'q1_hi' => '',
        'q2_en' => '', 'q2_hi' => '',
        'expected' => ['q1' => 'yes', 'q2' => 'yes'],
        'eligible_msg_en' => '', 'eligible_msg_hi' => '',
        'ineligible_msg_en' => '', 'ineligible_msg_hi' => ''
    ],
    'min_age' => 0,
    'max_age' => 120,
    'max_income' => 99999999,
    'target_occupations' => 'all',
    'target_gender' => 'all',
    'is_active' => 1,
    'sort_order' => 10
];

if ($is_edit) {
    $stmt = $pdo->prepare("SELECT * FROM `schemes` WHERE `id` = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        set_flash('error', "Scheme not found!");
        header('Location: ' . BASE_URL . '/admin/schemes.php');
        exit;
    }
    $scheme = array_merge($scheme, $row);
    // Decode JSON fields
    foreach (['applicable_states', 'benefits_en', 'benefits_hi', 'eligibility_en', 'eligibility_hi', 'documents_en', 'documents_hi', 'how_to_apply_en', 'how_to_apply_hi', 'eligibility_checker'] as $field) {
        if (is_string($scheme[$field])) {
            $scheme[$field] = json_decode($scheme[$field], true) ?? [];
        }
    }
}

// Process Form Submit
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $slug = trim($_POST['slug'] ?? '');
    $title_en = trim($_POST['title_en'] ?? '');
    $title_hi = trim($_POST['title_hi'] ?? '');
    $tagline_en = trim($_POST['tagline_en'] ?? '');
    $tagline_hi = trim($_POST['tagline_hi'] ?? '');
    $icon = trim($_POST['icon'] ?? 'fa-landmark');
    $category = trim($_POST['category'] ?? 'agriculture');
    $category_name_en = trim($_POST['category_name_en'] ?? '');
    $category_name_hi = trim($_POST['category_name_hi'] ?? '');
    $funding_type = $_POST['funding_type'] ?? 'central_sector';
    $funding_type_label_en = ($funding_type === 'central_sector') ? 'Central Sector Scheme (100% Central Funded)' : 'Centrally Sponsored Scheme (Shared 60:40 Centre & State)';
    $funding_type_label_hi = ($funding_type === 'central_sector') ? 'केंद्रीय क्षेत्र योजना (100% केंद्र सरकार द्वारा वित्तपोषित)' : 'केंद्र प्रायोजित योजना (60:40 केंद्र और राज्य सरकार द्वारा साझा)';
    $ministry_en = trim($_POST['ministry_en'] ?? '');
    $ministry_hi = trim($_POST['ministry_hi'] ?? '');
    $launch_year = trim($_POST['launch_year'] ?? '');
    $budget = trim($_POST['budget'] ?? '');
    $official_url = trim($_POST['official_url'] ?? '');
    $helpline = trim($_POST['helpline'] ?? '');
    $overview_en = trim($_POST['overview_en'] ?? '');
    $overview_hi = trim($_POST['overview_hi'] ?? '');
    $audio_text_en = trim($_POST['audio_text_en'] ?? '');
    $audio_text_hi = trim($_POST['audio_text_hi'] ?? '');

    $min_age = (int) ($_POST['min_age'] ?? 0);
    $max_age = (int) ($_POST['max_age'] ?? 120);
    $max_income = (int) ($_POST['max_income'] ?? 99999999);
    $target_occupations = trim($_POST['target_occupations'] ?? 'all');
    $target_gender = trim($_POST['target_gender'] ?? 'all');
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $sort_order = (int) ($_POST['sort_order'] ?? 0);

    // Multi-line to array conversions
    $benefits_en = array_filter(array_map('trim', explode("\n", $_POST['benefits_en'] ?? '')));
    $benefits_hi = array_filter(array_map('trim', explode("\n", $_POST['benefits_hi'] ?? '')));
    $eligibility_en = array_filter(array_map('trim', explode("\n", $_POST['eligibility_en'] ?? '')));
    $eligibility_hi = array_filter(array_map('trim', explode("\n", $_POST['eligibility_hi'] ?? '')));
    $documents_en = array_filter(array_map('trim', explode("\n", $_POST['documents_en'] ?? '')));
    $documents_hi = array_filter(array_map('trim', explode("\n", $_POST['documents_hi'] ?? '')));
    $how_to_apply_en = array_filter(array_map('trim', explode("\n", $_POST['how_to_apply_en'] ?? '')));
    $how_to_apply_hi = array_filter(array_map('trim', explode("\n", $_POST['how_to_apply_hi'] ?? '')));
    $applicable_states = array_filter(array_map('trim', explode(",", $_POST['applicable_states'] ?? 'All States & UTs')));

    // Eligibility Checker Structure
    $eligibility_checker = [
        'q1_en' => trim($_POST['q1_en'] ?? ''),
        'q1_hi' => trim($_POST['q1_hi'] ?? ''),
        'q2_en' => trim($_POST['q2_en'] ?? ''),
        'q2_hi' => trim($_POST['q2_hi'] ?? ''),
        'expected' => [
            'q1' => $_POST['expected_q1'] ?? 'yes',
            'q2' => $_POST['expected_q2'] ?? 'yes'
        ],
        'eligible_msg_en' => trim($_POST['eligible_msg_en'] ?? ''),
        'eligible_msg_hi' => trim($_POST['eligible_msg_hi'] ?? ''),
        'ineligible_msg_en' => trim($_POST['ineligible_msg_en'] ?? ''),
        'ineligible_msg_hi' => trim($_POST['ineligible_msg_hi'] ?? '')
    ];

    // Image handling: File upload or existing text input
    $image_path = trim($_POST['image_text'] ?? $scheme['image']);
    if (isset($_FILES['scheme_image']) && $_FILES['scheme_image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['scheme_image']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['scheme_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed)) {
            $dest_name = preg_replace('/[^a-z0-9_-]/', '', $slug) . '_' . time() . '.' . $file_ext;
            $upload_dir = dirname(__DIR__) . '/image/schemes/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($file_tmp, $upload_dir . $dest_name)) {
                $image_path = 'image/schemes/' . $dest_name;
            }
        }
    }

    if ($is_edit) {
        $updateSql = "
        UPDATE `schemes` SET
            `slug` = :slug, `title_en` = :title_en, `title_hi` = :title_hi,
            `tagline_en` = :tagline_en, `tagline_hi` = :tagline_hi, `icon` = :icon,
            `image` = :image, `category` = :category, `category_name_en` = :category_name_en,
            `category_name_hi` = :category_name_hi, `funding_type` = :funding_type,
            `funding_type_label_en` = :funding_type_label_en, `funding_type_label_hi` = :funding_type_label_hi,
            `ministry_en` = :ministry_en, `ministry_hi` = :ministry_hi, `launch_year` = :launch_year,
            `budget` = :budget, `applicable_states` = :applicable_states,
            `overview_en` = :overview_en, `overview_hi` = :overview_hi,
            `benefits_en` = :benefits_en, `benefits_hi` = :benefits_hi,
            `eligibility_en` = :eligibility_en, `eligibility_hi` = :eligibility_hi,
            `documents_en` = :documents_en, `documents_hi` = :documents_hi,
            `how_to_apply_en` = :how_to_apply_en, `how_to_apply_hi` = :how_to_apply_hi,
            `official_url` = :official_url, `helpline` = :helpline,
            `audio_text_en` = :audio_text_en, `audio_text_hi` = :audio_text_hi,
            `eligibility_checker` = :eligibility_checker, `min_age` = :min_age,
            `max_age` = :max_age, `max_income` = :max_income,
            `target_occupations` = :target_occupations, `target_gender` = :target_gender,
            `is_active` = :is_active, `sort_order` = :sort_order
        WHERE `id` = :id
        ";
        $stmt = $pdo->prepare($updateSql);
        $stmt->execute([
            ':slug' => $slug, ':title_en' => $title_en, ':title_hi' => $title_hi,
            ':tagline_en' => $tagline_en, ':tagline_hi' => $tagline_hi, ':icon' => $icon,
            ':image' => $image_path, ':category' => $category, ':category_name_en' => $category_name_en,
            ':category_name_hi' => $category_name_hi, ':funding_type' => $funding_type,
            ':funding_type_label_en' => $funding_type_label_en, ':funding_type_label_hi' => $funding_type_label_hi,
            ':ministry_en' => $ministry_en, ':ministry_hi' => $ministry_hi, ':launch_year' => $launch_year,
            ':budget' => $budget, ':applicable_states' => json_encode(array_values($applicable_states), JSON_UNESCAPED_UNICODE),
            ':overview_en' => $overview_en, ':overview_hi' => $overview_hi,
            ':benefits_en' => json_encode(array_values($benefits_en), JSON_UNESCAPED_UNICODE),
            ':benefits_hi' => json_encode(array_values($benefits_hi), JSON_UNESCAPED_UNICODE),
            ':eligibility_en' => json_encode(array_values($eligibility_en), JSON_UNESCAPED_UNICODE),
            ':eligibility_hi' => json_encode(array_values($eligibility_hi), JSON_UNESCAPED_UNICODE),
            ':documents_en' => json_encode(array_values($documents_en), JSON_UNESCAPED_UNICODE),
            ':documents_hi' => json_encode(array_values($documents_hi), JSON_UNESCAPED_UNICODE),
            ':how_to_apply_en' => json_encode(array_values($how_to_apply_en), JSON_UNESCAPED_UNICODE),
            ':how_to_apply_hi' => json_encode(array_values($how_to_apply_hi), JSON_UNESCAPED_UNICODE),
            ':official_url' => $official_url, ':helpline' => $helpline,
            ':audio_text_en' => $audio_text_en, ':audio_text_hi' => $audio_text_hi,
            ':eligibility_checker' => json_encode($eligibility_checker, JSON_UNESCAPED_UNICODE),
            ':min_age' => $min_age, ':max_age' => $max_age, ':max_income' => $max_income,
            ':target_occupations' => $target_occupations, ':target_gender' => $target_gender,
            ':is_active' => $is_active, ':sort_order' => $sort_order,
            ':id' => $id
        ]);
        set_flash('success', "Scheme '{$title_en}' updated successfully!");
    } else {
        $insertSql = "
        INSERT INTO `schemes` (
            `slug`, `title_en`, `title_hi`, `tagline_en`, `tagline_hi`,
            `icon`, `image`, `category`, `category_name_en`, `category_name_hi`,
            `funding_type`, `funding_type_label_en`, `funding_type_label_hi`,
            `ministry_en`, `ministry_hi`, `launch_year`, `budget`,
            `applicable_scope`, `applicable_states`, `overview_en`, `overview_hi`,
            `benefits_en`, `benefits_hi`, `eligibility_en`, `eligibility_hi`,
            `documents_en`, `documents_hi`, `how_to_apply_en`, `how_to_apply_hi`,
            `official_url`, `helpline`, `audio_text_en`, `audio_text_hi`,
            `eligibility_checker`, `min_age`, `max_age`, `max_income`,
            `target_occupations`, `target_gender`, `is_active`, `sort_order`
        ) VALUES (
            :slug, :title_en, :title_hi, :tagline_en, :tagline_hi,
            :icon, :image, :category, :category_name_en, :category_name_hi,
            :funding_type, :funding_type_label_en, :funding_type_label_hi,
            :ministry_en, :ministry_hi, :launch_year, :budget,
            'all_india', :applicable_states, :overview_en, :overview_hi,
            :benefits_en, :benefits_hi, :eligibility_en, :eligibility_hi,
            :documents_en, :documents_hi, :how_to_apply_en, :how_to_apply_hi,
            :official_url, :helpline, :audio_text_en, :audio_text_hi,
            :eligibility_checker, :min_age, :max_age, :max_income,
            :target_occupations, :target_gender, :is_active, :sort_order
        )
        ";
        $stmt = $pdo->prepare($insertSql);
        $stmt->execute([
            ':slug' => $slug, ':title_en' => $title_en, ':title_hi' => $title_hi,
            ':tagline_en' => $tagline_en, ':tagline_hi' => $tagline_hi, ':icon' => $icon,
            ':image' => $image_path, ':category' => $category, ':category_name_en' => $category_name_en,
            ':category_name_hi' => $category_name_hi, ':funding_type' => $funding_type,
            ':funding_type_label_en' => $funding_type_label_en, ':funding_type_label_hi' => $funding_type_label_hi,
            ':ministry_en' => $ministry_en, ':ministry_hi' => $ministry_hi, ':launch_year' => $launch_year,
            ':budget' => $budget, ':applicable_states' => json_encode(array_values($applicable_states), JSON_UNESCAPED_UNICODE),
            ':overview_en' => $overview_en, ':overview_hi' => $overview_hi,
            ':benefits_en' => json_encode(array_values($benefits_en), JSON_UNESCAPED_UNICODE),
            ':benefits_hi' => json_encode(array_values($benefits_hi), JSON_UNESCAPED_UNICODE),
            ':eligibility_en' => json_encode(array_values($eligibility_en), JSON_UNESCAPED_UNICODE),
            ':eligibility_hi' => json_encode(array_values($eligibility_hi), JSON_UNESCAPED_UNICODE),
            ':documents_en' => json_encode(array_values($documents_en), JSON_UNESCAPED_UNICODE),
            ':documents_hi' => json_encode(array_values($documents_hi), JSON_UNESCAPED_UNICODE),
            ':how_to_apply_en' => json_encode(array_values($how_to_apply_en), JSON_UNESCAPED_UNICODE),
            ':how_to_apply_hi' => json_encode(array_values($how_to_apply_hi), JSON_UNESCAPED_UNICODE),
            ':official_url' => $official_url, ':helpline' => $helpline,
            ':audio_text_en' => $audio_text_en, ':audio_text_hi' => $audio_text_hi,
            ':eligibility_checker' => json_encode($eligibility_checker, JSON_UNESCAPED_UNICODE),
            ':min_age' => $min_age, ':max_age' => $max_age, ':max_income' => $max_income,
            ':target_occupations' => $target_occupations, ':target_gender' => $target_gender,
            ':is_active' => $is_active, ':sort_order' => $sort_order
        ]);
        set_flash('success', "New scheme '{$title_en}' created successfully!");
    }

    header('Location: ' . BASE_URL . '/admin/schemes.php');
    exit;
}

include __DIR__ . '/header.php';
?>

<style>
  /* Tabbed scheme-edit layout: groups a 40+ field form into manageable
     steps instead of one long scroll, with a sticky save bar always
     reachable. */
  .se-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    background: #ffffff;
    border: 1px solid var(--adm-border);
    border-radius: 10px;
    padding: 8px;
    margin-bottom: 20px;
    position: sticky;
    top: 76px;
    z-index: 15;
  }
  .se-tabs .nav-link {
    border: none;
    border-radius: 8px;
    padding: 9px 14px;
    font-size: 0.84rem;
    font-weight: 700;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: 7px;
    white-space: nowrap;
  }
  .se-tabs .nav-link .se-tab-num {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #475569;
    font-size: 0.7rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .se-tabs .nav-link.active {
    color: var(--adm-saffron-dark);
    background: var(--adm-saffron-tint);
  }
  .se-tabs .nav-link.active .se-tab-num {
    background: var(--adm-saffron);
    color: #ffffff;
  }

  .se-label {
    font-weight: 700;
    color: #1e293b;
    font-size: 0.86rem;
    margin-bottom: 6px;
    display: block;
  }
  .se-hint { color: #64748b; font-size: 0.78rem; display: block; margin-top: 4px; }
  .se-required { color: #dc2626; }

  .se-save-bar {
    position: sticky;
    bottom: 0;
    z-index: 10;
    background: #ffffff;
    border: 1px solid var(--adm-border);
    border-radius: 10px;
    padding: 14px 16px;
    margin-top: 10px;
    margin-bottom: 24px;
    box-shadow: 0 -6px 16px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
  }

  @media (max-width: 575.98px) {
    .se-tabs { position: static; overflow-x: auto; flex-wrap: nowrap; -webkit-overflow-scrolling: touch; }
    .se-save-bar { flex-direction: column; align-items: stretch; }
    .se-save-bar .btn { width: 100%; }
  }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div>
    <h2 class="fw-bold mb-1" style="color: var(--adm-blue-dark); font-size: 1.4rem;">
      <i class="fas fa-edit text-warning"></i>
      <?php echo $is_edit ? 'Edit Scheme: ' . htmlspecialchars($scheme['title_en']) : 'Add New Government Scheme'; ?>
    </h2>
    <p class="text-muted mb-0" style="font-size: 0.88rem;">
      Configure bilingual details, funding classification, imagery, and eligibility criteria — organized into steps below.
    </p>
  </div>
  <a href="<?php echo BASE_URL; ?>/admin/schemes.php" class="btn btn-default flex-shrink-0">
    <i class="fas fa-arrow-left"></i> Back to Schemes
  </a>
</div>

<form method="POST" action="" enctype="multipart/form-data">

  <!-- Step Navigation -->
  <ul class="nav se-tabs" id="schemeTabs" role="tablist">
    <li class="nav-item">
      <button class="nav-link active" id="tab-basic-btn" data-bs-toggle="tab" data-bs-target="#tab-basic" type="button" role="tab">
        <span class="se-tab-num">1</span> Basic Info
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="tab-image-btn" data-bs-toggle="tab" data-bs-target="#tab-image" type="button" role="tab">
        <span class="se-tab-num">2</span> Imagery
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="tab-overview-btn" data-bs-toggle="tab" data-bs-target="#tab-overview" type="button" role="tab">
        <span class="se-tab-num">3</span> Overview
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="tab-details-btn" data-bs-toggle="tab" data-bs-target="#tab-details" type="button" role="tab">
        <span class="se-tab-num">4</span> Benefits &amp; Docs
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="tab-eligibility-btn" data-bs-toggle="tab" data-bs-target="#tab-eligibility" type="button" role="tab">
        <span class="se-tab-num">5</span> Eligibility &amp; Audio
      </button>
    </li>
    <li class="nav-item">
      <button class="nav-link" id="tab-status-btn" data-bs-toggle="tab" data-bs-target="#tab-status" type="button" role="tab">
        <span class="se-tab-num">6</span> Status
      </button>
    </li>
  </ul>

  <div class="tab-content">

    <!-- 1. Basic Identification -->
    <div class="tab-pane fade show active" id="tab-basic" role="tabpanel">
      <div class="adm-card">
        <div class="adm-card-header">
          <h4><i class="fas fa-id-card text-primary"></i> Basic Scheme Information</h4>
        </div>
        <div class="adm-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="se-label">Scheme Title (English) <span class="se-required">*</span></label>
              <input type="text" name="title_en" value="<?php echo htmlspecialchars($scheme['title_en']); ?>" class="form-control" required placeholder="e.g. PM-KISAN (Pradhan Mantri Kisan Samman Nidhi)">
            </div>
            <div class="col-md-6">
              <label class="se-label">Scheme Title (Hindi) <span class="se-required">*</span></label>
              <input type="text" name="title_hi" value="<?php echo htmlspecialchars($scheme['title_hi']); ?>" class="form-control" required placeholder="e.g. प्रधानमंत्री किसान सम्मान निधि (PM-KISAN)">
            </div>

            <div class="col-md-4">
              <label class="se-label">Unique URL Slug <span class="se-required">*</span></label>
              <input type="text" name="slug" value="<?php echo htmlspecialchars($scheme['slug']); ?>" class="form-control" required placeholder="e.g. pm-kisan">
            </div>
            <div class="col-md-4">
              <label class="se-label">Funding Classification <span class="se-required">*</span></label>
              <select name="funding_type" class="form-control" required>
                <option value="central_sector" <?php echo ($scheme['funding_type'] === 'central_sector') ? 'selected' : ''; ?>>Central Sector Scheme (100% Central Funded)</option>
                <option value="centrally_sponsored" <?php echo ($scheme['funding_type'] === 'centrally_sponsored') ? 'selected' : ''; ?>>Centrally Sponsored Scheme (Shared 60:40 Centre &amp; State)</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="se-label">Category / Sector <span class="se-required">*</span></label>
              <select name="category" class="form-control" required>
                <option value="agriculture" <?php echo ($scheme['category'] === 'agriculture') ? 'selected' : ''; ?>>Agriculture &amp; Farmers</option>
                <option value="energy_utilities" <?php echo ($scheme['category'] === 'energy_utilities') ? 'selected' : ''; ?>>Renewable Energy &amp; Power</option>
                <option value="healthcare" <?php echo ($scheme['category'] === 'healthcare') ? 'selected' : ''; ?>>Healthcare &amp; Wellness</option>
                <option value="housing" <?php echo ($scheme['category'] === 'housing') ? 'selected' : ''; ?>>Housing &amp; Rural Development</option>
                <option value="employment" <?php echo ($scheme['category'] === 'employment') ? 'selected' : ''; ?>>Employment &amp; Skills</option>
                <option value="social_security" <?php echo ($scheme['category'] === 'social_security') ? 'selected' : ''; ?>>Social Security &amp; Pension</option>
                <option value="financial_services" <?php echo ($scheme['category'] === 'financial_services') ? 'selected' : ''; ?>>Financial Inclusion &amp; Credit</option>
                <option value="water_sanitation" <?php echo ($scheme['category'] === 'water_sanitation') ? 'selected' : ''; ?>>Water &amp; Sanitation</option>
                <option value="women_child" <?php echo ($scheme['category'] === 'women_child') ? 'selected' : ''; ?>>Women &amp; Child Development</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="se-label">Category Label (English)</label>
              <input type="text" name="category_name_en" value="<?php echo htmlspecialchars($scheme['category_name_en']); ?>" class="form-control" placeholder="e.g. Agriculture & Farmers Welfare">
            </div>
            <div class="col-md-6">
              <label class="se-label">Category Label (Hindi)</label>
              <input type="text" name="category_name_hi" value="<?php echo htmlspecialchars($scheme['category_name_hi']); ?>" class="form-control" placeholder="e.g. कृषि एवं किसान कल्याण">
            </div>

            <div class="col-md-6">
              <label class="se-label">Ministry (English)</label>
              <input type="text" name="ministry_en" value="<?php echo htmlspecialchars($scheme['ministry_en']); ?>" class="form-control" placeholder="e.g. Ministry of Agriculture and Farmers Welfare">
            </div>
            <div class="col-md-6">
              <label class="se-label">Ministry (Hindi)</label>
              <input type="text" name="ministry_hi" value="<?php echo htmlspecialchars($scheme['ministry_hi']); ?>" class="form-control" placeholder="e.g. कृषि एवं किसान कल्याण मंत्रालय">
            </div>

            <div class="col-md-3">
              <label class="se-label">Launch Year</label>
              <input type="text" name="launch_year" value="<?php echo htmlspecialchars($scheme['launch_year']); ?>" class="form-control" placeholder="e.g. 2019">
            </div>
            <div class="col-md-3">
              <label class="se-label">Budget Allocation</label>
              <input type="text" name="budget" value="<?php echo htmlspecialchars($scheme['budget']); ?>" class="form-control" placeholder="e.g. ₹60,000 Crore / year">
            </div>
            <div class="col-md-3">
              <label class="se-label">Helpline Number</label>
              <input type="text" name="helpline" value="<?php echo htmlspecialchars($scheme['helpline']); ?>" class="form-control" placeholder="e.g. 155261">
            </div>
            <div class="col-md-3">
              <label class="se-label">FontAwesome Icon</label>
              <input type="text" name="icon" value="<?php echo htmlspecialchars($scheme['icon']); ?>" class="form-control" placeholder="e.g. fa-tractor">
            </div>

            <div class="col-12">
              <label class="se-label">Official Government Portal URL</label>
              <input type="url" name="official_url" value="<?php echo htmlspecialchars($scheme['official_url']); ?>" class="form-control" placeholder="https://pmkisan.gov.in/">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 2. Scheme Imagery -->
    <div class="tab-pane fade" id="tab-image" role="tabpanel">
      <div class="adm-card">
        <div class="adm-card-header">
          <h4><i class="fas fa-image text-success"></i> Scheme Visual Imagery</h4>
        </div>
        <div class="adm-card-body">
          <div class="row g-3 align-items-center">
            <div class="col-md-3 text-center">
              <?php $current_img = !empty($scheme['image']) ? (BASE_URL . '/' . $scheme['image']) : (BASE_URL . '/image/schemes/default-scheme-banner.jpg'); ?>
              <img src="<?php echo htmlspecialchars($current_img); ?>" alt="Preview" style="max-width: 100%; height: 110px; object-fit: cover; border-radius: 8px; border: 1.5px solid #cbd5e1; margin-bottom: 8px;">
              <div class="text-muted" style="font-size: 0.76rem;">Current Image</div>
            </div>
            <div class="col-md-9">
              <div class="mb-3">
                <label class="se-label">Upload New Scheme Image (.jpg, .png, .webp)</label>
                <input type="file" name="scheme_image" class="form-control" accept="image/*">
                <small class="se-hint">Upload a high-resolution, relevant image for cards and details.</small>
              </div>
              <div>
                <label class="se-label">Or Image Relative Path on Server</label>
                <input type="text" name="image_text" value="<?php echo htmlspecialchars($scheme['image']); ?>" class="form-control" placeholder="image/schemes/pm-kisan.jpg">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 3. Summaries & Overviews -->
    <div class="tab-pane fade" id="tab-overview" role="tabpanel">
      <div class="adm-card">
        <div class="adm-card-header">
          <h4><i class="fas fa-align-left text-info"></i> Tagline &amp; Full Overview (Bilingual)</h4>
        </div>
        <div class="adm-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="se-label">Short Tagline (English)</label>
              <textarea name="tagline_en" rows="2" class="form-control" placeholder="Short 1-2 sentence card summary..."><?php echo htmlspecialchars($scheme['tagline_en']); ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="se-label">Short Tagline (Hindi)</label>
              <textarea name="tagline_hi" rows="2" class="form-control" placeholder="कार्ड पर दिखने वाला संक्षिप्त हिन्दी विवरण..."><?php echo htmlspecialchars($scheme['tagline_hi']); ?></textarea>
            </div>

            <div class="col-md-6">
              <label class="se-label">Comprehensive Overview (English)</label>
              <textarea name="overview_en" rows="6" class="form-control" placeholder="Full background, objective, and operational model..."><?php echo htmlspecialchars($scheme['overview_en']); ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="se-label">Comprehensive Overview (Hindi)</label>
              <textarea name="overview_hi" rows="6" class="form-control" placeholder="योजना का विस्तृत हिन्दी विवरण एवं उद्देश्य..."><?php echo htmlspecialchars($scheme['overview_hi']); ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 4. Multi-Line Lists: Benefits, Eligibility, Documents, How to Apply -->
    <div class="tab-pane fade" id="tab-details" role="tabpanel">
      <div class="adm-card">
        <div class="adm-card-header">
          <h4><i class="fas fa-list-ol text-warning"></i> Detailed Sections</h4>
          <span class="badge" style="background: #eef2f7; color: #475569; font-weight: 600; font-size: 0.72rem;">1 item per line</span>
        </div>
        <div class="adm-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="se-label">Key Benefits (English)</label>
              <textarea name="benefits_en" rows="5" class="form-control"><?php echo htmlspecialchars(implode("\n", (array)($scheme['benefits_en'] ?? []))); ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="se-label">Key Benefits (Hindi)</label>
              <textarea name="benefits_hi" rows="5" class="form-control"><?php echo htmlspecialchars(implode("\n", (array)($scheme['benefits_hi'] ?? []))); ?></textarea>
            </div>

            <div class="col-md-6">
              <label class="se-label">Eligibility Rules &amp; Exclusions (English)</label>
              <textarea name="eligibility_en" rows="5" class="form-control"><?php echo htmlspecialchars(implode("\n", (array)($scheme['eligibility_en'] ?? []))); ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="se-label">Eligibility Rules &amp; Exclusions (Hindi)</label>
              <textarea name="eligibility_hi" rows="5" class="form-control"><?php echo htmlspecialchars(implode("\n", (array)($scheme['eligibility_hi'] ?? []))); ?></textarea>
            </div>

            <div class="col-md-6">
              <label class="se-label">Required Documents (English)</label>
              <textarea name="documents_en" rows="4" class="form-control"><?php echo htmlspecialchars(implode("\n", (array)($scheme['documents_en'] ?? []))); ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="se-label">Required Documents (Hindi)</label>
              <textarea name="documents_hi" rows="4" class="form-control"><?php echo htmlspecialchars(implode("\n", (array)($scheme['documents_hi'] ?? []))); ?></textarea>
            </div>

            <div class="col-md-6">
              <label class="se-label">How to Apply Steps (English)</label>
              <textarea name="how_to_apply_en" rows="5" class="form-control"><?php echo htmlspecialchars(implode("\n", (array)($scheme['how_to_apply_en'] ?? []))); ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="se-label">How to Apply Steps (Hindi)</label>
              <textarea name="how_to_apply_hi" rows="5" class="form-control"><?php echo htmlspecialchars(implode("\n", (array)($scheme['how_to_apply_hi'] ?? []))); ?></textarea>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 5. Audio Narration & Eligibility Checker Criteria -->
    <div class="tab-pane fade" id="tab-eligibility" role="tabpanel">
      <div class="adm-card">
        <div class="adm-card-header">
          <h4><i class="fas fa-volume-up text-danger"></i> Audio Narration &amp; Eligibility Parameters</h4>
        </div>
        <div class="adm-card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="se-label">Audio Script (English - spoken via TTS)</label>
              <textarea name="audio_text_en" rows="3" class="form-control"><?php echo htmlspecialchars($scheme['audio_text_en']); ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="se-label">Audio Script (Hindi - spoken via TTS)</label>
              <textarea name="audio_text_hi" rows="3" class="form-control"><?php echo htmlspecialchars($scheme['audio_text_hi']); ?></textarea>
            </div>
          </div>

          <hr class="my-4">

          <div class="row g-3">
            <div class="col-md-3">
              <label class="se-label">Minimum Age</label>
              <input type="number" name="min_age" value="<?php echo (int)$scheme['min_age']; ?>" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="se-label">Maximum Age</label>
              <input type="number" name="max_age" value="<?php echo (int)$scheme['max_age']; ?>" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="se-label">Max Income Limit (₹)</label>
              <input type="number" name="max_income" value="<?php echo (int)$scheme['max_income']; ?>" class="form-control">
            </div>
            <div class="col-md-3">
              <label class="se-label">Target Gender</label>
              <select name="target_gender" class="form-control">
                <option value="all" <?php echo ($scheme['target_gender'] === 'all') ? 'selected' : ''; ?>>All (Male &amp; Female)</option>
                <option value="female" <?php echo ($scheme['target_gender'] === 'female') ? 'selected' : ''; ?>>Female Only</option>
                <option value="male" <?php echo ($scheme['target_gender'] === 'male') ? 'selected' : ''; ?>>Male Only</option>
              </select>
            </div>

            <div class="col-12">
              <label class="se-label">Target Occupations (comma-separated: farmer, artisan, street_vendor, unorganized, business, student, senior, all)</label>
              <input type="text" name="target_occupations" value="<?php echo htmlspecialchars($scheme['target_occupations'] ?? 'all'); ?>" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="se-label">Eligibility Question 1 (English)</label>
              <input type="text" name="q1_en" value="<?php echo htmlspecialchars($scheme['eligibility_checker']['q1_en'] ?? ''); ?>" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="se-label">Eligibility Question 1 (Hindi)</label>
              <input type="text" name="q1_hi" value="<?php echo htmlspecialchars($scheme['eligibility_checker']['q1_hi'] ?? ''); ?>" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="se-label">Eligibility Question 2 (English)</label>
              <input type="text" name="q2_en" value="<?php echo htmlspecialchars($scheme['eligibility_checker']['q2_en'] ?? ''); ?>" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="se-label">Eligibility Question 2 (Hindi)</label>
              <input type="text" name="q2_hi" value="<?php echo htmlspecialchars($scheme['eligibility_checker']['q2_hi'] ?? ''); ?>" class="form-control">
            </div>

            <div class="col-md-6">
              <label class="se-label">Eligible Verdict Message (English)</label>
              <input type="text" name="eligible_msg_en" value="<?php echo htmlspecialchars($scheme['eligibility_checker']['eligible_msg_en'] ?? ''); ?>" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="se-label">Eligible Verdict Message (Hindi)</label>
              <input type="text" name="eligible_msg_hi" value="<?php echo htmlspecialchars($scheme['eligibility_checker']['eligible_msg_hi'] ?? ''); ?>" class="form-control">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 6. Status -->
    <div class="tab-pane fade" id="tab-status" role="tabpanel">
      <div class="adm-card">
        <div class="adm-card-header">
          <h4><i class="fas fa-toggle-on text-success"></i> Publish Status</h4>
        </div>
        <div class="adm-card-body">
          <div class="row g-3 align-items-center">
            <div class="col-md-6">
              <label style="font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_active" value="1" <?php echo ($scheme['is_active'] == 1) ? 'checked' : ''; ?> style="width: 18px; height: 18px;">
                Active on Public Portal
              </label>
              <small class="se-hint">Unchecking this hides the scheme from the live website without deleting it.</small>
            </div>
            <div class="col-md-6">
              <label class="se-label">Sort Order (lower shows first)</label>
              <input type="number" name="sort_order" value="<?php echo (int)$scheme['sort_order']; ?>" class="form-control" style="max-width: 160px;">
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- Sticky Save Bar: always reachable regardless of active tab -->
  <div class="se-save-bar">
    <span class="text-muted" style="font-size: 0.82rem;"><i class="fas fa-info-circle"></i> Switching tabs keeps unsaved changes — nothing is lost until you save.</span>
    <div class="d-flex gap-2">
      <a href="<?php echo BASE_URL; ?>/admin/schemes.php" class="btn btn-default">Cancel</a>
      <button type="submit" class="btn btn-gov-saffron fw-bold px-4">
        <i class="fas fa-save"></i> Save Scheme to Database
      </button>
    </div>
  </div>

</form>

<script>
// Jump straight to the tab containing the first invalid field instead of
// silently blocking submit on a hidden tab (native HTML5 validation does
// not switch tabs on its own).
(function () {
  var form = document.querySelector('form[enctype="multipart/form-data"]');
  if (!form) return;
  form.addEventListener('submit', function (e) {
    var invalid = form.querySelector(':invalid');
    if (!invalid) return;
    var pane = invalid.closest('.tab-pane');
    if (pane && !pane.classList.contains('active')) {
      e.preventDefault();
      var trigger = document.querySelector('[data-bs-target="#' + pane.id + '"]');
      if (trigger) {
        var tab = new bootstrap.Tab(trigger);
        tab.show();
        setTimeout(function () { invalid.reportValidity(); }, 150);
      }
    }
  });
})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
