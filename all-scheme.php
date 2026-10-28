<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/schemes.php';

$allSchemes = get_all_schemes();

$pageTitle = 'Major Government Schemes (Central Sector & Centrally Sponsored) - ' . APP_NAME;
$metaDescription = 'Explore major flagship Indian government schemes categorized into Central Sector and Centrally Sponsored Schemes as per official classifications.';
$activeNav = 'schemes';

include __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb & Hero (Strict Light Theme with Banner Background) -->
<section class="detail-hero" style="background: linear-gradient(120deg, rgba(248, 250, 252, 0.93) 0%, rgba(241, 245, 249, 0.92) 100%), url('image/barr.png'); background-position: center; background-size: cover; border-bottom: 1px solid #e2e8f0; padding: 28px 0 40px;">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb" style="margin-bottom: 16px;">
                <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home mr-1"></i> Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Government Schemes (सरकारी योजनाएं)</li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-9">
                <span class="badge mb-2 px-3 py-2" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.8rem; font-weight: 700; border-radius: 20px;">
                    <i class="fas fa-landmark mr-1"></i> VAJIRAM &amp; RAVI 2026 &amp; VISIONIAS PT 365 CLASSIFICATION
                </span>
                <h1 style="color: #0f172a; font-size: 2.1rem; font-weight: 800; margin-bottom: 8px;">
                    Major Government Schemes <span style="font-size: 1.4rem; color: #475569; font-weight: 600;">(प्रमुख सरकारी योजनाएँ)</span>
                </h1>
                <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; max-width: 820px; margin: 0;">
                    Complete information on flagship Indian welfare initiatives. Schemes are categorized under constitutional and financial norms into <strong>Central Sector Schemes (100% Central Funded)</strong> and <strong>Centrally Sponsored Schemes (CSS - Shared Centre + State Funding)</strong>.
                </p>
            </div>
            <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; display: inline-block; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size: 1.8rem; font-weight: 800; color: #1e3a8a; line-height: 1;"><?= count($allSchemes) ?></div>
                    <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Active Flagship Schemes</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Schemes Section (Light Theme) -->
