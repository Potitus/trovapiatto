<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Invalidate OPcache on first run after deploy
if (function_exists('opcache_invalidate')) {
    opcache_invalidate(__FILE__, true);
}

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
tp_cors_headers('GET, POST, PUT, DELETE, OPTIONS', 'Content-Type, Authorization, X-Auth-Token');

try {
    $db = tp_db_connect();

    // Verifica schema con cache TTL (evita ~15 query DDL a ogni richiesta verso DB remoto).
    // Fail-open: se la cache non è scrivibile, riesegue sempre (comportamento precedente).
    $schemaCache = sys_get_temp_dir() . '/tp_schema_ok';
    $schemaTtl = (int)tp_env('TP_SCHEMA_TTL', 600);
    $schemaFresh = is_file($schemaCache) && (time() - filemtime($schemaCache) < $schemaTtl);
    if (!$schemaFresh) {
        createTables($db);
        ensureRestaurantColumns($db);
        ensureAllergensColumn($db);
        ensureCategoryIdColumn($db);
        ensureMultilingueColumns($db);
        ensureOrdersTables($db);
        @file_put_contents($schemaCache, (string)time());
    }
    
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    
    // Se POST senza action in GET, prendi da POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($action)) {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
    }
    
    // Trim e normalizza l'azione
    $action = trim($action);
    if (tp_env('TP_DEBUG', '')) {
        error_log("DEBUG: Action received: '$action' | GET action: " . var_export($_GET['action'] ?? 'NULL', true));
    }
    
    switch($action) {
        case 'get-restaurants':
            getRestaurants($db);
            break;
        case 'debug':
            echo json_encode(['success' => true, 'message' => 'API online', 'db_connected' => true, 'version' => '2.0-ocr', 'has_import_ocr' => true, 'action_received' => $action]);
            break;
        case 'create-restaurant':
            verifyApiToken();
            createRestaurant($db);
            break;
        case 'update-restaurant':
            verifyApiToken();
            updateRestaurant($db);
            break;
        case 'update-slug':
            verifyApiToken();
            updateSlug($db);
            break;
        case 'delete-restaurant':
            verifyApiToken();
            deleteRestaurant($db);
            break;
        case 'get-restaurant':
            getRestaurant($db);
            break;
        case 'get-categories':
            getCategories($db);
            break;
        case 'add-category':
            verifyApiToken();
            addCategory($db);
            break;
        case 'update-category':
            verifyApiToken();
            updateCategory($db);
            break;
        case 'delete-category':
            verifyApiToken();
            deleteCategory($db);
            break;
        case 'get-menu':
            getMenu($db);
            break;
        case 'get-items':
            getItems($db);
            break;
        case 'add-dish':
            verifyApiToken();
            addDish($db);
            break;
        case 'add-item':
            verifyApiToken();
            addDish($db); // Alias per compatibilità
            break;
        case 'clone-dish':
            verifyApiToken();
            cloneDish($db);
            break;
        case 'get-all-restaurants':
            getAllRestaurants($db);
            break;
        case 'get-all-restaurants-legacy':
            getAllRestaurants($db);
            break;
        case 'update-dish':
            verifyApiToken();
            updateDish($db);
            break;
        case 'update-item':
            verifyApiToken();
            updateDish($db); // Alias per compatibilità
            break;
        case 'delete-dish':
            verifyApiToken();
            deleteDish($db);
            break;
        case 'upload-image':
            verifyApiToken();
            uploadImage();
            break;
        case 'get-example-menu':
            getExampleMenu();
            break;
        case 'sync-categories':
            verifyApiToken();
            syncCategories($db);
            break;
        case 'run-ocr':
            verifyApiToken();
            runOCROnImage();
            break;
        case 'import-menu-ocr':
            verifyApiToken();
            importMenuOCR($db);
            break;
        case 'import-menu-dishes':
            verifyApiToken();
            importMenuDishes($db);
            break;
        case 'submit-menu-request':
            submitMenuRequest();
            break;
        case 'search-dishes':
            searchDishes($db);
            break;
        case 'add-review':
            addReview($db);
            break;
        case 'get-reviews':
            getReviews($db);
            break;
        case 'delete-review':
            deleteReview($db);
            break;
        case 'get-top-dishes':
            getTopDishes($db);
            break;
        case 'register-user':
            registerUser($db);
            break;
        case 'add-points':
            addPoints($db);
            break;
        case 'get-user-profile':
            getUserProfile($db);
            break;
        case 'get-leaderboard':
            getLeaderboard($db);
            break;
        case 'get-community-stats':
            getCommunityStats($db);
            break;
        case 'get-badges':
            getBadges($db);
            break;
        case 'get-user-badges':
            getUserBadges($db);
            break;
        case 'get-recent-reviews':
            getRecentReviews($db);
            break;
        case 'get-what-people-ate':
            getWhatPeopleAte($db);
            break;
        case 'get-starred-restaurants':
            getStarredRestaurants($db);
            break;
        case 'submit-order':
            submitOrder($db);
            break;
        case 'submit-reservation':
            submitReservation($db);
            break;
        default:
            // Fallback per get-all-restaurants
            if (strpos($action, 'get-all-restaurants') !== false || $action == 'get-all-restaurants') {
                getAllRestaurants($db);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Azione non trovata', 'action_received' => $action, 'debug' => 'Case non trovato nello switch']);
            }
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
    
    $db->exec("CREATE TABLE IF NOT EXISTS reviews (
        id VARCHAR(100) PRIMARY KEY,
        restaurant_id VARCHAR(100) NOT NULL,
        dish_id VARCHAR(100) DEFAULT NULL,
        user_name VARCHAR(255) NOT NULL DEFAULT 'Anonimo',
        rating DECIMAL(2,1) NOT NULL,
        comment TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_restaurant (restaurant_id),
        INDEX idx_dish (dish_id),
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS community_users (
        id VARCHAR(100) PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        display_name VARCHAR(255) NOT NULL,
        avatar_url VARCHAR(500) DEFAULT NULL,
        points INT DEFAULT 0,
        level INT DEFAULT 1,
        total_reviews INT DEFAULT 0,
        positive_reviews INT DEFAULT 0,
        streak_days INT DEFAULT 0,
        last_active_date DATE DEFAULT NULL,
        favorite_cuisine VARCHAR(255) DEFAULT NULL,
        bio TEXT,
        joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_username (username),
        INDEX idx_points (points DESC)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS community_points_log (
        id VARCHAR(100) PRIMARY KEY,
        user_id VARCHAR(100) NOT NULL,
        points INT NOT NULL,
        reason VARCHAR(255) NOT NULL,
        reference_type VARCHAR(50) DEFAULT NULL,
        reference_id VARCHAR(100) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS community_badges (
        id VARCHAR(100) PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(50) NOT NULL DEFAULT 'fa-certificate',
        color VARCHAR(20) NOT NULL DEFAULT '#8b5cf6',
        tier VARCHAR(20) NOT NULL DEFAULT 'bronze',
        points_required INT DEFAULT 0,
        reviews_required INT DEFAULT 0,
        special_condition VARCHAR(100) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS community_user_badges (
        id VARCHAR(100) PRIMARY KEY,
        user_id VARCHAR(100) NOT NULL,
        badge_id VARCHAR(100) NOT NULL,
        earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user (user_id),
        FOREIGN KEY (user_id) REFERENCES community_users(id) ON DELETE CASCADE,
        FOREIGN KEY (badge_id) REFERENCES community_badges(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_badge (user_id, badge_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS orders (
        id VARCHAR(100) PRIMARY KEY,
        restaurant_id VARCHAR(100) NOT NULL,
        customer_name VARCHAR(255),
        customer_phone VARCHAR(20),
        customer_email VARCHAR(255),
        items JSON NOT NULL,
        subtotal DECIMAL(8,2) DEFAULT 0,
        shipping DECIMAL(8,2) DEFAULT 0,
        total DECIMAL(8,2) DEFAULT 0,
        currency VARCHAR(10) DEFAULT '€',
        order_type VARCHAR(50) DEFAULT 'delivery',
        notes TEXT,
        status ENUM('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_restaurant (restaurant_id),
        INDEX idx_status (status),
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    $db->exec("CREATE TABLE IF NOT EXISTS reservations (
        id VARCHAR(100) PRIMARY KEY,
        restaurant_id VARCHAR(100) NOT NULL,
        customer_name VARCHAR(255) NOT NULL,
        customer_phone VARCHAR(20) NOT NULL,
        customer_email VARCHAR(255),
        party_size INT NOT NULL DEFAULT 1,
        reservation_date DATE NOT NULL,
        reservation_time TIME NOT NULL,
        notes TEXT,
        status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_restaurant (restaurant_id),
        INDEX idx_date (reservation_date),
        FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    seedCommunityBadges($db);

    // === USERS TABLE (Fase 1 Auth) ===
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id VARCHAR(100) PRIMARY KEY,
        email VARCHAR(255) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        name VARCHAR(255) NOT NULL,
        role ENUM('admin','agent','restaurant_owner','user') DEFAULT 'user',
        phone VARCHAR(20),
        avatar_url VARCHAR(500),
        email_verified BOOLEAN DEFAULT false,
        last_login DATETIME,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_role (role)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

    // Seed admin user (solo nuovi deploy; password da env)
    $adminEmail = 'admin@trovapiatto.it';
    $adminHash = password_hash(tp_env('ADMIN_PASSWORD', 'trovapiatto2024'), PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT IGNORE INTO users (id, email, password_hash, name, role) VALUES (?, ?, ?, ?, 'admin')");
    $stmt->execute(['usr_admin_001', $adminEmail, $adminHash, 'Admin Trovapiatto']);
}

// === JWT MIDDLEWARE (Fase 1 Auth) ===
$JWT_SECRET_KEY = tp_jwt_secret();

function _jwt_b64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function _jwt_b64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function verifyApiToken() {
    global $JWT_SECRET_KEY;
    $auth = '';
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        $auth = $headers['Authorization'] ?? $headers['authorization'] ?? $headers['AUTHORIZATION'] ?? '';
    }
    if (empty($auth)) {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    }
    if (empty($auth) && function_exists('apache_request_headers')) {
        $allHeaders = apache_request_headers();
        foreach ($allHeaders as $key => $value) {
            if (strtolower($key) === 'authorization') {
                $auth = $value;
                break;
            }
        }
    }
    if (empty($auth)) {
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            $auth = $headers['X-Auth-Token'] ?? $headers['x-auth-token'] ?? '';
        }
    }
    if (empty($auth)) {
        $auth = $_SERVER['HTTP_X_AUTH_TOKEN'] ?? '';
    }
    
    $token = '';
    if (!empty($auth) && preg_match('/Bearer\s+(.+)$/i', $auth, $matches)) {
        $token = $matches[1];
    } elseif (isset($_GET['token']) && !empty($_GET['token'])) {
        $token = $_GET['token'];
    }
    
    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token mancante. Effettua il login.']);
        exit;
    }
    
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        http_response_code(401);
        echo json_encode(['error' => 'Token invalido']);
        exit;
    }
    [$hdr, $payload, $signature] = $parts;
    $expected = _jwt_b64url_encode(hash_hmac('sha256', "$hdr.$payload", $JWT_SECRET_KEY, true));
    if (!hash_equals($expected, $signature)) {
        http_response_code(401);
        echo json_encode(['error' => 'Token non valido']);
        exit;
    }
    $data = json_decode(_jwt_b64url_decode($payload), true);
    if (!$data || $data['exp'] < time()) {
        http_response_code(401);
        echo json_encode(['error' => 'Token scaduto. Effettua di nuovo il login.']);
        exit;
    }
    return $data;
}

function ensureRestaurantColumns($db) {
    try {
        $result = $db->query("SHOW COLUMNS FROM restaurants LIKE 'city'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE restaurants ADD COLUMN city VARCHAR(255) DEFAULT NULL");
            error_log("✅ Colonna 'city' aggiunta a 'restaurants'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna city: " . $e->getMessage());
    }
    
    try {
        $result = $db->query("SHOW COLUMNS FROM restaurants LIKE 'cuisine'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE restaurants ADD COLUMN cuisine VARCHAR(255) DEFAULT NULL");
            error_log("✅ Colonna 'cuisine' aggiunta a 'restaurants'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna cuisine: " . $e->getMessage());
    }
    
    try {
        $result = $db->query("SHOW COLUMNS FROM restaurants LIKE 'website'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE restaurants ADD COLUMN website VARCHAR(500) DEFAULT NULL");
            error_log("✅ Colonna 'website' aggiunta a 'restaurants'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna website: " . $e->getMessage());
    }
    
    try {
        $result = $db->query("SHOW COLUMNS FROM restaurants LIKE 'rating'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE restaurants ADD COLUMN rating DECIMAL(3,1) DEFAULT 0");
            error_log("✅ Colonna 'rating' aggiunta a 'restaurants'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna rating: " . $e->getMessage());
    }
    
    try {
        $result = $db->query("SHOW COLUMNS FROM restaurants LIKE 'offer_image_url'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE restaurants ADD COLUMN offer_image_url VARCHAR(500) DEFAULT NULL");
            error_log("✅ Colonna 'offer_image_url' aggiunta a 'restaurants'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna offer_image_url: " . $e->getMessage());
    }
    
    try {
        $result = $db->query("SHOW COLUMNS FROM restaurants LIKE 'offer_text'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE restaurants ADD COLUMN offer_text VARCHAR(500) DEFAULT NULL");
            error_log("✅ Colonna 'offer_text' aggiunta a 'restaurants'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna offer_text: " . $e->getMessage());
    }
    
    try {
        $result = $db->query("SHOW COLUMNS FROM restaurants LIKE 'michelin_stars'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE restaurants ADD COLUMN michelin_stars INT DEFAULT 0");
            error_log("✅ Colonna 'michelin_stars' aggiunta a 'restaurants'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna michelin_stars: " . $e->getMessage());
    }

    try {
        $result = $db->query("SHOW COLUMNS FROM restaurants LIKE 'available_hours'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE restaurants ADD COLUMN available_hours TEXT DEFAULT NULL");
            error_log("✅ Colonna 'available_hours' aggiunta a 'restaurants'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonna available_hours: " . $e->getMessage());
    }
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

function ensureMultilingueColumns($db) {
    try {
        // Controlla colonna name_en
        $result = $db->query("SHOW COLUMNS FROM dishes LIKE 'name_en'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE dishes ADD COLUMN name_en VARCHAR(255) DEFAULT NULL");
            error_log("✅ Colonna 'name_en' aggiunta a 'dishes'");
        }
        
        // Controlla colonna description_en
        $result = $db->query("SHOW COLUMNS FROM dishes LIKE 'description_en'");
        if ($result->rowCount() == 0) {
            $db->exec("ALTER TABLE dishes ADD COLUMN description_en LONGTEXT DEFAULT NULL");
            error_log("✅ Colonna 'description_en' aggiunta a 'dishes'");
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore nel controllo/aggiunta colonne multilingue: " . $e->getMessage());
    }
}

function ensureOrdersTables($db) {
    // Orders table
    try {
        $result = $db->query("SHOW TABLES LIKE 'orders'");
        if ($result->rowCount() == 0) {
            $db->exec("CREATE TABLE orders (
                id VARCHAR(100) PRIMARY KEY,
                restaurant_id VARCHAR(100) NOT NULL,
                customer_name VARCHAR(255),
                customer_phone VARCHAR(20),
                customer_email VARCHAR(255),
                items JSON NOT NULL,
                subtotal DECIMAL(8,2) DEFAULT 0,
                shipping DECIMAL(8,2) DEFAULT 0,
                total DECIMAL(8,2) DEFAULT 0,
                currency VARCHAR(10) DEFAULT '€',
                order_type VARCHAR(50) DEFAULT 'delivery',
                notes TEXT,
                status ENUM('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_restaurant (restaurant_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            error_log("✅ Tabella 'orders' creata");
        } else {
            // Aggiungi colonne mancanti se la tabella esiste gia
            $cols = ['items','subtotal','shipping','total','currency','order_type','notes','status'];
            foreach ($cols as $col) {
                $r = $db->query("SHOW COLUMNS FROM orders LIKE '$col'");
                if ($r->rowCount() == 0) {
                    $defs = [
                        'items' => "JSON",
                        'subtotal' => "DECIMAL(8,2) DEFAULT 0",
                        'shipping' => "DECIMAL(8,2) DEFAULT 0",
                        'total' => "DECIMAL(8,2) DEFAULT 0",
                        'currency' => "VARCHAR(10) DEFAULT '€'",
                        'order_type' => "VARCHAR(50) DEFAULT 'delivery'",
                        'notes' => "TEXT",
                        'status' => "ENUM('pending','confirmed','preparing','ready','delivered','cancelled') DEFAULT 'pending'",
                    ];
                    $db->exec("ALTER TABLE orders ADD COLUMN $col " . $defs[$col]);
                    error_log("✅ Colonna '$col' aggiunta a 'orders'");
                }
            }
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore tabella orders: " . $e->getMessage());
    }
    // Reservations table
    try {
        $result = $db->query("SHOW TABLES LIKE 'reservations'");
        if ($result->rowCount() == 0) {
            $db->exec("CREATE TABLE reservations (
                id VARCHAR(100) PRIMARY KEY,
                restaurant_id VARCHAR(100) NOT NULL,
                customer_name VARCHAR(255) NOT NULL,
                customer_phone VARCHAR(20) NOT NULL,
                customer_email VARCHAR(255),
                party_size INT NOT NULL DEFAULT 1,
                reservation_date DATE NOT NULL,
                reservation_time TIME NOT NULL,
                notes TEXT,
                status ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_restaurant (restaurant_id),
                INDEX idx_date (reservation_date)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            error_log("✅ Tabella 'reservations' creata");
        } else {
            $cols = [
                'party_size' => "INT NOT NULL DEFAULT 1",
                'reservation_date' => "DATE NOT NULL",
                'reservation_time' => "TIME NOT NULL",
                'notes' => "TEXT",
                'status' => "ENUM('pending','confirmed','cancelled') DEFAULT 'pending'",
            ];
            foreach ($cols as $col => $def) {
                try {
                    $r = $db->query("SHOW COLUMNS FROM reservations LIKE '$col'");
                    if ($r->rowCount() == 0) {
                        $db->exec("ALTER TABLE reservations ADD COLUMN $col $def");
                        error_log("✅ Colonna '$col' aggiunta a 'reservations'");
                    }
                } catch(Exception $e) {
                    error_log("⚠️ Skip colonna '$col' reservations: " . $e->getMessage());
                }
            }
        }
    } catch(Exception $e) {
        error_log("⚠️ Errore tabella reservations: " . $e->getMessage());
    }
}

function getRestaurants($db) {
    $stmt = $db->prepare("SELECT * FROM restaurants ORDER BY created_at DESC");
    $stmt->execute();
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll()], JSON_UNESCAPED_UNICODE);
}

/**
 * Recupera TUTTI i ristoranti con informazioni complete per la mappa
 */
function getAllRestaurants($db) {
    try {
        // Verifica quali colonne esistono nella tabella
        $columns_query = "DESCRIBE restaurants";
        $cols_stmt = $db->prepare($columns_query);
        $cols_stmt->execute();
        $columns = $cols_stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        // Costruisci query con solo colonne che esistono
        $select_fields = ['r.id', 'r.name', 'r.slug', 'r.description', 'r.address', 'r.phone', 'r.email', 'r.logo_url', 'r.latitude', 'r.longitude'];
        
        // Aggiungi colonne opzionali se esistono
        if (in_array('city', $columns)) $select_fields[] = 'r.city';
        if (in_array('cuisine', $columns)) $select_fields[] = 'r.cuisine';
        if (in_array('website', $columns)) $select_fields[] = 'r.website';
        if (in_array('available_hours', $columns)) $select_fields[] = 'r.available_hours';
        if (in_array('rating', $columns)) $select_fields[] = 'COALESCE(r.rating, 0) as rating';
        if (in_array('michelin_stars', $columns)) $select_fields[] = 'COALESCE(r.michelin_stars, 0) as michelin_stars';
        
        $query = "SELECT " . implode(', ', $select_fields) . ",
            (SELECT COUNT(*) FROM dishes WHERE restaurant_id = r.id) as dish_count,
            (SELECT COUNT(DISTINCT category_id) FROM dishes WHERE restaurant_id = r.id) as category_count
        FROM restaurants r 
        ORDER BY r.name ASC";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $restaurants = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'total' => count($restaurants),
            'data' => $restaurants
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore nel caricamento ristoranti: ' . $e->getMessage()]);
    }
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
    $stmt->execute([
        $id, 
        $data['name'], 
        $slug, 
        $data['description'] ?? null, 
        $data['address'] ?? null, 
        $data['phone'] ?? null, 
        $data['email'] ?? null, 
        $data['logo_url'] ?? null, 
        $data['latitude'] ?? null, 
        $data['longitude'] ?? null,
        $data['city'] ?? null,
        $data['cuisine'] ?? null,
        $data['website'] ?? null,
        $data['rating'] ?? null
    ]);
    
    $menuCreate = createMenuFolder($slug, $data['name']);
    
    echo json_encode(['success' => true, 'id' => $id, 'slug' => $slug, 'menu_created' => $menuCreate], JSON_UNESCAPED_UNICODE);
}

function createMenuFolder($slug, $restaurantName) {
    try {
        // Salva i menu nella cartella `menu` alla radice del progetto (root)
        $menuPath = dirname(dirname(__DIR__)) . '/menu/' . $slug;
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
    
    $stmt = $db->prepare("UPDATE restaurants SET name = ?, description = ?, address = ?, phone = ?, email = ?, logo_url = ?, latitude = ?, longitude = ?, city = ?, cuisine = ?, website = ?, michelin_stars = ? WHERE id = ?");
    $result = $stmt->execute([$data['name'] ?? null, $data['description'] ?? null, $data['address'] ?? null, $data['phone'] ?? null, $data['email'] ?? null, $data['logo_url'] ?? null, $data['latitude'] ?? null, $data['longitude'] ?? null, $data['city'] ?? null, $data['cuisine'] ?? null, $data['website'] ?? null, $data['michelin_stars'] ?? 0, $data['id']]);
    if ($result && array_key_exists('available_hours', $data)) {
        try {
            $hoursJson = null;
            if (is_array($data['available_hours']) && !empty($data['available_hours'])) {
                $hoursJson = json_encode($data['available_hours'], JSON_UNESCAPED_UNICODE);
            }
            $db->prepare("UPDATE restaurants SET available_hours = ? WHERE id = ?")->execute([$hoursJson, $data['id']]);
        } catch (Exception $e) { /* colonna assente: ignora */ }
    }
    
    echo json_encode(['success' => $result], JSON_UNESCAPED_UNICODE);
}

function updateSlug($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['id']) || !isset($data['new_slug'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID e new_slug mancanti']);
        return;
    }
    
    $stmt = $db->prepare("UPDATE restaurants SET slug = ? WHERE id = ?");
    $result = $stmt->execute([$data['new_slug'], $data['id']]);
    
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
    $lang = isset($_GET['language']) ? $_GET['language'] : 'it'; // Lingua di default: italiano
    
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
        SELECT d.* , COALESCE(c.name, d.category, 'Vari') as category 
        FROM dishes d 
        LEFT JOIN categories c ON d.category_id = c.id 
        WHERE d.restaurant_id = ? 
        ORDER BY COALESCE(c.name, d.category, 'Vari'), d.name
    ");
    $stmt->execute([$restaurant['id']]);
    $dishes = $stmt->fetchAll();
    
    // Se lingua non è italiana, applica traduzioni
    if ($lang === 'en') {
        foreach ($dishes as &$dish) {
            // Usa la traduzione inglese se disponibile, altrimenti quella italiana
            if (!empty($dish['name_en'])) {
                $dish['name'] = $dish['name_en'];
            }
            if (!empty($dish['description_en'])) {
                $dish['description'] = $dish['description_en'];
            }
        }
    }
    
    echo json_encode(['success' => true, 'data' => $dishes, 'language' => $lang], JSON_UNESCAPED_UNICODE);
}

function getItems($db) {
    // Alias di getMenu per compatibilità
    // Può filtrare per categoria se passata in GET
    $categoryId = isset($_GET['category']) ? $_GET['category'] : null;
    $lang = isset($_GET['language']) ? $_GET['language'] : 'it'; // Lingua di default: italiano
    
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
                SELECT d.*, COALESCE(c.name, d.category, 'Vari') as category 
                FROM dishes d 
                LEFT JOIN categories c ON d.category_id = c.id 
                WHERE d.restaurant_id = ? 
                ORDER BY COALESCE(c.name, d.category, 'Vari'), d.name
            ");
            $stmt->execute([$restaurant['id']]);
        } else {
            // JOIN con tabella categories per ottenere il nome della categoria dal category_id
            $stmt = $db->prepare("
                SELECT d.*, COALESCE(c.name, d.category, 'Vari') as category 
                FROM dishes d 
                LEFT JOIN categories c ON d.category_id = c.id 
                WHERE d.restaurant_id = ? 
                ORDER BY COALESCE(c.name, d.category, 'Vari'), d.name
            ");
            $stmt->execute([$rid]);
        }
    } else {
        // JOIN con tabella categories per ottenere il nome della categoria dal category_id
        $stmt = $db->prepare("
            SELECT d.*, COALESCE(c.name, d.category, 'Vari') as category 
            FROM dishes d 
            LEFT JOIN categories c ON d.category_id = c.id 
            ORDER BY COALESCE(c.name, d.category, 'Vari'), d.name
        ");
        $stmt->execute([]);
    }
    
    $dishes = $stmt->fetchAll();
    
    // Se lingua non è italiana, applica traduzioni
    if ($lang === 'en') {
        foreach ($dishes as &$dish) {
            // Usa la traduzione inglese se disponibile, altrimenti quella italiana
            if (!empty($dish['name_en'])) {
                $dish['name'] = $dish['name_en'];
            }
            if (!empty($dish['description_en'])) {
                $dish['description'] = $dish['description_en'];
            }
        }
    }
    
    echo json_encode(['success' => true, 'data' => $dishes, 'language' => $lang], JSON_UNESCAPED_UNICODE);
}

function extractAllergensFromText($text) {
    $allergens = [];
    $keywords = [
        'glutine' => ['grano', 'pasta', 'pane', 'orzo', 'pizza', 'focaccia', 'farina'],
        'lattosio' => ['latte', 'formaggio', 'burro', 'panna', 'mozzarella', 'burrata'],
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
    if (!empty($data['allergens']) && is_array($data['allergens'])) {
        $allergens = array_map('strtolower', $data['allergens']);
    } else {
        $allergenText = ($data['name'] ?? '') . ' ' . ($data['description'] ?? '');
        $allergens = extractAllergensFromText($allergenText);
    }
    
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
    
    // Campi multilingue (opzionali)
    $name_en = $data['name_en'] ?? null;
    $description_en = $data['description_en'] ?? null;
    
    $stmt = $db->prepare("INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, name_en, description_en, price, image_url, available, allergens) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $data['restaurant_id'], $categoryStr, $categoryId, $data['name'], $data['description'] ?? null, $name_en, $description_en, $data['price'] ?? null, $data['image_url'] ?? null, $data['available'] ?? true, json_encode($allergens)]);
    echo json_encode(['success' => true, 'id' => $id, 'allergens' => $allergens], JSON_UNESCAPED_UNICODE);
}

/**
 * CLONA PIATTO ESISTENTE
 * Copia tutti i dati da un piatto esistente per velocizzare l'inserimento
 * 
 * POST /api.php?action=clone-dish
 * Body: {
 *   "source_dish_id": "dish_xxx",
 *   "restaurant_id": "ristorante_slug",
 *   "new_name": "Nome Nuovo (opzionale)"
 * }
 */
function cloneDish($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['source_dish_id']) || !isset($data['restaurant_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dati mancanti: source_dish_id e restaurant_id richiesti']);
        return;
    }
    
    // Recupera il piatto sorgente
    $stmt = $db->prepare("SELECT * FROM dishes WHERE id = ?");
    $stmt->execute([$data['source_dish_id']]);
    $sourceDish = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$sourceDish) {
        http_response_code(404);
        echo json_encode(['error' => 'Piatto sorgente non trovato']);
        return;
    }
    
    // Verifica che il piatto clonato appartenga allo stesso ristorante
    if ($sourceDish['restaurant_id'] !== $data['restaurant_id']) {
        http_response_code(403);
        echo json_encode(['error' => 'Non puoi clonare piatti da altri ristoranti']);
        return;
    }
    
    // Genera nuovo ID per il piatto clonato
    $newId = uniqid('dish_');
    
    // Usa il nuovo nome se fornito, altrimenti clona il nome con "(Copia)"
    $newName = $data['new_name'] ?? ($sourceDish['name'] . ' (Copia)');
    $newDescription = $data['new_description'] ?? $sourceDish['description'];
    $newNameEn = $data['new_name_en'] ?? $sourceDish['name_en'];
    $newDescriptionEn = $data['new_description_en'] ?? $sourceDish['description_en'];
    $newPrice = $data['new_price'] ?? $sourceDish['price'];
    $newCategory = $data['new_category'] ?? $sourceDish['category'];
    $newCategoryId = $data['new_category_id'] ?? $sourceDish['category_id'];
    
    // Copia gli allergeni dal piatto sorgente
    $allergens = $sourceDish['allergens'];
    
    try {
        $stmt = $db->prepare("INSERT INTO dishes (id, restaurant_id, category, category_id, name, description, name_en, description_en, price, image_url, available, allergens) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $newId,
            $data['restaurant_id'],
            $newCategory,
            $newCategoryId,
            $newName,
            $newDescription,
            $newNameEn,
            $newDescriptionEn,
            $newPrice,
            $sourceDish['image_url'],
            $data['available'] ?? $sourceDish['available'],
            $allergens
        ]);
        
        echo json_encode([
            'success' => true,
            'id' => $newId,
            'message' => "Piatto clonato con successo",
            'cloned_data' => [
                'name' => $newName,
                'description' => $newDescription,
                'price' => $newPrice,
                'category' => $newCategory,
                'image_url' => $sourceDish['image_url']
            ]
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore nella clonazione: ' . $e->getMessage()]);
    }
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
    $name_en = $data['name_en'] ?? $currentDish['name_en'];
    $description_en = $data['description_en'] ?? $currentDish['description_en'];
    $price = $data['price'] ?? $currentDish['price'];
    $image_url = $data['image_url'] ?? $currentDish['image_url'];
    $available = isset($data['available']) ? $data['available'] : $currentDish['available'];
    
    // Gestione allergeni: priorità a quelli manuali, poi auto-estrazione se testo cambia, altrimenti mantieni
    if (!empty($data['allergens']) && is_array($data['allergens'])) {
        $allergens = array_map('strtolower', $data['allergens']);
    } elseif (
        (isset($data['name']) && $data['name'] !== $currentDish['name']) ||
        (isset($data['description']) && $data['description'] !== $currentDish['description'])
    ) {
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
    
    $stmt = $db->prepare("UPDATE dishes SET category = ?, category_id = ?, name = ?, description = ?, name_en = ?, description_en = ?, price = ?, image_url = ?, available = ?, allergens = ? WHERE id = ?");
    $result = $stmt->execute([
        $category, 
        $category_id,
        $name, 
        $description,
        $name_en,
        $description_en,
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

/**
 * Gestisce la richiesta di aggiunta menu dal form pubblico
 * Invia email a info@trovapiatto.it
 */
function submitMenuRequest() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Nessun dato ricevuto']);
        return;
    }
    
    $required = ['restaurant_name', 'cuisine', 'address', 'contact_name', 'phone', 'email'];
    foreach ($required as $field) {
        if (empty($input[$field])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => "Campo obbligatorio mancante: $field"]);
            return;
        }
    }
    
    $name = htmlspecialchars(trim($input['restaurant_name']));
    $cuisine = htmlspecialchars(trim($input['cuisine']));
    $address = htmlspecialchars(trim($input['address']));
    $contact = htmlspecialchars(trim($input['contact_name']));
    $phone = htmlspecialchars(trim($input['phone']));
    $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
    $website = htmlspecialchars(trim($input['website'] ?? ''));
    $dishCount = intval($input['dish_count'] ?? 0);
    $notes = htmlspecialchars(trim($input['notes'] ?? ''));
    
    // Log su file come backup
    $logDir = __DIR__ . '/logs';
    if (!is_dir($logDir)) mkdir($logDir, 0755, true);
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'restaurant_name' => $name,
        'cuisine' => $cuisine,
        'address' => $address,
        'contact_name' => $contact,
        'phone' => $phone,
        'email' => $email,
        'website' => $website,
        'dish_count' => $dishCount,
        'notes' => $notes
    ];
    $logFile = $logDir . '/menu-requests.json';
    $logs = [];
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true) ?? [];
    }
    $logs[] = $logEntry;
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    $to = 'info@trovapiatto.it';
    $subject = "Nuova Richiesta Menu: $name";
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
            .header { background: #e73a3a; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
            .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
            .field { margin-bottom: 12px; }
            .label { font-weight: bold; color: #555; }
            .value { color: #222; }
            .footer { background: #333; color: white; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; }
            .badge { display: inline-block; background: #4CAF50; color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; margin-top: 10px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>trovapiatto - Nuova Richiesta Menu</h1>
            <span class='badge'>PRIMO ANNO GRATUITO</span>
        </div>
        <div class='content'>
            <h2>Dettagli Ristorante</h2>
            <div class='field'><span class='label'>Nome:</span> <span class='value'>$name</span></div>
            <div class='field'><span class='label'>Tipo Cucina:</span> <span class='value'>$cuisine</span></div>
            <div class='field'><span class='label'>Indirizzo:</span> <span class='value'>$address</span></div>
            " . ($website ? "<div class='field'><span class='label'>Sito Web:</span> <span class='value'>$website</span></div>" : "") . "
            " . ($dishCount > 0 ? "<div class='field'><span class='label'>N. Piatti:</span> <span class='value'>$dishCount</span></div>" : "") . "
            
            <h2>Contatto</h2>
            <div class='field'><span class='label'>Referente:</span> <span class='value'>$contact</span></div>
            <div class='field'><span class='label'>Telefono:</span> <span class='value'>$phone</span></div>
            <div class='field'><span class='label'>Email:</span> <span class='value'>$email</span></div>
            
            " . ($notes ? "<h2>Note</h2><div class='field'><span class='value'>$notes</span></div>" : "") . "
            
            <br>
            <p><strong>Azione richiesta:</strong> Creare il ristorante e caricare il menu su trovapiatto.</p>
        </div>
        <div class='footer'>
            trovapiatto.it - Richiesta automatica dal form aggiungi-menu
        </div>
    </body>
    </html>";
    
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: trovapiatto <noreply@trovapiatto.it>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    $sent = @mail($to, $subject, $body, $headers);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Richiesta ricevuta! Ti contatteremo entro 24 ore.',
        'email_sent' => (bool)$sent
    ]);
}

/**
 * Cerca piatti in tutti i menu, restituisce risultati con info ristorante
 * Ordine: rating ristorante * peso + numero recensioni * peso
 * GET /api.php?action=search-dishes&q=nome_piatto
 */
function searchDishes($db) {
    $q = isset($_GET['q']) ? trim($_GET['q']) : '';
    if (strlen($q) < 2) {
        http_response_code(400);
        echo json_encode(['error' => 'Query troppo corta (minimo 2 caratteri)']);
        return;
    }

    // --- Intento "pasto + citta" (es. "pranzo Bari"): nessun piatto si chiama
    // "pranzo", quindi puliamo la query da pasto/citta/parole riempitive.
    // Se non resta nulla, ripieghiamo sui piatti tipici di quel pasto a Bari
    // (stessa guida curata di app/assets/js/meal-intent.js).
    $mealFallbacks = [
        'pranzo'    => ['orecchiette', 'tiella', 'focaccia', 'panzerotto', 'burrata', 'branzino'],
        'cena'      => ['branzino', 'pizza', 'crudo', 'polpo', 'burrata', 'frutta di mare'],
        'colazione' => ['pasticciotto', 'caffe', 'cornetto', 'focaccia'],
        'merenda'   => ['focaccia', 'panzerotto', 'sgagliozze', 'pasticciotto'],
        'aperitivo' => ['taralli', 'burrata', 'focaccia', 'crudo'],
        'brunch'    => ['focaccia', 'burrata', 'pasticciotto', 'caffe'],
    ];
    $mealWords = [
        'pranzo' => 'pranzo', 'pranzi' => 'pranzo', 'pranzare' => 'pranzo',
        'cena' => 'cena', 'cene' => 'cena', 'cenare' => 'cena', 'stasera' => 'cena', 'apericena' => 'cena',
        'colazione' => 'colazione', 'colazioni' => 'colazione', 'breakfast' => 'colazione',
        'merenda' => 'merenda', 'merende' => 'merenda', 'spuntino' => 'merenda', 'spuntini' => 'merenda',
        'aperitivo' => 'aperitivo', 'aperitivi' => 'aperitivo', 'spritz' => 'aperitivo',
        'brunch' => 'brunch',
    ];
    $stopwords = ['bari', 'dove', 'mangiare', 'mangio', 'mangi', 'cosa', 'quale', 'quali',
        'ristorante', 'ristoranti', 'buon', 'buono', 'buona', 'miglior', 'migliore', 'migliori',
        'vicino', 'centro', 'oggi', 'sera', 'mezzogiorno', 'pomeriggio',
        'a', 'di', 'da', 'in', 'con', 'per', 'il', 'lo', 'la', 'i', 'gli', 'le',
        'un', 'una', 'uno', 'del', 'della', 'dei', 'delle', 'al', 'allo', 'alla', 'sul', 'sulla'];

    $norm = function ($s) {
        $s = mb_strtolower($s, 'UTF-8');
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        return $s;
    };
    $normQ = ' ' . preg_replace('/[^a-z0-9 ]/', ' ', $norm($q)) . ' ';

    $detectedMeal = null;
    foreach ($mealWords as $w => $meal) {
        if (strpos($normQ, ' ' . $w . ' ') !== false) { $detectedMeal = $meal; break; }
    }
    $wantsBari = strpos($normQ, ' bari ') !== false;

    // Dividi la query in parole singole per ricerca multipla
    $words = array_filter(explode(' ', $q), function($w) { return strlen($w) >= 2; });

    if (empty($words)) {
        $words = [$q];
    }

    // Rimuovi pasto/citta/stopwords: "pranzo a Bari" -> [] ; "pranzo orecchiette" -> [orecchiette]
    $cleanWords = [];
    foreach ($words as $w) {
        $nw = trim(preg_replace('/[^a-z0-9]/', '', $norm($w)));
        if ($nw === '' || isset($mealWords[$nw]) || in_array($nw, $stopwords, true)) continue;
        $cleanWords[] = $w;
    }
    $words = array_values($cleanWords);

    $useOr = false;
    if (empty($words)) {
        if ($detectedMeal && isset($mealFallbacks[$detectedMeal])) {
            // Fallback: piatti tipici del pasto (match ANY, non ALL)
            $words = $mealFallbacks[$detectedMeal];
            $useOr = true;
        } else {
            echo json_encode([
                'success' => true, 'query' => $q, 'total' => 0, 'data' => [],
                'hint' => 'Prova con il nome di un piatto (es. orecchiette) o con un pasto (es. pranzo Bari)'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
    }

    // Costruisci WHERE: AND per ogni parola (ricerca piatto) oppure OR (fallback pasto)
    $whereParts = [];
    $params = [];
    foreach ($words as $word) {
        $searchTerm = '%' . $word . '%';
        $whereParts[] = "(d.name LIKE ? OR d.description LIKE ? OR d.category LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    $whereClause = implode($useOr ? ' OR ' : ' AND ', $whereParts);

    // Se l'utente ha nominato Bari, i ristoranti di Bari salgono in cima (senza escludere gli altri)
    $bariBoost = $wantsBari ? "(r.city LIKE '%Bari%') DESC, " : '';

    // Cerca piatti che matchano tutte le parole
    static $hasHoursCol = null;
    if ($hasHoursCol === null) {
        try {
            $cstmt = $db->query("SHOW COLUMNS FROM restaurants LIKE 'available_hours'");
            $hasHoursCol = $cstmt->rowCount() > 0;
        } catch (Exception $e) { $hasHoursCol = false; }
    }
    $hoursField = $hasHoursCol ? ",\n            r.available_hours" : "";
    $stmt = $db->prepare("
        SELECT 
            d.id, d.name, d.description, d.price, d.category,
            r.id as restaurant_id, r.name as restaurant_name, r.slug as restaurant_slug,
            r.logo_url, r.city, r.cuisine, r.latitude, r.longitude$hoursField,
            COALESCE(r.rating, 0) as restaurant_rating,
            (SELECT COUNT(*) FROM reviews WHERE restaurant_id = r.id) as review_count,
            (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE restaurant_id = r.id) as avg_user_rating
        FROM dishes d
        JOIN restaurants r ON d.restaurant_id = r.id
        WHERE $whereClause
        ORDER BY 
            $bariBoost
            (COALESCE(r.rating, 0) * 0.4 + 
             (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE restaurant_id = r.id) * 0.4 +
             LEAST((SELECT COUNT(*) FROM reviews WHERE restaurant_id = r.id) / 10, 1) * 0.2
            ) DESC,
            r.name ASC
        LIMIT 20
    ");
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    $out = [
        'success' => true,
        'query' => $q,
        'total' => count($results),
        'data' => $results,
    ];
    if ($detectedMeal) $out['meal'] = $detectedMeal;
    if ($wantsBari) $out['city'] = 'Bari';
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
}

/**
 * GET /api.php?action=get-starred-restaurants
 * Returns dishes from restaurants with michelin_stars > 0
 */
function getStarredRestaurants($db) {
    $stmt = $db->prepare("
        SELECT 
            MIN(d.id) as id, d.name, MIN(d.description) as description, MIN(d.price) as price, MIN(d.category) as category,
            r.id as restaurant_id, r.name as restaurant_name, r.slug as restaurant_slug,
            r.logo_url, r.city, r.cuisine, r.latitude, r.longitude,
            r.michelin_stars,
            COALESCE(r.rating, 0) as restaurant_rating,
            (SELECT COUNT(*) FROM reviews WHERE restaurant_id = r.id) as review_count,
            (SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE restaurant_id = r.id) as avg_user_rating
        FROM dishes d
        JOIN restaurants r ON d.restaurant_id = r.id
        WHERE r.michelin_stars > 0
        GROUP BY d.name, r.id
        ORDER BY r.michelin_stars DESC, r.name ASC, category ASC
        LIMIT 50
    ");
    $stmt->execute();
    $results = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'total' => count($results),
        'data' => $results
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Aggiunge una recensione/valutazione per un ristorante
 * POST /api.php?action=add-review
 * Body: { "restaurant_id": "rest_xxx", "user_name": "Mario", "rating": 4.5, "comment": "Ottimo!" }
 */
function addReview($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data || !isset($data['restaurant_id']) || !isset($data['rating'])) {
        http_response_code(400);
        echo json_encode(['error' => 'restaurant_id e rating sono obbligatori']);
        return;
    }
    
    $rating = floatval($data['rating']);
    if ($rating < 1 || $rating > 5) {
        http_response_code(400);
        echo json_encode(['error' => 'Il rating deve essere tra 1 e 5']);
        return;
    }
    
    $id = uniqid('rev_');
    $userName = $data['user_name'] ?? 'Anonimo';
    $comment = $data['comment'] ?? null;
    $dishId = $data['dish_id'] ?? null;
    
    $stmt = $db->prepare("INSERT INTO reviews (id, restaurant_id, dish_id, user_name, rating, comment) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $data['restaurant_id'], $dishId, $userName, $rating, $comment]);
    
    awardReviewPoints($db, $userName, $rating, $data['restaurant_id'], $dishId);

    // Aggiorna il rating medio del ristorante
    $avgStmt = $db->prepare("SELECT COALESCE(AVG(rating), 0) as avg_rating FROM reviews WHERE restaurant_id = ?");
    $avgStmt->execute([$data['restaurant_id']]);
    $avgRating = $avgStmt->fetch();
    
    if ($avgRating && isset($avgRating['avg_rating'])) {
        $updateStmt = $db->prepare("UPDATE restaurants SET rating = ? WHERE id = ?");
        $updateStmt->execute([round($avgRating['avg_rating'], 1), $data['restaurant_id']]);
    }
    
    echo json_encode([
        'success' => true, 
        'id' => $id,
        'new_avg_rating' => $avgRating['avg_rating'] ?? null
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Ottiene le recensioni di un ristorante
 * GET /api.php?action=get-reviews&restaurant_id=rest_xxx
 */
function getReviews($db) {
    $rid = isset($_GET['restaurant_id']) ? $_GET['restaurant_id'] : null;
    if (!$rid) {
        http_response_code(400);
        echo json_encode(['error' => 'restaurant_id mancante']);
        return;
    }
    
    $stmt = $db->prepare("
        SELECT r.*, d.name as dish_name
        FROM reviews r
        LEFT JOIN dishes d ON r.dish_id = d.id
        WHERE r.restaurant_id = ?
        ORDER BY r.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$rid]);
    $reviews = $stmt->fetchAll();
    
    // Statistiche
    $statsStmt = $db->prepare("
        SELECT 
            COUNT(*) as total_reviews,
            COALESCE(AVG(rating), 0) as avg_rating,
            SUM(CASE WHEN rating >= 4.5 THEN 1 ELSE 0 END) as excellent,
            SUM(CASE WHEN rating >= 4 AND rating < 4.5 THEN 1 ELSE 0 END) as good,
            SUM(CASE WHEN rating >= 3 AND rating < 4 THEN 1 ELSE 0 END) as average,
            SUM(CASE WHEN rating < 3 THEN 1 ELSE 0 END) as poor
        FROM reviews WHERE restaurant_id = ?
    ");
    $statsStmt->execute([$rid]);
    $stats = $statsStmt->fetch();
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'data' => $reviews
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Ottiene i piatti piu cercati/valutati (per homepage)
 * GET /api.php?action=get-top-dishes&limit=10
 */
function getTopDishes($db) {
    $limit = isset($_GET['limit']) ? min(intval($_GET['limit']), 20) : 10;
    
    $stmt = $db->prepare("
        SELECT 
            d.name, d.price, d.category,
            r.name as restaurant_name, r.slug as restaurant_slug, r.logo_url,
            COALESCE(r.rating, 0) as restaurant_rating,
            (SELECT COUNT(*) FROM reviews WHERE restaurant_id = r.id) as review_count
        FROM dishes d
        JOIN restaurants r ON d.restaurant_id = r.id
        WHERE d.price IS NOT NULL AND d.price > 0
        ORDER BY 
            (COALESCE(r.rating, 0) * 0.5 + LEAST((SELECT COUNT(*) FROM reviews WHERE restaurant_id = r.id) / 5, 1) * 0.5) DESC
        LIMIT ?
    ");
    $stmt->execute([$limit]);
    $dishes = $stmt->fetchAll();
    
    echo json_encode(['success' => true, 'data' => $dishes], JSON_UNESCAPED_UNICODE);
}

// ===== COMMUNITY LOYALTY SYSTEM =====

function awardReviewPoints($db, $userName, $rating, $restaurantId, $dishId) {
    $user = $db->prepare("SELECT * FROM community_users WHERE username = ?");
    $user->execute([$userName]);
    $u = $user->fetch();

    if (!$u) {
        $uid = uniqid('usr_');
        $db->prepare("INSERT INTO community_users (id, username, display_name, points, total_reviews, positive_reviews) VALUES (?, ?, ?, 0, 0, 0)")
            ->execute([$uid, $userName, $userName]);
        $user->execute([$userName]);
        $u = $user->fetch();
    }

    $points = 10;
    $reasons = ['Recensione lasciata'];

    if ($rating >= 4) {
        $points += 10;
        $reasons[] = 'Recensione positiva (+10)';
    }
    if ($rating >= 4.5) {
        $points += 5;
        $reasons[] = 'Recensione eccellente (+5)';
    }

    $firstCheck = $db->prepare("SELECT COUNT(*) as c FROM reviews WHERE user_name = ? AND id != (SELECT MAX(id) FROM reviews WHERE user_name = ?)");
    $firstCheck->execute([$userName, $userName]);
    if ($firstCheck->fetch()['c'] === 0) {
        $points += 50;
        $reasons[] = 'Prima recensione (+50)';
    }

    $restCount = $db->prepare("SELECT COUNT(DISTINCT restaurant_id) as c FROM reviews WHERE user_name = ?");
    $restCount->execute([$userName]);
    $rc = $restCount->fetch()['c'];

    $db->prepare("UPDATE community_users SET total_reviews = total_reviews + 1, positive_reviews = positive_reviews + ? WHERE id = ?")
        ->execute([$rating >= 4 ? 1 : 0, $u['id']]);

    $today = date('Y-m-d');
    if ($u['last_active_date'] === $today) {
        // same day, no streak change
    } elseif ($u['last_active_date'] === date('Y-m-d', strtotime('-1 day'))) {
        $db->prepare("UPDATE community_users SET streak_days = streak_days + 1, last_active_date = ? WHERE id = ?")
            ->execute([$today, $u['id']]);
    } else {
        $db->prepare("UPDATE community_users SET streak_days = 1, last_active_date = ? WHERE id = ?")
            ->execute([$today, $u['id']]);
    }

    $logStmt = $db->prepare("INSERT INTO community_points_log (id, user_id, points, reason, reference_type, reference_id) VALUES (?, ?, ?, ?, ?, ?)");
    $logStmt->execute([uniqid('pt_'), $u['id'], $points, implode(', ', $reasons), 'review', null]);

    $db->prepare("UPDATE community_users SET points = points + ? WHERE id = ?")->execute([$points, $u['id']]);

    $newUser = $db->prepare("SELECT * FROM community_users WHERE id = ?");
    $newUser->execute([$u['id']]);
    $nu = $newUser->fetch();
    $newLevel = floor(($nu['points']) / 100) + 1;
    if ($newLevel > $nu['level']) {
        $db->prepare("UPDATE community_users SET level = ? WHERE id = ?")->execute([$newLevel, $u['id']]);
    }

    checkAndAwardBadges($db, $u['id']);
}

function seedCommunityBadges($db) {
    $count = $db->query("SELECT COUNT(*) as c FROM community_badges")->fetch()['c'];
    if ($count > 0) return;

    $badges = [
        ['b001', 'Prima Recensione', 'Hai lasciato la tua prima recensione', 'fa-star', '#f59e0b', 'bronze', 0, 1, null],
        ['b002', 'Esperto Palato', '10 recensioni lasciate', 'fa-utensils', '#8b5cf6', 'silver', 0, 10, null],
        ['b003', 'Critico Famoso', '25 recensioni lasciate', 'fa-crown', '#f59e0b', 'gold', 0, 25, null],
        ['b004', 'Leggenda del Gusto', '50 recensioni lasciate', 'fa-trophy', '#ef4444', 'platinum', 0, 50, null],
        ['b005', 'Cuore dOro', '10 recensioni positive di fila', 'fa-heart', '#ef4444', 'silver', 0, 0, '10_positive_streak'],
        ['b006', 'Cercatore di Sapori', 'Hai recensito 5 ristoranti diversi', 'fa-compass', '#06b6d4', 'bronze', 0, 0, '5_restaurants'],
        ['b007', 'Viaggiatore Gastronomico', 'Hai recensito 10 ristoranti diversi', 'fa-plane', '#8b5cf6', 'silver', 0, 0, '10_restaurants'],
        ['b008', 'Mattiniero', 'Hai recensito prima delle 10', 'fa-sun', '#f97316', 'bronze', 0, 0, 'early_bird'],
        ['b009', 'Nottambulo', 'Hai recensito dopo le 22', 'fa-moon', '#6366f1', 'bronze', 0, 0, 'night_owl'],
        ['b010', 'Centurione', 'Raggiungi 100 punti', 'fa-shield', '#10b981', 'silver', 100, 0, null],
        ['b011', 'Mille', 'Raggiungi 1000 punti', 'fa-gem', '#f59e0b', 'gold', 1000, 0, null],
        ['b012', 'Fidato', 'Sei attivo da 7 giorni consecutivi', 'fa-calendar-check', '#06b6d4', 'bronze', 0, 0, '7_day_streak'],
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO community_badges (id, name, description, icon, color, tier, points_required, reviews_required, special_condition) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($badges as $b) {
        $stmt->execute($b);
    }
}

function registerUser($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    $username = trim($data['username'] ?? '');
    $displayName = trim($data['display_name'] ?? '');

    if (!$username || strlen($username) < 2) {
        http_response_code(400);
        echo json_encode(['error' => 'Username richiesto (min 2 caratteri)']);
        return;
    }

    $existing = $db->prepare("SELECT * FROM community_users WHERE username = ?");
    $existing->execute([$username]);
    $user = $existing->fetch();

    if ($user) {
        if ($displayName && $displayName !== $user['display_name']) {
            $upd = $db->prepare("UPDATE community_users SET display_name = ? WHERE id = ?");
            $upd->execute([$displayName, $user['id']]);
            $user['display_name'] = $displayName;
        }
        echo json_encode(['success' => true, 'user' => $user, 'new' => false], JSON_UNESCAPED_UNICODE);
        return;
    }

    $id = uniqid('usr_');
    $stmt = $db->prepare("INSERT INTO community_users (id, username, display_name) VALUES (?, ?, ?)");
    $stmt->execute([$id, $username, $displayName ?: $username]);

    $bonusPoints = 50;
    $logStmt = $db->prepare("INSERT INTO community_points_log (id, user_id, points, reason) VALUES (?, ?, ?, ?)");
    $logStmt->execute([uniqid('pt_'), $id, $bonusPoints, 'Benvenuto nella community!']);

    $updStmt = $db->prepare("UPDATE community_users SET points = ? WHERE id = ?");
    $updStmt->execute([$bonusPoints, $id]);

    $newUser = $db->prepare("SELECT * FROM community_users WHERE id = ?");
    $newUser->execute([$id]);

    echo json_encode([
        'success' => true,
        'user' => $newUser->fetch(),
        'new' => true,
        'welcome_bonus' => $bonusPoints
    ], JSON_UNESCAPED_UNICODE);
}

function addPoints($db) {
    $data = json_decode(file_get_contents('php://input'), true);
    $userId = $data['user_id'] ?? '';
    $points = intval($data['points'] ?? 0);
    $reason = $data['reason'] ?? '';
    $refType = $data['reference_type'] ?? null;
    $refId = $data['reference_id'] ?? null;

    if (!$userId || $points <= 0 || !$reason) {
        http_response_code(400);
        echo json_encode(['error' => 'user_id, points e reason sono obbligatori']);
        return;
    }

    $logStmt = $db->prepare("INSERT INTO community_points_log (id, user_id, points, reason, reference_type, reference_id) VALUES (?, ?, ?, ?, ?, ?)");
    $logStmt->execute([uniqid('pt_'), $userId, $points, $reason, $refType, $refId]);

    $db->prepare("UPDATE community_users SET points = points + ? WHERE id = ?")->execute([$points, $userId]);

    $user = $db->prepare("SELECT * FROM community_users WHERE id = ?");
    $user->execute([$userId]);
    $userData = $user->fetch();

    if ($userData) {
        $newLevel = floor($userData['points'] / 100) + 1;
        if ($newLevel > $userData['level']) {
            $db->prepare("UPDATE community_users SET level = ? WHERE id = ?")->execute([$newLevel, $userId]);
            $userData['level'] = $newLevel;
        }
    }

    checkAndAwardBadges($db, $userId);

    echo json_encode(['success' => true, 'new_balance' => $userData['points'] ?? 0], JSON_UNESCAPED_UNICODE);
}

function getUserProfile($db) {
    $username = $_GET['username'] ?? '';
    if (!$username) {
        http_response_code(400);
        echo json_encode(['error' => 'username mancante']);
        return;
    }

    $stmt = $db->prepare("SELECT * FROM community_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Utente non trovato']);
        return;
    }

    $logStmt = $db->prepare("SELECT * FROM community_points_log WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
    $logStmt->execute([$user['id']]);
    $log = $logStmt->fetchAll();

    $badgesStmt = $db->prepare("SELECT b.*, ub.earned_at FROM community_badges b JOIN community_user_badges ub ON b.id = ub.badge_id WHERE ub.user_id = ? ORDER BY ub.earned_at DESC");
    $badgesStmt->execute([$user['id']]);
    $badges = $badgesStmt->fetchAll();

    $nextLevel = $user['level'] * 100;
    $progress = $nextLevel > 0 ? min(100, ($user['points'] % 100)) : 0;

    echo json_encode([
        'success' => true,
        'user' => $user,
        'points_log' => $log,
        'badges' => $badges,
        'progress' => ['next_level_points' => $nextLevel, 'progress_pct' => $progress]
    ], JSON_UNESCAPED_UNICODE);
}

function getCommunityStats($db) {
    $stats = ['users' => 0, 'reviews' => 0, 'restaurants' => 0, 'dishes' => 0];
    $queries = [
        'users' => "SELECT COUNT(*) as c FROM community_users",
        'reviews' => "SELECT COUNT(*) as c FROM reviews",
        'restaurants' => "SELECT COUNT(*) as c FROM restaurants",
        'dishes' => "SELECT COUNT(*) as c FROM dishes",
    ];
    foreach ($queries as $k => $sql) {
        try {
            $row = $db->query($sql)->fetch();
            $stats[$k] = (int)($row['c'] ?? 0);
        } catch (Exception $e) {
            // tabella mancante: resta 0
        }
    }
    echo json_encode(['success' => true, 'data' => $stats], JSON_UNESCAPED_UNICODE);
}

function getLeaderboard($db) {
    $period = $_GET['period'] ?? 'all';
    $limit = min(intval($_GET['limit'] ?? 20), 50);

    $sql = "SELECT id, username, display_name, avatar_url, points, level, total_reviews, positive_reviews, streak_days FROM community_users";

    if ($period === 'week') {
        $sql .= " WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
    } elseif ($period === 'month') {
        $sql .= " WHERE joined_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
    }

    $sql .= " ORDER BY points DESC LIMIT " . (int)$limit;

    $stmt = $db->query($sql);
    $users = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $users, 'period' => $period], JSON_UNESCAPED_UNICODE);
}

function getBadges($db) {
    $stmt = $db->query("SELECT * FROM community_badges ORDER BY points_required ASC, reviews_required ASC");
    $badges = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $badges], JSON_UNESCAPED_UNICODE);
}

function getUserBadges($db) {
    $userId = $_GET['user_id'] ?? '';
    if (!$userId) {
        http_response_code(400);
        echo json_encode(['error' => 'user_id mancante']);
        return;
    }

    $stmt = $db->prepare("
        SELECT b.*, ub.earned_at,
        CASE WHEN ub.id IS NOT NULL THEN 1 ELSE 0 END as earned
        FROM community_badges b
        LEFT JOIN community_user_badges ub ON b.id = ub.badge_id AND ub.user_id = ?
        ORDER BY b.points_required ASC
    ");
    $stmt->execute([$userId]);
    $badges = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $badges], JSON_UNESCAPED_UNICODE);
}

function checkAndAwardBadges($db, $userId) {
    $user = $db->prepare("SELECT * FROM community_users WHERE id = ?");
    $user->execute([$userId]);
    $u = $user->fetch();
    if (!$u) return;

    $allBadges = $db->query("SELECT * FROM community_badges")->fetchAll();
    $earnedIds = $db->prepare("SELECT badge_id FROM community_user_badges WHERE user_id = ?");
    $earnedIds->execute([$userId]);
    $earned = array_column($earnedIds->fetchAll(), 'badge_id');

    foreach ($allBadges as $badge) {
        if (in_array($badge['id'], $earned)) continue;

        $shouldAward = false;

        if ($badge['points_required'] > 0 && $u['points'] >= $badge['points_required']) {
            $shouldAward = true;
        }
        if ($badge['reviews_required'] > 0 && $u['total_reviews'] >= $badge['reviews_required']) {
            $shouldAward = true;
        }
        if ($badge['special_condition'] === '7_day_streak' && $u['streak_days'] >= 7) {
            $shouldAward = true;
        }
        if ($badge['special_condition'] === '10_positive_streak') {
            $check = $db->prepare("SELECT COUNT(*) as c FROM (SELECT rating FROM reviews WHERE user_name = ? ORDER BY created_at DESC LIMIT 10) t WHERE t.rating >= 4");
            $check->execute([$u['username']]);
            if ($check->fetch()['c'] >= 10) $shouldAward = true;
        }

        if ($shouldAward) {
            $ins = $db->prepare("INSERT IGNORE INTO community_user_badges (id, user_id, badge_id) VALUES (?, ?, ?)");
            $ins->execute([uniqid('ub_'), $userId, $badge['id']]);
        }
    }
}

function getRecentReviews($db) {
    $limit = min(max((int)($_GET['limit'] ?? 20), 1), 100);
    $stmt = $db->query("SELECT id, user_name, rating, comment, created_at, dish_id, restaurant_id FROM reviews ORDER BY created_at DESC LIMIT $limit");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($reviews as &$r) {
        if (!empty($r['dish_id'])) {
            $d = $db->prepare("SELECT name, price, image_url FROM dishes WHERE id = ?");
            $d->execute([$r['dish_id']]);
            $r['dish'] = $d->fetch(PDO::FETCH_ASSOC) ?: null;
        } else {
            $r['dish'] = null;
        }
        if (!empty($r['restaurant_id'])) {
            $d = $db->prepare("SELECT name, slug, logo_url FROM restaurants WHERE id = ?");
            $d->execute([$r['restaurant_id']]);
            $r['restaurant'] = $d->fetch(PDO::FETCH_ASSOC) ?: null;
        } else {
            $r['restaurant'] = null;
        }
    }
    $total = $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
    echo json_encode(['success' => true, 'data' => $reviews, 'total' => (int)$total, 'v' => 3]);
}

function deleteReview($db) {
    $id = $_GET['id'] ?? '';
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID mancante']);
        return;
    }
    $stmt = $db->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['success' => true, 'deleted' => $stmt->rowCount()]);
}

function getWhatPeopleAte($db) {
    $limit = min(max((int)($_GET['limit'] ?? 20), 1), 100);
    $stmt = $db->query("SELECT dish_id, COUNT(*) as review_count, ROUND(AVG(rating),1) as avg_rating, MAX(created_at) as last_reviewed FROM reviews WHERE dish_id IS NOT NULL AND dish_id != '' GROUP BY dish_id ORDER BY review_count DESC LIMIT $limit");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as &$r) {
        $d = $db->prepare("SELECT d.name, d.price, d.image_url, COALESCE(c.name, d.category) AS category FROM dishes d LEFT JOIN categories c ON d.category_id = c.id WHERE d.id = ?");
        $d->execute([$r['dish_id']]);
        $r['dish'] = $d->fetch(PDO::FETCH_ASSOC) ?: null;
        $rest = $db->prepare("SELECT rest.name, rest.slug, rest.logo_url FROM dishes d JOIN restaurants rest ON d.restaurant_id = rest.id WHERE d.id = ?");
        $rest->execute([$r['dish_id']]);
        $r['restaurant'] = $rest->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    echo json_encode(['success' => true, 'data' => $rows]);
}

function getInput() {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (strpos($contentType, 'application/json') !== false) {
        return json_decode(file_get_contents('php://input'), true);
    }
    // Form-encoded (from $.post with form.serialize())
    $input = file_get_contents('php://input');
    $data = [];
    parse_str($input, $data);
    if (!empty($data)) return $data;
    // Fallback: $_POST
    return !empty($_POST) ? $_POST : null;
}

function submitOrder($db) {
    $data = getInput();
    if (!$data || empty($data['restaurant_id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dati ordine mancanti']);
        return;
    }

    $id = uniqid('order_');
    $restaurantId = $data['restaurant_id'];
    $customerName = $data['customer_name'] ?? null;
    $customerPhone = $data['customer_phone'] ?? null;
    $customerEmail = $data['customer_email'] ?? null;
    $itemsRaw = $data['items'] ?? '[]';
    $items = is_string($itemsRaw) ? json_decode($itemsRaw, true) : $itemsRaw;
    $subtotal = $data['subtotal'] ?? 0;
    $shipping = $data['shipping'] ?? 0;
    $total = $data['total'] ?? 0;
    $currency = $data['currency'] ?? '€';
    $orderType = $data['order_type'] ?? 'delivery';
    $notes = $data['notes'] ?? null;

    $stmt = $db->prepare("INSERT INTO orders (id, restaurant_id, customer_name, customer_phone, customer_email, items, subtotal, shipping, total, currency, order_type, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$id, $restaurantId, $customerName, $customerPhone, $customerEmail, json_encode($items), $subtotal, $shipping, $total, $currency, $orderType, $notes]);

    $restStmt = $db->prepare("SELECT phone, name FROM restaurants WHERE id = ?");
    $restStmt->execute([$restaurantId]);
    $restaurant = $restStmt->fetch(PDO::FETCH_ASSOC);

    $response = ['success' => true, 'id' => $id, 'tipo' => $orderType, 'status' => 'pending'];

    if ($restaurant && !empty($restaurant['phone'])) {
        $whatsappPhone = preg_replace('/[^0-9]/', '', $restaurant['phone']);
        $itemsText = '';
        if (is_array($items)) {
            foreach ($items as $item) {
                $qty = $item['qty'] ?? 1;
                $title = $item['title'] ?? 'Piatto';
                $price = $item['price'] ?? 0;
                $itemsText .= "  {$qty}x {$title} - €" . number_format($price, 2) . "\n";
            }
        }
        $orderMsg = "🍽️ *Nuovo Ordine* - {$restaurant['name']}\n\n";
        if ($customerName) $orderMsg .= "👤 {$customerName}\n";
        if ($customerPhone) $orderMsg .= "📞 {$customerPhone}\n";
        $orderMsg .= "\n📦 *Dettagli:*\n{$itemsText}\n";
        $orderMsg .= "💰 Totale: €" . number_format($total, 2) . "\n";
        if ($notes) $orderMsg .= "📝 Note: {$notes}\n";
        $response['whatsapp_url'] = "https://wa.me/{$whatsappPhone}?text=" . urlencode($orderMsg);
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}

function submitReservation($db) {
    $data = getInput();
    if (!$data || empty($data['restaurant_id']) || empty($data['customer_name']) || empty($data['customer_phone']) || empty($data['reservation_date']) || empty($data['reservation_time'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dati prenotazione mancanti']);
        return;
    }

    $id = uniqid('res_');
    $restaurantId = $data['restaurant_id'];
    $customerName = $data['customer_name'];
    $customerPhone = $data['customer_phone'];
    $customerEmail = $data['customer_email'] ?? null;
    $partySize = $data['party_size'] ?? 1;
    $reservationDate = $data['reservation_date'];
    $reservationTime = $data['reservation_time'];
    $notes = $data['notes'] ?? null;

    // Detect existing columns to avoid errors
    $existingCols = [];
    $colResult = $db->query("SHOW COLUMNS FROM reservations");
    foreach ($colResult->fetchAll() as $col) {
        $existingCols[] = $col['Field'];
    }

    $fields = ['id', 'restaurant_id', 'customer_name', 'customer_phone'];
    $values = [$id, $restaurantId, $customerName, $customerPhone];
    $placeholders = array_fill(0, count($values), '?');

    // Handle legacy column name mapping
    $colAliases = [
        'res_date' => $reservationDate,
        'reservation_date' => $reservationDate,
        'res_time' => $reservationTime,
        'reservation_time' => $reservationTime,
        'num_people' => $partySize,
        'party_size' => $partySize,
        'guests' => $partySize,
    ];

    // Add mandatory legacy columns first
    foreach ($existingCols as $col) {
        if (in_array($col, $fields)) continue;
        if (isset($colAliases[$col]) && !in_array($col, $fields)) {
            $fields[] = $col;
            $values[] = $colAliases[$col];
            $placeholders[] = '?';
        }
    }

    // Add remaining optional columns
    if (in_array('customer_email', $existingCols) && !in_array('customer_email', $fields)) { $fields[] = 'customer_email'; $values[] = $customerEmail; $placeholders[] = '?'; }
    if (in_array('notes', $existingCols) && !in_array('notes', $fields)) { $fields[] = 'notes'; $values[] = $notes; $placeholders[] = '?'; }

    $sql = "INSERT INTO reservations (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $db->prepare($sql);
    $stmt->execute($values);

    $response = ['success' => true, 'id' => $id, 'status' => 'pending'];

    $restStmt = $db->prepare("SELECT phone, name FROM restaurants WHERE id = ?");
    $restStmt->execute([$restaurantId]);
    $restaurant = $restStmt->fetch(PDO::FETCH_ASSOC);

    if ($restaurant && !empty($restaurant['phone'])) {
        $whatsappPhone = preg_replace('/[^0-9]/', '', $restaurant['phone']);
        $resMsg = "📅 *Nuova Prenotazione* - {$restaurant['name']}\n\n";
        $resMsg .= "👤 {$customerName}\n📞 {$customerPhone}\n";
        $resMsg .= "👥 {$partySize} persone\n";
        $resMsg .= "📆 {$reservationDate} alle {$reservationTime}\n";
        if ($notes) $resMsg .= "📝 Note: {$notes}\n";
        $response['whatsapp_url'] = "https://wa.me/{$whatsappPhone}?text=" . urlencode($resMsg);
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
}
?>
