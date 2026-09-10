<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
header('Content-Type: application/json');
$host = tp_env('DB_HOST', '31.11.39.157');
$db   = 'Sql1658368';
$user = tp_env('DB_USER', 'Sql1658368');
$pass = tp_env('DB_PASS', 'TrovaP.2026@');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Update Beccheria logo
    $logoUrl = 'https://www.trovapiatto.it/images/restaurants/beccheria-logo.jpg';
    $stmt = $pdo->prepare("UPDATE restaurants SET logo_url = ? WHERE slug = ?");
    $stmt->execute([$logoUrl, 'beccheria-del-borgo-antico']);
    echo "Logo updated: " . $stmt->rowCount() . " row(s)\n";

    // 2. Update dish image for Panino con arrosto misto
    $imageUrl = 'https://www.trovapiatto.it/images/restaurants/beccheria-carne.jpg';
    $stmt = $pdo->prepare("UPDATE dishes SET image_url = ? WHERE id = ?");
    $stmt->execute([$imageUrl, 'dish_58c9bf6fd62b']);
    echo "Dish image updated: " . $stmt->rowCount() . " row(s)\n";

    // 3. Also update the insegna photo for Beccheria (exterior)
    $extUrl = 'https://www.trovapiatto.it/images/restaurants/beccheria-logo.jpg';
    // Update dishes that have no image with a generic Beccheria image
    $stmt = $pdo->prepare("UPDATE dishes SET image_url = ? WHERE restaurant_id = (SELECT id FROM restaurants WHERE slug = ?) AND (image_url IS NULL OR image_url = '')");
    $stmt->execute([$imageUrl, 'beccheria-del-borgo-antico']);
    echo "Empty dish images updated: " . $stmt->rowCount() . " row(s)\n";

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
