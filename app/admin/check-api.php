<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Check what functions exist after loading
$result = [];

// Try to include the file in a way that defines functions but doesn't execute
$code = file_get_contents(__DIR__ . '/app/admin/api.php');
$result['file_size'] = strlen($code);
$result['has_jwt_b64url'] = strpos($code, '_jwt_b64url_encode') !== false;
$result['has_base64_encode_in_jwt'] = (bool)preg_match('/function\s+_jwt_b64url_encode.*?base64_encode/s', $code);
$result['has_require_once'] = strpos($code, 'require_once') !== false;
$result['has_jwt_secret_define'] = strpos($code, "define('JWT_SECRET'") !== false;
$result['has_jwt_secret_key'] = strpos($code, '$JWT_SECRET_KEY') !== false;
$result['has_global_jwt'] = strpos($code, 'global $JWT_SECRET_KEY') !== false;
$result['has_verify_function'] = strpos($code, 'function verifyApiToken()') !== false;

// Check the actual JWT line in verify function
preg_match('/\$expected\s*=.*?;/s', $code, $matches);
$result['expected_line'] = $matches[0] ?? 'not found';

echo json_encode($result, JSON_PRETTY_PRINT);
