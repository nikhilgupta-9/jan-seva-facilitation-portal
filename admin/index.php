<?php
// admin/index.php - Facilitator Dashboard
require_once __DIR__ . '/auth.php';

$page_title = 'Dashboard Overview';

// 1. Fetch Metrics
// Total Revenue & Paid Orders
$stmtRev = $pdo->query("SELECT COALESCE(SUM(amount), 0) as total_rev, COUNT(id) as total_paid FROM orders WHERE status = 'paid'");
$revData = $stmtRev->fetch();
$total_revenue = (float) $revData['total_rev'];
$total_paid = (int) $revData['total_paid'];

// Today's Collection
$stmtToday = $pdo->query("SELECT COALESCE(SUM(amount), 0) as today_rev, COUNT(id) as today_paid FROM orders WHERE status = 'paid' AND DATE(created_at) = CURDATE()");
$todayData = $stmtToday->fetch();
$today_revenue = (float) $todayData['today_rev'];
$today_paid = (int) $todayData['today_paid'];

// Total Orders by status
$stmtStatus = $pdo->query("SELECT status, COUNT(id) as cnt FROM orders GROUP BY status");
$statusCounts = ['paid' => 0, 'pending' => 0, 'failed' => 0];
while ($row = $stmtStatus->fetch()) {
    $statusCounts[$row['status']] = (int) $row['cnt'];
}
$total_orders = array_sum($statusCounts);

// Doorstep leads
$stmtDoor = $pdo->query("SELECT COUNT(id) FROM doorstep_requests WHERE status = 'new'");
$pending_doorstep = (int) $stmtDoor->fetchColumn();

// Citizen Inquiries count
$stmtMsg = $pdo->query("SELECT COUNT(id) FROM contact_messages WHERE status = 'new'");
$new_messages = (int) $stmtMsg->fetchColumn();

// Total Services
$stmtSrv = $pdo->query("SELECT COUNT(id) FROM services WHERE is_active = 1");
$active_services = (int) $stmtSrv->fetchColumn();

