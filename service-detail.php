<?php
// service-detail.php - Citizen Service Details & Application Guide (Strict Light Theme, GIGW Compliant & SEO Optimized)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/services.php';

$slug = trim($_GET['slug'] ?? '');

// Also check URL path if rewrite passed pathinfo
if (empty($slug) && !empty($_SERVER['PATH_INFO'])) {
    $slug = trim($_SERVER['PATH_INFO'], '/');
}

$service = null;
if (!empty($slug)) {
    $service = get_service_by_slug($pdo, $slug);
}

if (!$service) {
    // Fallback: try first active service
    $stmt_first = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC, id ASC LIMIT 1");
    $service_row = $stmt_first->fetch(PDO::FETCH_ASSOC);
    if ($service_row) {
        $service = hydrate_service_json_fields($service_row);
    }
}

if (!$service) {
    header('Location: ' . BASE_URL . '/service.php');
    exit;
}

// SEO Meta Variables
$pageTitle = !empty($service['meta_title']) 
    ? $service['meta_title'] . ' | ' . APP_NAME 
    : $service['service_name'] . ' (' . ($service['service_name_hi'] ?? '') . ') - Online Apply & Verification | ' . APP_NAME;

$metaDescription = !empty($service['meta_desc']) 
    ? $service['meta_desc'] 
    : $service['short_desc'];

$metaKeywords = !empty($service['meta_keywords']) 
    ? $service['meta_keywords'] 
    : $service['service_name'] . ', ' . $service['slug'] . ', citizen service India, apply online, PVC card download';

$canonicalUrl = BASE_URL . '/service/' . urlencode($service['slug']);
$activeNav = 'services';
$is_api = ($service['service_type'] === 'api');

// Fetch 3 related services in the same or other categories
$related_stmt = $pdo->prepare("SELECT * FROM services WHERE id != ? AND is_active = 1 ORDER BY (category = ?) DESC, sort_order ASC LIMIT 3");
$related_stmt->execute([$service['id'], $service['category']]);
$related_services = $related_stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<!-- Schema.org JSON-LD Structured Data: GovernmentService, BreadcrumbList, and FAQPage -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "GovernmentService",
      "@id": "<?= $canonicalUrl ?>#service",
      "name": "<?= htmlspecialchars($service['service_name']) ?>",
      "alternateName": "<?= htmlspecialchars($service['service_name_hi'] ?? '') ?>",
      "description": "<?= htmlspecialchars($service['short_desc']) ?>",
      "url": "<?= $canonicalUrl ?>",
      "serviceType": "<?= $is_api ? 'Digital Identity Pass Generation' : 'Official Department Guidance' ?>",
      "provider": {
        "@type": "GovernmentOrganization",
        "name": "<?= htmlspecialchars($service['department_en'] ?? 'Government of India') ?>",
        "url": "<?= htmlspecialchars($service['official_url']) ?>"
      },
      "areaServed": {
        "@type": "AdministrativeArea",
        "name": "India"
      },
      "offers": {
        "@type": "Offer",
        "price": "<?= number_format((float)$service['fee'], 2, '.', '') ?>",
        "priceCurrency": "INR",
        "availability": "https://schema.org/InStock"
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "<?= $canonicalUrl ?>#breadcrumbs",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "<?= BASE_URL ?>/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Citizen Services",
          "item": "<?= BASE_URL ?>/services"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "<?= htmlspecialchars($service['service_name']) ?>",
          "item": "<?= $canonicalUrl ?>"
        }
      ]
    }
    <?php if (!empty($service['faqs'])): ?>
    ,{
      "@type": "FAQPage",
      "@id": "<?= $canonicalUrl ?>#faq",
      "mainEntity": [
        <?php foreach ($service['faqs'] as $idx => $faq): ?>
        {
          "@type": "Question",
          "name": "<?= htmlspecialchars($faq['q'] ?? '') ?>",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "<?= htmlspecialchars($faq['a'] ?? '') ?>"
          }
        }<?= ($idx < count($service['faqs']) - 1) ? ',' : '' ?>
        <?php endforeach; ?>
      ]
    }
    <?php endif; ?>
  ]
}
</script>

