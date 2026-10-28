<?php
// service.php - Citizen Services Catalog (Strict Light Theme, GIGW Compliant, Database-Driven & SEO Optimized)
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/services.php';

// Fetch all active services from DB
$allServices = get_all_services($pdo, null, null, true);

// Get distinct categories
$cat_stmt = $pdo->query("SELECT DISTINCT category FROM services WHERE is_active = 1 AND category IS NOT NULL AND category != '' ORDER BY category ASC");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

// SEO Meta Variables
$pageTitle = 'Citizen Identity & Government Services - Apply Online & Verification | ' . APP_NAME;
$metaDescription = 'Explore all digital identity cards, student IDs, worker welfare passes, and official Indian government facilitation services. Instant generation & assisted guidance.';
$metaKeywords = 'citizen services India, ABHA card online, APAAR student id, eshram card download, voter id verify, aadhaar service guide, ration card, pan card correction, driving licence, pm kisan ekyc';
$canonicalUrl = BASE_URL . '/services';
$activeNav = 'services';

include __DIR__ . '/includes/header.php';
?>

<!-- Schema.org JSON-LD Structured Data for Services Collection -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "CollectionPage",
  "name": "Citizen Identity & Government Services Directory",
  "description": "<?= htmlspecialchars($metaDescription) ?>",
  "url": "<?= BASE_URL ?>/services",
  "provider": {
    "@type": "Organization",
    "name": "<?= APP_NAME ?>",
    "url": "<?= BASE_URL ?>"
  },
  "mainEntity": {
    "@type": "ItemList",
    "numberOfItems": <?= count($allServices) ?>,
    "itemListElement": [
      <?php foreach ($allServices as $i => $s): ?>
      {
        "@type": "ListItem",
        "position": <?= $i + 1 ?>,
        "item": {
          "@type": "GovernmentService",
          "name": "<?= htmlspecialchars($s['service_name']) ?>",
          "description": "<?= htmlspecialchars($s['short_desc']) ?>",
          "url": "<?= BASE_URL ?>/service/<?= urlencode($s['slug']) ?>",
          "serviceType": "<?= ($s['service_type'] === 'api') ? 'Digital Identity Pass Generation' : 'Assisted Government Guidance' ?>",
          "provider": {
            "@type": "GovernmentOrganization",
            "name": "<?= htmlspecialchars($s['department_en'] ?? 'Government of India') ?>"
          },
          "offers": {
            "@type": "Offer",
            "price": "<?= number_format((float)$s['fee'], 2, '.', '') ?>",
            "priceCurrency": "INR"
          }
        }
      }<?= ($i < count($allServices) - 1) ? ',' : '' ?>
      <?php endforeach; ?>
    ]
  }
}
</script>

<style>
/* Strict Light Theme & Responsive Civic Styles for Services Directory */
.service-hero {
    background: linear-gradient(120deg, rgba(248, 250, 252, 0.94) 0%, rgba(241, 245, 249, 0.92) 100%), url('image/barr.png');
    background-position: center;
    background-size: cover;
    border-bottom: 1px solid #e2e8f0;
    padding: 28px 0 40px;
}
.services-catalog-wrapper {
    background: #f8fafc;
    padding: 40px 0 70px;
}
.service-catalog-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
}
.service-catalog-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.service-card-media {
    position: relative;
    height: 180px;
    background: #e2e8f0;
    overflow: hidden;
}
.service-card-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}
.service-catalog-card:hover .service-card-media img {
    transform: scale(1.04);
}
.service-card-body {
    padding: 18px 20px 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    flex: 1;
}
.service-cat-pill {
    background: #f1f5f9;
    color: #475569;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 12px;
    display: inline-block;
    text-transform: uppercase;
}
.service-badge-api {
    background: rgba(5, 150, 105, 0.95);
    color: #ffffff;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    backdrop-filter: blur(4px);
}
.service-badge-guide {
    background: rgba(30, 64, 175, 0.95);
    color: #ffffff;
    font-size: 0.74rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    backdrop-filter: blur(4px);
}
.cat-filter-btn {
    border-radius: 20px;
    font-weight: 700;
    font-size: 0.85rem;
    padding: 7px 16px;
    transition: all 0.2s ease;
}
.filter-tabs-scroll {
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    padding-bottom: 6px;
}
.service-search-box {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 30px;
    padding: 10px 20px;
    font-size: 0.95rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
    transition: border-color 0.2s ease;
}
.service-search-box:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    outline: none;
}
@media (max-width: 767px) {
    .service-hero { padding: 20px 0 30px; }
    .service-hero h1 { font-size: 1.7rem !important; }
    .service-card-media { height: 160px; }
}
</style>

