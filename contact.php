<?php
// contact.php - Public Contact & Citizen Support Desk
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/utils/mailer.php';

$pageTitle = 'Contact Us - ' . APP_NAME;
$metaDescription = 'Get in touch with our citizen facilitation desk for inquiries, doorstep appointments, or feedback.';
$activeNav = 'contact';

$success_msg = '';
$err_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = preg_replace('/[^0-9]/', '', $_POST['mobile'] ?? $_POST['phone'] ?? '');
    $service_interest = trim($_POST['service_interest'] ?? 'General Inquiry');
    $subject = trim($_POST['subject'] ?? 'Citizen Inquiry');
    $message = trim($_POST['message'] ?? '');
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($name) || strlen($phone) !== 10 || empty($message)) {
        $err_msg = 'Please provide your full name, a valid 10-digit mobile number, and your query message.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, service_interest, subject, message, status, ip_address) 
                                   VALUES (?, ?, ?, ?, ?, ?, 'new', ?)");
            $stmt->execute([$name, $email ?: null, $phone, $service_interest, $subject, $message, $ip]);
            $inq_id = $pdo->lastInsertId();
            $success_msg = "Thank you, {$name}! Your inquiry has been registered under Reference Ticket #INQ-{$inq_id}. Our citizen support desk will respond shortly.";

            // Email notification is best-effort only - the DB row above is
            // the reliable record, so a mail failure must never surface
            // as an error to the citizen or block their submission.
            try {
                send_contact_notification_email([
                    'id' => $inq_id,
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'service_interest' => $service_interest,
                    'subject' => $subject,
                    'message' => $message,
                    'ip' => $ip,
                ]);
            } catch (Exception $e) {
                error_log('Contact form email notification failed: ' . $e->getMessage());
            }
        } catch (Exception $e) {
            $err_msg = 'Unable to save inquiry at this moment. Please try calling our helpline.';
        }
    }
}

// Fetch active services for inquiry dropdown
$service_options = [];
try {
    $stmt_srv = $pdo->query("SELECT service_name FROM services WHERE is_active = 1 ORDER BY service_name ASC");
    $service_options = $stmt_srv->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}

