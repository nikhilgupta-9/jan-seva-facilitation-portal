<?php
// admin/orders.php - Order History & Transaction Management
require_once __DIR__ . '/auth.php';

$page_title = 'Orders & Revenue Audit';

$status_filter = trim($_GET['status'] ?? 'all');
$search_query = trim($_GET['search'] ?? '');

$sql = "SELECT o.*, s.service_name, s.slug, u.phone, u.name as citizen_name 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        LEFT JOIN users u ON o.user_id = u.id 
        WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $sql .= " AND o.status = ?";
    $params[] = $status_filter;
}

if (!empty($search_query)) {
    $sql .= " AND (o.order_id LIKE ? OR u.phone LIKE ? OR u.name LIKE ?)";
    $term = "%{$search_query}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY o.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>

<!-- Title Row -->
<div class="row" style="margin-bottom: 24px;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-navy); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-receipt text-warning"></i> Orders &amp; Payment Audit Ledger
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.9rem;">
      Audited record of all generated cards, payment gateway transaction IDs, and citizen requests.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <span class="badge" style="background: #e2e8f0; color: #334155; font-size: 0.9rem; padding: 7px 14px;">
      Total: <?php echo count($orders); ?> Orders
    </span>
  </div>
</div>

<div class="adm-card">
  <!-- Filters Bar -->
  <div class="adm-card-header">
    <form method="GET" action="<?php echo BASE_URL; ?>/admin/orders.php" class="form-inline" style="display: flex; gap: 10px; flex-wrap: wrap; width: 100%;">
      <div class="form-group">
        <label for="status" style="margin-right: 6px; font-size: 0.88rem;">Filter Status:</label>
        <select name="status" id="status" class="form-control" onchange="this.form.submit();" style="height: 36px;">
          <option value="all" <?php echo ($status_filter === 'all') ? 'selected' : ''; ?>>All Statuses</option>
          <option value="paid" <?php echo ($status_filter === 'paid') ? 'selected' : ''; ?>>Paid Only</option>
          <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Pending Only</option>
          <option value="failed" <?php echo ($status_filter === 'failed') ? 'selected' : ''; ?>>Failed Only</option>
        </select>
      </div>

      <div class="form-group" style="flex: 1; min-width: 200px;">
        <input type="text" name="search" class="form-control" placeholder="Search order ID, citizen name, phone..." value="<?php echo htmlspecialchars($search_query); ?>" style="width: 100%; height: 36px;">
      </div>

      <button type="submit" class="btn btn-primary btn-sm" style="height: 36px; padding: 0 16px;">
        <i class="fas fa-search"></i> Search
      </button>

      <?php if (!empty($search_query) || $status_filter !== 'all'): ?>
        <a href="<?php echo BASE_URL; ?>/admin/orders.php" class="btn btn-default btn-sm" style="height: 36px; line-height: 24px;">Reset</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="adm-card-body" style="padding: 0;">
    <?php if (!empty($orders)): ?>
      <div class="table-responsive">
        <table class="table table-hover table-striped" style="font-size: 0.88rem; margin-bottom: 0;">
          <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
            <tr>
              <th style="width: 50px;">#</th>
              <th>Order ID</th>
              <th>Citizen Details</th>
              <th>Service</th>
              <th>Fee Amount</th>
              <th>Payment Gateway ID</th>
              <th>Status</th>
              <th>Timestamp</th>
              <th style="text-align: right;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($orders as $idx => $ord): ?>
              <tr>
                <td><?php echo $idx + 1; ?></td>
                <td><code><?php echo htmlspecialchars($ord['order_id']); ?></code></td>
                <td>
                  <strong><?php echo htmlspecialchars($ord['citizen_name'] ?? 'Citizen'); ?></strong><br>
                  <small class="text-muted"><i class="fas fa-phone-alt"></i> +91-<?php echo htmlspecialchars($ord['phone'] ?? 'N/A'); ?></small>
                </td>
                <td>
                  <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.75rem;">
                    <?php echo htmlspecialchars($ord['service_name']); ?>
                  </span>
                </td>
                <td><strong><?php echo format_inr($ord['amount']); ?></strong></td>
                <td>
                  <?php if (!empty($ord['payment_id'])): ?>
                    <code style="font-size: 0.78rem;"><?php echo htmlspecialchars($ord['payment_id']); ?></code>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($ord['status'] === 'paid'): ?>
                    <span class="label label-success" style="font-size: 0.74rem;">PAID</span>
                  <?php elseif ($ord['status'] === 'pending'): ?>
                    <span class="label label-warning" style="font-size: 0.74rem;">PENDING</span>
                  <?php else: ?>
                    <span class="label label-danger" style="font-size: 0.74rem;">FAILED</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span style="font-size: 0.8rem; color: #64748b;">
                    <?php echo date('d M Y, h:i A', strtotime($ord['created_at'])); ?>
                  </span>
                </td>
                <td style="text-align: right;">
                  <?php if ($ord['status'] === 'paid'): ?>
                    <a href="<?php echo BASE_URL; ?>/download-card.php?order_id=<?php echo urlencode($ord['order_id']); ?>" target="_blank" class="btn btn-info btn-xs" title="View / Print Card">
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
      <p class="text-muted text-center" style="padding: 40px 0;">No transactions found matching your criteria.</p>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
