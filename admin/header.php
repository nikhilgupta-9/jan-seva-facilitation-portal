<?php
// admin/header.php - Global Modular Admin Header & Sidebar Navigation
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

$current_admin_page = basename($_SERVER['PHP_SELF']);

// Fetch active badges
$badge_doorstep = 0;
$badge_messages = 0;
$badge_feedbacks = 0;
try {
    if (isset($pdo)) {
        $badge_doorstep = (int) $pdo->query("SELECT COUNT(id) FROM doorstep_requests WHERE status = 'new'")->fetchColumn();
        $badge_messages = (int) $pdo->query("SELECT COUNT(id) FROM contact_messages WHERE status = 'new'")->fetchColumn();
        $badge_feedbacks = (int) $pdo->query("SELECT COUNT(id) FROM feedbacks WHERE status = 'pending'")->fetchColumn();
    }
} catch (Exception $e) {
    // Silent
}

// Nav items: shared between desktop sidebar and mobile drawer
$admin_nav_items = [
    ['page' => 'index.php', 'icon' => 'fa-tachometer-alt', 'label' => 'Dashboard', 'badge' => 0],
    ['page' => 'orders.php', 'icon' => 'fa-receipt', 'label' => 'Orders & Revenue', 'badge' => 0],
    ['page' => 'services.php', 'icon' => 'fa-cogs', 'label' => 'Service Catalog', 'badge' => 0],
    ['page' => 'schemes.php', 'icon' => 'fa-landmark', 'label' => 'Govt Schemes', 'badge' => 0],
    ['page' => 'doorstep.php', 'icon' => 'fa-user-shield', 'label' => 'Doorstep Leads', 'badge' => $badge_doorstep],
    ['page' => 'feedbacks.php', 'icon' => 'fa-comments', 'label' => 'Citizen Feedbacks', 'badge' => $badge_feedbacks],
    ['page' => 'contact-settings.php', 'icon' => 'fa-address-book', 'label' => 'Contact & Site Settings', 'badge' => 0],
    ['page' => 'messages.php', 'icon' => 'fa-envelope', 'label' => 'Citizen Inquiries', 'badge' => $badge_messages],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title><?php echo isset($page_title) ? $page_title . ' | Admin Console' : 'Admin Console | ' . APP_NAME; ?></title>

<!-- Fonts & Assets -->
<link rel="icon" href="<?php echo BASE_URL; ?>/img/favicon.ico" type="image/x-icon">
<link href="<?php echo BASE_URL; ?>/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
  :root {
    --adm-saffron: #f37021;
    --adm-saffron-dark: #d8570c;
    --adm-saffron-tint: #fff1e6;
    --adm-blue: #0ea5e9;
    --adm-blue-dark: #0284c7;
    --adm-blue-tint: #eaf6fe;
    --adm-green: #16a34a;
    --adm-bg: #f4f9fc;
    --adm-border: #e2e8f0;
    --adm-text: #1e293b;
    --adm-sidebar-w: 264px;
    --adm-navy: var(--adm-blue-dark); /* legacy alias used across content pages */
  }
  * { box-sizing: border-box; }
  html, body {
    width: 100%;
    max-width: 100%;
    overflow-x: hidden;
  }
  body {
    background-color: var(--adm-bg);
    color: var(--adm-text);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    margin: 0;
    padding: 0;
    line-height: 1.5;
  }

  /* ===== Sidebar ===== */
  .admin-sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: var(--adm-sidebar-w);
    height: 100vh;
    background: #ffffff;
    border-right: 1px solid var(--adm-border);
    display: flex;
    flex-direction: column;
    z-index: 1040;
    transition: transform 0.25s ease;
  }
  .admin-sidebar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 18px 20px;
    border-bottom: 1px solid var(--adm-border);
    text-decoration: none;
    background: linear-gradient(135deg, var(--adm-blue-tint) 0%, var(--adm-saffron-tint) 100%);
  }
  .admin-sidebar-brand-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--adm-saffron) 0%, var(--adm-saffron-dark) 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    color: #ffffff;
    flex-shrink: 0;
  }
  .admin-sidebar-brand-title {
    font-size: 1rem;
    font-weight: 800;
    letter-spacing: -0.2px;
    line-height: 1.2;
    margin: 0;
    color: var(--adm-blue-dark);
  }
  .admin-sidebar-brand-title small {
    display: block;
    font-size: 0.68rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
  }
  .admin-sidebar-nav {
    flex: 1;
    overflow-y: auto;
    padding: 14px 12px;
  }
  .admin-nav-list {
    margin: 0;
    padding: 0;
    list-style: none;
  }
  .admin-nav-list > li {
    margin-bottom: 4px;
  }
  .admin-nav-list > li > a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    color: #475569;
    font-weight: 600;
    font-size: 0.88rem;
    text-decoration: none;
    border-radius: 8px;
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
  }
  .admin-nav-list > li > a i:first-child {
    width: 18px;
    text-align: center;
    color: var(--adm-blue-dark);
  }
  .admin-nav-list > li > a:hover {
    color: var(--adm-blue-dark);
    background: var(--adm-blue-tint);
  }
  .admin-nav-list > li.active > a {
    color: var(--adm-saffron-dark);
    background: var(--adm-saffron-tint);
    border-left-color: var(--adm-saffron);
    font-weight: 700;
  }
  .admin-nav-list > li.active > a i:first-child {
    color: var(--adm-saffron);
  }
  .admin-nav-badge {
    margin-left: auto;
    background: #ef4444;
    color: #ffffff;
    font-size: 0.7rem;
    padding: 1px 7px;
    border-radius: 10px;
    font-weight: 700;
  }
  .admin-sidebar-footer {
    padding: 14px 18px;
    border-top: 1px solid var(--adm-border);
  }
  .admin-sidebar-footer a {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
    padding: 6px 0;
  }
  .admin-sidebar-footer a:hover { color: var(--adm-blue-dark); }

  /* ===== Sidebar backdrop (mobile) ===== */
  .admin-sidebar-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    z-index: 1035;
  }
  .admin-sidebar-backdrop.show { display: block; }

  /* ===== Topbar ===== */
  .admin-topbar {
    position: fixed;
    top: 0;
    left: var(--adm-sidebar-w);
    right: 0;
    width: auto;
    background: #ffffff;
    border-bottom: 1px solid var(--adm-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    padding: 12px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    z-index: 1020;
    height: 64px;
  }
  .admin-topbar-left {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
  }
  .admin-topbar-title {
    font-size: 1.05rem;
    font-weight: 800;
    color: var(--adm-blue-dark);
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }
  .admin-mobile-toggle {
    display: none;
    background: var(--adm-blue-tint);
    border: 1px solid var(--adm-border);
    color: var(--adm-blue-dark);
    width: 38px;
    height: 38px;
    border-radius: 8px;
    font-size: 1rem;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .admin-topbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
  }
  .admin-user-pill {
    background: var(--adm-blue-tint);
    border: 1px solid #bae6fd;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.82rem;
    color: #0c4a6e;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .admin-user-pill .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #22c55e;
  }

  /* ===== Content wrapper (sticky footer layout) ===== */
  .admin-content-wrap {
    margin-left: var(--adm-sidebar-w);
    padding-top: 64px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
  }
  main.admin-main {
    flex: 1 0 auto;
    width: 100%;
    padding: 24px;
    max-width: 1400px;
    margin: 0 auto;
  }
  .admin-footer {
    flex-shrink: 0;
    width: 100%;
  }

  /* ===== Cards & General Elements ===== */
  .adm-card {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid var(--adm-border);
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    margin-bottom: 24px;
    overflow: hidden;
  }
  .adm-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--adm-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #ffffff;
    flex-wrap: wrap;
    gap: 10px;
  }
  .adm-card-header h4 {
    margin: 0;
    font-size: 1.15rem;
    font-weight: 800;
    color: var(--adm-blue-dark);
  }
  .adm-card-body {
    padding: 20px;
  }

  /* Stat Card Widget */
  .stat-card {
    background: #ffffff;
    border-radius: 10px;
    padding: 18px 20px;
    border: 1px solid var(--adm-border);
    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s;
  }
  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.06);
  }
  .stat-icon {
    width: 52px;
    height: 52px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.45rem;
    flex-shrink: 0;
  }
  .stat-label {
    font-size: 0.78rem;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.4px;
  }
  .stat-value {
    font-size: 1.55rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.2;
    margin: 2px 0;
  }

  /* Responsive Tables */
  .table-responsive {
    border: 1px solid var(--adm-border);
    border-radius: 8px;
    background: #ffffff;
  }
  .table-responsive table {
    margin-bottom: 0;
  }

  /* Form Elements */
  .form-control {
    border-radius: 6px;
    border-color: #cbd5e1;
    height: 42px;
    box-shadow: none;
    font-size: 0.92rem;
  }
  .form-control:focus {
    border-color: var(--adm-saffron);
    box-shadow: 0 0 0 3px rgba(243, 112, 33, 0.15);
  }
  textarea.form-control {
    height: auto;
  }

  /* ===== Legacy Bootstrap 3 class shim (content pages still use these) ===== */
  /* Bootstrap 5 dropped .label, .btn-default, .btn-xs, .input-sm, .table-condensed
     and the bare .col-xs-* tier, which made cards/badges/buttons collapse to
     shrink-to-fit content instead of their intended size. Re-create them here. */
  .label {
    display: inline-block;
    padding: 3px 8px;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    color: #ffffff;
    border-radius: 4px;
    white-space: nowrap;
  }
  .label-success  { background: var(--adm-green); }
  .label-warning  { background: #d97706; }
  .label-danger   { background: #dc2626; }
  .label-info     { background: var(--adm-blue-dark); }
  .label-default  { background: #64748b; }

  .btn-default {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
  }
  .btn-default:hover { background: #f1f5f9; color: #1e293b; }
  .btn-gov-saffron {
    background: linear-gradient(135deg, var(--adm-saffron) 0%, var(--adm-saffron-dark) 100%);
    border: none;
    color: #ffffff;
  }
  .btn-gov-saffron:hover {
    background: linear-gradient(135deg, var(--adm-saffron-dark) 0%, var(--adm-saffron) 100%);
    color: #ffffff;
  }
  .btn-xs {
    padding: 2px 9px;
    font-size: 0.75rem;
    border-radius: 5px;
    line-height: 1.6;
  }
  .input-sm {
    height: 32px;
    padding: 4px 10px;
    font-size: 0.82rem;
  }
  .table-condensed td,
  .table-condensed th {
    padding: 6px 10px;
  }

  @media (max-width: 575.98px) {
    .col-xs-12 { flex: 0 0 100%; max-width: 100%; }
    .col-xs-6  { flex: 0 0 50%; max-width: 50%; }
  }

  /* ===== Mobile ===== */
  @media (max-width: 991px) {
    .admin-sidebar {
      transform: translateX(-100%);
    }
    .admin-sidebar.show {
      transform: translateX(0);
      box-shadow: 0 0 24px rgba(0,0,0,0.25);
    }
    .admin-topbar {
      left: 0;
    }
    .admin-content-wrap {
      margin-left: 0;
    }
    .admin-mobile-toggle {
      display: inline-flex;
    }
    .admin-topbar-title {
      font-size: 0.95rem;
    }
  }

  @media (max-width: 575px) {
    .admin-user-pill { display: none; }
    main.admin-main { padding: 15px; }
    .adm-card-header { flex-direction: column; align-items: flex-start; }
    .adm-card-body { padding: 15px; }
  }
</style>
</head>
<body>

<!-- Mobile backdrop -->
<div class="admin-sidebar-backdrop" id="admin-sidebar-backdrop"></div>

<!-- 1. Sidebar -->
<aside class="admin-sidebar" id="admin-sidebar">
  <a href="<?php echo BASE_URL; ?>/admin/index.php" class="admin-sidebar-brand">
    <div class="admin-sidebar-brand-icon">
      <i class="fas fa-shield-alt"></i>
    </div>
    <h1 class="admin-sidebar-brand-title">
      <?php echo htmlspecialchars(APP_NAME); ?>
      <small>Admin Console</small>
    </h1>
  </a>

  <nav class="admin-sidebar-nav">
    <ul class="admin-nav-list">
      <?php foreach ($admin_nav_items as $item): ?>
        <li class="<?php echo ($current_admin_page === $item['page']) ? 'active' : ''; ?>">
          <a href="<?php echo BASE_URL; ?>/admin/<?php echo $item['page']; ?>">
            <i class="fas <?php echo $item['icon']; ?>"></i>
            <?php echo htmlspecialchars($item['label']); ?>
            <?php if ($item['badge'] > 0): ?>
              <span class="admin-nav-badge"><?php echo $item['badge']; ?></span>
            <?php endif; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <div class="admin-sidebar-footer">
    <a href="<?php echo BASE_URL; ?>/index.php" target="_blank">
      <i class="fas fa-external-link-alt"></i> Live Website
    </a>
    <a href="<?php echo BASE_URL; ?>/admin/logout.php">
      <i class="fas fa-sign-out-alt"></i> Logout
    </a>
  </div>
</aside>

<!-- 2. Topbar + Content -->
<div class="admin-content-wrap">
  <header class="admin-topbar">
    <div class="admin-topbar-left">
      <button type="button" class="admin-mobile-toggle" id="btn-admin-nav-toggle" aria-label="Toggle Navigation">
        <i class="fas fa-bars"></i>
      </button>
      <h2 class="admin-topbar-title"><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Dashboard'; ?></h2>
    </div>
    <div class="admin-topbar-right">
      <span class="admin-user-pill">
        <span class="status-dot"></span>
        <strong><?php echo htmlspecialchars($admin_user ?? 'Admin'); ?></strong>
      </span>
    </div>
  </header>

  <main class="admin-main">
    <!-- Flash Alert Message -->
    <?php $flash = get_flash(); if ($flash): ?>
      <div class="alert alert-<?php echo ($flash['type'] === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert" style="border-radius: 8px;">
        <strong><?php echo ($flash['type'] === 'success') ? '<i class="fas fa-check-circle"></i> Success:' : '<i class="fas fa-exclamation-circle"></i> Notice:'; ?></strong>
        <?php echo $flash['message']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
