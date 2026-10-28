<?php
// index.php - Government Schemes & Citizen Services Portal Homepage (Strict Light Theme, Dynamic & Mobile Responsive)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/schemes.php';
require_once __DIR__ . '/data/services.php';

// Fetch active services (Top 6 featured for homepage)
$allServices = get_all_services($pdo, null, null, true);
$featuredServices = array_slice($allServices, 0, 6);

// Fetch active government schemes (Top 6 flagship for homepage)
$allSchemes = get_all_schemes();
$featuredSchemes = array_slice($allSchemes, 0, 6);

// Fetch admin-managed homepage FAQs
$homeFaqs = [];
try {
    $stmt_faq = $pdo->query("SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $homeFaqs = $stmt_faq->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Fetch latest notice-board updates (admin-posted + PIB auto-fetched)
$portalUpdates = [];
try {
    $stmt_upd = $pdo->query("
        SELECT * FROM portal_updates
        WHERE is_active = 1
        ORDER BY published_at DESC, id DESC
        LIMIT 10
    ");
    $portalUpdates = $stmt_upd->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Fetch approved citizen feedbacks for homepage testimonials
$homeFeedbacks = [];
try {
    $stmt_fb = $pdo->query("
        SELECT * FROM feedbacks 
        WHERE status = 'approved' 
        ORDER BY is_featured DESC, id DESC 
        LIMIT 6
    ");
    $homeFeedbacks = $stmt_fb->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// Fetch active hero banners from database (standard 1920x600 px)
$activeBanners = [];
try {
    $stmt_b = $pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY sort_order ASC, id ASC");
    $activeBanners = $stmt_b->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

// SEO Meta Variables
$pageTitle = APP_NAME . ' - Government Schemes & Citizen Services Portal | Jan Seva Facilitation';
$metaDescription = 'Single-window Indian citizen facilitation portal. Explore 20+ flagship Central & State schemes, check eligibility, and generate verified digital identity passes (ABHA, APAAR, e-Shram, Voter ID).';
$metaKeywords = 'government schemes India, citizen facilitation portal, ABHA health card, APAAR student id, eshram card, voter id verification, PM kisan, ayushman bharat, eligibility checker';
$canonicalUrl = BASE_URL . '/';
$activeNav = 'home';

include __DIR__ . '/includes/header.php';
?>

<!-- Schema.org JSON-LD Structured Data: WebSite & GovernmentOrganization -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "<?= BASE_URL ?>/#website",
      "url": "<?= BASE_URL ?>/",
      "name": "<?= APP_NAME ?>",
      "description": "<?= htmlspecialchars($metaDescription) ?>",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "<?= BASE_URL ?>/service.php?q={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@type": "GovernmentOrganization",
      "@id": "<?= BASE_URL ?>/#organization",
      "name": "<?= APP_NAME ?>",
      "url": "<?= BASE_URL ?>/",
      "logo": "<?= BASE_URL ?>/img/favicon.ico",
      "description": "Independent civic public facilitator simplifying citizen access to digital passes and government welfare schemes."
    }
  ]
}
</script>

    <!-- Header Carousel Start (Standard 1920x600 px Ratio, Dynamic from Admin) -->
    <div class="container-fluid p-0 hero-carousel-wrapper">
        <div id="header-carousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <?php if (!empty($activeBanners) && count($activeBanners) > 1): ?>
            <div class="carousel-indicators">
                <?php foreach ($activeBanners as $idx => $b): ?>
                    <button type="button" data-bs-target="#header-carousel" data-bs-slide-to="<?= $idx ?>" class="<?= $idx === 0 ? 'active' : '' ?>" aria-current="<?= $idx === 0 ? 'true' : 'false' ?>" aria-label="Slide <?= $idx + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="carousel-inner">
                <?php if (!empty($activeBanners)): ?>
                    <?php foreach ($activeBanners as $idx => $b): 
                        $targetUrl = (strpos($b['link_url'], 'http') === 0 ? '' : BASE_URL . '/') . ltrim($b['link_url'], '/');
                        $targetAttr = ($b['target'] === '_blank') ? ' target="_blank" rel="noopener noreferrer"' : '';
                        $imgUrl = (strpos($b['image_url'], 'http') === 0 ? '' : BASE_URL . '/') . ltrim($b['image_url'], '/');
                    ?>
                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>">
                        <a href="<?= htmlspecialchars($targetUrl) ?>" class="hero-banner-link"<?= $targetAttr ?> title="<?= htmlspecialchars($b['title']) ?>">
                            <div class="hero-banner-slide">
                                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="hero-banner-img" onerror="this.src='image/section.png'">
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="carousel-item active">
                        <a href="<?= BASE_URL ?>/service/abha-card" class="hero-banner-link">
                            <div class="hero-banner-slide">
                                <img src="<?= BASE_URL ?>/image/banners/banner-abha-card.jpg" alt="ABHA Health Card" class="hero-banner-img">
                            </div>
                        </a>
                    </div>
                    <div class="carousel-item">
                        <a href="<?= BASE_URL ?>/service/eshram-card" class="hero-banner-link">
                            <div class="hero-banner-slide">
                                <img src="<?= BASE_URL ?>/image/banners/banner-eshram-card.jpg" alt="e-Shram Card" class="hero-banner-img">
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#header-carousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <!-- Header Carousel End -->

    <!-- Notice Board (IGNOU-style scrolling updates) Start -->
    <section class="notice-board-section" id="schemes-updates">
        <div class="container-fluid p-0 m-0">
            <div class="notice-board">
                <div class="notice-board-header">
                    <i class="fas fa-bullhorn"></i>
                    <span>Schemes &amp; Updates</span>
                </div>
                <?php if (!empty($portalUpdates)): ?>
                <div class="notice-board-body">
                    <ul class="notice-board-list" id="noticeBoardList">
                        <?php
                        $catIcon = ['scheme' => 'fa-landmark', 'deadline' => 'fa-hourglass-half', 'service' => 'fa-id-card', 'notice' => 'fa-info-circle', 'general' => 'fa-circle-dot'];

                        function render_notice_item(array $u, array $catIcon, bool $hidden = false): void {
                            $isNew = (strtotime($u['published_at']) >= strtotime('-3 days'));
                            $icon = $catIcon[$u['category']] ?? 'fa-circle-dot';
                            $tag = $u['created_via'] === 'cron' ? htmlspecialchars($u['source_name'] ?: 'PIB') : null;
                            $hasLink = !empty($u['source_url']);
                            $tagName = $hasLink ? 'a' : 'span';
                            $extraAttrs = $hasLink
                                ? ' href="' . htmlspecialchars($u['source_url']) . '" target="_blank" rel="noopener noreferrer"' . ($hidden ? ' tabindex="-1"' : '')
                                : '';
                            ?>
                        <li<?= $hidden ? ' aria-hidden="true"' : '' ?>>
                            <<?= $tagName ?><?= $extraAttrs ?>>
                                <i class="fas <?= $icon ?> notice-item-icon"></i>
                                <span class="notice-item-text"><?= htmlspecialchars($u['title_en']) ?></span>
                                <span class="notice-item-date"><?= date('d M', strtotime($u['published_at'])) ?></span>
                                <?php if ($isNew): ?><span class="notice-item-new">New</span><?php endif; ?>
                                <?php if ($tag): ?><span class="notice-item-src"><?= $tag ?></span><?php endif; ?>
                            </<?= $tagName ?>>
                        </li>
                            <?php
                        }

                        foreach ($portalUpdates as $u) {
                            render_notice_item($u, $catIcon);
                        }
                        // Duplicate the list right after itself so the CSS loop-scroll has no visible seam
                        foreach ($portalUpdates as $u) {
                            render_notice_item($u, $catIcon, true);
                        }
                        ?>
                    </ul>
                </div>
                <?php else: ?>
                <div class="notice-board-body notice-board-empty">
                    <p class="text-muted mb-0">No updates posted yet.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- Notice Board End -->

    <!-- Services Category Section (Dynamic from Database) -->
    <div class="container-fluid light-bg cate-section py-5">
        <div class="container">

            <div class="row mb-4 align-items-center">
                <div class="col-sm-12 col-md-9">
                    <div class="wow fadeIn" data-wow-delay="0.1s">
                        <span class="text-primary text-uppercase fw-bold small"><i class="fas fa-shield-alt me-1"></i> Fast Digital Issuance &amp; Verification</span>
                        <h1 class="display-5 mb-2 fs-2">Citizen Services &amp; Digital Identity Passes</h1>
                        <p class="text-muted mb-0">Apply online for verified digital citizen identity documents and government welfare cards.</p>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3 text-md-end mt-3 mt-md-0">
                    <a class="btn btn-primary btn-custom rounded-pill"
                        href="service.php" style="text-align: center; width: fit-content;">All Services &rarr;</a>
                </div>
            </div>

            <div class="row g-4">
                <?php if (!empty($featuredServices)): ?>
                    <?php foreach ($featuredServices as $svc): 
                        $svcSlug = htmlspecialchars($svc['slug']);
                        $detailUrl = 'service/' . $svcSlug;
                        $svcImg = !empty($svc['image_url']) ? htmlspecialchars($svc['image_url']) : 'image/services/' . $svcSlug . '.jpg';
                        $fee = (float)($svc['fee'] ?? 0);
                        $btnText = $fee > 0 ? 'Apply Now (₹' . number_format($fee, 0) . ')' : 'View Guide (Free)';
                    ?>
                    <div class="col-sm-12 col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                        <div class="service-item position-relative overflow-hidden bg-white h-100 rounded shadow-sm border d-flex flex-column">
                            <div class="position-relative overflow-hidden">
                                <a href="<?= $detailUrl ?>">
                                    <img class="img-fluid w-100" style="height: 200px; object-fit: cover;" src="<?= $svcImg ?>" alt="<?= htmlspecialchars($svc['service_name']) ?>" onerror="this.src='image/services/1.jpg'">
                                </a>
                                <?php if (!empty($svc['badge_text'])): ?>
                                    <span class="badge bg-primary position-absolute top-0 end-0 m-2 shadow-sm"><?= htmlspecialchars($svc['badge_text']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <div class="mb-1 fs-5 service-title fw-bold">
                                    <a href="<?= $detailUrl ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($svc['service_name']) ?></a>
                                </div>
                                <?php if (!empty($svc['service_name_hi'])): ?>
                                    <div class="text-muted small mb-2"><?= htmlspecialchars($svc['service_name_hi']) ?></div>
                                <?php endif; ?>
                                <p class="text-muted small flex-grow-1 mb-3">
                                    <?= htmlspecialchars(mb_substr($svc['short_desc'] ?? '', 0, 95)) ?><?= mb_strlen($svc['short_desc'] ?? '') > 95 ? '...' : '' ?>
                                </p>
                                <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                    <a class="btn btn-primary btn-custom rounded-pill btn-sm px-3" href="<?= $detailUrl ?>"><?= $btnText ?></a>
                                    <span class="text-muted small"><i class="fas fa-clock text-primary me-1"></i><?= htmlspecialchars($svc['turnaround_time'] ?? '15 Mins') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-4">
                        <p class="text-muted">Services are loading from the database.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4 d-md-none">
                <a class="btn btn-primary btn-custom rounded-pill" href="service.php">View All Citizen Services &rarr;</a>
            </div>
        </div>
    </div>
    <!-- Services Category Section End -->

    <!-- Schemes & Benefits Section (Dynamic from Database) -->
    <section class="py-5 bg-light" id="schemes">
        <div class="container">

            <!-- Section Heading -->
            <div class="text-center mb-4">
                <span class="badge mb-2 scheme-gov-badge">
                    <i class="fas fa-landmark me-1"></i> GOVERNMENT OF INDIA
                </span>
                <h2 class="fw-bold text-dark mb-2">Government Schemes &amp; Welfare Initiatives</h2>
                <p class="text-secondary mx-auto" style="max-width: 650px;">
                    Explore flagship central &amp; state government schemes, check your eligibility, and visit official application portals.
                </p>
            </div>

            <!-- Category Filter -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-5" id="schemeFilters">
                <button type="button" class="btn rounded-pill filter-btn active" data-filter="all">All Schemes</button>
                <button type="button" class="btn rounded-pill filter-btn" data-filter="farmers">Farmers</button>
                <button type="button" class="btn rounded-pill filter-btn" data-filter="health">Health</button>
                <button type="button" class="btn rounded-pill filter-btn" data-filter="workers">Workers</button>
                <button type="button" class="btn rounded-pill filter-btn" data-filter="business">Business</button>
                <button type="button" class="btn rounded-pill filter-btn" data-filter="housing">Housing &amp; Infra</button>
            </div>

            <!-- Scheme Cards Grid -->
            <div class="row g-4" id="schemeList">
                <?php if (!empty($featuredSchemes)): ?>
                    <?php foreach ($featuredSchemes as $sch): 
                        // Normalize category filter alias for instant JavaScript category filtering
                        $rawCat = $sch['category'] ?? '';
                        $filterCat = match($rawCat) {
                            'agriculture' => 'farmers',
                            'healthcare' => 'health',
                            'social_security', 'employment' => 'workers',
                            'financial_services' => 'business',
                            'housing', 'energy_utilities' => 'housing',
                            default => $rawCat
                        };
                        $schImg = !empty($sch['image']) ? $sch['image'] : 'image/schemes/' . $sch['slug'] . '.jpg';
                        $detailLink = 'scheme_details.php?slug=' . urlencode($sch['slug']);
                        $fundingLabel = $sch['funding_type_label_en'] ?? 'Central Scheme';
                        $isCentralSector = ($sch['funding_type'] ?? '') === 'central_sector';
                    ?>
                    <div class="col-sm-6 col-lg-4 scheme-item" data-category="<?= htmlspecialchars($filterCat) ?>">
                        <div class="card h-100 border-0 shadow-sm scheme-card rounded-4 overflow-hidden d-flex flex-column bg-white">
                            <div class="position-relative overflow-hidden" style="height: 175px; background: #f1f5f9;">
                                <a href="<?= $detailLink ?>" class="d-block w-100 h-100">
                                    <img src="<?= htmlspecialchars($schImg) ?>" 
                                         alt="<?= htmlspecialchars($sch['title_en']) ?>" 
                                         class="w-100 h-100" 
                                         style="object-fit: cover; transition: transform 0.3s ease;"
                                         onerror="this.src='image/section.png'">
                                </a>
                                <span class="badge position-absolute top-0 end-0 m-2 shadow-sm <?= $isCentralSector ? 'bg-success' : 'bg-primary' ?>" style="font-size: 0.72rem; padding: 5px 10px; border-radius: 20px;">
                                    <i class="fas <?= $isCentralSector ? 'fa-landmark' : 'fa-handshake' ?> me-1"></i><?= htmlspecialchars($fundingLabel) ?>
                                </span>
                            </div>
                            <div class="card-body p-4 d-flex flex-column flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="scheme-card-icon-box <?= $isCentralSector ? 'icon-central' : 'icon-sponsored' ?>">
                                        <i class="fas <?= htmlspecialchars($sch['icon'] ?: 'fa-landmark') ?>"></i>
                                    </span>
                                    <span class="badge" style="background: #eff6ff; color: #1e3a8a; border: 1px solid #bfdbfe; font-size: 0.74rem; font-weight: 600; padding: 4px 10px; border-radius: 12px;">
                                        <?= htmlspecialchars($sch['category_name_en'] ?? ucfirst($rawCat)) ?>
                                    </span>
                                </div>
                                <h5 class="card-title fw-bold text-dark mb-1" style="font-size: 1.05rem; min-height: 48px; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <a href="<?= $detailLink ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($sch['title_en']) ?></a>
                                </h5>
                                <div class="text-muted small mb-2 text-truncate" style="min-height: 20px; font-size: 0.82rem;">
                                    <?= htmlspecialchars($sch['title_hi'] ?? '') ?>
                                </div>
                                <p class="card-text text-secondary small mb-3 flex-grow-1" style="min-height: 44px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    <?= htmlspecialchars(mb_substr($sch['tagline_en'] ?? $sch['overview_en'] ?? '', 0, 105)) ?>...
                                </p>
                                <div class="scheme-card-btn-row pt-3 border-top mt-auto d-flex align-items-center gap-2">
                                    <a href="<?= $detailLink ?>" class="btn-scheme-card-detail flex-grow-1">
                                        <i class="fas fa-info-circle me-1"></i> Read Details
                                    </a>
                                    <?php if (!empty($sch['official_url'])): ?>
                                    <a href="<?= htmlspecialchars($sch['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-scheme-card-official text-nowrap">
                                        Official <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <p id="noResults" class="text-center text-secondary mt-4" hidden>No schemes found in this category.</p>

            <!-- Bottom CTAs: All Schemes & Eligibility Checker -->
            <div class="text-center mt-5 d-flex flex-column flex-sm-row justify-content-center align-items-center gap-3">
                <a href="all-scheme.php" class="btn-scheme-bottom-primary">
                    Explore All 20+ Schemes <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <a href="eligibility-checker.php" class="btn-scheme-bottom-outline">
                    <i class="fas fa-clipboard-check me-2"></i> Check Your Eligibility
                </a>
            </div>

        </div>
    </section>
    <!-- Schemes & Benefits Section End -->

    <!-- Process Section Start (Rooted in Agriculture, Driven by Passion) -->
    <section class="process-section"
        style="background: linear-gradient(#d5e8f9eb,#d5e7f9eb) , url('image/Digital.jpg'); background-position: center; background-size: contain;">
        <div class="container">

            <div class="row process-row p-4 py-5 pb-3 mb-4">
                <div class="row">
                    <div class="col-sm-12 col-md-9">
                        <div class="wow fadeIn" data-wow-delay="0.1s">
                            <h1 class="display-5 mb-5 fs-2">Rooted in Agriculture, Driven by Passion</h1>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-3">
                        <a class="btn btn-primary btn-custom d-none d-lg-flex rounded-pill ms-auto me-0"
                            href="contact.php" style="text-align: center; width: fit-content;">View All Services</a>
                    </div>
                </div>

                <!-- Step 1 -->
                <div class="col-lg-3 col-sm-6 mb-4">
                    <div class="process-card">
                        <div class="process-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <span class="step-number">STEP 01</span>
                        <h4>Explore Schemes</h4>
                        <p>Discover government schemes according to your needs and category.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="col-lg-3 col-sm-6 mb-4">
                    <div class="process-card">
                        <div class="process-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <span class="step-number">STEP 02</span>
                        <h4>Check Eligibility</h4>
                        <p>Review the eligibility criteria and benefits of each scheme.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="col-lg-3 col-sm-6 mb-4">
                    <div class="process-card">
                        <div class="process-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <span class="step-number">STEP 03</span>
                        <h4>Prepare Documents</h4>
                        <p>Check the required documents before starting your application.</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="col-lg-3 col-sm-6 mb-4">
                    <div class="process-card">
                        <div class="process-icon">
                            <i class="fas fa-external-link-alt"></i>
                        </div>
                        <span class="step-number">STEP 04</span>
                        <h4>Visit Official Portal</h4>
                        <p>Continue to the official government website for application instructions.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Government Scheme Documents -->
                <div class="col-lg-4 col-md-6 mb-3 ps-0">
                    <div class="p-4 rounded h-100 aadhaar-card aadhaar-card1 documents">
                        <div class="fs-5 heading">Documents Required for Government Schemes</div>
                        <p class="mb-4">
                            Aadhaar Card | Income Certificate |
                            Residence Proof | Bank Details
                        </p>
                        <a href="https://www.myscheme.gov.in/" class="scheme-btn" target="_blank" rel="noopener">
                            Explore Scheme Requirements
                        </a>
                    </div>
                </div>

                <!-- Government Service Centres -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="p-4 rounded h-100 aadhaar-card aadhaar-card1 centre">
                        <div class="fs-5 heading">Find Your Nearest Government Service Centre</div>
                        <p class="mb-4">
                            Find official citizen service portals and information
                            about government services available in your state.
                        </p>
                        <a href="https://services.india.gov.in/" class="scheme-btn" target="_blank" rel="noopener">
                            Explore Government Services
                        </a>
                    </div>
                </div>

                <!-- Citizen Help -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="p-4 rounded h-100 aadhaar-card aadhaar-card1 help">
                        <div class="fs-5 heading">Need Help with Government Services?</div>
                        <p class="mb-4">
                            Find official helpline numbers, public grievance
                            portals and citizen support resources.
                        </p>
                        <a href="https://pgportal.gov.in/" class="scheme-btn mt-3" target="_blank" rel="noopener">
                            Submit a Grievance
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Process Section End -->

    <!-- About Start -->
    <div class="container-fluid py-5 about-section">
        <div class="container">
            <div class="row g-5">
                <div class="col-xl-9">
                    <div class="wow fadeIn" data-wow-delay="0.1s">
                        <h5 class="mb-3" style="color: orange;"><i class="fa fa-leaf me-2"></i>About Us</h5>
                        <h1 class="display-5 mb-5" style="color: white;">Empowering Citizens, Enabling a Better Future</h1>
                    </div>
                    <div class="row g-5">
                        <div class="col-lg-4">
                            <div class="position-relative h-100 wow fadeIn" data-wow-delay="0.1s">
                                <img class="img-fluid rounded w-100 h-100" style="object-fit: cover;"
                                    src="image/about-us-1.png" alt="Empowering Citizens">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <h4 class="lh-base mb-4 wow fadeIn" style="color: white;" data-wow-delay="0.1s">We help
                                citizens discover government schemes and access important public services with ease</h4>
                            <p class="mb-4 wow fadeIn text-light" data-wow-delay="0.2s">Our platform brings together scheme
                                information, eligibility criteria, required documents and application procedures.</p>
                            <div class="about-items-2 bg-light rounded">
                                <div class="row g-0">
                                    <div class="col-md-6 wow fadeIn" data-wow-delay="0.3s">
                                        <div class="h-100 p-5 border-end border-bottom">
                                            <i class="fa fa-seedling fa-3x text-primary mb-3"></i>
                                            <h5>Easy Access to Schemes</h5>
                                            <span>Comprehensive access to state and central digital schemes.</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 wow fadeIn" data-wow-delay="0.4s">
                                        <div class="h-100 p-5">
                                            <i class="fa fa-leaf fa-3x text-primary mb-3"></i>
                                            <h5>Trusted Official Resources</h5>
                                            <span>Direct official portal integration and document eligibility checks.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 d-none d-xl-block">
                    <div class="position-relative h-100 wow fadeIn" data-wow-delay="0.3s">
                        <img class="img-fluid rounded w-100 h-100" style="object-fit: cover;" src="image/about-us.png" alt="Citizen Support">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <!-- Gov Banner Start -->
    <section class="mt-5">
        <div class="container gov-banner">
            <div class="row g-0 align-items-stretch">
                <!-- Left Content -->
                <div class="col-lg-6 col-md-6">
                    <div class="gov-banner-content">
                        <span class="gov-tag">
                            <i class="fas fa-landmark me-2"></i>
                            Digital India | Citizen Services
                        </span>
                        <h2>Government Services Made Simple</h2>
                        <p>
                            Explore government schemes, apply for essential
                            cards, and access important public services
                            through one convenient platform.
                        </p>
                        <a href="all-scheme.php" class="gov-btn">
                            Explore Our Services
                            <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                <!-- Right Image -->
                <div class="col-lg-6 col-md-6 px-0">
                    <div class="gov-banner-image">
                        <img src="image/section.png" alt="Digital Government Services">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- Gov Banner End -->

    <!-- Features Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-xl-9">
                    <div class="row g-5">
                        <div class="col-lg-4">
                            <div class="position-relative h-100 wow fadeIn" data-wow-delay="0.3s">
                                <img class="img-fluid rounded w-100 h-100" style="object-fit: cover;" src="image/team.png" alt="Our Team">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="wow fadeIn" data-wow-delay="0.1s">
                                <h5 class="mb-3"><i class="fa fa-leaf text-primary me-2"></i>Features</h5>
                                <h1 class="display-5 mb-5">Why Choose Our Services</h1>
                            </div>
                            <p class="mb-4 fadeIn" data-wow-delay="0.2s">Get trusted guidance and easy application
                                support to access government schemes and citizen services.</p>
                            <div class="row g-4">
                                <div class="col-md-6 wow fadeIn" data-wow-delay="0.3s">
                                    <div class="d-flex align-items-center bg-light rounded h-100 p-4">
                                        <div class="flex-shrink-0 btn-lg-square rounded bg-primary text-dark me-3">
                                            <i class="fa-solid fa-file-circle-check text-light fs-4"></i>
                                        </div>
                                        <h5 class="lh-base mb-0">Easy Application Process</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 wow fadeIn" data-wow-delay="0.4s">
                                    <div class="d-flex align-items-center bg-light rounded h-100 p-4">
                                        <div class="flex-shrink-0 btn-lg-square rounded bg-primary text-dark me-3">
                                            <i class="fa-solid fa-user-tie text-light fs-4"></i>
                                        </div>
                                        <h5 class="lh-base mb-0">Expert Assistance</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 wow fadeIn" data-wow-delay="0.5s">
                                    <div class="d-flex align-items-center bg-light rounded h-100 p-4">
                                        <div class="flex-shrink-0 btn-lg-square rounded bg-primary text-dark me-3">
                                            <i class="fa-solid fa-circle-check text-light fs-4"></i>
                                        </div>
                                        <h5 class="lh-base mb-0">Reliable Information</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 wow fadeIn" data-wow-delay="0.6s">
                                    <div class="d-flex align-items-center bg-light rounded h-100 p-4">
                                        <div class="flex-shrink-0 btn-lg-square rounded bg-primary text-dark me-3">
                                            <i class="fa-solid fa-handshake-angle fs-4 text-light"></i>
                                        </div>
                                        <h5 class="lh-base mb-0">Citizen-Focused Services</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 d-none d-xl-block">
                    <div class="position-relative h-100 wow fadeIn" data-wow-delay="0.5s">
                        <img class="img-fluid rounded w-100 h-100 mb-n4" style="object-fit: cover;" src="image/pen.png" alt="Application Guide">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Features End -->

    <!-- FAQ Section Start -->
    <section class="gov-faq-section" id="faq" style="background: linear-gradient(#d5e8f9eb,#d5e7f9eb) , url('image/Digital.jpg'); background-position: center; background-size: contain;">
        <div class="container">
            <div class="faq-heading text-center">
                <span class="faq-subtitle">
                    <i class="fas fa-question-circle"></i> Help Center
                </span>
                <h2>Frequently Asked Questions</h2>
                <p>
                    Find answers to common questions about government cards, schemes, eligibility and online applications.
                </p>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="faq-image">
                        <img src="image/faq.png" alt="Government service assistance">
                        <div class="faq-image-content">
                            <i class="fas fa-headset"></i>
                            <div>
                                <h5>Need Assistance?</h5>
                                <p>Find the information you need.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="faq-list" id="govFaqAccordion">
                        <?php if (!empty($homeFaqs)): ?>
                            <?php foreach ($homeFaqs as $fIdx => $faq): ?>
                                <div class="faq-item<?= $fIdx === 0 ? ' active-faq' : '' ?>">
                                    <button class="faq-question" type="button" aria-expanded="<?= $fIdx === 0 ? 'true' : 'false' ?>">
                                        <span><?= $fIdx + 1 ?>. <?= htmlspecialchars($faq['question_en']) ?></span>
                                        <i class="fas <?= $fIdx === 0 ? 'fa-minus' : 'fa-plus' ?> faq-icon"></i>
                                    </button>
                                    <div class="faq-answer" style="<?= $fIdx === 0 ? 'display:block;' : '' ?>">
                                        <p><?= nl2br(htmlspecialchars($faq['answer_en'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted">FAQs will appear here once added from the admin panel.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- FAQ Section End -->

    <!-- Testimonial Start (Dynamic Approved Citizen Feedbacks) -->
    <div class="container-fluid bg-light py-5" id="testimonials">
        <div class="container py-5">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                <div class="section-title wow fadeIn" data-wow-delay="0.1s" style="margin-bottom: 0;">
                    <h5 class="mb-2"><i class="fas fa-comments text-primary me-2"></i>Citizen Feedback</h5>
                    <h1 class="display-5 mb-0 fs-2">What Citizens Say About Our Services</h1>
                </div>
                <div class="wow fadeIn" data-wow-delay="0.2s">
                    <a href="feedback.php" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" style="background: #1e3a8a; border-color: #1e3a8a;">
                        <i class="fas fa-pen me-2"></i> Share Your Experience (फीडबैक दें) &rarr;
                    </a>
                </div>
            </div>

            <?php if (!empty($homeFeedbacks)): ?>
                <div class="owl-carousel citizen-feedback-carousel">
                    <?php foreach ($homeFeedbacks as $idx => $fb):
                        $stars = max(1, min(5, (int)$fb['rating']));
                        $initial = mb_strtoupper(mb_substr($fb['citizen_name'], 0, 1));
                    ?>
                    <div class="feedback-item">
                        <div class="bg-white rounded-4 h-100 p-4 shadow-sm border d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div style="color: #f59e0b !important; font-size: 0.95rem;">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i class="fas fa-star<?= $s <= $stars ? '' : ' text-muted' ?>" style="<?= $s <= $stars ? '' : 'color: #cbd5e1 !important;' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="badge bg-success-subtle text-success small" style="font-size: 0.72rem;">
                                        <i class="fas fa-check-circle me-1"></i> Verified Citizen
                                    </span>
                                </div>
                                <?php if (!empty($fb['title'])): ?>
                                    <h6 class="fw-bold text-dark mb-2">"<?= htmlspecialchars($fb['title']) ?>"</h6>
                                <?php endif; ?>
                                <p class="text-secondary small mb-3" style="line-height: 1.6;">
                                    <?= htmlspecialchars($fb['comment']) ?>
                                </p>
                            </div>
                            <div class="pt-3 border-top d-flex align-items-center">
                                <div style="width: 46px; height: 46px; border-radius: 50%; background: #eff6ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; border: 1.5px solid #bfdbfe; flex-shrink: 0;">
                                    <?= $initial ?>
                                </div>
                                <div class="ms-3 flex-grow-1 overflow-hidden">
                                    <h6 class="mb-0 fw-bold text-dark text-truncate"><?= htmlspecialchars($fb['citizen_name']) ?></h6>
                                    <div class="text-muted small text-truncate">
                                        <?= htmlspecialchars($fb['city'] ?? $fb['state'] ?? 'India') ?><?php if (!empty($fb['occupation'])): ?> • <span class="text-primary fw-semibold"><?= htmlspecialchars($fb['occupation']) ?></span><?php endif; ?>
                                    </div>
                                    <div class="text-muted text-truncate" style="font-size: 0.72rem;">
                                        Service: <?= htmlspecialchars($fb['service_name']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted">No approved reviews yet. Be the first to share your experience!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <!-- Testimonial End -->

<?php include __DIR__ . '/includes/footer.php'; ?>
