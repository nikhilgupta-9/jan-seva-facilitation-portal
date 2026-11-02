<?php
/**
 * Worker endpoint hit by ayushman_fire_and_forget_ping() in mailer.php.
 * The caller writes the request and closes the socket immediately without
 * reading a response, so this script runs entirely outside the citizen's
 * request lifecycle — whatever it does here has no bearing on how long the
 * submit took.
 *
 * The token is a random 32-char hex filename component (128 bits of
 * entropy) written to a temp file right before the ping, so it is
 * effectively unguessable; that's the only access control this endpoint
 * needs, matching the trust model of the same temp-file handoff used by
 * the CLI fallback path.
 */

$token = $_GET['token'] ?? '';
if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
    http_response_code(400);
    exit;
}

$tmpFile = sys_get_temp_dir() . '/ayushman_mail_' . $token . '.json';
if (!is_file($tmpFile)) {
    http_response_code(404);
    exit;
}

$payload = json_decode((string) file_get_contents($tmpFile), true);
@unlink($tmpFile);

if (!is_array($payload)) {
    exit;
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/mailer.php';

try {
    send_ayushman_confirmation_email($payload);
} catch (Throwable $e) {
    error_log('Ayushman async confirmation email (HTTP worker) failed: ' . $e->getMessage());
}
