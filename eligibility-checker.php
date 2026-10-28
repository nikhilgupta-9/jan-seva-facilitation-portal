<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/schemes.php';

// Fetch all active schemes from the master database
$allSchemes = get_all_schemes();

$pageTitle = 'Government Schemes Eligibility Checker (पात्रता जांचें) - ' . APP_NAME;
$metaDescription = 'Find government schemes you qualify for by answering a few simple questions. Evaluates Central Sector and Centrally Sponsored Schemes.';
$activeNav = 'eligibility';

include __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs & Hero with Background Image Pattern (Strict Light Theme) -->
<section class="detail-hero" style="background: linear-gradient(120deg, rgba(248, 250, 252, 0.94) 0%, rgba(241, 245, 249, 0.92) 100%), url('image/barr.png'); background-position: center; background-size: cover; border-bottom: 1px solid #e2e8f0; padding: 28px 0 40px;">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb" style="margin-bottom: 14px;">
                <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home mr-1"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="all-scheme.php">Government Schemes</a></li>
                <li class="breadcrumb-item active" aria-current="page">Eligibility Checker (पात्रता जांचें)</li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-9">
                <span class="badge mb-2 px-3 py-2" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.82rem; font-weight: 700; border-radius: 20px;">
                    <i class="fas fa-user-check mr-1"></i> DIRECT CITIZEN WELFARE ASSESSMENT
                </span>
                <h1 style="color: #0f172a; font-size: 2.1rem; font-weight: 800; margin-bottom: 8px;">
                    Check Your Eligibility <span style="font-size: 1.35rem; color: #475569; font-weight: 600;">(सरकारी योजनाओं की पात्रता जांचें)</span>
                </h1>
                <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; max-width: 820px; margin: 0;">
                    Find out which flagship Indian government schemes you and your family qualify for. Our system evaluates official criteria from the <strong>Central Schemes Directory</strong> to instantly identify financial, housing, health, and pension benefits.
                </p>
            </div>
            <div class="col-lg-3 text-lg-end mt-3 mt-lg-0">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; display: inline-block; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <div style="font-size: 1.7rem; font-weight: 800; color: #059669; line-height: 1;"><?= count($allSchemes) ?></div>
                    <div style="font-size: 0.74rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Flagship Schemes Analyzed</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Interactive Eligibility Checker Section -->
