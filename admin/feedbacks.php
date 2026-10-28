<?php
// admin/feedbacks.php - Moderate Citizen Feedbacks & Ratings
require_once __DIR__ . '/auth.php';

$page_title = 'Citizen Feedbacks & Ratings Moderation';

// Handle Actions (Approve, Reject, Toggle Feature, Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $fb_id = (int) ($_POST['feedback_id'] ?? 0);
    $action = trim($_POST['action']);

    if ($fb_id > 0) {
        if ($action === 'approve') {
            $stmt = $pdo->prepare("UPDATE feedbacks SET status = 'approved' WHERE id = ?");
            $stmt->execute([$fb_id]);
            set_flash('success', "Feedback #FB-{$fb_id} has been APPROVED and is now live on the portal.");
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("UPDATE feedbacks SET status = 'rejected', is_featured = 0 WHERE id = ?");
            $stmt->execute([$fb_id]);
            set_flash('warning', "Feedback #FB-{$fb_id} has been REJECTED and will not be displayed publicly.");
        } elseif ($action === 'toggle_featured') {
            $stmt = $pdo->prepare("UPDATE feedbacks SET is_featured = (CASE WHEN is_featured = 1 THEN 0 ELSE 1 END), status = 'approved' WHERE id = ?");
            $stmt->execute([$fb_id]);
            set_flash('success', "Feedback #FB-{$fb_id} homepage featured status updated.");
        } elseif ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM feedbacks WHERE id = ?");
            $stmt->execute([$fb_id]);
            set_flash('success', "Feedback #FB-{$fb_id} deleted permanently.");
        }
    }

    header('Location: ' . BASE_URL . '/admin/feedbacks.php' . (isset($_GET['status']) ? '?status=' . urlencode($_GET['status']) : ''));
    exit;
}

// Filters & Search
$status_filter = trim($_GET['status'] ?? 'all');
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM feedbacks WHERE 1=1";
$params = [];

if ($status_filter === 'pending') {
    $sql .= " AND status = 'pending'";
} elseif ($status_filter === 'approved') {
    $sql .= " AND status = 'approved'";
} elseif ($status_filter === 'rejected') {
    $sql .= " AND status = 'rejected'";
} elseif ($status_filter === 'featured') {
    $sql .= " AND is_featured = 1 AND status = 'approved'";
}

if (!empty($search)) {
    $sql .= " AND (citizen_name LIKE ? OR phone LIKE ? OR email LIKE ? OR service_name LIKE ? OR city LIKE ? OR comment LIKE ?)";
    $term = "%{$search}%";
    $params = array_merge($params, [$term, $term, $term, $term, $term, $term]);
}

$sql .= " ORDER BY (CASE WHEN status = 'pending' THEN 0 ELSE 1 END), id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Metrics
$count_all = (int) $pdo->query("SELECT COUNT(*) FROM feedbacks")->fetchColumn();
$count_pending = (int) $pdo->query("SELECT COUNT(*) FROM feedbacks WHERE status = 'pending'")->fetchColumn();
$count_approved = (int) $pdo->query("SELECT COUNT(*) FROM feedbacks WHERE status = 'approved'")->fetchColumn();
$count_featured = (int) $pdo->query("SELECT COUNT(*) FROM feedbacks WHERE is_featured = 1 AND status = 'approved'")->fetchColumn();
$count_rejected = (int) $pdo->query("SELECT COUNT(*) FROM feedbacks WHERE status = 'rejected'")->fetchColumn();

include __DIR__ . '/header.php';
?>

<!-- Title Row -->
<div class="row" style="margin-bottom: 24px;">
  <div class="col-sm-8 col-xs-12">
    <h2 style="font-weight: 800; color: var(--adm-navy); margin: 0; font-size: 1.6rem;">
      <i class="fas fa-comments text-primary"></i> Citizen Feedbacks &amp; Testimonials
    </h2>
    <p class="text-muted" style="margin-top: 4px; font-size: 0.9rem;">
      Moderate verified citizen reviews, approve genuine ratings, and choose testimonials featured on the homepage.
    </p>
  </div>
  <div class="col-sm-4 col-xs-12 text-right">
    <a href="<?php echo BASE_URL; ?>/feedback.php" target="_blank" class="btn btn-default" style="font-weight: 600;">
      <i class="fas fa-external-link-alt"></i> View Public Feedback Page
    </a>
  </div>
