<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
tp_cors_headers('GET, POST, PUT, DELETE, OPTIONS', 'Content-Type, Authorization');

define('TOKEN_EXPIRY', 24 * 60 * 60); // 24 ore
if (!defined('JWT_SECRET')) define('JWT_SECRET', tp_jwt_secret());

try {
    $db = tp_db_connect();
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// === JWT FUNCTIONS ===
function b64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function b64url_decode($data) {
    return base64_decode(strtr($data, '-_', '+/'));
}

function generateToken($userId, $email, $role, $name) {
    $header = b64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = b64url_encode(json_encode([
        'sub' => $userId,
        'email' => $email,
        'role' => $role,
        'name' => $name,
        'iat' => time(),
        'exp' => time() + TOKEN_EXPIRY
    ]));
    $signature = b64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$signature";
}

function verifyToken($token) {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;

    [$header, $payload, $signature] = $parts;
    $expected = b64url_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));

    if (!hash_equals($expected, $signature)) return false;

    $data = json_decode(b64url_decode($payload), true);
    if (!$data || $data['exp'] < time()) return false;

    return $data;
}

function getInput() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) $input = [];
    return $input;
}

// === ROUTING ===
$action = isset($_GET['action']) ? trim($_GET['action']) : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($action)) {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
}

switch($action) {
    case 'login':
        login($db);
        break;
    case 'register':
        register($db);
        break;
    case 'verify':
        verify($db);
        break;
    case 'logout':
        logout();
        break;
    case 'change-password':
        changePassword($db);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Azione non trovata']);
}

function tp_login_throttle_check() {
    tp_throttle('login', 10, 300, true);
}

// === LOGIN ===
function login($db) {
    tp_login_throttle_check();
    $input = getInput();
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? $input['password'] : '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email e password sono obbligatori']);
        return;
    }

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Credenziali non valide']);
        return;
    }

    // Update last_login
    $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);

    $token = generateToken($user['id'], $user['email'], $user['role'], $user['name']);

    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'avatar_url' => $user['avatar_url']
        ]
    ]);
}

// === REGISTER ===
function register($db) {
    $input = getInput();
    $email = isset($input['email']) ? trim($input['email']) : '';
    $password = isset($input['password']) ? $input['password'] : '';
    $name = isset($input['name']) ? trim($input['name']) : '';
    $role = isset($input['role']) ? trim($input['role']) : 'user';

    if (empty($email) || empty($password) || empty($name)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email, password e nome sono obbligatori']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Email non valida']);
        return;
    }

    if (strlen($password) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'La password deve avere almeno 6 caratteri']);
        return;
    }

    // Check if email exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['error' => 'Email già registrata']);
        return;
    }

    // Validate role
    $allowedRoles = ['admin', 'agent', 'restaurant_owner', 'user'];
    if (!in_array($role, $allowedRoles)) $role = 'user';

    $userId = 'usr_' . bin2hex(random_bytes(16));
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (id, email, password_hash, name, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $email, $passwordHash, $name, $role]);

    $token = generateToken($userId, $email, $role, $name);

    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $userId,
            'name' => $name,
            'email' => $email,
            'role' => $role
        ]
    ]);
}

// === VERIFY ===
function verify($db) {
    $token = isset($_GET['token']) ? $_GET['token'] : '';
    if (empty($token)) {
        http_response_code(400);
        echo json_encode(['error' => 'Token mancante']);
        return;
    }

    $payload = verifyToken($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['error' => 'Token scaduto o non valido']);
        return;
    }

    // Fetch fresh user data
    $stmt = $db->prepare("SELECT id, name, email, role, avatar_url, created_at FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$payload['sub']]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Utente non trovato']);
        return;
    }

    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
}

// === LOGOUT ===
function logout() {
    // JWT is stateless - client removes token
    echo json_encode(['success' => true, 'message' => 'Logout effettuato']);
}

// === CHANGE PASSWORD ===
function changePassword($db) {
    $input = getInput();
    $token = isset($input['token']) ? $input['token'] : '';
    $oldPassword = isset($input['old_password']) ? $input['old_password'] : '';
    $newPassword = isset($input['new_password']) ? $input['new_password'] : '';

    if (empty($token) || empty($oldPassword) || empty($newPassword)) {
        http_response_code(400);
        echo json_encode(['error' => 'Token, vecchia e nuova password sono obbligatori']);
        return;
    }

    if (strlen($newPassword) < 6) {
        http_response_code(400);
        echo json_encode(['error' => 'La nuova password deve avere almeno 6 caratteri']);
        return;
    }

    $payload = verifyToken($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['error' => 'Token scaduto o non valido']);
        return;
    }

    $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$payload['sub']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($oldPassword, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Vecchia password non corretta']);
        return;
    }

    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$newHash, $payload['sub']]);

    echo json_encode(['success' => true, 'message' => 'Password aggiornata']);
}
