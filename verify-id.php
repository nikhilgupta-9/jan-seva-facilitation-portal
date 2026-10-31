<?php
// verify-id.php - Isolated, locked verification gate for the Digital Health Smart ID QR.
//
// Deliberately NOT part of the main site (no includes/header.php / footer.php,
// no nav, no branding) and NOT inside admin/. It exists only as the target of
// the QR's encrypted link, and it never discloses any citizen data to a plain
// visitor — scanning this QR with an ordinary camera/QR app lands here and
// sees a generic locked screen, nothing else, regardless of whether the token
// is valid, tampered, or expired (the response is intentionally identical in
// every case so this page can't be used to probe/enumerate tokens).
//
// The only thing this page can do is hand an ALREADY-authenticated admin
// (an existing admin session, not created here) a link into the real admin
// scanner, which is the sole place the encrypted payload actually gets
// decrypted and matched against the live database record.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/config.php';

// Cards now encode a short Card ID (?id=...); the old encrypted ?token=
// form is still accepted here purely to pass through to the admin scanner
// for any cards printed/saved before this change.
$cardId = trim($_GET['id'] ?? '');
$token = trim($_GET['token'] ?? '');
$isAdmin = !empty($_SESSION['admin_logged_in']) || !empty($_SESSION['admin_user']) || !empty($_SESSION['admin_id']);

header('X-Robots-Tag: noindex, nofollow');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Secured Health ID Record</title>
<style>
  * { box-sizing: border-box; }
  body {
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #0f172a;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
    padding: 20px;
  }
  .lock-card {
    background: #ffffff;
    border-radius: 16px;
    max-width: 380px;
    width: 100%;
    padding: 36px 28px;
    text-align: center;
    box-shadow: 0 20px 60px rgba(0,0,0,0.4);
  }
  .lock-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #1e293b;
    color: #fbbf24;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 18px;
    font-size: 28px;
  }
  h1 {
    font-size: 1.1rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0 0 10px;
  }
  p {
    font-size: 0.86rem;
    color: #64748b;
    line-height: 1.55;
    margin: 0;
  }
  .badge {
    display: inline-block;
    margin-top: 16px;
    background: #f1f5f9;
    color: #475569;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.3px;
    padding: 5px 12px;
    border-radius: 20px;
  }
  .admin-link {
    display: inline-block;
    margin-top: 20px;
    background: #059669;
    color: #ffffff;
    text-decoration: none;
    font-weight: 700;
    font-size: 0.86rem;
    padding: 11px 20px;
    border-radius: 8px;
  }
</style>
</head>
<body>
  <div class="lock-card">
    <div class="lock-icon">&#128274;</div>
    <h1>Secured Health ID Record</h1>
    <p>This record is encrypted and only readable by an authorized administrative Scanner. No information is shown here.</p>
    <span class="badge">AES-256 PROTECTED</span>

    <?php if ($isAdmin && !empty($cardId)): ?>
      <div>
        <a class="admin-link" href="<?php echo htmlspecialchars(BASE_URL . '/admin/ayushman-scan-detail.php?id=' . urlencode($cardId)); ?>">
          Open in Admin Scanner
        </a>
      </div>
    <?php elseif ($isAdmin && !empty($token)): ?>
      <div>
        <a class="admin-link" href="<?php echo htmlspecialchars(BASE_URL . '/admin/ayushman-scanner.php?token=' . urlencode($token)); ?>">
          Open in Admin Scanner
        </a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