<section class="eligibility-section" style="background: #f8fafc; padding: 50px 20px 70px;">
  <div class="eligibility-container">

    <!-- Left Content Column -->
    <div class="eligibility-info">
      <span class="eligibility-tag">
        <i class="fa-solid fa-clipboard-check"></i>
        INSTANT SCHEME MATCHING ENGINE
      </span>

      <h2>Find Government Schemes You <span>Qualify For</span></h2>

      <p>
        Fill in your basic profile details below. Our system matches your age, state, occupation, and income against the official eligibility rules of 20+ flagship Central and State schemes.
      </p>

      <div class="eligibility-benefits">
        <div class="eligibility-benefit">
          <span class="eligibility-icon" style="background: #eff6ff; color: #2563eb;">
            <i class="fa-solid fa-user-check"></i>
          </span>
          <div>
            <h4>Personalized Scheme Matching</h4>
            <p>Direct match for farmers, artisans, laborers, women, and students.</p>
          </div>
        </div>

        <div class="eligibility-benefit">
          <span class="eligibility-icon" style="background: #ecfdf5; color: #059669;">
            <i class="fa-solid fa-shield-alt"></i>
          </span>
          <div>
            <h4>Central &amp; State Schemes Separation</h4>
            <p>Clear classification into 100% Central DBT and Shared (CSS) schemes.</p>
          </div>
        </div>

        <div class="eligibility-benefit">
          <span class="eligibility-icon" style="background: #fff7ed; color: #ea580c;">
            <i class="fa-solid fa-file-invoice-dollar"></i>
          </span>
          <div>
            <h4>Direct Benefit &amp; Application Guide</h4>
            <p>See exact financial assistance amount, required documents, and official links.</p>
          </div>
        </div>
      </div>

      <div class="eligibility-note" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 24px;">
        <i class="fa-solid fa-circle-info" style="color: #2563eb; font-size: 1.1rem;"></i>
        <span><strong>Citizen Privacy Guarantee:</strong> No Aadhaar card number, bank account details, or OTP required. Results are based on general demographic rules.</span>
      </div>
    </div>

    <!-- Right Profile Intake Form Card -->
    <div class="eligibility-card" style="box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05); border: 1px solid #e2e8f0;">
      <div class="eligibility-card-header">
        <div>
          <span class="eligibility-step" style="color: #1e3a8a;">CITIZEN PROFILE DETAILS</span>
          <h3 style="color: #0f172a; font-weight: 800; font-size: 1.25rem;">Enter Your Details</h3>
          <p>Provide accurate information to get matched schemes.</p>
        </div>
        <div class="eligibility-header-icon" style="background: #eff6ff; color: #1e3a8a;">
          <i class="fa-solid fa-file-circle-check"></i>
        </div>
      </div>

      <form id="eligibilityForm">

        <!-- Age Field -->
        <div class="eligibility-field">
          <label for="citizenAge">Your Age (आपकी आयु) <span>*</span></label>
          <div class="eligibility-input-wrap">
            <i class="fa-solid fa-calendar-days"></i>
            <input type="number" id="citizenAge" placeholder="Enter age (e.g. 35)" min="1" max="110" required>
          </div>
        </div>

        <!-- State Field -->
        <div class="eligibility-field">
          <label for="citizenState">State / Union Territory (राज्य / केंद्र शासित प्रदेश) <span>*</span></label>
          <div class="eligibility-input-wrap">
            <i class="fa-solid fa-location-dot"></i>
            <select id="citizenState" required>
              <option value="">Select your state</option>
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
              <option value="Chhattisgarh">Chhattisgarh (छत्तीसगढ़)</option>
              <option value="Delhi">Delhi (दिल्ली)</option>
              <option value="Uttarakhand">Uttarakhand (उत्तराखंड)</option>
              <option value="Himachal Pradesh">Himachal Pradesh (हिमाचल प्रदेश)</option>
              <option value="Assam">Assam (असम)</option>
              <option value="Karnataka">Karnataka (कर्नाटक)</option>
              <option value="Kerala">Kerala (केरल)</option>
              <option value="Tamil Nadu">Tamil Nadu (तमिलनाडु)</option>
              <option value="Andhra Pradesh">Andhra Pradesh (आंध्र प्रदेश)</option>
              <option value="Telangana">Telangana (तेलंगाना)</option>
              <option value="Jammu and Kashmir">Jammu and Kashmir (जम्मू और कश्मीर)</option>
              <option value="Goa">Goa (गोवा)</option>
              <option value="Tripura">Tripura (त्रिपुरा)</option>
              <option value="All States & UTs">Other State / UT (अन्य राज्य)</option>
            </select>
          </div>
        </div>

        <!-- Occupation Field -->
        <div class="eligibility-field">
          <label for="citizenOccupation">Occupation / Profession (पेशा / कार्य क्षेत्र) <span>*</span></label>
          <div class="eligibility-input-wrap">
            <i class="fa-solid fa-briefcase"></i>
            <select id="citizenOccupation" required>
              <option value="all">Any / General Citizen (सामान्य नागरिक)</option>
              <option value="farmer">Farmer / Agri Laborer (किसान / खेतिहर मजदूर / पशुपालक)</option>
              <option value="artisan">Traditional Artisan / Craftsperson (विश्वकर्मा कारीगर - बढ़ई, लोहार, दर्जी, मोची आदि)</option>
              <option value="street_vendor">Street Vendor / Hawker (रेहड़ी-पटरी / ठेला / फुटपाथ विक्रेता)</option>
              <option value="unorganized">Daily Wage / Construction Laborer (असंगठित मजदूर / दिहाड़ी मजदूर / रिक्शा चालक)</option>
              <option value="business">Small Business Owner / Shopkeeper (छोटा व्यवसायी / दुकानदार)</option>
              <option value="student">Student / Youth seeking skills (छात्र / युवा / कौशल प्रशिक्षणार्थी)</option>
              <option value="homemaker">Rural Woman / SHG Member (महिला / स्वयं सहायता समूह सदस्य)</option>
              <option value="senior">Senior Citizen / Pensioner (वरिष्ठ नागरिक / 60+ वर्ष)</option>
            </select>
          </div>
        </div>

        <!-- Annual Family Income -->
        <div class="eligibility-field">
          <label for="familyIncome">Annual Family Income (वार्षिक पारिवारिक आय) <span>*</span></label>
          <div class="eligibility-input-wrap">
            <i class="fa-solid fa-indian-rupee-sign"></i>
            <select id="familyIncome" required>
              <option value="">Select annual income</option>
              <option value="100000">Below ₹1 Lakh (BPL / Antyodaya / गरीब परिवार)</option>
              <option value="300000">₹1 Lakh – ₹3 Lakh (Low Income / मध्यम-गरीब)</option>
              <option value="500000">₹3 Lakh – ₹5 Lakh (Lower Middle Class)</option>
              <option value="800000">₹5 Lakh – ₹8 Lakh (Middle Class)</option>
              <option value="1000000">Above ₹8 Lakh (Taxpayer / Higher Income)</option>
            </select>
          </div>
        </div>

        <!-- Social Category & Gender in Row -->
        <div class="row">
          <div class="col-sm-6">
            <div class="eligibility-field">
              <label for="citizenCategory">Social Category (वर्ग) <span>*</span></label>
              <div class="eligibility-input-wrap">
                <i class="fa-solid fa-users"></i>
                <select id="citizenCategory" required>
                  <option value="General">General</option>
                  <option value="OBC">OBC (अन्य पिछड़ा वर्ग)</option>
                  <option value="SC">SC (अनुसूचित जाति)</option>
                  <option value="ST">ST (अनुसूचित जनजाति)</option>
                  <option value="Other">Other / Prefer not to say</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="eligibility-field">
              <label for="citizenGender">Gender (लिंग)</label>
              <div class="eligibility-input-wrap">
                <i class="fa-solid fa-venus-mars"></i>
                <select id="citizenGender">
                  <option value="all">Any (सभी)</option>
                  <option value="female">Female (महिला)</option>
                  <option value="male">Male (पुरुष)</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <button type="submit" class="eligibility-submit" style="background: #1e3a8a; border-radius: 8px;">
          <i class="fa-solid fa-search-plus mr-1"></i> Check My Scheme Eligibility (पात्रता देखें)
        </button>

        <p class="eligibility-privacy">
          <i class="fa-solid fa-lock text-success"></i>
          100% Free &amp; Private. Evaluated with Master Database Rules.
        </p>
      </form>
    </div>

  </div>

  <!-- Dynamic Results Container (Expands below on submit) -->
  <div class="container mt-5 d-none" id="eligibilityResultsWrapper">
    <div class="p-4 rounded-4" style="background: #ffffff; border: 1.5px solid #bbf7d0; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);">
      
      <!-- Results Summary Header -->
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 pb-3 mb-4" style="border-bottom: 1.5px solid #f1f5f9;">
        <div>
          <span class="badge px-3 py-2" style="background: #ecfdf5; color: #047857; font-size: 0.82rem; font-weight: 700; border-radius: 20px;">
            <i class="fas fa-check-circle mr-1"></i> ELIGIBILITY REPORT GENERATED
          </span>
          <h3 style="color: #0f172a; font-weight: 800; font-size: 1.45rem; margin: 8px 0 4px;" id="resultSummaryHeading">
            You Qualify for Schemes
          </h3>
          <p style="color: #64748b; font-size: 0.88rem; margin: 0;" id="resultSummarySub">
            Based on your profile details.
          </p>
        </div>

        <div class="d-flex align-items-center gap-2">
          <!-- Result Filter Buttons -->
          <div class="btn-group" role="group" id="resultFilterTabs">
            <button type="button" class="btn btn-sm btn-primary active" data-resfilter="all" style="font-weight: 700; border-radius: 20px 0 0 20px;">
              All Schemes (<span id="countAllQualified">0</span>)
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-resfilter="central_sector" style="font-weight: 700;">
              Central Sector (<span id="countCentralQualified">0</span>)
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary" data-resfilter="centrally_sponsored" style="font-weight: 700; border-radius: 0 20px 20px 0;">
              Centrally Sponsored (<span id="countSponsoredQualified">0</span>)
            </button>
          </div>

          <button type="button" id="btnResetSearchProfile" class="btn btn-sm btn-outline-secondary" style="border-radius: 20px; font-weight: 600;" title="Check Another Profile">
            <i class="fas fa-undo"></i> Reset
          </button>
        </div>
      </div>

      <!-- Results Grid -->
      <div class="row g-4" id="matchedSchemesGrid">
        <!-- Injected via JavaScript -->
      </div>

    </div>
  </div>

