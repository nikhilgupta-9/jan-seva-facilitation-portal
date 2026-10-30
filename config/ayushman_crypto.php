<?php
// config/ayushman_crypto.php - Cryptographic engine for Ayushman Digital Health Smart ID
// Military-grade AES-256-CBC authenticated encryption with HMAC-SHA256 (Encrypt-then-MAC)

if (!defined('AYUSHMAN_ENCRYPTION_KEY')) {
    // 32-byte (256-bit) persistent encryption key
    $envKey = function_exists('env') ? env('AYUSHMAN_ENCRYPTION_KEY') : null;
    define('AYUSHMAN_ENCRYPTION_KEY', $envKey ?: 'd47f9a1b8c2e63540a91f58e23b7c841e06d9a34bc1258ef9041285cb87123aa');
}

/**
 * Encrypt citizen payload into a tamper-proof authenticated token for QR encoding.
 * Format: SECURE-AYUSHMAN:v1:<base64url(iv[16] . ciphertext . hmac[32])>
 */
function ayushman_encrypt_payload(array $data): string
{
    $keyBin = hex2bin(AYUSHMAN_ENCRYPTION_KEY);
    $data['_timestamp'] = time();
    $data['_nonce'] = bin2hex(random_bytes(8));
    $plaintext = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $iv = random_bytes(16);
    $ciphertext = openssl_encrypt($plaintext, 'aes-256-cbc', $keyBin, OPENSSL_RAW_DATA, $iv);
    if ($ciphertext === false) {
        throw new RuntimeException('Encryption failed: ' . openssl_error_string());
    }

    // Key separation for HMAC
    $hmacKey = hash_hmac('sha256', 'AYUSHMAN_HMAC_AUTHENTICATION_SALT', $keyBin, true);
    $hmac = hash_hmac('sha256', $iv . $ciphertext, $hmacKey, true);

    $payload = $iv . $ciphertext . $hmac;
    $encoded = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');

    return 'SECURE-AYUSHMAN:v1:' . $encoded;
}

/**
 * Decrypt and verify tamper-proof authenticated token.
 * Returns decoded associative array if valid, null if invalid or tampered.
 */
function ayushman_decrypt_payload(string $token): ?array
{
    $token = trim($token);
    $prefix = 'SECURE-AYUSHMAN:v1:';
    if (strpos($token, $prefix) !== 0) {
        return null;
    }

    $b64 = substr($token, strlen($prefix));
    $raw = base64_decode(strtr($b64, '-_', '+/'), true);
    if ($raw === false || strlen($raw) < 48) { // 16 bytes IV + at least 1 byte cipher + 32 bytes HMAC
        return null;
    }

    $iv = substr($raw, 0, 16);
    $hmac = substr($raw, -32);
    $ciphertext = substr($raw, 16, -32);

    $keyBin = hex2bin(AYUSHMAN_ENCRYPTION_KEY);
    $hmacKey = hash_hmac('sha256', 'AYUSHMAN_HMAC_AUTHENTICATION_SALT', $keyBin, true);
    $expectedHmac = hash_hmac('sha256', $iv . $ciphertext, $hmacKey, true);

    if (!hash_equals($hmac, $expectedHmac)) {
        return null; // Tampered or wrong key
    }

    $decrypted = openssl_decrypt($ciphertext, 'aes-256-cbc', $keyBin, OPENSSL_RAW_DATA, $iv);
    if ($decrypted === false) {
        return null;
    }

    $data = json_decode($decrypted, true);
    return is_array($data) ? $data : null;
}

/**
 * Generate a unique public application number: AYUSH-YYYYMMDD-XXXX
 */
function ayushman_generate_application_no(): string
{
    $datePart = date('Ymd');
    $randPart = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    return 'AYUSH-' . $datePart . '-' . $randPart;
}

/**
 * Generate a unique Digital Health Smart ID Card number: DHS-XXXX-XXXX-XXXX
 */
function ayushman_generate_card_id(): string
{
    $p1 = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    $p2 = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    $p3 = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
    return 'DHS-' . $p1 . '-' . $p2 . '-' . $p3;
}
