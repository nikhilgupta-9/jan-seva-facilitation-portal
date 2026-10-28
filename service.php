<?php
// service.php - Citizen Services Catalog (Dynamic from DB)
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Citizen Identity & Government Services - ' . APP_NAME;
$metaDescription = 'Explore all digital identity cards and official government facilitation services.';
$activeNav = 'services';

$search_query = trim($_GET['q'] ?? '');
$category_filter = trim($_GET['cat'] ?? '');

$sql = "SELECT * FROM services WHERE is_active = 1";
$params = [];

if (!empty($category_filter) && $category_filter !== 'all') {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (service_name LIKE ? OR short_desc LIKE ? OR category LIKE ?)";
    $like = '%' . $search_query . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY service_type ASC, id ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Distinct categories for filter buttons
$cat_stmt = $pdo->query("SELECT DISTINCT category FROM services WHERE is_active = 1 ORDER BY category ASC");
$all_categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

include __DIR__ . '/includes/header.php';
?>

    <!-- Hero Banner Start -->
    <section class="detail-hero">
        <div class="container">
            <div aria-label="breadcrumb">
                <ol class="breadcrumb detail-breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="all-scheme.php">Government Schemes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Citizen Services</li>
                </ol>
            </div>

            <div class="row align-items-center">
                <div class="col-lg-8">
                    <span class="detail-kicker"><i class="fas fa-landmark me-2"></i> GOVERNMENT SERVICES</span>
                    <h1>Citizen Identity &amp; Government Services</h1>
                    <p>Direct real-time digital generation for supported cards, and assisted step-by-step guidance for official government departments.</p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="track-status.php" class="btn btn-portal-primary">
                        <i class="fas fa-search me-1"></i> Track Existing Order
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!-- Hero Banner End -->

    <!-- Services Section Start -->
    <div class="container-fluid light-bg cate-section py-5">
        <div class="container">
            <div class="row align-items-center mb-4">
                <div class="col-sm-12 col-md-8">
                    <div class="wow fadeIn" data-wow-delay="0.1s">
                        <h1 class="display-5 mb-2 fs-2">Available Citizen Services</h1>
                        <p class="text-muted mb-0">Select an identity card or government welfare service to proceed with application or assisted guidance.</p>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 text-md-end mt-3 mt-md-0">
                    <a class="btn btn-primary btn-custom rounded-pill" href="all-scheme.php">
                        <i class="fas fa-list me-1"></i> View Welfare Schemes
                    </a>
                </div>
            </div>

            <!-- Categories / Filter tabs -->
            <div class="d-flex flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <a href="service.php" class="btn btn-sm <?= empty($category_filter) || $category_filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
                    All Services
                </a>
                <?php foreach ($all_categories as $cat): ?>
                    <a href="service.php?cat=<?= urlencode($cat) ?>" class="btn btn-sm <?= ($category_filter === $cat) ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill px-3">
                        <?= htmlspecialchars($cat) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Services Grid -->
            <div class="row g-4">
                <?php if (empty($services)): ?>
                    <div class="col-12 py-5 text-center">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4 class="text-secondary">No services found matching your criteria.</h4>
                        <a href="service.php" class="btn btn-primary btn-custom rounded-pill mt-3">Reset Filters</a>
                    </div>
                <?php else: ?>
                    <?php 
                    $default_imgs = [
                        'image/services/1.jpg',
                        'image/services/2.jpg',
                        'image/services/3.jpg',
                        'image/services/4.jpg',
                        'image/services/5.jpg',
                        'image/services/6.jpg',
                    ];
                    $idx = 0;
                    foreach ($services as $srv): 
                        $img_src = !empty($srv['icon_image']) && file_exists(__DIR__ . '/' . $srv['icon_image'])
                            ? $srv['icon_image'] 
                            : $default_imgs[$idx % count($default_imgs)];
                        $idx++;
                    ?>
                        <div class="col-sm-12 col-md-6 col-lg-4 wow fadeIn" data-wow-delay="0.1s">
                            <div class="service-item position-relative overflow-hidden bg-white h-100 shadow-sm rounded">
                                <div class="position-relative overflow-hidden">
                                    <img class="img-fluid w-100" src="<?= htmlspecialchars($img_src) ?>" alt="<?= htmlspecialchars($srv['service_name']) ?>" style="height: 190px; object-fit: cover;">
                                    <span class="badge position-absolute top-0 end-0 m-3 <?= $srv['service_type'] === 'api' ? 'bg-success' : 'bg-primary' ?> px-3 py-2 rounded-pill">
                                        <?= $srv['service_type'] === 'api' ? 'Instant API' : 'Assisted' ?>
                                    </span>
                                </div>
                                <div class="p-4 d-flex flex-column justify-content-between" style="min-height: 210px;">
                                    <div>
                                        <span class="text-muted small text-uppercase fw-bold"><?= htmlspecialchars($srv['category'] ?? 'General') ?></span>
                                        <h4 class="mb-2 fs-5 service-title mt-1"><?= htmlspecialchars($srv['service_name']) ?></h4>
                                        <p class="text-secondary small mb-3"><?= htmlspecialchars($srv['short_desc'] ?? '') ?></p>
                                    </div>
                                    <div>
                                        <a class="btn btn-primary btn-custom rounded-pill w-100" href="servise-detail.php?slug=<?= urlencode($srv['slug']) ?>">
                                            <?= $srv['service_type'] === 'api' ? 'Apply Now (₹' . intval($srv['fee']) . ')' : 'Check Guidelines &rarr;' ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Services Section End -->

<?php include __DIR__ . '/includes/footer.php'; ?>