<style>
/* Detail Hero with Clean Civic Gradient & Background Pattern */
.service-detail-hero {
    background: linear-gradient(120deg, rgba(248, 250, 252, 0.94) 0%, rgba(241, 245, 249, 0.92) 100%), url('image/barr.png');
    background-position: center;
    background-size: cover;
    border-bottom: 1px solid #e2e8f0;
    padding: 28px 0 38px;
}
.detail-main {
    background: #f8fafc;
    padding: 38px 0 70px;
}
.detail-panel {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 26px 28px;
    margin-bottom: 24px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
}
.panel-heading {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1.5px solid #f1f5f9;
    padding-bottom: 14px;
    margin-bottom: 18px;
}
.panel-heading h2 {
    font-size: 1.35rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
}
.section-eyebrow {
    font-size: 0.74rem;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 2px;
}
.heading-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #eff6ff;
    color: #1d4ed8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}
.detail-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.detail-list li {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px dashed #f1f5f9;
    color: #334155;
    font-size: 0.92rem;
    line-height: 1.5;
}
.detail-list li:last-child {
    border-bottom: none;
}
.detail-list li i {
    color: #10b981;
    font-size: 1.05rem;
    margin-top: 3px;
    flex-shrink: 0;
}
.step-number-badge {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #1e3a8a;
    color: #ffffff;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}
.step-item-wrap {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    padding: 14px 0;
    border-bottom: 1px solid #f1f5f9;
}
.step-item-wrap:last-child {
    border-bottom: none;
}
.faq-accordion .accordion-button:not(.collapsed) {
    background-color: #eff6ff;
    color: #1e40af;
    font-weight: 700;
}
.faq-accordion .accordion-button {
    font-weight: 700;
    color: #0f172a;
    font-size: 0.95rem;
}
.detail-sidebar .sidebar-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px;
    margin-bottom: 20px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);
}
@media (max-width: 767px) {
    .service-detail-hero h1 { font-size: 1.6rem !important; }
    .detail-panel { padding: 18px 20px; }
}
</style>

<!-- Breadcrumb Hero Header -->
<section class="service-detail-hero">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb" style="margin-bottom: 14px;">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php"><i class="fas fa-home mr-1"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/service.php">Citizen Services</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($service['service_name']) ?></li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <span class="badge px-3 py-2" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.8rem; font-weight: 700; border-radius: 20px;">
                        <i class="fas fa-landmark mr-1"></i> <?= htmlspecialchars($service['department_en'] ?? 'Government of India') ?>
                    </span>
                    <span class="badge px-3 py-2" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; font-size: 0.8rem; font-weight: 600; border-radius: 20px;">
                        <i class="fas fa-tag mr-1"></i> <?= htmlspecialchars($service['category']) ?>
                    </span>
                    <?php if ($is_api): ?>
                        <span class="badge px-3 py-2" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.8rem; font-weight: 700; border-radius: 20px;">
                            <i class="fas fa-bolt mr-1"></i> Instant Digital Pass (<?= format_inr($service['fee']) ?>)
                        </span>
                    <?php else: ?>
                        <span class="badge px-3 py-2" style="background: #f8fafc; color: #1e3a8a; border: 1px solid #cbd5e1; font-size: 0.8rem; font-weight: 700; border-radius: 20px;">
                            <i class="fas fa-compass mr-1"></i> Assisted Department Guide (Free)
                        </span>
                    <?php endif; ?>
                </div>

                <h1 style="color: #0f172a; font-size: 2.1rem; font-weight: 800; margin-bottom: 6px;">
                    <?= htmlspecialchars($service['service_name']) ?>
                </h1>

                <?php if (!empty($service['service_name_hi'])): ?>
                    <div style="font-size: 1.15rem; color: #475569; font-weight: 600; margin-bottom: 8px;">
                        <?= htmlspecialchars($service['service_name_hi']) ?>
                    </div>
                <?php endif; ?>

                <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; max-width: 820px; margin: 0;">
                    <?= htmlspecialchars($service['short_desc']) ?>
                </p>
            </div>

            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 20px; display: inline-block; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Turnaround Schedule</div>
                    <div style="font-size: 1.25rem; font-weight: 800; color: #059669; line-height: 1.2; margin-top: 4px;">
                        <?= htmlspecialchars($service['turnaround_time'] ?? 'Instant') ?>
                    </div>
                    <div style="font-size: 0.74rem; color: #64748b; margin-top: 2px;">Official Verification Mode</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Service Content Section -->
