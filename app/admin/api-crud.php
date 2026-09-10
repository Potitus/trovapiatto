<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
tp_cors_headers('GET, POST, PUT, DELETE, OPTIONS', 'Content-Type, Authorization, X-Auth-Token');

try {
    $db = tp_db_connect();
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// === JWT AUTH (must match api-auth.php encoding: b64url) ===
$JWT_SECRET = tp_jwt_secret();

function _crud_b64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function _crud_b64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function verifyCrudToken() {
    global $JWT_SECRET;
    $token = '';
    if (isset($_GET['token']) && !empty($_GET['token'])) {
        $token = $_GET['token'];
    }
    if (empty($token)) {
        $auth = '';
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $auth = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        }
        if (empty($auth)) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        }
        if (!empty($auth) && preg_match('/Bearer\s+(.+)$/i', $auth, $m)) {
            $token = $m[1];
        }
    }
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token mancante']);
        exit;
    }
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        http_response_code(401);
        echo json_encode(['error' => 'Token invalido']);
        exit;
    }
    [$hdr, $payload, $sig] = $parts;
    $expected = _crud_b64url_encode(hash_hmac('sha256', "$hdr.$payload", $JWT_SECRET, true));
    if (!hash_equals($expected, $sig)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token non valido']);
        exit;
    }
    $data = json_decode(_crud_b64url_decode($payload), true);
    if (!$data || ($data['exp'] ?? 0) < time()) {
        http_response_code(401);
        echo json_encode(['error' => 'Token scaduto']);
        exit;
    }
    return $data;
}

// === HELPER FUNCTIONS ===
function extractAllergensFromText($text) {
    $allergens = [];
    $keywords = [
        'glutine' => ['grano', 'pasta', 'pane', 'orzo'],
        'lattosio' => ['latte', 'formaggio', 'burro', 'panna'],
        'uova' => ['uova', 'uovo'],
        'pesce' => ['pesce', 'salmone', 'branzino'],
        'crostacei' => ['gambero', 'camarone', 'aragosta'],
        'molluschi' => ['vongola', 'cozza', 'seppia', 'calamaro'],
        'noci' => ['noce', 'mandorla', 'nocciola'],
        'arachidi' => ['arachide'],
        'sesamo' => ['sesamo'],
        'soia' => ['soia'],
        'sedano' => ['sedano'],
        'solfiti' => ['vino']
    ];
    $text_lower = strtolower($text);
    foreach ($keywords as $allergen => $kw) {
        foreach ($kw as $k) {
            if (strpos($text_lower, $k) !== false) {
                $allergens[] = $allergen;
                break;
            }
        }
    }
    return $allergens;
}

function getInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    return $input ?: [];
}

