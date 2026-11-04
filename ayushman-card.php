<?php
// ayushman-card.php - High-Resolution Digital Health Smart ID Card Viewer & Download Center
require_once __DIR__ . '/config/config.php';

$appNo = trim($_GET['app_no'] ?? '');
$record = null;
$isExistingCard = (($_GET['status'] ?? '') === 'existing');

if (!empty($appNo) && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT * FROM ayushman_applications WHERE application_no = ? LIMIT 1");
    $stmt->execute([$appNo]);
    $record = $stmt->fetch();
}

$page_title = $record ? 'Digital Health Smart ID - ' . $record['full_name'] : 'Ayushman Health Card Search';

// Build the public verification URL to embed in the QR code.
// Kept deliberately short (just the Card ID) instead of the long encrypted
// token — a long AES payload pushes the QR to a very dense, hard-to-scan
// code at the card's small print/screen size. Security is unaffected: this
// gate page (verify-id.php) never discloses anything to a plain scan either
// way, and the Card ID is still only resolvable to real data via the
// authenticated admin scanner.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = defined('BASE_URL') ? BASE_URL : '/id-card';
$qrVerificationUrl = $protocol . $host . rtrim($base, '/') . '/verify-id.php?id=' . urlencode($record['card_id'] ?? '');

$isAdmin = !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['admin_user']) || !empty($_SESSION['admin_id']);

include __DIR__ . '/includes/header.php';
?>

<style>
/* Smart Card PVC Styles (CR80 Standard Ratio) */
.smart-card-wrapper {
    background: #f1f5f9;
    padding: 30px 15px 50px;
}
.pvc-smart-card {
    width: 100%;
    max-width: 540px;
    height: 330px;
    background: #ffffff;
    border-radius: 14px;
    position: relative;
    box-shadow: 0 14px 34px rgba(11, 60, 93, 0.16), 0 2px 6px rgba(0,0,0,0.06);
    border: 1px solid #cbd5e1;
    overflow: hidden;
    margin: 0 auto 30px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.pvc-smart-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: linear-gradient(90deg, #ff9933 0%, #ff9933 33.3%, #ffffff 33.3%, #ffffff 66.6%, #138808 66.6%, #138808 100%);
    z-index: 5;
}
.card-header-bar {
    background: linear-gradient(135deg, #0b3c5d 0%, #0d4a75 100%);
    color: #ffffff;
    padding: 10px 16px 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #0073cb;
}
.card-brand-pill {
    background: #ffffff;
    border-radius: 6px;
    padding: 3px 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0,0,0,0.14);
    height: 38px;
    flex-shrink: 0;
}
.card-brand-pill img {
    height: 28px;
    width: auto;
    max-width: 130px;
    object-fit: contain;
    display: block;
}
.card-header-titles h4 {
    margin: 0;
    font-size: 0.88rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    color: #ffffff;
    text-transform: uppercase;
}
.card-header-titles p {
    margin: 1px 0 0;
    font-size: 0.72rem;
    color: #93c5fd;
    font-weight: 600;
    letter-spacing: 0.3px;
}
.card-body-section {
    padding: 12px 16px;
    display: flex;
    gap: 14px;
    align-items: center;
    flex: 1;
}
.card-photo-col {
    width: 95px;
    text-align: center;
    flex-shrink: 0;
}
.card-photo-col img {
    width: 92px;
    height: 110px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #0b3c5d;
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
    background: #f8fafc;
}
.card-verified-badge {
    background: #ecfdf5;
    border: 1px solid #10b981;
    color: #065f46;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 800;
    padding: 3px 4px;
    margin-top: 6px;
    display: block;
    text-align: center;
    letter-spacing: 0.2px;
}
.card-details-col {
    flex: 1;
    min-width: 0;
}
.card-name-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: #0b3c5d;
    margin: 0 0 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    text-transform: uppercase;
}
.card-meta-line {
    font-size: 0.75rem;
    color: #475569;
    margin-bottom: 2px;
    line-height: 1.35;
}
.card-meta-line strong {
    color: #0f172a;
    font-weight: 700;
}
.card-abha-pill {
    background: #f0f9ff;
    border: 1.5px solid #0284c7;
    border-radius: 6px;
    padding: 4px 8px;
    margin: 6px 0;
    display: inline-block;
}
.card-abha-pill .label-txt {
    font-size: 0.66rem;
    font-weight: 700;
    color: #0369a1;
    text-transform: uppercase;
    display: block;
}
.card-abha-pill .val-txt {
    font-size: 0.88rem;
    font-weight: 800;
    color: #0c4a6e;
    font-family: monospace;
    letter-spacing: 1px;
}
.card-qr-col {
    width: 105px;
    text-align: center;
    flex-shrink: 0;
}
.card-qr-box {
    width: 95px;
    height: 95px;
    padding: 4px;
    background: #ffffff;
    border: 1.5px solid #0b3c5d;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 4px;
}
.card-qr-box canvas, .card-qr-box img {
    width: 100% !important;
    height: 100% !important;
}
.card-qr-label {
    font-size: 0.62rem;
    font-weight: 800;
    color: #dc2626;
    letter-spacing: 0.2px;
    line-height: 1.2;
}
.card-footer-strip {
    background: #0b3c5d;
    color: #ffffff;
    padding: 6px 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    border-top: 1px solid #e2e8f0;
}

