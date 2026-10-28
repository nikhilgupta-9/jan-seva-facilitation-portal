<?php
// admin/messages.php - Manage Citizen Inquiries & Feedback
require_once __DIR__ . '/auth.php';

$page_title = 'Citizen Messages & Inquiries';

// Handle Status Updates or Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $msg_id = (int) $_POST['message_id'];
        $new_status = trim($_POST['status'] ?? 'read');
        $stmt = $pdo->prepare("UPDATE contact_messages SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $msg_id]);
        set_flash('success', "Message #{$msg_id} marked as '{$new_status}'.");
        header('Location: ' . BASE_URL . '/admin/messages.php');
        exit;
    }
    if (isset($_POST['action']) && $_POST['action'] === 'delete_message') {
        $msg_id = (int) $_POST['message_id'];
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$msg_id]);
        set_flash('success', "Message #{$msg_id} deleted successfully.");
        header('Location: ' . BASE_URL . '/admin/messages.php');
        exit;
    }
}

// Filters
$status_filter = trim($_GET['status'] ?? 'all');
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if ($status_filter !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)";
    $term = "%{$search}%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

// Counts
$total_all = (int) $pdo->query("SELECT COUNT(id) FROM contact_messages")->fetchColumn();
$total_new = (int) $pdo->query("SELECT COUNT(id) FROM contact_messages WHERE status = 'new'")->fetchColumn();
$total_read = (int) $pdo->query("SELECT COUNT(id) FROM contact_messages WHERE status = 'read'")->fetchColumn();
$total_replied = (int) $pdo->query("SELECT COUNT(id) FROM contact_messages WHERE status = 'replied'")->fetchColumn();

include __DIR__ . '/header.php';
?>

<!-- Title & Metrics Row -->
<div class="row" style="margin-bottom: 24px;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-navy); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-envelope-open-text text-primary"></i> Citizen Contact Messages &amp; Inquiries
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.9rem;">
      Review inquiries submitted through the public Contact Us portal, provide direct WhatsApp assistance, and track grievance resolution.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <a href="<?php echo BASE_URL; ?>/contact.php" target="_blank" class="btn btn-default" style="font-weight: 600;">
      <i class="fas fa-external-link-alt"></i> View Public Contact Form
    </a>
  </div>
</div>

<!-- Stat Row -->
<div class="row">
  <div class="col-sm-3 col-xs-6">
    <div class="stat-card">
      <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
        <i class="fas fa-inbox"></i>
      </div>
      <div>
        <div class="stat-label">Total Received</div>
        <div class="stat-value"><?php echo $total_all; ?></div>
      </div>
    </div>
  </div>

  <div class="col-sm-3 col-xs-6">
    <div class="stat-card">
      <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
        <i class="fas fa-envelope"></i>
      </div>
      <div>
        <div class="stat-label">New Unread</div>
        <div class="stat-value text-danger"><?php echo $total_new; ?></div>
      </div>
    </div>
  </div>

  <div class="col-sm-3 col-xs-6">
    <div class="stat-card">
      <div class="stat-icon" style="background: #fef3c7; color: #d97706;">
        <i class="fas fa-eye"></i>
      </div>
      <div>
        <div class="stat-label">Reviewed</div>
        <div class="stat-value" style="color: #d97706;"><?php echo $total_read; ?></div>
      </div>
    </div>
  </div>

  <div class="col-sm-3 col-xs-6">
    <div class="stat-card">
      <div class="stat-icon" style="background: #dcfce7; color: #16a34a;">
        <i class="fas fa-check-double"></i>
      </div>
      <div>
        <div class="stat-label">Replied / Closed</div>
        <div class="stat-value text-success"><?php echo $total_replied; ?></div>
      </div>
    </div>
  </div>
</div>