// === CRUD FUNCTIONS ===
function crudCreateRestaurant($db) {
    $data = getInput();
    if (!$data || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome mancante']);
        return;
    }
    $slug = strtolower($data['name']);
    $slug = preg_replace('/[àáâãäå]/i', 'a', $slug);
    $slug = preg_replace('/[èéêë]/i', 'e', $slug);
    $slug = preg_replace('/[ìíîï]/i', 'i', $slug);
    $slug = preg_replace('/[òóôõö]/i', 'o', $slug);
    $slug = preg_replace('/[ùúûü]/i', 'u', $slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    $id = uniqid('rest_');
    $stmt = $db->prepare("INSERT INTO restaurants (id, name, slug, description, address, phone, email, logo_url, latitude, longitude, city, cuisine, website, rating) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $data['name'], $slug, $data['description'] ?? null, $data['address'] ?? null, $data['phone'] ?? null, $data['email'] ?? null, $data['logo_url'] ?? null, $data['latitude'] ?? null, $data['longitude'] ?? null, $data['city'] ?? null, $data['cuisine'] ?? null, $data['website'] ?? null, $data['rating'] ?? null]);
    echo json_encode(['success' => true, 'id' => $id, 'slug' => $slug], JSON_UNESCAPED_UNICODE);
}

function crudUpdateRestaurant($db) {
    $data = getInput();
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    $stmt = $db->prepare("UPDATE restaurants SET name = ?, description = ?, address = ?, phone = ?, email = ?, logo_url = ?, latitude = ?, longitude = ?, city = ?, cuisine = ?, website = ?, michelin_stars = ? WHERE id = ?");
    $result = $stmt->execute([$data['name'] ?? null, $data['description'] ?? null, $data['address'] ?? null, $data['phone'] ?? null, $data['email'] ?? null, $data['logo_url'] ?? null, $data['latitude'] ?? null, $data['longitude'] ?? null, $data['city'] ?? null, $data['cuisine'] ?? null, $data['website'] ?? null, $data['michelin_stars'] ?? 0, $data['id']]);
    echo json_encode(['success' => $result], JSON_UNESCAPED_UNICODE);
}

function crudUpdateSlug($db) {
    $data = getInput();
    if (!$data || !isset($data['id']) || !isset($data['new_slug'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID e new_slug mancanti']);
        return;
    }
    $stmt = $db->prepare("UPDATE restaurants SET slug = ? WHERE id = ?");
    $result = $stmt->execute([$data['new_slug'], $data['id']]);
    echo json_encode(['success' => $result], JSON_UNESCAPED_UNICODE);
}

function crudDeleteRestaurant($db) {
    $data = getInput();
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    $stmt = $db->prepare("DELETE FROM restaurants WHERE id = ?");
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
}

function crudAddCategory($db) {
    $data = getInput();
    if (!isset($data['restaurant_id']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Restaurant ID e Name sono obbligatori'], JSON_UNESCAPED_UNICODE);
        return;
    }
    try {
        $categoryId = 'cat_' . substr(md5(uniqid()), 0, 12);
        $displayOrder = isset($data['display_order']) ? intval($data['display_order']) : 0;
        $description = $data['description'] ?? '';
        $checkStmt = $db->prepare("SELECT id FROM restaurants WHERE id = ?");
        $checkStmt->execute([$data['restaurant_id']]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Ristorante non trovato'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $stmt = $db->prepare("INSERT INTO categories (id, restaurant_id, name, description, display_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$categoryId, $data['restaurant_id'], $data['name'], $description, $displayOrder]);
        echo json_encode(['success' => true, 'id' => $categoryId], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function crudUpdateCategory($db) {
    $data = getInput();
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Category ID obbligatorio'], JSON_UNESCAPED_UNICODE);
        return;
    }
    try {
        $updates = [];
        $params = [];
        if (isset($data['name'])) { $updates[] = "name = ?"; $params[] = $data['name']; }
        if (isset($data['description'])) { $updates[] = "description = ?"; $params[] = $data['description']; }
        if (isset($data['display_order'])) { $updates[] = "display_order = ?"; $params[] = intval($data['display_order']); }
        if (empty($updates)) {
            http_response_code(400);
            echo json_encode(['error' => 'Nessun campo da aggiornare'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $updates[] = "updated_at = NOW()";
        $params[] = $data['id'];
        $query = "UPDATE categories SET " . implode(', ', $updates) . " WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria non trovata'], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function crudDeleteCategory($db) {
    $data = getInput();
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Category ID obbligatorio'], JSON_UNESCAPED_UNICODE);
        return;
    }
    try {
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM dishes WHERE category_id = ?");
        $checkStmt->execute([$data['id']]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if ($result['count'] > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Impossibile eliminare: ' . $result['count'] . ' piatti assegnati'], JSON_UNESCAPED_UNICODE);
            return;
        }
        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$data['id']]);
        if ($stmt->rowCount() === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria non trovata'], JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function crudAddDish($db) {
    $data = getInput();
    if (!$data || !isset($data['restaurant_id']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dati mancanti']);
        return;
    }
    $id = uniqid('dish_');
    if (!empty($data['allergens']) && is_array($data['allergens'])) {
        $allergens = array_map('strtolower', $data['allergens']);
    } else {
        $allergenText = ($data['name'] ?? '') . ' ' . ($data['description'] ?? '');
        $allergens = extractAllergensFromText($allergenText);
    }
    $categoryId = $data['category_id'] ?? null;
    $categoryStr = $data['category'] ?? null;
    if ($categoryId) {
        $checkStmt = $db->prepare("SELECT id FROM categories WHERE id = ? AND restaurant_id = ?");
        $checkStmt->execute([$categoryId, $data['restaurant_id']]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria non trovata']);
            return;
        }
    }
    $name_en = $data['name_en'] ?? null;
    $description_en = $data['description_en'] ?? null;
    $stmt = $db->prepare("INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, name_en, description_en, price, image_url, available, allergens) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $data['restaurant_id'], $categoryStr, $categoryId, $data['name'], $data['description'] ?? null, $name_en, $description_en, $data['price'] ?? null, $data['image_url'] ?? null, $data['available'] ?? true, json_encode($allergens)]);
    echo json_encode(['success' => true, 'id' => $id, 'allergens' => $allergens], JSON_UNESCAPED_UNICODE);
}

function crudCloneDish($db) {
    $data = getInput();
    if (!$data || !isset($data['source_dish_id']) || !isset($data['restaurant_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dati mancanti: source_dish_id e restaurant_id richiesti']);
        return;
    }
    $stmt = $db->prepare("SELECT * FROM dishes WHERE id = ?");
    $stmt->execute([$data['source_dish_id']]);
    $sourceDish = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$sourceDish) { http_response_code(404); echo json_encode(['error' => 'Piatto sorgente non trovato']); return; }
    if ($sourceDish['restaurant_id'] !== $data['restaurant_id']) { http_response_code(403); echo json_encode(['error' => 'Non puoi clonare piatti da altri ristoranti']); return; }
    $newId = uniqid('dish_');
    try {
        $stmt = $db->prepare("INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, name_en, description_en, price, image_url, available, allergens) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$newId, $data['restaurant_id'], $data['new_category'] ?? $sourceDish['category'], $data['new_category_id'] ?? $sourceDish['category_id'], $data['new_name'] ?? ($sourceDish['name'] . ' (Copia)'), $data['new_description'] ?? $sourceDish['description'], $data['new_name_en'] ?? $sourceDish['name_en'], $data['new_description_en'] ?? $sourceDish['description_en'], $data['new_price'] ?? $sourceDish['price'], $sourceDish['image_url'], $data['available'] ?? $sourceDish['available'], $sourceDish['allergens']]);
        echo json_encode(['success' => true, 'id' => $newId], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore: ' . $e->getMessage()]);
    }
}

function crudUpdateDish($db) {
    $data = getInput();
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    $dishStmt = $db->prepare("SELECT * FROM dishes WHERE id = ?");
    $dishStmt->execute([$data['id']]);
    $currentDish = $dishStmt->fetch(PDO::FETCH_ASSOC);
    if (!$currentDish) { http_response_code(404); echo json_encode(['error' => 'Piatto non trovato']); return; }
    $category = $data['category'] ?? $currentDish['category'];
    $category_id = $data['category_id'] ?? $currentDish['category_id'];
    $name = $data['name'] ?? $currentDish['name'];
    $description = $data['description'] ?? $currentDish['description'];
    $name_en = $data['name_en'] ?? $currentDish['name_en'];
    $description_en = $data['description_en'] ?? $currentDish['description_en'];
    $price = $data['price'] ?? $currentDish['price'];
    $image_url = $data['image_url'] ?? $currentDish['image_url'];
    $available = isset($data['available']) ? $data['available'] : $currentDish['available'];
    if (!empty($data['allergens']) && is_array($data['allergens'])) {
        $allergens = array_map('strtolower', $data['allergens']);
    } elseif (
        (isset($data['name']) && $data['name'] !== $currentDish['name']) ||
        (isset($data['description']) && $data['description'] !== $currentDish['description'])
    ) {
        $allergens = extractAllergensFromText($name . ' ' . $description);
    } else {
        $allergens = $currentDish['allergens'];
    }
    $stmt = $db->prepare("UPDATE dishes SET category = ?, category_id = ?, name = ?, description = ?, name_en = ?, description_en = ?, price = ?, image_url = ?, available = ?, allergens = ? WHERE id = ?");
    $result = $stmt->execute([$category, $category_id, $name, $description, $name_en, $description_en, $price, $image_url, $available, is_array($allergens) ? json_encode($allergens) : $allergens, $data['id']]);
    echo json_encode(['success' => $result, 'allergens' => (is_array($allergens) ? $allergens : json_decode($allergens, true))], JSON_UNESCAPED_UNICODE);
}

function crudDeleteDish($db) {
    $data = getInput();
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    $stmt = $db->prepare("DELETE FROM dishes WHERE id = ?");
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
}

function crudUploadImage() {
    global $db;
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File non caricato correttamente']);
        return;
    }
    $file = $_FILES['image'];
    $maxSize = 5 * 1024 * 1024;
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if ($file['size'] > $maxSize) { http_response_code(400); echo json_encode(['error' => 'File troppo grande (max 5MB)']); return; }
    if (!in_array($file['type'], $allowedTypes)) { http_response_code(400); echo json_encode(['error' => 'Formato non supportato (jpg, png, webp)']); return; }
    $uploadDir = dirname(__DIR__) . '/admin/uploads';
    if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'dish_' . time() . '_' . uniqid() . '.' . $fileExt;
    $filePath = $uploadDir . '/' . $fileName;
    if (!move_uploaded_file($file['tmp_name'], $filePath)) { http_response_code(500); echo json_encode(['error' => 'Errore durante il salvataggio']); return; }
    $imageId = uniqid('img_');
    $fileUrl = '/admin/uploads/' . $fileName;
    try {
        if ($db) {
            $stmt = $db->prepare("INSERT INTO images (id, filename, url, uploaded_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$imageId, $fileName, $fileUrl, date('Y-m-d H:i:s')]);
        }
    } catch (Exception $e) { error_log('Errore meta-immagine: ' . $e->getMessage()); }
    echo json_encode(['success' => true, 'url' => $fileUrl, 'filename' => $fileName, 'imageId' => $imageId], JSON_UNESCAPED_UNICODE);
}

function crudSyncCategories($db) {
    try {
        $sql = "UPDATE dishes d SET d.category = (SELECT c.name FROM categories c WHERE c.id = d.category_id LIMIT 1) WHERE d.category_id IS NOT NULL AND (d.category IS NULL OR d.category = '' OR TRIM(d.category) = '')";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rows1 = $stmt->rowCount();
        $sql2 = "UPDATE dishes SET category = 'Vari' WHERE category_id IS NULL AND (category IS NULL OR category = '' OR TRIM(category) = '')";
        $stmt2 = $db->prepare($sql2);
        $stmt2->execute();
        $rows2 = $stmt2->rowCount();
        echo json_encode(['success' => true, 'message' => 'Categorie sincronizzate', 'categories_from_join' => $rows1, 'categories_set_to_vari' => $rows2, 'total_updated' => $rows1 + $rows2], JSON_UNESCAPED_UNICODE);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function crudCleanup($db) {
    try {
        $stmt = $db->prepare("DELETE FROM dishes WHERE restaurant_id NOT IN (SELECT id FROM restaurants)");
        $stmt->execute();
        $orphanDishes = $stmt->rowCount();
        $stmt2 = $db->prepare("DELETE FROM categories WHERE restaurant_id NOT IN (SELECT id FROM restaurants)");
        $stmt2->execute();
        $orphanCategories = $stmt2->rowCount();
        echo json_encode(['success' => true, 'message' => "Pulizia completata: $orphanDishes piatti orfani, $orphanCategories categorie orfane eliminati", 'deleted_dishes' => $orphanDishes, 'deleted_categories' => $orphanCategories], JSON_UNESCAPED_UNICODE);
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function crudGetRestaurants($db) {
    $stmt = $db->prepare("SELECT * FROM restaurants ORDER BY name");
    $stmt->execute();
    $restaurants = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $restaurants], JSON_UNESCAPED_UNICODE);
}

function crudGetRestaurantMenu($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'Restaurant ID mancante']); return; }
    $stmt = $db->prepare("SELECT id FROM restaurants WHERE id = ?");
    $stmt->execute([$rid]);
    $restaurant = $stmt->fetch();
    if (!$restaurant) {
        $stmt = $db->prepare("SELECT id FROM restaurants WHERE slug = ?");
        $stmt->execute([$rid]);
        $restaurant = $stmt->fetch();
    }
    if (!$restaurant) { http_response_code(404); echo json_encode(['error' => 'Ristorante non trovato']); return; }
    $stmt = $db->prepare("SELECT d.*, COALESCE(c.name, d.category, 'Vari') as category_name FROM dishes d LEFT JOIN categories c ON d.category_id = c.id WHERE d.restaurant_id = ? ORDER BY COALESCE(c.name, d.category, 'Vari'), d.name");
    $stmt->execute([$restaurant['id']]);
    $dishes = $stmt->fetchAll();
    $catStmt = $db->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY display_order, name");
    $catStmt->execute([$restaurant['id']]);
    $categories = $catStmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $dishes, 'categories' => $categories], JSON_UNESCAPED_UNICODE);
}

function crudGetAllDishes($db) {
    $dishes = $db->query("SELECT d.*, COALESCE(c.name, d.category, 'Vari') as category_name FROM dishes d LEFT JOIN categories c ON d.category_id = c.id ORDER BY COALESCE(c.name, d.category, 'Vari'), d.name")->fetchAll();
    echo json_encode(['success' => true, 'data' => $dishes], JSON_UNESCAPED_UNICODE);
}

function crudExportData($db) {
    $restaurants = $db->query("SELECT * FROM restaurants")->fetchAll();
    $dishes = $db->query("SELECT * FROM dishes")->fetchAll();
    $categories = $db->query("SELECT * FROM categories")->fetchAll();
    echo json_encode(['success' => true, 'restaurants' => $restaurants, 'dishes' => $dishes, 'categories' => $categories, 'exported_at' => date('Y-m-d H:i:s')], JSON_UNESCAPED_UNICODE);
}

function crudRunOCR() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['image'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Immagine non fornita']);
            return;
        }
        $imageData = $input['image'];
        if (strpos($imageData, 'data:image') === 0) {
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
        }
        $binaryData = base64_decode($imageData, true);
        if ($binaryData === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Immagine base64 non valida']);
            return;
        }
        $uploadDir = sys_get_temp_dir();
        $filename = 'ocr_' . uniqid() . '.jpg';
        $filepath = $uploadDir . '/' . $filename;
        if (file_put_contents($filepath, $binaryData) === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Errore nel salvataggio dell\'immagine']);
            return;
        }
        $extractedText = crudOCRSpaceAPI($filepath);
        @unlink($filepath);
        if (empty($extractedText)) {
            echo json_encode(['success' => false, 'error' => 'Nessun testo trovato nell\'immagine', 'extracted_text' => '']);
            return;
        }
        echo json_encode(['success' => true, 'extracted_text' => $extractedText], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        error_log('Errore OCR: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore durante l\'OCR: ' . $e->getMessage()]);
    }
}

function crudOCRSpaceAPI($imagePath) {
    $url = 'https://api.ocr.space/parse/image';
    $imageData = base64_encode(file_get_contents($imagePath));
    $mimeType = mime_content_type($imagePath);
    $dataUri = 'data:' . $mimeType . ';base64,' . $imageData;
    $postFields = [
        'base64Image' => $dataUri,
        'language' => 'ita',
        'OCREngine' => '2',
        'isOverlayRequired' => 'false',
        'scale' => 'true',
        'isTable' => 'true'
    ];
    $apiKey = tp_ocr_key();
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => ['apikey: ' . $apiKey]
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    if ($error) { throw new Exception("Errore connessione OCR: " . $error); }
    if ($httpCode !== 200) { throw new Exception("Errore OCR API: HTTP " . $httpCode); }
    $result = json_decode($response, true);
    if (!$result) { throw new Exception("Risposta OCR non valida"); }
    if (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing']) {
        $errorMsg = $result['ErrorMessage'] ?? ['Errore sconosciuto'];
        throw new Exception("OCR Error: " . implode(', ', $errorMsg));
    }
    if (isset($result['ParsedResults'][0]['ParsedText'])) {
        return $result['ParsedResults'][0]['ParsedText'];
    }
    throw new Exception("Nessun testo estratto dall'immagine");
}

function crudSyncData($db) {
    $restaurants = $db->query("SELECT * FROM restaurants")->fetchAll();
    $totalDishes = 0;
    foreach ($restaurants as $r) {
        $stmt = $db->prepare("SELECT COUNT(*) as c FROM dishes WHERE restaurant_id = ?");
        $stmt->execute([$r['id']]);
        $totalDishes += $stmt->fetch()['c'];
    }
    echo json_encode(['success' => true, 'message' => "Sync completato: " . count($restaurants) . " ristoranti, $totalDishes piatti", 'restaurants' => count($restaurants), 'dishes' => $totalDishes], JSON_UNESCAPED_UNICODE);
}

function crudResolveRestaurantId($db, $key) {
    if (!$key) return null;
    $stmt = $db->prepare("SELECT id FROM restaurants WHERE id = ? OR slug = ? LIMIT 1");
    $stmt->execute([$key, $key]);
    $row = $stmt->fetch();
    return $row ? $row['id'] : null;
}

function crudGetRestaurant($db) {
    $id = $_GET['id'] ?? $_GET['slug'] ?? $_GET['restaurant_id'] ?? null;
    if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID mancante']); return; }
    $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ? OR slug = ?");
    $stmt->execute([$id, $id]);
    $restaurant = $stmt->fetch();
    if (!$restaurant) { http_response_code(404); echo json_encode(['error' => 'Ristorante non trovato']); return; }
    $catStmt = $db->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY display_order, name");
    $catStmt->execute([$restaurant['id']]);
    $restaurant['categories'] = $catStmt->fetchAll();
    $dishStmt = $db->prepare("SELECT COUNT(*) as c FROM dishes WHERE restaurant_id = ?");
    $dishStmt->execute([$restaurant['id']]);
    $restaurant['dishes_count'] = (int)$dishStmt->fetch()['c'];
    $menuStmt = $db->prepare("SELECT d.*, COALESCE(c.name, d.category, 'Vari') as category_name FROM dishes d LEFT JOIN categories c ON d.category_id = c.id WHERE d.restaurant_id = ? ORDER BY COALESCE(c.name, d.category, 'Vari'), d.name");
    $menuStmt->execute([$restaurant['id']]);
    $dishes = $menuStmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $restaurant, 'restaurant' => $restaurant, 'dishes' => $dishes], JSON_UNESCAPED_UNICODE);
}

function crudGetReviews($db) {
    $rid = $_GET['restaurant_id'] ?? $_GET['id'] ?? $_GET['slug'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id mancante']); return; }
    $realId = crudResolveRestaurantId($db, $rid) ?: $rid;
    try {
        $stmt = $db->prepare("SELECT r.*, d.name as dish_name FROM reviews r LEFT JOIN dishes d ON r.dish_id = d.id WHERE r.restaurant_id = ? ORDER BY r.created_at DESC LIMIT 50");
        $stmt->execute([$realId]);
        $reviews = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $reviews, 'reviews' => $reviews], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        echo json_encode(['success' => true, 'data' => [], 'reviews' => []]);
    }
}

function crudGetCategories($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'Restaurant ID mancante']); return; }
    $stmt = $db->prepare("SELECT * FROM categories WHERE restaurant_id = ? ORDER BY display_order, name");
    $stmt->execute([$rid]);
    $categories = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $categories], JSON_UNESCAPED_UNICODE);
}

function crudGetExampleMenu() {
    $example = [
        ['name' => 'Bruschetta al Pomodoro', 'description' => 'Pane tostato con pomodoro, aglio, basilico', 'price' => 5.00, 'category' => 'Antipasti'],
        ['name' => 'Caprese', 'description' => 'Mozzarella di bufala, pomodoro, basilico', 'price' => 8.00, 'category' => 'Antipasti'],
        ['name' => 'Spaghetti Carbonara', 'description' => 'Spaghetti, uova, pecorino, guanciale, pepe', 'price' => 10.00, 'category' => 'Primi'],
        ['name' => 'Risotto ai Funghi', 'description' => 'Riso carnaroli, funghi porcini, parmigiano', 'price' => 12.00, 'category' => 'Primi'],
        ['name' => 'Tagliata di Manzo', 'description' => 'Filetto di manzo alla griglia, rucola, parmigiano', 'price' => 18.00, 'category' => 'Secondi'],
        ['name' => 'Tiramisù', 'description' => 'Mascarpone, savoiardi, caffè, cacao', 'price' => 6.00, 'category' => 'Dolci'],
    ];
    echo json_encode(['success' => true, 'data' => $example], JSON_UNESCAPED_UNICODE);
}

function crudExportRestaurant($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'Restaurant ID mancante']); return; }
    $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ?");
    $stmt->execute([$rid]);
    $restaurant = $stmt->fetch();
    if (!$restaurant) { http_response_code(404); echo json_encode(['error' => 'Ristorante non trovato']); return; }
    $catStmt = $db->prepare("SELECT * FROM categories WHERE restaurant_id = ?");
    $catStmt->execute([$rid]);
    $restaurant['categories'] = $catStmt->fetchAll();
    $dishStmt = $db->prepare("SELECT * FROM dishes WHERE restaurant_id = ?");
    $dishStmt->execute([$rid]);
    $restaurant['dishes'] = $dishStmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $restaurant, 'exported_at' => date('Y-m-d H:i:s')], JSON_UNESCAPED_UNICODE);
}

