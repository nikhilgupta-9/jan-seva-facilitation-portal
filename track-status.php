<?php
// track-status.php - Citizen Application & ID Card Tracker (Original GIGW Template Design)
require_once __DIR__ . '/config/config.php';

$pageTitle = 'Track Card Status & Download History - ' . APP_NAME;
$activeNav = 'track';

$query_input = trim($_GET['q'] ?? '');
$orders_found = [];

if (!empty($query_input)) {
    $clean_phone = preg_replace('/[^0-9]/', '', $query_input);
    $stmt = $pdo->prepare("SELECT o.*, s.service_name, s.slug, s.category, u.phone, u.name as citizen_name, sr.created_at as card_issued_at 
                           FROM orders o 
                           JOIN services s ON o.service_id = s.id 
                           LEFT JOIN users u ON o.user_id = u.id 
                           LEFT JOIN service_requests sr ON o.order_id = sr.order_id 
                           WHERE o.order_id = ? OR u.phone = ? 
                           ORDER BY o.id DESC");
    $stmt->execute([$query_input, $clean_phone]);
    $orders_found = $stmt->fetchAll();
}

include __DIR__ . '/includes/header.php';
?>

<section class="detail-hero">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Track Status</li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="detail-kicker"><i class="fas fa-search-location mr-2"></i> REAL-TIME QUERY</span>
                <h1>Track Your Card Request</h1>
                <p>Check the real-time issuance status of your digital PVC ID card or re-download your verified card.</p>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<div class="detail-main">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Search Panel -->
                <section class="detail-panel" style="padding: 30px;">
                    <div class="panel-heading mb-3">
                        <div>
                            <span class="section-eyebrow">ORDER LOOKUP</span>
                            <h2>Search by Mobile or Order ID</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-search"></i></span>
                    </div>
                    <p class="section-intro mb-4">Enter your registered <strong>10-Digit Mobile Number</strong> or unique <strong>Order Reference ID</strong> (e.g. <code>ORD-20261028-XXXX</code>):</p>

                    <form method="GET" action="">
                        <div class="input-group input-group-lg search-header">
                            <input type="text" name="q" class="form-control" placeholder="e.g. 9876543210 or ORD-20261028-XXXX" value="<?= htmlspecialchars($query_input) ?>" required>
                            <button class="btn btn-primary search-btn px-4" type="submit">
                                <i class="fas fa-search me-1"></i> Check Status
                            </button>
                        </div>
                    </form>
                </section>

                <!-- Results Section -->
                <?php if (!empty($query_input)): ?>
                    <?php if (!empty($orders_found)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="fw-bold mb-0" style="font-size: 1.15rem; color: #1C1B3A;">
                                <i class="fas fa-check-circle text-success me-2"></i> Found <?= count($orders_found) ?> Record(s)
                            </h4>
                            <span class="text-muted small">Query: "<?= htmlspecialchars($query_input) ?>"</span>
                        </div>

                        <?php foreach ($orders_found as $item): ?>
                            <section class="detail-panel" style="padding: 24px;">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom pb-3 mb-3">
                                    <div>
                                        <span class="category-badge"><?= htmlspecialchars($item['category']) ?></span>
                                        <h3 class="d-inline-block ms-2 mb-0" style="font-size: 1.1rem; color: #1C1B3A; font-weight: 700;">
                                            <?= htmlspecialchars($item['service_name']) ?>
                                        </h3>
                                    </div>
                                    <div>
                                        <?php if ($item['status'] === 'paid'): ?>
                                            <span class="info-badge" style="background: #e8f5e9; color: #166534;"><i class="fas fa-check me-1"></i> Verified &amp; Paid</span>
                                        <?php else: ?>
                                            <span class="info-badge" style="background: #fff8e1; color: #b45309;"><i class="fas fa-clock me-1"></i> Pending Payment</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="row g-2 small mb-3">
                                    <div class="col-sm-6">
                                        <div class="text-secondary mb-1"><strong>Order Reference:</strong> <code><?= htmlspecialchars($item['order_id']) ?></code></div>
                                        <div class="text-secondary mb-1"><strong>Citizen Name:</strong> <?= htmlspecialchars($item['citizen_name'] ?? 'Applicant') ?></div>
                                        <div class="text-secondary"><strong>Registered Mobile:</strong> +91-XXXXXX<?= substr($item['phone'] ?? '9999', -4) ?></div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="text-secondary mb-1"><strong>Amount:</strong> <?= format_inr($item['amount']) ?></div>
                                        <div class="text-secondary mb-1"><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($item['created_at'])) ?></div>
                                        <div class="text-secondary"><strong>Payment ID:</strong> <span class="font-monospace"><?= htmlspecialchars($item['payment_id'] ?? 'Pending') ?></span></div>
                                    </div>
                                </div>

                                <div class="text-end pt-2 border-top">
                                    <?php if ($item['status'] === 'paid'): ?>
                                        <a href="<?= BASE_URL ?>/download-card.php?order_id=<?= urlencode($item['order_id']) ?>" class="btn btn-portal-primary">
                                            <i class="fas fa-id-card me-1"></i> View &amp; Download Digital PVC Card &rarr;
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>/servise-detail.php?slug=<?= urlencode($item['slug']) ?>" class="btn btn-outline-portal">
                                            <i class="fas fa-credit-card me-1"></i> Complete Payment (<?= format_inr($item['amount']) ?>) &rarr;
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </section>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <section class="detail-panel text-center" style="padding: 40px 20px;">
                            <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                            <h3 style="font-size: 1.3rem;">No Records Found</h3>
                            <p class="text-muted small mx-auto mb-4" style="max-width: 450px;">
                                We couldn't locate any records for "<strong><?= htmlspecialchars($query_input) ?></strong>". Please verify your 10-digit mobile number or unique Order ID.
                            </p>
                            <div>
                                <a href="<?= BASE_URL ?>/all-scheme.php" class="btn btn-portal-primary">
                                    <i class="fas fa-th-large me-1"></i> Browse Schemes &amp; Services
                                </a>
                            </div>
                        </section>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="detail-disclaimer">
            <i class="fas fa-info-circle"></i>
            <p><strong>Disclaimer:</strong> <?= htmlspecialchars(LEGAL_DISCLAIMER) ?></p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
