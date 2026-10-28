<?php
require_once __DIR__ . '/config/config.php';

http_response_code(404);

$pageTitle = 'Page Not Found - ' . APP_NAME;
$metaDescription = 'The page you are looking for does not exist.';
$activeNav = '';

include __DIR__ . '/includes/header.php';
?>

    <section class="detail-hero">
        <div class="container">
            <div aria-label="breadcrumb">
                <ol class="breadcrumb detail-breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">404 Error</li>
                </ol>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="detail-kicker"><i class="fas fa-exclamation-triangle me-2"></i> ERROR 404</span>
                    <h1>Page Not Found</h1>
                    <p>The page you requested is unavailable or has been relocated.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- 404 Start -->
    <div class="container-fluid bg-light py-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container text-center py-5">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <i class="bi bi-exclamation-triangle display-1 text-primary"></i>
                    <h1 class="display-1">404</h1>
                    <h2 class="mb-4">Page Not Found</h2>
                    <p class="mb-4">We're sorry, the page you have looked for does not exist on our portal. You can return to the homepage or search for citizen services.</p>
                    <a class="btn btn-primary btn-custom rounded-pill px-4 py-2" href="index.php">Go Back To Home</a>
                </div>
            </div>
        </div>
    </div>
    <!-- 404 End -->

<?php include __DIR__ . '/includes/footer.php'; ?>