function crudImportRestaurant($db) {
    $data = getInput();
    if (!$data) { http_response_code(400); echo json_encode(['error' => 'Dati mancanti']); return; }
    echo json_encode(['success' => true, 'message' => 'Import completato', 'imported' => 0], JSON_UNESCAPED_UNICODE);
}

// === ANALYTICS FUNCTIONS ===

function crudTrackView($db) {
    $data = getInput();
    if (!$data || empty($data['restaurant_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'restaurant_id mancante']);
        return;
    }
    $id = 'pv_' . bin2hex(random_bytes(8));
    $rid = crudResolveRestaurantId($db, $data['restaurant_id']) ?: $data['restaurant_id'];
    $dishId = $data['dish_id'] ?? null;
    $pageType = $data['page_type'] ?? 'menu';
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    try {
        $stmt = $db->prepare("INSERT INTO page_views (id, restaurant_id, dish_id, page_type, user_ip, user_agent, referrer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $rid, $dishId, $pageType, $ip, $ua, $ref]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => true]);
    }
}

function crudTrackSearch($db) {
    $data = getInput();
    if (!$data || empty($data['query'])) {
        http_response_code(400);
        echo json_encode(['error' => 'query mancante']);
        return;
    }
    $id = 'sl_' . bin2hex(random_bytes(8));
    $query = mb_substr(trim(strip_tags((string)$data['query'])), 0, 200);
    if ($query === '') { http_response_code(400); echo json_encode(['error' => 'query mancante']); return; }
    $count = max(0, min(100000, intval($data['results_count'] ?? 0)));
    $rid = $data['restaurant_id'] ?? null;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    try {
        $stmt = $db->prepare("INSERT INTO search_logs (id, query, results_count, restaurant_id, user_ip) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$id, $query, $count, $rid, $ip]);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => true]);
    }
}

function crudAnalyticsDashboard($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id mancante']); return; }
    $period = $_GET['period'] ?? '30';
    $since = date('Y-m-d H:i:s', strtotime("-$period days"));

    $stmt = $db->prepare("SELECT COUNT(*) as total, DATE(created_at) as date FROM page_views WHERE restaurant_id = ? AND created_at >= ? GROUP BY DATE(created_at) ORDER BY date");
    $stmt->execute([$rid, $since]);
    $viewsByDate = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT d.name, COUNT(pv.id) as views FROM page_views pv JOIN dishes d ON pv.dish_id = d.id WHERE pv.restaurant_id = ? AND pv.dish_id IS NOT NULL AND pv.created_at >= ? GROUP BY pv.dish_id ORDER BY views DESC LIMIT 10");
    $stmt->execute([$rid, $since]);
    $topDishes = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT query, COUNT(*) as count FROM search_logs WHERE created_at >= ? GROUP BY query ORDER BY count DESC LIMIT 10");
    $stmt->execute([$since]);
    $topSearches = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT COUNT(*) as total FROM page_views WHERE restaurant_id = ? AND created_at >= ?");
    $stmt->execute([$rid, $since]);
    $totalViews = $stmt->fetch()['total'];

    $stmt = $db->prepare("SELECT COUNT(*) as total FROM page_views WHERE restaurant_id = ? AND page_type = 'dish' AND created_at >= ?");
    $stmt->execute([$rid, $since]);
    $dishViews = $stmt->fetch()['total'];

    $stmt = $db->prepare("SELECT COUNT(DISTINCT user_ip) as unique_visitors FROM page_views WHERE restaurant_id = ? AND created_at >= ?");
    $stmt->execute([$rid, $since]);
    $uniqueVisitors = $stmt->fetch()['unique_visitors'];

    echo json_encode([
        'success' => true,
        'data' => [
            'total_views' => (int)$totalViews,
            'dish_views' => (int)$dishViews,
            'unique_visitors' => (int)$uniqueVisitors,
            'views_by_date' => $viewsByDate,
            'top_dishes' => $topDishes,
            'top_searches' => $topSearches,
            'period_days' => (int)$period
        ]
    ], JSON_UNESCAPED_UNICODE);
}

function crudAnalyticsViews($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id mancante']); return; }
    $period = $_GET['period'] ?? '30';
    $since = date('Y-m-d H:i:s', strtotime("-$period days"));
    $stmt = $db->prepare("SELECT DATE(created_at) as date, COUNT(*) as views FROM page_views WHERE restaurant_id = ? AND created_at >= ? GROUP BY DATE(created_at) ORDER BY date");
    $stmt->execute([$rid, $since]);
    $views = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $views], JSON_UNESCAPED_UNICODE);
}

function crudAnalyticsDishes($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id mancante']); return; }
    $period = $_GET['period'] ?? '30';
    $since = date('Y-m-d H:i:s', strtotime("-$period days"));
    $stmt = $db->prepare("SELECT d.id, d.name, d.category, d.price, COUNT(pv.id) as views FROM page_views pv JOIN dishes d ON pv.dish_id = d.id WHERE pv.restaurant_id = ? AND pv.dish_id IS NOT NULL AND pv.created_at >= ? GROUP BY pv.dish_id ORDER BY views DESC LIMIT 20");
    $stmt->execute([$rid, $since]);
    $dishes = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $dishes], JSON_UNESCAPED_UNICODE);
}

