<?php
/**
 * Minimal, dependency-free .env loader.
 * Parses KEY=VALUE lines (ignoring blanks and #comments) and exposes them
 * via env(), without overwriting variables already set in the real environment.
 */

function load_env(string $path): void
{
    static $loaded = false;
    if ($loaded || !is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $value = trim($value, "\"'");

        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }

    $loaded = true;
}

function env(string $key, $default = null)
{
    $value = getenv($key);
    return $value === false ? $default : $value;
}
