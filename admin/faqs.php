<?php
// admin/faqs.php - Manage Homepage FAQ Accordion
require_once __DIR__ . '/auth.php';

$page_title = 'Homepage FAQs';
$edit_id = isset($_GET['edit']) ? (int) $_GET['edit'] : 0;

// Handle Create / Update
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['action'] ?? '') === 'save_faq') {
    $id = (int) ($_POST['id'] ?? 0);
    $question_en = trim($_POST['question_en'] ?? '');
    $question_hi = trim($_POST['question_hi'] ?? '');
    $answer_en = trim($_POST['answer_en'] ?? '');
    $answer_hi = trim($_POST['answer_hi'] ?? '');
    $sort_order = (int) ($_POST['sort_order'] ?? 10);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($question_en === '' || $answer_en === '') {
        set_flash('error', 'Question and Answer (English) are required.');
        header('Location: ' . BASE_URL . '/admin/faqs.php');
        exit;
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE faqs SET question_en=?, question_hi=?, answer_en=?, answer_hi=?, sort_order=?, is_active=? WHERE id=?");
        $stmt->execute([$question_en, $question_hi, $answer_en, $answer_hi, $sort_order, $is_active, $id]);
        set_flash('success', 'FAQ updated successfully.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO faqs (question_en, question_hi, answer_en, answer_hi, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$question_en, $question_hi, $answer_en, $answer_hi, $sort_order, $is_active]);
        set_flash('success', 'New FAQ published to the homepage.');
    }

    header('Location: ' . BASE_URL . '/admin/faqs.php');
    exit;
}

// Handle quick toggle / delete
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && isset($_POST['quick_action'])) {
    $id = (int) ($_POST['faq_id'] ?? 0);
    if ($_POST['quick_action'] === 'toggle_status' && $id > 0) {
        $pdo->prepare("UPDATE faqs SET is_active = NOT is_active WHERE id = ?")->execute([$id]);
        set_flash('success', 'Status toggled.');
    } elseif ($_POST['quick_action'] === 'delete' && $id > 0) {
        $pdo->prepare("DELETE FROM faqs WHERE id = ?")->execute([$id]);
        set_flash('success', 'FAQ deleted.');
    }
    header('Location: ' . BASE_URL . '/admin/faqs.php');
    exit;
}

// Fetch record being edited
$editing = null;
if ($edit_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM faqs WHERE id = ?");
    $stmt->execute([$edit_id]);
    $editing = $stmt->fetch(PDO::FETCH_ASSOC);
}

$faqs = $pdo->query("SELECT * FROM faqs ORDER BY sort_order ASC, id ASC")->fetchAll(PDO::FETCH_ASSOC);
$active_count = (int) $pdo->query("SELECT COUNT(*) FROM faqs WHERE is_active = 1")->fetchColumn();

include __DIR__ . '/header.php';
?>

