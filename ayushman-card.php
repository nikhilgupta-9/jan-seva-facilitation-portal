<?php
// ayushman-card.php - Unified Digital Health ID (यूनिफ़ाइड डिजिटल हेल्थ आईडी) Viewer & Smart PVC Download Center
require_once __DIR__ . '/config/config.php';

$appNo = trim($_GET['app_no'] ?? '');
$record = null;
$isExistingCard = (($_GET['status'] ?? '') === 'existing');

if (!empty($appNo) && isset($pdo)) {
    $stmt = $pdo->prepare("SELECT * FROM ayushman_applications WHERE application_no = ? LIMIT 1");
    $stmt->execute([$appNo]);
    $record = $stmt->fetch();
}

$page_title = $record ? 'Unified Digital Health ID - ' . $record['full_name'] : 'Digital Health Smart ID Search';

// Build the public verification URL to embed in the QR code.
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$base = defined('BASE_URL') ? BASE_URL : '/id-card';
$qrVerificationUrl = $protocol . $host . rtrim($base, '/') . '/verify-id.php?id=' . urlencode($record['card_id'] ?? '');

$isAdmin = !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['admin_user']) || !empty($_SESSION['admin_id']);

// Format data fields for display
if ($record) {
    $fullName = trim($record['full_name'] ?? 'Citizen Applicant');
    $dobFormatted = !empty($record['dob']) ? date('d/m/Y', strtotime($record['dob'])) : '12/05/1991';
    
    // Gender display (Hindi + English)
    $gRaw = strtolower(trim($record['gender'] ?? ''));
    if ($gRaw === 'female' || $gRaw === 'महिला') {
        $genderDisplay = 'महिला / Female';
    } elseif ($gRaw === 'other' || $gRaw === 'अन्य') {
        $genderDisplay = 'अन्य / Other';
    } else {
        $genderDisplay = 'पुरुष / Male';
    }

    // UIDID formatted as standard 12-digit format
    $last4 = !empty($record['aadhaar_last4']) ? $record['aadhaar_last4'] : substr($record['mobile'] ?? '0987', -4);
    $maskedUidid = '8765 4321 ' . $last4;

    // ABHA number formatted with spaces (14-digit)
    if (!empty($record['abha_number'])) {
        $cleanAbha = preg_replace('/[^0-9]/', '', $record['abha_number']);
        if (strlen($cleanAbha) === 14) {
            $abhaDisplay = substr($cleanAbha, 0, 4) . ' ' . substr($cleanAbha, 4, 4) . ' ' . substr($cleanAbha, 8, 4) . ' ' . substr($cleanAbha, 12, 2);
        } else {
            $abhaDisplay = $record['abha_number'];
        }
    } else {
        $abhaDisplay = '1022 3456 ' . substr($record['mobile'] ?? '7890', -4);
    }

    // 4 Schemes Data
    $pmjayId = !empty($record['pmjay_letter_no']) ? $record['pmjay_letter_no'] : ('AB' . substr($record['mobile'] ?? '12345678', -8));
    $eshramUan = !empty($record['bocw_reg_no']) ? $record['bocw_reg_no'] : ('1234 5678 ' . substr($record['mobile'] ?? '9012', -4));
    $rationId = !empty($record['ration_card_no']) ? $record['ration_card_no'] : ('9876 5432 ' . substr($record['mobile'] ?? '1010', -4));

    // Photo URL
    $photoUrl = !empty($record['photo_url']) ? BASE_URL . '/' . htmlspecialchars($record['photo_url']) : BASE_URL . '/images/tuser1.jpg';

    // Full Address
    $addrParts = array_filter([$record['address'], $record['district'], $record['state'], $record['pincode']]);
    $fullAddress = implode(', ', $addrParts) ?: 'As per Aadhaar e-KYC Records';
}

include __DIR__ . '/includes/header.php';
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&family=Noto+Sans+Devanagari:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<style>
/* =========================================================
   Unified Digital Health ID - Screen & Cyber Showcase Styles
   ========================================================= */
.smart-card-wrapper {
    background: #06152d;
    padding: 30px 15px 50px;
    font-family: 'Plus Jakarta Sans', 'Noto Sans Devanagari', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    min-height: calc(100vh - 200px);
}

.cards-container-row {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 26px;
    width: 100%;
}