</div>

<!-- Metrics Cards -->
<div class="row" style="margin-bottom: 20px;">
  <div class="col-xs-6 col-md-3">
    <div class="adm-card" style="padding: 16px; margin-bottom: 16px; border-left: 4px solid #f59e0b;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #b45309; text-transform: uppercase;">Pending Moderation</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
        <?php echo $count_pending; ?>
      </div>
      <div style="font-size: 0.75rem; color: #64748b;">Requires admin approval</div>
    </div>
  </div>

  <div class="col-xs-6 col-md-3">
    <div class="adm-card" style="padding: 16px; margin-bottom: 16px; border-left: 4px solid #10b981;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #047857; text-transform: uppercase;">Approved &amp; Live</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
        <?php echo $count_approved; ?>
      </div>
      <div style="font-size: 0.75rem; color: #64748b;">Visible to citizens</div>
    </div>
  </div>

  <div class="col-xs-6 col-md-3">
    <div class="adm-card" style="padding: 16px; margin-bottom: 16px; border-left: 4px solid #3b82f6;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #1d4ed8; text-transform: uppercase;">Featured on Home</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
        <?php echo $count_featured; ?>
      </div>
      <div style="font-size: 0.75rem; color: #64748b;">Home showcase reviews</div>
    </div>
  </div>

  <div class="col-xs-6 col-md-3">
    <div class="adm-card" style="padding: 16px; margin-bottom: 16px; border-left: 4px solid #64748b;">
      <div style="font-size: 0.78rem; font-weight: 700; color: #475569; text-transform: uppercase;">Total Submissions</div>
      <div style="font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-top: 4px;">
        <?php echo $count_all; ?>
      </div>
      <div style="font-size: 0.75rem; color: #64748b;">All time verified reviews</div>
    </div>
  </div>
</div>

<!-- Filter Tabs & Search -->
<div class="adm-card" style="margin-bottom: 20px; padding: 14px 18px;">
  <div class="row align-items-center">
    <div class="col-md-7 col-xs-12" style="margin-bottom: 8px;">
      <div class="btn-group" role="group">
        <a href="feedbacks.php?status=all" class="btn btn-sm <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-default'; ?>">
          All (<?php echo $count_all; ?>)
        </a>
        <a href="feedbacks.php?status=pending" class="btn btn-sm <?php echo $status_filter === 'pending' ? 'btn-warning' : 'btn-default'; ?>">
          <i class="fas fa-clock"></i> Pending (<?php echo $count_pending; ?>)
        </a>
        <a href="feedbacks.php?status=approved" class="btn btn-sm <?php echo $status_filter === 'approved' ? 'btn-success' : 'btn-default'; ?>">
          <i class="fas fa-check"></i> Approved (<?php echo $count_approved; ?>)
        </a>
        <a href="feedbacks.php?status=featured" class="btn btn-sm <?php echo $status_filter === 'featured' ? 'btn-info' : 'btn-default'; ?>">
          <i class="fas fa-star"></i> Featured (<?php echo $count_featured; ?>)
        </a>
        <a href="feedbacks.php?status=rejected" class="btn btn-sm <?php echo $status_filter === 'rejected' ? 'btn-danger' : 'btn-default'; ?>">
          Rejected (<?php echo $count_rejected; ?>)
        </a>
      </div>
    </div>

    <div class="col-md-5 col-xs-12 text-right">
      <form action="feedbacks.php" method="GET" class="form-inline" style="display: inline-block;">
        <?php if ($status_filter !== 'all'): ?>
          <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
        <?php endif; ?>
        <div class="input-group">
          <input type="text" name="search" class="form-control input-sm" placeholder="Search name, phone, city..." value="<?php echo htmlspecialchars($search); ?>">
          <span class="input-group-btn">
            <button class="btn btn-default btn-sm" type="submit"><i class="fas fa-search"></i></button>
            <?php if (!empty($search)): ?>
              <a href="feedbacks.php?status=<?php echo htmlspecialchars($status_filter); ?>" class="btn btn-default btn-sm" title="Clear Search"><i class="fas fa-times"></i></a>
            <?php endif; ?>
          </span>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Feedbacks Table -->
