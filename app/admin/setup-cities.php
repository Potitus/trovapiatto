<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
header('Content-Type: application/json');
$host = tp_env('DB_HOST', '31.11.39.157');
$db = tp_env('DB_NAME', 'Sql1658368_5');
$user = tp_env('DB_USER', 'Sql1658368');
$pass = tp_env('DB_PASS', 'TrovaP.2026@');
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $cities = $pdo->query("SELECT name, slug, region, restaurant_count FROM cities ORDER BY restaurant_count DESC")->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'cities' => $cities]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
