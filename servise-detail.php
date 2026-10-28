<?php
// servise-detail.php - Service Details & Application Guide (Original GIGW Template Design)
require_once __DIR__ . '/config/config.php';

$slug = trim($_GET['slug'] ?? '');
$service = null;

if (!empty($slug)) {
    $service = get_service_by_slug($pdo, $slug);
}

if (!$service) {
    if (!empty($slug)) {
        $stmt_fallback = $pdo->prepare("SELECT * FROM services WHERE slug LIKE ? AND is_active = 1 LIMIT 1");
        $stmt_fallback->execute(['%' . $slug . '%']);
        $service = $stmt_fallback->fetch();
    }
    if (!$service) {
        $stmt_first = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
        $service = $stmt_first->fetch();
    }
}

if (!$service) {
    header('Location: ' . BASE_URL . '/all-scheme.php');
    exit;
}

$pageTitle = $service['service_name'] . ' - Service Details & Application Guide';
$metaDescription = $service['short_desc'];
$activeNav = 'services';
$is_api = ($service['service_type'] === 'api');

include __DIR__ . '/includes/header.php';
?>

<section class="detail-hero">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/all-scheme.php">Government Schemes</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($service['service_name']) ?></li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="detail-kicker"><i class="fas fa-landmark mr-2"></i> GOVERNMENT SERVICE INFORMATION</span>
                <h1><?= htmlspecialchars($service['service_name']) ?></h1>
                <p><?= htmlspecialchars($service['short_desc']) ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<div class="detail-main">
    <div class="container">
        <div class="row">

            <!-- Main column -->
            <div class="col-lg-8">

                <!-- Dynamic scheme overview -->
                <section class="detail-panel scheme-overview" id="schemeOverview" aria-live="polite">
                    <div class="overview-top">
                        <div class="overview-symbol" id="schemeIcon"><i class="<?= htmlspecialchars($service['icon'] ?? 'fas fa-id-card') ?>"></i></div>
                        <div class="overview-badges">
                            <span class="category-badge" id="schemeCategory"><?= htmlspecialchars($service['category']) ?></span>
                            <span class="info-badge"><i class="fas fa-check-circle"></i> <?= $is_api ? 'Direct Digital Generation (₹50)' : 'Official Department Guidance' ?></span>
                        </div>
                    </div>
                    <span class="section-eyebrow" id="schemeDepartment"><?= strtoupper(htmlspecialchars($service['category'])) ?></span>
                    <h2 id="schemeTitle"><?= htmlspecialchars($service['service_name']) ?></h2>
                    <p class="overview-description" id="schemeDescription"><?= nl2br(htmlspecialchars($service['full_desc'])) ?></p>
                    <div class="overview-actions">
                        <a href="<?= htmlspecialchars($service['official_url']) ?>" id="officialLink" class="btn btn-portal-primary" target="_blank" rel="noopener noreferrer">
                            Visit Official Website <i class="fas fa-external-link-alt ml-2"></i>
                        </a>
                        <button type="button" class="btn btn-outline-portal" id="copySchemeLink" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($service['official_url']) ?>'); alert('Official link copied to clipboard!');">
                            <i class="fas fa-link mr-2"></i> Copy Official Link
                        </button>
                    </div>
                </section>

                <!-- APPLICATION & VERIFICATION PANEL -->
                <?php if ($is_api): ?>
                <section class="detail-panel information-panel" id="application-form-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">INSTANT GENERATION &amp; DATA INPUT</span>
                            <h2>Apply for <?= htmlspecialchars($service['service_name']) ?></h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-wpforms"></i></span>
                    </div>
                    <p class="section-intro">Fill in your information below to authenticate via mobile OTP and generate your verified CR80 digital PVC Card pass.</p>

                    <form id="direct-api-form" autocomplete="off" class="mt-4">
                        <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>">
                        <input type="hidden" name="service_slug" value="<?= htmlspecialchars($service['slug']) ?>">

                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label fw-bold small">Applicant Full Name <span class="text-danger">*</span></label>
                                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="As written on official identity" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-bold small">Aadhaar Linked Mobile Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-secondary">+91</span>
                                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="10-digit mobile" maxlength="10" required>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label fw-bold small">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" id="dob" name="dob" class="form-control" required value="1996-05-15">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label fw-bold small">Gender <span class="text-danger">*</span></label>
                                <select id="gender" name="gender" class="form-control" required>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Transgender">Transgender / Other</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
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
                                    <option value="Tamil Nadu">Tamil Nadu</option>
                                    <option value="Gujarat">Gujarat</option>
                                    <option value="Haryana">Haryana</option>
                                    <option value="Punjab">Punjab</option>
                                    <option value="Other">Other State</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label fw-bold small">Permanent Address</label>
                                <input type="text" id="address" name="address" class="form-control" placeholder="House / Village, District, Pincode" value="H.No 120, Street 4, Civil Lines">
                            </div>

                            <!-- Specific Dynamic Fields Depending on Slug -->
                            <?php if ($service['slug'] === 'abha-card'): ?>
                                <div class="col-12 mb-3">
                                    <div class="p-3 bg-light rounded border">
                                        <label for="aadhaar_input" class="form-label fw-bold text-primary small mb-1">
                                            <i class="fas fa-fingerprint text-success me-1"></i> 12-Digit Aadhaar or Virtual ID (Optional)
                                        </label>
                                        <input type="password" id="aadhaar_input" name="aadhaar_input" class="form-control" placeholder="XXXX-XXXX-XXXX" maxlength="14">
                                        <small class="text-muted">Aadhaar helps automatically link electronic health records across ABDM-certified hospitals and health centres.</small>
                                    </div>
                                </div>
                            <?php elseif ($service['slug'] === 'apaar-card'): ?>
                                <div class="col-md-6 mb-3">
                                    <label for="institution" class="form-label fw-bold small">School / College / University <span class="text-danger">*</span></label>
                                    <input type="text" id="institution" name="institution" class="form-control" placeholder="e.g. Kendriya Vidyalaya / Delhi University" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="course" class="form-label fw-bold small">Class / Degree Program</label>
                                    <input type="text" id="course" name="course" class="form-control" placeholder="e.g. Class 12 / B.Tech / B.Com" value="Higher Secondary / Graduation">
                                </div>
                            <?php elseif ($service['slug'] === 'eshram-card'): ?>
                                <div class="col-md-6 mb-3">
                                    <label for="occupation" class="form-label fw-bold small">Primary Occupation / Skill Sector <span class="text-danger">*</span></label>
                                    <input type="text" id="occupation" name="occupation" class="form-control" placeholder="e.g. Agriculture / Construction / Driver" value="Building & Other Construction Worker" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="father_name" class="form-label fw-bold small">Father / Husband Name</label>
                                    <input type="text" id="father_name" name="father_name" class="form-control" placeholder="Enter Full Name" value="Shri Ram Prasad">
                                </div>
                            <?php elseif ($service['slug'] === 'voter-id'): ?>
                                <div class="col-md-6 mb-3">
                                    <label for="epic_number" class="form-label fw-bold small">10-Character EPIC Number (Voter ID) <span class="text-danger">*</span></label>
                                    <input type="text" id="epic_number" name="epic_number" class="form-control text-uppercase" placeholder="e.g. WBF8492015" maxlength="10" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="constituency" class="form-label fw-bold small">Assembly / Parliamentary Constituency</label>
                                    <input type="text" id="constituency" name="constituency" class="form-control" placeholder="e.g. New Delhi AC-40" value="New Delhi Constituency">
                                </div>
                            <?php endif; ?>

                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="citizenConsent" required checked>
                                    <label class="form-check-label small text-secondary" for="citizenConsent">
                                        I confirm that the details entered above belong to me. I authorize this facilitation platform to process digital verification and acknowledge that a nominal platform fee of <strong><?= format_inr($service['fee']) ?></strong> will be charged.
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <button type="submit" id="btn-submit-application" class="btn btn-portal-primary py-3 px-4 w-100 fw-bold">
                                    <i class="fas fa-lock me-2"></i> Verify Phone &amp; Proceed (Pay <?= format_inr($service['fee']) ?>)
                                </button>
                            </div>
                        </div>
                    </form>
                </section>

                <?php else: ?>
                <!-- STREAM B: ASSISTED CHECKLIST & REDIRECTION -->
                <section class="detail-panel information-panel" id="assisted-guidance-panel">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">STEP-BY-STEP ASSISTANCE</span>
                            <h2>Prerequisite Checklist &amp; Official Redirection</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-compass"></i></span>
                    </div>
                    <p class="section-intro">Review the prerequisite checklist and prepare your documents before visiting the official portal.</p>

                    <div class="p-3 bg-light rounded mb-4">
                        <h6 class="fw-bold mb-3"><i class="fas fa-clipboard-check text-success me-2"></i> Document Verification Checklist:</h6>
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
                            <label class="form-check-label small" for="chk-doc-4">Active Mobile Number for receiving official Government OTP</label>
                        </div>
                    </div>

                    <div class="d-flex gap-3 flex-wrap align-items-center">
                        <a href="<?= htmlspecialchars($service['official_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-portal-primary py-3 px-4 fw-bold">
                            <i class="fas fa-external-link-alt me-2"></i> Open Official Government Portal Now
                        </a>
                        <a href="<?= BASE_URL ?>/doorstep-booking.php?service_id=<?= (int)$service['id'] ?>" class="btn btn-outline-portal py-3 px-4">
                            <i class="fas fa-home me-2"></i> Book Doorstep Executive Visit
                        </a>
                    </div>
                </section>
                <?php endif; ?>

                <!-- Key Benefits -->
                <section class="detail-panel information-panel" id="benefits">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">WHAT YOU SHOULD KNOW</span>
                            <h2>Key Benefits</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-gift"></i></span>
                    </div>
                    <ul class="detail-list" id="benefitsList">
                        <li><i class="fas fa-check-circle"></i><span>Universal electronic identity pass accessible on mobile or printed on physical PVC card.</span></li>
                        <li><i class="fas fa-check-circle"></i><span>Direct linkage with verified central and state digital public records.</span></li>
                        <li><i class="fas fa-check-circle"></i><span>Instant validation through secure embedded QR code.</span></li>
                        <li><i class="fas fa-check-circle"></i><span>24/7 re-download access through our citizen status tracking portal.</span></li>
                    </ul>
                </section>

                <!-- Eligibility -->
                <section class="detail-panel information-panel" id="eligibility">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">CHECK BEFORE APPLYING</span>
                            <h2>Eligibility Criteria</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-user-check"></i></span>
                    </div>
                    <p class="section-intro">Eligibility depends on the specific service and current government rules. Use these general prompts, then verify the official criteria.</p>
                    <ul class="detail-list" id="eligibilityList">
                        <li><i class="fas fa-check-circle"></i><span>Citizen of India with valid demographic credentials.</span></li>
                        <li><i class="fas fa-check-circle"></i><span>Active mobile number capable of receiving SMS verification codes.</span></li>
                        <li><i class="fas fa-check-circle"></i><span>Confirm any age, income, or category criteria listed by the scheme.</span></li>
                    </ul>
                </section>

                <!-- Required Documents -->
                <section class="detail-panel information-panel" id="documents">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">KEEP THESE READY</span>
                            <h2>Required Documents</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-folder-open"></i></span>
                    </div>
                    <p class="section-intro">Keep scanned copies or original identity documents ready before beginning:</p>
                    <div class="document-grid" id="documentsList">
                        <div class="document-item"><i class="far fa-id-card"></i><span>Identity proof (Aadhaar, Voter ID, PAN)</span></div>
                        <div class="document-item"><i class="fas fa-map-marker-alt"></i><span>Address or residency proof</span></div>
                        <div class="document-item"><i class="fas fa-file-invoice"></i><span>Scheme-specific supporting documents</span></div>
                        <div class="document-item"><i class="fas fa-mobile-alt"></i><span>Active mobile number for OTP</span></div>
                    </div>
                </section>

                <!-- How to Apply -->
                <section class="detail-panel information-panel" id="how-to-apply">
                    <div class="panel-heading">
                        <div>
                            <span class="section-eyebrow">APPLICATION GUIDE</span>
                            <h2>How to Apply</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-list-ol"></i></span>
                    </div>
                    <div class="steps-list" id="stepsList">
                        <div class="step-item"><span class="step-number">01</span><div><h3>Review Service Guidelines</h3><p>Check the required parameters and verify eligibility conditions.</p></div></div>
                        <div class="step-item"><span class="step-number">02</span><div><h3>Submit Demographic Details</h3><p>Enter your name, date of birth, mobile number, and address in the application form.</p></div></div>
                        <div class="step-item"><span class="step-number">03</span><div><h3>Verify with Mobile OTP</h3><p>Confirm the 6-digit verification code received on your mobile number.</p></div></div>
                        <div class="step-item"><span class="step-number">04</span><div><h3>Download Your Digital Card</h3><p>Pay the nominal ₹50 facilitation fee to print or save your verified PVC Card immediately.</p></div></div>
                    </div>
                </section>
            </div>

            <!-- Sidebar -->
            <aside class="col-lg-4">
                <div class="detail-sidebar">
                    <div class="sidebar-card">
                        <div class="sidebar-card-heading"><i class="fas fa-external-link-alt"></i><h3>Official Resource</h3></div>
                        <p>For current statutory rules, application status and official services, visit the official department website.</p>
                        <a href="<?= htmlspecialchars($service['official_url']) ?>" id="sidebarOfficialLink" target="_blank" rel="noopener noreferrer" class="sidebar-official-link">Open Official Website <i class="fas fa-arrow-right"></i></a>
                    </div>

                    <div class="sidebar-card">
                        <div class="sidebar-card-heading"><i class="fas fa-list-ul"></i><h3>On This Page</h3></div>
                        <ul class="sidebar-links">
                            <li><a href="#schemeOverview">Service Overview <i class="fas fa-chevron-right"></i></a></li>
                            <?php if ($is_api): ?>
                                <li><a href="#application-form-panel">Application Form <i class="fas fa-chevron-right"></i></a></li>
                            <?php else: ?>
                                <li><a href="#assisted-guidance-panel">Checklist &amp; Redirect <i class="fas fa-chevron-right"></i></a></li>
                            <?php endif; ?>
                            <li><a href="#benefits">Key Benefits <i class="fas fa-chevron-right"></i></a></li>
                            <li><a href="#eligibility">Eligibility Criteria <i class="fas fa-chevron-right"></i></a></li>
                            <li><a href="#documents">Required Documents <i class="fas fa-chevron-right"></i></a></li>
                            <li><a href="#how-to-apply">How to Apply <i class="fas fa-chevron-right"></i></a></li>
                        </ul>
                    </div>

                    <div class="sidebar-note">
                        <i class="fas fa-shield-alt"></i>
                        <h3>Protect Your Information</h3>
                        <p>Only share personal documents on a verified portal. We use ephemeral processing and never store your biometric records.</p>
                    </div>

                    <div class="sidebar-card mt-3">
                        <div class="sidebar-card-heading"><i class="fas fa-home"></i><h3>Doorstep Assistance</h3></div>
                        <p>Need physical help with form filling or paper scanning? Book a certified citizen executive to visit your home.</p>
                        <a href="<?= BASE_URL ?>/doorstep-booking.php?service_id=<?= (int)$service['id'] ?>" class="sidebar-official-link">Book Home Visit <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </aside>
        </div>

        <div class="detail-disclaimer">
            <i class="fas fa-info-circle"></i>
            <p><strong>Disclaimer:</strong> <?= htmlspecialchars(LEGAL_DISCLAIMER) ?></p>
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