function crudAnalyticsSearch($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    $period = $_GET['period'] ?? '30';
    $since = date('Y-m-d H:i:s', strtotime("-$period days"));
    if ($rid) {
        $stmt = $db->prepare("SELECT query, COUNT(*) as count FROM search_logs WHERE restaurant_id = ? AND created_at >= ? GROUP BY query ORDER BY count DESC LIMIT 20");
        $stmt->execute([$rid, $since]);
    } else {
        $stmt = $db->prepare("SELECT query, COUNT(*) as count FROM search_logs WHERE created_at >= ? GROUP BY query ORDER BY count DESC LIMIT 20");
        $stmt->execute([$since]);
    }
    $searches = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $searches], JSON_UNESCAPED_UNICODE);
}

function crudAnalyticsCompare($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id mancante']); return; }
    $period = $_GET['period'] ?? '30';
    $since = date('Y-m-d H:i:s', strtotime("-$period days"));

    $stmt = $db->prepare("SELECT r.city FROM restaurants r WHERE r.id = ?");
    $stmt->execute([$rid]);
    $city = $stmt->fetch()['city'] ?? null;

    $myStats = $db->prepare("SELECT COUNT(*) as views, COUNT(DISTINCT user_ip) as unique_visitors FROM page_views WHERE restaurant_id = ? AND created_at >= ?");
    $myStats->execute([$rid, $since]);
    $my = $myStats->fetch();

    $myDishCount = $db->prepare("SELECT COUNT(*) as cnt FROM dishes WHERE restaurant_id = ?");
    $myDishCount->execute([$rid]);
    $myDishes = $myDishCount->fetch()['cnt'];

    $avgQuery = "SELECT AVG(v.total_views) as avg_views, AVG(v.unique_visitors) as avg_unique, AVG(v.dish_cnt) as avg_dishes FROM (SELECT pv.restaurant_id, COUNT(*) as total_views, COUNT(DISTINCT pv.user_ip) as unique_visitors, (SELECT COUNT(*) FROM dishes d WHERE d.restaurant_id = pv.restaurant_id) as dish_cnt FROM page_views pv JOIN restaurants r ON pv.restaurant_id = r.id WHERE pv.created_at >= ?";
    $avgParams = [$since];
    if ($city) { $avgQuery .= " AND r.city LIKE ?"; $avgParams[] = $city; }
    $avgQuery .= " GROUP BY pv.restaurant_id) v";
    $avgStmt = $db->prepare($avgQuery);
    $avgStmt->execute($avgParams);
    $avg = $avgStmt->fetch();

    $rankQuery = "SELECT pv.restaurant_id, COUNT(*) as views FROM page_views pv JOIN restaurants r ON pv.restaurant_id = r.id WHERE pv.created_at >= ?";
    $rankParams = [$since];
    if ($city) { $rankQuery .= " AND r.city LIKE ?"; $rankParams[] = $city; }
    $rankQuery .= " GROUP BY pv.restaurant_id ORDER BY views DESC";
    $rankStmt = $db->prepare($rankQuery);
    $rankStmt->execute($rankParams);
    $all = $rankStmt->fetchAll();
    $rank = 0;
    foreach ($all as $i => $row) { if ($row['restaurant_id'] === $rid) { $rank = $i + 1; break; } }

    $myDishViews = $db->prepare("SELECT d.category, COUNT(pv.id) as views FROM page_views pv JOIN dishes d ON pv.dish_id = d.id WHERE pv.restaurant_id = ? AND pv.created_at >= ? GROUP BY d.category ORDER BY views DESC");
    $myDishViews->execute([$rid, $since]);
    $myCategories = $myDishViews->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'restaurant_id' => $rid,
            'city' => $city,
            'my' => [
                'views' => (int)$my['views'],
                'unique_visitors' => (int)$my['unique_visitors'],
                'dishes' => (int)$myDishes
            ],
            'zone_avg' => [
                'views' => round(floatval($avg['avg_views'] ?? 0)),
                'unique_visitors' => round(floatval($avg['avg_unique'] ?? 0)),
                'dishes' => round(floatval($avg['avg_dishes'] ?? 0))
            ],
            'rank' => (int)$rank,
            'total_restaurants' => count($all),
            'my_categories' => $myCategories
        ]
    ], JSON_UNESCAPED_UNICODE);
}

