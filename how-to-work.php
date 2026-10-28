<?php
require_once __DIR__ . '/config/config.php';

$pageTitle = 'How It Works - ' . APP_NAME;
$metaDescription = 'Explore schemes, check eligibility, prepare documents, and visit the official portal.';
$activeNav = 'howitworks';

include __DIR__ . '/includes/header.php';
?>

    <section class="detail-hero">
        <div class="container">
            <div aria-label="breadcrumb">
                <ol class="breadcrumb detail-breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Government Schemes</li>
                </ol>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="detail-kicker"><i class="fas fa-landmark mr-2"></i> SCHEME</span>
                    <h1> All Government Schemes</h1>
                    <p>Understand scheme benefits, eligibility conditions, required documents and how to find the
                        official application process.</p>
                </div>
            </div>
        </div>
    </section>

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
                        <a class="btn btn-primary btn-custom  d-none d-lg-flex  rounded-pill  ms-auto me-0  "
                            href="contact.php" style="text-align: center; width: fit-content;">View All Services</a>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 mb-4 ">
                    <div class="process-card">
                        <div class="process-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <span class="step-number">STEP 01</span>
                        <h4>Explore Schemes</h4>
                        <p>Discover government schemes according to your needs and category.</p>
                    </div>
                </div>

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

                <div class="col-lg-4 col-md-6 mb-3 ">
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

                <div class="col-lg-4 col-md-6 mb-3  ">
                    <div class="p-4 rounded h-100 aadhaar-card aadhaar-card1 help">
                        <div class="fs-5 heading ">Need Help with Government Services?</div>
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
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
