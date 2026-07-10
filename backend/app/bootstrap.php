<?php
/**
 * HairLook bootstrap — single entry point for loading application code.
 * Include this file from pages, scripts, and tests.
 */
// Ensure production-like behavior for API endpoints: suppress PHP notices in JSON responses during development
if (php_sapi_name() !== 'cli') {
    ini_set('display_errors', '0');
    error_reporting(0);
}

require_once __DIR__ . '/funciones_barberia.php';
