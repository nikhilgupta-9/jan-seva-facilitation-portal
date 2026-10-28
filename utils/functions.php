<?php
/**
 * Small, focused view/data helpers shared across all pages.
 */

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function sanitize($data)
{
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim((string) $data), ENT_QUOTES, 'UTF-8');
}

function asset_url(string $path): string
{
    return ltrim($path, '/');
}

function nav_active_class(?string $activeNav, string $key): string
{
    return $activeNav === $key ? ' active' : '';
}

function app_url(string $path = ''): string
{
    $base = rtrim(env('APP_URL', 'http://localhost/gp'), '/');
    return $base . '/' . ltrim($path, '/');
}

function scheme_url(string $slug): string
{
    $base = defined('BASE_URL') ? BASE_URL : '';
    return $base . '/scheme/' . rawurlencode($slug);
}