<!-- Filter and Search Bar Card -->
<div class="adm-card">
  <div class="adm-card-header">
    <!-- Filter Tabs -->
    <div class="btn-group" role="group">
      <a href="<?php echo BASE_URL; ?>/admin/messages.php?status=all" class="btn btn-sm <?php echo ($status_filter === 'all') ? 'btn-primary' : 'btn-default'; ?>">
        All (<?php echo $total_all; ?>)
      </a>
      <a href="<?php echo BASE_URL; ?>/admin/messages.php?status=new" class="btn btn-sm <?php echo ($status_filter === 'new') ? 'btn-danger' : 'btn-default'; ?>">
        <i class="fas fa-circle text-danger" style="font-size: 0.6rem;"></i> New (<?php echo $total_new; ?>)
      </a>
      <a href="<?php echo BASE_URL; ?>/admin/messages.php?status=read" class="btn btn-sm <?php echo ($status_filter === 'read') ? 'btn-warning' : 'btn-default'; ?>">
        Read (<?php echo $total_read; ?>)
      </a>
      <a href="<?php echo BASE_URL; ?>/admin/messages.php?status=replied" class="btn btn-sm <?php echo ($status_filter === 'replied') ? 'btn-success' : 'btn-default'; ?>">
        Replied (<?php echo $total_replied; ?>)
      </a>
    </div>

    <!-- Search Form -->
    <form method="GET" action="<?php echo BASE_URL; ?>/admin/messages.php" class="form-inline" style="margin-left: auto;">
      <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
      <div class="input-group">
        <input type="text" name="search" class="form-control" placeholder="Search name, phone, message..." value="<?php echo htmlspecialchars($search); ?>" style="height: 34px; font-size: 0.85rem;">
        <span class="input-group-btn">
          <button type="submit" class="btn btn-default btn-sm" style="height: 34px;">
            <i class="fas fa-search"></i>
          </button>
        </span>
      </div>
    </form>
  </div>

  <div class="adm-card-body" style="padding: 0;">
    <?php if (empty($messages)): ?>
      <div class="text-center" style="padding: 50px 20px;">
        <div style="font-size: 3rem; color: #cbd5e1; margin-bottom: 15px;">
          <i class="fas fa-inbox"></i>
        </div>
        <h4 style="color: #64748b; font-weight: 700;">No Messages Found</h4>
        <p class="text-muted">No citizen inquiries match your filter criteria.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover table-striped" style="font-size: 0.88rem; margin: 0;">
          <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
            <tr>
              <th style="width: 60px;">#ID</th>
              <th>Citizen Details</th>
              <th>Service / Subject</th>
              <th>Message Content</th>
              <th>Status</th>
              <th>Received At</th>
              <th style="text-align: right; width: 180px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($messages as $m): 
              $clean_phone = preg_replace('/[^0-9]/', '', $m['phone']);
            ?>
              <tr>
                <td><code>#<?php echo $m['id']; ?></code></td>
                <td>
                  <strong><?php echo htmlspecialchars($m['name']); ?></strong><br>
                  <a href="tel:<?php echo htmlspecialchars($m['phone']); ?>" style="color: #0284c7;">
                    <i class="fas fa-phone-alt"></i> +91-<?php echo htmlspecialchars($m['phone']); ?>
                  </a><br>
                  <?php if (!empty($m['email'])): ?>
                    <small class="text-muted"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($m['email']); ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($m['service_interest'])): ?>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; margin-bottom: 3px;">
                      <?php echo htmlspecialchars($m['service_interest']); ?>
                    </span><br>
                  <?php endif; ?>
                  <strong><?php echo htmlspecialchars($m['subject'] ?: 'General Citizen Inquiry'); ?></strong>
                </td>
                <td style="max-width: 320px;">
                  <div style="line-height: 1.4; color: #334155;">
                    <?php echo nl2br(htmlspecialchars($m['message'])); ?>
                  </div>
                </td>
                <td>
                  <?php if ($m['status'] === 'new'): ?>
                    <span class="label label-danger" style="font-size: 0.75rem; padding: 3px 8px;">New</span>
                  <?php elseif ($m['status'] === 'read'): ?>
                    <span class="label label-warning" style="font-size: 0.75rem; padding: 3px 8px;">Read</span>
                  <?php else: ?>
                    <span class="label label-success" style="font-size: 0.75rem; padding: 3px 8px;">Replied</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span style="font-size: 0.8rem; color: #64748b;">
                    <?php echo date('d M Y, h:i A', strtotime($m['created_at'])); ?>
                  </span>
                </td>
                <td style="text-align: right;">
                  <!-- Direct WhatsApp Contact -->
                  <?php if (!empty($clean_phone)): ?>
                    <a href="https://wa.me/91<?php echo $clean_phone; ?>?text=Namaste%20<?php echo urlencode($m['name']); ?>,%20we%20received%20your%20inquiry%20on%20Jan%20Seva%20Portal." target="_blank" class="btn btn-success btn-xs" title="Chat on WhatsApp">
                      <i class="fab fa-whatsapp"></i>
                    </a>
                  <?php endif; ?>

                  <!-- Change Status Dropdown Form -->
                  <form method="POST" action="<?php echo BASE_URL; ?>/admin/messages.php" style="display: inline-block;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="message_id" value="<?php echo $m['id']; ?>">
                    <?php if ($m['status'] === 'new'): ?>
                      <input type="hidden" name="status" value="read">
                      <button type="submit" class="btn btn-warning btn-xs" title="Mark as Read">
                        <i class="fas fa-check"></i> Read
                      </button>
                    <?php elseif ($m['status'] === 'read'): ?>
                      <input type="hidden" name="status" value="replied">
                      <button type="submit" class="btn btn-primary btn-xs" title="Mark as Replied">
                        <i class="fas fa-reply"></i> Replied
                      </button>
                    <?php endif; ?>
                  </form>

                  <!-- Delete Message -->
                  <form method="POST" action="<?php echo BASE_URL; ?>/admin/messages.php" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete message #<?php echo $m['id']; ?>?');">
                    <input type="hidden" name="action" value="delete_message">
                    <input type="hidden" name="message_id" value="<?php echo $m['id']; ?>">
                    <button type="submit" class="btn btn-default btn-xs text-danger" title="Delete">
                      <i class="fas fa-trash-alt text-danger"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