<style>
  .fq-label { font-weight: 700; color: #1e293b; font-size: 0.86rem; margin-bottom: 6px; display: block; }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
  <div>
    <h2 class="fw-bold mb-1" style="color: var(--adm-blue-dark); font-size: 1.5rem;">
      <i class="fas fa-question-circle text-warning"></i> Homepage FAQs
    </h2>
    <p class="text-muted mb-0" style="font-size: 0.9rem; max-width: 640px;">
      Manage the "Frequently Asked Questions" accordion shown on the public homepage. Drag order via Sort Order; lower shows first.
    </p>
  </div>
  <a href="<?php echo BASE_URL; ?>/index.php#faq" target="_blank" class="btn btn-primary fw-bold flex-shrink-0">
    <i class="fas fa-eye"></i> View on Homepage
  </a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4 col-sm-6">
    <div class="stat-card mb-0">
      <div class="stat-icon" style="background: var(--adm-blue-tint); color: var(--adm-blue-dark);"><i class="fas fa-list-ol"></i></div>
      <div><div class="stat-label">Total FAQs</div><div class="stat-value" style="color: var(--adm-blue-dark);"><?php echo count($faqs); ?></div></div>
    </div>
  </div>
  <div class="col-md-4 col-sm-6">
    <div class="stat-card mb-0">
      <div class="stat-icon" style="background: #dcfce7; color: #16a34a;"><i class="fas fa-check-circle"></i></div>
      <div><div class="stat-label">Live on Homepage</div><div class="stat-value" style="color: #16a34a;"><?php echo $active_count; ?></div></div>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Add / Edit Form -->
  <div class="col-lg-5">
    <div class="adm-card">
      <div class="adm-card-header" style="border-left: 4px solid var(--adm-saffron);">
        <h4 style="margin:0; font-weight:800; color: var(--adm-blue-dark); font-size: 1rem;">
          <i class="fas <?php echo $editing ? 'fa-edit' : 'fa-plus-circle'; ?>"></i>
          <?php echo $editing ? 'Edit FAQ' : 'Add New FAQ'; ?>
        </h4>
      </div>
      <div class="adm-card-body">
        <form method="POST" action="<?php echo BASE_URL; ?>/admin/faqs.php">
          <input type="hidden" name="action" value="save_faq">
          <input type="hidden" name="id" value="<?php echo $editing ? (int)$editing['id'] : 0; ?>">

          <div class="mb-3">
            <label class="fq-label">Question (English) <span style="color:#dc2626;">*</span></label>
            <input type="text" name="question_en" class="form-control" required
                   value="<?php echo htmlspecialchars($editing['question_en'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="fq-label">Question (Hindi)</label>
            <input type="text" name="question_hi" class="form-control"
                   value="<?php echo htmlspecialchars($editing['question_hi'] ?? ''); ?>">
          </div>
          <div class="mb-3">
            <label class="fq-label">Answer (English) <span style="color:#dc2626;">*</span></label>
            <textarea name="answer_en" rows="4" class="form-control" required><?php echo htmlspecialchars($editing['answer_en'] ?? ''); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="fq-label">Answer (Hindi)</label>
            <textarea name="answer_hi" rows="4" class="form-control"><?php echo htmlspecialchars($editing['answer_hi'] ?? ''); ?></textarea>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="fq-label">Sort Order</label>
              <input type="number" name="sort_order" class="form-control" value="<?php echo (int)($editing['sort_order'] ?? 10); ?>">
            </div>
            <div class="col-6 d-flex align-items-end">
              <label style="font-weight:700; cursor:pointer; display:flex; align-items:center; gap:8px; margin-bottom: 10px;">
                <input type="checkbox" name="is_active" value="1" style="width:18px;height:18px;"
                       <?php echo (!$editing || $editing['is_active'] == 1) ? 'checked' : ''; ?>>
                Active
              </label>
            </div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-gov-saffron fw-bold flex-grow-1">
              <i class="fas fa-save"></i> <?php echo $editing ? 'Save Changes' : 'Publish FAQ'; ?>
            </button>
            <?php if ($editing): ?>
              <a href="<?php echo BASE_URL; ?>/admin/faqs.php" class="btn btn-default">Cancel</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- List -->
  <div class="col-lg-7">
    <div class="adm-card">
      <div class="adm-card-header">
        <h4 style="margin:0; font-weight:800; font-size: 1rem;"><i class="fas fa-list-alt text-primary"></i> All FAQs (<?php echo count($faqs); ?>)</h4>
      </div>
      <div class="adm-card-body" style="padding: 0;">
        <div class="table-responsive">
          <table class="table table-hover table-striped" style="font-size: 0.86rem; margin-bottom: 0;">
            <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
              <tr>
                <th style="width: 50px;">#</th>
                <th>Question</th>
                <th style="width: 80px; text-align:center;">Status</th>
                <th style="width: 90px; text-align:right;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($faqs)): ?>
                <tr><td colspan="4" class="text-center py-4 text-muted">No FAQs yet. Add one on the left.</td></tr>
              <?php else: ?>
                <?php foreach ($faqs as $f): ?>
                  <tr>
                    <td class="text-muted"><?php echo (int)$f['sort_order']; ?></td>
                    <td>
                      <div style="font-weight: 600; color: #1e293b;"><?php echo htmlspecialchars($f['question_en']); ?></div>
                      <div class="text-muted text-truncate" style="font-size: 0.78rem; max-width: 420px;"><?php echo htmlspecialchars($f['answer_en']); ?></div>
                    </td>
                    <td style="text-align:center;">
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="quick_action" value="toggle_status">
                        <input type="hidden" name="faq_id" value="<?php echo $f['id']; ?>">
                        <button type="submit" class="btn btn-xs <?php echo $f['is_active'] ? 'btn-success' : 'btn-default'; ?>" style="border-radius:12px;">
                          <?php echo $f['is_active'] ? 'Live' : 'Hidden'; ?>
                        </button>
                      </form>
                    </td>
                    <td style="text-align:right; white-space:nowrap;">
                      <a href="?edit=<?php echo $f['id']; ?>" class="btn btn-xs btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                      <form method="POST" class="d-inline" onsubmit="return confirm('Delete this FAQ?');">
                        <input type="hidden" name="quick_action" value="delete">
                        <input type="hidden" name="faq_id" value="<?php echo $f['id']; ?>">
                        <button type="submit" class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