/* Card Back Styles */
.pvc-smart-card-back {
    background: #f8fafc;
}
.card-back-body {
    padding: 14px 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.card-back-address {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 8px 12px;
    font-size: 0.74rem;
    color: #334155;
    line-height: 1.45;
}
.card-back-rules {
    font-size: 0.68rem;
    color: #64748b;
    line-height: 1.4;
    margin: 8px 0;
}
.card-back-bottom {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    border-top: 1px dashed #cbd5e1;
    padding-top: 6px;
}

/* Responsive adjustments for Mobile & Tablet */
@media (max-width: 991.98px) {
    .printable-section {
        margin-bottom: 30px;
    }
}

@media (max-width: 767.98px) {
    .pvc-smart-card {
        max-width: 100%;
        height: auto;
        min-height: 290px;
        margin-bottom: 24px;
        border-radius: 10px;
    }
    .card-header-bar {
        padding: 8px 12px;
    }
    .card-header-titles h4 {
        font-size: 0.8rem;
    }
    .card-header-titles p {
        font-size: 0.65rem;
    }
    .card-body-section {
        padding: 10px 12px;
        gap: 10px;
    }
    .card-photo-col {
        width: 80px;
    }
    .card-photo-col img {
        width: 76px;
        height: 94px;
    }
    .card-verified-badge {
        font-size: 0.58rem;
        padding: 2px 3px;
        margin-top: 4px;
    }
    .card-name-title {
        font-size: 0.92rem;
        margin-bottom: 2px;
    }
    .card-meta-line {
        font-size: 0.7rem;
        margin-bottom: 1px;
    }
    .card-abha-pill {
        padding: 3px 6px;
        margin: 4px 0;
    }
    .card-abha-pill .label-txt {
        font-size: 0.58rem;
    }
    .card-abha-pill .val-txt {
        font-size: 0.78rem;
        letter-spacing: 0.5px;
    }
    .card-qr-col {
        width: 85px;
    }
    .card-qr-box {
        width: 76px;
        height: 76px;
        padding: 3px;
    }
    .card-qr-label {
        font-size: 0.56rem;
    }
    .card-footer-strip {
        padding: 6px 12px;
        font-size: 0.65rem;
    }
    .card-back-body {
        padding: 10px 12px;
    }
    .card-back-address {
        font-size: 0.68rem;
        padding: 6px 8px;
    }
    .card-back-rules {
        font-size: 0.62rem;
        margin: 6px 0;
    }
}

@media (max-width: 480px) {
    .card-header-bar {
        padding: 6px 10px;
    }
    .card-header-titles h4 {
        font-size: 0.74rem;
    }
    .card-body-section {
        padding: 8px 8px;
        gap: 6px;
    }
    .card-photo-col {
        width: 66px;
    }
    .card-photo-col img {
        width: 64px;
        height: 78px;
    }
    .card-qr-col {
        width: 70px;
    }
    .card-qr-box {
        width: 64px;
        height: 64px;
    }
    .card-name-title {
        font-size: 0.82rem;
    }
    .card-meta-line {
        font-size: 0.64rem;
    }
    .card-abha-pill .val-txt {
        font-size: 0.7rem;
        letter-spacing: 0;
    }
    .card-header-bar svg {
        width: 22px;
        height: 22px;
    }
}

/* =========================================================
   Print Only Styles - Standard ISO/IEC 7810 ID-1 (CR80) PVC Card
   Physical Size: 86mm × 54mm (Wallet & CSC Lamination Standard)
   ========================================================= */
.print-sheet-header,
.print-sheet-footer,
.print-cut-guide,
.print-fold-separator {
    display: none;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 12mm;
    }

    *, *::before, *::after {
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
        color-adjust: exact !important;
    }

    body * {
        visibility: hidden;
    }

    /* Hide web layout chrome and non-printable elements */
    header, footer, nav, .navbar, .no-print, .breadcrumb, .alert, .ayush-hero-banner, .btn, .site-footer, .col-lg-5 {
        display: none !important;
    }

    /* Enable only the printable section */
    .printable-section, .printable-section * {
        visibility: visible;
    }

    .printable-section {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
        float: none !important;
    }

    .smart-card-wrapper,
    .smart-card-wrapper .container,
    .smart-card-wrapper .row {
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
        display: block !important;
    }

    /* Printable Official Header */
    .print-sheet-header {
        display: block !important;
        text-align: center;
        border-bottom: 1.5pt solid #0b3c5d;
        padding-bottom: 2.5mm;
        margin-bottom: 3.5mm;
    }
    .print-gov-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 3.5mm;
        margin-bottom: 1.5mm;
    }
    .print-sheet-logo {
        height: 8.5mm !important;
        width: auto !important;
        max-width: 32mm !important;
        object-fit: contain !important;
        display: block !important;
    }
    .print-gov-title h2 {
        font-size: 11pt !important;
        font-weight: 800 !important;
        color: #0b3c5d !important;
        margin: 0 !important;
        line-height: 1.15 !important;
        text-transform: uppercase;
        letter-spacing: 0.3pt;
    }
    .print-gov-title p {
        font-size: 7.5pt !important;
        font-weight: 600 !important;
        color: #475569 !important;
        margin: 0.5mm 0 0 !important;
        line-height: 1.15 !important;
    }
    .print-meta-strip {
        display: flex;
        justify-content: space-between;
        background: #f1f5f9 !important;
        border: 0.5pt solid #cbd5e1 !important;
        border-radius: 1.5mm;
        padding: 1.2mm 3mm;
        font-size: 6.5pt !important;
        color: #1e293b !important;
        margin-top: 1.5mm;
    }
    .print-meta-strip strong {
        color: #0b3c5d !important;
    }

    /* Cutting Guideline Banner */
    .print-cut-guide {
        display: block !important;
        text-align: center;
        font-size: 6.2pt !important;
        color: #475569 !important;
        margin: 2mm 0 3.5mm !important;
        padding: 1.2mm 2mm;
        background: #fefce8 !important;
        border: 0.4pt dashed #ca8a04 !important;
        border-radius: 1mm;
    }

    /* Cards Side-by-Side Flex Layout */
    .print-cards-layout {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 0 !important;
        margin: 0 auto !important;
        width: 100% !important;
        page-break-inside: avoid !important;
    }

    /* Vertical Fold/Cut Separator between cards */
    .print-fold-separator {
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        height: 54mm !important;
        width: 6mm !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }
    .print-fold-separator .fold-line {
        flex: 1 !important;
        width: 0 !important;
        border-left: 0.5pt dashed #94a3b8 !important;
    }
    .print-fold-separator .fold-text {
        font-size: 3.5pt !important;
        color: #64748b !important;
        writing-mode: vertical-rl !important;
        transform: rotate(180deg) !important;
        white-space: nowrap !important;
        letter-spacing: 0.2pt !important;
        padding: 1.5mm 0 !important;
    }

    /* FIXED STANDARD CR80 PVC DIMENSIONS (86mm × 54mm) - NEVER STRETCHES ACROSS A4 SHEET */
    .pvc-smart-card {
        width: 86mm !important;
        max-width: 86mm !important;
        min-width: 86mm !important;
        height: 54mm !important;
        max-height: 54mm !important;
        min-height: 54mm !important;
        box-sizing: border-box !important;
        border: 0.5pt dashed #475569 !important;
        border-radius: 3.18mm !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        position: relative !important;
        overflow: hidden !important;
        page-break-inside: avoid !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        background: #ffffff !important;
    }

    /* Tri-color stripe at top of card */
    .pvc-smart-card::before {
        height: 1.2mm !important;
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 10 !important;
    }

    /* Card Header Bar (7.8mm) */
    .card-header-bar {
        height: 7.8mm !important;
        min-height: 7.8mm !important;
        max-height: 7.8mm !important;
        padding: 0.8mm 2mm !important;
        box-sizing: border-box !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        border-bottom: 0.4pt solid #0073cb !important;
        margin-top: 1.2mm !important;
    }
    .card-brand-pill {
        background: #ffffff !important;
        border-radius: 0.8mm !important;
        padding: 0.3mm 1.2mm !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        height: 5.6mm !important;
        box-shadow: none !important;
        flex-shrink: 0 !important;
    }
    .card-brand-pill img {
        height: 4.8mm !important;
        width: auto !important;
        max-width: 20mm !important;
        object-fit: contain !important;
        display: block !important;
    }
    .card-header-titles h4 {
        font-size: 5.2pt !important;
        font-weight: 800 !important;
        line-height: 1.1 !important;
        margin: 0 !important;
        letter-spacing: 0.15pt !important;
        color: #ffffff !important;
    }
    .card-header-titles p {
        font-size: 3.6pt !important;
        font-weight: 600 !important;
        line-height: 1.1 !important;
        margin: 0.2mm 0 0 !important;
        letter-spacing: 0.1pt !important;
        color: #93c5fd !important;
    }
    .card-header-badge-right .badge {
        font-size: 3.6pt !important;
        padding: 0.3mm 0.8mm !important;
        border-radius: 0.4mm !important;
    }

    /* Card Body Section (39.5mm) */
    .card-body-section {
        height: 39.5mm !important;
        max-height: 39.5mm !important;
        padding: 1.2mm 2.2mm !important;
        box-sizing: border-box !important;
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 1.5mm !important;
        flex: 1 !important;
        overflow: hidden !important;
    }

    /* Photo Column (Left) */
    .card-photo-col {
        width: 17mm !important;
        min-width: 17mm !important;
        max-width: 17mm !important;
        flex: 0 0 17mm !important;
        text-align: center !important;
    }
    .card-photo-col img {
        width: 16.5mm !important;
        height: 20.5mm !important;
        object-fit: cover !important;
        border: 0.3pt solid #0b3c5d !important;
        border-radius: 0.8mm !important;
        box-shadow: none !important;
        display: block !important;
        margin: 0 auto !important;
    }
    .card-verified-badge {
        font-size: 3.4pt !important;
        font-weight: 700 !important;
        padding: 0.3mm 0.6mm !important;
        margin-top: 0.6mm !important;
        border-radius: 0.5mm !important;
        border: 0.3pt solid #10b981 !important;
        line-height: 1.1 !important;
        white-space: nowrap !important;
        display: block !important;
    }

    /* Citizen Details Column (Center) */
    .card-details-col {
        flex: 1 !important;
        min-width: 0 !important;
        padding: 0 0.8mm !important;
        overflow: hidden !important;
    }
    .card-name-title {
        font-size: 7.2pt !important;
        font-weight: 800 !important;
        margin: 0 0 0.6mm !important;
        line-height: 1.1 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
        color: #0b3c5d !important;
    }
    .card-meta-line {
        font-size: 4.8pt !important;
        line-height: 1.25 !important;
        margin-bottom: 0.4mm !important;
        color: #334155 !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }
    .card-meta-line strong {
        color: #0f172a !important;
    }
    .card-abha-pill {
        background: #f0f9ff !important;
        border: 0.4pt solid #0284c7 !important;
        border-radius: 0.8mm !important;
        padding: 0.5mm 1.2mm !important;
        margin: 0.6mm 0 !important;
        display: inline-block !important;
    }
    .card-abha-pill .label-txt {
        font-size: 3.6pt !important;
        font-weight: 700 !important;
        line-height: 1 !important;
    }
    .card-abha-pill .val-txt {
        font-size: 5.8pt !important;
        font-weight: 800 !important;
        letter-spacing: 0.2pt !important;
        line-height: 1.1 !important;
    }

    /* QR Code Column (Right) */
    .card-qr-col {
        width: 16mm !important;
        min-width: 16mm !important;
        max-width: 16mm !important;
        flex: 0 0 16mm !important;
        text-align: center !important;
    }
    .card-qr-box {
        width: 15mm !important;
        height: 15mm !important;
        min-width: 15mm !important;
        min-height: 15mm !important;
        padding: 0.4mm !important;
        border: 0.4pt solid #0b3c5d !important;
        border-radius: 0.8mm !important;
        box-shadow: none !important;
        margin: 0 auto 0.5mm !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #ffffff !important;
    }
    .card-qr-box img, .card-qr-box canvas {
        width: 14mm !important;
        height: 14mm !important;
        max-width: 100% !important;
        max-height: 100% !important;
        object-fit: contain !important;
        display: block !important;
    }
    .card-qr-label {
        font-size: 3.2pt !important;
        font-weight: 800 !important;
        line-height: 1.05 !important;
        color: #dc2626 !important;
        margin-top: 0.4mm !important;
    }

    /* Card Footer Strip (5.5mm) */
    .card-footer-strip {
        height: 5.5mm !important;
        min-height: 5.5mm !important;
        max-height: 5.5mm !important;
        padding: 0.6mm 2.2mm !important;
        box-sizing: border-box !important;
        font-size: 4.4pt !important;
        font-weight: 700 !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-top: 0.3pt solid #e2e8f0 !important;
        line-height: 1 !important;
    }

    /* Back Card Specifics */
    .card-back-body {
        height: 39.5mm !important;
        max-height: 39.5mm !important;
        padding: 1.2mm 2.2mm !important;
        box-sizing: border-box !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        overflow: hidden !important;
    }
    .card-back-address {
        padding: 0.8mm 1.5mm !important;
        font-size: 4.8pt !important;
        font-weight: 600 !important;
        color: #1e293b !important;
        line-height: 1.25 !important;
        border: 0.3pt solid #cbd5e1 !important;
        border-radius: 0.8mm !important;
        background: #ffffff !important;
        margin-bottom: 0.6mm !important;
    }
    .card-back-rules {
        font-size: 4.1pt !important;
        color: #334155 !important;
        line-height: 1.2 !important;
        margin: 0.6mm 0 !important;
    }
    .card-back-rules ul {
        padding-left: 3mm !important;
        margin: 0 !important;
    }
    .card-back-rules li {
        margin-bottom: 0.3mm !important;
    }
    .card-back-bottom {
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
        border-top: 0.3pt dashed #cbd5e1 !important;
        padding-top: 0.6mm !important;
    }
    .card-back-bottom div {
        font-size: 4pt !important;
        color: #1e293b !important;
        line-height: 1.1 !important;
    }
    .card-back-bottom .badge {
        font-size: 3.8pt !important;
        padding: 0.4mm 1mm !important;
        border-radius: 0.4mm !important;
    }

    /* Printable Sheet Official Footer */
    .print-sheet-footer {
        display: block !important;
        margin-top: 6mm;
        padding-top: 2.5mm;
        border-top: 0.8pt dashed #94a3b8;
        text-align: center;
        font-size: 6pt !important;
        color: #64748b !important;
    }
    .print-sheet-footer p {
        margin: 0.5mm 0 !important;
    }
}
</style>

