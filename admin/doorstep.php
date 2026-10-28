<?php
// admin/doorstep.php - Manage Doorstep Assistance Requests
require_once __DIR__ . '/auth.php';

$page_title = 'Doorstep Assistance Leads';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $req_id = (int) $_POST['request_id'];
    $new_status = trim($_POST['status'] ?? 'contacted');
    $stmt = $pdo->prepare("UPDATE doorstep_requests SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $req_id]);
    set_flash('success', "Doorstep lead #DS-{$req_id} updated to status '{$new_status}'.");
    header('Location: ' . BASE_URL . '/admin/doorstep.php');
    exit;
}

$stmt = $pdo->query("SELECT dr.*, s.service_name 
                     FROM doorstep_requests dr 
                     LEFT JOIN services s ON dr.service_id = s.id 
                     ORDER BY dr.id DESC");
$requests = $stmt->fetchAll();

include __DIR__ . '/header.php';
?>

<!-- Title Row -->
<div class="row" style="margin-bottom: 24px;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-navy); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-user-shield text-warning"></i> Doorstep Assistance Leads &amp; Dispatch
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.9rem;">
      In-person home visit requests from senior citizens and rural residents needing digital document support.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <a href="<?php echo BASE_URL; ?>/doorstep-booking.php" target="_blank" class="btn btn-default" style="font-weight: 600;">
      <i class="fas fa-external-link-alt"></i> View Public Booking Form
    </a>
  </div>
</div>

<div class="adm-card">
  <div class="adm-card-header">
    <h4><i class="fas fa-calendar-alt text-primary"></i> Citizen Appointment Queue</h4>
    <span class="badge" style="background: #e2e8f0; color: #334155;">
      Total: <?php echo count($requests); ?> Leads
    </span>
  </div>

  <div class="adm-card-body" style="padding: 0;">
    <?php if (!empty($requests)): ?>
      <div class="table-responsive">
        <table class="table table-hover table-striped" style="font-size: 0.88rem; margin-bottom: 0;">
          <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
            <tr>
              <th style="width: 70px;">#Lead</th>
              <th>Citizen Details</th>
              <th>Service Required</th>
              <th>Preferred Slot</th>
              <th>Address &amp; Pincode</th>
              <th>Status</th>
              <th style="text-align: right; width: 200px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($requests as $r): 
              $clean_phone = preg_replace('/[^0-9]/', '', $r['phone']);
            ?>
              <tr>
                <td><code>#DS-<?php echo $r['id']; ?></code></td>
                <td>
                  <strong><?php echo htmlspecialchars($r['citizen_name']); ?></strong><br>
                  <a href="tel:<?php echo htmlspecialchars($r['phone']); ?>" style="color: #0284c7;">
                    <i class="fas fa-phone-alt"></i> +91-<?php echo htmlspecialchars($r['phone']); ?>
                  </a>
                </td>
                <td>
                  <span class="badge" style="background: #eff6ff; color: #1e40af; font-size: 0.75rem;">
                    <?php echo htmlspecialchars($r['service_name'] ?? 'General Assistance'); ?>
                  </span>
                </td>
                <td>
                  <i class="fas fa-clock text-muted"></i> <?php echo htmlspecialchars($r['preferred_slot'] ?? 'Not specified'); ?>
                </td>
                <td style="max-width: 250px;">
                  <div style="line-height: 1.4; color: #334155;">
                    <?php echo htmlspecialchars($r['address']); ?><br>
                    <strong>PIN: <?php echo htmlspecialchars($r['pincode']); ?></strong>
                  </div>
                </td>
                <td>
                  <?php if ($r['status'] === 'new'): ?>
                    <span class="label label-danger" style="font-size: 0.74rem;">NEW</span>
                  <?php elseif ($r['status'] === 'contacted'): ?>
                    <span class="label label-warning" style="font-size: 0.74rem;">CONTACTED</span>
                  <?php elseif ($r['status'] === 'completed'): ?>
                    <span class="label label-success" style="font-size: 0.74rem;">COMPLETED</span>
                  <?php else: ?>
                    <span class="label label-default" style="font-size: 0.74rem;">CANCELLED</span>
                  <?php endif; ?>
                </td>
                <td style="text-align: right;">
                  <!-- WhatsApp Chat Button -->
                  <?php if (!empty($clean_phone)): ?>
                    <a href="https://wa.me/91<?php echo $clean_phone; ?>?text=Namaste%20<?php echo urlencode($r['citizen_name']); ?>,%20we%20have%20received%20your%20Doorstep%20Assistance%20booking." target="_blank" class="btn btn-success btn-xs" title="Chat on WhatsApp">
                      <i class="fab fa-whatsapp"></i>
                    </a>
                  <?php endif; ?>

                  <!-- Change Status Form -->
                  <form method="POST" action="<?php echo BASE_URL; ?>/admin/doorstep.php" style="display: inline-block;">
                    <input type="hidden" name="request_id" value="<?php echo $r['id']; ?>">
                    <select name="status" onchange="this.form.submit();" class="form-control input-sm" style="display: inline-block; width: 110px; height: 26px; padding: 2px 6px; font-size: 0.75rem;">
                      <option value="new" <?php echo ($r['status'] === 'new') ? 'selected' : ''; ?>>New</option>
                      <option value="contacted" <?php echo ($r['status'] === 'contacted') ? 'selected' : ''; ?>>Contacted</option>
                      <option value="completed" <?php echo ($r['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                      <option value="cancelled" <?php echo ($r['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <p class="text-muted text-center" style="padding: 40px 0;">No doorstep assistance leads logged yet.</p>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
