<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
header('Content-Type: application/json');

$host = tp_env('DB_HOST', '31.11.39.157');
$db = tp_env('DB_NAME', 'Sql1658368_5');
$user = tp_env('DB_USER', 'Sql1658368');
$pass = tp_env('DB_PASS', 'TrovaP.2026@');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pv = $pdo->query("SELECT COUNT(*) as c FROM page_views")->fetch()['c'];
    $sl = $pdo->query("SELECT COUNT(*) as c FROM search_logs")->fetch()['c'];
    $ro = $pdo->query("SELECT COUNT(*) as c FROM restaurant_owners")->fetch()['c'];
    $us = $pdo->query("SELECT COUNT(*) as c FROM users")->fetch()['c'];

    echo json_encode([
        'page_views' => $pv,
        'search_logs' => $sl,
        'restaurant_owners' => $ro,
        'users' => $us
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
