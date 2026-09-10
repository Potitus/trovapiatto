<?php
// Endpoint semplice per registrare pageviews in un file JSON
// Non espone dati: scrive solo conteggi. Visualizzazione solo da area admin.

// Path del file di storage (relativo alla cartella admin)
$storageFile = __DIR__ . '/page_views.json';

// Recupera il path dalla richiesta (POST JSON o form) oppure usa REQUEST_URI
$input = file_get_contents('php://input');
$data = null;
if ($input) {
    $maybe = json_decode($input, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($maybe['path'])) {
        $data = $maybe;
    }
}

if (!$data && isset($_POST['path'])) {
    $data = ['path' => $_POST['path']];
}

// fallback: use REQUEST_URI
if (!$data) {
    $data = ['path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/'];
}

$path = mb_substr(trim((string)$data['path']), 0, 200);
if ($path === '' || $path[0] !== '/') $path = '/';
$timestamp = time();

// carica esistente
$counts = [];
if (file_exists($storageFile)) {
    $raw = file_get_contents($storageFile);
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $counts = $decoded;
    }
}

// struttura: counts[path] = ['count' => n, 'last' => ts]
// cap anti-abuso: max 2000 path distinti (evict meno recenti)
if (!isset($counts[$path]) && count($counts) > 2000) {
    uasort($counts, fn($a, $b) => (($a['last'] ?? 0) <=> ($b['last'] ?? 0)));
    $counts = array_slice($counts, -1500, null, true);
}
if (!isset($counts[$path])) {
    $counts[$path] = ['count' => 0, 'last' => 0];
}
$counts[$path]['count'] += 1;
$counts[$path]['last'] = $timestamp;

// aggiorna totale
$counts['_total'] = isset($counts['_total']) ? $counts['_total'] + 1 : 1;

// salva in modo atomico
$tmp = $storageFile . '.tmp';
file_put_contents($tmp, json_encode($counts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
rename($tmp, $storageFile);

// Risposta minima
header('Content-Type: application/json');
echo json_encode(['status' => 'ok']);

?>