include __DIR__ . '/includes/header.php';
?>

    <!-- Hero Banner Start -->
    <section class="detail-hero">
        <div class="container">
            <div aria-label="breadcrumb">
                <ol class="breadcrumb detail-breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
                </ol>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="detail-kicker"><i class="fas fa-headset me-2"></i> CITIZEN SUPPORT</span>
                    <h1>Citizen Support &amp; Grievance Desk</h1>
                    <p>Have questions regarding digital card generation, verification status, or doorstep visits? Get in touch with our team.</p>
                </div>
            </div>
        </div>
    </section>
    <!-- Hero Banner End -->

    <!-- Contact Info Cards Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-xl-8">
                    <div class="row g-5">
                        <div class="col-lg-4">
                            <div class="position-relative h-100 wow fadeIn" data-wow-delay="0.3s">
                                <img class="img-fluid rounded w-100 h-100" style="object-fit: cover;"
                                    src="image/faq.png" alt="Citizen Support">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="wow fadeIn" data-wow-delay="0.1s">
                                <h5 class="mb-3 text-primary"><i class="fa fa-leaf text-primary me-2"></i>Contact Us</h5>
                                <h2 class="display-5 mb-4" style="font-size: calc(1.5rem + 1.5vw);">Any Query? Please Contact Us!</h2>
                            </div>
                            <h4 class="lh-base mb-4 fadeIn" data-wow-delay="0.2s" style="font-size: 1.15rem;">Please Get in Touch and Let's Assist You with Digital Services &amp; Facilitation</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <a class="btn btn-lg-square btn-primary" href="<?= htmlspecialchars(SOCIAL_TWITTER ?: '#') ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                                <a class="btn btn-lg-square btn-primary" href="<?= htmlspecialchars(SOCIAL_FACEBOOK ?: '#') ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                                <a class="btn btn-lg-square btn-primary" href="<?= htmlspecialchars(SOCIAL_YOUTUBE ?: '#') ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                                <a class="btn btn-lg-square btn-primary" href="<?= htmlspecialchars(SOCIAL_LINKEDIN ?: '#') ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="bg-light rounded p-4 p-md-5">
                        <p class="mb-2"><i class="fa fa-envelope text-primary me-2"></i>Email Address:</p>
                        <h5 class="mb-4"><a href="mailto:<?= htmlspecialchars(SUPPORT_EMAIL) ?>" class="text-dark text-decoration-none"><?= htmlspecialchars(SUPPORT_EMAIL) ?></a></h5>
                        <p class="mb-2"><i class="fa fa-phone-alt text-primary me-2"></i>Phone No:</p>
                        <h5 class="mb-4"><a href="tel:<?= preg_replace('/[^0-9]/', '', HELPLINE_NUMBER) ?>" class="text-dark text-decoration-none"><?= htmlspecialchars(HELPLINE_NUMBER) ?></a></h5>
                        <p class="mb-2"><i class="fa fa-map-marker-alt text-primary me-2"></i>Location:</p>
                        <h5 class="mb-0"><?= htmlspecialchars(OFFICE_ADDRESS) ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact Info Cards End -->

    <!-- Contact Form Start -->
    <div class="container-fluid bg-light py-5">
        <div class="container py-5">
            <div class="row g-4 justify-content-center">
                <div class="col-12 col-xl-8 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="bg-white rounded p-4 p-md-5">
                        <h2 class="mb-3" style="font-size: calc(1.4rem + 1vw); font-weight: 700;">Send Us a Message</h2>
                        <p class="lh-base mb-4 text-muted" style="font-size: 1rem;">Complete this secure form to reach our dedicated citizen support desk. We typically respond within 24 business hours.</p>

                        <?php if ($success_msg): ?>
                            <div class="alert alert-success border-0 shadow-sm p-4 mb-4" style="border-radius: 10px;">
                                <div class="d-flex gap-3 align-items-center">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                    <div>
                                        <h5 class="fw-bold text-success mb-1">Inquiry Registered Successfully!</h5>
                                        <p class="mb-0 text-secondary"><?= htmlspecialchars($success_msg) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($err_msg): ?>
                            <div class="alert alert-danger py-2 mb-4"><?= htmlspecialchars($err_msg) ?></div>
                        <?php endif; ?>

                        <form method="POST" action="contact.php">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name" required placeholder="Your Name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" />
                                        <label for="name">Your Full Name <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" placeholder="Your Email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
                                        <label for="email">Your Email Address</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="mobile" name="mobile" maxlength="10" required placeholder="Mobile" value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" />
                                        <label for="mobile">10-Digit Mobile Number <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="service_interest" name="service_interest">
                                            <option value="General Inquiry">General Citizen Inquiry</option>
                                            <?php foreach ($service_options as $sname): ?>
                                                <option value="<?= htmlspecialchars($sname) ?>" <?= (isset($_POST['service_interest']) && $_POST['service_interest'] === $sname) ? 'selected' : '' ?>><?= htmlspecialchars($sname) ?></option>
                                            <?php endforeach; ?>
                                            <option value="Doorstep Assistance">Doorstep Executive Query</option>
                                            <option value="Payment / Order Issue">Payment / Order Download Issue</option>
                                        </select>
                                        <label for="service_interest">Service of Interest</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="subject" name="subject" placeholder="Subject" value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>" />
                                        <label for="subject">Subject</label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-floating">
                                        <textarea style="height: 165px" class="form-control" id="message" name="message" required placeholder="Message"><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                        <label for="message">Leave your query or message here <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button class="btn btn-primary btn-custom rounded-pill px-5 py-3" type="submit">
                                        <i class="fas fa-paper-plane me-2"></i> Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Contact Form End -->

<?php include __DIR__ . '/includes/footer.php'; ?>
