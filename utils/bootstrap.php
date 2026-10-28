<?php
/**
 * Single entry point every page requires before rendering: loads env vars
 * and shared helper functions. Using __DIR__ keeps includes working
 * regardless of the caller's working directory.
 */

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/functions.php';

load_env(dirname(__DIR__) . '/.env');

$appDebug = env('APP_DEBUG', 'false') === 'true';
if ($appDebug) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}