/* Outer Cyber Glow Showcase Frame (Front & Back) */
.cyber-card-outer {
    position: relative;
    padding: 8px;
    border-radius: 24px;
    background: rgba(3, 169, 244, 0.08);
    box-shadow: 0 0 35px rgba(0, 229, 255, 0.45), 0 0 70px rgba(0, 168, 255, 0.22);
    border: 2px solid #00f0ff;
    width: 100%;
    max-width: 550px;
    margin: 0 auto;
    box-sizing: border-box;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.cyber-card-outer:hover {
    box-shadow: 0 0 45px rgba(0, 229, 255, 0.6), 0 0 90px rgba(0, 168, 255, 0.35);
}
.cyber-card-outer::before {
    content: '';
    position: absolute;
    inset: 4px;
    border-radius: 19px;
    border: 1px solid rgba(0, 240, 255, 0.5);
    pointer-events: none;
}

/* The PVC Smart Card (Standard CR80 aspect ratio ~ 1.586) */
.unified-card {
    width: 100%;
    max-width: 100%;
    min-height: 336px;
    background: linear-gradient(180deg, #ffffff 0%, #f0f9ff 65%, #e0f2fe 100%);
    border-radius: 15px;
    overflow: hidden;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    box-shadow: 0 14px 34px rgba(0,0,0,0.32);
    border: 1px solid #7dd3fc;
    box-sizing: border-box;
}

/* Subtle circuit grid watermark */
.unified-card::after {
    content: '';
    position: absolute;
    inset: 0;
    background-image: 
        radial-gradient(circle at 80% 25%, rgba(14, 165, 233, 0.08) 0%, transparent 50%),
        linear-gradient(rgba(14, 165, 233, 0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(14, 165, 233, 0.04) 1px, transparent 1px);
    background-size: 100% 100%, 20px 20px, 20px 20px;
    pointer-events: none;
    z-index: 1;
}

/* Top Cyber Bar */
.unified-card-header {
    background: linear-gradient(135deg, #051937 0%, #002952 50%, #004d7a 100%);
    padding: 7px 12px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 2px solid #00e5ff;
    position: relative;
    z-index: 2;
    gap: 8px;
}
.header-brand-box {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    flex: 1;
}
.rejuvenate-logo-badge {
    background: #ffffff;
    border-radius: 6px;
    padding: 2px 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 36px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.25);
    border: 1px solid #38bdf8;
    flex-shrink: 0;
}
.rejuvenate-logo-badge img {
    height: 26px;
    width: auto;
    max-width: 110px;
    object-fit: contain;
    display: block;
}
.header-titles {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
    overflow: hidden;
}
.header-titles .title-hi {
    color: #ffffff;
    font-size: 1.05rem;
    font-weight: 800;
    letter-spacing: 0.3px;
    line-height: 1.15;
    text-shadow: 0 2px 4px rgba(0,0,0,0.4);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.header-titles .title-en {
    color: #00e5ff;
    font-size: 0.8rem;
    font-weight: 800;
    letter-spacing: 0.5px;
    line-height: 1.1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Hologram Lock Badge */
.hologram-lock-badge {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #00e5ff 0%, #a855f7 50%, #ec4899 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 12px rgba(0, 229, 255, 0.7), inset 0 0 6px rgba(255,255,255,0.6);
    border: 1.5px solid #ffffff;
    position: relative;
    flex-shrink: 0;
}
.hologram-lock-badge i {
    color: #ffffff;
    font-size: 0.92rem;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.5));
}

/* Middle Profile & Biometrics Section */
.unified-profile-section {
    padding: 6px 12px 4px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    position: relative;
    z-index: 2;
    min-width: 0;
}

.profile-photo-box {
    width: 80px;
    height: 96px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #0284c7;
    box-shadow: 0 3px 10px rgba(2, 132, 199, 0.22);
    background: #ffffff;
    flex-shrink: 0;
}
.profile-photo-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.profile-details-box {
    flex: 1;
    min-width: 0;
    line-height: 1.3;
    overflow: hidden;
}
.profile-name {
    font-size: 0.96rem;
    font-weight: 900;
    color: #051937;
    margin-bottom: 2px;
    letter-spacing: 0.2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.profile-name strong {
    color: #0f172a;
}
.profile-field {
    font-size: 0.74rem;
    color: #1e293b;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.profile-field strong {
    color: #0f172a;
    font-weight: 800;
}
.profile-field.field-uidid {
    font-weight: 800;
    color: #0f172a;
    letter-spacing: 0.3px;
}
.profile-field.field-abha {
    font-weight: 800;
    color: #0369a1;
    letter-spacing: 0.3px;
    margin-top: 1px;
}

.cyber-qr-box {
    width: 90px;
    height: 90px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.cyber-iris-bg {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}
.cyber-iris-svg {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 0 6px rgba(0, 229, 255, 0.6));
}
.qr-code-white-card {
    position: relative;
    z-index: 3;
    width: 66px;
    height: 66px;
    background: #ffffff;
    border-radius: 8px;
    padding: 3px;
    border: 1.5px solid #00e5ff;
    box-shadow: 0 0 10px rgba(0, 229, 255, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
}
.qr-code-white-card img, .qr-code-white-card canvas {
    width: 100% !important;
    height: 100% !important;
    display: block;
}

/* 4 Scheme Integration Tiles (2x2 Grid) */
.unified-schemes-grid {
    padding: 0 12px 8px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 6px;
    position: relative;
    z-index: 2;
    min-width: 0;
}
.scheme-card-tile {
    background: #ffffff;
    border: 1px solid #bae6fd;
    border-radius: 7px;
    padding: 4px 6px;
    display: flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 5px rgba(14, 165, 233, 0.08);
    min-height: 46px;
    min-width: 0;
    overflow: hidden;
}
.scheme-tile-icon {
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.scheme-logo-svg {
    width: 100%;
    height: 100%;
    display: block;
}
.scheme-tile-info {
    flex: 1;
    min-width: 0;
    line-height: 1.14;
    overflow: hidden;
}
.scheme-tile-title {
    font-size: 0.6rem;
    font-weight: 800;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.scheme-tile-meta {
    font-size: 0.56rem;
    font-weight: 700;
    color: #334155;
    margin: 1px 0;
    display: flex;
    align-items: center;
    gap: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.badge-active {
    background: #22c55e;
    color: #ffffff;
    font-size: 0.48rem;
    font-weight: 800;
    padding: 1px 4px;
    border-radius: 3px;
}
.badge-phh {
    background: #0284c7;
    color: #ffffff;
    font-size: 0.48rem;
    font-weight: 800;
    padding: 1px 4px;
    border-radius: 3px;
}
.scheme-tile-hi {
    font-size: 0.6rem;
    font-weight: 800;
    color: #0369a1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Back Card Styles */
.unified-card-back {
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
}
.card-back-body {
    padding: 8px 12px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    z-index: 2;
    min-width: 0;
}
.card-back-address {
    background: #ffffff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    padding: 5px 8px;
    font-size: 0.68rem;
    color: #334155;
    line-height: 1.32;
    box-shadow: 0 1px 4px rgba(14, 165, 233, 0.05);
    overflow: hidden;
}
.card-back-address strong {
    color: #0369a1;
    font-weight: 800;
}
.card-back-rules {
    font-size: 0.62rem;
    color: #475569;
    line-height: 1.34;
    margin: 4px 0;
    overflow: hidden;
}
.card-back-rules ul {
    padding-left: 14px;
    margin: 0;
}
.card-back-rules li {
    margin-bottom: 2px;
}
.card-back-rules strong {
    color: #0f172a;
}
.card-back-bottom {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    border-top: 1px dashed #cbd5e1;
    padding-top: 4px;
}
.card-back-bottom .label-sm {
    font-size: 0.58rem;
    color: #64748b;
    font-weight: 600;
}
.card-back-bottom .val-sm {
    font-size: 0.68rem;
    font-weight: 800;
    color: #0b3c5d;
}
.card-back-footer {
    background: linear-gradient(135deg, #051937 0%, #002952 100%);
    color: #ffffff;
    padding: 6px 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    border-top: 1.5px solid #00e5ff;
}

/* =========================================================
   Mobile Screen Responsive Tuning (< 580px)
   ========================================================= */
@media (max-width: 580px) {
    .smart-card-wrapper {
        padding: 15px 8px 30px;
    }
    .cyber-card-outer {
        padding: 4px;
        border-radius: 14px;
        box-shadow: 0 0 15px rgba(0, 229, 255, 0.35);
        max-width: 100%;
    }
    .cyber-card-outer::before {
        inset: 2px;
        border-radius: 11px;
    }
    .unified-card {
        height: auto;
        min-height: 310px;
        border-radius: 10px;
    }
    .unified-card-header {
        padding: 5px 8px;
    }
    .rejuvenate-logo-badge {
        height: 26px;
        padding: 1px 4px;
    }
    .rejuvenate-logo-badge img {
        height: 16px;
        max-width: 65px;
    }
    .header-titles .title-hi {
        font-size: 0.74rem;
    }
    .header-titles .title-en {
        font-size: 0.56rem;
    }
    .hologram-lock-badge {
        width: 22px;
        height: 22px;
    }
    .hologram-lock-badge i {
        font-size: 0.62rem;
    }
    .unified-profile-section {
        padding: 4px 6px 2px;
        gap: 5px;
    }
    .profile-photo-box {
        width: 48px;
        height: 60px;
        border-radius: 5px;
    }
    .profile-name {
        font-size: 0.72rem;
    }
    .profile-field {
        font-size: 0.56rem;
        line-height: 1.18;
    }
    .cyber-qr-box {
        width: 54px;
        height: 54px;
    }
    .qr-code-white-card {
        width: 46px;
        height: 46px;
        border-radius: 4px;
        padding: 2px;
    }
    .unified-schemes-grid {
        padding: 0 6px 6px;
        gap: 4px;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    }
    .scheme-card-tile {
        padding: 2px 4px;
        gap: 3px;
        min-height: 38px;
    }
    .scheme-tile-icon {
        width: 20px;
        height: 20px;
    }
    .scheme-tile-title {
        font-size: 0.44rem;
    }
    .scheme-tile-meta {
        font-size: 0.42rem;
    }
    .badge-active, .badge-phh {
        font-size: 0.38rem;
        padding: 0.5px 2px;
    }
    .scheme-tile-hi {
        font-size: 0.44rem;
    }
    .card-back-body {
        padding: 6px 8px;
    }
    .card-back-address {
        font-size: 0.58rem;
        padding: 4px 5px;
    }
    .card-back-rules {
        font-size: 0.52rem;
        line-height: 1.25;
    }
    .card-back-rules ul {
        padding-left: 10px;
    }
    .card-back-footer {
        padding: 4px 8px;
        font-size: 0.52rem;
    }
}

@media (max-width: 380px) {
    .unified-schemes-grid {
        grid-template-columns: 1fr;
    }
    .scheme-tile-title {
        font-size: 0.58rem;
    }
    .scheme-tile-meta {
        font-size: 0.54rem;
    }
    .scheme-tile-hi {
        font-size: 0.56rem;
    }
    .scheme-tile-icon {
        width: 24px;
        height: 24px;
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
        background: #ffffff !important;
    }

    /* Printable Official Header */
    .print-sheet-header {
        display: block !important;
        text-align: center;
        border-bottom: 1.5pt solid #0b3c5d;
        padding-bottom: 2mm;
        margin-bottom: 3mm;
    }
    .print-gov-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 3mm;
        margin-bottom: 1mm;
    }
    .print-sheet-logo {
        height: 7.5mm !important;
        width: auto !important;
        max-width: 30mm !important;
        object-fit: contain !important;
        display: block !important;
    }
    .print-gov-title h2 {
        font-size: 10.5pt !important;
        font-weight: 800 !important;
        color: #0b3c5d !important;
        margin: 0 !important;
        line-height: 1.15 !important;
        text-transform: uppercase;
        letter-spacing: 0.3pt;
    }
    .print-gov-title p {
        font-size: 7pt !important;
        font-weight: 600 !important;
        color: #475569 !important;
        margin: 0.5mm 0 0 !important;
        line-height: 1.15 !important;
    }
    .print-meta-strip {
        display: flex;
        justify-content: space-between;
        background: #f1f5f9 !important;
        border: 0.4pt solid #cbd5e1 !important;
        border-radius: 1mm;
        padding: 1mm 2.5mm;
        font-size: 6pt !important;
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
        font-size: 6pt !important;
        color: #475569 !important;
        margin: 1.5mm 0 3mm !important;
        padding: 1mm;
        background: #fefce8 !important;
        border: 0.4pt dashed #ca8a04 !important;
        border-radius: 1mm;
    }

    /* Cards Side-by-Side Flex Layout */
    .cards-container-row {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        justify-content: center !important;
        align-items: center !important;
        gap: 0 !important;
        width: 100% !important;
        page-break-inside: avoid !important;
    }
    .cyber-card-outer {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        width: 86mm !important;
        max-width: 86mm !important;
        min-width: 86mm !important;
    }
    .cyber-card-outer::before {
        display: none !important;
    }

    /* STRICT CR80 PVC DIMENSIONS: 86mm × 54mm (NEVER STRETCHES ACROSS A4 SHEET) */
    .unified-card {
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
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
        background: #ffffff !important;
    }

    /* Print Header (8.5mm) */
    .unified-card-header {
        height: 8.5mm !important;
        min-height: 8.5mm !important;
        max-height: 8.5mm !important;
        padding: 0.8mm 1.8mm !important;
        border-bottom: 0.4pt solid #00e5ff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    .rejuvenate-logo-badge {
        height: 6mm !important;
        padding: 0.2mm 1mm !important;
        border-radius: 0.6mm !important;
        box-shadow: none !important;
    }
    .rejuvenate-logo-badge img {
        height: 5.2mm !important;
        max-width: 18mm !important;
    }
    .header-titles .title-hi {
        font-size: 5.4pt !important;
        line-height: 1.1 !important;
    }
    .header-titles .title-en {
        font-size: 4pt !important;
        line-height: 1 !important;
    }
    .hologram-lock-badge {
        width: 5.5mm !important;
        height: 5.5mm !important;
        box-shadow: none !important;
    }
    .hologram-lock-badge i {
        font-size: 3pt !important;
    }

    /* Print Profile Section (19.5mm) */
    .unified-profile-section {
        height: 19.5mm !important;
        min-height: 19.5mm !important;
        max-height: 19.5mm !important;
        padding: 0.6mm 1.8mm !important;
        gap: 1.5mm !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
    }
    .profile-photo-box {
        width: 14mm !important;
        height: 18mm !important;
        min-width: 14mm !important;
        box-shadow: none !important;
        border: 0.3pt solid #0284c7 !important;
        border-radius: 0.6mm !important;
    }
    .profile-details-box {
        line-height: 1.18 !important;
    }
    .profile-name {
        font-size: 5.8pt !important;
        margin-bottom: 0.2mm !important;
    }
    .profile-field {
        font-size: 4.2pt !important;
        line-height: 1.18 !important;
    }
    .profile-field.field-uidid,
    .profile-field.field-abha {
        font-size: 4.4pt !important;
    }
    .cyber-qr-box {
        width: 16.5mm !important;
        height: 18mm !important;
        min-width: 16.5mm !important;
    }
    .cyber-iris-svg {
        display: none !important;
    }
    .qr-code-white-card {
        width: 16mm !important;
        height: 16mm !important;
        padding: 0.3mm !important;
        box-shadow: none !important;
        border: 0.3pt solid #00e5ff !important;
        border-radius: 0.6mm !important;
    }

    /* Print 4 Tiles (23mm) */
    .unified-schemes-grid {
        height: 23mm !important;
        max-height: 23mm !important;
        padding: 0 1.8mm 1mm !important;
        gap: 1mm !important;
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) !important;
    }
    .scheme-card-tile {
        min-height: 10.2mm !important;
        height: 10.2mm !important;
        max-height: 10.2mm !important;
        padding: 0.4mm 1.2mm !important;
        border: 0.3pt solid #bae6fd !important;
        border-radius: 0.6mm !important;
        gap: 1.2mm !important;
        box-shadow: none !important;
    }
    .scheme-tile-icon {
        width: 7mm !important;
        height: 7mm !important;
        min-width: 7mm !important;
    }
    .scheme-tile-title {
        font-size: 3.8pt !important;
        line-height: 1.05 !important;
    }
    .scheme-tile-meta {
        font-size: 3.5pt !important;
        line-height: 1.05 !important;
        margin: 0.1mm 0 !important;
    }
    .badge-active, .badge-phh {
        font-size: 2.8pt !important;
        padding: 0.2mm 0.6mm !important;
    }
    .scheme-tile-hi {
        font-size: 3.6pt !important;
        line-height: 1 !important;
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

    /* Print Back Card */
    .card-back-body {
        height: 38mm !important;
        max-height: 38mm !important;
        padding: 1mm 2mm !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: space-between !important;
    }
    .card-back-address {
        font-size: 4.4pt !important;
        padding: 0.6mm 1.2mm !important;
        line-height: 1.2 !important;
        border: 0.3pt solid #bae6fd !important;
        box-shadow: none !important;
    }
    .card-back-rules {
        font-size: 3.5pt !important;
        line-height: 1.15 !important;
        margin: 0.4mm 0 !important;
    }
    .card-back-rules li {
        margin-bottom: 0.2mm !important;
    }
    .card-back-bottom {
        border-top: 0.3pt dashed #cbd5e1 !important;
        padding-top: 0.4mm !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: flex-end !important;
    }
    .card-back-bottom .label-sm {
        font-size: 3.2pt !important;
    }
    .card-back-bottom .val-sm {
        font-size: 4pt !important;
    }
    .card-back-footer {
        height: 5.5mm !important;
        min-height: 5.5mm !important;
        padding: 0.6mm 2mm !important;
        font-size: 3.8pt !important;
        border-top: 0.3pt solid #00e5ff !important;
        display: flex !important;
        justify-content: space-between !important;
        align-items: center !important;
    }

    .print-sheet-footer {
        display: block !important;
        margin-top: 5mm;
        padding-top: 2mm;
        border-top: 0.8pt dashed #94a3b8;
        text-align: center;
        font-size: 5.5pt !important;
        color: #64748b !important;
    }
    .print-sheet-footer p {
        margin: 0.4mm 0 !important;
    }
}
</style>

<!-- Banner -->
<section class="no-print" style="background: #021226; border-bottom: 1px solid #0f2b48; padding: 18px 0;">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-7 col-sm-12">
        <ol class="breadcrumb" style="background: transparent; margin: 0; padding: 0; font-size: 0.88rem;">
          <li><a href="<?php echo BASE_URL; ?>/index.php" style="color: #93c5fd;"><i class="fas fa-home"></i> होम</a></li>
          <li><a href="<?php echo BASE_URL; ?>/ayushman-helpdesk.php" style="color: #93c5fd;">आयुष्मान हेल्प डेस्क</a></li>
          <li class="active" style="color: #00e5ff; font-weight: 700;">यूनिफ़ाइड डिजिटल हेल्थ आईडी</li>
        </ol>
      </div>
      <div class="col-md-5 col-sm-12 text-md-end text-start mt-2 mt-md-0">
        <?php if ($record): ?>
          <span class="badge" style="background: #059669; font-size: 0.85rem; padding: 6px 14px;">
            <i class="fas fa-check-circle"></i> कार्ड एक्टिवेटिड &bull; 4-योजना लिंक्ड
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
          <div style="background: #ffffff; border-radius: 16px; padding: 36px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); text-align: center; border: 1px solid #38bdf8;">
            <i class="fas fa-id-card-alt fa-3x" style="color: #0284c7; margin-bottom: 14px;"></i>
            <h3 style="margin-top: 0; font-weight: 800; color: #0b3c5d;">यूनिफ़ाइड डिजिटल हेल्थ आईडी खोजें</h3>
            <p style="color: #64748b; font-size: 0.92rem;">
              कृपया अपना आवेदन क्रमांक (Application Number) दर्ज करें:
            </p>
            <form method="GET" action="" style="margin-top: 20px;">
              <div class="input-group">
                <input type="text" name="app_no" class="form-control" placeholder="उदा. AYUSH-20261104-XXXX" required style="height: 46px; font-weight: 700; text-transform: uppercase;">
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
      <div class="alert alert-success no-print" style="background: rgba(16, 185, 129, 0.12); border: 1.5px solid #10b981; color: #ecfdf5; border-radius: 12px; padding: 18px; margin-bottom: 25px;">
        <div class="row align-items-center">
          <div class="col-md-8 col-sm-12">
            <h4 style="margin: 0 0 6px; font-weight: 800; color: #34d399;">
              <?php if ($isExistingCard): ?>
                <i class="fas fa-id-card text-success"></i> इस मोबाइल नंबर पर पहले से यूनिफ़ाइड डिजिटल हेल्थ आईडी कार्ड मौजूद है!
              <?php else: ?>
                <i class="fas fa-check-circle text-success"></i> यूनिफ़ाइड डिजिटल हेल्थ आईडी सफलतापूर्वक जनरेट हुआ!
              <?php endif; ?>
            </h4>
            <p style="margin: 0; font-size: 0.88rem; color: #e2e8f0;">
              <?php if ($isExistingCard): ?>
                प्रति मोबाइल नंबर एक प्राधिकृत कार्ड जारी किया जाता है, इसलिए आपका मौजूदा कार्ड नीचे दिखाया जा रहा है।
              <?php endif; ?>
              आवेदन क्रमांक: <strong style="color: #67e8f9;"><?php echo htmlspecialchars($record['application_no']); ?></strong> |
              स्मार्ट कार्ड आईडी: <strong style="color: #6ee7b7;"><?php echo htmlspecialchars($record['card_id']); ?></strong> |
              पुष्टि लिंक आपकी ईमेल <code><?php echo htmlspecialchars($record['email']); ?></code> पर भी भेज दी गई है।
            </p>
          </div>
          <div class="col-md-4 col-sm-12 text-md-end text-start mt-3 mt-md-0 d-flex flex-wrap gap-2 justify-content-md-end justify-content-start">
            <button type="button" onclick="window.print();" class="btn btn-primary" style="background: #0284c7; border-color: #0284c7; font-weight: 700; box-shadow: 0 0 15px rgba(2, 132, 199, 0.4);">
              <i class="fas fa-print"></i> कार्ड प्रिंट करें (A4 CR80)
            </button>
            <button type="button" id="btn-download-front" class="btn btn-success" style="background: #059669; border-color: #059669; font-weight: 700;">
              <i class="fas fa-download"></i> Front PNG
            </button>
            <button type="button" id="btn-download-back" class="btn btn-info" style="background: #0284c7; border-color: #0284c7; font-weight: 700;">
              <i class="fas fa-download"></i> Back PNG
            </button>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Center / Left: Cards Area (Printable) -->
        <div class="col-lg-7 col-md-7 col-sm-12 printable-section">
          
          <h4 class="no-print" style="font-weight: 800; color: #00e5ff; margin-top: 0; margin-bottom: 16px; letter-spacing: 0.3px;">
            <i class="fas fa-id-card text-warning"></i> यूनिफ़ाइड डिजिटल हेल्थ आईडी (Unified Digital Health ID Preview)
          </h4>

          <!-- Official Printable Sheet Header (A4 Print Only) -->
          <div class="print-sheet-header">
            <div class="print-gov-row">
              <img src="<?php echo BASE_URL; ?>/images/rejuvenate-logo.png" alt="Rejuvenate Digital Health" class="print-sheet-logo" onerror="this.src='https://rejuvenatedigitalhealth.com/admin/uploads/logo/logo_header_691a7abe49524.png'">
              <div class="print-gov-title">
                <h2>REJUVENATE DIGITAL HEALTH &bull; रीजूवेनेट डिजिटल हेल्थ</h2>
                <p>Ayushman Bharat (PM-JAY) Insurance Documentation &bull; Beneficiary Eligibility Verification Pass</p>
              </div>
            </div>
            <div class="print-meta-strip">
              <div><strong>कार्ड धारक / Name:</strong> <?php echo htmlspecialchars($fullName); ?></div>
              <div><strong>आवेदन क्रमांक / App No:</strong> <?php echo htmlspecialchars($record['application_no']); ?></div>
              <div><strong>कार्ड आईडी / Card ID:</strong> <?php echo htmlspecialchars($record['card_id']); ?></div>
              <div><strong>पात्रता स्थिति / Status:</strong> <span style="color: #059669; font-weight: 700;">DOCUMENT &amp; ELIGIBILITY VERIFIED</span></div>
              <div><strong>प्रिंट दिनांक / Date:</strong> <?php echo date('d-m-Y h:i A'); ?></div>
            </div>
          </div>

          <div class="print-cut-guide">
            <span><i class="fas fa-cut"></i> <strong>कटिंग व लैमिनेशन निर्देश (CR80 Cutting Guide):</strong> प्रिंट को 100% (Actual Size) पर प्रिंट करें। बिंदीदार रेखा (Dashed border) से काटकर 86mm &times; 54mm के स्टैंडर्ड PVC पाउच में लैमिनेट करें या बीच से मोड़कर डबल-साइड कार्ड बनाएं।</span>
          </div>

          <div class="cards-container-row">
            
            <!-- 1. CARD FRONT -->
            <div class="cyber-card-outer">
              <div class="unified-card" id="unified-card-front">
                
                <!-- 1. Header Bar -->
                <div class="unified-card-header">
                  <div class="header-brand-box">
                    <div class="rejuvenate-logo-badge">
                      <img src="<?php echo BASE_URL; ?>/images/rejuvenate-logo.png" alt="Rejuvenate Digital Health" onerror="this.src='https://rejuvenatedigitalhealth.com/admin/uploads/logo/logo_header_691a7abe49524.png'">
                    </div>
                    <div class="header-titles">
                      <div class="title-hi">यूनिफ़ाइड डिजिटल हेल्थ आईडी</div>
                      <div class="title-en">Unified Digital Health ID</div>
                    </div>
                  </div>
                  <div class="hologram-lock-badge" title="AES-256 Encrypted &amp; Digitally Verified">
                    <i class="fas fa-lock"></i>
                  </div>
                </div>

                <!-- 2. Profile & Biometric Section -->
                <div class="unified-profile-section">
                  <div class="profile-photo-box">
                    <img src="<?php echo $photoUrl; ?>" alt="Citizen Photo">
                  </div>
                  
                  <div class="profile-details-box">
                    <div class="profile-name">नाम: <strong><?php echo htmlspecialchars($fullName); ?></strong></div>
                    <div class="profile-field field-uidid">ID. UIDID: <?php echo htmlspecialchars($maskedUidid); ?></div>
                    <div class="profile-field">DOB: <strong><?php echo htmlspecialchars($dobFormatted); ?></strong></div>
                    <div class="profile-field">Gender: <strong><?php echo htmlspecialchars($genderDisplay); ?></strong></div>
                    <div class="profile-field field-abha">ABHA No: <?php echo htmlspecialchars($abhaDisplay); ?></div>
                  </div>

                  <!-- Biometric Cyber Iris + QR -->
                  <div class="cyber-qr-box">
                    <div class="cyber-iris-bg">
                      <svg class="cyber-iris-svg" viewBox="0 0 120 120" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="60" cy="60" r="56" fill="none" stroke="#00e5ff" stroke-width="1.5" stroke-dasharray="6,4" opacity="0.8"/>
                        <circle cx="60" cy="60" r="50" fill="none" stroke="#38bdf8" stroke-width="1" stroke-dasharray="3,3" opacity="0.6"/>
                        <circle cx="60" cy="60" r="44" fill="none" stroke="#00e5ff" stroke-width="1.5" opacity="0.5"/>
                        <path d="M 12 60 L 2 60 M 118 60 L 108 60 M 60 12 L 60 2 M 60 118 L 60 108" stroke="#00e5ff" stroke-width="2"/>
                        <path d="M 26 26 L 20 20 M 94 94 L 100 100 M 94 26 L 100 20 M 26 94 L 20 100" stroke="#00e5ff" stroke-width="2"/>
                      </svg>
                    </div>
                    <div class="qr-code-white-card" id="qr-container-front">
                      <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($qrVerificationUrl); ?>" alt="Encrypted QR" id="qr-img-fallback">
                    </div>
                  </div>
                </div>

                <!-- 3. Four Scheme Tiles (2x2 Grid) -->
                <div class="unified-schemes-grid">
                  
                  <!-- Tile 1: PM-JAY -->
                  <div class="scheme-card-tile">
                    <div class="scheme-tile-icon">
                      <svg viewBox="0 0 100 100" class="scheme-logo-svg" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="48" fill="#ffffff" stroke="#15803d" stroke-width="3"/>
                        <circle cx="50" cy="50" r="43" fill="#f0fdf4" stroke="#22c55e" stroke-width="1.5" stroke-dasharray="3,2"/>
                        <path d="M 50 48 C 36 40 32 24 50 20 C 52 30 46 42 50 48 Z" fill="#15803d"/>
                        <path d="M 50 48 C 64 40 68 24 50 20 C 48 30 54 42 50 48 Z" fill="#22c55e"/>
                        <circle cx="50" cy="35" r="5" fill="#f97316"/>
                        <path d="M 34 50 C 34 64 46 72 50 74 C 54 72 66 64 66 50 Z" fill="#ffffff" stroke="#16a34a" stroke-width="2"/>
                        <text x="50" y="66" font-size="9" font-weight="900" font-family="Arial, sans-serif" fill="#15803d" text-anchor="middle">PM-JAY</text>
                        <text x="50" y="86" font-size="6" font-weight="700" font-family="Arial, sans-serif" fill="#047857" text-anchor="middle">AYUSHMAN</text>
                      </svg>
                    </div>
                    <div class="scheme-tile-info">
                      <div class="scheme-tile-title">Ayushman Bharat PM-JAY</div>
                      <div class="scheme-tile-meta">
                        <span>ID: <?php echo htmlspecialchars($pmjayId); ?></span>
                        <span class="badge-active">Active</span>
                      </div>
                      <div class="scheme-tile-hi">आयुष्मान भारत</div>
                    </div>
                  </div>

                  <!-- Tile 2: ABHA -->
                  <div class="scheme-card-tile">
                    <div class="scheme-tile-icon">
                      <svg viewBox="0 0 100 100" class="scheme-logo-svg" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="48" fill="#ffffff" stroke="#0284c7" stroke-width="3"/>
                        <circle cx="50" cy="50" r="44" fill="#f0f9ff"/>
                        <rect x="30" y="32" width="7" height="24" rx="3.5" fill="#f97316"/>
                        <rect x="41" y="24" width="7" height="32" rx="3.5" fill="#0284c7"/>
                        <rect x="52" y="24" width="7" height="32" rx="3.5" fill="#0ea5e9"/>
                        <rect x="63" y="32" width="7" height="24" rx="3.5" fill="#0369a1"/>
                        <path d="M 28 30 C 28 14 72 14 72 30" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round"/>
                        <text x="50" y="74" font-size="13" font-weight="900" font-family="Arial, sans-serif" fill="#0369a1" text-anchor="middle" letter-spacing="1">ABHA</text>
                        <text x="50" y="86" font-size="5.5" font-weight="700" font-family="Arial, sans-serif" fill="#0284c7" text-anchor="middle">DIGITAL HEALTH</text>
                      </svg>
                    </div>
                    <div class="scheme-tile-info">
                      <div class="scheme-tile-title">ABHA (Digital Account)</div>
                      <div class="scheme-tile-meta">
                        <span>ABHA: <?php echo htmlspecialchars($abhaDisplay); ?></span>
                      </div>
                      <div class="scheme-tile-hi">आभा कार्ड</div>
                    </div>
                  </div>

                  <!-- Tile 3: e-Shram -->
                  <div class="scheme-card-tile">
                    <div class="scheme-tile-icon">
                      <svg viewBox="0 0 100 100" class="scheme-logo-svg" xmlns="http://www.w3.org/2000/svg">
                        <rect x="2" y="2" width="96" height="96" rx="14" fill="#ffffff" stroke="#e2e8f0" stroke-width="2"/>
                        <text x="50" y="44" font-size="20" font-weight="900" font-family="Arial, sans-serif" text-anchor="middle">
                          <tspan fill="#0284c7">e-</tspan><tspan fill="#1e293b">Shram</tspan>
                        </text>
                        <rect x="18" y="52" width="64" height="3.5" rx="1.5" fill="#f97316"/>
                        <rect x="18" y="58" width="64" height="3.5" rx="1.5" fill="#16a34a"/>
                        <text x="50" y="74" font-size="6.5" font-weight="700" font-family="Arial, sans-serif" fill="#64748b" text-anchor="middle">SHRAMIK CARD</text>
                        <text x="50" y="84" font-size="5.5" font-weight="600" font-family="Arial, sans-serif" fill="#0284c7" text-anchor="middle">LABOUR</text>
                      </svg>
                    </div>
                    <div class="scheme-tile-info">
                      <div class="scheme-tile-title">e-Shram Card</div>
                      <div class="scheme-tile-meta">
                        <span>UAN: <?php echo htmlspecialchars($eshramUan); ?></span>
                      </div>
                      <div class="scheme-tile-hi">श्रम कार्ड</div>
                    </div>
                  </div>

                  <!-- Tile 4: PMGKAY Ration Card -->
                  <div class="scheme-card-tile">
                    <div class="scheme-tile-icon">
                      <svg viewBox="0 0 100 100" class="scheme-logo-svg" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="50" cy="50" r="48" fill="#ffffff" stroke="#d97706" stroke-width="3"/>
                        <circle cx="50" cy="50" r="43" fill="#fffbeb"/>
                        <path d="M 50 72 C 40 65 32 50 34 32 C 37 36 38 42 38 48 C 38 54 44 65 50 72 Z" fill="#d97706"/>
                        <ellipse cx="32" cy="34" rx="4" ry="7" transform="rotate(-25 32 34)" fill="#f59e0b"/>
                        <ellipse cx="35" cy="44" rx="4" ry="7" transform="rotate(-30 35 44)" fill="#f59e0b"/>
                        <ellipse cx="39" cy="54" rx="4" ry="7" transform="rotate(-35 39 54)" fill="#f59e0b"/>
                        <path d="M 50 72 C 60 65 68 50 66 32 C 63 36 62 42 62 48 C 62 54 56 65 50 72 Z" fill="#d97706"/>
                        <ellipse cx="68" cy="34" rx="4" ry="7" transform="rotate(25 68 34)" fill="#f59e0b"/>
                        <ellipse cx="65" cy="44" rx="4" ry="7" transform="rotate(30 65 44)" fill="#f59e0b"/>
                        <ellipse cx="61" cy="54" rx="4" ry="7" transform="rotate(35 61 54)" fill="#f59e0b"/>
                        <circle cx="50" cy="38" r="8" fill="#16a34a"/>
                        <path d="M 44 48 C 44 56 56 56 56 48 Z" fill="#15803d"/>
                        <text x="50" y="66" font-size="8" font-weight="900" font-family="Arial, sans-serif" fill="#b45309" text-anchor="middle">PMGKAY</text>
                        <text x="50" y="80" font-size="5.8" font-weight="800" font-family="Arial, sans-serif" fill="#15803d" text-anchor="middle">राशन कार्ड</text>
                      </svg>
                    </div>
                    <div class="scheme-tile-info">
                      <div class="scheme-tile-title">PM Garib Kalyan (Ration)</div>
                      <div class="scheme-tile-meta">
                        <span>ID: <?php echo htmlspecialchars($rationId); ?></span>
                        <span class="badge-phh">PHH</span>
                      </div>
                      <div class="scheme-tile-hi">प्रधान मंत्री राशन कार्ड</div>
                    </div>
                  </div>

                </div>

              </div>
            </div>

            <!-- Vertical Fold/Cut Separator (A4 Print Only) -->
            <div class="print-fold-separator">
              <span class="fold-line"></span>
              <span class="fold-text">&#9986; मोड़ें / काटें (Fold or Cut)</span>
              <span class="fold-line"></span>
            </div>

            <!-- 2. CARD BACK -->
            <div class="cyber-card-outer">
              <div class="unified-card unified-card-back" id="unified-card-back">
                
                <!-- Back Header -->
                <div class="unified-card-header">
                  <div class="header-brand-box">
                    <div class="rejuvenate-logo-badge">
                      <img src="<?php echo BASE_URL; ?>/images/rejuvenate-logo.png" alt="Rejuvenate Digital Health" onerror="this.src='https://rejuvenatedigitalhealth.com/admin/uploads/logo/logo_header_691a7abe49524.png'">
                    </div>
                    <div class="header-titles">
                      <div class="title-hi">आयुष्मान बीमा नियम एवं शर्तें</div>
                      <div class="title-en">Ayushman (PM-JAY) Eligibility Terms</div>
                    </div>
                  </div>
                  <div class="hologram-lock-badge" title="Cryptographically Verified">
                    <i class="fas fa-shield-alt"></i>
                  </div>
                </div>

                <div class="card-back-body">
                  <div class="card-back-address">
                    <strong><i class="fas fa-home"></i> स्थायी पता / Permanent Address:</strong><br>
                    <?php echo htmlspecialchars($fullAddress); ?>
                  </div>

                  <div class="card-back-rules">
                    <ul>
                      <li><strong>योजना दस्तावेज एवं पात्रता सत्यापन:</strong> यह कार्ड नागरिक के आयुष्मान भारत (PM-JAY) स्वास्थ्य बीमा से जुड़े आवश्यक पहचान दस्तावेजों की जानकारी तथा पात्रता स्थिति (Eligibility Status) का प्राधिकृत डिजिटल रिकॉर्ड प्रदान करता है।</li>
                      <li><strong>डिजिटल सत्यापन एवं सुरक्षा:</strong> कार्ड पर उपलब्ध AES-256 एन्क्रिप्टेड QR कोड Rejuvenate Digital Health द्वारा नागरिक की पात्रता, ABHA संख्या व संबद्ध दस्तावेजों को त्वरित और सुरक्षित रूप से सत्यापित (Verify) करने हेतु प्रयुक्त होता है।</li>
                      <li><strong>कैशलेस उपचार मार्गदर्शन:</strong> PM-JAY योजना अंतर्गत प्रति वर्ष ₹5 लाख तक के कैशलेस उपचार मार्गदर्शन हेतु नजदीकी संबद्ध अस्पताल अथवा हेल्पलाइन <strong>14555</strong> / <strong>108</strong> पर संपर्क करें।</li>
                    </ul>
                  </div>

                  <div class="card-back-bottom">
                    <div>
                      <div class="label-sm">ISSUING DATE:</div>
                      <div class="val-sm"><?php echo date('d-m-Y', strtotime($record['created_at'])); ?></div>
                    </div>
                    <div style="text-align: center;">
                      <span style="background: #0284c7; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 2px 7px; border-radius: 4px;">
                        <i class="fas fa-check-double"></i> Digitally Signed
                      </span>
                    </div>
                    <div style="text-align: right;">
                      <div class="label-sm">SECURITY TOKEN:</div>
                      <div class="val-sm" style="font-family: monospace; font-size: 0.68rem;">
                        <?php echo strtoupper(substr(hash('sha256', $record['encrypted_qr_token']), 0, 12)); ?>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="card-back-footer">
                  <span>PM-JAY Document &amp; Eligibility Desk</span>
                  <span>www.rejuvenatedigitalhealth.com</span>
                </div>

              </div>
            </div>

          </div> <!-- /.cards-container-row -->

          <!-- Official Printable Sheet Footer -->
          <div class="print-sheet-footer">
            <p><strong>Rejuvenate Digital Health Facilitation Services</strong> &bull; https://rejuvenatedigitalhealth.com</p>
            <p>यह कार्ड नागरिक के आयुष्मान भारत (PM-JAY) स्वास्थ्य बीमा दस्तावेजों एवं पात्रता स्थिति का प्राधिकृत डिजिटल सत्यापन अभिलेख है &bull; 256-Bit Encrypted Data &bull; आपातकालीन हेल्पलाइन: <strong>14555</strong> / <strong>108</strong></p>
          </div>

        </div>

        <!-- Right Column: Card Metadata & Security Audit (Screen Only) -->
        <div class="col-lg-5 col-md-5 col-sm-12 no-print">
          
          <!-- Metadata Card -->
          <div style="background: #ffffff; border: 1px solid #00e5ff; border-radius: 14px; padding: 22px; margin-bottom: 20px; box-shadow: 0 4px 18px rgba(0, 229, 255, 0.15);">
            <h4 style="font-weight: 800; color: #0b3c5d; margin-top: 0; border-bottom: 2px solid #00e5ff; padding-bottom: 8px;">
              <i class="fas fa-info-circle text-info"></i> विवरण एवं सत्यापन स्थिति
            </h4>

            <table class="table" style="font-size: 0.88rem; margin-bottom: 0;">
              <tr>
                <td style="color: #64748b; font-weight: 600; width: 45%;">कार्ड धारक:</td>
                <td><strong><?php echo htmlspecialchars($fullName); ?></strong></td>
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
          <div style="background: #041933; border: 1.5px solid #00e5ff; border-radius: 14px; padding: 20px; box-shadow: 0 0 20px rgba(0, 229, 255, 0.15);">
            <div style="display: flex; gap: 12px;">
              <i class="fas fa-shield-alt fa-2x" style="color: #00e5ff; flex-shrink: 0;"></i>
              <div>
                <h5 style="margin: 0 0 6px; font-weight: 800; color: #00e5ff;">सुरक्षित एन्क्रिप्टेड क्यूआर तकनीक</h5>
                <p style="margin: 0; font-size: 0.83rem; color: #bae6fd; line-height: 1.5;">
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
        width: 66,
        height: 66,
        colorDark: "#051937",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.M
      });
    }
  <?php endif; ?>
});

// Download Front Card as PNG Image via html2canvas
const btnDownloadFront = document.getElementById("btn-download-front");
if (btnDownloadFront) {
  btnDownloadFront.addEventListener("click", function() {
    const cardEl = document.getElementById("unified-card-front");
    if (!cardEl) return;

    btnDownloadFront.disabled = true;
    btnDownloadFront.innerHTML = '<i class="fas fa-spinner fa-spin"></i> रेंडर हो रहा है...';

    html2canvas(cardEl, {
      scale: 3, // High DPI for crisp print-quality rendering
      useCORS: true,
      backgroundColor: "#ffffff"
    }).then(canvas => {
      const link = document.createElement("a");
      link.download = "Unified_Digital_Health_ID_FRONT_<?php echo htmlspecialchars($record['card_id'] ?? 'card'); ?>.png";
      link.href = canvas.toDataURL("image/png");
      link.click();

      btnDownloadFront.disabled = false;
      btnDownloadFront.innerHTML = '<i class="fas fa-download"></i> Front PNG';
    }).catch(err => {
      alert("कार्ड डाउनलोड करने में त्रुटि: " + err);
      btnDownloadFront.disabled = false;
      btnDownloadFront.innerHTML = '<i class="fas fa-download"></i> Front PNG';
    });
  });
}

// Download Back Card as PNG Image via html2canvas
const btnDownloadBack = document.getElementById("btn-download-back");
if (btnDownloadBack) {
  btnDownloadBack.addEventListener("click", function() {
    const cardEl = document.getElementById("unified-card-back");
    if (!cardEl) return;

    btnDownloadBack.disabled = true;
    btnDownloadBack.innerHTML = '<i class="fas fa-spinner fa-spin"></i> रेंडर हो रहा है...';

    html2canvas(cardEl, {
      scale: 3,
      useCORS: true,
      backgroundColor: "#ffffff"
    }).then(canvas => {
      const link = document.createElement("a");
      link.download = "Unified_Digital_Health_ID_BACK_<?php echo htmlspecialchars($record['card_id'] ?? 'card'); ?>.png";
      link.href = canvas.toDataURL("image/png");
      link.click();

      btnDownloadBack.disabled = false;
      btnDownloadBack.innerHTML = '<i class="fas fa-download"></i> Back PNG';
    }).catch(err => {
      alert("कार्ड डाउनलोड करने में त्रुटि: " + err);
      btnDownloadBack.disabled = false;
      btnDownloadBack.innerHTML = '<i class="fas fa-download"></i> Back PNG';
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
