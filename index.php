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

<style>
/* Homepage Specific Responsive & Strict Light Theme Styles */
.hero-carousel-img {
    width: 100%;
    max-height: 440px;
    object-fit: cover;
    background-color: #f1f5f9;
}
.home-section-header {
    margin-bottom: 28px;
}
.home-section-header .section-eyebrow {
    font-size: 0.76rem;
    font-weight: 700;
    color: #1d4ed8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
    margin-bottom: 4px;
}
.home-section-header h2 {
    font-size: 1.85rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 6px;
    line-height: 1.3;
}
.home-section-header p {
    color: #475569;
    font-size: 0.95rem;
    max-width: 780px;
    margin: 0;
}
.service-home-card, .scheme-home-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
}
.service-home-card:hover, .scheme-home-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.home-card-media {
    position: relative;
    height: 170px;
    background: #e2e8f0;
    overflow: hidden;
}
.home-card-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.service-home-card:hover .home-card-media img,
.scheme-home-card:hover .home-card-media img {
    transform: scale(1.04);
}
.home-card-body {
    padding: 18px 20px 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex: 1;
}
.eligibility-cta-box {
    background: linear-gradient(120deg, rgba(248, 250, 252, 0.95) 0%, rgba(241, 245, 249, 0.92) 100%), url('image/barr.png');
    background-position: center;
    background-size: cover;
    border: 1.5px solid #bfdbfe;
    border-radius: 18px;
    padding: 38px 30px;
    box-shadow: 0 8px 24px rgba(30, 58, 138, 0.05);
}
.process-step-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 24px 20px;
    height: 100%;
    transition: transform 0.2s ease;
}
.process-step-box:hover {
    transform: translateY(-3px);
    border-color: #93c5fd;
}
.process-step-num {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: #eff6ff;
    color: #1e3a8a;
    font-weight: 800;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 12px;
}
@media (max-width: 767px) {
    .home-section-header h2 { font-size: 1.5rem !important; }
    .hero-carousel-img { max-height: 240px; }
    .home-card-media { height: 150px; }
    .eligibility-cta-box { padding: 24px 18px; }
}
</style>

<!-- Top Hero Banner Carousel -->
<div class="container-fluid p-0">
    <div id="header-carousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <a href="<?= BASE_URL ?>/service/abha-card">
                    <img class="hero-carousel-img" src="img/ABHA Card Apply Healthcare Banner.png" alt="ABHA Health Card Online Apply">
                </a>
            </div>
            <div class="carousel-item">
                <a href="<?= BASE_URL ?>/service/eshram-card">
                    <img class="hero-carousel-img" src="img/e-Shram Card Benefits Banner.png" alt="e-Shram Card Welfare Benefits">
                </a>
            </div>
            <div class="carousel-item">
                <a href="<?= BASE_URL ?>/service/eshram-card">
                    <img class="hero-carousel-img" src="img/e-Shram Card Application Banner.png" alt="e-Shram Card Instant PVC Card">
                </a>
            </div>
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

<!-- Ticker / Live Updates Section -->
<section class="updata-section" style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 12px 0;">
    <div class="container">
        <div class="updata-body row align-items-center g-2">
            <div class="col-sm-12 col-md-4 col-lg-3">
                <span class="badge px-3 py-2" style="background: #eff6ff; color: #1e3a8a; font-weight: 700; font-size: 0.82rem; border-radius: 20px; border: 1px solid #bfdbfe;">
                    <i class="fas fa-bullhorn text-warning me-1"></i> Schemes &amp; Updates
                </span>
            </div>
            <div class="marquee-body col-sm-12 col-md-8 col-lg-9">
                <marquee behavior="scroll" direction="left" scrollamount="6" style="color: #334155; font-size: 0.9rem; font-weight: 600;">
                    📢 Latest: Check eligibility for 20+ flagship Central Sector &amp; Centrally Sponsored Schemes • Instant digital issuance available for ABHA Health ID, APAAR Student ID, e-Shram &amp; Voter ID • Doorstep executive assistance available across all districts.
                </marquee>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 1: Citizen Services & Digital Identity Cards (Dynamic from DB) -->
