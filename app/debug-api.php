<?php
require_once __DIR__ . '/admin/config.php';
tp_require_setup_access();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Test 1: Can we even load api.php?
$result = [];
try {
    ob_start();
    include __DIR__ . '/app/admin/api.php';
    $output = ob_get_clean();
    $result['include_success'] = true;
    $result['output_length'] = strlen($output);
} catch (Throwable $e) {
    ob_end_clean();
    $result['include_error'] = $e->getMessage();
    $result['include_file'] = $e->getFile();
    $result['include_line'] = $e->getLine();
}

// Test 2: Check if verifyApiToken function exists
$result['verifyApiToken_exists'] = function_exists('verifyApiToken');
$result['jwt_b64url_encode_exists'] = function_exists('_jwt_b64url_encode');

echo json_encode($result, JSON_PRETTY_PRINT);
