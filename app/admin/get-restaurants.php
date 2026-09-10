<?php
// File intermediario per caricare i ristoranti
// Bypass di tutti i problemi di api.php originale

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
tp_cors_headers('GET, POST', 'Content-Type');

try {
    $db = tp_db_connect();
    
    // Unica query con conteggi aggregati (evita N+1: prima erano 2 query per ristorante)
    $query = "SELECT
        r.id, r.name, r.slug, r.description, r.address, r.phone, r.email, r.logo_url,
        r.latitude, r.longitude, r.city, r.cuisine, r.website, r.rating,
        (SELECT COUNT(*) FROM dishes d WHERE d.restaurant_id = r.id) as dishes_count,
        (SELECT COUNT(DISTINCT d.category_id) FROM dishes d WHERE d.restaurant_id = r.id) as category_count
    FROM restaurants r
    ORDER BY r.name ASC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $restaurants = $stmt->fetchAll();
    foreach ($restaurants as &$r) {
        $r['dishes_count'] = intval($r['dishes_count'] ?? 0);
        $r['category_count'] = intval($r['category_count'] ?? 0);
    }
    unset($r);

    // Se richiesto, carica i piatti di tutti in un colpo solo
    if (!empty($_GET['include_dishes'])) {
        $dishes_query = $db->prepare("
            SELECT d.*, COALESCE(c.name, 'Vari') as category
            FROM dishes d
            LEFT JOIN categories c ON d.category_id = c.id
            ORDER BY d.restaurant_id, COALESCE(c.name, 'Vari'), d.name
        ");
        $dishes_query->execute();
        $byRest = [];
        foreach ($dishes_query->fetchAll() as $d) {
            $byRest[$d['restaurant_id']][] = $d;
        }
        foreach ($restaurants as &$r) {
            $r['dishes'] = $byRest[$r['id']] ?? [];
        }
        unset($r);
    }
    
    echo json_encode([
        'success' => true,
        'total' => count($restaurants),
        'data' => $restaurants
    ], JSON_UNESCAPED_UNICODE);
    
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
