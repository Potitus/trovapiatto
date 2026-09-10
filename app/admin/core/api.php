<?php
require_once __DIR__ . '/../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
tp_cors_headers('GET, POST, PUT, DELETE, OPTIONS', 'Content-Type, Authorization');

try {
    $db = tp_db_connect();
    
    createTables($db);
    ensureAllergensColumn($db);
    ensureCategoryIdColumn($db);
    
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Se POST senza action in GET, prendi da POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($action)) {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
    }
    
    switch($action) {
        case 'get-restaurants':
            getRestaurants($db);
            break;
        case 'debug':
            echo json_encode(['success' => true, 'message' => 'API online', 'db_connected' => true, 'version' => '2.0-ocr', 'has_import_ocr' => true]);
            break;
        case 'create-restaurant':
            createRestaurant($db);
            break;
        case 'update-restaurant':
            updateRestaurant($db);
            break;
        case 'delete-restaurant':
            deleteRestaurant($db);
            break;
        case 'get-restaurant':
            getRestaurant($db);
            break;
        case 'get-categories':
            getCategories($db);
            break;
        case 'add-category':
            addCategory($db);
            break;
        case 'update-category':
            updateCategory($db);
            break;
        case 'delete-category':
            deleteCategory($db);
            break;
        case 'get-menu':
            getMenu($db);
            break;
        case 'get-items':
            getItems($db);
            break;
        case 'add-dish':
            addDish($db);
            break;
        case 'add-item':
            addDish($db); // Alias per compatibilità
            break;
        case 'update-dish':
            updateDish($db);
            break;
        case 'update-item':
            updateDish($db); // Alias per compatibilità
            break;
        case 'delete-dish':
            deleteDish($db);
            break;
        case 'upload-image':
            uploadImage();
            break;
        case 'get-example-menu':
            getExampleMenu();
            break;
        case 'sync-categories':
            syncCategories($db);
            break;
        case 'run-ocr':
            runOCROnImage();
            break;
        case 'import-menu-ocr':
            importMenuOCR($db);
            break;
        case 'import-menu-dishes':
            importMenuDishes($db);
            break;
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Azione non trovata']);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

function createTables($db) {
    $db->exec("CREATE TABLE IF NOT EXISTS restaurants (
        id VARCHAR(100) PRIMARY KEY,
        name VARCHAR(255) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description LONGTEXT,
        address VARCHAR(500),
        phone VARCHAR(20),
        email VARCHAR(255),
        logo_url VARCHAR(500),
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_slug (slug)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $db->exec("CREATE TABLE IF NOT EXISTS categories (
        id VARCHAR(100) PRIMARY KEY,
        restaurant_id VARCHAR(100) NOT NULL,
        name VARCHAR(255) NOT NULL,
        description LONGTEXT,
        display_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_restaurant (restaurant_id),
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
        UNIQUE KEY unique_category (restaurant_id, name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $db->exec("CREATE TABLE IF NOT EXISTS dishes (
        id VARCHAR(100) PRIMARY KEY,
        restaurant_id VARCHAR(100) NOT NULL,
        category_id VARCHAR(100),
        category VARCHAR(100),
        name VARCHAR(255) NOT NULL,
        description LONGTEXT,
        price DECIMAL(8, 2),
        image_url VARCHAR(500),
        available BOOLEAN DEFAULT true,
        allergens JSON,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_restaurant (restaurant_id),
        INDEX idx_category (category_id),
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function ensureAllergensColumn($db) {
    try {
        $result = $db->query("SHOW COLUMNS FROM dishes LIKE 'allergens'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE dishes ADD COLUMN allergens JSON DEFAULT NULL");
            error_log("✅ Colonna 'allergens' aggiunta a 'dishes'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna allergens: " . $e->getMessage());
    }
}

function ensureCategoryIdColumn($db) {
    try {
        $result = $db->query("SHOW COLUMNS FROM dishes LIKE 'category_id'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE dishes ADD COLUMN category_id VARCHAR(100) DEFAULT NULL");
            $db->exec("ALTER TABLE dishes ADD INDEX idx_category (category_id)");
            $db->exec("ALTER TABLE dishes ADD FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL");
            error_log("✅ Colonna 'category_id' aggiunta a 'dishes'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna category_id: " . $e->getMessage());
    }
}

function getRestaurants($db) {
    $stmt = $db->prepare("SELECT * FROM restaurants ORDER BY created_at DESC");
    $stmt->execute();
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
}

function getRestaurant($db) {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    $stmt = $db->prepare("SELECT * FROM restaurants WHERE id = ? OR slug = ?");
    $stmt->execute([$id, $id]);
    $data = $stmt->fetch();
    echo json_encode(['success' => $data ? true : false, 'data' => $data], JSON_UNESCAPED_UNICODE);
}

function createRestaurant($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome mancante']);
        return;
    }
    
    $slug = strtolower(preg_replace('/[^a-z0-9-]/', '-', preg_replace('/[àáâãäå]/i', 'a', preg_replace('/[èéêë]/i', 'e', preg_replace('/[ìíîï]/i', 'i', preg_replace('/[òóôõö]/i', 'o', preg_replace('/[ùúûü]/i', 'u', $data['name'])))))));
    $slug = trim($slug, '-');
    $id = uniqid('rest_');
    
    $stmt = $db->prepare("INSERT INTO restaurants (id, name, slug, description, address, phone, email, logo_url, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $data['name'], $slug, $data['description'] ?? null, $data['address'] ?? null, $data['phone'] ?? null, $data['email'] ?? null, $data['logo_url'] ?? null, $data['latitude'] ?? null, $data['longitude'] ?? null]);
    
    $menuCreate = createMenuFolder($slug, $data['name']);
    
    echo json_encode(['success' => true, 'id' => $id, 'slug' => $slug, 'menu_created' => $menuCreate], JSON_UNESCAPED_UNICODE);
}

function createMenuFolder($slug, $restaurantName) {
    try {
        $menuPath = dirname(__DIR__) . '/menu/' . $slug;
        if (!is_dir($menuPath)) {
            mkdir($menuPath, 0755, true);
        }
        $menuFile = $menuPath . '/index.html';
        if (!file_exists($menuFile)) {
            $template = getMenuTemplate($slug, $restaurantName);
            file_put_contents($menuFile, $template);
        }
        return true;
    } catch(Exception $e) {
        error_log("MENU CREATE ERROR: " . $e->getMessage());
        return false;
    }
}

function updateRestaurant($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    
    $stmt = $db->prepare("UPDATE restaurants SET name = ?, description = ?, address = ?, phone = ?, email = ?, logo_url = ?, latitude = ?, longitude = ? WHERE id = ?");
    $result = $stmt->execute([$data['name'] ?? null, $data['description'] ?? null, $data['address'] ?? null, $data['phone'] ?? null, $data['email'] ?? null, $data['logo_url'] ?? null, $data['latitude'] ?? null, $data['longitude'] ?? null, $data['id']]);
    
    echo json_encode(['success' => $result], JSON_UNESCAPED_UNICODE);
}

function deleteRestaurant($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    $stmt = $db->prepare("DELETE FROM restaurants WHERE id = ?");
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
}

function getMenu($db) {
    $rid = isset($_GET['restaurant_id']) ? $_GET['restaurant_id'] : null;
    if (!$rid) {
        http_response_code(400);
        echo json_encode(['error' => 'Restaurant ID mancante']);
        return;
    }
    
    // Prova prima a cercare per ID diretto
    $stmt = $db->prepare("SELECT id FROM restaurants WHERE id = ?");
    $stmt->execute([$rid]);
    $restaurant = $stmt->fetch();
    
    // Se non trovato, cerca per slug
    if (!$restaurant) {
        $stmt = $db->prepare("SELECT id FROM restaurants WHERE slug = ?");
        $stmt->execute([$rid]);
        $restaurant = $stmt->fetch();
    }
    
    if (!$restaurant) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Ristorante non trovato']);
        return;
    }
    
    // JOIN con tabella categories per ottenere il nome della categoria dal category_id
    $stmt = $db->prepare("
        SELECT d.* , COALESCE(c.name, 'Vari') as category 
        FROM dishes d 
        LEFT JOIN categories c ON d.category_id = c.id 
        WHERE d.restaurant_id = ? 
        ORDER BY COALESCE(c.name, 'Vari'), d.name
    ");
    $stmt->execute([$restaurant['id']]);
    $dishes = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $dishes], JSON_UNESCAPED_UNICODE);
}

function getItems($db) {
    // Alias di getMenu per compatibilità
    // Può filtrare per categoria se passata in GET
    $categoryId = isset($_GET['category']) ? $_GET['category'] : null;
    
    // Se è specifico per ristorante
    $rid = isset($_GET['restaurant_id']) ? $_GET['restaurant_id'] : null;
    
    if ($rid) {
        // Prova prima a cercare per ID diretto
        $stmt = $db->prepare("SELECT id FROM restaurants WHERE id = ?");
        $stmt->execute([$rid]);
        $restaurant = $stmt->fetch();
        
        // Se non trovato, cerca per slug
        if (!$restaurant) {
            $stmt = $db->prepare("SELECT id FROM restaurants WHERE slug = ?");
            $stmt->execute([$rid]);
            $restaurant = $stmt->fetch();
        }
        
        if ($restaurant) {
            // JOIN con tabella categories per ottenere il nome della categoria dal category_id
            $stmt = $db->prepare("
                SELECT d.*, COALESCE(c.name, 'Vari') as category 
                FROM dishes d 
                LEFT JOIN categories c ON d.category_id = c.id 
                WHERE d.restaurant_id = ? 
                ORDER BY COALESCE(c.name, 'Vari'), d.name
            ");
            $stmt->execute([$restaurant['id']]);
        } else {
            // JOIN con tabella categories per ottenere il nome della categoria dal category_id
            $stmt = $db->prepare("
                SELECT d.*, COALESCE(c.name, 'Vari') as category 
                FROM dishes d 
                LEFT JOIN categories c ON d.category_id = c.id 
                WHERE d.restaurant_id = ? 
                ORDER BY COALESCE(c.name, 'Vari'), d.name
            ");
            $stmt->execute([$rid]);
        }
    } else {
        // JOIN con tabella categories per ottenere il nome della categoria dal category_id
        $stmt = $db->prepare("
            SELECT d.*, COALESCE(c.name, 'Vari') as category 
            FROM dishes d 
            LEFT JOIN categories c ON d.category_id = c.id 
            ORDER BY COALESCE(c.name, 'Vari'), d.name
        ");
        $stmt->execute([]);
    }
    
    $dishes = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $dishes], JSON_UNESCAPED_UNICODE);
}

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

function addDish($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['restaurant_id']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dati mancanti']);
        return;
    }
    
    $id = uniqid('dish_');
    $allergenText = ($data['name'] ?? '') . ' ' . ($data['description'] ?? '');
    $allergens = extractAllergensFromText($allergenText);
    
    // Se è fornito category_id, usalo; altrimenti usa category (string legacy)
    $categoryId = isset($data['category_id']) ? $data['category_id'] : null;
    $categoryStr = isset($data['category']) ? $data['category'] : null;
    
    // Se è fornito category_id, valida che esista e appartenga al ristorante
    if ($categoryId) {
        $checkStmt = $db->prepare("SELECT id FROM categories WHERE id = ? AND restaurant_id = ?");
        $checkStmt->execute([$categoryId, $data['restaurant_id']]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria non trovata per questo ristorante']);
            return;
        }
    }
    
    $stmt = $db->prepare("INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, price, image_url, available, allergens) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $data['restaurant_id'], $categoryStr, $categoryId, $data['name'], $data['description'] ?? null, $data['price'] ?? null, $data['image_url'] ?? null, $data['available'] ?? true, json_encode($allergens)]);
    echo json_encode(['success' => true, 'id' => $id, 'allergens' => $allergens], JSON_UNESCAPED_UNICODE);
}

function updateDish($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    
    // Recupera il piatto attuale
    $dishStmt = $db->prepare("SELECT * FROM dishes WHERE id = ?");
    $dishStmt->execute([$data['id']]);
    $currentDish = $dishStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$currentDish) {
        http_response_code(404);
        echo json_encode(['error' => 'Piatto non trovato']);
        return;
    }
    
    // Usa i dati forniti, o mantieni i dati attuali
    $category = $data['category'] ?? $currentDish['category'];
    $category_id = $data['category_id'] ?? $currentDish['category_id'];
    $name = $data['name'] ?? $currentDish['name'];
    $description = $data['description'] ?? $currentDish['description'];
    $price = $data['price'] ?? $currentDish['price'];
    $image_url = $data['image_url'] ?? $currentDish['image_url'];
    $available = isset($data['available']) ? $data['available'] : $currentDish['available'];
    
    // Estrai allergeni solo se nome o descrizione cambiano
    if (isset($data['name']) || isset($data['description'])) {
        $allergenText = $name . ' ' . $description;
        $allergens = extractAllergensFromText($allergenText);
    } else {
        $allergens = $currentDish['allergens'];
    }
    
    // Se è fornito category_id, valida che esista
    if ($category_id && isset($data['category_id'])) {
        $checkStmt = $db->prepare("SELECT id FROM categories WHERE id = ? AND restaurant_id = ?");
        $checkStmt->execute([$category_id, $currentDish['restaurant_id']]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Categoria non trovata per questo ristorante']);
            return;
        }
    }
    
    $stmt = $db->prepare("UPDATE dishes SET category = ?, category_id = ?, name = ?, description = ?, price = ?, image_url = ?, available = ?, allergens = ? WHERE id = ?");
    $result = $stmt->execute([
        $category, 
        $category_id,
        $name, 
        $description, 
        $price, 
        $image_url, 
        $available, 
        is_array($allergens) ? json_encode($allergens) : $allergens, 
        $data['id']
    ]);
    
    echo json_encode(['success' => $result, 'allergens' => (is_array($allergens) ? $allergens : json_decode($allergens, true))], JSON_UNESCAPED_UNICODE);
}

function deleteDish($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    $stmt = $db->prepare("DELETE FROM dishes WHERE id = ?");
    $stmt->execute([$data['id']]);
    echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
}

function getCategories($db) {
    $rid = isset($_GET['restaurant_id']) ? $_GET['restaurant_id'] : null;
    if (!$rid) {
        http_response_code(400);
        echo json_encode(['error' => 'Restaurant ID mancante'], JSON_UNESCAPED_UNICODE);
        return;
    }
    try {
        $stmt = $db->prepare("SELECT id, restaurant_id, name, description, display_order, created_at, updated_at FROM categories WHERE restaurant_id = ? ORDER BY display_order, name");
        $stmt->execute([$rid]);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'data' => $categories], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore nel recupero categorie: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function addCategory($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['restaurant_id']) || !isset($data['name'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Restaurant ID e Name sono obbligatori'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        $categoryId = 'cat_' . substr(md5(uniqid()), 0, 12);
        $displayOrder = isset($data['display_order']) ? intval($data['display_order']) : 0;
        $description = isset($data['description']) ? $data['description'] : '';
        
        // Verifica che il ristorante esista
        $checkStmt = $db->prepare("SELECT id FROM restaurants WHERE id = ?");
        $checkStmt->execute([$data['restaurant_id']]);
        if (!$checkStmt->fetch()) {
            http_response_code(404);
            echo json_encode(['error' => 'Ristorante non trovato'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Inserisci categoria
        $stmt = $db->prepare("INSERT INTO categories (id, restaurant_id, name, description, display_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$categoryId, $data['restaurant_id'], $data['name'], $description, $displayOrder]);
        
        echo json_encode(['success' => true, 'id' => $categoryId], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore nell\'aggiunta categoria: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function updateCategory($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Category ID obbligatorio'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        // Prepara update dinamico basato su quali campi sono forniti
        $updates = [];
        $params = [];
        
        if (isset($data['name'])) {
            $updates[] = "name = ?";
            $params[] = $data['name'];
        }
        if (isset($data['description'])) {
            $updates[] = "description = ?";
            $params[] = $data['description'];
        }
        if (isset($data['display_order'])) {
            $updates[] = "display_order = ?";
            $params[] = intval($data['display_order']);
        }
        
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
        echo json_encode(['error' => 'Errore nell\'aggiornamento categoria: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function deleteCategory($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Category ID obbligatorio'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    try {
        // Verifica se la categoria è usata dai piatti
        $checkStmt = $db->prepare("SELECT COUNT(*) as count FROM dishes WHERE category_id = ?");
        $checkStmt->execute([$data['id']]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            // Se ci sono piatti in questa categoria, possiamo:
            // 1. Eliminare i piatti (non consigliato)
            // 2. Spostare i piatti in una categoria di default
            // 3. Restituire errore
            // Scegliamo l'opzione 3 per sicurezza
            http_response_code(409);
            echo json_encode(['error' => 'Impossibile eliminare la categoria: ' . $result['count'] . ' piatti sono assegnati a questa categoria'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        // Elimina categoria
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
        echo json_encode(['error' => 'Errore nell\'eliminazione categoria: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
    }
}

function getExampleMenu() {
    echo json_encode(['success' => true, 'data' => [
        ['category' => 'Antipasti', 'name' => 'Burrata', 'price' => 12.50],
        ['category' => 'Piatti', 'name' => 'Spaghetti', 'price' => 15.00],
        ['category' => 'Dolci', 'name' => 'Tiramisu', 'price' => 8.00]
    ]], JSON_UNESCAPED_UNICODE);
}

function getMenuTemplate($slug, $restaurantName) {
    $escaped_name = htmlspecialchars($restaurantName, ENT_QUOTES, 'UTF-8');
    $page = <<<'EOT'
<!doctype html>
<html lang="it">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1, viewport-fit=cover" />
    <meta name="HandheldFriendly" content="true">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>RESTAURANT_TITLE | Menu Digitale</title>
    <meta name="description" content="Scopri il menu digitale di RESTAURANT_TITLE">
    <meta name="theme-color" content="#537b83">
    
    <link rel="shortcut icon" href="/favicon.php?size=32x32" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="/assets/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/style.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/custom.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/jquery.lm.cart.css">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@600;700&display=swap">
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" type="text/css" href="https://www.trovapiatto.it/demo/assets/fonts/css/fontawesome-all.min.css">
    
    <style>
        body { font-family: "roboto", sans-serif !important; }
        h1, h2, h3, h4, h5, h6 { font-family: "source sans pro", sans-serif !important; }
        
        .menu-category-section {
            position: relative;
            padding: 20px;
            margin: 15px 0;
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .menu-category-section:hover {
            box-shadow: 0 5px 15px rgba(239, 61, 38, 0.15);
        }
        .category-title-wrapper {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            padding: 15px 20px;
            background: linear-gradient(135deg, rgba(239, 61, 38, 0.05) 0%, rgba(83, 123, 131, 0.05) 100%);
            border-radius: 10px;
            transition: all 0.3s ease;
            margin: -20px -20px 0 -20px;
            border-radius: 15px 15px 0 0;
        }
        .category-title-wrapper:hover {
            background: linear-gradient(135deg, rgba(239, 61, 38, 0.1) 0%, rgba(83, 123, 131, 0.1) 100%);
            transform: scale(1.01);
        }
        .category-title-wrapper h2 {
            margin: 0 !important;
            font-size: 1.5em;
            font-weight: 700;
            color: #EF3D26;
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }
        .category-toggle-icon {
            font-size: 20px;
            transition: transform 0.3s ease;
            color: #EF3D26;
            margin-left: auto;
        }
        .category-toggle-icon.collapsed {
            transform: rotate(0deg);
        }
        [data-itemsContainer] {
            max-height: 10000px;
            opacity: 1;
            overflow: hidden;
            transition: all 0.5s ease;
            padding: 20px 0 0 0;
            margin: 20px 0 0 0;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
        }
        [data-itemsContainer].collapsed {
            max-height: 0;
            opacity: 0;
            padding: 0;
            margin: 0;
        }
        .dish-card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .dish-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 12px;
        }
        .dish-price {
            font-size: 1.3em;
            font-weight: 700;
            color: #EF3D26;
            margin: 8px 0;
        }
        .dish-description {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 10px;
            flex-grow: 1;
        }
        .dish-allergens {
            font-size: 0.85em;
            color: #d32f2f;
            margin-top: 8px;
        }
        .page-header-logo {
            max-height: 100px;
            max-width: 80%;
            object-fit: contain;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>
<body class="theme-light" data-background="none" data-highlight="custom" data-menu-id="1" data-menu-currency="&euro;">

<div id="page">

    <!-- Header Bar -->
    <div class="header header-fixed header-logo-center mb-4" id="header-bar">
        <h1 class="header-title" id="header-title">RESTAURANT_TITLE</h1>
        <div class="header-icon-1-group">
            <a href="javascript:history.go(-1);" class="header-icon header-icon-1">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>
        <div class="header-icon-4-group">
            <a href="javascript:void(0);" class="cart-icon header-icon header-icon-4" data-menu="menu-cart">
                <i class="far fa-list-alt" style="font-size: 18px;"></i>
                <span class="badge bg-danger lmcart-totalitems">0</span>
                <span class="icon-label" style="margin-left: -4px;">Ordine</span>
            </a>
        </div>
    </div>

    <!-- Footer Bar -->
    <div id="footer-bar" class="footer-bar-5">
        <a href="javascript:location.reload();"><i class="far fa-list-alt"></i><span>Home</span></a>
        <a href="javascript:void(0);" data-menu="menu-categorie"><i class="fas fa-bars"></i><span>Menù</span></a>
        <a href="javascript:void(0);" data-menu="menu-search"><i class="fa fa-search"></i><span>Cerca</span></a>
        <a href="javascript:void(0);" data-menu="menu-contatti"><i class="fa fa-info"></i><span>Contatti</span></a>
        <a href="javascript:void(0);" data-menu="menu-share"><i class="fa fa-share-alt"></i><span>Condividi</span></a>
    </div>

    <!-- Page Content -->
    <div class="page-content header-clear">

        <!-- Page Header -->
        <div class="page-header card m-0 mb-3 shadow-l bg-highlight" data-card-height="200" style="background: linear-gradient(135deg, rgba(239, 61, 38, 0.9), rgba(83, 123, 131, 0.9));">
            <div class="card-bottom text-center mb-0">
                <img id="restaurant-logo" src="" alt="Logo" class="page-header-logo" style="display: none;">
                <h2 id="restaurant-name" style="color: white; margin: 0;">RESTAURANT_TITLE</h2>
                <p id="restaurant-desc" style="color: rgba(255,255,255,0.9); font-size: 0.95em; margin: 8px 0 0 0;"></p>
            </div>
            <div class="card-overlay bg-gradient opacity-60"></div>
        </div>

        <!-- Menu Items Container -->
        <div id="menu-items-container" class="menu-items-dynamic" style="padding: 0 15px;">
            <div class="text-center p-4">
                <div class="spinner-border color-highlight" role="status"></div>
                <p class="color-gray2-dark font-13 mt-3">Caricamento menu...</p>
            </div>
        </div>

        <!-- Footer Copy -->
        <div class="footer card card-style">
            <div class="text-center mb-3">
                <h3 id="footer-name" style="margin: 0; color: #537b83;">RESTAURANT_TITLE</h3>
            </div>
            <div class="text-center mb-3">
                <a id="phone-link" href="" class="icon icon-xs rounded-sm shadow-l mr-1 mt-1 bg-primary color-white">
                    <i class="fas fa-phone"></i>
                </a>
                <a id="whatsapp-link" href="" class="icon icon-xs rounded-sm shadow-l mr-1 mt-1 bg-phone">
                    <i class="fab fa-whatsapp font-17"></i>
                </a>
                <a id="email-link" href="" class="icon icon-xs rounded-sm shadow-l mr-1 mt-1 bg-red2-dark">
                    <i class="fas fa-envelope"></i>
                </a>
                <a id="map-link" href="" class="icon icon-xs rounded-sm shadow-l mr-1 bg-magenta2-dark" target="_blank">
                    <i class="fas fa-map-marker-alt font-17"></i>
                </a>
                <a href="javascript:void(0);" class="back-to-top icon icon-xs rounded-sm shadow-l mt-1 bg-dark1-light" onclick="window.scrollTo({top:0,behavior:'smooth'});">
                    <i class="fa fa-angle-up"></i>
                </a>
            </div>
            <div class="clear"></div>
        </div>

        <p class="footer-copyright text-center color-gray2-dark">
            Il tuo Menù Digitale su <a href="https://www.trovapiatto.it" class="color-gray2-dark font-weight-bold" target="_blank">trovapiatto.it</a>
        </p>

    </div>

</div>

<!-- Menu Search -->
<div id="menu-search" class="menu menu-box-top menu-box-detached rounded-m" data-menu-height="180" data-menu-effect="menu-over">
    <div class="menu-title mt-n1">
        <h1>Trova</h1>
        <p class="color-highlight">Scrivi cosa cerchi tra le voci menù</p>
        <a href="javascript:void(0);" class="close-menu"><i class="fa fa-times"></i></a>
    </div>
    <div class="search-box search-header bg-theme card-style mr-3 ml-3">
        <i class="fa fa-search"></i>
        <input type="text" id="search-input" class="border-0" placeholder="Cosa cerchi?" onkeyup="searchDishes()">
    </div>
</div>

<!-- Menu Categorie -->
<div id="menu-categorie" class="menu menu-box-bottom menu-box-detached rounded-m" data-menu-height="420" data-menu-effect="menu-over">
    <div class="menu-title mt-n1">
        <h1>Menù</h1>
        <p class="color-highlight mb-0">Consulta velocemente le categorie del Menù</p>
        <a href="#" class="close-menu"><i class="fa fa-times"></i></a>
    </div>
    <div class="content mb-0">
        <div class="divider mb-0"></div>
        <div class="list-group list-custom-small list-icon-0" id="category-list"></div>
    </div>
</div>

<!-- Menu Contatti -->
<div id="menu-contatti" class="menu menu-box-bottom menu-box-detached rounded-m" data-menu-height="320" data-menu-effect="menu-over">
    <div class="menu-title mt-n1">
        <h1>Contatti</h1>
        <p class="color-highlight">Rimani in contatto con noi</p>
        <a href="javascript:void(0);" class="close-menu"><i class="fa fa-times"></i></a>
    </div>
    <div class="content mb-0">
        <div class="divider mb-0"></div>
        <div class="list-group list-custom-small list-icon-0 notranslate" id="contact-list"></div>
    </div>
</div>

<!-- Menu Share -->
<div id="menu-share" class="menu menu-box-bottom menu-box-detached rounded-m" data-menu-height="260" data-menu-effect="menu-over">
    <div class="menu-title mt-n1">
        <h1>Condividi Menù</h1>
        <p class="color-highlight">Invia questo Menù ai tuoi Amici</p>
        <a href="javascript:void(0);" class="close-menu"><i class="fa fa-times"></i></a>
    </div>
    <div class="content mb-0">
        <div class="divider mb-0"></div>
        <div class="list-group list-custom-small list-icon-0">
            <a href="javascript:void(0);" onclick="shareToFacebook()" class="shareLink">
                <i class="font-18 fab fa-facebook color-facebook"></i>
                <span class="font-13">Facebook</span>
                <i class="fa fa-angle-right"></i>
            </a>
            <a href="javascript:void(0);" onclick="shareToWhatsApp()" class="shareLink">
                <i class="font-18 fab fa-whatsapp-square color-whatsapp"></i>
                <span class="font-13">WhatsApp</span>
                <i class="fa fa-angle-right"></i>
            </a>
        </div>
    </div>
</div>

<!-- Menu Cart -->
<div id="menu-cart" class="shopping-cart menu menu-box-bottom menu-box-detached rounded-m pb-3" data-menu-height="90%" data-menu-effect="menu-over">
    <div class="menu-title mt-n1">
        <h1>Il tuo Ordine</h1>
        <p class="color-highlight">Hai selezionato i seguenti prodotti</p>
        <a href="javascript:void(0);" class="close-menu"><i class="fa fa-times"></i></a>
    </div>
    <div class="lmcart-items"></div>
    <div class="divider mt-3 mb-2"></div>
    <div class="content mt-0 mb-0">
        <div>
            <div class="d-flex">
                <div class="mr-3">
                    <h1 class="font-600">Totale*</h1>
                    <span class="lmcart-totalitems">0</span> prodotto/i nel tuo ordine
                </div>
                <div class="ml-auto text-center">
                    <h1 class="notranslate">€ <span class="lmcart-total">0.00</span></h1>
                </div>
            </div>
        </div>
    </div>
    <div class="content mt-2">
        <a href="javascript:void(0);" id="empty-lmcart" class="btn btn-m btn-full mt-0 mb-0 rounded-xl text-uppercase font-900 shadow-s bg-danger btn-icon text-left color-white">
            <i class="fas fa-trash font-18 text-center"></i> Annulla il tuo ordine
        </a>
    </div>
</div>

<!-- Menu Allergeni -->
<div id="menu-allergeni" class="menu menu-box-bottom menu-box-detached rounded-m" data-menu-height="420" data-menu-effect="menu-over">
    <div class="menu-title mt-n1">
        <h1>Allergeni</h1>
        <p class="color-highlight mb-0">Consulta la lista degli Allergeni</p>
        <a href="#" class="close-menu"><i class="fa fa-times"></i></a>
    </div>
    <div class="content mb-0">
        <div class="divider mb-0"></div>
        <div class="list-group list-custom-small list-icon-0 line-height-xl" id="allergen-list"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/jquery.cookie.min.js"></script>
<script src="/assets/js/cookiescart.js"></script>
<script src="/assets/js/jquery.lm.cart.js"></script>
<script src="/assets/js/custom.js"></script>

<script>
const RESTAURANT_SLUG = 'RESTAURANT_SLUG';
const API_URL = '../admin/api.php';
let allDishes = [];
let restaurantData = null;

const ALLERGEN_MAP = {
    'glutine': 'Cereali contenenti glutine',
    'lattosio': 'Latte',
    'uova': 'Uova',
    'pesce': 'Pesce',
    'crostacei': 'Crostacei',
    'molluschi': 'Molluschi',
    'noci': 'Frutta a guscio',
    'arachidi': 'Arachidi',
    'sesamo': 'Sesamo',
    'soia': 'Soia',
    'sedano': 'Sedano',
    'solfiti': 'Solfiti'
};

function normalizeAllergenKey(key) {
    const k = key.toLowerCase();
    const alias = { 'latticini': 'lattosio', 'frutta a guscio': 'noci', 'cereali contenenti glutine': 'glutine' };
    return alias[k] || k;
}

document.addEventListener('DOMContentLoaded', function() {
    loadRestaurantData();
    loadDishes();
    loadAllergens();
});

async function loadRestaurantData() {
    try {
        const response = await fetch(API_URL + '?action=get-restaurant&id=' + RESTAURANT_SLUG);
        const result = await response.json();
        if (result.success && result.data) {
            restaurantData = result.data;
            document.getElementById('restaurant-name').textContent = result.data.name;
            document.getElementById('header-title').textContent = result.data.name;
            document.getElementById('footer-name').textContent = result.data.name;
            document.title = result.data.name + ' | Menu Digitale';
            
            if (result.data.logo_url) {
                const logoImg = document.getElementById('restaurant-logo');
                logoImg.src = result.data.logo_url;
                logoImg.style.display = 'block';
            }
            
            if (result.data.description) {
                document.getElementById('restaurant-desc').textContent = result.data.description;
            }
            if (result.data.phone) {
                document.getElementById('phone-link').href = 'tel:' + result.data.phone;
                document.getElementById('whatsapp-link').href = 'https://wa.me/' + result.data.phone.replace(/[^0-9]/g, '');
            }
            if (result.data.email) {
                document.getElementById('email-link').href = 'mailto:' + result.data.email;
            }
            if (result.data.address) {
                document.getElementById('map-link').href = 'https://www.google.com/maps/search/' + encodeURIComponent(result.data.address);
            }
            
            loadContactList(result.data);
        }
    } catch(e) {
        console.error('Errore:', e);
    }
}

function loadContactList(data) {
    let html = '';
    if (data.phone) {
        html += '<a href="tel:' + data.phone + '"><i class="font-18 fas fa-phone color-highlight"></i><span class="font-13">' + data.phone + '</span><i class="fa fa-angle-right"></i></a>';
        html += '<a href="https://wa.me/' + data.phone.replace(/[^0-9]/g, '') + '"><i class="font-20 fab fa-whatsapp-square color-highlight"></i><span class="font-13">Messaggio WhatsApp</span><i class="fa fa-angle-right"></i></a>';
    }
    if (data.address) {
        html += '<a href="https://www.google.com/maps/search/' + encodeURIComponent(data.address) + '" target="_blank"><i class="font-18 fas fa-map-marker-alt color-highlight"></i><span class="font-13">Visualizza su Google Map</span><strong>' + data.address + '</strong><i class="fa fa-angle-right"></i></a>';
    }
    document.getElementById('contact-list').innerHTML = html;
}

async function loadDishes() {
    try {
        const response = await fetch(API_URL + '?action=get-menu&restaurant_id=' + RESTAURANT_SLUG);
        const result = await response.json();
        if (result.success && result.data) {
            allDishes = result.data;
            displayDishes(allDishes);
            loadCategoryList(allDishes);
        }
    } catch(e) {
        console.error('Errore:', e);
    }
}

function displayDishes(dishes) {
    if (!dishes || dishes.length === 0) {
        document.getElementById('menu-items-container').innerHTML = '<div class="text-center p-4"><p>Nessun piatto disponibile</p></div>';
        return;
    }
    
    const grouped = {};
    dishes.forEach(dish => {
        const cat = dish.category || 'Vari';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(dish);
    });
    
    let html = '';
    for (const [category, items] of Object.entries(grouped)) {
        html += '<div class="menu-category-section">';
        html += '<div class="category-title-wrapper" onclick="toggleCategory(this)">';
        html += '<h2><i class="fas fa-folder"></i> ' + category + '</h2>';
        html += '<i class="category-toggle-icon fas fa-chevron-up"></i>';
        html += '</div>';
        html += '<div data-itemsContainer>';
        
        items.forEach(dish => {
            const allergens = dish.allergens ? (typeof dish.allergens === 'string' ? JSON.parse(dish.allergens) : dish.allergens) : [];
            const allergensHtml = allergens.length > 0 ? '<div class="dish-allergens"><i class="fa fa-exclamation-circle mr-2"></i>' + allergens.map(a => ALLERGEN_MAP[normalizeAllergenKey(a)] || normalizeAllergenKey(a)).join(', ') + '</div>' : '';
            
            html += '<div class="card card-style dish-card">';
            if (dish.image_url) {
                html += '<img src="' + dish.image_url + '" alt="' + dish.name + '" onerror="this.src=\'https://via.placeholder.com/300x200?text=' + encodeURIComponent(dish.name) + '\';">';
            }
            html += '<div class="content mb-0">';
            html += '<h2 style="font-size: 1.1em; margin-bottom: 8px;">' + dish.name + '</h2>';
            if (dish.description) {
                html += '<p class="dish-description">' + dish.description + '</p>';
            }
            html += '<div class="dish-price">€ ' + parseFloat(dish.price).toFixed(2) + '</div>';
            html += allergensHtml;
            html += '<a href="javascript:void(0);" class="lmcart-add btn btn-m btn-full mb-0 rounded-xl text-uppercase font-900 shadow-s bg-green1-dark btn-icon text-left" style="margin-top: 12px;" data-id="' + dish.id + '" data-title="' + dish.name + '" data-price="' + dish.price + '" data-cat="' + category + '">';
            html += '<i class="far fa-list-alt font-15 text-center"></i> Ordina';
            html += '</a>';
            html += '</div></div>';
        });
        
        html += '</div></div>';
    }
    
    document.getElementById('menu-items-container').innerHTML = html;
}

function toggleCategory(element) {
    const container = element.nextElementSibling;
    container.classList.toggle('collapsed');
    element.querySelector('.category-toggle-icon').classList.toggle('collapsed');
}

function loadCategoryList(dishes) {
    const categories = [...new Set(dishes.map(d => d.category || 'Vari'))];
    let html = '';
    categories.forEach(cat => {
        html += '<a href="javascript:void(0);" class="cat-link main-lev cat-pl-0" onclick="scrollToCategory(\'' + cat + '\')">';
        html += '<span class="font-13 line-height-s">' + cat + '</span>';
        html += '<i class="fa fa-angle-right"></i></a>';
    });
    document.getElementById('category-list').innerHTML = html;
}

function scrollToCategory(category) {
    const element = document.querySelector('[data-itemsContainer]');
    if (element && element.parentElement.querySelector('.category-title-wrapper h2').textContent.includes(category)) {
        element.closest('.menu-category-section').scrollIntoView({ behavior: 'smooth' });
        document.querySelector('.close-menu').click();
    }
}

function loadAllergens() {
    let html = '';
    for (const [key, value] of Object.entries(ALLERGEN_MAP)) {
        html += '<a href="javascript:void(0);" style="cursor: default;"><h5 style="line-height: 1.3; margin: 12px 0;">' + value + '</h5></a>';
    }
    document.getElementById('allergen-list').innerHTML = html;
}

function searchDishes() {
    const query = document.getElementById('search-input').value.toLowerCase();
    if (!query) {
        displayDishes(allDishes);
        return;
    }
    const filtered = allDishes.filter(d => d.name.toLowerCase().includes(query) || d.description.toLowerCase().includes(query));
    displayDishes(filtered);
}

function shareToFacebook() {
    window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.href), '_blank');
    document.querySelector('.close-menu').click();
}

function shareToWhatsApp() {
    window.open('https://wa.me/?text=' + encodeURIComponent('Guarda questo menù: ' + window.location.href), '_blank');
    document.querySelector('.close-menu').click();
}
</script>

</body>
</html>
EOT;
    $page = str_replace('RESTAURANT_NAME', $escaped_name, $page);
    $page = str_replace('RESTAURANT_TITLE', $escaped_name, $page);
    $page = str_replace('RESTAURANT_SLUG', $slug, $page);
    return $page;
}

function uploadImage() {
    global $db;
    
    // Valida la richiesta
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File non caricato correttamente']);
        return;
    }
    
    $file = $_FILES['image'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    
    // Validazione dimensione
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(['error' => 'File troppo grande (max 5MB)']);
        return;
    }
    
    // Validazione tipo
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato non supportato (jpg, png, webp)']);
        return;
    }
    
    // Crea cartella uploads se non esiste
    $uploadDir = dirname(__DIR__) . '/admin/uploads';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Genera nome file univoco
    $fileExt = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = 'dish_' . time() . '_' . uniqid() . '.' . $fileExt;
    $filePath = $uploadDir . '/' . $fileName;
    
    // Sposta il file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore durante il salvataggio del file']);
        return;
    }
    
    // Salva nel database (track delle immagini caricate)
    $imageId = uniqid('img_');
    $fileUrl = '/admin/uploads/' . $fileName;
    $uploadedDate = date('Y-m-d H:i:s');
    
    try {
        if ($db) {
            // Se tabella non esiste, crea l'entry comunque ritornando l'URL
            $stmt = $db->prepare("INSERT INTO images (id, filename, url, uploaded_date) VALUES (?, ?, ?, ?)");
            $stmt->execute([$imageId, $fileName, $fileUrl, $uploadedDate]);
        }
    } catch (Exception $e) {
        // Se database fallisce, comunque ritorna l'URL (file è salvato)
        error_log('Errore salvataggio meta-immagine: ' . $e->getMessage());
    }
    
    // Ritorna URL del file caricato
    echo json_encode([
        'success' => true, 
        'url' => $fileUrl, 
        'filename' => $fileName,
        'imageId' => $imageId,
        'message' => 'Foto caricata con successo. Ora salva il piatto con questa immagine.'
    ], JSON_UNESCAPED_UNICODE);
}

function syncCategories($db) {
    try {
        // Aggiorna il campo 'category' con il nome della categoria dalla tabella categories
        // Per TUTTI i piatti che hanno un category_id valido
        $sql = "
            UPDATE dishes d
            SET d.category = (
                SELECT c.name FROM categories c WHERE c.id = d.category_id LIMIT 1
            )
            WHERE d.category_id IS NOT NULL 
            AND (d.category IS NULL OR d.category = '' OR TRIM(d.category) = '')
        ";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $rowsAffected = $stmt->rowCount();
        
        // Aggiorna i piatti senza category_id a 'Vari'
        $sql2 = "
            UPDATE dishes
            SET category = 'Vari'
            WHERE category_id IS NULL 
            AND (category IS NULL OR category = '' OR TRIM(category) = '')
        ";
        $stmt2 = $db->prepare($sql2);
        $stmt2->execute();
        $rowsAffected2 = $stmt2->rowCount();
        
        echo json_encode([
            'success' => true,
            'message' => 'Categorie sincronizzate con successo',
            'categories_from_join' => $rowsAffected,
            'categories_set_to_vari' => $rowsAffected2,
            'total_updated' => $rowsAffected + $rowsAffected2
        ], JSON_UNESCAPED_UNICODE);
        
    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

/**
 * Importa menu da OCR
 * Processa le immagini e estrae i piatti tramite OCR
 */
function importMenuOCR($db) {
    try {
        // Leggi JSON POST
        $rawInput = file_get_contents('php://input');
        $input = json_decode($rawInput, true);
        
        // DEBUG: Log dei dati ricevuti
        error_log('importMenuOCR JSON input: ' . substr($rawInput, 0, 200));
        error_log('importMenuOCR parsed: ' . print_r($input, true));
        
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'JSON non valido', 'received' => substr($rawInput, 0, 100)]);
            return;
        }
        
        if (!isset($input['files']) || empty($input['files'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nessun file fornito nel JSON']);
            return;
        }

        $restaurantSlug = $input['restaurant_slug'] ?? 'enzociro';
        $category = $input['category'] ?? 'Piatti';
        
        $files = $input['files'];
        $dishes = [];
        $errors = [];
        $uploadDir = __DIR__ . '/ocr-uploads';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Processa ogni file
        foreach ($files as $idx => $file) {
            if (!isset($file['data'])) {
                $errors[] = "File {$idx}: dati base64 mancanti";
                continue;
            }

            $filename = $file['name'] ?? "file_$idx";
            $base64Data = $file['data'];
            
            // Decodifica Base64
            $binaryData = base64_decode($base64Data, true);
            if ($binaryData === false) {
                $errors[] = "$filename: decodifica Base64 fallita";
                continue;
            }

            // Valida tipo file
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_buffer($finfo, $binaryData);
            finfo_close($finfo);

            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                $errors[] = "$filename: formato non supportato ($mime)";
                continue;
            }

            // Salva temporaneamente
            $savedPath = $uploadDir . '/' . uniqid() . '_' . basename($filename);
            file_put_contents($savedPath, $binaryData);

            // Estrai con Tesseract (se disponibile)
            $extractedText = extractOCRText($savedPath);
            
            // Parse testo in piatti
            $extractedDishes = parseMenuText($extractedText, $category);
            $dishes = array_merge($dishes, $extractedDishes);

            // Pulizia
            @unlink($savedPath);
        }

        if (empty($dishes)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nessun piatto estratto', 'errors' => $errors]);
            return;
        }

        echo json_encode([
            'success' => true,
            'dishes' => $dishes,
            'count' => count($dishes),
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE);

    } catch(Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Importa piatti estratti nel database
 */
function importMenuDishes($db) {
    try {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data || !isset($data['dishes'])) {
            throw new Exception('Dati non validi');
        }

        $restaurantSlug = $data['restaurant_slug'] ?? 'enzociro';
        $dishes = $data['dishes'];

        // Trova il ristorante
        $stmt = $db->prepare("SELECT id FROM restaurants WHERE slug = ?");
        $stmt->execute([$restaurantSlug]);
        $restaurant = $stmt->fetch();

        if (!$restaurant) {
            throw new Exception("Ristorante '$restaurantSlug' non trovato");
        }

        $restaurantId = $restaurant['id'];
        $importedCount = 0;
        $errors = [];

        // Importa ogni piatto
        foreach ($dishes as $dish) {
            try {
                if (empty($dish['name'])) {
                    $errors[] = 'Piatto senza nome saltato';
                    continue;
                }

                // Trova o crea categoria
                $categoryName = $dish['category'] ?? 'Vari';
                $stmt = $db->prepare("
                    SELECT id FROM categories 
                    WHERE restaurant_id = ? AND name = ?
                    LIMIT 1
                ");
                $stmt->execute([$restaurantId, $categoryName]);
                $category = $stmt->fetch();

                $categoryId = null;
                if ($category) {
                    $categoryId = $category['id'];
                } else {
                    // Crea categoria
                    $newCategoryId = uniqid('cat_');
                    $stmt = $db->prepare("
                        INSERT INTO categories (id, restaurant_id, name, display_order)
                        VALUES (?, ?, ?, 999)
                    ");
                    $stmt->execute([$newCategoryId, $restaurantId, $categoryName]);
                    $categoryId = $newCategoryId;
                }

                // Inserisci piatto
                $dishId = uniqid('dish_');
                $stmt = $db->prepare("
                    INSERT INTO dishes 
                    (id, restaurant_id, category_id, category, name, description, price, available)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $stmt->execute([
                    $dishId,
                    $restaurantId,
                    $categoryId,
                    $categoryName,
                    $dish['name'],
                    $dish['description'] ?? '',
                    $dish['price'] ?? 0
                ]);

                $importedCount++;
            } catch(Exception $e) {
                $errors[] = $dish['name'] . ': ' . $e->getMessage();
            }
        }

        echo json_encode([
            'success' => true,
            'imported' => $importedCount,
            'total' => count($dishes),
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE);

    } catch(Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ], JSON_UNESCAPED_UNICODE);
    }
}

/**
 * Esegue OCR su un'immagine base64
 * Endpoint: POST /api.php?action=run-ocr
 */
function runOCROnImage() {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['image'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Immagine non fornita']);
            return;
        }

        $imageData = $input['image'];
        
        // Decodifica base64
        if (strpos($imageData, 'data:image') === 0) {
            // Estrai il base64 dal data URL
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
        }
        
        $binaryData = base64_decode($imageData, true);
        if ($binaryData === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Immagine base64 non valida']);
            return;
        }

        // Salva temporaneamente l'immagine
        $uploadDir = sys_get_temp_dir();
        $filename = 'ocr_' . uniqid() . '.jpg';
        $filepath = $uploadDir . '/' . $filename;
        
        if (file_put_contents($filepath, $binaryData) === false) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Errore nel salvataggio dell\'immagine']);
            return;
        }

        // Estrai testo con OCR
        $extractedText = extractOCRText($filepath);
        
        // Pulisci il file temporaneo
        @unlink($filepath);
        
        if (empty($extractedText)) {
            http_response_code(200);
            echo json_encode([
                'success' => false, 
                'error' => 'Nessun testo trovato nell\'immagine',
                'extracted_text' => ''
            ]);
            return;
        }

        // Risposta di successo
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'extracted_text' => $extractedText
        ], JSON_UNESCAPED_UNICODE);

    } catch (Exception $e) {
        error_log('Errore OCR: ' . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Errore durante l\'OCR: ' . $e->getMessage()
        ]);
    }
}

/**
 * Estrae testo da immagine con OCR
 */
function extractOCRText($imagePath) {
    // Prova Tesseract (Linux)
    if (function_exists('shell_exec') && which('tesseract')) {
        $output = shell_exec("tesseract '$imagePath' stdout 2>/dev/null");
        if (!empty($output)) {
            return $output;
        }
    }

    // Fallback: estrai da nome file o metadata
    return pathinfo($imagePath, PATHINFO_FILENAME);
}

/**
 * Parse testo menu in array di piatti
 */
function parseMenuText($text, $defaultCategory = 'Piatti') {
    $dishes = [];
    $lines = array_filter(array_map('trim', explode("\n", $text)));

    foreach ($lines as $line) {
        // Salta righe vuote o troppo corte
        if (strlen($line) < 3) continue;

        // Cerca pattern "Nome ... Prezzo"
        if (preg_match('/^([^0-9€]+?)[\s\.]+(\d+[,.]?\d*)\s*€?$/', $line, $matches)) {
            $name = trim($matches[1]);
            $price = (float)str_replace(',', '.', $matches[2]);

            $dishes[] = [
                'name' => $name,
                'category' => $defaultCategory,
                'price' => $price,
                'description' => ''
            ];
        } elseif (strlen($line) > 5 && !strpos($line, '€') && !is_numeric($line[0])) {
            // Linea sembra essere un piatto senza prezzo
            $dishes[] = [
                'name' => $line,
                'category' => $defaultCategory,
                'price' => 0,
                'description' => ''
            ];
        }
    }

    return $dishes;
}

/**
 * Trova eseguibile nel PATH
 */
function which($cmd) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $output = shell_exec("where $cmd 2>nul");
    } else {
        $output = shell_exec("which $cmd 2>/dev/null");
    }
    return !empty($output);
}
?>