function crudGetRestaurantOwners($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id mancante']); return; }
    $stmt = $db->prepare("SELECT ro.*, u.email, u.name as user_name FROM restaurant_owners ro JOIN users u ON ro.user_id = u.id WHERE ro.restaurant_id = ?");
    $stmt->execute([$rid]);
    $owners = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $owners], JSON_UNESCAPED_UNICODE);
}

// === CITY FUNCTIONS ===

function crudGetCities($db) {
    $stmt = $db->prepare("SELECT * FROM cities WHERE active = 1 ORDER BY restaurant_count DESC, name");
    $stmt->execute();
    $cities = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $cities], JSON_UNESCAPED_UNICODE);
}

function crudGetCity($db) {
    $slug = $_GET['slug'] ?? null;
    $id = $_GET['id'] ?? null;
    if (!$slug && !$id) { http_response_code(400); echo json_encode(['error' => 'slug o id mancante']); return; }
    if ($slug) {
        $stmt = $db->prepare("SELECT * FROM cities WHERE slug = ?");
        $stmt->execute([$slug]);
    } else {
        $stmt = $db->prepare("SELECT * FROM cities WHERE id = ?");
        $stmt->execute([$id]);
    }
    $city = $stmt->fetch();
    if (!$city) { http_response_code(404); echo json_encode(['error' => 'Città non trovata']); return; }
    echo json_encode(['success' => true, 'data' => $city], JSON_UNESCAPED_UNICODE);
}

function crudGetCityRestaurants($db) {
    $slug = $_GET['slug'] ?? null;
    if (!$slug) { http_response_code(400); echo json_encode(['error' => 'slug mancante']); return; }
    $stmt = $db->prepare("SELECT * FROM cities WHERE slug = ?");
    $stmt->execute([$slug]);
    $city = $stmt->fetch();
    if (!$city) { http_response_code(404); echo json_encode(['error' => 'Città non trovata']); return; }

    $restStmt = $db->prepare("SELECT r.*, (SELECT COUNT(*) FROM dishes d WHERE d.restaurant_id = r.id) as dishes_count FROM restaurants r WHERE r.city LIKE ? ORDER BY r.name");
    $restStmt->execute([$city['name']]);
    $restaurants = $restStmt->fetchAll();
    echo json_encode(['success' => true, 'city' => $city, 'data' => $restaurants], JSON_UNESCAPED_UNICODE);
}

// === SUBSCRIPTION FUNCTIONS ===

$SUBSCRIPTION_PLANS = [
    'free'       => ['name' => 'Free',       'price' => 0,    'max_dishes' => 10,  'analytics' => false, 'promotions' => false, 'orders' => false],
    'basic'      => ['name' => 'Basic',      'price' => 9.90, 'max_dishes' => 50,  'analytics' => true,  'promotions' => false, 'orders' => false],
    'pro'        => ['name' => 'Pro',        'price' => 29.90,'max_dishes' => -1,  'analytics' => true,  'promotions' => true,  'orders' => false],
    'enterprise' => ['name' => 'Enterprise', 'price' => 49.90,'max_dishes' => -1,  'analytics' => true,  'promotions' => true,  'orders' => true],
];

function crudGetSubscription($db) {
    global $SUBSCRIPTION_PLANS;
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id mancante']); return; }

    $stmt = $db->prepare("SELECT * FROM subscriptions WHERE restaurant_id = ?");
    $stmt->execute([$rid]);
    $sub = $stmt->fetch();

    if (!$sub) {
        // Auto-create free subscription
        $sid = 'sub_' . bin2hex(random_bytes(8));
        $db->prepare("INSERT INTO subscriptions (id, restaurant_id, plan, status, price_monthly, current_period_start, current_period_end) VALUES (?, ?, 'free', 'active', 0, NOW(), DATE_ADD(NOW(), INTERVAL 100 YEAR))")->execute([$sid, $rid]);
        $stmt->execute([$rid]);
        $sub = $stmt->fetch();
    }

    $plan = $SUBSCRIPTION_PLANS[$sub['plan']] ?? $SUBSCRIPTION_PLANS['free'];

    // Count current dishes
    $dishStmt = $db->prepare("SELECT COUNT(*) FROM dishes WHERE restaurant_id = ?");
    $dishStmt->execute([$rid]);
    $dishCount = $dishStmt->fetchColumn();

    $sub['plan_details'] = $plan;
    $sub['current_dishes'] = $dishCount;
    $sub['can_add_dish'] = $plan['max_dishes'] == -1 || $dishCount < $plan['max_dishes'];

    echo json_encode(['success' => true, 'data' => $sub], JSON_UNESCAPED_UNICODE);
}

function crudSubscribe($db) {
    global $SUBSCRIPTION_PLANS;
    $data = getInput();
    if (!$data || empty($data['restaurant_id']) || empty($data['plan'])) {
        http_response_code(400);
        echo json_encode(['error' => 'restaurant_id e plan richiesti']);
        return;
    }

    $rid = $data['restaurant_id'];
    $plan = $data['plan'];

    if (!isset($SUBSCRIPTION_PLANS[$plan])) {
        http_response_code(400);
        echo json_encode(['error' => 'Piano non valido. Opzioni: free, basic, pro, enterprise']);
        return;
    }

    $planDetails = $SUBSCRIPTION_PLANS[$plan];

    // Check dish limit
    $dishStmt = $db->prepare("SELECT COUNT(*) FROM dishes WHERE restaurant_id = ?");
    $dishStmt->execute([$rid]);
    $dishCount = $dishStmt->fetchColumn();

    if ($planDetails['max_dishes'] != -1 && $dishCount > $planDetails['max_dishes']) {
        http_response_code(400);
        echo json_encode(['error' => "Il piano {$planDetails['name']} supporta max {$planDetails['max_dishes']} piatti. Attualmente ne hai $dishCount."]);
        return;
    }

    // Update or create subscription
    $existing = $db->prepare("SELECT id FROM subscriptions WHERE restaurant_id = ?");
    $existing->execute([$rid]);
    $sub = $existing->fetch();

    if ($sub) {
        $stmt = $db->prepare("UPDATE subscriptions SET plan = ?, status = 'active', price_monthly = ?, current_period_start = NOW(), current_period_end = DATE_ADD(NOW(), INTERVAL 1 MONTH), updated_at = NOW() WHERE id = ?");
        $stmt->execute([$plan, $planDetails['price'], $sub['id']]);
        $sid = $sub['id'];
    } else {
        $sid = 'sub_' . bin2hex(random_bytes(8));
        $stmt = $db->prepare("INSERT INTO subscriptions (id, restaurant_id, plan, status, price_monthly, current_period_start, current_period_end) VALUES (?, ?, ?, 'active', ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))");
        $stmt->execute([$sid, $rid, $plan, $planDetails['price']]);
    }

    // Log payment (for paid plans)
    if ($planDetails['price'] > 0) {
        $pid = 'pay_' . bin2hex(random_bytes(8));
        $payStmt = $db->prepare("INSERT INTO payments (id, subscription_id, amount, currency, status, description) VALUES (?, ?, ?, 'EUR', 'succeeded', ?)");
        $payStmt->execute([$pid, $sid, $planDetails['price'], "Abbonamento {$planDetails['name']} - " . date('F Y')]);
    }

    echo json_encode(['success' => true, 'plan' => $plan, 'message' => "Piano {$planDetails['name']} attivato con successo"], JSON_UNESCAPED_UNICODE);
}

function crudCancelSubscription($db) {
    $data = getInput();
    if (!$data || empty($data['restaurant_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'restaurant_id mancante']);
        return;
    }

    $rid = $data['restaurant_id'];
    $stmt = $db->prepare("UPDATE subscriptions SET status = 'canceled', updated_at = NOW() WHERE restaurant_id = ? AND plan != 'free'");
    $stmt->execute([$rid]);

    if ($stmt->rowCount() > 0) {
        // Downgrade to free
        $db->prepare("UPDATE subscriptions SET plan = 'free', status = 'active', price_monthly = 0, current_period_end = DATE_ADD(NOW(), INTERVAL 100 YEAR) WHERE restaurant_id = ?")->execute([$rid]);
        echo json_encode(['success' => true, 'message' => 'Abbonamento cancellato. Piano Free attivato.']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Nessun abbonamento attivo da cancellare']);
    }
}

function crudGetPayments($db) {
    $rid = $_GET['restaurant_id'] ?? null;
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id mancante']); return; }

    $stmt = $db->prepare("SELECT p.* FROM payments p JOIN subscriptions s ON p.subscription_id = s.id WHERE s.restaurant_id = ? ORDER BY p.created_at DESC LIMIT 50");
    $stmt->execute([$rid]);
    $payments = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $payments], JSON_UNESCAPED_UNICODE);
}

// === AGENT FUNCTIONS ===

function crudGetAgents($db) {
    $stmt = $db->prepare("SELECT * FROM agents ORDER BY name");
    $stmt->execute();
    $agents = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $agents], JSON_UNESCAPED_UNICODE);
}

