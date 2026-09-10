<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

$run = $_GET['run'] ?? '';
echo json_encode(['run' => $run]);

if ($run === 'agents') {
    echo json_encode(['step' => 'before_connect']);
    $host = tp_env('DB_HOST', '31.11.39.157');
    $db = tp_env('DB_NAME', 'Sql1658368_5');
    $user = tp_env('DB_USER', 'Sql1658368');
    $pass = tp_env('DB_PASS', 'TrovaP.2026@');
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        echo json_encode(['step' => 'connected']);
        $pdo->exec("SET NAMES utf8mb4");
        echo json_encode(['step' => 'charset_set']);
        $pdo->exec("CREATE TABLE IF NOT EXISTS agents (
            id VARCHAR(100) PRIMARY KEY, user_id VARCHAR(100),
            name VARCHAR(255) NOT NULL, email VARCHAR(255), phone VARCHAR(50),
            zone VARCHAR(255), tier VARCHAR(20) DEFAULT 'bronze',
            commission_rate DECIMAL(5,2) DEFAULT 10.00, bonus_rate DECIMAL(5,2) DEFAULT 0,
            total_earnings DECIMAL(10,2) DEFAULT 0, total_referrals INT DEFAULT 0,
            active TINYINT(1) DEFAULT 1, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        echo json_encode(['step' => 'agents_table']);
        $pdo->exec("CREATE TABLE IF NOT EXISTS agent_commissions (
            id VARCHAR(100) PRIMARY KEY, agent_id VARCHAR(100) NOT NULL,
            restaurant_id VARCHAR(100) NOT NULL, subscription_id VARCHAR(100),
            amount DECIMAL(8,2) NOT NULL, type VARCHAR(20) DEFAULT 'commission',
            status VARCHAR(20) DEFAULT 'pending', period VARCHAR(20),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_agent (agent_id)
        )");
        echo json_encode(['step' => 'commissions_table']);
        $pdo->exec("CREATE TABLE IF NOT EXISTS agent_referrals (
            id VARCHAR(100) PRIMARY KEY, agent_id VARCHAR(100) NOT NULL,
            restaurant_id VARCHAR(100) NOT NULL, referred_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_referral (agent_id, restaurant_id)
        )");
        echo json_encode(['step' => 'referrals_table', 'success' => true]);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