<div class="adm-card">
  <div class="adm-card-header">
    <h4><i class="fas fa-list text-primary"></i> Citizen Feedback List</h4>
    <span class="badge" style="background: #e2e8f0; color: #334155;">
      Showing <?php echo count($feedbacks); ?> Records
    </span>
  </div>

  <div class="adm-card-body" style="padding: 0;">
    <?php if (!empty($feedbacks)): ?>
      <div class="table-responsive">
        <table class="table table-hover table-striped" style="font-size: 0.88rem; margin-bottom: 0;">
          <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
            <tr>
              <th style="width: 60px;">#ID</th>
              <th style="width: 220px;">Citizen Info</th>
              <th style="width: 200px;">Service Availed</th>
              <th style="width: 130px;">Rating</th>
              <th>Review &amp; Feedback</th>
              <th style="width: 110px;">Status</th>
              <th style="width: 180px; text-align: right;">Moderation Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($feedbacks as $fb): 
                $status = $fb['status'];
                $isPending = ($status === 'pending');
                $isApproved = ($status === 'approved');
                $isRejected = ($status === 'rejected');
                $isFeatured = (bool) $fb['is_featured'];
                $rating = max(1, min(5, (int)$fb['rating']));
            ?>
              <tr style="<?php echo $isPending ? 'background-color: #fffbeb;' : ''; ?>">
                <td>
                  <strong>#FB-<?php echo $fb['id']; ?></strong>
                  <div style="font-size: 0.72rem; color: #94a3b8;">
                    <?php echo date('d M Y', strtotime($fb['created_at'])); ?>
                  </div>
                </td>

                <td>
                  <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                    <?php echo htmlspecialchars($fb['citizen_name']); ?>
                  </div>
                  <div style="font-size: 0.8rem; color: #475569;">
                    <i class="fas fa-phone-alt text-muted" style="font-size: 0.75rem;"></i> <?php echo htmlspecialchars($fb['phone']); ?>
                  </div>
                  <?php if (!empty($fb['city']) || !empty($fb['state'])): ?>
                    <div style="font-size: 0.76rem; color: #64748b;">
                      <i class="fas fa-map-marker-alt text-muted" style="font-size: 0.75rem;"></i> 
                      <?php echo htmlspecialchars($fb['city'] ?? ''); ?><?php echo (!empty($fb['city']) && !empty($fb['state'])) ? ', ' : ''; ?><?php echo htmlspecialchars($fb['state'] ?? ''); ?>
                    </div>
                  <?php endif; ?>
                  <?php if (!empty($fb['occupation'])): ?>
                    <div style="font-size: 0.72rem; color: #2563eb; font-weight: 600;">
                      <i class="fas fa-user-tag" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($fb['occupation']); ?>
                    </div>
                  <?php endif; ?>
                </td>

                <td>
                  <span class="badge" style="background: #eff6ff; color: #1e3a8a; border: 1px solid #bfdbfe; font-size: 0.78rem; white-space: normal; text-align: left; line-height: 1.3;">
                    <?php echo htmlspecialchars($fb['service_name']); ?>
                  </span>
                </td>

                <td>
                  <div style="color: #f59e0b; font-size: 0.95rem; letter-spacing: 1px;">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                      <i class="fas fa-star<?php echo $s <= $rating ? '' : ' text-muted'; ?>" style="<?php echo $s <= $rating ? '' : 'color: #cbd5e1;'; ?>"></i>
                    <?php endfor; ?>
                  </div>
                  <div style="font-size: 0.75rem; color: #64748b; font-weight: 600; margin-top: 2px;">
                    <?php echo $rating; ?> / 5 Stars
                  </div>
                </td>

                <td>
                  <?php if (!empty($fb['title'])): ?>
                    <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px; font-size: 0.92rem;">
                      "<?php echo htmlspecialchars($fb['title']); ?>"
                    </div>
                  <?php endif; ?>
                  <div style="color: #334155; line-height: 1.5; font-size: 0.85rem;">
                    <?php echo nl2br(htmlspecialchars($fb['comment'])); ?>
                  </div>
                </td>

                <td>
                  <?php if ($isApproved): ?>
                    <span class="label label-success" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                      <i class="fas fa-check-circle"></i> Approved
                    </span>
                  <?php elseif ($isPending): ?>
                    <span class="label label-warning" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; display: inline-block; background-color: #f59e0b;">
                      <i class="fas fa-clock"></i> Pending
                    </span>
                  <?php else: ?>
                    <span class="label label-danger" style="font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; display: inline-block;">
                      <i class="fas fa-times-circle"></i> Rejected
                    </span>
                  <?php endif; ?>

                  <div style="margin-top: 6px;">
                    <?php if ($isFeatured): ?>
                      <span class="badge" style="background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; font-size: 0.72rem;">
                        <i class="fas fa-star"></i> Featured
                      </span>
                    <?php endif; ?>
                  </div>
                </td>

                <td style="text-align: right; vertical-align: middle;">
                  <div class="btn-group" role="group">
                    <?php if (!$isApproved): ?>
                      <form action="feedbacks.php" method="POST" style="display: inline-block; margin-right: 3px;">
                        <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success btn-xs" title="Approve &amp; Publish">
                          <i class="fas fa-check"></i> Approve
                        </button>
                      </form>
                    <?php endif; ?>

                    <?php if ($isApproved): ?>
                      <!-- Toggle Featured -->
                      <form action="feedbacks.php" method="POST" style="display: inline-block; margin-right: 3px;">
                        <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                        <input type="hidden" name="action" value="toggle_featured">
                        <button type="submit" class="btn btn-xs <?php echo $isFeatured ? 'btn-info' : 'btn-default'; ?>" title="<?php echo $isFeatured ? 'Remove from Homepage Spotlight' : 'Spotlight on Homepage'; ?>">
                          <i class="fas fa-star"></i> <?php echo $isFeatured ? 'Unfeature' : 'Feature'; ?>
                        </button>
                      </form>
                    <?php endif; ?>

                    <?php if (!$isRejected): ?>
                      <form action="feedbacks.php" method="POST" style="display: inline-block; margin-right: 3px;">
                        <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                        <input type="hidden" name="action" value="reject">
                        <button type="submit" class="btn btn-warning btn-xs" title="Reject / Hide Review" onclick="return confirm('Are you sure you want to reject this feedback? It will be hidden from the public.');">
                          <i class="fas fa-ban"></i> Reject
                        </button>
                      </form>
                    <?php endif; ?>

                    <form action="feedbacks.php" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to permanently delete feedback #FB-<?php echo $fb['id']; ?>? This cannot be undone.');">
                      <input type="hidden" name="feedback_id" value="<?php echo $fb['id']; ?>">
                      <input type="hidden" name="action" value="delete">
                      <button type="submit" class="btn btn-danger btn-xs" title="Delete Feedback">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div style="padding: 50px 20px; text-align: center; color: #64748b;">
        <i class="fas fa-comments" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 14px;"></i>
        <h4 style="color: #334155; font-weight: 700;">No citizen feedback found</h4>
        <p style="font-size: 0.9rem; max-width: 420px; margin: 0 auto;">
          <?php if (!empty($search)): ?>
            No reviews match your search query. Try clearing the search box.
          <?php else: ?>
            No reviews found under the '<?php echo htmlspecialchars($status_filter); ?>' filter.
          <?php endif; ?>
        </p>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
