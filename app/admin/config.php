<?php
// Trovapiatto - Config centralizzata
// Valori letti da env con fallback per compatibilita' deploy attuale.
// Su hosting: impostare Variabili d'ambiente (o .env via getenv) per
// DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, JWT_SECRET, OCR_API_KEY.

function tp_load_dotenv() {
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;
    // Cerca .env nella root del progetto (2 livelli sopra app/admin)
    $candidates = [dirname(__DIR__, 2) . '/.env', __DIR__ . '/.env'];
    foreach ($candidates as $f) {
        if (!is_readable($f)) continue;
        foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k); $v = trim($v);
            if (strlen($v) >= 2 && (($v[0] === '"' && $v[-1] === '"') || ($v[0] === "'" && $v[-1] === "'"))) {
                $v = substr($v, 1, -1);
            }
            if (getenv($k) === false) {
                putenv("$k=$v");
                $_SERVER[$k] = $v;
            }
        }
        break;
    }
}
tp_load_dotenv();

function tp_env($key, $default = null) {
    $v = getenv($key);
    if ($v !== false && $v !== '') return $v;
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
    return $default;
}

function tp_db_config() {
    return [
        'host' => tp_env('DB_HOST', '31.11.39.157'),
        'name' => tp_env('DB_NAME', 'Sql1658368_5'),
        'user' => tp_env('DB_USER', 'Sql1658368'),
        'pass' => tp_env('DB_PASS', 'TrovaP.2026@'),
        'port' => (int)tp_env('DB_PORT', 3306),
    ];
}

function tp_jwt_secret() {
    return tp_env('JWT_SECRET', 'trovapiatto_jwt_secret_2026_very_long_key_for_hmac_sha256');
}

function tp_ocr_key() {
    return tp_env('OCR_API_KEY', 'K85400257588957');
}

function tp_db_connect() {
    $c = tp_db_config();
    $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
        $opts[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
    }
    return new PDO($dsn, $c['user'], $c['pass'], $opts);
}

// CORS ristretto: solo origin trovapiatto + localhost per sviluppo
function tp_cors_headers($allowedMethods = 'GET, POST, PUT, DELETE, OPTIONS', $allowedHeaders = 'Content-Type, Authorization, X-Auth-Token') {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $allowed = [
        'https://www.trovapiatto.it',
        'https://trovapiatto.it',
    ];
    // Consenti anteprime locali solo in sviluppo
    if (strpos($origin, 'http://localhost') === 0 || strpos($origin, 'http://127.0.0.1') === 0) {
        $allowed[] = $origin;
    }
    if (in_array($origin, $allowed, true)) {
        header("Access-Control-Allow-Origin: $origin");
        header('Vary: Origin');
    }
    // Niente wildcard: se origin non riconosciuta, nessun header CORS
    header("Access-Control-Allow-Methods: $allowedMethods");
    header("Access-Control-Allow-Headers: $allowedHeaders");
    header('Access-Control-Max-Age: 86400');
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
        http_response_code(200);
        exit();
    }
}

// Accesso riservato a script setup/debug/test:
// - CLI sempre consentita
// - via web serve ?key= uguale a TP_SETUP_KEY (fail-closed se non impostata)
function tp_require_setup_access() {
    if (php_sapi_name() === 'cli') return;
    $key = tp_env('TP_SETUP_KEY', '');
    $given = isset($_GET['key']) ? (string)$_GET['key'] : '';
    if ($key !== '' && $given !== '' && hash_equals($key, $given)) return;
    // Conta i tentativi respinti: oltre 20/10min = probabile probing -> mail con cooldown
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $dir = sys_get_temp_dir() . '/tp_throttle';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $f = $dir . '/setup_probe_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $ip) . '.json';
    $now = time();
    $d = ['count' => 0, 'first' => $now];
    if (is_file($f)) {
        $tmp = json_decode(@file_get_contents($f), true);
        if (is_array($tmp) && ($now - (int)($tmp['first'] ?? 0)) < 600) $d = $tmp;
    }
    $d['count'] = (int)($d['count'] ?? 0) + 1;
    @file_put_contents($f, json_encode($d));
    if ($d['count'] === 20) {
        tp_send_attack_alert('setup-probing', ['count' => $d['count'], 'uri' => ($_SERVER['REQUEST_URI'] ?? '?')]);
    }
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Accesso negato']);
    exit;
}

// Throttle generico file-based: max $max tentativi per $window secondi (per IP).
// Ritorna true se consentito, false se superato (e invia 429 JSON se $respond).
function tp_throttle($prefix, $max = 10, $window = 300, $respond = true, $alertKind = null) {
    if ($alertKind) $GLOBALS['tp_throttle_alert'] = $alertKind;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $dir = sys_get_temp_dir() . '/tp_throttle';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $f = $dir . '/' . $prefix . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $ip) . '.json';
    $now = time();
    $data = ['count' => 0, 'first' => $now];
    if (is_file($f)) {
        $d = json_decode(@file_get_contents($f), true);
        if (is_array($d) && ($now - (int)($d['first'] ?? 0)) < $window) $data = $d;
    }
    $data['count'] = (int)($data['count'] ?? 0) + 1;
    @file_put_contents($f, json_encode($data));
    if ($data['count'] > $max) {
        // $alertKind valorizzato dai chiamanti sensibili (login): mail con cooldown
        if (!empty($GLOBALS['tp_throttle_alert'])) {
            tp_send_attack_alert($GLOBALS['tp_throttle_alert'], ['count' => $data['count'], 'ip' => $ip]);
            $GLOBALS['tp_throttle_alert'] = null;
        }
        if ($respond && php_sapi_name() !== 'cli') {
            http_response_code(429);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'Troppi tentativi. Riprova tra qualche minuto.']);
            exit;
        }
        return false;
    }
    return true;
}

// Email destinatario allarmi anti-abuso (default richiesto dal proprietario)
function tp_alert_email() {
    return tp_env('ALERT_EMAIL', 'datafap@gmail.com');
}

// Invia allarme possibile attacco, max 1 mail/ora per tipo (cooldown anti-spam)
function tp_send_attack_alert($kind, $details = []) {
    $to = tp_alert_email();
    if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
    $dir = sys_get_temp_dir() . '/tp_alerts';
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    $f = $dir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$kind) . '.json';
    $now = time();
    $cooldown = (int)tp_env('ALERT_COOLDOWN', 3600);
    if (is_file($f)) {
        $d = json_decode(@file_get_contents($f), true);
        if (is_array($d) && ($now - (int)($d['last'] ?? 0)) < $cooldown) return false;
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $uri = ($_SERVER['REQUEST_METHOD'] ?? '?') . ' ' . ($_SERVER['REQUEST_URI'] ?? '?');
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '-';
    $subject = "[trovapiatto] possibile attacco: $kind";
    $body = "Tipo: $kind\nIP: $ip\nRichiesta: $uri\nUser-Agent: $ua\nData: " . date('Y-m-d H:i:s') . "\nDettagli: " . json_encode($details) . "\n";
    $headers = "From: trovapiatto <noreply@trovapiatto.it>\r\nContent-Type: text/plain; charset=UTF-8";
    $sent = @mail($to, $subject, $body, $headers);
    @file_put_contents($f, json_encode(['last' => $now, 'sent' => (bool)$sent]));
    return (bool)$sent;
}
