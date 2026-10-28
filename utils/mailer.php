<?php
/**
 * Minimal contact-form handler. Validates input and appends the submission
 * as a JSON line to storage/contact-submissions.log instead of wiring a
 * real SMTP integration (not asked for).
 *
 * @return array{success: bool, errors: string[]}
 */
function handle_contact_submission(array $post): array
{
    $errors = [];

    $name = sanitize($post['name'] ?? '');
    $email = sanitize($post['email'] ?? '');
    $mobile = sanitize($post['mobile'] ?? '');
    $subject = sanitize($post['subject'] ?? '');
    $message = sanitize($post['message'] ?? '');

    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if ($message === '') {
        $errors[] = 'Message cannot be empty.';
    }

    if ($errors !== []) {
        return ['success' => false, 'errors' => $errors];
    }

    $record = [
        'name' => $name,
        'email' => $email,
        'mobile' => $mobile,
        'subject' => $subject,
        'message' => $message,
        'submitted_at' => date('c'),
    ];

    $storageDir = dirname(__DIR__) . '/storage';
    if (!is_dir($storageDir)) {
        mkdir($storageDir, 0755, true);
    }

    file_put_contents(
        $storageDir . '/contact-submissions.log',
        json_encode($record, JSON_UNESCAPED_SLASHES) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );

    return ['success' => true, 'errors' => []];
}
