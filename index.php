<?php
// index.php - Government Schemes & Citizen Services Portal Homepage (Original GIGW Design)
require_once __DIR__ . '/config/config.php';

$pageTitle = APP_NAME . ' - Government Schemes & Citizen Services Portal';
$metaDescription = 'Explore government schemes, check eligibility and access citizen services.';
$activeNav = 'home';

include __DIR__ . '/includes/header.php';
?>

    <div class="container-fluid p-0">
        <div id="header-carousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <a href="servise-detail.php?slug=abha-card">
                        <img class="w-100" src="img/ABHA Card Apply Healthcare Banner.png" alt="ABHA Health Card">
                    </a>
                </div>
                <div class="carousel-item">
                    <a href="servise-detail.php?slug=eshram-card">
                        <img class="w-100" src="img/e-Shram Card Benefits Banner.png" alt="e-Shram Card Benefits">
                    </a>
                </div>
                <div class="carousel-item">
                    <a href="servise-detail.php?slug=eshram-card">
                        <img class="w-100" src="img/e-Shram Card Application Banner.png" alt="e-Shram Card Application">
                    </a>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#header-carousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#header-carousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
    <!-- Header End -->

    <!-- Update section -->
    <section class="updata-section">
        <div class="container">
            <div class="updata-body row">
                <div class="updata-heading heading fs-4 fw-600 col-sm-12 col-md-4">
                    Schemes &amp; Updates
                </div>
                <div class="marquee-body col-sm-12 col-md-8">
                    <marquee behavior="scroll" direction="left" scrollamount="5">
                        Get the latest updates on government schemes, public services,
                        application processes, eligibility criteria, and important notifications.
                    </marquee>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Category Section -->
    <div class="container-fluid light-bg cate-section py-5">
        <div class="container">

            <div class="row mb-3">
                <div class="col-sm-12 col-md-9">
                    <div class="wow fadeIn" data-wow-delay="0.1s">
                        <span class="text-primary text-uppercase fw-bold small"><i class="fas fa-shield-alt me-1"></i> Fast Digital Issuance &amp; Verification</span>
                        <h1 class="display-5 mb-3 fs-2">Citizen Services &amp; Digital Identity Passes</h1>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3 text-md-end">
                    <a class="btn btn-primary btn-custom d-none d-lg-inline-flex rounded-pill ms-auto me-0"
                        href="all-scheme.php" style="text-align: center; width: fit-content;">All Schemes &rarr;</a>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-sm-12 col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="service-item position-relative overflow-hidden bg-white h-100">
                        <div class="position-relative overflow-hidden mb-4">
                            <img class="img-fluid w-100" src="image/services/1.jpg" alt="ABHA Card">
                        </div>
                        <div class="p-3">
                            <div class="mb-3 fs-5 service-title">Ayushman Bharat Health Account (ABHA)</div>
                            <a class="btn btn-primary btn-custom rounded-pill" href="servise-detail.php?slug=abha-card">Apply Now (₹50)</a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="service-item position-relative overflow-hidden bg-white h-100">
                        <div class="position-relative overflow-hidden mb-4">
                            <img class="img-fluid w-100" src="image/services/2.jpg" alt="APAAR ID">
                        </div>
                        <div class="p-3">
                            <div class="mb-3 fs-5 service-title">APAAR Student ID Card</div>
                            <a class="btn btn-primary btn-custom rounded-pill" href="servise-detail.php?slug=apaar-card">Apply Now (₹50)</a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="service-item position-relative overflow-hidden bg-white h-100">
                        <div class="position-relative overflow-hidden mb-4">
                            <img class="img-fluid w-100" src="image/services/3.jpg" alt="e-Shram Card">
                        </div>
                        <div class="p-3">
                            <div class="mb-3 fs-5 service-title">e-Shram Worker UAN Card</div>
                            <a class="btn btn-primary btn-custom rounded-pill" href="servise-detail.php?slug=eshram-card">Apply Now (₹50)</a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="service-item position-relative overflow-hidden bg-white h-100">
                        <div class="position-relative overflow-hidden mb-4">
                            <img class="img-fluid w-100" src="image/services/4.jpg" alt="Voter ID">
                        </div>
                        <div class="p-3">
                            <div class="mb-3 fs-5 service-title">Voter ID (EPIC) Verification</div>
                            <a class="btn btn-primary btn-custom rounded-pill" href="servise-detail.php?slug=voter-id">Verify Now (₹50)</a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="service-item position-relative overflow-hidden bg-white h-100">
                        <div class="position-relative overflow-hidden mb-4">
                            <img class="img-fluid w-100" src="image/services/5.jpg" alt="Aadhaar Services">
                        </div>
                        <div class="p-3">
                            <div class="mb-3 fs-5 service-title">Aadhaar Services &amp; Enrollment</div>
                            <a class="btn btn-primary btn-custom rounded-pill" href="servise-detail.php?slug=aadhaar-services">View Guide</a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="service-item position-relative overflow-hidden bg-white h-100">
                        <div class="position-relative overflow-hidden mb-4">
                            <img class="img-fluid w-100" src="image/services/6.jpg" alt="Ration Card">
                        </div>
                        <div class="p-3">
                            <div class="mb-3 fs-5 service-title">State NFSA Ration Card</div>
                            <a class="btn btn-primary btn-custom rounded-pill" href="servise-detail.php?slug=ration-card">View Guide</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schemes & Benefits -->
    <section class="py-5 bg-light" id="schemes">
        <div class="container">

            <!-- Section Heading -->
            <div class="text-center mb-4">
                <span class="badge bg-primary mb-3">
                    GOVERNMENT OF INDIA
                </span>
                <h2 class="fw-bold">Government Schemes</h2>
                <p class="text-secondary">
                    Explore government schemes, benefits and official application portals.
                </p>
            </div>

            <!-- Category Filter -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-5" id="schemeFilters">
                <button class="btn btn-primary rounded-pill filter-btn" data-filter="all">All Schemes</button>
                <button class="btn btn-outline-primary rounded-pill filter-btn" data-filter="farmers">Farmers</button>
                <button class="btn btn-outline-primary rounded-pill filter-btn" data-filter="health">Health</button>
                <button class="btn btn-outline-primary rounded-pill filter-btn" data-filter="workers">Workers</button>
                <button class="btn btn-outline-primary rounded-pill filter-btn" data-filter="business">Business</button>
                <button class="btn btn-outline-primary rounded-pill filter-btn" data-filter="education">Education</button>
            </div>

            <!-- Scheme Cards -->
            <div class="row g-4" id="schemeList">

                <div class="col-sm-6 col-lg-4 scheme-item" data-category="farmers">
                    <div class="card h-100 border-0 shadow-sm scheme-card">
                        <div class="card-body p-4">
                            <div class="fs-1 mb-1">🌾</div>
                            <span class="badge bg-success-subtle text-success mb-1">
                                Farmers
                            </span>
                            <h5 class="card-title fw-bold">PM-KISAN Samman Nidhi</h5>
                            <p class="card-text text-secondary">
                                Financial assistance for eligible farmer families.
                            </p>
                            <a href="https://pmkisan.gov.in/" target="_blank" rel="noopener" class="scheme-btn">
                                Official Website ↗
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4 scheme-item" data-category="health">
                    <div class="card h-100 border-0 shadow-sm scheme-card">
                        <div class="card-body p-4">
                            <div class="fs-1 mb-1">🏥</div>
                            <span class="badge bg-danger-subtle text-danger mb-1">
                                Health
                            </span>
                            <h5 class="card-title fw-bold">Ayushman Bharat PM-JAY</h5>
                            <p class="card-text text-secondary">
                                Hospital treatment coverage for eligible beneficiaries.
                            </p>
                            <a href="https://pmjay.gov.in/" target="_blank" rel="noopener" class="scheme-btn">
                                Official Website ↗
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4 scheme-item" data-category="workers">
                    <div class="card h-100 border-0 shadow-sm scheme-card">
                        <div class="card-body p-4">
                            <div class="fs-1 mb-1">👷</div>
                            <span class="badge bg-warning-subtle text-dark mb-1">
                                Workers
                            </span>
                            <h5 class="card-title fw-bold">e-Shram Registration</h5>
                            <p class="card-text text-secondary">
                                Registration portal for eligible unorganised workers.
                            </p>
                            <a href="https://eshram.gov.in/" target="_blank" rel="noopener" class="scheme-btn">
                                Official Website ↗
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4 scheme-item" data-category="business">
                    <div class="card h-100 border-0 shadow-sm scheme-card">
                        <div class="card-body p-4">
                            <div class="fs-1 mb-1">🏢</div>
                            <span class="badge bg-info-subtle text-info mb-1">
                                Business
                            </span>
                            <h5 class="card-title fw-bold">PM MUDRA Yojana</h5>
                            <p class="card-text text-secondary">
                                Business loan information through participating lenders.
                            </p>
                            <a href="https://www.mudra.org.in/" target="_blank" rel="noopener" class="scheme-btn">
                                Official Website ↗
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-lg-4 scheme-item" data-category="education">
                    <div class="card h-100 border-0 shadow-sm scheme-card">
                        <div class="card-body p-4">
                            <div class="fs-1 mb-1">🎓</div>
                            <span class="badge bg-primary-subtle text-primary mb-1">
                                Education
                            </span>
                            <h5 class="card-title fw-bold">National Scholarship Portal</h5>
                            <p class="card-text text-secondary">
                                Scholarship information for eligible students.
                            </p>
                            <a href="https://scholarships.gov.in/" target="_blank" rel="noopener" class="scheme-btn">
                                Official Website ↗
                            </a>
                        </div>
                    </div>
                </div>

            </div>

            <p id="noResults" class="text-center text-secondary mt-4" hidden>No schemes found.</p>

            <div class="text-center mt-5">
                <a href="https://www.myscheme.gov.in/" target="_blank" rel="noopener" class="scheme-btn">
                    Explore All Schemes →
                </a>
            </div>

        </div>
    </section>

    <!-- Process Section -->
    <section class="process-section"
        style="background: linear-gradient(#d5e8f9eb,#d5e7f9eb) , url('image/Digital.jpg'); background-position: center; background-size: contain;">
        <div class="container">

            <div class="row process-row p-4 py-5 pb-3 mb-4">
                <div class="row">
                    <div class="col-sm-12 col-md-9">
                        <div class="wow fadeIn" data-wow-delay="0.1s">
                            <h1 class="display-5 mb-5 fs-2">Rooted in Agriculture, Driven by Passion</h1>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-3">
                        <a class="btn btn-primary btn-custom d-none d-lg-flex rounded-pill ms-auto me-0"
                            href="contact.php" style="text-align: center; width: fit-content;">View All Services</a>
                    </div>
                </div>

                <!-- Step 1 -->
                <div class="col-lg-3 col-sm-6 mb-4">
                    <div class="process-card">
                        <div class="process-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <span class="step-number">STEP 01</span>
                        <h4>Explore Schemes</h4>
                        <p>Discover government schemes according to your needs and category.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="col-lg-3 col-sm-6 mb-4">
                    <div class="process-card">
                        <div class="process-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <span class="step-number">STEP 02</span>
                        <h4>Check Eligibility</h4>
                        <p>Review the eligibility criteria and benefits of each scheme.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="col-lg-3 col-sm-6 mb-4">
                    <div class="process-card">
                        <div class="process-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <span class="step-number">STEP 03</span>
                        <h4>Prepare Documents</h4>
                        <p>Check the required documents before starting your application.</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="col-lg-3 col-sm-6 mb-4">
                    <div class="process-card">
                        <div class="process-icon">
                            <i class="fas fa-external-link-alt"></i>
                        </div>
                        <span class="step-number">STEP 04</span>
                        <h4>Visit Official Portal</h4>
                        <p>Continue to the official government website for application instructions.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Government Scheme Documents -->
                <div class="col-lg-4 col-md-6 mb-3 ps-0">
                    <div class="p-4 rounded h-100 aadhaar-card aadhaar-card1 documents">
                        <div class="fs-5 heading">Documents Required for Government Schemes</div>
                        <p class="mb-4">
                            Aadhaar Card | Income Certificate |
                            Residence Proof | Bank Details
                        </p>
                        <a href="https://www.myscheme.gov.in/" class="scheme-btn" target="_blank" rel="noopener">
                            Explore Scheme Requirements
                        </a>
                    </div>
                </div>

                <!-- Government Service Centres -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="p-4 rounded h-100 aadhaar-card aadhaar-card1 centre">
                        <div class="fs-5 heading">Find Your Nearest Government Service Centre</div>
                        <p class="mb-4">
                            Find official citizen service portals and information
                            about government services available in your state.
                        </p>
                        <a href="https://services.india.gov.in/" class="scheme-btn" target="_blank" rel="noopener">
                            Explore Government Services
                        </a>
                    </div>
                </div>

                <!-- Citizen Help -->
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="p-4 rounded h-100 aadhaar-card aadhaar-card1 help">
                        <div class="fs-5 heading">Need Help with Government Services?</div>
                        <p class="mb-4">
                            Find official helpline numbers, public grievance
                            portals and citizen support resources.
                        </p>
                        <a href="https://pgportal.gov.in/" class="scheme-btn mt-3" target="_blank" rel="noopener">
                            Submit a Grievance
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Start -->
    <div class="container-fluid py-5 about-section">
        <div class="container">
            <div class="row g-5">
                <div class="col-xl-9">
                    <div class="wow fadeIn" data-wow-delay="0.1s">
                        <h5 class="mb-3" style="color: orange;"><i class="fa fa-leaf me-2"></i>About Us</h5>
                        <h1 class="display-5 mb-5" style="color: white;">Empowering Citizens, Enabling a Better Future</h1>
                    </div>
                    <div class="row g-5">
                        <div class="col-lg-4">
                            <div class="position-relative h-100 wow fadeIn" data-wow-delay="0.1s">
                                <img class="img-fluid rounded w-100 h-100" style="object-fit: cover;"
                                    src="image/about-us-1.png" alt="">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <h4 class="lh-base mb-4 wow fadeIn" style="color: white;" data-wow-delay="0.1s">We help
                                citizens discover government schemes and access important public services with ease</h4>
                            <p class="mb-4 wow fadeIn text-light" data-wow-delay="0.2s">Our platform brings together scheme
                                information, eligibility criteria, required documents and application procedures.</p>
                            <div class="about-items-2 bg-light rounded">
                                <div class="row g-0">
                                    <div class="col-md-6 wow fadeIn" data-wow-delay="0.3s">
                                        <div class="h-100 p-5 border-end border-bottom">
                                            <i class="fa fa-seedling fa-3x text-primary mb-3"></i>
                                            <h5>Easy Access to Schemes</h5>
                                            <span>Comprehensive access to state and central digital schemes.</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6 wow fadeIn" data-wow-delay="0.4s">
                                        <div class="h-100 p-5">
                                            <i class="fa fa-leaf fa-3x text-primary mb-3"></i>
                                            <h5>Trusted Official Resources</h5>
                                            <span>Direct official portal integration and document eligibility checks.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 d-none d-xl-block">
                    <div class="position-relative h-100 wow fadeIn" data-wow-delay="0.3s">
                        <img class="img-fluid rounded w-100 h-100" style="object-fit: cover;" src="image/about-us.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- About End -->

    <section class="mt-5">
        <div class="container gov-banner">
            <div class="row no-gutters align-items-stretch">
                <!-- Left Content -->
                <div class="col-lg-6 col-md-6">
                    <div class="gov-banner-content">
                        <span class="gov-tag">
                            <i class="fas fa-landmark mr-2"></i>
                            Digital India | Citizen Services
                        </span>
                        <h2>Government Services Made Simple</h2>
                        <p>
                            Explore government schemes, apply for essential
                            cards, and access important public services
                            through one convenient platform.
                        </p>
                        <a href="all-scheme.php" class="gov-btn">
                            Explore Our Services
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>
                <!-- Right Image -->
                <div class="col-lg-6 col-md-6 px-0">
                    <div class="gov-banner-image">
                        <img src="image/section.png" alt="Digital Government Services">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Start -->
    <div class="container-fluid py-5">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-xl-9">
                    <div class="row g-5">
                        <div class="col-lg-4">
                            <div class="position-relative h-100 wow fadeIn" data-wow-delay="0.3s">
                                <img class="img-fluid rounded w-100 h-100" style="object-fit: cover;" src="image/team.png" alt="">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="wow fadeIn" data-wow-delay="0.1s">
                                <h5 class="mb-3"><i class="fa fa-leaf text-primary me-2"></i>Features</h5>
                                <h1 class="display-5 mb-5">Why Choose Our Services</h1>
                            </div>
                            <p class="mb-4 fadeIn" data-wow-delay="0.2s">Get trusted guidance and easy application
                                support to access government schemes and citizen services.</p>
                            <div class="row g-4">
                                <div class="col-md-6 wow fadeIn" data-wow-delay="0.3s">
                                    <div class="d-flex align-items-center bg-light rounded h-100 p-4">
                                        <div class="flex-shrink-0 btn-lg-square rounded bg-primary text-dark me-3">
                                            <i class="fa-solid fa-file-circle-check text-light fs-4"></i>
                                        </div>
                                        <h5 class="lh-base mb-0">Easy Application Process</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 wow fadeIn" data-wow-delay="0.4s">
                                    <div class="d-flex align-items-center bg-light rounded h-100 p-4">
                                        <div class="flex-shrink-0 btn-lg-square rounded bg-primary text-dark me-3">
                                            <i class="fa-solid fa-user-tie text-light fs-4"></i>
                                        </div>
                                        <h5 class="lh-base mb-0">Expert Assistance</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 wow fadeIn" data-wow-delay="0.5s">
                                    <div class="d-flex align-items-center bg-light rounded h-100 p-4">
                                        <div class="flex-shrink-0 btn-lg-square rounded bg-primary text-dark me-3">
                                            <i class="fa-solid fa-circle-check text-light fs-4"></i>
                                        </div>
                                        <h5 class="lh-base mb-0">Reliable Information</h5>
                                    </div>
                                </div>
                                <div class="col-md-6 wow fadeIn" data-wow-delay="0.6s">
                                    <div class="d-flex align-items-center bg-light rounded h-100 p-4">
                                        <div class="flex-shrink-0 btn-lg-square rounded bg-primary text-dark me-3">
                                            <i class="fa-solid fa-handshake-angle fs-4 text-light"></i>
                                        </div>
                                        <h5 class="lh-base mb-0">Citizen-Focused Services</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 d-none d-xl-block">
                    <div class="position-relative h-100 wow fadeIn" data-wow-delay="0.5s">
                        <img class="img-fluid rounded w-100 h-100 mb-n4" style="object-fit: cover;" src="image/pen.png" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Features End -->

    <!-- FAQ Section Start -->
    <section class="gov-faq-section" id="faq" style="background: linear-gradient(#d5e8f9eb,#d5e7f9eb) , url('image/Digital.jpg'); background-position: center; background-size: contain;">
        <div class="container">
            <div class="faq-heading text-center">
                <span class="faq-subtitle">
                    <i class="fas fa-question-circle"></i> Help Center
                </span>
                <h2>Frequently Asked Questions</h2>
                <p>
                    Find answers to common questions about government cards, schemes, eligibility and online applications.
                </p>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="faq-image">
                        <img src="image/faq.png" alt="Government service assistance">
                        <div class="faq-image-content">
                            <i class="fas fa-headset"></i>
                            <div>
                                <h5>Need Assistance?</h5>
                                <p>Find the information you need.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="faq-list" id="govFaqAccordion">
                        <div class="faq-item active-faq">
                            <button class="faq-question" type="button" aria-expanded="true">
                                <span>1. What services are available on this portal?</span>
                                <i class="fas fa-minus faq-icon"></i>
                            </button>
                            <div class="faq-answer" style="display:block;">
                                <p>
                                    The portal provides digital generation assistance for ABHA Health Card, APAAR Student ID, e-Shram Card, and Voter ID, along with assisted guidance for Aadhaar and Ration Card services.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button" aria-expanded="false">
                                <span>2. How can I apply for an ABHA Card?</span>
                                <i class="fas fa-plus faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>
                                    Visit the ABHA service page, enter your basic details, verify your mobile number with OTP, pay the nominal ₹50 facilitation fee, and download your verified PVC Health Card instantly.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button" aria-expanded="false">
                                <span>3. Who can apply for an e-Shram Card?</span>
                                <i class="fas fa-plus faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>
                                    Any unorganised worker between 16 and 59 years of age who is not an income tax payee or EPFO/ESIC member can register and obtain a 12-digit UAN Card.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button" aria-expanded="false">
                                <span>4. Which documents are required?</span>
                                <i class="fas fa-plus faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>
                                    Basic identity proof (Aadhaar or Voter ID), active mobile number for OTP verification, and scheme-specific details such as school registration or occupation details.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button" aria-expanded="false">
                                <span>5. How can I check my application status?</span>
                                <i class="fas fa-plus faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>
                                    You can check the real-time issuance status or re-download your PVC card anytime using your 10-digit mobile number on our <a href="track-status.php">Track Status</a> page.
                                </p>
                            </div>
                        </div>

                        <div class="faq-item">
                            <button class="faq-question" type="button" aria-expanded="false">
                                <span>6. Is this an official government website?</span>
                                <i class="fas fa-plus faq-icon"></i>
                            </button>
                            <div class="faq-answer">
                                <p>
                                    No, this is a private citizen facilitation platform. We charge a nominal service fee of ₹50 for electronic processing and PVC card formatting. Official government portals remain accessible directly at zero cost.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- FAQ Section End -->

    <!-- Testimonial Start -->
    <div class="container-fluid bg-light py-5">
        <div class="container py-5">
            <div class="section-title wow fadeIn" data-wow-delay="0.1s">
                <h5 class="mb-3"><i class="fas fa-comments text-primary me-2"></i>Citizen Feedback</h5>
                <h1 class="display-5 mb-5">What Citizens Say About Our Services</h1>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                    <div class="bg-white rounded h-100 p-4">
                        <i class="fa fa-quote-left fa-5x text-primary mb-2"></i>
                        <p>Got my ABHA Health Card in under 2 minutes with QR code. Very easy mobile OTP process.</p>
                        <hr class="w-25 my-4">
                        <div class="d-flex">
                            <img class="flex-shrink-0 rounded" width="50" height="50" src="img/testimonial-1.jpg" alt="">
                            <div class="ms-3">
                                <h5 class="mb-1">Rajesh Kumar</h5>
                                <span>Delhi</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.3s">
                    <div class="bg-white rounded h-100 p-4">
                        <i class="fa fa-quote-left fa-5x text-primary mb-2"></i>
                        <p>The doorstep assistant visited my grandfather for pension and ration card guidance. Excellent initiative.</p>
                        <hr class="w-25 my-4">
                        <div class="d-flex">
                            <img class="flex-shrink-0 rounded" width="50" height="50" src="img/testimonial-2.jpg" alt="">
                            <div class="ms-3">
                                <h5 class="mb-1">Pooja Sharma</h5>
                                <span>Lucknow</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.5s">
                    <div class="bg-white rounded h-100 p-4">
                        <i class="fa fa-quote-left fa-5x text-primary mb-2"></i>
                        <p>Generated my e-Shram card and downloaded the printable PVC format immediately. Very satisfied.</p>
                        <hr class="w-25 my-4">
                        <div class="d-flex">
                            <img class="flex-shrink-0 rounded" width="50" height="50" src="img/testimonial-3.jpg" alt="">
                            <div class="ms-3">
                                <h5 class="mb-1">Manoj Verma</h5>
                                <span>Jaipur</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Testimonial End -->

<?php include __DIR__ . '/includes/footer.php'; ?>