<div class="detail-main">
    <div class="container">
        <div class="row g-4">

            <!-- Main Column (Left) -->
            <div class="col-lg-8">

                <!-- Service Overview Panel -->
                <section class="detail-panel" id="overview">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">OFFICIAL SERVICE MANDATE</span>
                            <h2>Service Overview &amp; Objective</h2>
                        </div>
                        <span class="heading-icon"><i class="<?= htmlspecialchars($service['icon'] ?? 'fas fa-id-card') ?>"></i></span>
                    </div>

                    <!-- Photo & Quick Details -->
                    <div class="row g-3 align-items-center mb-3">
                        <div class="col-md-5">
                            <div style="border-radius: 10px; overflow: hidden; border: 1px solid #e2e8f0; height: 180px;">
                                <img src="<?= BASE_URL . '/' . htmlspecialchars($service['image_url'] ?? 'image/services/1.jpg') ?>" alt="<?= htmlspecialchars($service['service_name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                <div style="font-size: 0.76rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Issuing Authority</div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem; margin-bottom: 8px;">
                                    <?= htmlspecialchars($service['department_en'] ?? 'Government of India') ?>
                                </div>
                                <div style="font-size: 0.76rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Card Format</div>
                                <div style="font-weight: 700; color: #1e3a8a; font-size: 0.9rem;">
                                    <?= $is_api ? 'High-Definition CR80 PVC Digital Pass (ISO Standard)' : 'Official Department Verification & Form Guidelines' ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <p style="color: #334155; font-size: 0.95rem; line-height: 1.7; margin-bottom: 16px;">
                        <?= nl2br(htmlspecialchars($service['full_desc'])) ?>
                    </p>

                    <div class="d-flex gap-2 flex-wrap">
                        <a href="<?= htmlspecialchars($service['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary" style="font-weight: 700; border-radius: 8px; padding: 7px 14px;">
                            <i class="fas fa-external-link-alt me-1"></i> Visit Official Department Portal
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($service['official_url']) ?>'); alert('Official link copied to clipboard!');" style="font-weight: 600; border-radius: 8px; padding: 7px 14px;">
                            <i class="fas fa-copy me-1"></i> Copy Link
                        </button>
                    </div>
                </section>

                <!-- Dynamic Form Panel (STREAM A: Direct Instant Generation) -->
                <?php if ($is_api): ?>
                <section class="detail-panel" id="application-form-panel" style="border: 1.5px solid #bbf7d0;">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow" style="color: #047857;">STREAM A: INSTANT DIGITAL GENERATION</span>
                            <h2>Apply for <?= htmlspecialchars($service['service_name']) ?></h2>
                        </div>
                        <span class="heading-icon" style="background: #ecfdf5; color: #047857;"><i class="fas fa-bolt"></i></span>
                    </div>

                    <p style="color: #475569; font-size: 0.9rem; margin-bottom: 20px;">
                        Fill in your demographic information below to authenticate with mobile OTP and generate your verified CR80 digital PVC Card pass.
                    </p>

                    <form id="direct-api-form" autocomplete="off">
                        <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>">
                        <input type="hidden" name="service_slug" value="<?= htmlspecialchars($service['slug']) ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="full_name" class="form-label fw-bold small">Applicant Full Name <span class="text-danger">*</span></label>
                                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="As written on official ID" required>
                            </div>

                            <div class="col-md-6">
                                <label for="phone" class="form-label fw-bold small">Aadhaar Linked Mobile Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-secondary">+91</span>
                                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="10-digit mobile" maxlength="10" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="dob" class="form-label fw-bold small">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" id="dob" name="dob" class="form-control" required value="1996-05-15">
                            </div>

                            <div class="col-md-6">
                                <label for="gender" class="form-label fw-bold small">Gender <span class="text-danger">*</span></label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="Male">Male (पुरुष)</option>
                                    <option value="Female">Female (महिला)</option>
                                    <option value="Transgender">Transgender / Other</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="state" class="form-label fw-bold small">State / UT <span class="text-danger">*</span></label>
                                <select id="state" name="state" class="form-control" required>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Uttar Pradesh">Uttar Pradesh</option>
                                    <option value="Bihar">Bihar</option>
                                    <option value="Maharashtra">Maharashtra</option>
                                    <option value="Rajasthan">Rajasthan</option>
                                    <option value="Madhya Pradesh">Madhya Pradesh</option>
                                    <option value="West Bengal">West Bengal</option>
                                    <option value="Karnataka">Karnataka</option>
                                    <option value="Gujarat">Gujarat</option>
                                    <option value="Haryana">Haryana</option>
                                    <option value="Punjab">Punjab</option>
                                    <option value="Other">Other State</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="address" class="form-label fw-bold small">Permanent Address</label>
                                <input type="text" id="address" name="address" class="form-control" placeholder="House/Village, District, Pincode" value="Civil Lines, Delhi - 110054">
                            </div>

                            <!-- Specific Dynamic Fields Depending on Slug -->
                            <?php if ($service['slug'] === 'abha-card'): ?>
                                <div class="col-12">
                                    <div class="p-3 rounded border" style="background: #f8fafc;">
                                        <label for="aadhaar_input" class="form-label fw-bold text-primary small mb-1">
                                            <i class="fas fa-fingerprint text-success me-1"></i> 12-Digit Aadhaar or Virtual ID (Optional)
                                        </label>
                                        <input type="password" id="aadhaar_input" name="aadhaar_input" class="form-control" placeholder="XXXX-XXXX-XXXX" maxlength="14">
                                        <small class="text-muted">Aadhaar helps automatically link electronic health records across ABDM-certified hospitals and health centres.</small>
                                    </div>
                                </div>
                            <?php elseif ($service['slug'] === 'apaar-card'): ?>
                                <div class="col-md-6">
                                    <label for="institution" class="form-label fw-bold small">School / College / University <span class="text-danger">*</span></label>
                                    <input type="text" id="institution" name="institution" class="form-control" placeholder="e.g. Delhi University / Kendriya Vidyalaya" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="course" class="form-label fw-bold small">Course / Degree / Standard</label>
                                    <input type="text" id="course" name="course" class="form-control" placeholder="e.g. B.Tech / B.Com / Class 12" value="Undergraduate / Senior Secondary">
                                </div>
                            <?php elseif ($service['slug'] === 'eshram-card'): ?>
                                <div class="col-md-6">
                                    <label for="occupation" class="form-label fw-bold small">Primary Occupation Sector <span class="text-danger">*</span></label>
                                    <input type="text" id="occupation" name="occupation" class="form-control" placeholder="e.g. Construction / Agriculture / Driver" value="Building & Other Construction Worker" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="father_name" class="form-label fw-bold small">Father / Husband Name</label>
                                    <input type="text" id="father_name" name="father_name" class="form-control" placeholder="Enter Full Name" value="Shri Ram Prasad">
                                </div>
                            <?php elseif ($service['slug'] === 'voter-id'): ?>
                                <div class="col-md-6">
                                    <label for="epic_number" class="form-label fw-bold small">10-Character EPIC Number (Voter ID) <span class="text-danger">*</span></label>
                                    <input type="text" id="epic_number" name="epic_number" class="form-control text-uppercase" placeholder="e.g. WBF8492015" maxlength="10" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="constituency" class="form-label fw-bold small">Assembly / Parliamentary Constituency</label>
                                    <input type="text" id="constituency" name="constituency" class="form-control" placeholder="e.g. New Delhi AC-40" value="New Delhi Constituency">
                                </div>
                            <?php endif; ?>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="citizenConsent" required checked>
                                    <label class="form-check-label small text-secondary" for="citizenConsent">
                                        I confirm that the details entered above belong to me. I authorize this facilitation portal to process digital verification and acknowledge that a nominal platform fee of <strong><?= format_inr($service['fee']) ?></strong> will be charged.
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" id="btn-submit-application" class="btn btn-primary py-3 px-4 w-100 fw-bold" style="background: #1e3a8a; border: none; font-size: 1rem; border-radius: 8px;">
                                    <i class="fas fa-lock me-2"></i> Verify Phone &amp; Proceed (Pay <?= format_inr($service['fee']) ?>)
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
                <?php else: ?>

                <!-- Assisted Checklist Panel (STREAM B: Guidance & Redirection) -->
                <section class="detail-panel" id="assisted-guidance-panel" style="border: 1.5px solid #bfdbfe;">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow" style="color: #1e40af;">STREAM B: ASSISTED OFFICIAL GUIDANCE</span>
                            <h2>Prerequisite Checklist &amp; Official Redirection</h2>
                        </div>
                        <span class="heading-icon" style="background: #eff6ff; color: #1e40af;"><i class="fas fa-compass"></i></span>
                    </div>

                    <p style="color: #475569; font-size: 0.9rem; margin-bottom: 20px;">
                        Review the required documents and prerequisites below before navigating to the official statutory department portal.
                    </p>

                    <div class="p-3 rounded mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <h6 class="fw-bold mb-3" style="color: #0f172a;"><i class="fas fa-clipboard-check text-success me-2"></i> Prerequisite Verification Checklist:</h6>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="chk-doc-1" checked>
                            <label class="form-check-label small" for="chk-doc-1">Proof of Identity (PoI): Passport / Voter ID / PAN Card / Driving License</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="chk-doc-2" checked>
                            <label class="form-check-label small" for="chk-doc-2">Proof of Address (PoA): Latest Electricity Bill / Water Bill / Bank Passbook</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="chk-doc-3" checked>
                            <label class="form-check-label small" for="chk-doc-3">Date of Birth Proof: Birth Certificate / Class 10th Board Certificate</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="chk-doc-4" checked>
                            <label class="form-check-label small" for="chk-doc-4">Active Mobile Number linked with Aadhaar for receiving OTP</label>
                        </div>
                    </div>

                    <div class="d-flex gap-3 flex-wrap align-items-center">
                        <a href="<?= htmlspecialchars($service['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary py-3 px-4 fw-bold" style="background: #1e3a8a; border: none; border-radius: 8px;">
                            <i class="fas fa-external-link-alt me-2"></i> Open Official Government Portal Now &rarr;
                        </a>
                        <a href="<?= BASE_URL ?>/doorstep-booking.php?service_id=<?= (int)$service['id'] ?>" class="btn btn-outline-secondary py-3 px-4 fw-bold" style="border-radius: 8px;">
                            <i class="fas fa-home me-2"></i> Book Doorstep Home Visit
                        </a>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Key Benefits Panel -->
                <section class="detail-panel" id="benefits">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">KEY ADVANTAGES</span>
                            <h2>Benefits &amp; Features</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-gift"></i></span>
                    </div>
                    <ul class="detail-list">
                        <?php foreach ($service['benefits'] as $b): ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><?= htmlspecialchars($b) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <!-- Eligibility Criteria Panel -->
                <section class="detail-panel" id="eligibility">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">QUALIFYING CONDITIONS</span>
                            <h2>Eligibility Criteria</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-user-check"></i></span>
                    </div>
                    <p style="color: #64748b; font-size: 0.88rem; margin-bottom: 12px;">
                        Citizens must meet the following statutory requirements to access this service:
                    </p>
                    <ul class="detail-list">
                        <?php foreach ($service['eligibility'] as $e): ?>
                            <li>
                                <i class="fas fa-check-circle"></i>
                                <span><?= htmlspecialchars($e) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>

                <!-- Required Documents Panel -->
                <section class="detail-panel" id="documents">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">DOCUMENTATION CHECKLIST</span>
                            <h2>Required Documents</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-folder-open"></i></span>
                    </div>
                    <p style="color: #64748b; font-size: 0.88rem; margin-bottom: 14px;">
                        Keep the following documents handy in digital or physical format:
                    </p>
                    <div class="row g-2">
                        <?php foreach ($service['documents'] as $doc): ?>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px; font-size: 0.88rem; font-weight: 600; color: #334155;">
                                    <i class="far fa-file-alt text-primary"></i>
                                    <span><?= htmlspecialchars($doc) ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Application Process Steps Panel -->
                <section class="detail-panel" id="steps">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">STEP-BY-STEP WORKFLOW</span>
                            <h2>How to Apply</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-list-ol"></i></span>
                    </div>

                    <div>
                        <?php foreach ($service['steps'] as $idx => $st): 
                            $stepNum = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                            $stTitle = is_array($st) ? ($st['title'] ?? '') : $st;
                            $stDesc = is_array($st) ? ($st['desc'] ?? '') : '';
                        ?>
                            <div class="step-item-wrap">
                                <div class="step-number-badge"><?= $stepNum ?></div>
                                <div style="flex: 1;">
                                    <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">
                                        <?= htmlspecialchars($stTitle) ?>
                                    </h4>
                                    <?php if (!empty($stDesc)): ?>
                                        <p style="font-size: 0.88rem; color: #475569; margin: 0; line-height: 1.5;">
                                            <?= htmlspecialchars($stDesc) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Frequently Asked Questions (FAQ) Accordion Panel -->
                <?php if (!empty($service['faqs'])): ?>
                <section class="detail-panel" id="faqs">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">COMMON CITIZEN INQUIRIES</span>
                            <h2>Frequently Asked Questions</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-question-circle"></i></span>
                    </div>

                    <div class="accordion faq-accordion" id="serviceFaqAccordion">
                        <?php foreach ($service['faqs'] as $fIdx => $faq): 
                            $headingId = 'faqHeading' . $fIdx;
                            $collapseId = 'faqCollapse' . $fIdx;
                            $isOpen = ($fIdx === 0);
                        ?>
                            <div class="accordion-item mb-2" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                                <h3 class="accordion-header" id="<?= $headingId ?>">
                                    <button class="accordion-button <?= $isOpen ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= $collapseId ?>" aria-expanded="<?= $isOpen ? 'true' : 'false' ?>" aria-controls="<?= $collapseId ?>">
                                        <?= htmlspecialchars($faq['q'] ?? '') ?>
                                    </button>
                                </h3>
                                <div id="<?= $collapseId ?>" class="accordion-collapse collapse <?= $isOpen ? 'show' : '' ?>" aria-labelledby="<?= $headingId ?>" data-bs-parent="#serviceFaqAccordion">
                                    <div class="accordion-body" style="font-size: 0.9rem; color: #475569; line-height: 1.6;">
                                        <?= htmlspecialchars($faq['a'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
                <?php endif; ?>

            </div>

            <!-- Sticky Sidebar (Right) -->
            <aside class="col-lg-4">
                <div class="detail-sidebar" style="position: sticky; top: 90px;">

                    <!-- Quick Facts Card -->
                    <div class="sidebar-card">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="fas fa-info-circle text-primary" style="font-size: 1.15rem;"></i>
                            <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Quick Summary</h3>
                        </div>

                        <div class="mb-3">
                            <div style="font-size: 0.74rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Facilitation Fee</div>
                            <div style="font-size: 1.4rem; font-weight: 800; color: #0f172a;">
                                <?= format_inr($service['fee']) ?>
                                <?php if ($service['fee'] == 0): ?>
                                    <span class="badge bg-success ms-1" style="font-size: 0.7rem;">Free Guide</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div style="font-size: 0.74rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Turnaround Time</div>
                            <div style="font-weight: 700; color: #059669; font-size: 0.92rem;">
                                <i class="fas fa-clock me-1"></i> <?= htmlspecialchars($service['turnaround_time'] ?? 'Instant') ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div style="font-size: 0.74rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Verification Type</div>
                            <div style="font-weight: 600; color: #334155; font-size: 0.88rem;">
                                <?= $is_api ? 'Aadhaar Mobile OTP Direct e-KYC' : 'Official Department Verification' ?>
                            </div>
                        </div>

                        <a href="<?= htmlspecialchars($service['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary w-100 fw-bold" style="border-radius: 8px; font-size: 0.88rem;">
                            <i class="fas fa-external-link-alt me-1"></i> Official Portal Link &rarr;
                        </a>
                    </div>

                    <!-- Doorstep Facilitation Card -->
                    <div class="sidebar-card" style="border: 1px solid #bfdbfe; background: #f0f7ff;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-home text-primary"></i>
                            <h4 style="font-size: 1rem; font-weight: 800; color: #1e3a8a; margin: 0;">Doorstep Citizen Support</h4>
                        </div>
                        <p style="font-size: 0.84rem; color: #334155; line-height: 1.5; margin-bottom: 12px;">
                            Need help filling out official forms or biometric assistance? Book an authorized citizen executive to visit your residence.
                        </p>
                        <a href="<?= BASE_URL ?>/doorstep-booking.php?service_id=<?= (int)$service['id'] ?>" class="btn btn-sm btn-primary w-100 fw-bold" style="background: #1e3a8a; border: none; border-radius: 8px;">
                            Book Executive Visit
                        </a>
                    </div>

                    <!-- Security & Privacy Guarantee -->
                    <div class="sidebar-card" style="border-left: 4px solid #10b981;">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-shield-alt text-success"></i>
                            <h4 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin: 0;">Zero Biometric Retention</h4>
                        </div>
                        <p style="font-size: 0.82rem; color: #64748b; margin: 0; line-height: 1.5;">
                            All user details are processed ephemerally. We do not store biometric fingerprints, iris scans, or full Aadhaar numbers on our database servers.
                        </p>
                    </div>

                    <!-- Related Citizen Services -->
                    <?php if (!empty($related_services)): ?>
                    <div class="sidebar-card">
                        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                            <i class="fas fa-th-large text-secondary"></i>
                            <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;">Related Services</h4>
                        </div>

                        <?php foreach ($related_services as $rel): ?>
                            <a href="<?= BASE_URL ?>/service/<?= urlencode($rel['slug']) ?>" class="d-flex align-items-center gap-2 p-2 rounded mb-2 text-decoration-none" style="background: #f8fafc; border: 1px solid #e2e8f0; transition: background 0.2s;">
                                <i class="<?= htmlspecialchars($rel['icon'] ?? 'fas fa-id-card') ?> text-primary"></i>
                                <div style="flex: 1;">
                                    <div style="font-size: 0.86rem; font-weight: 700; color: #0f172a; line-height: 1.2;">
                                        <?= htmlspecialchars($rel['service_name']) ?>
                                    </div>
                                    <div style="font-size: 0.72rem; color: #64748b;">
                                        <?= htmlspecialchars($rel['category']) ?> • <?= format_inr($rel['fee']) ?>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-muted" style="font-size: 0.75rem;"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                </div>
            </aside>

        </div>

        <!-- Statutory Legal Disclaimer -->
        <div class="mt-4 p-3 rounded-3" style="background: #ffffff; border: 1px solid #e2e8f0; display: flex; align-items: flex-start; gap: 12px;">
            <i class="fas fa-info-circle text-primary mt-1"></i>
            <p style="font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.5;">
                <strong>Disclaimer:</strong> <?= htmlspecialchars(LEGAL_DISCLAIMER) ?> This portal is an independent civic facilitator designed to simplify citizen access to digital passes and government schemes. Official statutory services remain under the exclusive jurisdiction of the respective Government of India ministries and state departments.
            </p>
        </div>

    </div>
</div>

<!-- Citizen OTP Verification Modal (Bootstrap 5) -->
<?php include __DIR__ . '/includes/otp_modal.php'; ?>

<!-- Client-side Logic for Form Submission, OTP, Razorpay & Card Generation -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var currentFormData = null;
    var currentOrderId = null;

    var directForm = document.getElementById('direct-api-form');
    if (directForm) {
        directForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(directForm);
            currentFormData = {};
            formData.forEach(function(value, key){
                currentFormData[key] = value;
            });

            var nameVal = document.getElementById('full_name').value;
            var phoneVal = document.getElementById('phone').value;

            document.getElementById('otp_citizen_name').value = nameVal;
            document.getElementById('otp_phone_input').value = phoneVal;
            document.getElementById('otp-step-1').style.display = 'block';
            document.getElementById('otp-step-2').style.display = 'none';
            document.getElementById('otp-send-alert').classList.add('d-none');

            var otpModal = new bootstrap.Modal(document.getElementById('otpVerificationModal'));
            otpModal.show();
        });
    }

    var btnRequestOtp = document.getElementById('btn-request-otp');
    if (btnRequestOtp) {
        btnRequestOtp.addEventListener('click', function() {
            var phone = document.getElementById('otp_phone_input').value.replace(/\D/g, '');
            var name = document.getElementById('otp_citizen_name').value;
            var alertBox = document.getElementById('otp-send-alert');

            if (phone.length !== 10) {
                alertBox.className = 'alert alert-danger small py-2 mb-3';
                alertBox.textContent = 'Please enter a valid 10-digit mobile number.';
                alertBox.classList.remove('d-none');
                return;
            }

            btnRequestOtp.disabled = true;
            btnRequestOtp.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending OTP...';

            $.ajax({
                url: '<?= BASE_URL ?>/api/send_otp.php',
                type: 'POST',
                data: { phone: phone, name: name },
                dataType: 'json',
                success: function(res) {
                    btnRequestOtp.disabled = false;
                    btnRequestOtp.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Verification OTP';
                    if (res.success) {
                        document.getElementById('otp-target-display').textContent = '+91-' + phone;
                        if (res.demo_otp) {
                            document.getElementById('otp-demo-hint').innerHTML = 'Sandbox OTP: <strong>' + res.demo_otp + '</strong> (or enter <strong>123456</strong>)';
                        }
                        $('#otp-step-1').slideUp(200);
                        $('#otp-step-2').slideDown(200);
                    } else {
                        alertBox.className = 'alert alert-danger small py-2 mb-3';
                        alertBox.textContent = res.message;
                        alertBox.classList.remove('d-none');
                    }
                },
                error: function() {
                    btnRequestOtp.disabled = false;
                    btnRequestOtp.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Verification OTP';
                    alertBox.className = 'alert alert-danger small py-2 mb-3';
                    alertBox.textContent = 'Server communication error. Please try again.';
                    alertBox.classList.remove('d-none');
                }
            });
        });
    }

    var btnVerifyOtp = document.getElementById('btn-verify-otp');
    if (btnVerifyOtp) {
        btnVerifyOtp.addEventListener('click', function() {
            var phone = document.getElementById('otp_phone_input').value.replace(/\D/g, '');
            var otp = document.getElementById('otp_code_input').value.trim();
            var verifyAlert = document.getElementById('otp-verify-alert');

            if (otp.length < 4) {
                verifyAlert.className = 'alert alert-danger small py-2 mb-3';
                verifyAlert.textContent = 'Please enter the 6-digit verification code.';
                verifyAlert.classList.remove('d-none');
                return;
            }

            btnVerifyOtp.disabled = true;
            btnVerifyOtp.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Verifying...';

            $.ajax({
                url: '<?= BASE_URL ?>/api/verify_otp.php',
                type: 'POST',
                data: { phone: phone, otp: otp },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        var modalEl = document.getElementById('otpVerificationModal');
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        triggerPaymentFlow();
                    } else {
                        btnVerifyOtp.disabled = false;
                        btnVerifyOtp.innerHTML = '<i class="fas fa-check-circle me-1"></i> Verify & Proceed to Payment';
                        verifyAlert.className = 'alert alert-danger small py-2 mb-3';
                        verifyAlert.textContent = res.message;
                        verifyAlert.classList.remove('d-none');
                    }
                },
                error: function() {
                    btnVerifyOtp.disabled = false;
                    btnVerifyOtp.innerHTML = '<i class="fas fa-check-circle me-1"></i> Verify & Proceed to Payment';
                    verifyAlert.className = 'alert alert-danger small py-2 mb-3';
                    verifyAlert.textContent = 'Verification request failed. Try again.';
                    verifyAlert.classList.remove('d-none');
                }
            });
        });
    }

    function triggerPaymentFlow() {
        $.ajax({
            url: '<?= BASE_URL ?>/api/create_order.php',
            type: 'POST',
            data: currentFormData,
            dataType: 'json',
            success: function(orderRes) {
                if (!orderRes.success) {
                    alert('Error creating order: ' + orderRes.message);
                    return;
                }

                currentOrderId = orderRes.order_id;

                var options = {
                    "key": orderRes.key_id,
                    "amount": orderRes.amount_paise,
                    "currency": orderRes.currency,
                    "name": "<?= APP_NAME ?>",
                    "description": "Facilitation Fee for " + orderRes.service_name,
                    "image": "<?= BASE_URL ?>/img/favicon.ico",
                    "prefill": {
                        "name": orderRes.user_name,
                        "contact": orderRes.user_phone
                    },
                    "theme": {
                        "color": "#1C1B3A"
                    },
                    "handler": function(paymentResponse) {
                        completeOrder(orderRes.order_id, paymentResponse.razorpay_payment_id, paymentResponse.razorpay_signature);
                    },
                    "modal": {
                        "ondismiss": function() {
                            if (confirm("Sandbox Test Mode: Simulate successful test payment of <?= format_inr($service['fee']) ?>?")) {
                                completeOrder(orderRes.order_id, "pay_mock_" + Math.random().toString(36).substring(7), "sig_mock_ok");
                            }
                        }
                    }
                };

                try {
                    var rzp = new Razorpay(options);
                    rzp.on('payment.failed', function(response) {
                        alert("Payment failed: " + response.error.description);
                    });
                    rzp.open();
                } catch (e) {
                    if (confirm("Simulate successful test payment of <?= format_inr($service['fee']) ?>?")) {
                        completeOrder(orderRes.order_id, "pay_mock_test", "sig_mock");
                    }
                }
            },
            error: function() {
                alert('Server error creating payment order. Please try again.');
            }
        });
    }

    function completeOrder(orderId, paymentId, signature) {
        $('body').append('<div id="loading-overlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(28,27,58,0.92);z-index:999999;display:flex;flex-direction:column;align-items:center;justify-content:center;color:white;"><i class="fas fa-spinner fa-spin fa-4x text-warning"></i><h4 class="mt-3 fw-bold">Verifying Payment & Calling Official Gateway...</h4><p class="text-white-50">Generating your high-resolution digital PVC card. Please do not refresh.</p></div>');

        $.ajax({
            url: '<?= BASE_URL ?>/api/verify_payment.php',
            type: 'POST',
            data: {
                order_id: orderId,
                payment_id: paymentId,
                signature: signature
            },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    window.location.href = res.redirect_url;
                } else {
                    $('#loading-overlay').remove();
                    alert('Payment verification issue: ' + res.message);
                }
            },
            error: function() {
                $('#loading-overlay').remove();
                alert('Server processing error. Check your Track Status page.');
            }
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