<!-- Hero Section with Breadcrumb Pattern -->
<section class="service-hero">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb" style="margin-bottom: 14px;">
                <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home mr-1"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="all-scheme.php">Government Schemes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Citizen Services</li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge mb-2 px-3 py-2" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.8rem; font-weight: 700; border-radius: 20px;">
                    <i class="fas fa-shield-alt mr-1"></i> DIRECT CITIZEN FACILITATION &amp; IDENTITY CARDS
                </span>
                <h1 style="color: #0f172a; font-size: 2.1rem; font-weight: 800; margin-bottom: 8px;">
                    Citizen Identity &amp; Government Services <span style="font-size: 1.35rem; color: #475569; font-weight: 600;">(नागरिक सेवाएं एवं पहचान पत्र)</span>
                </h1>
                <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; max-width: 820px; margin: 0;">
                    Instant real-time digital pass generation for supported welfare cards (ABHA, APAAR, e-Shram, Voter ID) and step-by-step assisted guidance with prerequisite checklists for statutory government portals.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-inline-flex flex-column align-items-lg-end gap-2">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04); display: inline-block;">
                        <div style="font-size: 1.7rem; font-weight: 800; color: #1e3a8a; line-height: 1;"><?= count($allServices) ?></div>
                        <div style="font-size: 0.74rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Active Citizen Services</div>
                    </div>
                    <div>
                        <a href="track-status.php" class="btn btn-sm btn-outline-primary" style="font-weight: 700; border-radius: 20px; padding: 6px 16px;">
                            <i class="fas fa-search me-1"></i> Track Order Status
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Services Directory Section (Strict Light Theme) -->
<section class="services-catalog-wrapper">
    <div class="container">

        <!-- Toolbar: Search Bar and Quick Type Buttons -->
        <div class="row align-items-center mb-4 g-3">
            <div class="col-md-6 col-lg-5">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute top-50 translate-middle-y text-muted" style="left: 18px;"></i>
                    <input type="text" id="serviceSearchInput" class="form-control service-search-box ps-5" placeholder="Search by service name, e.g. ABHA, e-Shram, Voter, PAN...">
                </div>
            </div>

            <div class="col-md-6 col-lg-7 text-md-end">
                <div class="btn-group" role="group" id="serviceTypeToggle">
                    <button type="button" class="btn btn-sm btn-primary active cat-filter-btn" data-type="all">
                        All Services (<?= count($allServices) ?>)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary cat-filter-btn" data-type="api">
                        <i class="fas fa-bolt me-1 text-warning"></i> Instant Digital PVC
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary cat-filter-btn" data-type="redirect">
                        <i class="fas fa-compass me-1"></i> Assisted Guides
                    </button>
                </div>
            </div>
        </div>

        <!-- Category Filter Pills Bar -->
        <div class="filter-tabs-scroll d-flex gap-2 mb-4 pb-2 border-bottom">
            <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 py-1 fw-bold cat-pill-btn active" data-category="all">
                All Categories
            </button>
            <?php foreach ($categories as $cat): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-bold cat-pill-btn" data-category="<?= htmlspecialchars($cat) ?>">
                    <?= htmlspecialchars($cat) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Services Grid -->
        <div class="row g-4" id="servicesGridContainer">
            <?php foreach ($allServices as $srv): 
                $is_api = ($srv['service_type'] === 'api');
                $img_src = !empty($srv['image_url']) ? $srv['image_url'] : 'image/services/1.jpg';
                $clean_fee = (float) $srv['fee'];
                $slug_url = 'service/' . urlencode($srv['slug']);
                $slug_fallback = 'service-detail.php?slug=' . urlencode($srv['slug']);
            ?>
                <div class="col-12 col-md-6 col-lg-4 service-card-col" 
                     data-category="<?= htmlspecialchars($srv['category']) ?>" 
                     data-type="<?= htmlspecialchars($srv['service_type']) ?>"
                     data-title="<?= strtolower(htmlspecialchars($srv['service_name'] . ' ' . ($srv['service_name_hi'] ?? '') . ' ' . $srv['slug'] . ' ' . ($srv['department_en'] ?? ''))) ?>">
                    
                    <div class="service-catalog-card">
                        
                        <!-- Card Media Box -->
                        <div class="service-card-media">
                            <img src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($srv['service_name']) ?>" loading="lazy">
                            
                            <!-- Badges Overlay -->
                            <div class="position-absolute top-0 start-0 m-2">
                                <?php if ($is_api): ?>
                                    <span class="service-badge-api">
                                        <i class="fas fa-bolt me-1"></i> Instant Digital Pass (<?= format_inr($clean_fee) ?>)
                                    </span>
                                <?php else: ?>
                                    <span class="service-badge-guide">
                                        <i class="fas fa-compass me-1"></i> Assisted Guide (Free)
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="position-absolute bottom-0 end-0 m-2">
                                <span class="service-cat-pill">
                                    <i class="<?= htmlspecialchars($srv['icon'] ?? 'fas fa-id-card') ?> me-1"></i> <?= htmlspecialchars($srv['category']) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="service-card-body">
                            <div>
                                <!-- Department Tag -->
                                <div style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">
                                    <i class="fas fa-landmark me-1"></i> <?= htmlspecialchars($srv['department_en'] ?? 'Government of India') ?>
                                </div>

                                <!-- Service Titles -->
                                <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin-bottom: 2px; line-height: 1.35;">
                                    <a href="<?= $slug_url ?>" style="color: inherit; text-decoration: none;">
                                        <?= htmlspecialchars($srv['service_name']) ?>
                                    </a>
                                </h3>

                                <?php if (!empty($srv['service_name_hi'])): ?>
                                    <div style="font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 8px;">
                                        <?= htmlspecialchars($srv['service_name_hi']) ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Turnaround & Key Tag -->
                                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                    <span class="badge" style="background: #ecfdf5; color: #065f46; border: 1px solid #bbf7d0; font-size: 0.72rem; font-weight: 700; border-radius: 8px;">
                                        <i class="fas fa-clock me-1"></i> <?= htmlspecialchars($srv['turnaround_time'] ?? 'Instant') ?>
                                    </span>
                                    <span class="badge" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-size: 0.72rem; border-radius: 8px;">
                                        <?= htmlspecialchars($srv['badge_text'] ?? 'Verified') ?>
                                    </span>
                                </div>

                                <!-- Short Description -->
                                <p style="color: #475569; font-size: 0.88rem; line-height: 1.5; margin-bottom: 14px;">
                                    <?= htmlspecialchars($srv['short_desc']) ?>
                                </p>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex gap-2 pt-2" style="border-top: 1px solid #f1f5f9;">
                                <a href="<?= $slug_url ?>" class="btn btn-sm btn-primary flex-grow-1" style="background: #1e3a8a; border: none; font-weight: 700; border-radius: 8px; padding: 8px 12px; font-size: 0.84rem; text-align: center;">
                                    <?php if ($is_api): ?>
                                        <i class="fas fa-bolt me-1"></i> Apply Now (<?= format_inr($clean_fee) ?>)
                                    <?php else: ?>
                                        <i class="fas fa-arrow-right me-1"></i> Check Guidelines &rarr;
                                    <?php endif; ?>
                                </a>

                                <a href="<?= htmlspecialchars($srv['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary" style="border-radius: 8px; padding: 8px 11px; font-size: 0.84rem;" title="Visit Official Portal">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- No Results Fallback -->
        <div id="noServicesFound" class="d-none text-center py-5">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px 20px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4 style="font-weight: 800; color: #0f172a; margin-bottom: 6px;">No Services Found</h4>
                <p class="text-muted small mb-3">No citizen service matches your current filter or search criteria.</p>
                <button type="button" id="resetServicesFilterBtn" class="btn btn-sm btn-primary rounded-pill px-4 py-2 fw-bold">
                    Reset All Filters
                </button>
            </div>
        </div>

        <!-- Civic Information & Transparency Box -->
        <div class="mt-5 p-4 rounded-4" style="background: #ffffff; border: 1.5px solid #e2e8f0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);">
            <div class="row align-items-center g-3">
                <div class="col-md-8">
                    <div class="d-flex align-items-start gap-3">
                        <div style="width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                                GSP Citizen Security &amp; Data Privacy Commitment
                            </h4>
                            <p style="font-size: 0.85rem; color: #475569; margin: 0; line-height: 1.5;">
                                This portal operates as an independent digital public facilitator. All transactions are protected via 256-bit SSL encryption. We adhere strictly to zero-biometric retention norms and only process official demographic inputs authorized directly by citizen consent.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="doorstep-booking.php" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-bold" style="font-size: 0.84rem;">
                        <i class="fas fa-home me-1"></i> Book Doorstep Facilitation
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Client-side Interactive Instant Search & Filtering Script -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("serviceSearchInput");
    const typeButtons = document.querySelectorAll("#serviceTypeToggle button");
    const categoryButtons = document.querySelectorAll(".cat-pill-btn");
    const cards = document.querySelectorAll(".service-card-col");
    const noResults = document.getElementById("noServicesFound");
    const resetBtn = document.getElementById("resetServicesFilterBtn");

    let currentType = "all";
    let currentCategory = "all";
    let currentSearch = "";

    function filterCards() {
        let visibleCount = 0;

        cards.forEach(card => {
            const cardCat = card.getAttribute("data-category");
            const cardType = card.getAttribute("data-type");
            const cardTitle = card.getAttribute("data-title");

            const matchesType = (currentType === "all" || cardType === currentType);
            const matchesCategory = (currentCategory === "all" || cardCat.toLowerCase() === currentCategory.toLowerCase());
            const matchesSearch = (currentSearch === "" || cardTitle.includes(currentSearch));

            if (matchesType && matchesCategory && matchesSearch) {
                card.style.display = "";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });

        if (visibleCount === 0) {
            noResults.classList.remove("d-none");
        } else {
            noResults.classList.add("d-none");
        }
    }

    // Type Toggle
    typeButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            typeButtons.forEach(b => {
                b.classList.remove("active", "btn-primary");
                b.classList.add("btn-outline-primary");
            });
            this.classList.add("active", "btn-primary");
            this.classList.remove("btn-outline-primary");

            currentType = this.getAttribute("data-type");
            filterCards();
        });
    });

    // Category Buttons
    categoryButtons.forEach(btn => {
        btn.addEventListener("click", function () {
            categoryButtons.forEach(b => {
                b.classList.remove("active", "btn-dark");
                b.classList.add("btn-outline-secondary");
            });
            this.classList.add("active", "btn-dark");
            this.classList.remove("btn-outline-secondary");

            currentCategory = this.getAttribute("data-category");
            filterCards();
        });
    });

    // Search Input
    if (searchInput) {
        searchInput.addEventListener("input", function () {
            currentSearch = this.value.trim().toLowerCase();
            filterCards();
        });
    }

    // Reset Filter Button
    if (resetBtn) {
        resetBtn.addEventListener("click", function () {
            if (searchInput) searchInput.value = "";
            currentSearch = "";
            currentType = "all";
            currentCategory = "all";

            typeButtons.forEach(b => {
                b.classList.remove("active", "btn-primary");
                b.classList.add("btn-outline-primary");
                if (b.getAttribute("data-type") === "all") {
                    b.classList.add("active", "btn-primary");
                    b.classList.remove("btn-outline-primary");
                }
            });

            categoryButtons.forEach(b => {
                b.classList.remove("active", "btn-dark");
                b.classList.add("btn-outline-secondary");
                if (b.getAttribute("data-category") === "all") {
                    b.classList.add("active", "btn-dark");
                    b.classList.remove("btn-outline-secondary");
                }
            });

            filterCards();
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
