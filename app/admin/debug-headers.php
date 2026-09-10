<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
header('Content-Type: application/json; charset=utf-8');
tp_cors_headers('GET, OPTIONS', 'Content-Type, Authorization');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$result = [];

// getallheaders
if (function_exists('getallheaders')) {
    $result['getallheaders'] = getallheaders();
} else {
    $result['getallheaders'] = 'function not available';
}

// $_SERVER
$result['HTTP_AUTHORIZATION'] = $_SERVER['HTTP_AUTHORIZATION'] ?? 'NOT SET';
$result['REDIRECT_HTTP_AUTHORIZATION'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? 'NOT SET';

// apache_request_headers
if (function_exists('apache_request_headers')) {
    $result['apache_request_headers'] = apache_request_headers();
} else {
    $result['apache_request_headers'] = 'function not available';
}

echo json_encode($result, JSON_PRETTY_PRINT);
