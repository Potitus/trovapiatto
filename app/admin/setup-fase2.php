<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

$host = tp_env('DB_HOST', '31.11.39.157');
$db = tp_env('DB_NAME', 'Sql1658368_5');
$user = tp_env('DB_USER', 'Sql1658368');
$pass = tp_env('DB_PASS', 'TrovaP.2026@');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $results = [];

    // 1. restaurant_owners (without FK for safety)
    $pdo->exec("CREATE TABLE IF NOT EXISTS restaurant_owners (
        id VARCHAR(100) PRIMARY KEY,
        user_id VARCHAR(100) NOT NULL,
        restaurant_id VARCHAR(100) NOT NULL,
        role VARCHAR(20) DEFAULT 'owner',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_owner (user_id, restaurant_id)
    )");
    $results[] = 'restaurant_owners OK';

    // 2. page_views
    $pdo->exec("CREATE TABLE IF NOT EXISTS page_views (
        id VARCHAR(100) PRIMARY KEY,
        restaurant_id VARCHAR(100),
        dish_id VARCHAR(100),
        page_type VARCHAR(20) DEFAULT 'menu',
        user_ip VARCHAR(45),
        user_agent TEXT,
        referrer VARCHAR(500),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_pv_restaurant (restaurant_id),
        INDEX idx_pv_created (created_at),
        INDEX idx_pv_dish (dish_id)
    )");
    $results[] = 'page_views OK';

    // 3. search_logs
    $pdo->exec("CREATE TABLE IF NOT EXISTS search_logs (
        id VARCHAR(100) PRIMARY KEY,
        query VARCHAR(255),
        results_count INT DEFAULT 0,
        restaurant_id VARCHAR(100),
        user_ip VARCHAR(45),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_sl_query (query),
        INDEX idx_sl_created (created_at),
        INDEX idx_sl_restaurant (restaurant_id)
    )");
    $results[] = 'search_logs OK';

    // 4. Link admin user to all restaurants
    $adminUser = $pdo->query("SELECT id FROM users WHERE role='admin' LIMIT 1")->fetch();
    if ($adminUser) {
        $restaurants = $pdo->query("SELECT id FROM restaurants")->fetchAll(PDO::FETCH_COLUMN);
        $linked = 0;
        foreach ($restaurants as $rid) {
            $oid = 'own_' . bin2hex(random_bytes(8));
            try {
                $stmt = $pdo->prepare("INSERT IGNORE INTO restaurant_owners (id, user_id, restaurant_id, role) VALUES (?, ?, ?, 'owner')");
                $stmt->execute([$oid, $adminUser['id'], $rid]);
                $linked += $stmt->rowCount();
            } catch (Exception $e) { /* skip */ }
        }
        $results[] = "Linked admin to $linked restaurants";
    } else {
        $results[] = "No admin user found - skipped linking";
    }

    echo json_encode(['success' => true, 'results' => $results]);
} catch (Exception $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
