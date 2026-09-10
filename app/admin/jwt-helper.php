<?php
require_once __DIR__ . '/config.php';
// JWT Helper - Separato per evitare OPcache stale
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