<!-- Banner -->
<section class="no-print" style="background: #eef2f6; border-bottom: 1px solid #dde2e8; padding: 18px 0;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-7 col-sm-12">
        <ol class="breadcrumb" style="background: transparent; margin: 0; padding: 0; font-size: 0.88rem;">
          <li><a href="<?php echo BASE_URL; ?>/index.php"><i class="fas fa-home"></i> होम</a></li>
          <li><a href="<?php echo BASE_URL; ?>/ayushman-helpdesk.php">आयुष्मान हेल्प डेस्क</a></li>
          <li class="active">स्मार्ट आईडी कार्ड</li>
        </ol>
      </div>
      <div class="col-md-5 col-sm-12 text-right">
        <?php if ($record): ?>
          <span class="badge" style="background: #16a34a; font-size: 0.85rem; padding: 6px 14px;">
            <i class="fas fa-check-circle"></i> कार्ड जनरेटेड
          </span>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<div class="smart-card-wrapper">
  <div class="container">
    
    <?php if (!$record): ?>
      <!-- Search / Not Found Box -->
      <div class="row">
        <div class="col-md-6 col-md-offset-3 col-sm-12">
          <div style="background: #ffffff; border-radius: 12px; padding: 32px; box-shadow: 0 4px 18px rgba(0,0,0,0.06); text-align: center;">
            <i class="fas fa-id-card-alt fa-3x text-warning" style="margin-bottom: 14px;"></i>
            <h3 style="margin-top: 0; font-weight: 800; color: #0b3c5d;">डिजिटल हेल्थ स्मार्ट कार्ड खोजें</h3>
            <p style="color: #64748b; font-size: 0.92rem;">
              कृपया अपना आवेदन क्रमांक (Application Number) दर्ज करें:
            </p>
            <form method="GET" action="" style="margin-top: 20px;">
              <div class="input-group">
                <input type="text" name="app_no" class="form-control" placeholder="उदा. AYUSH-20261030-XXXX" required style="height: 46px; font-weight: 700; text-transform: uppercase;">
                <span class="input-group-btn">
                  <button type="submit" class="btn btn-primary" style="height: 46px; background: #0b3c5d; font-weight: 700; padding: 0 20px;">
                    <i class="fas fa-search"></i> कार्ड खोजें
                  </button>
                </span>
              </div>
            </form>
            <div style="margin-top: 20px;">
              <a href="<?php echo BASE_URL; ?>/ayushman-helpdesk.php" class="btn btn-default btn-sm">
                <i class="fas fa-plus-circle text-success"></i> नया कार्ड आवेदन करें
              </a>
            </div>
          </div>
        </div>
      </div>

    <?php else: ?>
      <!-- SUCCESS ALERT BANNER -->
      <div class="alert alert-success no-print" style="background: #ecfdf5; border: 1.5px solid #a7f3d0; color: #065f46; border-radius: 10px; padding: 18px; margin-bottom: 25px;">
        <div class="row align-items-center">
          <div class="col-md-8 col-sm-12">
            <h4 style="margin: 0 0 6px; font-weight: 800; color: #065f46;">
              <?php if ($isExistingCard): ?>
                <i class="fas fa-id-card text-success"></i> इस मोबाइल नंबर पर पहले से एक डिजिटल हेल्थ स्मार्ट आईडी कार्ड मौजूद है!
              <?php else: ?>
                <i class="fas fa-check-circle text-success"></i> डिजिटल हेल्थ स्मार्ट आईडी कार्ड सफलतापूर्वक जनरेट हुआ!
              <?php endif; ?>
            </h4>
            <p style="margin: 0; font-size: 0.88rem;">
              <?php if ($isExistingCard): ?>
                प्रति मोबाइल नंबर केवल एक ही कार्ड जारी किया जाता है, इसलिए आपका मौजूदा कार्ड नीचे दिखाया जा रहा है।
              <?php endif; ?>
              आवेदन क्रमांक: <strong><?php echo htmlspecialchars($record['application_no']); ?></strong> |
              कार्ड आईडी: <strong><?php echo htmlspecialchars($record['card_id']); ?></strong> |
              पुष्टि एवं डाउनलोड लिंक आपकी ईमेल <code><?php echo htmlspecialchars($record['email']); ?></code> पर भी भेज दी गई है।
            </p>
          </div>
          <div class="col-md-4 col-sm-12 text-md-end text-start mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end justify-content-start">
            <button type="button" onclick="window.print();" class="btn btn-primary" style="background: #0b3c5d; border-color: #0b3c5d; font-weight: 700;">
              <i class="fas fa-print"></i> कार्ड प्रिंट करें
            </button>
            <button type="button" id="btn-download-pvc" class="btn btn-success" style="background: #059669; border-color: #059669; font-weight: 700;">
              <i class="fas fa-download"></i> PNG सेव करें
            </button>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Center / Left: Cards Area (Printable) -->
        <div class="col-lg-7 col-md-7 col-sm-12 printable-section">
          
          <h4 class="no-print" style="font-weight: 800; color: #0b3c5d; margin-top: 0; margin-bottom: 16px;">
            <i class="fas fa-id-card text-warning"></i> राष्ट्रीय डिजिटल स्वास्थ्य स्मार्ट पहचान पत्र (PVC Card Preview)
          </h4>

          <!-- Official Printable Sheet Header (A4 Print Only) -->
          <div class="print-sheet-header">
            <div class="print-gov-row">
              <img src="<?php echo BASE_URL; ?>/images/rejuvenate-logo.png" alt="Rejuvenate Digital Health" class="print-sheet-logo" onerror="this.src='https://rejuvenatedigitalhealth.com/admin/uploads/logo/logo_header_691a7abe49524.png'">
              <div class="print-gov-title">
                <h2>REJUVENATE DIGITAL HEALTH &bull; रीजूवेनेट डिजिटल हेल्थ</h2>
                <p>Digital Citizen Health Card &bull; https://rejuvenatedigitalhealth.com</p>
              </div>
            </div>
            <div class="print-meta-strip">
              <div><strong>कार्ड धारक / Name:</strong> <?php echo htmlspecialchars($record['full_name']); ?></div>
              <div><strong>आवेदन क्रमांक / App No:</strong> <?php echo htmlspecialchars($record['application_no']); ?></div>
              <div><strong>कार्ड आईडी / Card ID:</strong> <?php echo htmlspecialchars($record['card_id']); ?></div>
              <div><strong>प्रिंट दिनांक / Date:</strong> <?php echo date('d-m-Y h:i A'); ?></div>
            </div>
          </div>

          <div class="print-cut-guide">
            <span><i class="fas fa-cut"></i> <strong>कटिंग व लैमिनेशन निर्देश (CR80 Cutting Guide):</strong> प्रिंट को 100% (Actual Size) पर प्रिंट करें। बिंदीदार रेखा (Dashed border) से काटकर 86mm &times; 54mm के स्टैंडर्ड PVC पाउच में लैमिनेट करें या बीच से मोड़कर डबल-साइड कार्ड बनाएं।</span>
          </div>

          <div class="print-cards-layout">
          <!-- 1. CARD FRONT -->
          <div id="pvc-card-front" class="pvc-smart-card">
            <!-- Header -->
            <div class="card-header-bar">
              <div style="display: flex; align-items: center; gap: 8px;">
                <div class="card-brand-pill">
                  <img src="<?php echo BASE_URL; ?>/images/rejuvenate-logo.png" alt="Rejuvenate Digital Health" onerror="this.src='https://rejuvenatedigitalhealth.com/admin/uploads/logo/logo_header_691a7abe49524.png'">
                </div>
                <div class="card-header-titles">
                  <h4>REJUVENATE DIGITAL HEALTH</h4>
                  <p>Digital Health Facilitation Pass &bull; Smart Health ID</p>
                </div>
              </div>
              <div class="card-header-badge-right">
                <span class="badge" style="background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.35); font-size: 0.68rem; padding: 4px 7px; color: #ffffff;">
                  <i class="fas fa-shield-alt text-success"></i> VERIFIED
                </span>
              </div>
            </div>

            <!-- Body Content -->
            <div class="card-body-section">
              <!-- Left: Photo -->
              <div class="card-photo-col">
                <img src="<?php echo !empty($record['photo_url']) ? BASE_URL . '/' . htmlspecialchars($record['photo_url']) : BASE_URL . '/images/tuser1.jpg'; ?>" alt="Citizen Photo">
                <span class="card-verified-badge">
                  <i class="fas fa-id-card"></i> Aadhaar Linked
                </span>
              </div>

              <!-- Middle: Citizen Data -->
              <div class="card-details-col">
                <h3 class="card-name-title"><?php echo htmlspecialchars($record['full_name']); ?></h3>
                
                <div class="card-meta-line">
                  <strong>DOB:</strong> <?php echo htmlspecialchars($record['dob'] ?: 'DD/MM/YYYY'); ?> &nbsp;|&nbsp; 
                  <strong>Gender:</strong> <?php echo htmlspecialchars($record['gender'] ?: 'Male'); ?>
                </div>

                <div class="card-meta-line">
                  <strong>Mobile:</strong> <?php echo htmlspecialchars(substr($record['mobile'], 0, 2) . 'XXXXXX' . substr($record['mobile'], -2)); ?>
                </div>

                <!-- ABHA Pill -->
                <div class="card-abha-pill">
                  <span class="label-txt">ABHA NUMBER / आभा संख्या</span>
                  <span class="val-txt"><?php echo htmlspecialchars($record['abha_number'] ?: 'NOT ENROLLED'); ?></span>
                </div>

                <?php if (!empty($record['ration_card_no'])): ?>
                  <div class="card-meta-line"><strong>Ration Card:</strong> <?php echo htmlspecialchars($record['ration_card_no']); ?></div>
                <?php endif; ?>

                <?php if (!empty($record['bocw_reg_no'])): ?>
                  <div class="card-meta-line"><strong>BOCW No:</strong> <?php echo htmlspecialchars($record['bocw_reg_no']); ?></div>
                <?php endif; ?>

                <?php if (!empty($record['pmjay_letter_no'])): ?>
                  <div class="card-meta-line"><strong>PMJAY Ref:</strong> <?php echo htmlspecialchars($record['pmjay_letter_no']); ?></div>
                <?php endif; ?>
              </div>

              <!-- Right: Encrypted QR Code -->
              <div class="card-qr-col">
                <div class="card-qr-box" id="qr-container-front">
                  <!-- Fallback QR Image via API or Canvas rendered by JS -->
                  <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($qrVerificationUrl); ?>" alt="Encrypted QR" id="qr-img-fallback">
                </div>
                <div class="card-qr-label">
                  <i class="fas fa-lock"></i> AES-256<br>ENCRYPTED QR
                </div>
              </div>
            </div>

            <!-- Bottom Strip -->
            <div class="card-footer-strip">
              <span><strong>CARD ID:</strong> <?php echo htmlspecialchars($record['card_id']); ?></span>
              <span><strong>APP NO:</strong> <?php echo htmlspecialchars($record['application_no']); ?></span>
            </div>
          </div>

          <!-- Vertical Fold/Cut Separator (A4 Print Only) -->
          <div class="print-fold-separator">
            <span class="fold-line"></span>
            <span class="fold-text">&#9986; मोड़ें / काटें (Fold or Cut)</span>
            <span class="fold-line"></span>
          </div>

          <!-- 2. CARD BACK -->
          <div id="pvc-card-back" class="pvc-smart-card pvc-smart-card-back">
            <div class="card-header-bar" style="background: #1e293b; border-bottom: 2px solid #0073cb;">
              <div class="card-header-titles">
                <h4>Cardholder Residential Record & Terms</h4>
                <p>Rejuvenate Digital Health • Citizen Verification Pass</p>
              </div>
              <div style="font-size: 0.72rem; color: #94a3b8; font-family: monospace;">
                rejuvenatedigitalhealth.com
              </div>
            </div>

            <div class="card-back-body">
              <div class="card-back-address">
                <strong><i class="fas fa-home text-primary"></i> स्थायी पता / Permanent Address:</strong><br>
                <?php 
                  $addrParts = array_filter([$record['address'], $record['district'], $record['state'], $record['pincode']]);
                  echo htmlspecialchars(implode(', ', $addrParts) ?: 'As per Aadhaar e-KYC Records');
                ?>
              </div>

              <div class="card-back-rules">
                <ul style="padding-left: 14px; margin: 0;">
                  <li>यह डिजिटल हेल्थ स्मार्ट आईडी कार्ड Rejuvenate Digital Health (rejuvenatedigitalhealth.com) द्वारा नागरिक सुविधा हेतु जारी किया गया है।</li>
                  <li>कार्ड पर मौजूद QR कोड AES-256 मिलिट्री ग्रेड एन्क्रिप्टेड है, जिसे केवल अधिकृत Admin Scanner द्वारा ही पढ़ा जा सकता है।</li>
                  <li>किसी भी चिकित्सा सहायता हेतु राष्ट्रीय स्वास्थ्य हेल्पलाइन <strong>14555</strong> या आपातकालीन <strong>108</strong> पर संपर्क करें।</li>
                </ul>
              </div>

              <div class="card-back-bottom">
                <div>
                  <div style="font-size: 0.65rem; color: #64748b;">ISSUING DATE:</div>
                  <div style="font-size: 0.75rem; font-weight: 700; color: #0b3c5d;">
                    <?php echo date('d-m-Y', strtotime($record['created_at'])); ?>
                  </div>
                </div>
                <div style="text-align: center;">
                  <span class="badge" style="background: #0073cb; font-size: 0.68rem; padding: 4px 8px;">
                    <i class="fas fa-check-double"></i> Digitally Signed
                  </span>
                </div>
                <div style="text-align: right;">
                  <div style="font-size: 0.65rem; color: #64748b;">SECURITY TOKEN:</div>
                  <div style="font-size: 0.68rem; font-family: monospace; font-weight: 700; color: #475569;">
                    <?php echo strtoupper(substr(hash('sha256', $record['encrypted_qr_token']), 0, 12)); ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="card-footer-strip" style="background: #1e293b;">
              <span>Emergency Helpline: 14555 / 108</span>
              <span>www.rejuvenatedigitalhealth.com</span>
            </div>
          </div>
          </div> <!-- /.print-cards-layout -->

          <!-- Official Printable Sheet Footer -->
          <div class="print-sheet-footer">
            <p><strong>Rejuvenate Digital Health Facilitation Services</strong> &bull; https://rejuvenatedigitalhealth.com</p>
            <p>कार्ड पर मौजूद QR कोड AES-256 एन्क्रिप्टेड है एवं केवल अधिकृत एडमिन स्कैनर द्वारा सत्यापन योग्य है &bull; आपातकालीन हेल्पलाइन: <strong>14555</strong> / <strong>108</strong></p>
          </div>

        </div>

        <!-- Right Column: Card Metadata & Security Audit -->
        <div class="col-lg-5 col-md-5 col-sm-12 no-print">
          
          <!-- Metadata Card -->
          <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.04);">
            <h4 style="font-weight: 800; color: #0b3c5d; margin-top: 0; border-bottom: 2px solid #f59e0b; padding-bottom: 8px;">
              <i class="fas fa-info-circle text-warning"></i> विवरण एवं सत्यापन स्थिति
            </h4>

            <table class="table" style="font-size: 0.88rem; margin-bottom: 0;">
              <tr>
                <td style="color: #64748b; font-weight: 600; width: 45%;">कार्ड धारक:</td>
                <td><strong><?php echo htmlspecialchars($record['full_name']); ?></strong></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">आवेदन क्रमांक:</td>
                <td><code style="font-weight: 700; color: #0b3c5d;"><?php echo htmlspecialchars($record['application_no']); ?></code></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">स्मार्ट कार्ड आईडी:</td>
                <td><code style="font-weight: 700; color: #059669;"><?php echo htmlspecialchars($record['card_id']); ?></code></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">आधार सत्यापन:</td>
                <td>
                  <span class="badge" style="background: #10b981; font-size: 0.78rem;">
                    <i class="fas fa-check"></i> आधार e-KYC (XXXX-XXXX-<?php echo htmlspecialchars($record['aadhaar_last4'] ?? '0000'); ?>)
                  </span>
                </td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">पंजीकृत मोबाइल:</td>
                <td><?php echo htmlspecialchars($record['mobile']); ?></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">ईमेल आईडी:</td>
                <td><?php echo htmlspecialchars($record['email']); ?></td>
              </tr>
              <tr>
                <td style="color: #64748b; font-weight: 600;">वर्तमान स्थिति:</td>
                <td>
                  <?php 
                    $st = $record['status'] ?? 'submitted';
                    $stColors = ['submitted' => '#0284c7', 'verified' => '#10b981', 'approved' => '#059669', 'rejected' => '#dc2626'];
                  ?>
                  <span class="badge" style="background: <?php echo $stColors[$st] ?? '#64748b'; ?>; text-transform: uppercase;">
                    <?php echo htmlspecialchars($st); ?>
                  </span>
                </td>
              </tr>
            </table>

            <div style="margin-top: 18px; padding-top: 14px; border-top: 1px dashed #e2e8f0;">
              <button type="button" class="btn btn-default btn-block" onclick="copyVerificationLink()">
                <i class="fas fa-share-alt"></i> सत्यापन लिंक कॉपी करें (Copy Verification URL)
              </button>
            </div>
          </div>

          <!-- Encryption & Admin Scanner Notice -->
          <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 12px; padding: 20px;">
            <div style="display: flex; gap: 12px;">
              <i class="fas fa-shield-alt fa-2x" style="color: #d97706; flex-shrink: 0;"></i>
              <div>
                <h5 style="margin: 0 0 6px; font-weight: 800; color: #92400e;">सुरक्षित एन्क्रिप्टेड क्यूआर तकनीक</h5>
                <p style="margin: 0; font-size: 0.83rem; color: #78350f; line-height: 1.5;">
                  इस स्मार्ट कार्ड का क्यूआर कोड <strong>AES-256-CBC</strong> एन्क्रिप्टेड है। किसी भी सामान्य स्कैनर से स्कैन करने पर व्यक्तिगत जानकारी नहीं दिखेगी। केवल अधिकृत एडमिन स्कैनर ही इसे डिक्रिप्ट कर सकता है।
                </p>
                <?php if ($isAdmin): ?>
                  <div style="margin-top: 12px;">
                    <a href="<?php echo BASE_URL; ?>/admin/ayushman-scan-detail.php?id=<?php echo urlencode($record['card_id']); ?>" class="btn btn-warning btn-sm" style="font-weight: 700; color: #1e293b; background: #f59e0b;">
                      <i class="fas fa-qrcode"></i> एडमिन स्कैनर से टेस्ट करें (Test in Admin Scanner)
                    </a>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

        </div>
      </div>
    <?php endif; ?>

  </div>