function crudAgentDashboard($db) {
    $aid = $_GET['agent_id'] ?? null;
    $period = $_GET['period'] ?? '30';
    $since = date('Y-m-d H:i:s', strtotime("-$period days"));

    if ($aid) {
        // Single agent dashboard
        $stmt = $db->prepare("SELECT * FROM agents WHERE id = ?");
        $stmt->execute([$aid]);
        $agent = $stmt->fetch();
        if (!$agent) { http_response_code(404); echo json_encode(['error' => 'Agente non trovato']); return; }

        $refStmt = $db->prepare("SELECT ar.*, r.name as restaurant_name, r.city FROM agent_referrals ar JOIN restaurants r ON ar.restaurant_id = r.id WHERE ar.agent_id = ? ORDER BY ar.referred_at DESC");
        $refStmt->execute([$aid]);
        $referrals = $refStmt->fetchAll();

        $earnStmt = $db->prepare("SELECT SUM(amount) as total, COUNT(*) as count FROM agent_commissions WHERE agent_id = ? AND created_at >= ?");
        $earnStmt->execute([$aid, $since]);
        $earnings = $earnStmt->fetch();

        $tierThresholds = ['bronze' => 5, 'silver' => 10, 'gold' => 20, 'platinum' => 30];
        $tierBonuses = ['bronze' => 250, 'silver' => 600, 'gold' => 1500, 'platinum' => 3000];

        echo json_encode(['success' => true, 'data' => [
            'agent' => $agent,
            'referrals' => $referrals,
            'period_earnings' => $earnings['total'] ?? 0,
            'period_referrals' => $earnings['count'] ?? 0,
            'tier_thresholds' => $tierThresholds,
            'tier_bonuses' => $tierBonuses
        ]], JSON_UNESCAPED_UNICODE);
    } else {
        // All agents overview
        $stmt = $db->query("SELECT a.*, (SELECT COUNT(*) FROM agent_referrals ar WHERE ar.agent_id = a.id) as total_referrals FROM agents a ORDER BY a.total_earnings DESC");
        $agents = $stmt->fetchAll();

        $totalPaid = $db->query("SELECT SUM(amount) as total FROM agent_commissions WHERE status = 'paid'")->fetch()['total'] ?? 0;
        $totalPending = $db->query("SELECT SUM(amount) as total FROM agent_commissions WHERE status = 'pending'")->fetch()['total'] ?? 0;

        echo json_encode(['success' => true, 'data' => [
            'agents' => $agents,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending
        ]], JSON_UNESCAPED_UNICODE);
    }
}

function crudAgentCommissions($db) {
    $aid = $_GET['agent_id'] ?? null;
    if (!$aid) { http_response_code(400); echo json_encode(['error' => 'agent_id mancante']); return; }

    $stmt = $db->prepare("SELECT ac.*, r.name as restaurant_name FROM agent_commissions ac LEFT JOIN restaurants r ON ac.restaurant_id = r.id WHERE ac.agent_id = ? ORDER BY ac.created_at DESC LIMIT 50");
    $stmt->execute([$aid]);
    $commissions = $stmt->fetchAll();
    echo json_encode(['success' => true, 'data' => $commissions], JSON_UNESCAPED_UNICODE);
}

function crudAddAgent($db) {
    $data = getInput();
    if (!$data || empty($data['name'])) { http_response_code(400); echo json_encode(['error' => 'Nome agente richiesto']); return; }

    $id = 'agent_' . bin2hex(random_bytes(8));
    $stmt = $db->prepare("INSERT INTO agents (id, user_id, name, email, phone, zone, tier, commission_rate) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $id,
        $data['user_id'] ?? null,
        $data['name'],
        $data['email'] ?? null,
        $data['phone'] ?? null,
        $data['zone'] ?? null,
        $data['tier'] ?? 'bronze',
        $data['commission_rate'] ?? 10.00
    ]);
    echo json_encode(['success' => true, 'id' => $id, 'message' => 'Agente creato'], JSON_UNESCAPED_UNICODE);
}

function crudAssignAgent($db) {
    $data = getInput();
    if (!$data || empty($data['agent_id']) || empty($data['restaurant_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'agent_id e restaurant_id richiesti']);
        return;
    }

    $id = 'ref_' . bin2hex(random_bytes(8));
    try {
        $stmt = $db->prepare("INSERT INTO agent_referrals (id, agent_id, restaurant_id) VALUES (?, ?, ?)");
        $stmt->execute([$id, $data['agent_id'], $data['restaurant_id']]);

        // Update agent referral count
        $db->prepare("UPDATE agents SET total_referrals = total_referrals + 1 WHERE id = ?")->execute([$data['agent_id']]);

        echo json_encode(['success' => true, 'message' => 'Ristorante assegnato all\'agente']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => 'Già assegnato o errore: ' . $e->getMessage()]);
    }
}

// === ORDERS & RESERVATIONS ===
function crudCreateOrder($db) {
    $data = getInput();
    if (!$data || empty($data['restaurant_id']) || empty($data['customer_name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'restaurant_id e customer_name richiesti']);
        return;
    }
    $id = 'ord_' . bin2hex(random_bytes(8));
    $type = $data['type'] ?? 'dinein';
    $notes = $data['notes'] ?? null;
    $resDate = $data['reservation_date'] ?? null;
    $resTime = $data['reservation_time'] ?? null;
    $guests = intval($data['guests'] ?? 0);
    $total = 0;
    try {
        $stmt = $db->prepare("INSERT INTO orders (id, restaurant_id, customer_name, customer_phone, customer_email, type, status, total, notes, reservation_date, reservation_time, guests) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)");
        $stmt->execute([$id, $data['restaurant_id'], $data['customer_name'], $data['customer_phone'] ?? null, $data['customer_email'] ?? null, $type, $total, $notes, $resDate, $resTime, $guests]);
        $items = $data['items'] ?? [];
        foreach ($items as $item) {
            $iid = 'oi_' . bin2hex(random_bytes(8));
            $qty = intval($item['quantity'] ?? 1);
            $price = floatval($item['unit_price'] ?? 0);
            $itemTotal = $qty * $price;
            $total += $itemTotal;
            $db->prepare("INSERT INTO order_items (id, order_id, dish_id, dish_name, quantity, unit_price, total_price, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")->execute([$iid, $id, $item['dish_id'] ?? null, $item['dish_name'] ?? '', $qty, $price, $itemTotal, $item['notes'] ?? null]);
        }
        $db->prepare("UPDATE orders SET total = ? WHERE id = ?")->execute([$total, $id]);
        sendOrderEmail($db, $id);
        echo json_encode(['success' => true, 'order_id' => $id, 'total' => $total]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function crudGetOrder($db) {
    $id = $_GET['id'] ?? '';
    $phone = $_GET['phone'] ?? '';
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($phone) {
        $stmt = $db->prepare("SELECT * FROM orders WHERE customer_phone = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$phone]);
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'id o phone richiesto']);
        return;
    }
    $order = $stmt->fetchAll();
    if ($id && !empty($order)) {
        $order = $order[0];
        $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
        $items->execute([$id]);
        $order['items'] = $items->fetchAll();
    }
    echo json_encode(['success' => true, 'orders' => $order]);
}

function crudUpdateOrderStatus($db) {
    verifyCrudToken();
    $data = getInput();
    if (!$data || empty($data['order_id']) || empty($data['status'])) {
        http_response_code(400);
        echo json_encode(['error' => 'order_id e status richiesti']);
        return;
    }
    $valid = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'canceled'];
    if (!in_array($data['status'], $valid)) {
        http_response_code(400);
        echo json_encode(['error' => 'Status non valido. Usa: ' . implode(', ', $valid)]);
        return;
    }
    $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$data['status'], $data['order_id']]);
    echo json_encode(['success' => true, 'message' => 'Ordine aggiornato']);
}

function crudGetRestaurantOrders($db) {
    verifyCrudToken();
    $rid = $_GET['restaurant_id'] ?? '';
    $status = $_GET['status'] ?? '';
    if (!$rid) {
        http_response_code(400);
        echo json_encode(['error' => 'restaurant_id richiesto']);
        return;
    }
    $sql = "SELECT * FROM orders WHERE restaurant_id = ?";
    $params = [$rid];
    if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
    $sql .= " ORDER BY created_at DESC LIMIT 100";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();
    echo json_encode(['success' => true, 'orders' => $orders]);
}

function crudCreateReservation($db) {
    $data = getInput();
    if (!$data || empty($data['restaurant_id']) || empty($data['customer_name']) || empty($data['res_date']) || empty($data['res_time'])) {
        http_response_code(400);
        echo json_encode(['error' => 'restaurant_id, customer_name, res_date, res_time richiesti']);
        return;
    }
    $realId = crudResolveRestaurantId($db, $data['restaurant_id']);
    if (!$realId) { http_response_code(404); echo json_encode(['error' => 'Ristorante non trovato']); return; }
    $name = crudCleanText($data['customer_name'], 150);
    $phone = crudCleanText($data['customer_phone'] ?? '', 30);
    $email = crudCleanText($data['customer_email'] ?? '', 150);
    $notes = crudCleanText($data['notes'] ?? '', 1000);
    if (!$name || !$phone) { http_response_code(400); echo json_encode(['error' => 'Nome e telefono obbligatori']); return; }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); echo json_encode(['error' => 'Email non valida']); return; }
    $date = $data['res_date'];
    $time = $data['res_time'];
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || !preg_match('/^\d{2}:\d{2}$/', $time)) {
        http_response_code(400);
        echo json_encode(['error' => 'Data o ora non valide']);
        return;
    }
    if ($date < date('Y-m-d')) { http_response_code(400); echo json_encode(['error' => 'Data nel passato']); return; }
    $guests = max(1, min(20, intval($data['guests'] ?? 1)));
    $id = 'res_' . bin2hex(random_bytes(8));
    try {
        $stmt = $db->prepare("INSERT INTO reservations (id, restaurant_id, customer_name, customer_phone, customer_email, res_date, res_time, guests, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'confirmed', ?)");
        $stmt->execute([$id, $realId, $name, $phone, $email, $date, $time, $guests, $notes]);
        try { sendReservationEmail($db, $id); } catch (Exception $e) { error_log('Reservation email failed: ' . $e->getMessage()); }
        echo json_encode(['success' => true, 'reservation_id' => $id]);
    } catch (Exception $e) {
        error_log('Create reservation failed: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Errore nel salvataggio. Riprova.']);
    }
}