</section>

<!-- Inject All Active Schemes JSON for Client-Side Evaluation Engine -->
<script>
window.masterSchemesData = <?= json_encode($allSchemes, JSON_UNESCAPED_UNICODE) ?>;

window.handleEligibilitySubmit = function (event) {
    event.preventDefault();

    const age = Number(document.getElementById("citizenAge").value);
    const state = document.getElementById("citizenState").value;
    const income = Number(document.getElementById("familyIncome").value);
    const category = document.getElementById("citizenCategory").value;
    const occupation = document.getElementById("citizenOccupation").value;
    const gender = document.getElementById("citizenGender").value;

    const wrapper = document.getElementById("eligibilityResultsWrapper");
    const grid = document.getElementById("matchedSchemesGrid");
    const heading = document.getElementById("resultSummaryHeading");
    const sub = document.getElementById("resultSummarySub");
    const countAll = document.getElementById("countAllQualified");
    const countCentral = document.getElementById("countCentralQualified");
    const countSponsored = document.getElementById("countSponsoredQualified");

    if (!Number.isFinite(age) || age < 1 || !state || !income) {
        alert("Please enter a valid age, state, and income.");
        return;
    }

    const matched = [];
    let centralCount = 0;
    let sponsoredCount = 0;

    // Filter logic against masterSchemesData
    Object.keys(window.masterSchemesData).forEach(slug => {
        const s = window.masterSchemesData[slug];
        
        const minAge = Number(s.min_age || 0);
        const maxAge = Number(s.max_age || 120);
        const maxIncome = Number(s.max_income || 99999999);
        const targetOcc = (s.target_occupations || 'all').toLowerCase();
        const targetGen = (s.target_gender || 'all').toLowerCase();
        const statesList = Array.isArray(s.applicable_states) ? s.applicable_states : [];

        // 1. Age condition
        const ageMatch = (age >= minAge && age <= maxAge);

        // 2. Income condition
        const incomeMatch = (income <= maxIncome);

        // 3. State condition
        let stateMatch = false;
        if (state === 'All States & UTs' || statesList.includes('All States & UTs') || statesList.includes(state)) {
            stateMatch = true;
        }

        // 4. Occupation condition
        let occMatch = false;
        if (targetOcc.includes('all')) {
            occMatch = true;
        } else if (occupation !== 'all' && targetOcc.includes(occupation.toLowerCase())) {
            occMatch = true;
        }

        // 5. Gender condition
        let genMatch = false;
        if (targetGen === 'all' || gender === 'all' || targetGen === gender) {
            genMatch = true;
        }

        // 6. Tribal condition (e.g. for Janjatiya Gram)
        if (slug === 'pm-janjatiya-gram' && category !== 'ST') {
            // Not ST, skip this specialized scheme
            return;
        }

        if (ageMatch && incomeMatch && stateMatch && occMatch && genMatch) {
            matched.push(s);
            if (s.funding_type === 'central_sector') {
                centralCount++;
            } else {
                sponsoredCount++;
            }
        }
    });

    countAll.textContent = matched.length;
    countCentral.textContent = centralCount;
    countSponsored.textContent = sponsoredCount;

    heading.innerHTML = `🎉 Congratulations! You Qualify for <span style="color: #059669;">${matched.length} Government Schemes</span>`;
    sub.innerHTML = `Results personalized for <strong>Age: ${age}</strong> | <strong>State: ${state}</strong> | <strong>Category: ${category}</strong> | <strong>Income: Up to ₹${(income/100000).toFixed(0)} Lakh</strong>.`;

    // Render cards HTML
    let html = '';
    matched.forEach(s => {
        const isCentral = (s.funding_type === 'central_sector');
        const imgSrc = s.image ? s.image : 'image/schemes/default-scheme-banner.jpg';
        const topBenefit = (s.benefits_en && s.benefits_en.length > 0) ? s.benefits_en[0] : s.tagline_en;

        html += `
            <div class="col-md-6 col-lg-4 res-scheme-card" data-funding="${s.funding_type}">
                <div class="scheme-white-card type-${s.funding_type}" style="border: 1px solid #e2e8f0; height: 100%; border-radius: 14px; overflow: hidden; background: #ffffff;">
                    
                    <!-- Scheme Photo Box -->
                    <div style="position: relative; height: 155px; background: #e2e8f0; overflow: hidden;">
                        <img src="${imgSrc}" alt="${s.title_en}" style="width: 100%; height: 100%; object-fit: cover;">
                        <div style="position: absolute; top: 10px; left: 10px;">
                            ${isCentral ? `
                                <span class="badge" style="background: rgba(30, 64, 175, 0.95); color: #fff; font-size: 0.72rem; padding: 4px 8px; border-radius: 12px; backdrop-filter: blur(4px);">
                                    <i class="fas fa-landmark mr-1"></i> Central Sector (100%)
                                </span>
                            ` : `
                                <span class="badge" style="background: rgba(5, 150, 105, 0.95); color: #fff; font-size: 0.72rem; padding: 4px 8px; border-radius: 12px; backdrop-filter: blur(4px);">
                                    <i class="fas fa-handshake mr-1"></i> Centrally Sponsored
                                </span>
                            `}
                        </div>
                        <div style="position: absolute; bottom: 8px; right: 8px;">
                            <span class="badge" style="background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 0.7rem; border-radius: 10px;">
                                ${s.category_name_en || s.category}
                            </span>
                        </div>
                    </div>

                    <div style="padding: 16px 18px; display: flex; flex-direction: column; justify-content: space-between; flex: 1;">
                        <div>
                            <span style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase;">
                                <i class="fas fa-building mr-1"></i> ${s.ministry_en}
                            </span>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 4px 0 2px; line-height: 1.3;">
                                ${s.title_en}
                            </h4>
                            <div style="font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 8px;">
                                ${s.title_hi}
                            </div>

                            <!-- Benefit Pill -->
                            <div class="p-2 mb-3 rounded-3" style="background: #f0fdf4; border: 1px solid #bbf7d0; font-size: 0.8rem; color: #065f46; font-weight: 600;">
                                <i class="fas fa-check-circle mr-1 text-success"></i> ${topBenefit}
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2 pt-2" style="border-top: 1px solid #f1f5f9;">
                            <a href="<?= BASE_URL ?>/scheme/${encodeURIComponent(s.slug)}" class="btn btn-sm btn-primary" style="flex: 1; background: #1e3a8a; border: none; font-weight: 700; border-radius: 6px; padding: 8px 10px; font-size: 0.82rem; text-align: center;">
                                <i class="fas fa-info-circle mr-1"></i> View Details &amp; Audio
                            </a>
                            <a href="${s.official_url}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary" style="font-weight: 600; border-radius: 6px; padding: 8px 10px; font-size: 0.82rem;" title="Official Government Portal">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        `;
    });

    grid.innerHTML = html;
    wrapper.classList.remove("d-none");
    wrapper.scrollIntoView({ behavior: "smooth", block: "start" });
};

// Filter tabs within results
document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll("#resultFilterTabs button");
    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            tabs.forEach(t => {
                t.classList.remove("active", "btn-primary");
                t.classList.add("btn-outline-primary");
            });
            this.classList.add("active", "btn-primary");
            this.classList.remove("btn-outline-primary");

            const filter = this.getAttribute("data-resfilter");
            const cards = document.querySelectorAll(".res-scheme-card");
            cards.forEach(card => {
                if (filter === "all" || card.getAttribute("data-funding") === filter) {
                    card.style.display = "";
                } else {
                    card.style.display = "none";
                }
            });
        });
    });

    const btnReset = document.getElementById("btnResetSearchProfile");
    if (btnReset) {
        btnReset.addEventListener("click", function () {
            document.getElementById("eligibilityForm").reset();
            document.getElementById("eligibilityResultsWrapper").classList.add("d-none");
            document.getElementById("eligibilityForm").scrollIntoView({ behavior: "smooth", block: "start" });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