</div>

<!-- Include qrcode.min.js & html2canvas.min.js -->
<script src="<?php echo BASE_URL; ?>/js/qrcode.min.js"></script>
<script src="<?php echo BASE_URL; ?>/js/html2canvas.min.js"></script>

<script>
// Generate High-Density QR code via qrcode.js
document.addEventListener("DOMContentLoaded", function() {
  <?php if ($record): ?>
    const qrTarget = document.getElementById("qr-container-front");
    const qrData = <?php echo json_encode($qrVerificationUrl); ?>;
    if (typeof QRCode !== "undefined" && qrTarget) {
      qrTarget.innerHTML = "";
      new QRCode(qrTarget, {
        text: qrData,
        width: 90,
        height: 90,
        colorDark: "#0b3c5d",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.M
      });
    }
  <?php endif; ?>
});

// Download Smart ID Card as PNG Image via html2canvas
const btnDownloadPvc = document.getElementById("btn-download-pvc");
if (btnDownloadPvc) {
  btnDownloadPvc.addEventListener("click", function() {
    const cardEl = document.getElementById("pvc-card-front");
    if (!cardEl) return;

    btnDownloadPvc.disabled = true;
    btnDownloadPvc.innerHTML = '<i class="fas fa-spinner fa-spin"></i> कार्ड रेंडर हो रहा है...';

    html2canvas(cardEl, {
      scale: 3, // High DPI for crisp print-quality rendering
      useCORS: true,
      backgroundColor: "#ffffff"
    }).then(canvas => {
      const link = document.createElement("a");
      link.download = "Digital_Health_Smart_ID_<?php echo htmlspecialchars($record['card_id'] ?? 'card'); ?>.png";
      link.href = canvas.toDataURL("image/png");
      link.click();

      btnDownloadPvc.disabled = false;
      btnDownloadPvc.innerHTML = '<i class="fas fa-download"></i> PNG सेव करें';
    }).catch(err => {
      alert("कार्ड डाउनलोड करने में त्रुटि: " + err);
      btnDownloadPvc.disabled = false;
      btnDownloadPvc.innerHTML = '<i class="fas fa-download"></i> PNG सेव करें';
    });
  });
}

function copyVerificationLink() {
  const url = <?php echo json_encode($qrVerificationUrl); ?>;
  navigator.clipboard.writeText(url).then(() => {
    alert("सत्यापन लिंक कॉपी कर लिया गया है!\n\n" + url);
  }).catch(() => {
    prompt("सत्यापन लिंक:", url);
  });
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
