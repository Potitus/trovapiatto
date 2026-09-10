<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');
tp_cors_headers('GET, POST, PUT, DELETE, OPTIONS', 'Content-Type, Authorization');

try {
    $pdo = tp_db_connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get-recipes':
        handleGetRecipes($pdo);
        break;
    case 'get-recipe':
        handleGetRecipe($pdo);
        break;
    case 'create-recipe':
        handleCreateRecipe($pdo);
        break;
    case 'update-recipe':
        handleUpdateRecipe($pdo);
        break;
    case 'delete-recipe':
        handleDeleteRecipe($pdo);
        break;
    case 'search-recipes':
        handleSearchRecipes($pdo);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
}

function handleGetRecipes($pdo) {
    $page = max(1, intval($_GET['page'] ?? 1));
    $limit = min(50, max(1, intval($_GET['limit'] ?? 12)));
    $offset = ($page - 1) * $limit;
    $restaurantId = $_GET['restaurant_id'] ?? null;

    $where = '';
    $params = [];
    if ($restaurantId) {
        // Accept both slug and ID
        $stmt = $pdo->prepare("SELECT id FROM restaurants WHERE slug = ? OR id = ?");
        $stmt->execute([$restaurantId, $restaurantId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $where = "WHERE r.restaurant_id = ?";
            $params[] = $row['id'];
        }
    }

    $sql = "SELECT r.*, rest.name as restaurant_name, rest.slug as restaurant_slug
            FROM recipes r
            LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
            $where
            ORDER BY r.created_at DESC
            LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $countSql = "SELECT COUNT(*) FROM recipes r $where";
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $total = $countStmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'recipes' => array_map(function($r) {
            $r['ingredients'] = json_decode($r['ingredients'], true);
            $r['steps'] = json_decode($r['steps'], true);
            return $r;
        }, $recipes),
        'total' => $total,
        'page' => $page,
        'pages' => ceil($total / $limit)
    ]);
}

function handleGetRecipe($pdo) {
    $slug = $_GET['slug'] ?? '';
    if (!$slug) {
        http_response_code(400);
        echo json_encode(['error' => 'Slug required']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT r.*, rest.name as restaurant_name, rest.slug as restaurant_slug, rest.address, rest.city
        FROM recipes r
        LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
        WHERE r.slug = ?
    ");
    $stmt->execute([$slug]);
    $recipe = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$recipe) {
        http_response_code(404);
        echo json_encode(['error' => 'Recipe not found']);
        return;
    }

    $recipe['ingredients'] = json_decode($recipe['ingredients'], true);
    $recipe['steps'] = json_decode($recipe['steps'], true);

    echo json_encode(['success' => true, 'recipe' => $recipe]);
}

function handleCreateRecipe($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'POST required']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $required = ['restaurant_id', 'dish_name'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' required"]);
            return;
        }
    }

    $slug = $data['slug'] ?? preg_replace('/[^a-z0-9-]/', '-', strtolower($data['dish_name']));
    $slug = trim($slug, '-');

    $stmt = $pdo->prepare("INSERT INTO recipes (restaurant_id, dish_name, slug, prep_time_minutes, cook_time_minutes, difficulty, portions, ingredients, steps, image_url, description)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                           ON DUPLICATE KEY UPDATE
                           prep_time_minutes = VALUES(prep_time_minutes),
                           cook_time_minutes = VALUES(cook_time_minutes),
                           difficulty = VALUES(difficulty),
                           portions = VALUES(portions),
                           ingredients = VALUES(ingredients),
                           steps = VALUES(steps),
                           image_url = VALUES(image_url),
                           description = VALUES(description)");

    $stmt->execute([
        $data['restaurant_id'],
        $data['dish_name'],
        $slug,
        $data['prep_time_minutes'] ?? 15,
        $data['cook_time_minutes'] ?? 30,
        $data['difficulty'] ?? 'media',
        $data['portions'] ?? 4,
        json_encode($data['ingredients'] ?? [], JSON_UNESCAPED_UNICODE),
        json_encode($data['steps'] ?? [], JSON_UNESCAPED_UNICODE),
        $data['image_url'] ?? '',
        $data['description'] ?? ''
    ]);

    echo json_encode(['success' => true, 'slug' => $slug, 'message' => 'Recipe created']);
}

function handleUpdateRecipe($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
        http_response_code(405);
        echo json_encode(['error' => 'POST/PUT required']);
        return;
    }

    $slug = $_GET['slug'] ?? '';
    if (!$slug) {
        http_response_code(400);
        echo json_encode(['error' => 'Slug required']);
        return;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    $fields = [];
    $params = [];
    $allowed = ['prep_time_minutes', 'cook_time_minutes', 'difficulty', 'portions', 'ingredients', 'steps', 'image_url', 'description', 'dish_name'];

    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            if (in_array($field, ['ingredients', 'steps'])) {
                $params[] = json_encode($data[$field], JSON_UNESCAPED_UNICODE);
            } else {
                $params[] = $data[$field];
            }
        }
    }

    if (empty($fields)) {
        http_response_code(400);
        echo json_encode(['error' => 'No fields to update']);
        return;
    }

    $params[] = $slug;
    $sql = "UPDATE recipes SET " . implode(', ', $fields) . " WHERE slug = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Recipe updated']);
}

function handleDeleteRecipe($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        http_response_code(405);
        echo json_encode(['error' => 'POST/DELETE required']);
        return;
    }

    $slug = $_GET['slug'] ?? '';
    if (!$slug) {
        http_response_code(400);
        echo json_encode(['error' => 'Slug required']);
        return;
    }

    $stmt = $pdo->prepare("DELETE FROM recipes WHERE slug = ?");
    $stmt->execute([$slug]);

    echo json_encode(['success' => true, 'message' => 'Recipe deleted']);
}

function handleSearchRecipes($pdo) {
    $q = $_GET['q'] ?? '';
    if (!$q) {
        http_response_code(400);
        echo json_encode(['error' => 'Query required']);
        return;
    }

    $stmt = $pdo->prepare("
        SELECT r.*, rest.name as restaurant_name, rest.slug as restaurant_slug
        FROM recipes r
        LEFT JOIN restaurants rest ON r.restaurant_id = rest.id
        WHERE r.dish_name LIKE ? OR r.description LIKE ?
        ORDER BY r.created_at DESC
        LIMIT 20
    ");
    $like = "%$q%";
    $stmt->execute([$like, $like]);
    $recipes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'recipes' => array_map(function($r) {
            $r['ingredients'] = json_decode($r['ingredients'], true);
            $r['steps'] = json_decode($r['steps'], true);
            return $r;
        }, $recipes)
    ]);
}
?>
