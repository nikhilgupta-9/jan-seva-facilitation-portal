<?php
/**
 * Single place that starts PHP sessions with hardened cookie settings
 * (HttpOnly, SameSite=Lax, Secure-when-HTTPS, strict session IDs).
 * Cookie params must be set BEFORE session_start(), so every entry point
 * that starts a session should call this instead of a bare session_start().
 */

function ayushman_start_secure_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443)
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if ($isHttps) {
        ini_set('session.cookie_secure', '1');
    }

    session_start();
}