<section class="schemes-light-wrapper">
    <div class="container">

        <!-- 2-Box Explainer Banner: Central Sector vs Centrally Sponsored -->
        <div class="row funding-explainer-row">
            <div class="col-md-6 mb-3 mb-md-0">
                <div class="funding-explainer-card sector-central">
                    <div>
                        <div class="explainer-title">
                            <i class="fas fa-shield-alt" style="color: #2563eb;"></i>
                            <span>Central Sector Schemes (केंद्रीय क्षेत्र योजनाएं)</span>
                        </div>
                        <p class="explainer-desc">
                            <strong>100% funded and directly implemented by the Central Government</strong> through its ministries. The financial burden is entirely borne by the Union Government with direct DBT transfers to beneficiaries.
                        </p>
                    </div>
                    <span class="explainer-pill pill-central">
                        <i class="fas fa-check-circle mr-1"></i> 100% Union Government Funded (e.g. PM-KISAN, APY, Mudra, PM SVANidhi)
                    </span>
                </div>
            </div>

            <div class="col-md-6">
                <div class="funding-explainer-card sector-sponsored">
                    <div>
                        <div class="explainer-title">
                            <i class="fas fa-hands-helping" style="color: #059669;"></i>
                            <span>Centrally Sponsored Schemes (केंद्र प्रायोजित योजनाएं - CSS)</span>
                        </div>
                        <p class="explainer-desc">
                            <strong>Implemented by State Governments with shared funding</strong> between the Centre and States (usually 60:40 for general states and 90:10 for Himalayan/North-Eastern states).
                        </p>
                    </div>
                    <span class="explainer-pill pill-sponsored">
                        <i class="fas fa-check-circle mr-1"></i> Shared Funding (Centre: 60% / State: 40% - e.g. Ayushman Bharat, PMAY, MGNREGA)
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls Panel -->
        <div class="schemes-filter-panel">
            <div class="row align-items-center mb-3">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <span class="filter-heading-label"><i class="fas fa-filter mr-1"></i> Filter by Funding Type (फंडिंग का प्रकार)</span>
                    <div class="filter-btn-group" id="fundingFilterGroup">
                        <button type="button" class="scheme-filter-btn active" data-funding="all">
                            <i class="fas fa-th-large"></i> All Schemes (<?= count($allSchemes) ?>)
                        </button>
                        <button type="button" class="scheme-filter-btn" data-funding="central_sector">
                            <i class="fas fa-landmark"></i> Central Sector (100% Central)
                        </button>
                        <button type="button" class="scheme-filter-btn" data-funding="centrally_sponsored">
                            <i class="fas fa-handshake"></i> Centrally Sponsored (CSS)
                        </button>
                    </div>
                </div>

                <div class="col-lg-6">
                    <span class="filter-heading-label"><i class="fas fa-search mr-1"></i> Quick Search by Name or Ministry</span>
                    <div class="search-input-wrapper">
                        <i class="fas fa-search"></i>
                        <input type="text" id="schemeSearchInput" class="form-control schemes-search-input" 
                               placeholder="Search scheme name (e.g., Kisan, Ayushman, Awas, Pension)...">
                    </div>
                </div>
            </div>

            <div class="row pt-2" style="border-top: 1px solid #f1f5f9;">
                <div class="col-md-6 mb-3 mb-md-0">
                    <label class="filter-heading-label" for="stateFilterSelect">
                        <i class="fas fa-map-marker-alt mr-1"></i> Filter by State / Scope (राज्य अनुसार फ़िल्टर)
                    </label>
                    <select id="stateFilterSelect" class="form-select schemes-select-input">
                        <option value="all">All States &amp; UTs (All-India Central &amp; CSS)</option>
                        <option value="Uttar Pradesh">Uttar Pradesh (उत्तर प्रदेश)</option>
                        <option value="Bihar">Bihar (बिहार)</option>
                        <option value="Rajasthan">Rajasthan (राजस्थान)</option>
                        <option value="Madhya Pradesh">Madhya Pradesh (मध्य प्रदेश)</option>
                        <option value="Maharashtra">Maharashtra (महाराष्ट्र)</option>
                        <option value="West Bengal">West Bengal (पश्चिम बंगाल)</option>
                        <option value="Haryana">Haryana (हरियाणा)</option>
                        <option value="Punjab">Punjab (पंजाब)</option>
                        <option value="Gujarat">Gujarat (गुजरात)</option>
                        <option value="Odisha">Odisha (ओडिशा)</option>
                        <option value="Jharkhand">Jharkhand (झारखंड)</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="filter-heading-label" for="categoryFilterSelect">
                        <i class="fas fa-layer-group mr-1"></i> Filter by Sector / Category (क्षेत्र / विभाग)
                    </label>
                    <select id="categoryFilterSelect" class="form-select schemes-select-input">
                        <option value="all">All Sectors (सभी क्षेत्र)</option>
                        <option value="agriculture">Agriculture &amp; Farmers Welfare (कृषि)</option>
                        <option value="healthcare">Healthcare &amp; Wellness (स्वास्थ्य)</option>
                        <option value="housing">Housing &amp; Shelter (आवास)</option>
                        <option value="employment">Employment &amp; Livelihood (रोजगार)</option>
                        <option value="social_security">Social Security &amp; Pension (पेंशन व सुरक्षा)</option>
                        <option value="financial_services">Financial &amp; Micro-Credit (ऋण एवं वित्त)</option>
                        <option value="water_sanitation">Water &amp; Sanitation (जल जीवन)</option>
                        <option value="energy_utilities">Renewable Energy &amp; Power (सौर ऊर्जा एवं बिजली)</option>
                        <option value="women_child">Women &amp; Child Development (महिला एवं बाल)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Scheme Results Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h2 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 0;">
                    Available Welfare Schemes <span style="font-size: 1rem; color: #64748b; font-weight: 600;">(उपलब्ध योजनाएं)</span>
                </h2>
                <div style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">
                    Showing <span id="displayedCount" style="font-weight: 700; color: #1e3a8a;"><?= count($allSchemes) ?></span> of <?= count($allSchemes) ?> schemes
                </div>
            </div>

            <button type="button" id="btnResetFilters" class="btn btn-sm btn-outline-secondary" style="border-radius: 20px; font-weight: 600; display: none;">
                <i class="fas fa-undo mr-1"></i> Reset All Filters
            </button>
        </div>

        <!-- Schemes Grid -->
        <div class="row g-4" id="schemesGrid">
            <?php foreach ($allSchemes as $slug => $scheme): ?>
                <?php
                    $isCentralSector = ($scheme['funding_type'] === 'central_sector');
                    $statesListStr = implode(',', $scheme['applicable_states']);
                    $searchable = strtolower($scheme['title_en'] . ' ' . $scheme['title_hi'] . ' ' . $scheme['tagline_en'] . ' ' . $scheme['ministry_en'] . ' ' . $scheme['category_name_en']);
                ?>
                <div class="col-md-6 col-lg-4 scheme-card-item"
                     data-funding="<?= htmlspecialchars($scheme['funding_type']) ?>"
                     data-category="<?= htmlspecialchars($scheme['category']) ?>"
                     data-states="<?= htmlspecialchars($statesListStr) ?>"
                     data-search="<?= htmlspecialchars($searchable) ?>">
                    
                    <div class="scheme-white-card type-<?= htmlspecialchars($scheme['funding_type']) ?>">
                        <div>
                            <!-- Scheme Relevant Image -->
                            <?php 
                                $cardImg = !empty($scheme['image']) ? $scheme['image'] : 'image/schemes/default-scheme-banner.jpg'; 
                            ?>
                            <div style="position: relative; border-radius: 10px; overflow: hidden; height: 165px; margin-bottom: 14px; background: #f1f5f9; border: 1px solid #e2e8f0;">
                                <img src="<?= htmlspecialchars($cardImg) ?>" alt="<?= htmlspecialchars($scheme['title_en']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                <div style="position: absolute; top: 10px; left: 10px;">
                                    <?php if ($isCentralSector): ?>
                                        <span class="scheme-funding-badge badge-central" style="background: rgba(239, 246, 255, 0.95); backdrop-filter: blur(4px); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <i class="fas fa-landmark"></i> Central Sector (100%)
                                        </span>
                                    <?php else: ?>
                                        <span class="scheme-funding-badge badge-sponsored" style="background: rgba(236, 253, 245, 0.95); backdrop-filter: blur(4px); box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                            <i class="fas fa-handshake"></i> Centrally Sponsored
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div style="position: absolute; bottom: 8px; right: 8px;">
                                    <span class="badge" style="background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 0.72rem; padding: 4px 8px; border-radius: 12px;">
                                        <i class="fas <?= htmlspecialchars($scheme['icon']) ?>"></i> <?= htmlspecialchars($scheme['category_name_en'] ?? $scheme['category']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Ministry Name -->
                            <span class="scheme-ministry-name">
                                <i class="fas fa-building mr-1"></i> <?= htmlspecialchars($scheme['ministry_en']) ?>
                            </span>

                            <!-- Title English & Hindi -->
                            <h3 class="scheme-card-title"><?= htmlspecialchars($scheme['title_en']) ?></h3>
                            <div class="scheme-card-hindi-title"><?= htmlspecialchars($scheme['title_hi']) ?></div>

                            <!-- Tagline / Summary -->
                            <p class="scheme-card-tagline"><?= htmlspecialchars($scheme['tagline_en']) ?></p>

                            <!-- Quick 2 Bullets from Benefits -->
                            <ul class="scheme-quick-bullets">
                                <?php for ($b = 0; $b < min(2, count($scheme['benefits_en'])); $b++): ?>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span><?= htmlspecialchars($scheme['benefits_en'][$b]) ?></span>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </div>

                        <!-- Card Footer with Action Buttons -->
                        <div class="scheme-card-footer">
                            <a href="<?= scheme_url($slug) ?>" class="btn-card-detail">
                                <i class="fas fa-info-circle"></i> Details &amp; Audio (विवरण)
                            </a>
                            <a href="<?= htmlspecialchars($scheme['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn-card-official" title="Official Government Portal">
                                <i class="fas fa-external-link-alt"></i> Official
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- No Results Fallback -->
        <div id="noResultsBox" class="text-center py-5 d-none">
            <div style="background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 16px; padding: 40px 20px; max-width: 520px; margin: 0 auto;">
                <i class="fas fa-search" style="font-size: 2.5rem; color: #94a3b8; margin-bottom: 14px;"></i>
                <h4 style="font-weight: 700; color: #1e293b;">No matching schemes found</h4>
                <p style="color: #64748b; font-size: 0.9rem;">No government scheme matched your current filter or search criteria. Try selecting another state, sector, or clearing the search text.</p>
                <button type="button" id="btnResetFiltersEmpty" class="btn btn-primary" style="background: #1e3a8a; border: none; border-radius: 20px; padding: 8px 20px; font-weight: 600;">
                    <i class="fas fa-undo mr-1"></i> Reset Filters
                </button>
            </div>
        </div>

        <!-- Citizen Assistance Callout (Light Theme) -->
        <div class="mt-5 p-4 rounded-4" style="background: #ffffff; border: 1px solid #e2e8f0; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.03);">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 50px; height: 50px; border-radius: 12px; background: #eff6ff; color: #1d4ed8; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1.15rem; font-weight: 700; color: #0f172a; margin-bottom: 3px;">
                                Need Help Applying or Getting Identity Proof Printed?
                            </h4>
                            <p style="color: #64748b; font-size: 0.88rem; margin: 0;">
                                Jan Seva Facilitation Portal assists villagers, farmers, and daily-wage workers with doorstep PVC card generation, Aadhaar PVC, Ayushman Card printing, and direct scheme navigation at minimal charges.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="card-form.php?type=ayushman" class="btn btn-sm" style="background: #059669; color: #fff; font-weight: 700; border-radius: 20px; padding: 10px 18px;">
                        <i class="fas fa-print mr-1"></i> Generate Plastic Card Now
                    </a>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- Interactive Filter Script -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const fundingButtons = document.querySelectorAll('#fundingFilterGroup .scheme-filter-btn');
    const stateSelect = document.getElementById('stateFilterSelect');
    const categorySelect = document.getElementById('categoryFilterSelect');
    const searchInput = document.getElementById('schemeSearchInput');
    const schemeCards = document.querySelectorAll('.scheme-card-item');
    const displayedCount = document.getElementById('displayedCount');
    const noResultsBox = document.getElementById('noResultsBox');
    const btnResetFilters = document.getElementById('btnResetFilters');
    const btnResetFiltersEmpty = document.getElementById('btnResetFiltersEmpty');

    let currentFunding = 'all';
    let currentState = 'all';
    let currentCategory = 'all';
    let currentSearch = '';

    function applyFilters() {
        let visibleCount = 0;
        let isFiltered = (currentFunding !== 'all' || currentState !== 'all' || currentCategory !== 'all' || currentSearch.length > 0);

        schemeCards.forEach(card => {
            const cardFunding = card.getAttribute('data-funding') || '';
            const cardCategory = card.getAttribute('data-category') || '';
            const cardStates = card.getAttribute('data-states') || '';
            const cardSearch = card.getAttribute('data-search') || '';

            // Funding match
            const matchFunding = (currentFunding === 'all' || cardFunding === currentFunding);

            // Category match
            const matchCategory = (currentCategory === 'all' || cardCategory === currentCategory);

            // State match: 'All States & UTs' covers all, otherwise check comma-separated list
            let matchState = false;
            if (currentState === 'all') {
                matchState = true;
            } else {
                matchState = cardStates.includes('All States & UTs') || cardStates.includes(currentState);
            }

            // Search query match
            let matchSearch = true;
            if (currentSearch.trim() !== '') {
                matchSearch = cardSearch.includes(currentSearch.toLowerCase());
            }

            if (matchFunding && matchCategory && matchState && matchSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        displayedCount.textContent = visibleCount;

        if (visibleCount === 0) {
            noResultsBox.classList.remove('d-none');
        } else {
            noResultsBox.classList.add('d-none');
        }

        if (isFiltered) {
            btnResetFilters.style.display = 'inline-block';
        } else {
            btnResetFilters.style.display = 'none';
        }
    }

    // Funding buttons click
    fundingButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            fundingButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFunding = this.getAttribute('data-funding');
            applyFilters();
        });
    });

    // State select change
    if (stateSelect) {
        stateSelect.addEventListener('change', function () {
            currentState = this.value;
            applyFilters();
        });
    }

    // Category select change
    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            currentCategory = this.value;
            applyFilters();
        });
    }

    // Search input
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            currentSearch = this.value.trim();
            applyFilters();
        });
    }

    // Reset filters
    function resetAll() {
        currentFunding = 'all';
        currentState = 'all';
        currentCategory = 'all';
        currentSearch = '';

        fundingButtons.forEach(b => {
            if (b.getAttribute('data-funding') === 'all') {
                b.classList.add('active');
            } else {
                b.classList.remove('active');
            }
        });

        if (stateSelect) stateSelect.value = 'all';
        if (categorySelect) categorySelect.value = 'all';
        if (searchInput) searchInput.value = '';

        applyFilters();
    }

    if (btnResetFilters) btnResetFilters.addEventListener('click', resetAll);
    if (btnResetFiltersEmpty) btnResetFiltersEmpty.addEventListener('click', resetAll);
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
