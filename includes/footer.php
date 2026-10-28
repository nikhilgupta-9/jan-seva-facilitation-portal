    <!-- Citizen Support & Facilitation Ribbon (Replaces non-professional newsletter) -->
    <section class="citizen-support-ribbon py-4 text-white wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="row g-3 g-lg-4 align-items-center">
                <!-- Helpline Card -->
                <div class="col-lg-3 col-sm-6">
                    <div class="d-flex align-items-center gap-3 support-ribbon-card h-100">
                        <div class="support-ribbon-icon bg-warning text-dark">
                            <i class="fas fa-phone-alt"></i>
                        </div>
                        <div>
                            <span class="text-white-50 d-block small" style="font-size: 0.75rem;">Citizen Helpline</span>
                            <a href="tel:<?= preg_replace('/[^0-9]/', '', HELPLINE_NUMBER) ?>" class="text-white fw-bold text-decoration-none fs-6">
                                <?= htmlspecialchars(HELPLINE_NUMBER) ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Grievance Desk -->
                <div class="col-lg-3 col-sm-6">
                    <div class="d-flex align-items-center gap-3 support-ribbon-card h-100">
                        <div class="support-ribbon-icon bg-primary text-white border border-secondary">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div>
                            <span class="text-white-50 d-block small" style="font-size: 0.75rem;">Citizen Grievance Desk</span>
                            <a href="<?= BASE_URL ?>/contact.php" class="text-white fw-bold text-decoration-none fs-6">
                                Register Inquiry &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Doorstep Booking -->
                <div class="col-lg-3 col-sm-6">
                    <div class="d-flex align-items-center gap-3 support-ribbon-card h-100">
                        <div class="support-ribbon-icon bg-success text-white">
                            <i class="fas fa-home"></i>
                        </div>
                        <div>
                            <span class="text-white-50 d-block small" style="font-size: 0.75rem;">In-Person Facilitation</span>
                            <a href="<?= BASE_URL ?>/doorstep-booking.php" class="text-white fw-bold text-decoration-none fs-6">
                                Book Home Visit &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Track Card Status -->
                <div class="col-lg-3 col-sm-6">
                    <div class="d-flex align-items-center gap-3 support-ribbon-card h-100">
                        <div class="support-ribbon-icon bg-info text-white">
                            <i class="fas fa-search-location"></i>
                        </div>
                        <div>
                            <span class="text-white-50 d-block small" style="font-size: 0.75rem;">Existing Card Requests</span>
                            <a href="<?= BASE_URL ?>/track-status.php" class="text-white fw-bold text-decoration-none fs-6">
                                Track &amp; Download &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Civic Footer Start (IGNOU / GIGW Indian Government Standard) -->
    <footer class="container-fluid footer text-light pt-5 pb-4 wow fadeIn" data-wow-delay="0.1s">
        <div class="container">
            <div class="row g-4 g-lg-5">
                <!-- Col 1: Portal Identity & Contact Desk -->
                <div class="col-lg-4 col-md-6">
                    <div class="footer-brand-box mb-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="footer-emblem-badge text-warning fs-3">
                                <i class="fa-solid fa-landmark-dome"></i>
                            </div>
                            <div>
                                <span class="d-block text-warning small fw-bold" style="font-size: 0.72rem;"><?= htmlspecialchars(APP_NAME_HI) ?></span>
                                <h5 class="text-white fw-bold mb-0"><?= htmlspecialchars(APP_NAME) ?></h5>
                            </div>
                        </div>
                        <p class="text-white-50 small mb-3 lh-sm">
                            An independent digital public facilitator portal providing doorstep and online digital pass issuance, scheme awareness, and assisted government services for Indian citizens.
                        </p>
                    </div>

                    <div class="footer-contact-details small text-white-50">
                        <p class="mb-2"><i class="fa fa-map-marker-alt text-warning me-2"></i><?= htmlspecialchars(OFFICE_ADDRESS) ?></p>
                        <p class="mb-2"><i class="fa fa-phone-alt text-warning me-2"></i><strong>Helpline:</strong> <?= htmlspecialchars(HELPLINE_NUMBER) ?> <?= defined('PHONE_SECONDARY') && PHONE_SECONDARY ? ' | ' . htmlspecialchars(PHONE_SECONDARY) : '' ?></p>
                        <p class="mb-2"><i class="fa fa-envelope text-warning me-2"></i><strong>Email:</strong> <?= htmlspecialchars(SUPPORT_EMAIL) ?></p>
                        <p class="mb-3"><i class="fa fa-clock text-warning me-2"></i><strong>Timings:</strong> <?= htmlspecialchars(OPERATING_HOURS) ?></p>
                    </div>

                    <div class="d-flex gap-2 pt-1">
                        <?php if (defined('SOCIAL_TWITTER') && SOCIAL_TWITTER): ?>
                            <a class="btn btn-sm btn-square btn-outline-light rounded-circle" href="<?= htmlspecialchars(SOCIAL_TWITTER) ?>" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (defined('SOCIAL_FACEBOOK') && SOCIAL_FACEBOOK): ?>
                            <a class="btn btn-sm btn-square btn-outline-light rounded-circle" href="<?= htmlspecialchars(SOCIAL_FACEBOOK) ?>" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (defined('SOCIAL_YOUTUBE') && SOCIAL_YOUTUBE): ?>
                            <a class="btn btn-sm btn-square btn-outline-light rounded-circle" href="<?= htmlspecialchars(SOCIAL_YOUTUBE) ?>" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                        <?php if (defined('SOCIAL_INSTAGRAM') && SOCIAL_INSTAGRAM): ?>
                            <a class="btn btn-sm btn-square btn-outline-light rounded-circle" href="<?= htmlspecialchars(SOCIAL_INSTAGRAM) ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (defined('SOCIAL_LINKEDIN') && SOCIAL_LINKEDIN): ?>
                            <a class="btn btn-sm btn-square btn-outline-light rounded-circle" href="<?= htmlspecialchars(SOCIAL_LINKEDIN) ?>" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Col 2: Citizen Identity & Digital Passes (Interlinked) -->
                <div class="col-lg-3 col-md-6 col-sm-6">
                    <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">
                        <i class="fas fa-id-card text-warning me-2"></i>Digital Identity Passes
                    </h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= BASE_URL ?>/service/abha-card"><i class="fas fa-chevron-right me-2 text-warning small"></i>ABHA Health ID Card (₹50)</a></li>
                        <li><a href="<?= BASE_URL ?>/service/apaar-card"><i class="fas fa-chevron-right me-2 text-warning small"></i>APAAR / ABC Student ID (₹50)</a></li>
                        <li><a href="<?= BASE_URL ?>/service/eshram-card"><i class="fas fa-chevron-right me-2 text-warning small"></i>e-Shram Worker Card (₹50)</a></li>
                        <li><a href="<?= BASE_URL ?>/service/voter-id"><i class="fas fa-chevron-right me-2 text-warning small"></i>Voter ID Verification (₹50)</a></li>
                        <li><a href="<?= BASE_URL ?>/service/aadhaar-services"><i class="fas fa-chevron-right me-2 text-warning small"></i>Aadhaar Services (ASK Center)</a></li>
                        <li><a href="<?= BASE_URL ?>/service/ration-card"><i class="fas fa-chevron-right me-2 text-warning small"></i>State Ration Card (NFSA)</a></li>
                        <li><a href="<?= BASE_URL ?>/service/pan-card"><i class="fas fa-chevron-right me-2 text-warning small"></i>PAN Card Correction Guide</a></li>
                        <li><a href="<?= BASE_URL ?>/service/edistrict-certificates"><i class="fas fa-chevron-right me-2 text-warning small"></i>e-District Revenue Services</a></li>
                        <li class="pt-1"><a href="<?= BASE_URL ?>/service.php" class="text-warning fw-bold"><i class="fas fa-th-large me-2"></i>All Citizen Services Catalog &rarr;</a></li>
                    </ul>
                </div>

                <!-- Col 3: Schemes & Welfare Initiatives (Interlinked) -->
                <div class="col-lg-2 col-md-6 col-sm-6">
                    <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">
                        <i class="fas fa-landmark text-warning me-2"></i>Citizen Welfare
                    </h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="<?= BASE_URL ?>/all-scheme.php"><i class="fas fa-chevron-right me-2 text-warning small"></i>All Welfare Schemes</a></li>
                        <li><a href="<?= BASE_URL ?>/eligibility-checker.php"><i class="fas fa-chevron-right me-2 text-warning small"></i>Eligibility Checker</a></li>
                        <li><a href="<?= BASE_URL ?>/how-to-work.php"><i class="fas fa-chevron-right me-2 text-warning small"></i>How It Works Guide</a></li>
                        <li><a href="<?= BASE_URL ?>/track-status.php"><i class="fas fa-chevron-right me-2 text-warning small"></i>Track Card Status</a></li>
                        <li><a href="<?= BASE_URL ?>/doorstep-booking.php"><i class="fas fa-chevron-right me-2 text-warning small"></i>Doorstep Executive</a></li>
                        <li><a href="<?= BASE_URL ?>/about.php"><i class="fas fa-chevron-right me-2 text-warning small"></i>About Our Mission</a></li>
                        <li><a href="<?= BASE_URL ?>/contact.php"><i class="fas fa-chevron-right me-2 text-warning small"></i>Grievance Redressal</a></li>
                    </ul>
                </div>

                <!-- Col 4: Official National Portals & GIGW Policies (Like IGNOU / GIGW) -->
                <div class="col-lg-3 col-md-6">
                    <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">
                        <i class="fas fa-link text-warning me-2"></i>Official Govt Links
                    </h5>
                    <ul class="list-unstyled footer-links mb-4">
                        <li><a href="https://india.gov.in" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt me-2 text-warning small"></i>National Portal of India</a></li>
                        <li><a href="https://digilocker.gov.in" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt me-2 text-warning small"></i>DigiLocker Platform</a></li>
                        <li><a href="https://mygov.in" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt me-2 text-warning small"></i>MyGov Citizen Portal</a></li>
                        <li><a href="https://nha.gov.in" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt me-2 text-warning small"></i>National Health Authority (NHA)</a></li>
                        <li><a href="https://eshram.gov.in" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt me-2 text-warning small"></i>Ministry of Labour (e-Shram)</a></li>
                        <li><a href="https://myaadhaar.uidai.gov.in" target="_blank" rel="noopener noreferrer"><i class="fas fa-external-link-alt me-2 text-warning small"></i>UIDAI (myAadhaar Portal)</a></li>
                    </ul>

                    <div class="p-2 rounded bg-black bg-opacity-25 border border-secondary">
                        <span class="d-block text-warning small fw-bold mb-1"><i class="fas fa-shield-alt me-1"></i> Data Security Assurance</span>
                        <p class="text-white-50 small mb-0" style="font-size: 0.72rem;">Citizen verification data is securely processed via 256-bit encrypted channels and never stored unnecessarily.</p>
                    </div>
                </div>
            </div>

            <!-- Mandatory Non-Governmental Facilitator Compliance Notice -->
            <div class="row mt-4 pt-3 border-top border-secondary">
                <div class="col-12">
                    <div class="p-3 rounded" style="background: rgba(255, 193, 7, 0.08); border-left: 4px solid #ffc107;">
                        <p class="mb-0 text-white-50 small" style="line-height: 1.6;">
                            <strong class="text-warning"><i class="fas fa-balance-scale me-1"></i> Statutory Compliance Disclaimer:</strong>
                            <?= htmlspecialchars(LEGAL_DISCLAIMER) ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Main Civic Footer End -->

    <!-- Copyright & GIGW Compliance Strip Start -->
    <div class="container-fluid copyright text-light py-3 border-top border-secondary" style="background: #0f172a;">
        <div class="container">
            <div class="row align-items-center gy-2">
                <div class="col-lg-5 text-center text-lg-start small text-white-50">
                    &copy; <?= date('Y') ?> <strong class="text-white"><?= htmlspecialchars(COMPANY_NAME) ?></strong>. All Rights Reserved.
                </div>
                <div class="col-lg-4 text-center small text-white-50">
                    <span class="badge bg-secondary bg-opacity-50 text-white me-2">GIGW 3.0 Standard</span>
                    <span>Last Updated: <?= date('d M Y') ?></span>
                </div>
                <div class="col-lg-3 text-center text-lg-end small">
                    <a class="text-white-50 text-decoration-none me-3 hover-warning" href="<?= BASE_URL ?>/contact.php">Help Desk</a>
                    <a class="text-warning text-decoration-none fw-bold" href="<?= BASE_URL ?>/admin/login.php" target="_blank">
                        <i class="fas fa-lock me-1"></i> Admin Console
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- Copyright End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top" aria-label="Back to top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/lib/wow/wow.min.js"></script>
    <script src="<?= BASE_URL ?>/lib/easing/easing.min.js"></script>
    <script src="<?= BASE_URL ?>/lib/waypoints/waypoints.min.js"></script>

    <!-- Template Javascript -->
    <script src="<?= BASE_URL ?>/js/main.js?v=<?= @filemtime(dirname(__DIR__) . '/js/main.js') ?: time() ?>"></script>

    <!-- GIGW Accessibility Handler (Font Resizer, Contrast, Language) -->
    <script>
    (function() {
        var root = document.documentElement;
        var currentFontSize = 100;
        var btnDec = document.getElementById('btn-font-dec');
        var btnInc = document.getElementById('btn-font-inc');
        var btnDark = document.getElementById('btn-theme-dark');
        var btnLight = document.getElementById('btn-theme-light');

        if (btnDec) {
            btnDec.addEventListener('click', function() {
                if (currentFontSize > 85) {
                    currentFontSize -= 5;
                    root.style.fontSize = currentFontSize + '%';
                }
            });
        }
        if (btnInc) {
            btnInc.addEventListener('click', function() {
                if (currentFontSize < 125) {
                    currentFontSize += 5;
                    root.style.fontSize = currentFontSize + '%';
                }
            });
        }
        if (btnDark) {
            btnDark.addEventListener('click', function() {
                document.body.classList.add('gigw-high-contrast');
                localStorage.setItem('contrast_mode', 'high');
            });
        }
        if (btnLight) {
            btnLight.addEventListener('click', function() {
                document.body.classList.remove('gigw-high-contrast');
                localStorage.setItem('contrast_mode', 'normal');
            });
        }

        if (localStorage.getItem('contrast_mode') === 'high') {
            document.body.classList.add('gigw-high-contrast');
        }

        // Mobile Dropdown Smooth Tap Handler
        document.querySelectorAll('.navbar .dropdown-toggle').forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    var menu = this.nextElementSibling;
                    if (menu && menu.classList.contains('dropdown-menu')) {
                        menu.classList.toggle('show');
                        this.classList.toggle('show');
                    }
                }
            });
        });
    })();
    </script>
</body>

</html>
