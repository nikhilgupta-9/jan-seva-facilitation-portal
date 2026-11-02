<?php
/**
 * Fire-and-forget worker: sends the Ayushman confirmation email outside the
 * request/response cycle. Invoked as a detached background process from
 * ayushman-helpdesk.php (see ayushman_dispatch_email_async()) because
 * XAMPP's Apache runs mod_php, not php-fpm, so fastcgi_finish_request()
 * isn't available there and flush() alone won't release the browser before
 * the SMTP round-trip finishes. Running this as its own OS process is what
 * actually decouples "citizen sees the card" from "SMTP send completes".
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only');
}

$payloadFile = $argv[1] ?? '';
if (!$payloadFile || !is_file($payloadFile)) {
    exit(1);
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/mailer.php';

$payload = json_decode(file_get_contents($payloadFile), true);
@unlink($payloadFile);

if (!is_array($payload)) {
    exit(1);
}

try {
    send_ayushman_confirmation_email($payload);
} catch (Throwable $e) {
    error_log('Ayushman async confirmation email failed: ' . $e->getMessage());
}