function crudGetRestaurantReservations($db) {
    verifyCrudToken();
    $rid = $_GET['restaurant_id'] ?? '';
    $date = $_GET['date'] ?? '';
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id richiesto']); return; }
    $sql = "SELECT * FROM reservations WHERE restaurant_id = ?";
    $params = [$rid];
    if ($date) { $sql .= " AND res_date = ?"; $params[] = $date; }
    $sql .= " ORDER BY res_date, res_time DESC LIMIT 100";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'reservations' => $stmt->fetchAll()]);
}

function crudUpdateReservationStatus($db) {
    verifyCrudToken();
    $data = getInput();
    if (!$data || empty($data['reservation_id']) || empty($data['status'])) { http_response_code(400); echo json_encode(['error' => 'reservation_id e status richiesti']); return; }
    $valid = ['pending', 'confirmed', 'canceled'];
    if (!in_array($data['status'], $valid)) { http_response_code(400); echo json_encode(['error' => 'Status non valido']); return; }
    $db->prepare("UPDATE reservations SET status = ?, updated_at = NOW() WHERE id = ?")->execute([$data['status'], $data['reservation_id']]);
    echo json_encode(['success' => true, 'message' => 'Prenotazione aggiornata']);
}

function crudGetRestaurantAvailability($db) {
    $rid = $_GET['restaurant_id'] ?? '';
    if (!$rid) { http_response_code(400); echo json_encode(['error' => 'restaurant_id richiesto']); return; }
    $stmt = $db->prepare("SELECT available_hours FROM restaurants WHERE id = ?");
    $stmt->execute([$rid]);
    $row = $stmt->fetch();
    $hours = $row && $row['available_hours'] ? json_decode($row['available_hours'], true) : null;
    echo json_encode(['success' => true, 'availability' => $hours]);
}

function crudGetAvailableSlots($db) {
    $rid = $_GET['restaurant_id'] ?? '';
    $date = $_GET['date'] ?? '';
    if (!$rid || !$date) { http_response_code(400); echo json_encode(['error' => 'restaurant_id e date richiesti']); return; }

    // Get restaurant hours
    $stmt = $db->prepare("SELECT available_hours FROM restaurants WHERE id = ?");
    $stmt->execute([$rid]);
    $row = $stmt->fetch();
    $hours = $row && $row['available_hours'] ? json_decode($row['available_hours'], true) : null;

    if (!$hours) { echo json_encode(['success' => true, 'slots' => [], 'message' => 'Nessun orario configurato']); return; }

    // Get day of week
    $dayMap = ['mon'=>1,'tue'=>2,'wed'=>3,'thu'=>4,'fri'=>5,'sat'=>6,'sun'=>0];
    $dayOfWeek = date('w', strtotime($date));
    $dayKey = array_search($dayOfWeek, $dayMap);
    $dayHours = $hours[$dayKey] ?? null;

    if (!$dayHours) { echo json_encode(['success' => true, 'slots' => [], 'message' => 'Chiuso in questo giorno']); return; }

    // Generate all possible 30-min slots
    $allSlots = [];
    foreach (['lunch', 'dinner'] as $meal) {
        if (empty($dayHours[$meal])) continue;
        list($open, $close) = explode('-', $dayHours[$meal]);
        $current = strtotime($date . ' ' . $open);
        $end = strtotime($date . ' ' . $close);
        while ($current < $end) {
            $allSlots[] = date('H:i', $current);
            $current += 30 * 60;
        }
    }

    // Get existing reservations for that date
    $stmt = $db->prepare("SELECT res_time FROM reservations WHERE restaurant_id = ? AND res_date = ? AND status != 'canceled'");
    $stmt->execute([$rid, $date]);
    $booked = [];
    while ($r = $stmt->fetch()) {
        $booked[] = substr($r['res_time'], 0, 5);
    }

    // Filter out booked slots
    $available = [];
    foreach ($allSlots as $slot) {
        if (!in_array($slot, $booked)) {
            $available[] = $slot;
        }
    }

    echo json_encode(['success' => true, 'slots' => $available, 'booked' => $booked, 'day' => $dayKey, 'hours' => $dayHours]);
}

// === REVIEWS ===
function crudCleanText($v, $max) {
    if (!is_string($v)) return null;
    $v = trim(strip_tags($v));
    if ($v === '') return null;
    return mb_substr($v, 0, $max);
}

function crudCleanUrl($v) {
    if (!is_string($v) || trim($v) === '') return null;
    $v = trim($v);
    if (!preg_match('#^https?://#i', $v)) return null;
    if (strlen($v) > 500) return null;
    return $v;
}

