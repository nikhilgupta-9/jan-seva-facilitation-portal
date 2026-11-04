<?php
// cron/send_ayushman_emails.php - Dedicated Cron Worker for Ayushman Confirmation Emails
// Can be executed via CLI: php cron/send_ayushman_emails.php
// Or via Web/Curl: http://localhost/gp/cron/send_ayushman_emails.php?key=SECURE_CRON_KEY

// Set execution limits
@set_time_limit(180);
@ini_set('memory_limit', '128M');

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/utils/mailer.php';

// Web authorization check (CLI is always permitted)
$isCli = (php_sapi_name() === 'cli');
if (!$isCli) {
    header('Content-Type: application/json; charset=utf-8');
    $cronKey = env('CRON_SECRET_KEY') ?: 'ayushman_cron_secret_2026';
    $providedKey = $_GET['key'] ?? '';
    // Allow localhost without key, or require key from remote
    $isLocal = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1', 'localhost']);
    if (!$isLocal && $providedKey !== $cronKey) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized cron access']);
        exit;
    }
}

// Ensure single instance execution using a file lock
$lockFile = '/tmp/ayushman_email_cron.lock';
$lockFp = @fopen($lockFile, 'c');
if ($lockFp && !@flock($lockFp, LOCK_EX | LOCK_NB)) {
    $msg = 'Cron worker already active; skipping concurrent run.';
    if ($isCli) {
        echo "[CRON] {$msg}\n";
    } else {
        echo json_encode(['success' => false, 'message' => $msg]);
    }
    exit;
}

$processed = 0;
$successCount = 0;
$failureCount = 0;
$results = [];

try {
    if (!isset($pdo)) {
        throw new Exception("Database connection not established.");
    }

    // Pick up to 15 pending emails (attempts < 5)
    $stmt = $pdo->prepare("SELECT * FROM ayushman_applications 
                           WHERE email_sent = 0 
                             AND (email_attempts IS NULL OR email_attempts < 5) 
                           ORDER BY id ASC LIMIT 15");
    $stmt->execute();
    $pending = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($pending as $row) {
        $processed++;
        $appId = (int) $row['id'];
        $appNo = $row['application_no'];
        $email = trim($row['email'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Invalid email — mark failed so it doesn't loop forever
            $failStmt = $pdo->prepare("UPDATE ayushman_applications 
                                       SET email_attempts = 5, email_error = 'Invalid email address provided' 
                                       WHERE id = ?");
            $failStmt->execute([$appId]);
            $failureCount++;
            $results[] = ["id" => $appId, "app_no" => $appNo, "status" => "skipped_invalid_email"];
            continue;
        }

        $emailPayload = [
            'application_no' => $appNo,
            'card_id'        => $row['card_id'],
            'full_name'      => $row['full_name'],
            'email'          => $email,
            'mobile'         => $row['mobile'],
            'abha_number'    => $row['abha_number'],
            'aadhaar_last4'  => $row['aadhaar_last4']
        ];

        try {
            $sent = send_ayushman_confirmation_email($emailPayload);
            if ($sent) {
                $upStmt = $pdo->prepare("UPDATE ayushman_applications 
                                         SET email_sent = 1, email_sent_at = NOW(), email_error = NULL 
                                         WHERE id = ?");
                $upStmt->execute([$appId]);
                $successCount++;
                $results[] = ["id" => $appId, "app_no" => $appNo, "status" => "sent"];
            } else {
                throw new Exception("Mailer returned false (transport failed)");
            }
        } catch (Throwable $e) {
            $errMsg = substr($e->getMessage(), 0, 255);
            $errStmt = $pdo->prepare("UPDATE ayushman_applications 
                                      SET email_attempts = COALESCE(email_attempts, 0) + 1, email_error = ? 
                                      WHERE id = ?");
            $errStmt->execute([$errMsg, $appId]);
            $failureCount++;
            $results[] = ["id" => $appId, "app_no" => $appNo, "status" => "failed", "error" => $errMsg];
        }

        // Brief delay between outbound SMTP sends to respect mail provider rate limits
        usleep(100000); // 100ms
    }

} catch (Throwable $e) {
    $errMsg = "Fatal cron error: " . $e->getMessage();
    error_log($errMsg);
    if ($isCli) {
        echo "[ERROR] {$errMsg}\n";
    } else {
        echo json_encode(['success' => false, 'error' => $errMsg]);
    }
} finally {
    if ($lockFp) {
        @flock($lockFp, LOCK_UN);
        @fclose($lockFp);
    }
}

$summary = [
    'success' => true,
    'timestamp' => date('Y-m-d H:i:s'),
    'processed' => $processed,
    'sent' => $successCount,
    'failed' => $failureCount,
    'details' => $results
];

if ($isCli) {
    echo "[CRON " . date('Y-m-d H:i:s') . "] Processed: {$processed}, Sent: {$successCount}, Failed: {$failureCount}\n";
} else {
    echo json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
