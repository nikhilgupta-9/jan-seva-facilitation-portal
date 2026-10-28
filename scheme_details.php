<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/data/schemes.php';

$allSchemes = get_all_schemes();

// Fetch scheme by slug, fallback to 'pm-kisan'
$currentSlug = isset($_GET['slug']) ? trim($_GET['slug']) : 'pm-kisan';
$scheme = get_scheme_by_slug($currentSlug);

if (!$scheme) {
    $currentSlug = 'pm-kisan';
    $scheme = get_scheme_by_slug($currentSlug);
}

$pageTitle = htmlspecialchars($scheme['title_en']) . ' - Scheme Details & Eligibility - ' . APP_NAME;
$metaDescription = htmlspecialchars($scheme['tagline_en']);
$activeNav = 'schemes';

$isCentralSector = ($scheme['funding_type'] === 'central_sector');

include __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumbs & Hero (Strict Light Theme with Banner Background) -->
<section class="detail-hero" style="background: linear-gradient(120deg, rgba(248, 250, 252, 0.93) 0%, rgba(241, 245, 249, 0.92) 100%), url('image/barr.png'); background-position: center; background-size: cover; border-bottom: 1px solid #e2e8f0; padding: 28px 0 40px;">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb" style="margin-bottom: 14px;">
                <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home mr-1"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="all-scheme.php">Government Schemes</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($scheme['title_en']) ?></li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                    <?php if ($isCentralSector): ?>
                        <span class="badge px-3 py-2" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.82rem; font-weight: 700; border-radius: 20px;">
                            <i class="fas fa-landmark mr-1"></i> Central Sector Scheme (100% Central Funded)
                        </span>
                    <?php else: ?>
                        <span class="badge px-3 py-2" style="background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-size: 0.82rem; font-weight: 700; border-radius: 20px;">
                            <i class="fas fa-handshake mr-1"></i> Centrally Sponsored Scheme (CSS - Shared Centre + State)
                        </span>
                    <?php endif; ?>

                    <span class="badge px-3 py-2" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; font-size: 0.82rem; font-weight: 600; border-radius: 20px;">
                        <i class="fas fa-layer-group mr-1"></i> <span class="lang-text" data-en="<?= htmlspecialchars($scheme['category_name_en']) ?>" data-hi="<?= htmlspecialchars($scheme['category_name_hi']) ?>"><?= htmlspecialchars($scheme['category_name_en']) ?></span>
                    </span>
                </div>

                <h1 style="color: #0f172a; font-size: 2.1rem; font-weight: 800; margin-bottom: 6px;">
                    <span class="lang-text" data-en="<?= htmlspecialchars($scheme['title_en']) ?>" data-hi="<?= htmlspecialchars($scheme['title_hi']) ?>">
                        <?= htmlspecialchars($scheme['title_en']) ?>
                    </span>
                </h1>

                <div style="font-size: 1.15rem; font-weight: 600; color: #475569; margin-bottom: 12px;">
                    <span class="lang-text" data-en="<?= htmlspecialchars($scheme['title_hi']) ?>" data-hi="<?= htmlspecialchars($scheme['title_en']) ?>">
                        <?= htmlspecialchars($scheme['title_hi']) ?>
                    </span>
                </div>

                <p style="color: #475569; font-size: 0.95rem; line-height: 1.6; max-width: 780px; margin: 0;">
                    <span class="lang-text" data-en="<?= htmlspecialchars($scheme['tagline_en']) ?>" data-hi="<?= htmlspecialchars($scheme['tagline_hi']) ?>">
                        <?= htmlspecialchars($scheme['tagline_en']) ?>
                    </span>
                </p>
            </div>

            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px; display: inline-block; text-align: left; box-shadow: 0 4px 12px rgba(0,0,0,0.03); width: 100%; max-width: 320px;">
                    <div style="font-size: 0.74rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                        Switch Scheme (योजना बदलें)
                    </div>
                    <select id="jumpSchemeSelect" class="form-select form-select-sm" style="font-weight: 600; border-color: #cbd5e1;" onchange="if(this.value) window.location.href='scheme_details.php?slug=' + this.value;">
                        <?php foreach ($allSchemes as $sSlug => $sData): ?>
                            <option value="<?= htmlspecialchars($sSlug) ?>" <?= ($sSlug === $currentSlug) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sData['title_en']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div style="font-size: 0.76rem; color: #64748b; margin-top: 8px;">
                        <i class="fas fa-calendar-alt mr-1"></i> Launched: <strong><?= htmlspecialchars($scheme['launch_year']) ?></strong> | 
                        <i class="fas fa-phone mr-1"></i> Helpline: <strong><?= htmlspecialchars($scheme['helpline']) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Detail Page Content -->