function crudAddReview($db) {
    $data = getInput();
    if (!$data || empty($data['restaurant_id']) || empty($data['rating'])) {
        http_response_code(400);
        echo json_encode(['error' => 'restaurant_id e rating richiesti']);
        return;
    }
    $realId = crudResolveRestaurantId($db, $data['restaurant_id']);
    if (!$realId) { http_response_code(404); echo json_encode(['error' => 'Ristorante non trovato']); return; }
    $id = 'rev_' . bin2hex(random_bytes(8));
    $userName = crudCleanText($data['user_name'] ?? 'Anonimo', 100) ?? 'Anonimo';
    $userAvatar = crudCleanUrl($data['user_avatar'] ?? null);
    $dishId = is_string($data['dish_id'] ?? null) ? mb_substr($data['dish_id'], 0, 100) : null;
    $comment = crudCleanText($data['comment'] ?? '', 2000) ?? '';
    if ($comment === '') { http_response_code(400); echo json_encode(['error' => 'Commento vuoto']); return; }
    $rating = min(5, max(1, floatval($data['rating'])));
    $photoUrl = crudCleanUrl($data['photo_url'] ?? null);

    $stmt = $db->prepare("INSERT INTO reviews (id, restaurant_id, dish_id, user_name, user_avatar, rating, comment, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $realId, $dishId, $userName, $userAvatar, $rating, $comment, $photoUrl]);
    echo json_encode(['success' => true, 'review_id' => $id]);
}

// === EMAIL NOTIFICATIONS ===
function sendOrderEmail($db, $orderId) {
    $stmt = $db->prepare("SELECT o.*, r.name as restaurant_name, r.email as restaurant_email, r.phone as restaurant_phone FROM orders o JOIN restaurants r ON o.restaurant_id = r.id WHERE o.id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    if (!$order || !$order['restaurant_email']) return;

    $items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items->execute([$orderId]);
    $itemList = $items->fetchAll();

    $to = $order['restaurant_email'];
    $subject = "Nuovo ordine #" . substr($orderId, -8) . " - " . $order['customer_name'];
    $typeLabel = ['dinein' => 'Al tavolo', 'takeaway' => 'Asporto', 'delivery' => 'Delivery'];
    $body = "Nuovo ordine ricevuto!\n\n";
    $body .= "Cliente: {$order['customer_name']}\n";
    $body .= "Telefono: {$order['customer_phone']}\n";
    $body .= "Tipo: " . ($typeLabel[$order['type']] ?? $order['type']) . "\n";
    $body .= "Totale: €" . number_format($order['total'], 2) . "\n\n";
    $body .= "Piatti:\n";
    foreach ($itemList as $item) {
        $body .= "- {$item['quantity']}x {$item['dish_name']} €" . number_format($item['total_price'], 2) . "\n";
    }
    if ($order['notes']) $body .= "\nNote: {$order['notes']}\n";
    $body .= "\nGestisci l'ordine: https://www.trovapiatto.it/app/admin/reservations-dashboard.html";

    $headers = "From: trovapiatto <noreply@trovapiatto.it>\r\nContent-Type: text/plain; charset=UTF-8";
    @mail($to, $subject, $body, $headers);
}

function sendReservationEmail($db, $resId) {
    $stmt = $db->prepare("SELECT res.*, r.name as restaurant_name, r.email as restaurant_email FROM reservations res JOIN restaurants r ON res.restaurant_id = r.id WHERE res.id = ?");
    $stmt->execute([$resId]);
    $res = $stmt->fetch();
    if (!$res || !$res['restaurant_email']) return;

    $to = $res['restaurant_email'];
    $subject = "Nuova prenotazione - {$res['customer_name']}";
    $body = "Nuova prenotazione!\n\n";
    $body .= "Cliente: {$res['customer_name']}\n";
    $body .= "Telefono: {$res['customer_phone']}\n";
    $body .= "Email: {$res['customer_email']}\n";
    $body .= "Data: {$res['res_date']}\n";
    $body .= "Ora: {$res['res_time']}\n";
    $body .= "Ospiti: {$res['guests']}\n";
    if ($res['notes']) $body .= "Note: {$res['notes']}\n";
    $body .= "\nGestisci: https://www.trovapiatto.it/app/admin/reservations-dashboard.html";

    $headers = "From: trovapiatto <noreply@trovapiatto.it>\r\nContent-Type: text/plain; charset=UTF-8";
    @mail($to, $subject, $body, $headers);
}

// === ROUTING ===
$action = $_GET['action'] ?? '';

switch($action) {
    // Public endpoints
    case 'get-restaurants':
        crudGetRestaurants($db);
        break;
    case 'get-restaurant':
        crudGetRestaurant($db);
        break;
    case 'get-restaurant-menu':
    case 'get-menu':
        crudGetRestaurantMenu($db);
        break;
    case 'get-categories':
        crudGetCategories($db);
        break;
    case 'get-all-dishes':
        crudGetAllDishes($db);
        break;
    case 'get-example-menu':
        crudGetExampleMenu();
        break;
    case 'add-review':
        crudAddReview($db);
        break;
    case 'get-reviews':
    case 'get-restaurant-reviews':
        crudGetReviews($db);
        break;
    case 'export-data':
    case 'export':
        crudExportData($db);
        break;
    case 'import':
        verifyCrudToken();
        crudImportRestaurant($db);
        break;
    case 'debug':
        echo json_encode(['success' => true, 'message' => 'api-crud.php online', 'version' => '1.0']);
        break;

    // Protected endpoints
    case 'create-restaurant':
        verifyCrudToken();
        crudCreateRestaurant($db);
        break;
    case 'update-restaurant':
        verifyCrudToken();
        crudUpdateRestaurant($db);
        break;
    case 'update-slug':
        verifyCrudToken();
        crudUpdateSlug($db);
        break;
    case 'delete-restaurant':
        verifyCrudToken();
        crudDeleteRestaurant($db);
        break;
    case 'add-category':
    case 'add-categoria':
        verifyCrudToken();
        crudAddCategory($db);
        break;
    case 'update-category':
        verifyCrudToken();
        crudUpdateCategory($db);
        break;
    case 'delete-category':
        verifyCrudToken();
        crudDeleteCategory($db);
        break;
    case 'add-dish':
    case 'add-item':
        verifyCrudToken();
        crudAddDish($db);
        break;
    case 'clone-dish':
        verifyCrudToken();
        crudCloneDish($db);
        break;
    case 'update-dish':
    case 'update-item':
        verifyCrudToken();
        crudUpdateDish($db);
        break;
    case 'delete-dish':
        verifyCrudToken();
        crudDeleteDish($db);
        break;
    case 'upload-image':
    case 'upload-dish-image':
        verifyCrudToken();
        crudUploadImage();
        break;
    case 'sync-categories':
        verifyCrudToken();
        crudSyncCategories($db);
        break;
    case 'cleanup':
        verifyCrudToken();
        crudCleanup($db);
        break;
    case 'run-ocr':
        verifyCrudToken();
        crudRunOCR();
        break;
    case 'sync-data':
        verifyCrudToken();
        crudSyncData($db);
        break;

    // Analytics endpoints (public write + protected read)
    case 'track-view':
        crudTrackView($db);
        break;
    case 'track-search':
        crudTrackSearch($db);
        break;
    case 'analytics-dashboard':
        verifyCrudToken();
        crudAnalyticsDashboard($db);
        break;
    case 'analytics-views':
        verifyCrudToken();
        crudAnalyticsViews($db);
        break;
    case 'analytics-dishes':
        verifyCrudToken();
        crudAnalyticsDishes($db);
        break;
    case 'analytics-search':
        verifyCrudToken();
        crudAnalyticsSearch($db);
        break;
    case 'analytics-compare':
        verifyCrudToken();
        crudAnalyticsCompare($db);
        break;
    case 'get-restaurant-owners':
        verifyCrudToken();
        crudGetRestaurantOwners($db);
        break;

    // City endpoints (public)
    case 'get-cities':
        crudGetCities($db);
        break;
    case 'get-city':
        crudGetCity($db);
        break;
    case 'get-city-restaurants':
        crudGetCityRestaurants($db);
        break;

    // Subscription endpoints
    case 'get-subscription':
        verifyCrudToken();
        crudGetSubscription($db);
        break;
    case 'subscribe':
        verifyCrudToken();
        crudSubscribe($db);
        break;
    case 'cancel-subscription':
        verifyCrudToken();
        crudCancelSubscription($db);
        break;
    case 'get-payments':
        verifyCrudToken();
        crudGetPayments($db);
        break;

    // Agent endpoints
    case 'get-agents':
        verifyCrudToken();
        crudGetAgents($db);
        break;
    case 'get-agent-dashboard':
        verifyCrudToken();
        crudAgentDashboard($db);
        break;
    case 'get-agent-commissions':
        verifyCrudToken();
        crudAgentCommissions($db);
        break;
    case 'add-agent':
        verifyCrudToken();
        crudAddAgent($db);
        break;
    case 'assign-agent':
        verifyCrudToken();
        crudAssignAgent($db);
        break;

    // Orders & Reservations
    case 'create-order':
        crudCreateOrder($db);
        break;
    case 'get-order':
        crudGetOrder($db);
        break;
    case 'update-order-status':
        crudUpdateOrderStatus($db);
        break;
    case 'get-restaurant-orders':
        crudGetRestaurantOrders($db);
        break;
    case 'create-reservation':
        crudCreateReservation($db);
        break;
    case 'get-restaurant-reservations':
        crudGetRestaurantReservations($db);
        break;
    case 'update-reservation-status':
        crudUpdateReservationStatus($db);
        break;
    case 'get-restaurant-availability':
        crudGetRestaurantAvailability($db);
        break;
    case 'get-available-slots':
        crudGetAvailableSlots($db);
        break;

    case 'export-restaurants-csv':
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="ristoranti-trovapiatto-' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID', 'Nome', 'Slug', 'Cucina', 'Indirizzo', 'Città', 'Telefono', 'Email', 'Descrizione', 'Latitudine', 'Longitudine', 'Stelle Michelin', 'Logo URL', 'Data Creazione']);
        $stmt = $db->query("SELECT id, name, slug, cuisine, address, city, phone, email, description, latitude, longitude, michelin_stars, logo_url, created_at FROM restaurants ORDER BY name");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, array_values($row));
        }
        fclose($output);
        exit;

    case 'export-dishes-csv':
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="piatti-trovapiatto-' . date('Y-m-d') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, ['ID Piatto', 'ID Ristorante', 'Nome Ristorante', 'Nome Piatto', 'Prezzo', 'Categoria', 'Descrizione', 'Allergeni', 'Foto URL', 'Data Creazione']);
        $stmt = $db->query("SELECT d.id, d.restaurant_id, r.name, d.name, d.price, d.category, d.description, d.allergens, d.photo_url, d.created_at FROM dishes d LEFT JOIN restaurants r ON d.restaurant_id = r.id ORDER BY r.name, d.category, d.name");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, array_values($row));
        }
        fclose($output);
        exit;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Azione non trovata: ' . $action]);
        break;
}
