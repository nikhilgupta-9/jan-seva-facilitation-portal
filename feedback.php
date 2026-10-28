<?php
// feedback.php - Verified Citizen Feedback & Service Rating
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Citizen Feedback & Service Rating - ' . APP_NAME;
$metaDescription = 'Submit verified citizen feedback and ratings for government card generation and doorstep facilitation services.';
$activeNav = 'feedback';

$err_msg = '';
$success_msg = '';
$verified_citizen = null;

// Helper to verify citizen order or service request
function verify_citizen_record($pdo, $identifier) {
    $identifier = trim($identifier);
    if (empty($identifier)) {
        return null;
    }

    $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL);
    $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);
    if (strlen($cleanPhone) > 10) {
        $cleanPhone = substr($cleanPhone, -10);
    }

    $matchedUser = null;
    $citizenName = '';
    $phone = '';
    $email = '';
    $servicesList = [];

    // 1. Check in `users` table
    if ($isEmail) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$identifier]);
        $matchedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    } elseif (!empty($cleanPhone) && strlen($cleanPhone) === 10) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE phone LIKE ? LIMIT 1");
        $stmt->execute(['%' . $cleanPhone]);
        $matchedUser = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($matchedUser) {
        $citizenName = $matchedUser['name'] ?? '';
        $phone = $matchedUser['phone'] ?? '';
        $email = $matchedUser['email'] ?? '';

        // Check paid orders for this user
        $stmt = $pdo->prepare("
            SELECT o.*, s.service_name, s.service_name_hi 
            FROM orders o 
            LEFT JOIN services s ON o.service_id = s.id 
            WHERE o.user_id = ? AND o.status = 'paid'
            ORDER BY o.id DESC
        ");
        $stmt->execute([$matchedUser['id']]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($orders as $ord) {
            $sName = $ord['service_name'] ?: 'Citizen Identity Service (Order #' . $ord['order_id'] . ')';
            $servicesList[] = [
                'type' => 'order',
                'service_id' => $ord['service_id'],
                'service_name' => $sName,
                'reference' => $ord['order_id'],
                'date' => $ord['created_at']
            ];
        }

        // Check service requests
        $stmt = $pdo->prepare("
            SELECT sr.*, s.service_name 
            FROM service_requests sr
            LEFT JOIN services s ON sr.service_id = s.id
            WHERE sr.user_id = ?
            ORDER BY sr.id DESC
        ");
        $stmt->execute([$matchedUser['id']]);
        $reqs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($reqs as $r) {
            $sName = $r['service_name'] ?: 'Digital Pass Generation';
            $exists = false;
            foreach ($servicesList as $sl) {
                if ($sl['service_name'] === $sName) { $exists = true; break; }
            }
            if (!$exists) {
                $servicesList[] = [
                    'type' => 'service_request',
                    'service_id' => $r['service_id'],
                    'service_name' => $sName,
                    'reference' => $r['order_id'],
                    'date' => $r['created_at']
                ];
            }
        }
    }

    // 2. Check in `doorstep_requests` table
    if (!empty($cleanPhone) && strlen($cleanPhone) === 10) {
        $stmt = $pdo->prepare("SELECT * FROM doorstep_requests WHERE phone LIKE ? ORDER BY id DESC");
        $stmt->execute(['%' . $cleanPhone]);
        $doorsteps = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($doorsteps as $ds) {
            if (empty($citizenName) && !empty($ds['citizen_name'])) {
                $citizenName = $ds['citizen_name'];
            }
            if (empty($phone)) {
                $phone = $ds['phone'];
            }
            $sName = $ds['service_needed'] ?: 'Doorstep Citizen Service';
            $servicesList[] = [
                'type' => 'doorstep',
                'service_id' => $ds['service_id'] ?? null,
                'service_name' => 'Doorstep: ' . $sName,
                'reference' => 'DOORSTEP-' . $ds['id'],
                'date' => $ds['created_at'],
                'city' => $ds['city'] ?? '',
                'state' => $ds['state'] ?? ''
            ];
        }
    }

    if (empty($servicesList)) {
        return null;
    }

    return [
        'verified' => true,
        'user_id' => $matchedUser['id'] ?? null,
        'citizen_name' => ($citizenName && $citizenName !== 'Citizen') ? $citizenName : '',
        'phone' => $phone ?: ($cleanPhone ?: ''),
        'email' => $email ?: ($isEmail ? $identifier : ''),
        'services' => $servicesList
    ];
}

// 1. Handle Verification POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_citizen') {
    $identifier = trim($_POST['identifier'] ?? '');
    if (empty($identifier)) {
        $err_msg = 'Please enter your registered 10-digit mobile number or email ID.';
    } else {
        $record = verify_citizen_record($pdo, $identifier);
        if ($record) {
            $_SESSION['verified_feedback_citizen'] = $record;
            $verified_citizen = $record;
        } else {
            $err_msg = 'No service record was found for "' . htmlspecialchars($identifier) . '". Feedback submission is reserved exclusively for citizens who have availed a verified service on Jan Seva Portal to ensure genuine reviews. If you recently applied, please verify using the exact mobile number used during registration.';
            unset($_SESSION['verified_feedback_citizen']);
        }
    }
}

// Check session for active verification
if (isset($_SESSION['verified_feedback_citizen']) && empty($verified_citizen)) {
    $verified_citizen = $_SESSION['verified_feedback_citizen'];
}

// Handle Reset / Verify Different Number
if (isset($_GET['reset']) && $_GET['reset'] === '1') {
    unset($_SESSION['verified_feedback_citizen']);
    header('Location: ' . BASE_URL . '/feedback.php');
    exit;
}

// 2. Handle Feedback Submission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_feedback') {
    if (!$verified_citizen) {
        $err_msg = 'Your session has expired. Please verify your mobile number or email again.';
    } else {
        $citizen_name = trim($_POST['citizen_name'] ?? '');
        $service_name = trim($_POST['service_name'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $state = trim($_POST['state'] ?? '');
        $occupation = trim($_POST['occupation'] ?? 'Citizen');
        $rating = (int) ($_POST['rating'] ?? 5);
        $title = trim($_POST['title'] ?? '');
        $comment = trim($_POST['comment'] ?? '');

        // Validation
        if (empty($citizen_name)) {
            $err_msg = 'Please provide your full name.';
        } elseif (empty($service_name)) {
            $err_msg = 'Please select the service you availed.';
        } elseif ($rating < 1 || $rating > 5) {
            $err_msg = 'Please provide a valid rating between 1 and 5 stars.';
        } elseif (empty($comment) || mb_strlen($comment) < 10) {
            $err_msg = 'Please share your detailed feedback (minimum 10 characters).';
        } else {
            try {
                // Find service_id if available
                $service_id = null;
                foreach ($verified_citizen['services'] as $svc) {
                    if ($svc['service_name'] === $service_name && !empty($svc['service_id'])) {
                        $service_id = $svc['service_id'];
                        break;
                    }
                }

                $stmt = $pdo->prepare("
                    INSERT INTO feedbacks 
                    (user_id, service_id, service_name, citizen_name, phone, email, city, state, occupation, rating, title, comment, status, is_featured, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 0, NOW())
                ");
                $stmt->execute([
                    $verified_citizen['user_id'] ?? null,
                    $service_id,
                    $service_name,
                    $citizen_name,
                    $verified_citizen['phone'],
                    $verified_citizen['email'] ?: null,
                    $city ?: null,
                    $state ?: null,
                    $occupation ?: 'Citizen',
                    $rating,
                    $title ?: 'Verified Citizen Review',
                    $comment
                ]);
                $fb_id = $pdo->lastInsertId();

                // Clear verification session
                unset($_SESSION['verified_feedback_citizen']);
                $verified_citizen = null;

                $success_msg = "Thank you, {$citizen_name}! Your feedback has been successfully registered with Reference #FB-{$fb_id}. To maintain public transparency and prevent spam, your review will be published on the portal following administrative verification.";
            } catch (Exception $e) {
                $err_msg = 'Unable to submit feedback at this time. Please try again later.';
            }
        }
    }
}

// Fetch recent approved feedbacks for showcase
$approved_feedbacks = [];
try {
    $stmt_fb = $pdo->query("
        SELECT * FROM feedbacks 
        WHERE status = 'approved' 
        ORDER BY is_featured DESC, id DESC 
        LIMIT 6
    ");
    $approved_feedbacks = $stmt_fb->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {}

include __DIR__ . '/includes/header.php';
?>

<style>
/* Feedback Page Styles (Strict Light Theme) */
.feedback-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 32px;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
}
.verify-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
}
.star-rating {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
    gap: 8px;
}
.star-rating input[type="radio"] {
    display: none;
}
.star-rating label {
    font-size: 2rem;
    color: #cbd5e1;
    cursor: pointer;
    transition: color 0.15s ease, transform 0.15s ease;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input[type="radio"]:checked ~ label {
    color: #f59e0b;
}
.star-rating label:hover {
    transform: scale(1.15);
}
.testimonial-card-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 24px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}
.testimonial-card-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.star-display i {
    color: #f59e0b;
    font-size: 0.95rem;
}
.avatar-initial {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #eff6ff;
    color: #1e3a8a;
    font-weight: 800;
    font-size: 1.1rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid #bfdbfe;
    flex-shrink: 0;
}
@media (max-width: 767px) {
    .feedback-card { padding: 22px 18px; }
}
</style>

<!-- Hero Banner Start -->
<section class="detail-hero">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Citizen Feedback</li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="detail-kicker"><i class="fas fa-star me-2"></i> VERIFIED CITIZEN REVIEWS</span>
                <h1>Citizen Feedback &amp; Service Rating</h1>
                <p>Share your genuine experience with our digital identity pass generation, welfare scheme guidance, or doorstep citizen facilitation.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-inline-flex align-items-center gap-2 bg-white px-3 py-2 rounded-pill border shadow-sm">
                    <i class="fas fa-shield-alt text-success fs-5"></i>
                    <span class="text-dark small fw-bold">100% Verified Citizen Moderation</span>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Hero Banner End -->

<div class="container py-5">

    <!-- Alert Messages -->
    <?php if (!empty($err_msg)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-triangle fs-4 text-danger"></i>
                <div>
                    <strong>Verification Notice:</strong> <?= htmlspecialchars($err_msg) ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-check-circle fs-3 text-success"></i>
                <div>
                    <h5 class="alert-heading mb-1 fw-bold">Feedback Successfully Registered!</h5>
                    <p class="mb-0 text-success-emphasis"><?= htmlspecialchars($success_msg) ?></p>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-5">
        
        <!-- Left Column: Verification & Form -->
        <div class="col-lg-7">

            <?php if (!$verified_citizen): ?>
                <!-- STEP 1: CITIZEN SERVICE VERIFICATION -->
                <div class="feedback-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: #eff6ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">Step 1: Verify Service Availed</h4>
                            <span class="text-muted small">Enter the mobile number or email you used while applying for a service</span>
                        </div>
                    </div>

                    <p class="text-secondary small mb-4">
                        To maintain authenticity and prevent fraudulent or sponsored reviews, feedback submission is exclusively open to citizens who have ordered a digital pass (ABHA, e-Shram, APAAR, Voter ID) or booked a doorstep facilitation service.
                    </p>

                    <form action="feedback.php" method="POST">
                        <input type="hidden" name="action" value="verify_citizen">
                        <div class="mb-3">
                            <label for="identifier" class="form-label fw-bold text-dark">
                                Registered Mobile Number or Email <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-mobile-alt"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="identifier" name="identifier" 
                                       placeholder="e.g. 9876543210 or yourname@gmail.com" required
                                       value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>">
                            </div>
                            <div class="form-text text-muted">
                                We will quickly check if an application or doorstep booking exists for this number.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold w-100" style="background: #1e3a8a; border-color: #1e3a8a;">
                            <i class="fas fa-search me-1"></i> Verify &amp; Unlock Feedback Form &rarr;
                        </button>
                    </form>

                    <div class="mt-4 pt-3 border-top d-flex align-items-center justify-content-between text-muted small">
                        <span><i class="fas fa-lock text-success me-1"></i> 100% Private &amp; Secure Verification</span>
                        <a href="service.php" class="text-primary text-decoration-none">Apply for a Service &rarr;</a>
                    </div>
                </div>

            <?php else: ?>
                <!-- STEP 2: VERIFIED CITIZEN FEEDBACK FORM -->
                <div class="feedback-card">
                    
                    <!-- Verified Status Ribbon -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-3 mb-4" style="background: #ecfdf5; border: 1px solid #a7f3d0;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-check-circle text-success fs-4"></i>
                            <div>
                                <div class="fw-bold text-success-emphasis" style="font-size: 0.95rem;">Citizen Record Verified!</div>
                                <div class="text-muted small">
                                    Linked to Mobile: <strong><?= htmlspecialchars($verified_citizen['phone'] ?: $verified_citizen['email']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <a href="feedback.php?reset=1" class="btn btn-sm btn-outline-secondary rounded-pill" title="Change Mobile Number">
                            <i class="fas fa-redo me-1"></i> Change
                        </a>
                    </div>

                    <h4 class="fw-bold text-dark mb-1">Step 2: Share Your Experience</h4>
                    <p class="text-muted small mb-4">Please rate the service and share your honest feedback for our public dashboard.</p>

                    <form action="feedback.php" method="POST">
                        <input type="hidden" name="action" value="submit_feedback">

                        <!-- Citizen Full Name -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="citizen_name" class="form-label fw-bold text-dark">
                                    Your Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="citizen_name" name="citizen_name" required
                                       placeholder="e.g. Ramesh Chandra" 
                                       value="<?= htmlspecialchars($_POST['citizen_name'] ?? $verified_citizen['citizen_name']) ?>">
                            </div>

                            <!-- Occupation -->
                            <div class="col-md-6">
                                <label for="occupation" class="form-label fw-bold text-dark">
                                    Occupation / Category
                                </label>
                                <select class="form-select" id="occupation" name="occupation">
                                    <option value="Farmer / Agriculturist">Farmer / Agriculturist (किसान)</option>
                                    <option value="Daily Wage / Unorganised Worker">Daily Wage / Construction (श्रमिक)</option>
                                    <option value="Small Business Owner">Small Business / Shopkeeper (दुकानदार)</option>
                                    <option value="Student / Scholar">Student / Scholar (छात्र)</option>
                                    <option value="Homemaker">Homemaker (गृहणी)</option>
                                    <option value="Senior Citizen">Senior Citizen / Pensioner (वरिष्ठ नागरिक)</option>
                                    <option value="Private Employee">Private Employee / Professional</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Location: City & State -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label fw-bold text-dark">
                                    City / District / Village
                                </label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       placeholder="e.g. Lucknow, Varanasi, Jaipur"
                                       value="<?= htmlspecialchars($_POST['city'] ?? $verified_citizen['services'][0]['city'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="state" class="form-label fw-bold text-dark">
                                    State
                                </label>
                                <select class="form-select" id="state" name="state">
                                    <option value="Uttar Pradesh">Uttar Pradesh</option>
                                    <option value="Bihar">Bihar</option>
                                    <option value="Rajasthan">Rajasthan</option>
                                    <option value="Madhya Pradesh">Madhya Pradesh</option>
                                    <option value="Delhi NCR">Delhi NCR</option>
                                    <option value="Maharashtra">Maharashtra</option>
                                    <option value="West Bengal">West Bengal</option>
                                    <option value="Haryana">Haryana</option>
                                    <option value="Punjab">Punjab</option>
                                    <option value="Gujarat">Gujarat</option>
                                    <option value="Odisha">Odisha</option>
                                    <option value="Jharkhand">Jharkhand</option>
                                    <option value="Other">Other State / UT</option>
                                </select>
                            </div>
                        </div>

                        <!-- Service Availed -->
                        <div class="mb-3">
                            <label for="service_name" class="form-label fw-bold text-dark">
                                Service Availed <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="service_name" name="service_name" required>
                                <?php foreach ($verified_citizen['services'] as $svc): ?>
                                    <option value="<?= htmlspecialchars($svc['service_name']) ?>" selected>
                                        <?= htmlspecialchars($svc['service_name']) ?> (Ref: <?= htmlspecialchars($svc['reference']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted">Automatically detected from your verified application history.</div>
                        </div>

                        <!-- Star Rating -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark d-block">
                                Service Experience Rating <span class="text-danger">*</span>
                            </label>
                            <div class="star-rating">
                                <input type="radio" id="star5" name="rating" value="5" checked>
                                <label for="star5" title="5 Stars - Outstanding"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star4" name="rating" value="4">
                                <label for="star4" title="4 Stars - Very Good"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star3" name="rating" value="3">
                                <label for="star3" title="3 Stars - Average"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star2" name="rating" value="2">
                                <label for="star2" title="2 Stars - Poor"><i class="fas fa-star"></i></label>
                                <input type="radio" id="star1" name="rating" value="1">
                                <label for="star1" title="1 Star - Very Poor"><i class="fas fa-star"></i></label>
                            </div>
                        </div>

                        <!-- Review Title / Headline -->
                        <div class="mb-3">
                            <label for="title" class="form-label fw-bold text-dark">
                                Review Headline
                            </label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   placeholder="e.g. Instant card generation with QR code and easy OTP"
                                   value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                        </div>

                        <!-- Comment / Testimonial Text -->
                        <div class="mb-4">
                            <label for="comment" class="form-label fw-bold text-dark">
                                Detailed Experience &amp; Comments <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="comment" name="comment" rows="4" required
                                      placeholder="How was your experience? How fast did you receive your digital pass? Was the facilitation fee affordable?"><?= htmlspecialchars($_POST['comment'] ?? '') ?></textarea>
                            <div class="form-text text-muted">Minimum 10 characters. Please do not include confidential Aadhaar or OTP details.</div>
                        </div>

                        <!-- Compliance Confirmation -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="confirm_authentic" required checked>
                            <label class="form-check-label text-secondary small" for="confirm_authentic">
                                I confirm this is my authentic personal feedback regarding the citizen facilitation service I availed on this portal.
                            </label>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-semibold" style="background: #1e3a8a; border-color: #1e3a8a;">
                                <i class="fas fa-paper-plane me-1"></i> Submit Verified Feedback
                            </button>
                            <a href="feedback.php?reset=1" class="btn btn-outline-secondary rounded-pill px-3 py-2">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </div>

        <!-- Right Column: Verification Policy & Quality Standard -->
        <div class="col-lg-5">
            
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                <h5 class="fw-bold text-dark mb-3"><i class="fas fa-shield-alt text-primary me-2"></i> How Review Moderation Works</h5>
                
                <div class="d-flex gap-3 mb-3">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #eff6ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">1</div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Citizen Record Check</h6>
                        <p class="text-muted small mb-0">We match your registered mobile number with paid orders or doorstep bookings in our portal database.</p>
                    </div>
                </div>

                <div class="d-flex gap-3 mb-3">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #eff6ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">2</div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Anti-Spam &amp; Content Review</h6>
                        <p class="text-muted small mb-0">Submitted reviews are queued with 'Pending' status. The portal administrator checks for compliance before approval.</p>
                    </div>
                </div>

                <div class="d-flex gap-3">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #eff6ff; color: #1e3a8a; display: flex; align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;">3</div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Homepage &amp; Public Display</h6>
                        <p class="text-muted small mb-0">Approved feedbacks are featured on the homepage to help rural citizens, workers, and students choose trusted services.</p>
                    </div>
                </div>
            </div>

            <!-- Help & Guidance Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
                <h6 class="fw-bold text-dark mb-2"><i class="fas fa-question-circle text-primary me-2"></i> Haven't used our service yet?</h6>
                <p class="text-muted small mb-3">
                    You can explore government welfare cards and apply online in 2 minutes, or request doorstep citizen assistance right to your village or home.
                </p>
                <div class="d-flex flex-column gap-2">
                    <a href="service.php" class="btn btn-sm btn-outline-primary rounded-pill text-start ps-3">
                        <i class="fas fa-id-card me-2"></i> View Citizen Services (ABHA, e-Shram, APAAR) &rarr;
                    </a>
                    <a href="all-scheme.php" class="btn btn-sm btn-outline-secondary rounded-pill text-start ps-3">
                        <i class="fas fa-landmark me-2"></i> Explore Central &amp; State Schemes &rarr;
                    </a>
                    <a href="track-status.php" class="btn btn-sm btn-outline-secondary rounded-pill text-start ps-3">
                        <i class="fas fa-search me-2"></i> Track Your Card Application Status &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Approved Citizen Reviews Showcase -->
    <?php if (!empty($approved_feedbacks)): ?>
        <div class="mt-5 pt-4 border-top">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <div>
                    <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase small mb-1">Verified Public Testimonials</span>
                    <h2 class="fw-bold text-dark fs-3 mb-0">What Citizens Say About Our Services</h2>
                </div>
                <div class="text-muted small">
                    Showing <span class="fw-bold text-primary"><?= count($approved_feedbacks) ?></span> verified public reviews
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($approved_feedbacks as $fb): 
                    $stars = max(1, min(5, (int)$fb['rating']));
                    $initial = mb_strtoupper(mb_substr($fb['citizen_name'], 0, 1));
                ?>
                <div class="col-md-6 col-lg-4">
                    <div class="testimonial-card-item">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="star-display">
                                    <?php for ($s = 1; $s <= 5; $s++): ?>
                                        <i class="fas fa-star<?= $s <= $stars ? '' : ' text-black-50' ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <span class="badge bg-success-subtle text-success small" style="font-size: 0.72rem;">
                                    <i class="fas fa-check-circle me-1"></i> Verified
                                </span>
                            </div>

                            <?php if (!empty($fb['title'])): ?>
                                <h6 class="fw-bold text-dark mb-2"><?= htmlspecialchars($fb['title']) ?></h6>
                            <?php endif; ?>

                            <p class="text-secondary small mb-3" style="line-height: 1.6;">
                                "<?= htmlspecialchars($fb['comment']) ?>"
                            </p>
                        </div>

                        <div class="pt-3 border-top d-flex align-items-center gap-3">
                            <div class="avatar-initial">
                                <?= $initial ?>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold text-dark mb-0" style="font-size: 0.95rem;"><?= htmlspecialchars($fb['citizen_name']) ?></div>
                                <div class="text-muted" style="font-size: 0.78rem;">
                                    <?= htmlspecialchars($fb['city'] ?? $fb['state'] ?? 'India') ?> • <span class="text-primary"><?= htmlspecialchars($fb['occupation'] ?? 'Citizen') ?></span>
                                </div>
                                <div class="text-muted" style="font-size: 0.72rem;">
                                    Service: <?= htmlspecialchars($fb['service_name']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
