<?php
// doorstep-booking.php - Doorstep Citizen Executive Assistance (Original GIGW Template Design)
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Doorstep Executive Assistance - ' . APP_NAME;
$activeNav = 'doorstep';

$service_id = (int) ($_GET['service_id'] ?? 0);
$success_msg = '';
$err_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citizen_name = trim($_POST['citizen_name'] ?? '');
    $phone = preg_replace('/[^0-9]/', '', $_POST['phone'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $pincode = trim($_POST['pincode'] ?? '');
    $service_needed = trim($_POST['service_needed'] ?? 'General Document Assistance');
    $preferred_slot = trim($_POST['preferred_slot'] ?? 'Morning (10:00 AM - 1:00 PM)');

    if (empty($citizen_name) || strlen($phone) !== 10 || empty($address) || empty($pincode)) {
        $err_msg = 'Please fill all mandatory fields with a valid 10-digit mobile number and 6-digit pincode.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO doorstep_requests (service_id, citizen_name, phone, state, city, address, pincode, service_needed, preferred_slot, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'new')");
            $stmt->execute([$service_id ?: 5, $citizen_name, $phone, $state, $city, $address, $pincode, $service_needed, $preferred_slot]);
            $request_id = $pdo->lastInsertId();
            $success_msg = "Your doorstep assistance request has been booked successfully! Reference Ticket: #DS-{$request_id}. A citizen executive will contact +91-{$phone} to verify address and schedule the visit.";
        } catch (Exception $e) {
            $err_msg = 'Failed to submit request: ' . $e->getMessage();
        }
    }
}

// Fetch active services for dropdown
$stmt = $pdo->query("SELECT id, service_name FROM services WHERE is_active = 1 ORDER BY service_name ASC");
$services_list = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<section class="detail-hero">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Doorstep Assistance</li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="detail-kicker"><i class="fas fa-home mr-2"></i> ASSISTED CITIZEN SERVICE</span>
                <h1>Doorstep Citizen Executive Visit</h1>
                <p>Specially designed for senior citizens, rural families, and persons requiring in-person document handling.</p>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<div class="detail-main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <section class="detail-panel" style="padding: 30px;">
                    <div class="panel-heading mb-4">
                        <div>
                            <span class="section-eyebrow">APPOINTMENT SCHEDULER</span>
                            <h2>Book a Home Visit Appointment</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-user-shield"></i></span>
                    </div>

                    <?php if ($success_msg): ?>
                        <div class="alert alert-success p-4 mb-4" style="border-radius: 10px;">
                            <div class="d-flex gap-3 align-items-start">
                                <i class="fas fa-check-circle fa-2x text-success mt-1"></i>
                                <div>
                                    <h5 class="fw-bold text-success mb-1">Appointment Registered!</h5>
                                    <p class="mb-3 text-secondary small"><?= htmlspecialchars($success_msg) ?></p>
                                    <div class="d-flex gap-2">
                                        <a href="<?= BASE_URL ?>/index.php" class="btn btn-portal-primary btn-sm">Return to Home</a>
                                        <a href="<?= BASE_URL ?>/all-scheme.php" class="btn btn-outline-portal btn-sm">Explore Schemes</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php if ($err_msg): ?>
                            <div class="alert alert-danger py-2 small mb-4"><?= htmlspecialchars($err_msg) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label for="citizen_name" class="form-label fw-bold small">Applicant / Beneficiary Name <span class="text-danger">*</span></label>
                                    <input type="text" id="citizen_name" name="citizen_name" class="form-control" required placeholder="Enter full name">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label fw-bold small">10-Digit Mobile Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-secondary">+91</span>
                                        <input type="tel" id="phone" name="phone" class="form-control" maxlength="10" required placeholder="98XXXXXXXX">
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="service_needed" class="form-label fw-bold small">Service Required:</label>
                                    <select id="service_needed" name="service_needed" class="form-select">
                                        <?php foreach ($services_list as $s): ?>
                                            <option value="<?= htmlspecialchars($s['service_name']) ?>" <?= ($service_id == $s['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['service_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <option value="General Digital ID Guidance">General Citizen ID Assistance</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="preferred_slot" class="form-label fw-bold small">Preferred Visit Slot:</label>
                                    <select id="preferred_slot" name="preferred_slot" class="form-select">
                                        <option value="Morning (10:00 AM - 1:00 PM)">Morning (10:00 AM - 1:00 PM)</option>
                                        <option value="Afternoon (1:00 PM - 4:00 PM)">Afternoon (1:00 PM - 4:00 PM)</option>
                                        <option value="Evening (4:00 PM - 7:00 PM)">Evening (4:00 PM - 7:00 PM)</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label fw-bold small">State / UT <span class="text-danger">*</span></label>
                                    <input type="text" id="state" name="state" class="form-control" required placeholder="e.g. Delhi / UP">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label fw-bold small">City / District <span class="text-danger">*</span></label>
                                    <input type="text" id="city" name="city" class="form-control" required placeholder="e.g. New Delhi">
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="pincode" class="form-label fw-bold small">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" id="pincode" name="pincode" class="form-control" maxlength="6" required placeholder="e.g. 110001">
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="address" class="form-label fw-bold small">Complete Residential Address <span class="text-danger">*</span></label>
                                    <textarea id="address" name="address" rows="3" class="form-control" required placeholder="House No., Street/Gali, Landmark, Area"></textarea>
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="alert alert-warning small mb-0" style="background: #FFFBEB; border-color: #FDE68A; color: #92400E;">
                                        <strong><i class="fas fa-info-circle me-1"></i> Convenience Fee Notice:</strong> A nominal convenience charge (approx. ₹100–₹150) may be collected in person by the visiting facilitator towards local travel and paper scanning upon visit completion.
                                    </div>
                                </div>

                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-portal-primary py-3 px-4 w-100 fw-bold">
                                        <i class="fas fa-calendar-check me-2"></i> Book Doorstep Executive Visit
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php endif; ?>
                </section>
            </div>
        </div>

        <div class="detail-disclaimer">
            <i class="fas fa-info-circle"></i>
            <p><strong>Disclaimer:</strong> <?= htmlspecialchars(LEGAL_DISCLAIMER) ?></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
