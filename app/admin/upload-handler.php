<?php
require_once __DIR__ . '/config.php';
/**
 * Handler per upload di immagini
 * Questo file gestisce l'upload di immagini per i piatti
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/jwt-helper.php';

header('Content-Type: application/json; charset=utf-8');
tp_cors_headers('GET, POST, OPTIONS', 'Content-Type, Authorization');

// Funzione di upload direttamente qui per evitare dipendenze
function uploadImageDirect() {
    // Valida la richiesta
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'File non caricato correttamente', 'success' => false]);
        return;
    }
    
    $file = $_FILES['image'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    
    // Validazione dimensione
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(['error' => 'File troppo grande (max 5MB)', 'success' => false]);
        return;
    }
    
    // Validazione tipo reale (mai fidarsi del mime dichiarato dal client)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $realMime = $finfo->file($file['tmp_name']);
    $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($mimeToExt[$realMime])) {
        http_response_code(400);
        echo json_encode(['error' => 'Formato non supportato (jpg, png, webp)', 'success' => false]);
        return;
    }
    $fileExt = $mimeToExt[$realMime];
    
    // Crea cartella uploads se non esiste
    $uploadDir = __DIR__ . '/uploads';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    
    // Genera nome file univoco (estensione dal mime reale, mai dal nome originale)
    $filePath = $uploadDir . '/' . $fileName;
    
    // Sposta il file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Errore durante il salvataggio del file', 'success' => false]);
        return;
    }
    
    // Genera URL relativo
    $imageUrl = '/app/admin/uploads/' . $fileName;
    
    // Risposta di successo
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Immagine caricata con successo',
        'image_url' => $imageUrl,
        'filename' => $fileName
    ]);
}

// Esegui l'upload se è POST (solo admin autenticati)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyApiToken();
    uploadImageDirect();
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Richiesta non valida', 'success' => false]);
}
?>