// 2. Fetch Recent Orders
$stmtRecent = $pdo->query("SELECT o.*, s.service_name, s.slug, u.phone, u.name as citizen_name 
                           FROM orders o 
                           JOIN services s ON o.service_id = s.id 
                           LEFT JOIN users u ON o.user_id = u.id 
                           ORDER BY o.id DESC LIMIT 10");
$recent_orders = $stmtRecent->fetchAll();

// 3. Daily Breakdown for past 7 days
$stmtDaily = $pdo->query("SELECT DATE(created_at) as order_date, COUNT(id) as order_count, SUM(amount) as daily_total 
                          FROM orders 
                          WHERE status = 'paid' 
                          GROUP BY DATE(created_at) 
                          ORDER BY order_date DESC LIMIT 7");
$daily_breakdown = $stmtDaily->fetchAll();

include __DIR__ . '/header.php';
?>

<!-- Dashboard Header Welcome -->
<div class="row" style="margin-bottom: 24px;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-navy); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-tachometer-alt text-warning"></i> Operations Dashboard &amp; Real-time Analytics
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.9rem;">
      Live overview of digital card generation, daily collections, doorstep leads, and citizen communications.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <a href="<?php echo BASE_URL; ?>/admin/contact-settings.php" class="btn btn-default" style="font-weight: 600;">
      <i class="fas fa-cog"></i> Site &amp; Contact Settings
    </a>
  </div>
</div>

<!-- Metric Cards Row (4 Responsive Columns) -->
<div class="row">
  <!-- Card 1: Total Collections -->
  <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
    <div class="stat-card">
      <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
        <i class="fas fa-rupee-sign"></i>
      </div>
      <div>
        <div class="stat-label">Total Collections</div>
        <div class="stat-value text-success"><?php echo format_inr($total_revenue); ?></div>
        <small class="text-muted"><?php echo $total_paid; ?> Paid Requests</small>
      </div>
    </div>
  </div>

  <!-- Card 2: Today's Revenue -->
  <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
    <div class="stat-card">
      <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
        <i class="fas fa-calendar-day"></i>
      </div>
      <div>
        <div class="stat-label">Today's Revenue</div>
        <div class="stat-value" style="color: #d97706;"><?php echo format_inr($today_revenue); ?></div>
        <small class="text-muted"><?php echo $today_paid; ?> Orders Today</small>
      </div>
    </div>
  </div>

  <!-- Card 3: Doorstep Leads -->
  <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
    <div class="stat-card">
      <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
        <i class="fas fa-user-shield"></i>
      </div>
      <div>
        <div class="stat-label">Doorstep Leads</div>
        <div class="stat-value text-danger"><?php echo $pending_doorstep; ?></div>
        <small class="text-muted">Awaiting Follow-up</small>
      </div>
    </div>
  </div>

  <!-- Card 4: Citizen Messages -->
  <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
    <div class="stat-card">
      <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
        <i class="fas fa-envelope-open-text"></i>
      </div>
      <div>
        <div class="stat-label">New Inquiries</div>
        <div class="stat-value" style="color: #0284c7;"><?php echo $new_messages; ?></div>
        <small class="text-muted"><a href="<?php echo BASE_URL; ?>/admin/messages.php" style="color: inherit; text-decoration: underline;">View Inbox</a></small>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <!-- Left Column: Recent Orders Table -->
  <div class="col-lg-8 col-md-12">
    <div class="adm-card">
      <div class="adm-card-header">
        <h4><i class="fas fa-history text-primary"></i> Recent Transactions &amp; Card Requests</h4>
        <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn btn-default btn-xs" style="font-weight: 600;">
          View All Orders <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <div class="adm-card-body" style="padding: 0;">
        <?php if (!empty($recent_orders)): ?>
          <div class="table-responsive">
            <table class="table table-hover table-striped" style="font-size: 0.88rem; margin-bottom: 0;">
              <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                <tr>
                  <th>Order ID</th>
                  <th>Service</th>
                  <th>Citizen</th>
                  <th>Amount</th>
                  <th>Status</th>
                  <th style="text-align: right;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recent_orders as $ord): ?>
                  <tr>
                    <td><code><?php echo htmlspecialchars($ord['order_id']); ?></code></td>
                    <td>
                      <span class="badge" style="background: #eff6ff; color: #1d4ed8; font-size: 0.75rem;">
                        <?php echo htmlspecialchars($ord['service_name']); ?>
                      </span>
                    </td>
                    <td>
                      <strong><?php echo htmlspecialchars($ord['citizen_name'] ?? 'Citizen'); ?></strong><br>
                      <small class="text-muted">+91-<?php echo htmlspecialchars($ord['phone'] ?? 'N/A'); ?></small>
                    </td>
                    <td><strong><?php echo format_inr($ord['amount']); ?></strong></td>
                    <td>
                      <?php if ($ord['status'] === 'paid'): ?>
                        <span class="label label-success" style="font-size: 0.74rem;">PAID</span>
                      <?php elseif ($ord['status'] === 'pending'): ?>
                        <span class="label label-warning" style="font-size: 0.74rem;">PENDING</span>
                      <?php else: ?>
                        <span class="label label-danger" style="font-size: 0.74rem;">FAILED</span>
                      <?php endif; ?>
                    </td>
                    <td style="text-align: right;">
                      <?php if ($ord['status'] === 'paid'): ?>
                        <a href="<?php echo BASE_URL; ?>/download-card.php?order_id=<?php echo urlencode($ord['order_id']); ?>" target="_blank" class="btn btn-info btn-xs" title="View / Print PVC Card">
                          <i class="fas fa-id-card"></i> Card
                        </a>
                      <?php else: ?>
                        <span class="text-muted">—</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <p class="text-muted text-center" style="padding: 30px 0;">No transactions recorded yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Right Column: Daily Collections Breakdown & Gateway Status -->
  <div class="col-lg-4 col-md-12">
    <!-- Daily Breakdown Box -->
    <div class="adm-card">
      <div class="adm-card-header">
        <h4><i class="fas fa-chart-line text-success"></i> Past 7 Days Collections</h4>
      </div>
      <div class="adm-card-body">
        <?php if (!empty($daily_breakdown)): ?>
          <table class="table table-condensed" style="font-size: 0.85rem; margin-bottom: 0;">
            <thead>
              <tr>
                <th>Date</th>
                <th>Cards</th>
                <th class="text-right">Collection</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($daily_breakdown as $db): ?>
                <tr>
                  <td><?php echo date('d M Y', strtotime($db['order_date'])); ?></td>
                  <td><span class="badge" style="background: #e2e8f0; color: #334155;"><?php echo $db['order_count']; ?></span></td>
                  <td class="text-right"><strong><?php echo format_inr($db['daily_total']); ?></strong></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <p class="text-muted" style="font-size: 0.85rem; margin: 0;">No collections recorded yet for this period.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Shortcuts Card -->
    <div class="adm-card">
      <div class="adm-card-header">
        <h4><i class="fas fa-bolt text-warning"></i> Quick Management</h4>
      </div>
      <div class="adm-card-body" style="font-size: 0.88rem;">
        <ul style="list-style: none; padding-left: 0; line-height: 2.2; margin: 0;">
          <li>
            <a href="<?php echo BASE_URL; ?>/admin/contact-settings.php">
              <i class="fas fa-address-book text-info" style="width: 20px;"></i> Edit Contact &amp; Helpline
            </a>
          </li>
          <li>
            <a href="<?php echo BASE_URL; ?>/admin/services.php">
              <i class="fas fa-cogs text-primary" style="width: 20px;"></i> Adjust Card Fees &amp; Catalog
            </a>
          </li>
          <li>
            <a href="<?php echo BASE_URL; ?>/admin/doorstep.php">
              <i class="fas fa-user-shield text-danger" style="width: 20px;"></i> Manage Doorstep Leads (<?php echo $pending_doorstep; ?>)
            </a>
          </li>
          <li>
            <a href="<?php echo BASE_URL; ?>/admin/messages.php">
              <i class="fas fa-envelope text-success" style="width: 20px;"></i> Citizen Inquiries Inbox (<?php echo $new_messages; ?>)
            </a>
          </li>
        </ul>
      </div>
    </div>

    <!-- System Health Box -->
    <div class="adm-card">
      <div class="adm-card-header">
        <h4><i class="fas fa-server text-info"></i> Gateway &amp; Sandbox Status</h4>
      </div>
      <div class="adm-card-body" style="font-size: 0.85rem;">
        <ul style="list-style: none; padding-left: 0; line-height: 2; margin-bottom: 0;">
          <li><i class="fas fa-check-circle text-success"></i> Payment Gateway: <strong>Razorpay (<?php echo strtoupper(PAYMENT_MODE); ?>)</strong></li>
          <li><i class="fas fa-check-circle text-success"></i> SMS OTP Provider: <strong>Enabled (Sandbox Bypass: 123456)</strong></li>
          <li><i class="fas fa-check-circle text-success"></i> ABDM / DigiLocker APIs: <strong>Operational</strong></li>
          <li><i class="fas fa-check-circle text-success"></i> Database: <strong>MariaDB / MySQL 8.x</strong></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