<section class="py-5" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
    <div class="container">

        <div class="row align-items-end home-section-header">
            <div class="col-md-8">
                <span class="section-eyebrow">
                    <i class="fas fa-shield-alt me-1"></i> DIRECT DIGITAL GENERATION &amp; ASSISTANCE
                </span>
                <h2>Citizen Identity &amp; Digital Services <span style="font-size: 1.25rem; color: #475569; font-weight: 600;">(नागरिक सेवाएं)</span></h2>
                <p>
                    Fast-track real-time generation for verified identity cards with secure QR codes and assisted step-by-step guidance for statutory government portals.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= BASE_URL ?>/service.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold" style="background: #1e3a8a; border: none; font-size: 0.88rem;">
                    View All Services (<?= count($allServices) ?>) &rarr;
                </a>
            </div>
        </div>

        <!-- 6 Featured Dynamic Services Grid -->
        <div class="row g-4">
            <?php foreach ($featuredServices as $srv): 
                $is_api = ($srv['service_type'] === 'api');
                $img_src = !empty($srv['image_url']) ? $srv['image_url'] : 'image/services/1.jpg';
                $clean_fee = (float) $srv['fee'];
                $slug_url = BASE_URL . '/service/' . urlencode($srv['slug']);
            ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="service-home-card">
                        
                        <!-- Media Header -->
                        <div class="home-card-media">
                            <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($srv['service_name']) ?>" loading="lazy">
                            <div class="position-absolute top-0 start-0 m-2">
                                <?php if ($is_api): ?>
                                    <span class="badge" style="background: rgba(5, 150, 105, 0.95); color: #fff; font-size: 0.72rem; padding: 4px 9px; border-radius: 12px; backdrop-filter: blur(4px);">
                                        <i class="fas fa-bolt me-1"></i> Direct API (<?= format_inr($clean_fee) ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(30, 64, 175, 0.95); color: #fff; font-size: 0.72rem; padding: 4px 9px; border-radius: 12px; backdrop-filter: blur(4px);">
                                        <i class="fas fa-compass me-1"></i> Assisted Guide (Free)
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="position-absolute bottom-0 end-0 m-2">
                                <span class="badge" style="background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 0.7rem; border-radius: 8px;">
                                    <?= htmlspecialchars($srv['category']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="home-card-body">
                            <div>
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">
                                    <i class="fas fa-landmark me-1"></i> <?= htmlspecialchars($srv['department_en'] ?? 'Government of India') ?>
                                </div>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 2px; line-height: 1.35;">
                                    <a href="<?= $slug_url ?>" style="color: inherit; text-decoration: none;">
                                        <?= htmlspecialchars($srv['service_name']) ?>
                                    </a>
                                </h3>
                                <?php if (!empty($srv['service_name_hi'])): ?>
                                    <div style="font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 8px;">
                                        <?= htmlspecialchars($srv['service_name_hi']) ?>
                                    </div>
                                <?php endif; ?>
                                <p style="color: #475569; font-size: 0.86rem; line-height: 1.5; margin-bottom: 14px;">
                                    <?= htmlspecialchars($srv['short_desc']) ?>
                                </p>
                            </div>

                            <!-- Card Footer Action -->
                            <div class="d-flex gap-2 pt-2" style="border-top: 1px solid #f1f5f9;">
                                <a href="<?= $slug_url ?>" class="btn btn-sm btn-primary flex-grow-1" style="background: #1e3a8a; border: none; font-weight: 700; border-radius: 6px; padding: 8px 10px; font-size: 0.82rem; text-align: center;">
                                    <?= $is_api ? 'Apply Now (' . format_inr($clean_fee) . ') &rarr;' : 'Check Guidelines &rarr;' ?>
                                </a>
                                <a href="<?= htmlspecialchars($srv['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; padding: 8px 10px; font-size: 0.82rem;" title="Official Portal">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- SECTION 2: Interactive Eligibility Checker Widget Banner -->
<section class="py-5" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
    <div class="container">
        <div class="eligibility-cta-box">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <span class="badge mb-2 px-3 py-2" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.8rem; font-weight: 700; border-radius: 20px;">
                        <i class="fas fa-user-check me-1"></i> INSTANT CITIZEN WELFARE ASSESSMENT
                    </span>
                    <h3 style="font-size: 1.7rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
                        Are You Eligible for Government Welfare Schemes? <span style="font-size: 1.15rem; color: #475569; font-weight: 600;">(पात्रता जांचें)</span>
                    </h3>
                    <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin-bottom: 16px;">
                        Answer 4 simple questions regarding your age, income, state, and occupation. Our matching engine evaluates official rules across <strong>20+ flagship Central &amp; State schemes</strong> to find your benefits.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <span style="font-size: 0.82rem; font-weight: 700; color: #059669;"><i class="fas fa-check-circle me-1"></i> 60-Second Result</span>
                        <span style="font-size: 0.82rem; font-weight: 700; color: #1e3a8a;"><i class="fas fa-shield-alt me-1"></i> 100% Private (No Aadhaar Stored)</span>
                        <span style="font-size: 0.82rem; font-weight: 700; color: #d97706;"><i class="fas fa-coins me-1"></i> Direct Benefit Transfer</span>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a href="<?= BASE_URL ?>/eligibility-checker.php" class="btn btn-primary btn-lg rounded-pill px-4 py-3 fw-bold" style="background: #059669; border-color: #059669; font-size: 1rem; box-shadow: 0 4px 14px rgba(5, 150, 105, 0.25);">
                        <i class="fas fa-search-plus me-2"></i> Check My Eligibility Now &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 3: Major Government Schemes (Dynamic from DB) -->
<section class="py-5" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
    <div class="container">

        <div class="row align-items-end home-section-header">
            <div class="col-md-8">
                <span class="section-eyebrow">
                    <i class="fas fa-landmark me-1"></i> VAJIRAM &amp; RAVI 2026 &amp; VISIONIAS PT 365 CLASSIFICATION
                </span>
                <h2>Major Government Welfare Schemes <span style="font-size: 1.25rem; color: #475569; font-weight: 600;">(प्रमुख सरकारी योजनाएँ)</span></h2>
                <p>
                    Explore official welfare initiatives classified strictly into <strong>Central Sector Schemes (100% Union Government)</strong> and <strong>Centrally Sponsored Schemes (CSS - Shared Centre + State)</strong>.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="<?= BASE_URL ?>/all-scheme.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold" style="background: #1e3a8a; border: none; font-size: 0.88rem;">
                    View All Schemes (<?= count($allSchemes) ?>) &rarr;
                </a>
            </div>
        </div>

        <!-- 6 Featured Dynamic Schemes Grid -->
        <div class="row g-4">
            <?php foreach ($featuredSchemes as $sch): 
                $isCentral = ($sch['funding_type'] === 'central_sector');
                $imgSrc = !empty($sch['image']) ? $sch['image'] : 'image/schemes/vajiram-schemes-banner.webp';
                $topBenefit = (!empty($sch['benefits_en']) && is_array($sch['benefits_en'])) ? $sch['benefits_en'][0] : ($sch['tagline_en'] ?? '');
                $detailUrl = BASE_URL . '/scheme_details.php?slug=' . urlencode($sch['slug']);
            ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="scheme-home-card">
                        
                        <!-- Media Header -->
                        <div class="home-card-media">
                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="<?= htmlspecialchars($sch['title_en']) ?>" loading="lazy">
                            <div class="position-absolute top-0 start-0 m-2">
                                <?php if ($isCentral): ?>
                                    <span class="badge" style="background: rgba(30, 64, 175, 0.95); color: #fff; font-size: 0.72rem; padding: 4px 9px; border-radius: 12px; backdrop-filter: blur(4px);">
                                        <i class="fas fa-landmark me-1"></i> Central Sector (100%)
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(5, 150, 105, 0.95); color: #fff; font-size: 0.72rem; padding: 4px 9px; border-radius: 12px; backdrop-filter: blur(4px);">
                                        <i class="fas fa-handshake me-1"></i> Centrally Sponsored
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="position-absolute bottom-0 end-0 m-2">
                                <span class="badge" style="background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 0.7rem; border-radius: 8px;">
                                    <?= htmlspecialchars($sch['category_name_en'] ?? $sch['category']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="home-card-body">
                            <div>
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">
                                    <i class="fas fa-building me-1"></i> <?= htmlspecialchars($sch['ministry_en'] ?? 'Ministry of India') ?>
                                </div>
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 2px; line-height: 1.35;">
                                    <a href="<?= $detailUrl ?>" style="color: inherit; text-decoration: none;">
                                        <?= htmlspecialchars($sch['title_en']) ?>
                                    </a>
                                </h3>
                                <div style="font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 10px;">
                                    <?= htmlspecialchars($sch['title_hi'] ?? '') ?>
                                </div>

                                <!-- Key Benefit Pill -->
                                <div class="p-2 mb-3 rounded" style="background: #f0fdf4; border: 1px solid #bbf7d0; font-size: 0.78rem; color: #065f46; font-weight: 600;">
                                    <i class="fas fa-check-circle text-success me-1"></i> <?= htmlspecialchars($topBenefit) ?>
                                </div>
                            </div>

                            <!-- Card Footer Action -->
                            <div class="d-flex gap-2 pt-2" style="border-top: 1px solid #f1f5f9;">
                                <a href="<?= $detailUrl ?>" class="btn btn-sm btn-primary flex-grow-1" style="background: #1e3a8a; border: none; font-weight: 700; border-radius: 6px; padding: 8px 10px; font-size: 0.82rem; text-align: center;">
                                    <i class="fas fa-volume-up me-1"></i> View Details &amp; Audio
                                </a>
                                <a href="<?= htmlspecialchars($sch['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; padding: 8px 10px; font-size: 0.82rem;" title="Official Portal">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- SECTION 4: 4-Step Citizen Process Workflow -->
<section class="py-5" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-2" style="background: #eff6ff; color: #1d4ed8; font-weight: 700; border-radius: 20px; border: 1px solid #bfdbfe;">
                SIMPLE 4-STEP FACILITATION
            </span>
            <h2 style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
                How Our Citizen Portal Works
            </h2>
            <p style="color: #64748b; font-size: 0.95rem; max-width: 650px; margin: 0 auto;">
                Designed for rural citizens, workers, and everyday Indians to easily identify schemes and access official services.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="process-step-box">
                    <div class="process-step-num">01</div>
                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Explore Directory</h4>
                    <p style="font-size: 0.86rem; color: #475569; margin: 0; line-height: 1.5;">
                        Browse 20+ flagship schemes and citizen ID cards categorized by welfare needs.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="process-step-box">
                    <div class="process-step-num">02</div>
                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Check Eligibility</h4>
                    <p style="font-size: 0.86rem; color: #475569; margin: 0; line-height: 1.5;">
                        Use our 60-second questionnaire to verify age, income, and category qualification.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="process-step-box">
                    <div class="process-step-num">03</div>
                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Verify with OTP</h4>
                    <p style="font-size: 0.86rem; color: #475569; margin: 0; line-height: 1.5;">
                        Authenticate your mobile number safely without storing biometric fingerprints.
                    </p>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="process-step-box">
                    <div class="process-step-num">04</div>
                    <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Download or Apply</h4>
                    <p style="font-size: 0.86rem; color: #475569; margin: 0; line-height: 1.5;">
                        Instant high-definition PVC card generation or direct redirection to official ministry portals.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 5: Doorstep Rural Facilitation Section -->
<section class="py-5" style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
    <div class="container">
        <div class="p-4 p-lg-5 rounded-4" style="background: #ffffff; border: 1.5px solid #e2e8f0; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge px-3 py-2 mb-2" style="background: #fff7ed; color: #c2410c; font-weight: 700; border-radius: 20px; border: 1px solid #fed7aa;">
                        <i class="fas fa-home me-1"></i> DOORSTEP CITIZEN ASSISTANCE
                    </span>
                    <h3 style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-bottom: 10px;">
                        Doorstep Assistance for Rural Citizens &amp; Seniors
                    </h3>
                    <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;">
                        Need physical help with online forms, paper scanning, or pensioner verification? Our verified citizen executives visit your village or residence with portable digital equipment at nominal government-regulated charges.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= BASE_URL ?>/doorstep-booking.php" class="btn btn-primary rounded-pill px-4 py-2 fw-bold" style="background: #1e3a8a; border: none; font-size: 0.9rem;">
                            <i class="fas fa-calendar-check me-1"></i> Book Doorstep Executive
                        </a>
                        <a href="<?= BASE_URL ?>/contact.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-bold" style="font-size: 0.9rem;">
                            <i class="fas fa-phone-alt me-1"></i> Talk to Helpline
                        </a>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div style="border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; height: 220px;">
                        <img src="<?= BASE_URL ?>/image/doorstep-rural-facilitation.jpg" alt="Doorstep Rural Citizen Facilitation" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 6: Frequently Asked Questions (FAQ) -->
<section class="py-5" id="faq" style="background: #ffffff; border-bottom: 1px solid #e2e8f0;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-2" style="background: #eff6ff; color: #1d4ed8; font-weight: 700; border-radius: 20px; border: 1px solid #bfdbfe;">
                HELP CENTER
            </span>
            <h2 style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
                Frequently Asked Questions
            </h2>
            <p style="color: #64748b; font-size: 0.95rem; max-width: 650px; margin: 0 auto;">
                Common questions about government schemes, eligibility, digital passes, and application procedures.
            </p>
        </div>

        <div class="row g-4 align-items-center">
            <div class="col-lg-5">
                <div class="p-4 rounded-4" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 14px;">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Need Assistance?</h4>
                    <p style="font-size: 0.88rem; color: #475569; line-height: 1.5; margin-bottom: 16px;">
                        Have questions about documents or status tracking? Our citizen support desk provides free guidance.
                    </p>
                    <a href="<?= BASE_URL ?>/contact.php" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3 py-2">
                        <i class="fas fa-envelope me-1"></i> Contact Support Desk
                    </a>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="accordion" id="homeFaqAccordion">
                    
                    <div class="accordion-item mb-2" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                        <h3 class="accordion-header" id="faqH1">
                            <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqC1" aria-expanded="true" aria-controls="faqC1">
                                1. What services are available on this portal?
                            </button>
                        </h3>
                        <div id="faqC1" class="accordion-collapse collapse show" aria-labelledby="faqH1" data-bs-parent="#homeFaqAccordion">
                            <div class="accordion-body" style="font-size: 0.9rem; color: #475569; line-height: 1.6;">
                                The portal provides real-time digital pass generation for ABHA Health Card, APAAR Student ID, e-Shram Worker UAN, and Voter ID, along with assisted guidance for Aadhaar, Ration Card, PAN Card, Driving Licence, and 20+ flagship Central &amp; State welfare schemes.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item mb-2" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                        <h3 class="accordion-header" id="faqH2">
                            <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqC2" aria-expanded="false" aria-controls="faqC2">
                                2. How does the scheme eligibility checker work?
                            </button>
                        </h3>
                        <div id="faqC2" class="accordion-collapse collapse" aria-labelledby="faqH2" data-bs-parent="#homeFaqAccordion">
                            <div class="accordion-body" style="font-size: 0.9rem; color: #475569; line-height: 1.6;">
                                The eligibility checker matches your age, annual family income, state/UT, occupation (e.g. farmer, artisan, laborer, student, woman), and social category against the official statutory rules of each scheme to instantly identify qualifying benefits.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item mb-2" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                        <h3 class="accordion-header" id="faqH3">
                            <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqC3" aria-expanded="false" aria-controls="faqC3">
                                3. Is my biometric and personal data secure?
                            </button>
                        </h3>
                        <div id="faqC3" class="accordion-collapse collapse" aria-labelledby="faqH3" data-bs-parent="#homeFaqAccordion">
                            <div class="accordion-body" style="font-size: 0.9rem; color: #475569; line-height: 1.6;">
                                Yes. All communication is protected via 256-bit SSL encryption. We follow strict zero-biometric storage protocols and never store fingerprint scans, iris records, or complete Aadhaar numbers on our database servers.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item mb-2" style="border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                        <h3 class="accordion-header" id="faqH4">
                            <button class="accordion-button collapsed fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#faqC4" aria-expanded="false" aria-controls="faqC4">
                                4. How can I track my existing card generation order?
                            </button>
                        </h3>
                        <div id="faqC4" class="accordion-collapse collapse" aria-labelledby="faqH4" data-bs-parent="#homeFaqAccordion">
                            <div class="accordion-body" style="font-size: 0.9rem; color: #475569; line-height: 1.6;">
                                You can check your application status or re-download your high-resolution PVC card pass anytime using your 10-digit mobile number on our <a href="<?= BASE_URL ?>/track-status.php" class="fw-bold text-primary">Track Order Status</a> page.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 7: Citizen Feedback & Testimonials -->
<section class="py-5" style="background: #f8fafc;">
    <div class="container">
        <div class="text-center mb-5">
            <span class="badge px-3 py-2 mb-2" style="background: #eff6ff; color: #1d4ed8; font-weight: 700; border-radius: 20px; border: 1px solid #bfdbfe;">
                CITIZEN VOICES
            </span>
            <h2 style="font-size: 1.85rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">
                What Citizens Say About Our Facilitation
            </h2>
            <p style="color: #64748b; font-size: 0.95rem; max-width: 650px; margin: 0 auto;">
                Real experiences from farmers, workers, students, and senior citizens across India.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-4">
                <div class="p-4 rounded-4 h-100" style="background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div class="text-warning mb-2" style="font-size: 1.1rem;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p style="font-size: 0.9rem; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                        "Got my ABHA Health Card in under 3 minutes with verified QR code. Very simple mobile OTP process without complicated technical jargon."
                    </p>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: #eff6ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-weight: 800;">
                            RK
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a;">Rajesh Kumar</div>
                            <div style="font-size: 0.74rem; color: #64748b;">Delhi NCR • Small Business Owner</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="p-4 rounded-4 h-100" style="background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div class="text-warning mb-2" style="font-size: 1.1rem;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p style="font-size: 0.9rem; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                        "The eligibility checker clearly showed that my family qualified for both Ayushman Bharat and PM-KISAN. Direct Hindi audio narration helped my father understand."
                    </p>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: #ecfdf5; color: #047857; display: flex; align-items: center; justify-content: center; font-weight: 800;">
                            PS
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a;">Pooja Sharma</div>
                            <div style="font-size: 0.74rem; color: #64748b;">Lucknow, UP • Farmer Family</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="p-4 rounded-4 h-100" style="background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 2px 8px rgba(0,0,0,0.03);">
                    <div class="text-warning mb-2" style="font-size: 1.1rem;">
                        <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                    </div>
                    <p style="font-size: 0.9rem; color: #334155; line-height: 1.6; margin-bottom: 16px;">
                        "Generated my e-Shram UAN card and downloaded the printable PVC format immediately. The doorstep executive booking option is very thoughtful."
                    </p>
                    <div class="d-flex align-items-center gap-2 pt-2 border-top">
                        <div style="width: 38px; height: 38px; border-radius: 50%; background: #fff7ed; color: #c2410c; display: flex; align-items: center; justify-content: center; font-weight: 800;">
                            MV
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 0.9rem; color: #0f172a;">Manoj Verma</div>
                            <div style="font-size: 0.74rem; color: #64748b;">Jaipur, Rajasthan • Construction Artisan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
