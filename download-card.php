<?php
// download-card.php - High-Resolution PVC Card Display & Download Center
require_once __DIR__ . '/config/config.php';

$order_id = trim($_GET['order_id'] ?? '');

if (empty($order_id)) {
    header('Location: ' . BASE_URL . '/track-status.php');
    exit;
}

// Fetch order and request
$stmt = $pdo->prepare("SELECT o.*, s.service_name, s.slug, s.category, sr.card_data_json, sr.created_at as issued_at 
                       FROM orders o 
                       JOIN services s ON o.service_id = s.id 
                       LEFT JOIN service_requests sr ON o.order_id = sr.order_id 
                       WHERE o.order_id = ? LIMIT 1");
$stmt->execute([$order_id]);
$record = $stmt->fetch();

if (!$record) {
    $pageTitle = 'Order Not Found - ' . APP_NAME;
    include __DIR__ . '/includes/header.php';
    echo '<div class="container py-5 text-center">
            <div class="card border-0 shadow-sm p-5 mx-auto" style="max-width: 500px; border-radius: 12px;">
                <i class="fas fa-search-minus fa-3x text-warning mb-3"></i>
                <h4 class="fw-bold text-dark">Order Reference Not Found</h4>
                <p class="text-muted small">We could not locate any card generated for order reference <code>' . htmlspecialchars($order_id) . '</code>.</p>
                <a href="' . BASE_URL . '/track-status.php" class="btn btn-primary fw-bold">Track Status &amp; Search Orders</a>
            </div>
          </div>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

$card_data = json_decode($record['card_data_json'] ?? '{}', true);
$pageTitle = 'Download Digital Card - ' . $record['service_name'];
$activeNav = 'services';

include __DIR__ . '/includes/header.php';
?>

<style>
@media print {
    body * { visibility: hidden; }
    .printable-card-area, .printable-card-area * { visibility: visible; }
    .printable-card-area {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .no-print { display: none !important; }
}
</style>

<section class="detail-hero no-print">
    <div class="container">
        <div aria-label="breadcrumb">
            <ol class="breadcrumb detail-breadcrumb">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/track-status.php">Track Status</a></li>
                <li class="breadcrumb-item active" aria-current="page">Download Card (<?= htmlspecialchars($order_id) ?>)</li>
            </ol>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="detail-kicker"><i class="fas fa-check-circle mr-2"></i> VERIFIED &amp; ISSUED</span>
                <h1>Digital Identity Card Ready</h1>
                <p>Your electronic card has been processed and formatted into standard CR80 PVC dimensions with secure QR code verification.</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <button type="button" onclick="window.print();" class="btn btn-portal-primary me-2">
                    <i class="fas fa-print me-1"></i> Print Card
                </button>
                <button type="button" id="btn-download-image" class="btn btn-outline-portal">
                    <i class="fas fa-download me-1"></i> Save as PNG
                </button>
            </div>
        </div>
    </div>
</section>

<!-- Main content -->
<div class="detail-main">
    <div class="container">
        <div class="row">
            <!-- Left Column: Realistic PVC Card Preview -->
            <div class="col-lg-7 printable-card-area">
                <section class="detail-panel" style="padding: 30px;">
                    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
                        <div>
                            <span class="section-eyebrow">OFFICIAL CR80 PVC PASS</span>
                            <h2 style="font-size: 1.3rem; margin: 0;"><?= htmlspecialchars($record['service_name']) ?></h2>
                        </div>
                        <span class="info-badge"><i class="fas fa-shield-alt"></i> Authentic Digital Pass</span>
                    </div>

                    <!-- Card Render Container -->
                    <div id="pvc-card-render" class="pvc-id-card card-<?= strtolower($card_data['type'] ?? 'abha') ?>" style="max-width: 480px; height: 280px; margin: 0 auto; box-shadow: 0 12px 28px rgba(0,0,0,0.18);">
                        <div class="card-header-gov">
                            <div class="card-gov-emblem">
                                <svg viewBox="0 0 100 100" style="width: 30px; height: 30px;">
                                    <circle cx="50" cy="50" r="42" stroke="#f9a825" stroke-width="4" fill="none" />
                                    <circle cx="50" cy="50" r="10" fill="#0b3c5d" />
                                    <?php for ($i = 0; $i < 24; $i++): $ang = $i * 15; ?>
                                        <line x1="50" y1="50" x2="<?= 50 + 35 * cos(deg2rad($ang)) ?>" y2="<?= 50 + 35 * sin(deg2rad($ang)) ?>" stroke="#0b3c5d" stroke-width="2" />
                                    <?php endfor; ?>
                                </svg>
                            </div>
                            <div class="card-header-titles">
                                <h4><?= htmlspecialchars($card_data['title'] ?? 'Official Identity Card') ?></h4>
                                <p><?= htmlspecialchars($card_data['authority'] ?? 'Govt of India Facilitation System') ?></p>
                            </div>
                            <div class="card-gov-emblem text-end">
                                <i class="fas fa-shield-alt text-success fs-4"></i>
                            </div>
                        </div>

                        <div class="card-body-content">
                            <div class="card-photo-box">
                                <img src="<?= htmlspecialchars($card_data['photo'] ?? BASE_URL . '/images/tuser1.jpg') ?>" alt="Applicant Photo">
                            </div>

                            <table class="card-details-table">
                                <tr>
                                    <td class="label-col">Name:</td>
                                    <td class="val-col"><?= htmlspecialchars($card_data['name'] ?? 'N/A') ?></td>
                                </tr>
                                <?php if (!empty($card_data['abha_number'])): ?>
                                    <tr><td class="label-col">ABHA No:</td><td class="val-col"><?= htmlspecialchars($card_data['abha_number']) ?></td></tr>
                                    <tr><td class="label-col">ABHA Addr:</td><td class="val-col"><?= htmlspecialchars($card_data['abha_address']) ?></td></tr>
                                <?php elseif (!empty($card_data['apaar_id'])): ?>
                                    <tr><td class="label-col">APAAR ID:</td><td class="val-col"><?= htmlspecialchars($card_data['apaar_id']) ?></td></tr>
                                    <tr><td class="label-col">School/Inst:</td><td class="val-col"><?= htmlspecialchars($card_data['institution']) ?></td></tr>
                                <?php elseif (!empty($card_data['uan_number'])): ?>
                                    <tr><td class="label-col">UAN No:</td><td class="val-col"><?= htmlspecialchars($card_data['uan_number']) ?></td></tr>
                                    <tr><td class="label-col">Occupation:</td><td class="val-col"><?= htmlspecialchars($card_data['occupation']) ?></td></tr>
                                <?php elseif (!empty($card_data['epic_number'])): ?>
                                    <tr><td class="label-col">EPIC No:</td><td class="val-col"><?= htmlspecialchars($card_data['epic_number']) ?></td></tr>
                                    <tr><td class="label-col">Constituency:</td><td class="val-col"><?= htmlspecialchars($card_data['constituency']) ?></td></tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="label-col">DOB / Gender:</td>
                                    <td class="val-col"><?= htmlspecialchars($card_data['dob'] ?? '') ?> (<?= htmlspecialchars($card_data['gender'] ?? '') ?>)</td>
                                </tr>
                                <tr>
                                    <td class="label-col">State:</td>
                                    <td class="val-col"><?= htmlspecialchars($card_data['state'] ?? 'India') ?></td>
                                </tr>
                            </table>

                            <div class="card-qr-box">
                                <img src="<?= htmlspecialchars($card_data['qr_code'] ?? '') ?>" alt="Verification QR" style="width: 100%; height: 100%;">
                            </div>
                        </div>

                        <div class="card-id-highlight">
                            <?= htmlspecialchars($card_data['abha_number'] ?? ($card_data['apaar_id'] ?? ($card_data['uan_number'] ?? ($card_data['epic_number'] ?? $order_id)))) ?>
                        </div>

                        <div class="card-footer-strip">
                            <span>Verified Citizen Identity &bull; Authentic Digital Pass</span>
                            <span>Issued Date: <?= htmlspecialchars($card_data['issued_date'] ?? date('d/m/Y')) ?></span>
                        </div>
                    </div>

                    <!-- Print Instructions -->
                    <div class="no-print mt-4 p-3 bg-light rounded border small text-secondary">
                        <strong><i class="fas fa-lightbulb text-warning me-1"></i> Printing Recommendation:</strong> For physical wallet usage, print using a color inkjet or thermal PVC card printer on standard CR80 PVC card stock (30 mil thickness).
                    </div>
                </section>
            </div>

            <!-- Right Column: Order Audit & Receipt Details -->
            <div class="col-lg-5 no-print">
                <section class="detail-panel" style="padding: 26px;">
                    <div class="panel-heading mb-3">
                        <div>
                            <span class="section-eyebrow">FACILITATION RECEIPT</span>
                            <h2>Order Audit Summary</h2>
                        </div>
                        <span class="heading-icon"><i class="fas fa-receipt"></i></span>
                    </div>

                    <table class="table table-borderless small mb-0">
                        <tr>
                            <td class="text-muted ps-0">Order Reference:</td>
                            <td class="text-end fw-bold"><code><?= htmlspecialchars($record['order_id']) ?></code></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Payment Transaction:</td>
                            <td class="text-end font-monospace small"><?= htmlspecialchars($record['payment_id'] ?? 'MOCK_SANDBOX_SUCCESS') ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Service Name:</td>
                            <td class="text-end fw-bold"><?= htmlspecialchars($record['service_name']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Amount Paid:</td>
                            <td class="text-end fw-bold text-success fs-6"><?= format_inr($record['amount']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Payment Status:</td>
                            <td class="text-end"><span class="badge bg-success">PAID / VERIFIED</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted ps-0">Issuance Timestamp:</td>
                            <td class="text-end"><?= date('d M Y, h:i A', strtotime($record['issued_at'] ?? $record['created_at'])) ?></td>
                        </tr>
                    </table>

                    <div class="mt-4 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/track-status.php" class="btn btn-outline-portal w-100">
                            <i class="fas fa-search me-1"></i> Look up Another Order
                        </a>
                    </div>
                </section>
            </div>
        </div>

        <div class="detail-disclaimer">
            <i class="fas fa-info-circle"></i>
            <p><strong>Disclaimer:</strong> <?= htmlspecialchars(LEGAL_DISCLAIMER) ?></p>
        </div>
    </div>
</div>

<!-- html2canvas Download Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var btnDownload = document.getElementById('btn-download-image');
    if (btnDownload) {
        btnDownload.addEventListener('click', function() {
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Rendering PNG...');

            var element = document.getElementById('pvc-card-render');
            html2canvas(element, {
                scale: 3,
                useCORS: true,
                backgroundColor: null
            }).then(function(canvas) {
                var image = canvas.toDataURL('image/png');
                var link = document.createElement('a');
                link.download = '<?= $record['order_id'] ?>_PVC_CARD.png';
                link.href = image;
                link.click();
                btn.prop('disabled', false).html('<i class="fas fa-download me-1"></i> Save as PNG');
            }).catch(function(err) {
                alert('Error generating card image: ' + err);
                btn.prop('disabled', false).html('<i class="fas fa-download me-1"></i> Save as PNG');
            });
        });
    }
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
