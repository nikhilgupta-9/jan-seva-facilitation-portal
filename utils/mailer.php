<?php
/**
 * Minimal, dependency-free mail sender used to notify the facilitation
 * desk whenever a citizen submits the Contact Us form.
 *
 * Delivery is always best-effort: the contact form's own DB insert into
 * `contact_messages` (handled in contact.php) is the reliable record of
 * every inquiry, and is never blocked by a mail failure. This function
 * only adds an email notification on top of that.
 *
 * Two delivery paths:
 *  - SMTP (used when SMTP_HOST is set in .env) via a small hand-rolled
 *    SMTP client (STARTTLS + AUTH LOGIN). No external library needed.
 *  - PHP's built-in mail() as a fallback when SMTP isn't configured.
 *    Note: on a bare XAMPP install this usually requires a local
 *    sendmail/Postfix relay to actually deliver; SMTP is the reliable
 *    option for real delivery.
 */

function send_contact_notification_email(array $inquiry): bool
{
    $to = env('CONTACT_RECEIVER_EMAIL') ?: (defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : null);
    if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $appName = defined('APP_NAME') ? APP_NAME : 'Citizen Portal';
    $subject = '[' . $appName . '] New Contact Inquiry #' . ($inquiry['id'] ?? '') . ' - ' . ($inquiry['subject'] ?: 'Citizen Inquiry');

    $bodyLines = [
        "A new inquiry was submitted on the Contact Us page.",
        "",
        "Reference Ticket: #INQ-" . ($inquiry['id'] ?? ''),
        "Name: " . $inquiry['name'],
        "Phone: " . $inquiry['phone'],
        "Email: " . ($inquiry['email'] ?: 'Not provided'),
        "Service of Interest: " . $inquiry['service_interest'],
        "Subject: " . $inquiry['subject'],
        "",
        "Message:",
        $inquiry['message'],
        "",
        "Submitted: " . date('d M Y, h:i A'),
        "IP Address: " . ($inquiry['ip'] ?: 'Unknown'),
        "",
        "View and respond from the admin panel: " . app_url('admin/messages.php'),
    ];
    $body = implode("\n", $bodyLines);

    $smtpHost = env('SMTP_HOST');
    if ($smtpHost) {
        try {
            return smtp_send_mail($to, $subject, $body);
        } catch (Exception $e) {
            error_log('Contact form SMTP send failed: ' . $e->getMessage());
            return false;
        }
    }

    // Fallback: PHP's built-in mail(). Requires a working local MTA.
    $fromEmail = defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : 'no-reply@localhost';
    $headers = "From: {$appName} <{$fromEmail}>\r\n"
        . "Reply-To: " . ($inquiry['email'] ?: $fromEmail) . "\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";

    return @mail($to, $subject, $body, $headers);
}

/**
 * Tiny SMTP client: connects, optionally upgrades to TLS via STARTTLS,
 * authenticates with AUTH LOGIN, and sends one plain-text message.
 * Throws on any unexpected server response.
 */
function smtp_send_mail(string $to, string $subject, string $body): bool
{
    $host = env('SMTP_HOST');
    $port = (int) (env('SMTP_PORT', '587'));
    $user = env('SMTP_USER');
    $pass = env('SMTP_PASS');
    $fromEmail = defined('SUPPORT_EMAIL') ? SUPPORT_EMAIL : $user;
    $appName = defined('APP_NAME') ? APP_NAME : 'Citizen Portal';

    $timeout = 15;
    $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$socket) {
        throw new Exception("Could not connect to SMTP host {$host}:{$port} - {$errstr}");
    }
    stream_set_timeout($socket, $timeout);

    $expect = function (string $context) use ($socket) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if ($code >= 400 || $code === 0) {
            throw new Exception("SMTP error during {$context}: {$response}");
        }
        return $response;
    };

    $send = function (string $command) use ($socket) {
        fwrite($socket, $command . "\r\n");
    };

    $expect('connect');

    $localHost = parse_url(app_url(), PHP_URL_HOST) ?: 'localhost';
    $send("EHLO {$localHost}");
    $expect('EHLO');

    if ($port == 587) {
        $send('STARTTLS');
        $expect('STARTTLS');
        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new Exception('Failed to enable TLS');
        }
        $send("EHLO {$localHost}");
        $expect('EHLO after STARTTLS');
    }

    if ($user && $pass) {
        $send('AUTH LOGIN');
        $expect('AUTH LOGIN');
        $send(base64_encode($user));
        $expect('AUTH username');
        $send(base64_encode($pass));
        $expect('AUTH password');
    }

    $send("MAIL FROM:<{$fromEmail}>");
    $expect('MAIL FROM');
    $send("RCPT TO:<{$to}>");
    $expect('RCPT TO');
    $send('DATA');
    $expect('DATA');

    $headers = "From: {$appName} <{$fromEmail}>\r\n"
        . "To: <{$to}>\r\n"
        . "Subject: {$subject}\r\n"
        . "MIME-Version: 1.0\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n";

    // Escape leading dots per RFC 5321 so the body isn't mistaken for the terminator.
    $escapedBody = preg_replace('/^\./m', '..', $body);

    $send($headers . "\r\n" . $escapedBody . "\r\n.");
    $expect('message body');

    $send('QUIT');
    fclose($socket);

    return true;
}