<div class="schemes-light-wrapper" style="padding-top: 24px;">
    <div class="container">

        <!-- Top Action Bar: Bilingual Switcher + Audio Player + Eligibility Button -->
        <div class="detail-top-action-bar">
            <!-- Language Switcher -->
            <div class="d-flex align-items-center gap-2">
                <span style="font-size: 0.82rem; font-weight: 700; color: #475569; text-transform: uppercase;">
                    <i class="fas fa-globe mr-1"></i> Language:
                </span>
                <div class="lang-switcher-wrapper">
                    <button type="button" id="btnLangEn" class="lang-btn active">
                        🇬🇧 English
                    </button>
                    <button type="button" id="btnLangHi" class="lang-btn">
                        🇮🇳 हिन्दी
                    </button>
                </div>
            </div>

            <!-- Direct Eligibility Modal Trigger Button -->
            <div>
                <button type="button" class="btn-eligibility-trigger" data-bs-toggle="modal" data-bs-target="#eligibilityModal">
                    <i class="fas fa-check-circle"></i>
                    <span id="btnEligibilityLabel">क्या मैं पात्र हूँ? (Check My Eligibility)</span>
                </button>
            </div>
        </div>

        <!-- Accessible Audio Narration Bar -->
        <div class="audio-narration-card">
            <div class="audio-narration-info">
                <div class="audio-wave-icon" id="audioWaveIcon">
                    <i class="fas fa-volume-up"></i>
                </div>
                <div>
                    <h5 class="audio-label-title" id="audioTitleText">Listen to Scheme Summary (ऑडियो विवरण सुनें)</h5>
                    <p class="audio-label-sub" id="audioSubText">Click Play to listen to official guidelines and benefits via voice narration.</p>
                </div>
            </div>
            <div class="audio-ctrl-group">
                <button type="button" id="btnAudioPlay" class="btn-audio-ctrl">
                    <i class="fas fa-play"></i> <span id="btnPlayText">Play Audio</span>
                </button>
                <button type="button" id="btnAudioPause" class="btn-audio-ctrl" style="display: none;">
                    <i class="fas fa-pause"></i> <span>Pause</span>
                </button>
                <button type="button" id="btnAudioStop" class="btn-audio-ctrl" style="display: none;">
                    <i class="fas fa-stop"></i> <span>Stop</span>
                </button>
            </div>
        </div>

        <div class="row">
            <!-- Left Main Column (Detailed Sections) -->
            <div class="col-lg-8">

                <!-- 1. Scheme Overview -->
                <section class="detail-clean-panel">
                    <?php 
                        $detailImg = !empty($scheme['image']) ? $scheme['image'] : 'image/schemes/vajiram-schemes-banner.webp'; 
                    ?>
                    <div style="border-radius: 12px; overflow: hidden; height: 260px; margin-bottom: 20px; border: 1px solid #e2e8f0; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.04);">
                        <img src="<?= htmlspecialchars($detailImg) ?>" alt="<?= htmlspecialchars($scheme['title_en']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>

                    <h2 class="detail-panel-title">
                        <i class="fas fa-info-circle"></i>
                        <span class="lang-text" data-en="Scheme Overview &amp; Objectives" data-hi="योजना का विवरण एवं उद्देश्य">
                            Scheme Overview &amp; Objectives
                        </span>
                    </h2>
                    <p style="font-size: 0.95rem; line-height: 1.8; color: #334155; margin: 0;">
                        <span class="lang-text" data-en="<?= htmlspecialchars($scheme['overview_en']) ?>" data-hi="<?= htmlspecialchars($scheme['overview_hi']) ?>">
                            <?= htmlspecialchars($scheme['overview_en']) ?>
                        </span>
                    </p>
                    
                    <div class="mt-3 p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0; font-size: 0.85rem; color: #475569;">
                        <strong><i class="fas fa-landmark mr-1"></i> Nodal Ministry:</strong> 
                        <span class="lang-text" data-en="<?= htmlspecialchars($scheme['ministry_en']) ?>" data-hi="<?= htmlspecialchars($scheme['ministry_hi']) ?>">
                            <?= htmlspecialchars($scheme['ministry_en']) ?>
                        </span>
                        <br>
                        <strong><i class="fas fa-coins mr-1"></i> Funding Structure:</strong> 
                        <span class="lang-text" data-en="<?= htmlspecialchars($scheme['funding_type_label_en']) ?>" data-hi="<?= htmlspecialchars($scheme['funding_type_label_hi']) ?>">
                            <?= htmlspecialchars($scheme['funding_type_label_en']) ?>
                        </span>
                    </div>
                </section>

                <!-- 2. Key Scheme Benefits -->
                <section class="detail-clean-panel">
                    <h2 class="detail-panel-title">
                        <i class="fas fa-gift"></i>
                        <span class="lang-text" data-en="Key Scheme Benefits &amp; Financial Support" data-hi="मुख्य लाभ एवं वित्तीय सहायता">
                            Key Scheme Benefits &amp; Financial Support
                        </span>
                    </h2>
                    <div id="benefitsListContainer">
                        <?php foreach ($scheme['benefits_en'] as $idx => $bEn): ?>
                            <?php $bHi = $scheme['benefits_hi'][$idx] ?? $bEn; ?>
                            <div class="benefit-bullet-item">
                                <i class="fas fa-check-circle"></i>
                                <p class="benefit-bullet-text">
                                    <span class="lang-text" data-en="<?= htmlspecialchars($bEn) ?>" data-hi="<?= htmlspecialchars($bHi) ?>">
                                        <?= htmlspecialchars($bEn) ?>
                                    </span>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- 3. Eligibility Criteria & Ineligibility (Exclusions) -->
                <section class="detail-clean-panel">
                    <h2 class="detail-panel-title">
                        <i class="fas fa-user-check"></i>
                        <span class="lang-text" data-en="Eligibility Conditions &amp; Exclusions" data-hi="पात्रता शर्तें एवं अपात्रता (नियम)">
                            Eligibility Conditions &amp; Exclusions
                        </span>
                    </h2>
                    <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 16px;">
                        <span class="lang-text" data-en="Please review the eligibility rules carefully before applying to ensure successful processing." data-hi="आवेदन करने से पहले कृपया पात्रता नियमों की सावधानीपूर्वक जांच करें ताकि आवेदन निरस्त न हो।">
                            Please review the eligibility rules carefully before applying to ensure successful processing.
                        </span>
                    </p>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="eligibility-split-box box-eligible">
                                <div class="split-box-heading">
                                    <i class="fas fa-check-double"></i>
                                    <span class="lang-text" data-en="Detailed Eligibility Rules" data-hi="विस्तृत पात्रता नियम">
                                        Detailed Eligibility Rules
                                    </span>
                                </div>
                                <ul class="eligibility-check-list">
                                    <?php foreach ($scheme['eligibility_en'] as $idx => $eEn): ?>
                                        <?php 
                                            $eHi = $scheme['eligibility_hi'][$idx] ?? $eEn;
                                            $isExclusion = (stripos($eEn, 'Exclusion') !== false || stripos($eHi, 'अपात्रता') !== false);
                                        ?>
                                        <li>
                                            <?php if ($isExclusion): ?>
                                                <i class="fas fa-times-circle" style="color: #dc2626;"></i>
                                            <?php else: ?>
                                                <i class="fas fa-check-circle" style="color: #16a34a;"></i>
                                            <?php endif; ?>
                                            <span>
                                                <span class="lang-text" data-en="<?= htmlspecialchars($eEn) ?>" data-hi="<?= htmlspecialchars($eHi) ?>">
                                                    <?= htmlspecialchars($eEn) ?>
                                                </span>
                                            </span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 4. Required Documents Checklist -->
                <section class="detail-clean-panel">
                    <h2 class="detail-panel-title">
                        <i class="fas fa-file-alt"></i>
                        <span class="lang-text" data-en="Required Documents Checklist" data-hi="आवश्यक दस्तावेजों की सूची">
                            Required Documents Checklist
                        </span>
                    </h2>
                    <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 16px;">
                        <span class="lang-text" data-en="Keep these valid documents ready before proceeding with your registration or CSC application." data-hi="पंजीकरण या सीएससी से आवेदन करने से पहले निम्नलिखित वैध दस्तावेज तैयार रखें।">
                            Keep these valid documents ready before proceeding with your registration or CSC application.
                        </span>
                    </p>

                    <div class="row g-2">
                        <?php foreach ($scheme['documents_en'] as $idx => $dEn): ?>
                            <?php $dHi = $scheme['documents_hi'][$idx] ?? $dEn; ?>
                            <div class="col-md-6">
                                <div class="doc-item-card">
                                    <div class="doc-item-icon">
                                        <i class="fas fa-file-contract"></i>
                                    </div>
                                    <p class="doc-item-name">
                                        <span class="lang-text" data-en="<?= htmlspecialchars($dEn) ?>" data-hi="<?= htmlspecialchars($dHi) ?>">
                                            <?= htmlspecialchars($dEn) ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- 5. Step-by-Step How to Apply -->
                <section class="detail-clean-panel">
                    <h2 class="detail-panel-title">
                        <i class="fas fa-list-ol"></i>
                        <span class="lang-text" data-en="How to Apply (Step-by-Step)" data-hi="आवेदन कैसे करें (चरणबद्ध प्रक्रिया)">
                            How to Apply (Step-by-Step)
                        </span>
                    </h2>
                    <div class="steps-timeline">
                        <?php foreach ($scheme['how_to_apply_en'] as $idx => $stepEn): ?>
                            <?php $stepHi = $scheme['how_to_apply_hi'][$idx] ?? $stepEn; ?>
                            <div class="step-timeline-item">
                                <div class="step-badge-number"><?= $idx + 1 ?></div>
                                <div class="step-content-box">
                                    <span class="lang-text" data-en="<?= htmlspecialchars($stepEn) ?>" data-hi="<?= htmlspecialchars($stepHi) ?>">
                                        <?= htmlspecialchars($stepEn) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

            </div>

            <!-- Right Sidebar Column -->
            <div class="col-lg-4">

                <!-- Quick Summary Box -->
                <div class="detail-clean-panel" style="padding: 22px;">
                    <div style="font-size: 0.76rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
                        <i class="fas fa-bookmark mr-1"></i> Quick Scheme Overview
                    </div>
                    <h4 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 14px;">
                        <?= htmlspecialchars($scheme['title_en']) ?>
                    </h4>

                    <div style="font-size: 0.85rem; color: #334155; line-height: 1.8;">
                        <div class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #f1f5f9;">
                            <span class="text-muted">Funding Model:</span>
                            <strong class="text-end"><?= $isCentralSector ? '100% Central' : 'Centre: 60% / State: 40%' ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #f1f5f9;">
                            <span class="text-muted">Launched:</span>
                            <strong><?= htmlspecialchars($scheme['launch_year']) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2" style="border-bottom: 1px solid #f1f5f9;">
                            <span class="text-muted">National Helpline:</span>
                            <strong><a href="tel:<?= htmlspecialchars($scheme['helpline']) ?>" style="color: #1e3a8a; text-decoration: none;"><?= htmlspecialchars($scheme['helpline']) ?></a></strong>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Applicable Scope:</span>
                            <strong>All Eligible Indian Citizens</strong>
                        </div>
                    </div>

                    <div class="mt-4 pt-3" style="border-top: 1px solid #e2e8f0;">
                        <a href="<?= htmlspecialchars($scheme['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn w-100" style="background: #1e3a8a; color: #fff; font-weight: 700; border-radius: 8px; padding: 11px;">
                            <i class="fas fa-external-link-alt mr-1"></i> Open Official Government Portal
                        </a>
                        <button type="button" class="btn btn-outline-success w-100 mt-2" data-bs-toggle="modal" data-bs-target="#eligibilityModal" style="border-radius: 8px; font-weight: 700; padding: 10px;">
                            <i class="fas fa-check-circle mr-1"></i> Check My Eligibility Directly
                        </button>
                    </div>
                </div>

                <!-- Citizen Doorstep Facilitation Card -->
                <div class="detail-clean-panel" style="background: #ffffff; border: 1.5px solid #bbf7d0; padding: 22px;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fas fa-hands-helping" style="color: #059669; font-size: 1.3rem;"></i>
                        <h5 style="font-size: 1rem; font-weight: 700; color: #065f46; margin: 0;">
                            Jan Seva Citizen Assistance
                        </h5>
                    </div>
                    <p style="font-size: 0.85rem; color: #475569; line-height: 1.6; margin-bottom: 14px;">
                        Need physical plastic cards, ID lamination, or assistance submitting application forms? Our portal bridges the digital divide for villagers and laborers at minimal cost.
                    </p>
                    <a href="card-form.php?type=ayushman" class="btn btn-sm w-100" style="background: #059669; color: #fff; font-weight: 700; border-radius: 8px; padding: 9px;">
                        <i class="fas fa-id-card mr-1"></i> Print PVC Card at Doorstep
                    </a>
                </div>

                <!-- Related Schemes in Same Category -->
                <div class="detail-clean-panel" style="padding: 22px;">
                    <h5 style="font-size: 0.95rem; font-weight: 700; color: #0f172a; margin-bottom: 12px;">
                        <i class="fas fa-sitemap mr-1" style="color: #1e3a8a;"></i> Other Major Schemes
                    </h5>
                    <div class="list-group list-group-flush">
                        <?php 
                            $count = 0;
                            foreach ($allSchemes as $rSlug => $rScheme): 
                                if ($rSlug === $currentSlug) continue;
                                if ($count++ >= 5) break;
                        ?>
                            <a href="scheme_details.php?slug=<?= urlencode($rSlug) ?>" class="list-group-item list-group-item-action px-0 py-2" style="font-size: 0.84rem; color: #1e293b; font-weight: 600; border-color: #f1f5f9;">
                                <i class="fas fa-chevron-right mr-2" style="font-size: 0.7rem; color: #94a3b8;"></i>
                                <?= htmlspecialchars($rScheme['title_en']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-2 text-center">
                        <a href="all-scheme.php" style="font-size: 0.82rem; font-weight: 700; color: #1e3a8a; text-decoration: none;">
                            View All 12 Flagship Schemes &rarr;
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<!-- ==========================================================================
     DIRECT ELIGIBILITY CHECKER MODAL (Light Theme, Interactive, Plain-Language)
     ========================================================================== -->
<div class="modal fade" id="eligibilityModal" tabindex="-1" aria-labelledby="eligibilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content eligibility-modal-card">
            
            <div class="eligibility-modal-header">
                <div>
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: #059669; letter-spacing: 0.5px;">
                        <i class="fas fa-check-circle mr-1"></i> Direct Eligibility Assessment
                    </span>
                    <h4 class="modal-title" id="eligibilityModalLabel" style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-top: 2px;">
                        <span class="lang-text" data-en="Eligibility Check: <?= htmlspecialchars($scheme['title_en']) ?>" data-hi="पात्रता जांच: <?= htmlspecialchars($scheme['title_hi']) ?>">
                            Eligibility Check: <?= htmlspecialchars($scheme['title_en']) ?>
                        </span>
                    </h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="eligibility-modal-body">
                <p style="font-size: 0.88rem; color: #475569; margin-bottom: 20px;">
                    <span class="lang-text" data-en="Answer 2 simple questions below to get an instant, straightforward plain-language eligibility verdict." data-hi="नीचे दिए गए 2 आसान सवालों का जवाब देकर तुरंत स्पष्ट एवं सीधी पात्रता रिपोर्ट प्राप्त करें।">
                        Answer 2 simple questions below to get an instant, straightforward plain-language eligibility verdict.
                    </span>
                </p>

                <!-- Question 1 -->
                <div class="eligibility-question-block">
                    <div class="eligibility-q-text">
                        <span style="color: #1e3a8a; margin-right: 4px;">1.</span>
                        <span class="lang-text" data-en="<?= htmlspecialchars($scheme['eligibility_checker']['q1_en']) ?>" data-hi="<?= htmlspecialchars($scheme['eligibility_checker']['q1_hi']) ?>">
                            <?= htmlspecialchars($scheme['eligibility_checker']['q1_en']) ?>
                        </span>
                    </div>
                    <div class="eligibility-options-row">
                        <button type="button" class="eligibility-option-btn opt-q1" data-q="1" data-val="yes">
                            <i class="fas fa-check mr-1"></i> <span class="lang-text" data-en="Yes" data-hi="हाँ">Yes</span>
                        </button>
                        <button type="button" class="eligibility-option-btn opt-q1" data-q="1" data-val="no">
                            <i class="fas fa-times mr-1"></i> <span class="lang-text" data-en="No" data-hi="नहीं">No</span>
                        </button>
                    </div>
                </div>

                <!-- Question 2 -->
                <div class="eligibility-question-block">
                    <div class="eligibility-q-text">
                        <span style="color: #1e3a8a; margin-right: 4px;">2.</span>
                        <span class="lang-text" data-en="<?= htmlspecialchars($scheme['eligibility_checker']['q2_en']) ?>" data-hi="<?= htmlspecialchars($scheme['eligibility_checker']['q2_hi']) ?>">
                            <?= htmlspecialchars($scheme['eligibility_checker']['q2_en']) ?>
                        </span>
                    </div>
                    <div class="eligibility-options-row">
                        <button type="button" class="eligibility-option-btn opt-q2" data-q="2" data-val="yes">
                            <i class="fas fa-check mr-1"></i> <span class="lang-text" data-en="Yes" data-hi="हाँ">Yes</span>
                        </button>
                        <button type="button" class="eligibility-option-btn opt-q2" data-q="2" data-val="no">
                            <i class="fas fa-times mr-1"></i> <span class="lang-text" data-en="No" data-hi="नहीं">No</span>
                        </button>
                    </div>
                </div>

                <!-- Check Verdict Button -->
                <div class="text-center mt-3">
                    <button type="button" id="btnCalculateEligibility" class="btn" style="background: #1e3a8a; color: #ffffff; font-weight: 700; padding: 11px 28px; border-radius: 30px; font-size: 0.95rem;">
                        <i class="fas fa-calculator mr-1"></i>
                        <span class="lang-text" data-en="Check Result (परिणाम देखें)" data-hi="परिणाम देखें (Check Result)">Check Result (परिणाम देखें)</span>
                    </button>
                </div>

                <!-- Result Box (Hidden by default) -->
                <div id="verdictResultContainer" class="d-none">
                    <!-- Dynamic verdict injected via JS -->
                </div>

            </div>

            <div class="modal-footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; justify-content: space-between;">
                <div style="font-size: 0.78rem; color: #64748b;">
                    <i class="fas fa-info-circle mr-1"></i> Direct assistance based on VisionIAS / Ministry guidelines.
                </div>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" style="border-radius: 20px; font-weight: 600;">
                    Close (बंद करें)
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ==========================================================================
     INTERACTIVE JAVASCRIPT: BILINGUAL SWITCH + AUDIO NARRATION + ELIGIBILITY
     ========================================================================== -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Scheme audio narration texts
    const audioTextEn = <?= json_encode($scheme['audio_text_en']) ?>;
    const audioTextHi = <?= json_encode($scheme['audio_text_hi']) ?>;
    
    // Eligibility checker data
    const expectedQ1 = <?= json_encode($scheme['eligibility_checker']['expected']['q1'] ?? 'yes') ?>;
    const expectedQ2 = <?= json_encode($scheme['eligibility_checker']['expected']['q2'] ?? 'yes') ?>;
    const eligibleMsgEn = <?= json_encode($scheme['eligibility_checker']['eligible_msg_en']) ?>;
    const eligibleMsgHi = <?= json_encode($scheme['eligibility_checker']['eligible_msg_hi']) ?>;
    const ineligibleMsgEn = <?= json_encode($scheme['eligibility_checker']['ineligible_msg_en']) ?>;
    const ineligibleMsgHi = <?= json_encode($scheme['eligibility_checker']['ineligible_msg_hi']) ?>;
    const officialUrl = <?= json_encode($scheme['official_url']) ?>;

    let currentLang = 'en'; // default English
    let synth = window.speechSynthesis;
    let speechUtterance = null;
    let isSpeaking = false;
    let isPaused = false;

    // -------------------------------------------------------------------------
    // 1. Bilingual Switcher Logic
    // -------------------------------------------------------------------------
    const btnLangEn = document.getElementById('btnLangEn');
    const btnLangHi = document.getElementById('btnLangHi');
    const langElements = document.querySelectorAll('.lang-text');
    const audioTitleText = document.getElementById('audioTitleText');
    const audioSubText = document.getElementById('audioSubText');
    const btnPlayText = document.getElementById('btnPlayText');
    const btnEligibilityLabel = document.getElementById('btnEligibilityLabel');

    function setLanguage(lang) {
        currentLang = lang;
        if (lang === 'hi') {
            btnLangHi.classList.add('active');
            btnLangEn.classList.remove('active');
            
            // Update labels
            if (audioTitleText) audioTitleText.textContent = 'योजना का ऑडियो विवरण सुनें';
            if (audioSubText) audioSubText.textContent = 'प्ले बटन पर क्लिक करके योजना के नियमों व लाभों को हिंदी में सुनें।';
            if (btnPlayText) btnPlayText.textContent = 'ऑडियो सुनें';
            if (btnEligibilityLabel) btnEligibilityLabel.textContent = 'क्या मैं पात्र हूँ? (पात्रता जांचें)';
        } else {
            btnLangEn.classList.add('active');
            btnLangHi.classList.remove('active');

            // Update labels
            if (audioTitleText) audioTitleText.textContent = 'Listen to Scheme Summary (ऑडियो विवरण सुनें)';
            if (audioSubText) audioSubText.textContent = 'Click Play to listen to official guidelines and benefits via voice narration.';
            if (btnPlayText) btnPlayText.textContent = 'Play Audio';
            if (btnEligibilityLabel) btnEligibilityLabel.textContent = 'Check My Eligibility (क्या मैं पात्र हूँ?)';
        }

        // Switch all text nodes having data-en and data-hi
        langElements.forEach(el => {
            const text = el.getAttribute('data-' + lang);
            if (text) {
                el.innerHTML = text;
            }
        });

        // Stop any audio playing when language switches
        stopAudio();
    }

    if (btnLangEn) {
        btnLangEn.addEventListener('click', function () {
            setLanguage('en');
        });
    }

    if (btnLangHi) {
        btnLangHi.addEventListener('click', function () {
            setLanguage('hi');
        });
    }

    // -------------------------------------------------------------------------
    // 2. Audio Narration Player (Web SpeechSynthesis API)
    // -------------------------------------------------------------------------
    const btnAudioPlay = document.getElementById('btnAudioPlay');
    const btnAudioPause = document.getElementById('btnAudioPause');
    const btnAudioStop = document.getElementById('btnAudioStop');
    const audioWaveIcon = document.getElementById('audioWaveIcon');

    function playAudio() {
        if (!synth) {
            alert('Your browser does not support audio speech narration.');
            return;
        }

        if (isPaused) {
            synth.resume();
            isPaused = false;
            isSpeaking = true;
            btnAudioPlay.style.display = 'none';
            btnAudioPause.style.display = 'inline-flex';
            audioWaveIcon.classList.add('playing');
            return;
        }

        synth.cancel(); // cancel any previous utterance

        const textToSpeak = (currentLang === 'hi') ? audioTextHi : audioTextEn;
        speechUtterance = new SpeechSynthesisUtterance(textToSpeak);

        // Pick voice appropriate to current language
        const voices = synth.getVoices();
        if (currentLang === 'hi') {
            speechUtterance.lang = 'hi-IN';
            const hiVoice = voices.find(v => v.lang.includes('hi') || v.lang.includes('Hindi'));
            if (hiVoice) speechUtterance.voice = hiVoice;
        } else {
            speechUtterance.lang = 'en-IN';
            const enVoice = voices.find(v => v.lang.includes('en-IN') || v.lang.includes('en-GB') || v.lang.includes('en-US'));
            if (enVoice) speechUtterance.voice = enVoice;
        }

        speechUtterance.rate = 0.95; // slightly slower for high clarity
        speechUtterance.pitch = 1.0;

        speechUtterance.onstart = function () {
            isSpeaking = true;
            isPaused = false;
            btnAudioPlay.style.display = 'none';
            btnAudioPause.style.display = 'inline-flex';
            btnAudioStop.style.display = 'inline-flex';
            audioWaveIcon.classList.add('playing');
        };

        speechUtterance.onend = function () {
            stopAudio();
        };

        speechUtterance.onerror = function () {
            stopAudio();
        };

        synth.speak(speechUtterance);
    }

    function pauseAudio() {
        if (synth && isSpeaking && !isPaused) {
            synth.pause();
            isPaused = true;
            btnAudioPause.style.display = 'none';
            btnAudioPlay.style.display = 'inline-flex';
            btnPlayText.textContent = (currentLang === 'hi') ? 'जारी रखें' : 'Resume Audio';
            audioWaveIcon.classList.remove('playing');
        }
    }

    function stopAudio() {
        if (synth) {
            synth.cancel();
        }
        isSpeaking = false;
        isPaused = false;
        if (btnAudioPlay) {
            btnAudioPlay.style.display = 'inline-flex';
            btnPlayText.textContent = (currentLang === 'hi') ? 'ऑडियो सुनें' : 'Play Audio';
        }
        if (btnAudioPause) btnAudioPause.style.display = 'none';
        if (btnAudioStop) btnAudioStop.style.display = 'none';
        if (audioWaveIcon) audioWaveIcon.classList.remove('playing');
    }

    if (btnAudioPlay) btnAudioPlay.addEventListener('click', playAudio);
    if (btnAudioPause) btnAudioPause.addEventListener('click', pauseAudio);
    if (btnAudioStop) btnAudioStop.addEventListener('click', stopAudio);

    // Stop speaking when user navigates away
    window.addEventListener('beforeunload', stopAudio);

    // -------------------------------------------------------------------------
    // 3. Direct Interactive Eligibility Checker
    // -------------------------------------------------------------------------
    let selectedQ1 = null;
    let selectedQ2 = null;

    const optQ1Buttons = document.querySelectorAll('.opt-q1');
    const optQ2Buttons = document.querySelectorAll('.opt-q2');
    const btnCalculate = document.getElementById('btnCalculateEligibility');
    const verdictContainer = document.getElementById('verdictResultContainer');

    optQ1Buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            optQ1Buttons.forEach(b => {
                b.classList.remove('selected-yes', 'selected-no');
            });
            const val = this.getAttribute('data-val');
            selectedQ1 = val;
            if (val === 'yes') {
                this.classList.add('selected-yes');
            } else {
                this.classList.add('selected-no');
            }
        });
    });

    optQ2Buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            optQ2Buttons.forEach(b => {
                b.classList.remove('selected-yes', 'selected-no');
            });
            const val = this.getAttribute('data-val');
            selectedQ2 = val;
            if (val === 'yes') {
                this.classList.add('selected-yes');
            } else {
                this.classList.add('selected-no');
            }
        });
    });

    if (btnCalculate) {
        btnCalculate.addEventListener('click', function () {
            if (!selectedQ1 || !selectedQ2) {
                alert(currentLang === 'hi' ? 'कृपया दोनों सवालों के जवाब (हाँ / नहीं) चुनें।' : 'Please answer both questions before checking eligibility.');
                return;
            }

            const isEligible = (selectedQ1 === expectedQ1 && selectedQ2 === expectedQ2);

            let verdictHtml = '';
            if (isEligible) {
                const heading = (currentLang === 'hi') ? '✅ हाँ! आप इस योजना के लिए पात्र हैं।' : '✅ Yes! You Are Eligible for this Scheme.';
                const msg = (currentLang === 'hi') ? eligibleMsgHi : eligibleMsgEn;
                const nextStep = (currentLang === 'hi') 
                    ? 'आप आवश्यक दस्तावेजों के साथ आधिकारिक पोर्टल से सीधे आवेदन कर सकते हैं या हमारे जन सेवा पोर्टल से सहायता ले सकते हैं।'
                    : 'You can apply directly on the official government portal or take doorstep assistance from our Jan Seva portal.';
                const btnLabel = (currentLang === 'hi') ? 'आधिकारिक पोर्टल पर जाएं' : 'Proceed to Official Portal';

                verdictHtml = `
                    <div class="verdict-box-success">
                        <div style="font-size: 2.2rem; margin-bottom: 6px;">🎉</div>
                        <h4 style="font-weight: 800; color: #065f46; margin-bottom: 8px;">${heading}</h4>
                        <p style="font-size: 0.95rem; color: #047857; line-height: 1.6; margin-bottom: 12px; font-weight: 600;">${msg}</p>
                        <p style="font-size: 0.86rem; color: #475569; margin-bottom: 16px;">${nextStep}</p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            <a href="${officialUrl}" target="_blank" rel="noopener noreferrer" class="btn btn-success" style="border-radius: 20px; font-weight: 700; padding: 8px 22px;">
                                <i class="fas fa-external-link-alt mr-1"></i> ${btnLabel}
                            </a>
                            <a href="card-form.php?type=ayushman" class="btn btn-outline-success" style="border-radius: 20px; font-weight: 700; padding: 8px 20px;">
                                <i class="fas fa-print mr-1"></i> Get Plastic ID Printed
                            </a>
                        </div>
                    </div>
                `;
            } else {
                const heading = (currentLang === 'hi') ? '⚠️ आप वर्तमान नियमों के अनुसार पात्र नहीं हैं।' : '⚠️ You May Not Be Eligible Under Current Rules.';
                const msg = (currentLang === 'hi') ? ineligibleMsgHi : ineligibleMsgEn;
                const advice = (currentLang === 'hi') 
                    ? 'कृपया अन्य केंद्रीय क्षेत्र या केंद्र प्रायोजित योजनाओं की सूची देखें जिनके लिए आप पात्र हो सकते हैं।'
                    : 'Please explore our other Central Sector and Centrally Sponsored schemes that match your profile.';

                verdictHtml = `
                    <div class="verdict-box-danger">
                        <div style="font-size: 2.2rem; margin-bottom: 6px;">📋</div>
                        <h4 style="font-weight: 800; color: #c2410c; margin-bottom: 8px;">${heading}</h4>
                        <p style="font-size: 0.95rem; color: #9a3412; line-height: 1.6; margin-bottom: 12px; font-weight: 600;">${msg}</p>
                        <p style="font-size: 0.86rem; color: #475569; margin-bottom: 16px;">${advice}</p>
                        <a href="all-scheme.php" class="btn btn-outline-danger" style="border-radius: 20px; font-weight: 700; padding: 8px 22px;">
                            <i class="fas fa-th mr-1"></i> Explore Other Schemes
                        </a>
                    </div>
                `;
            }

            verdictContainer.innerHTML = verdictHtml;
            verdictContainer.classList.remove('d-none');
            verdictContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
