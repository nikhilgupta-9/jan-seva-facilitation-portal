<?php
/**
 * Shared page chrome: <head> through the end of the navbar.
 * Matches original GSP-Card / GIGW template design.
 */
require_once dirname(__DIR__) . '/config/config.php';

$pageTitle = $pageTitle ?? APP_NAME . ' - Government Schemes & Citizen Services Portal';
$metaDescription = $metaDescription ?? '';
$activeNav = $activeNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="<?= htmlspecialchars($metaDescription) ?>" name="description">

    <!-- Favicon -->
    <link href="<?= BASE_URL ?>/img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Comic+Relief:wght@400;700&family=Open+Sans:wght@400;600&display=swap"
        rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="<?= BASE_URL ?>/lib/animate/animate.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="<?= BASE_URL ?>/css/style.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/css/new-style.css" rel="stylesheet">

    <!-- Razorpay Checkout & html2canvas for card generation backend -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>

<body>

    <!-- top  header   -->
    <header>
        <div class="my-header">
            <div class="container header-body">
                <div class="header-left header-item">
                    <a href="<?= htmlspecialchars(SOCIAL_FACEBOOK ?: '#') ?>"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="<?= htmlspecialchars(SOCIAL_INSTAGRAM ?: '#') ?>"><i class="fa-brands fa-instagram"></i></a>
                    <a href="<?= htmlspecialchars(SOCIAL_YOUTUBE ?: '#') ?>"><i class="fa-brands fa-youtube"></i></a>
                    <span class="tagline d-none d-lg-inline"><?= htmlspecialchars(APP_TAGLINE ?: 'Simple Process, Easy Application') ?></span>
                </div>
                <div class="header-right header-item">
                    <div class="element" id="btn-font-dec" style="cursor: pointer;">A-</div>
                    <div class="element" id="btn-font-inc" style="cursor: pointer;">A+</div>
                    <div class="element" style="display:flex; gap:10px;">
                        <div class="light color-input-header" id="btn-theme-dark" style="background-color: black; cursor: pointer;"></div>
                        <div class="dark color-input-header" id="btn-theme-light" style="background-color: rgb(229, 228, 228); cursor: pointer;"></div>
                    </div>
                    <div class="element">
                        <span class="english me-2" id="lang-en" style="cursor: pointer;">English</span>
                        <span class="hindi" id="lang-hi" style="cursor: pointer;">Hindi</span>
                    </div>
                </div>

            </div>
        </div>
    </header>
    <!-- center  header   -->

    <div class="header-center-body">
        <div class="container">
            <div class="header-center d-flex py-3 align-items-center justify-content-between">
                <div>
                    <a href="<?= BASE_URL ?>/index.php" class="navbar-brand ms-4 ms-lg-0 d-none d-lg-flex align-items-center gap-3 text-decoration-none">
                        <div class="brand-emblem-icon d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; border-radius: 12px; background: #1C1B3A; color: #ffc107; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 10px rgba(28,27,58,0.15);">
                            <i class="fa-solid fa-landmark-dome"></i>
                        </div>
                        <div class="d-flex flex-column text-start">
                            <span class="hindi-brand text-muted" style="font-size: 0.74rem; font-weight: 600; line-height: 1.2; letter-spacing: 0.2px;"><?= htmlspecialchars(APP_NAME_HI) ?></span>
                            <span class="fs-4 fw-bold text-primary mb-0" style="line-height: 1.2; letter-spacing: -0.3px;"><?= htmlspecialchars(APP_NAME) ?></span>
                            <small class="text-secondary" style="font-size: 0.7rem; letter-spacing: 0.2px;"><?= htmlspecialchars(APP_TAGLINE) ?></small>
                        </div>
                    </a>
                </div>
                <div class="center-text"></div>
                <div class="d-flex align-items-center justify-content-center gap-4">
                    <form action="<?= BASE_URL ?>/service.php" method="GET">
                        <div class="input-group search-header">
                            <input type="search" name="search" class="form-control" placeholder="Search here..." aria-label="Search">
                            <button class="btn btn-primary search-btn px-4" type="submit">Search</button>
                        </div>
                    </form>
                    <a class="btn btn-primary btn-custom d-none d-lg-flex py-2" href="<?= BASE_URL ?>/contact.php">Contact Us</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navbar Start -->
    <div class="container-fluid p-0">
        <nav class="navbar navbar-expand-lg navbar-dark px-lg-5">
            <a href="<?= BASE_URL ?>/index.php" class="navbar-brand ms-3 ms-lg-0 d-flex d-lg-none align-items-center gap-2 text-decoration-none">
                <div class="brand-emblem-icon d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; border-radius: 8px; background: rgba(255,255,255,0.12); color: #ffc107; font-size: 1.15rem; flex-shrink: 0;">
                    <i class="fa-solid fa-landmark-dome"></i>
                </div>
                <div class="d-flex flex-column text-start">
                    <span class="fs-6 fw-bold text-white mb-0" style="line-height: 1.15;"><?= htmlspecialchars(APP_NAME) ?></span>
                    <small class="text-white-50" style="font-size: 0.65rem;"><?= htmlspecialchars(APP_TAGLINE) ?></small>
                </div>
            </a>
            <button type="button" class="navbar-toggler me-4" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav mx-auto p-4 p-lg-0 align-items-lg-center">
                    <!-- 1. Home -->
                    <a href="<?= BASE_URL ?>/index.php" class="nav-item nav-link <?= ($activeNav === 'home') ? 'active' : '' ?>">Home</a>

                    <!-- 2. Corporate Citizen Services Dropdown -->
                    <div class="nav-item dropdown">
                        <a href="<?= BASE_URL ?>/service.php" class="nav-link dropdown-toggle <?= in_array($activeNav, ['services', 'service-detail']) ? 'active' : '' ?>" data-bs-toggle="dropdown">
                            Citizen Services
                        </a>
                        <div class="dropdown-menu dropdown-menu-corporate dropdown-menu-corporate-wide border-top border-4 border-primary mt-2">
                            <div class="row g-2">
                                <!-- Col 1: Direct Generation -->
                                <div class="col-lg-6">
                                    <div class="dropdown-header text-uppercase text-primary fw-bold">
                                        <i class="fas fa-bolt text-warning me-1"></i> Instant Digital Cards (₹50)
                                    </div>
                                    <a href="<?= BASE_URL ?>/servise-detail.php?slug=abha-card" class="dropdown-item d-flex align-items-center gap-2">
                                        <span class="srv-icon-badge bg-danger text-white"><i class="fas fa-heartbeat"></i></span>
                                        <div>
                                            <div class="fw-bold srv-name">ABHA Health Card</div>
                                            <small class="text-muted d-block">Ayushman Bharat Health ID</small>
                                        </div>
                                    </a>
                                    <a href="<?= BASE_URL ?>/servise-detail.php?slug=apaar-card" class="dropdown-item d-flex align-items-center gap-2">
                                        <span class="srv-icon-badge bg-primary text-white"><i class="fas fa-graduation-cap"></i></span>
                                        <div>
                                            <div class="fw-bold srv-name">APAAR Student ID</div>
                                            <small class="text-muted d-block">One Nation One Student ID</small>
                                        </div>
                                    </a>
                                    <a href="<?= BASE_URL ?>/servise-detail.php?slug=eshram-card" class="dropdown-item d-flex align-items-center gap-2">
                                        <span class="srv-icon-badge bg-warning text-dark"><i class="fas fa-hard-hat"></i></span>
                                        <div>
                                            <div class="fw-bold srv-name">e-Shram Worker Card</div>
                                            <small class="text-muted d-block">Unorganized Worker Pass</small>
                                        </div>
                                    </a>
                                    <a href="<?= BASE_URL ?>/servise-detail.php?slug=voter-id" class="dropdown-item d-flex align-items-center gap-2">
                                        <span class="srv-icon-badge bg-success text-white"><i class="fas fa-id-card"></i></span>
                                        <div>
                                            <div class="fw-bold srv-name">Voter ID Verification</div>
                                            <small class="text-muted d-block">Digital EPIC Card Verification</small>
                                        </div>
                                    </a>
                                </div>

                                <!-- Col 2: Assisted Govt Guidance -->
                                <div class="col-lg-6">
                                    <div class="dropdown-header text-uppercase text-secondary fw-bold">
                                        <i class="fas fa-compass text-info me-1"></i> Assisted Govt Services
                                    </div>
                                    <a href="<?= BASE_URL ?>/servise-detail.php?slug=aadhaar-services" class="dropdown-item d-flex align-items-center gap-2">
                                        <span class="srv-icon-badge bg-info text-white"><i class="fas fa-fingerprint"></i></span>
                                        <div>
                                            <div class="fw-bold srv-name">Aadhaar Services (ASK)</div>
                                            <small class="text-muted d-block">Update &amp; Appointment Guidance</small>
                                        </div>
                                    </a>
                                    <a href="<?= BASE_URL ?>/servise-detail.php?slug=ration-card" class="dropdown-item d-flex align-items-center gap-2">
                                        <span class="srv-icon-badge bg-warning text-dark"><i class="fas fa-utensils"></i></span>
                                        <div>
                                            <div class="fw-bold srv-name">Ration Card (NFSA)</div>
                                            <small class="text-muted d-block">State Food &amp; Supply Portals</small>
                                        </div>
                                    </a>
                                    <a href="<?= BASE_URL ?>/servise-detail.php?slug=pan-card" class="dropdown-item d-flex align-items-center gap-2">
                                        <span class="srv-icon-badge bg-primary text-white"><i class="fas fa-address-card"></i></span>
                                        <div>
                                            <div class="fw-bold srv-name">PAN Card Correction</div>
                                            <small class="text-muted d-block">NSDL / UTIITSL Citizen Guide</small>
                                        </div>
                                    </a>
                                    <a href="<?= BASE_URL ?>/servise-detail.php?slug=edistrict-certificates" class="dropdown-item d-flex align-items-center gap-2">
                                        <span class="srv-icon-badge bg-secondary text-white"><i class="fas fa-landmark"></i></span>
                                        <div>
                                            <div class="fw-bold srv-name">e-District Services</div>
                                            <small class="text-muted d-block">Caste, Income &amp; Domicile Portals</small>
                                        </div>
                                    </a>
                                </div>

                                <!-- Bottom Catalog Link -->
                                <div class="col-12 pt-2 border-top text-center">
                                    <a href="<?= BASE_URL ?>/service.php" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold" style="font-size: 0.8rem;">
                                        <i class="fas fa-th-large me-1"></i> View All Services Catalog &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Schemes & Benefits -->
                    <a href="<?= BASE_URL ?>/all-scheme.php" class="nav-item nav-link <?= ($activeNav === 'schemes') ? 'active' : '' ?>">Schemes &amp; Benefits</a>

                    <!-- 4. Track Status (Highlighted) -->
                    <a href="<?= BASE_URL ?>/track-status.php" class="nav-item nav-link nav-link-track <?= ($activeNav === 'track') ? 'active' : '' ?>">
                        <i class="fas fa-search-location me-1 text-warning"></i> Track Status
                    </a>

                    <!-- 5. Corporate Citizen Guide Dropdown -->
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= in_array($activeNav, ['eligibility', 'how-to-work', 'doorstep', 'about']) ? 'active' : '' ?>" data-bs-toggle="dropdown">
                            Citizen Guide
                        </a>
                        <div class="dropdown-menu dropdown-menu-corporate dropdown-menu-corporate-single border-top border-4 border-primary mt-2">
                            <a href="<?= BASE_URL ?>/eligibility-checker.php" class="dropdown-item d-flex align-items-center gap-2 <?= ($activeNav === 'eligibility') ? 'active' : '' ?>">
                                <span class="srv-icon-badge bg-primary text-white"><i class="fas fa-user-check"></i></span>
                                <div>
                                    <div class="fw-bold srv-name">Eligibility Checker</div>
                                    <small class="text-muted d-block">Check qualification for welfare</small>
                                </div>
                            </a>
                            <a href="<?= BASE_URL ?>/how-to-work.php" class="dropdown-item d-flex align-items-center gap-2 <?= ($activeNav === 'how-to-work') ? 'active' : '' ?>">
                                <span class="srv-icon-badge bg-info text-white"><i class="fas fa-route"></i></span>
                                <div>
                                    <div class="fw-bold srv-name">How It Works</div>
                                    <small class="text-muted d-block">Simple 4-step facilitation process</small>
                                </div>
                            </a>
                            <a href="<?= BASE_URL ?>/doorstep-booking.php" class="dropdown-item d-flex align-items-center gap-2 <?= ($activeNav === 'doorstep') ? 'active' : '' ?>">
                                <span class="srv-icon-badge bg-warning text-dark"><i class="fas fa-home"></i></span>
                                <div>
                                    <div class="fw-bold srv-name">Doorstep Executive</div>
                                    <small class="text-muted d-block">Schedule home visit for documents</small>
                                </div>
                            </a>
                            <div class="dropdown-divider my-1"></div>
                            <a href="<?= BASE_URL ?>/about.php" class="dropdown-item d-flex align-items-center gap-2 <?= ($activeNav === 'about') ? 'active' : '' ?>">
                                <span class="srv-icon-badge bg-secondary text-white"><i class="fas fa-building"></i></span>
                                <div>
                                    <div class="fw-bold srv-name">About Our Portal</div>
                                    <small class="text-muted d-block">Facilitator mission &amp; compliance</small>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- 6. Contact Us -->
                    <a href="<?= BASE_URL ?>/contact.php" class="nav-item nav-link <?= ($activeNav === 'contact') ? 'active' : '' ?>">Contact Us</a>
                </div>
            </div>
        </nav>
    </div>
    <!-- Navbar End -->
