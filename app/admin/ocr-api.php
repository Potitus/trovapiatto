<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/jwt-helper.php';
/**
 * OCR API per trovapiatto
 * Elabora foto di menu e estrae piatti, prezzi, descrizioni e allergeni
 */

header('Content-Type: application/json; charset=utf-8');
tp_cors_headers('GET, POST, OPTIONS', 'Content-Type');

// Configurazione DB
try {
    $db = tp_db_connect();
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore connessione database: ' . $e->getMessage()]);
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch($action) {
    case 'upload-menu-ocr':
        verifyApiToken();
        uploadAndProcessOCR($db);
        break;
    case 'import-dishes':
        verifyApiToken();
        importDishes($db);
        break;
    case 'check-ocr':
    case 'check-tesseract':
        checkOCR();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Azione non valida']);
}

/**
 * Carica foto e elabora OCR
 */
function uploadAndProcessOCR($db) {
    if (!isset($_FILES['files'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Nessun file caricato']);
        return;
    }

    $restaurantSlug = $_POST['restaurant_slug'] ?? '';
    $category = $_POST['category'] ?? 'Piatti';

    if (empty($restaurantSlug)) {
        http_response_code(400);
        echo json_encode(['error' => 'Seleziona un ristorante']);
        return;
    }

    // Verifica che il ristorante esista
    $stmt = $db->prepare("SELECT id, name FROM restaurants WHERE slug = ?");
    $stmt->execute([$restaurantSlug]);
    $restaurant = $stmt->fetch();

    if (!$restaurant) {
        http_response_code(404);
        echo json_encode(['error' => 'Ristorante non trovato']);
        return;
    }

    $allDishes = [];
    $errors = [];
    $uploadDir = sys_get_temp_dir() . '/trovapiatto_ocr/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['files']['error'][$index] !== UPLOAD_ERR_OK) {
            $errors[] = "Errore upload file " . ($_FILES['files']['name'][$index] ?? $index);
            continue;
        }

        $fileName = uniqid('menu_') . '_' . basename($_FILES['files']['name'][$index]);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $filePath)) {
            try {
                // Elabora l'immagine con OCR
                $dishes = processImageWithOCR($filePath, $category);
                $allDishes = array_merge($allDishes, $dishes);
            } catch(Exception $e) {
                $errors[] = "Errore elaborazione " . $_FILES['files']['name'][$index] . ": " . $e->getMessage();
            }

            // Pulisci il file temporaneo
            @unlink($filePath);
        } else {
            $errors[] = "Impossibile salvare " . $_FILES['files']['name'][$index];
        }
    }

    // Rimuovi duplicati basandosi sul nome
    $uniqueDishes = [];
    $seenNames = [];
    foreach ($allDishes as $dish) {
        $normalizedName = strtolower(trim($dish['name']));
        if (!in_array($normalizedName, $seenNames)) {
            $seenNames[] = $normalizedName;
            $uniqueDishes[] = $dish;
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'dishes_count' => count($uniqueDishes),
            'preview' => $uniqueDishes,
            'errors' => $errors,
            'restaurant_id' => $restaurant['id'],
            'restaurant_name' => $restaurant['name']
        ]
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Elabora un'immagine con OCR
 * Usa Tesseract se disponibile, altrimenti usa OCR.space (gratuito)
 */
function processImageWithOCR($imagePath, $defaultCategory) {
    $dishes = [];

    // Prova a usare Tesseract se disponibile (solo su VPS/Dedicato)
    $tesseractPath = findTesseract();

    if ($tesseractPath) {
        $text = runTesseract($tesseractPath, $imagePath);
    } else {
        // Hosting condiviso - usa OCR.space API (gratuita)
        $text = ocrSpaceAPI($imagePath);
    }

    if (empty($text)) {
        throw new Exception("Impossibile leggere il testo dall'immagine. Verifica che l'immagine sia nitida e ben illuminata.");
    }

    // Parsa il testo estratto
    $dishes = parseOCRText($text, $defaultCategory);

    if (empty($dishes)) {
        throw new Exception("Nessun piatto riconosciuto nel testo. Prova con una foto più nitida o inserisci i piatti manualmente.");
    }

    return $dishes;
}

/**
 * Trova il percorso di Tesseract
 */
function findTesseract() {
    // Tesseract non è disponibile su hosting condiviso Aruba
    // Usiamo OCR.space API come alternativa
    return null;
}

/**
 * Esegue Tesseract su un'immagine
 */
function runTesseract($tesseractPath, $imagePath) {
    $outputPath = tempnam(sys_get_temp_dir(), 'ocr_');
    $output = [];
    $returnCode = 0;

    // Esegui Tesseract con supporto italiano
    exec("{$tesseractPath} \"{$imagePath}\" \"{$outputPath}\" -l ita+eng 2>&1", $output, $returnCode);

    if ($returnCode !== 0) {
        throw new Exception("Errore Tesseract: " . implode("\n", $output));
    }

    $text = file_get_contents($outputPath . '.txt');
    @unlink($outputPath . '.txt');

    return $text;
}

/**
 * OCR.space API - gratuita fino a 25.000 richieste/mese
 * https://ocr.space/ocrapi/freeapi
 */
function ocrSpaceAPI($imagePath) {
    $url = 'https://api.ocr.space/parse/image';

    // Leggi e codifica l'immagine in base64
    $imageData = base64_encode(file_get_contents($imagePath));
    $mimeType = mime_content_type($imagePath);
    $dataUri = 'data:' . $mimeType . ';base64,' . $imageData;

    // Parametri API (chiave demo gratuita)
    $postFields = [
        'base64Image' => $dataUri,
        'language' => 'ita',      // Italiano
        'OCREngine' => '2',       // Engine 2 (migliore per menu)
        'isOverlayRequired' => 'false',
        'scale' => 'true',        // Migliora la qualita
        'isTable' => 'true'       // Ottimizza per tabelle/menu
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . getOCRspaceKey()
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        throw new Exception("Errore connessione OCR: " . $error);
    }

    if ($httpCode !== 200) {
        throw new Exception("Errore OCR API: HTTP " . $httpCode);
    }

    $result = json_decode($response, true);

    if (!$result) {
        throw new Exception("Risposta OCR non valida");
    }

    // Controlla errori
    if (isset($result['IsErroredOnProcessing']) && $result['IsErroredOnProcessing']) {
        $errorMsg = $result['ErrorMessage'] ?? ['Errore sconosciuto'];
        throw new Exception("OCR Error: " . implode(', ', $errorMsg));
    }

    // Estrai testo
    if (isset($result['ParsedResults'][0]['ParsedText'])) {
        return $result['ParsedResults'][0]['ParsedText'];
    }

    throw new Exception("Nessun testo estratto dall'immagine");
}

/**
 * Restituisce la chiave API OCR.space
 * Usa la chiave demo se non configurata
 */
function getOCRspaceKey() {
    // Chiave demo OCR.space (gratuita, limitata)
    // Per produzione, registrati gratis su https://ocr.space/ocrapi/freeapi
    return tp_ocr_key(); // da env OCR_API_KEY
}

/**
 * Parsa il testo OCR estratto e identifica i piatti
 */
function parseOCRText($text, $defaultCategory) {
    $dishes = [];
    $lines = explode("\n", $text);
    $currentCategory = $defaultCategory;

    $allergenKeywords = [
        'glutine' => ['grano', 'pasta', 'pane', 'orzo', 'farina', 'semola'],
        'lattosio' => ['latte', 'formaggio', 'burro', 'panna', 'mozzarella', 'ricotta'],
        'uova' => ['uova', 'uovo'],
        'pesce' => ['pesce', 'salmone', 'branzino', 'tonno', 'orata'],
        'crostacei' => ['gambero', 'camarone', 'aragosta', 'scampo'],
        'molluschi' => ['vongola', 'cozza', 'seppia', 'calamaro', 'polpo'],
        'noci' => ['noce', 'mandorla', 'nocciola', 'pistacchio'],
        'arachidi' => ['arachide'],
        'sesamo' => ['sesamo'],
        'soia' => ['soia'],
        'sedano' => ['sedano'],
        'solfiti' => ['vino', 'aceto']
    ];

    foreach ($lines as $line) {
        $line = trim($line);

        if (empty($line)) {
            continue;
        }

        // Rileva se è una intestazione di categoria
        if (isCategoryHeader($line)) {
            $currentCategory = cleanCategoryName($line);
            continue;
        }

        // Prova a estrarre un piatto dalla riga
        $dish = extractDishFromLine($line, $currentCategory, $allergenKeywords);

        if ($dish) {
            $dishes[] = $dish;
        }
    }

    return $dishes;
}

/**
 * Verifica se una riga è un'intestazione di categoria
 */
function isCategoryHeader($line) {
    $categoryPatterns = [
        '/^(ANTIPASTI|PRIMI|SECONDI|CONTORNI|PIATTI|DOLCI|DESSERT|BEVANDE|BIRRE|VINI|FRITTI|SALATE|PESCE|CARNE|VEGETALI|VEGAN|SENZA GLUTINE)/i',
        '/^[-=]{3,}$/',
        '/^[A-Z\s]{3,}$/',
    ];

    foreach ($categoryPatterns as $pattern) {
        if (preg_match($pattern, $line)) {
            return true;
        }
    }

    return false;
}

/**
 * Pulisce il nome della categoria
 */
function cleanCategoryName($line) {
    $line = trim($line);
    $line = preg_replace('/^[-=\s]+|[-=\s]+$/u', '', $line);
    return strtoupper($line);
}

/**
 * Estrae un piatto da una riga di testo
 */
function extractDishFromLine($line, $category, $allergenKeywords) {
    // Pattern per: Nome Piatto | Prezzo | Descrizione | Allergeni
    $patterns = [
        // Formato: Nome | Prezzo | Descrizione | Allergeni
        '/^(.+?)\s*[|\-–]\s*(\d+[.,]?\d*)\s*[|\-–]?\s*(.*?)\s*[|\-–]?\s*(.*?)$/',
        // Formato: Nome - Prezzo €
        '/^(.+?)\s*[-–]\s*(?:€\s*)?(\d+[.,]?\d*)\s*$/',
        // Formato: Nome Prezzo (senza separatore)
        '/^(.+?)\s+(\d+[.,]\d{2})\s*$/',
    ];

    foreach ($patterns as $pattern) {
        if (preg_match($pattern, $line, $matches)) {
            $name = trim($matches[1]);
            $price = floatval(str_replace(',', '.', $matches[2]));
            $description = isset($matches[3]) ? trim($matches[3]) : '';
            $allergensText = isset($matches[4]) ? trim($matches[4]) : '';

            // Valida il prezzo
            if ($price <= 0 || $price > 1000) {
                continue;
            }

            // Valida il nome (deve avere almeno 2 caratteri)
            if (strlen($name) < 2) {
                continue;
            }

            // Ignora intestazioni e voci non piatti
            if (preg_match('/^(TOTALE|SUBTOTALE|IVA|SERVIZIO|CONTANTI|CARTA)/i', $name)) {
                continue;
            }

            // Estrai allergeni dal testo
            $allergens = extractAllergens($allergensText . ' ' . $description, $allergenKeywords);

            return [
                'name' => $name,
                'price' => $price,
                'description' => $description,
                'category' => $category,
                'allergens' => $allergens
            ];
        }
    }

    return null;
}

/**
 * Estrae allergeni dal testo
 */
function extractAllergens($text, $keywords) {
    $allergens = [];
    $text_lower = strtolower($text);

    foreach ($keywords as $allergen => $kw) {
        foreach ($kw as $k) {
            if (strpos($text_lower, $k) !== false) {
                if (!in_array($allergen, $allergens)) {
                    $allergens[] = $allergen;
                }
                break;
            }
        }
    }

    return $allergens;
}

/**
 * Importa piatti nel database
 */
function importDishes($db) {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!$data || !isset($data['dishes']) || !isset($data['restaurant_slug'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Dati mancanti']);
        return;
    }

    $restaurantSlug = $data['restaurant_slug'];
    $dishes = $data['dishes'];

    // Ottieni restaurant_id
    $stmt = $db->prepare("SELECT id FROM restaurants WHERE slug = ?");
    $stmt->execute([$restaurantSlug]);
    $restaurant = $stmt->fetch();

    if (!$restaurant) {
        http_response_code(404);
        echo json_encode(['error' => 'Ristorante non trovato']);
        return;
    }

    $restaurantId = $restaurant['id'];
    $importedCount = 0;
    $failedCount = 0;
    $results = [];

    foreach ($dishes as $dish) {
        try {
            $id = uniqid('dish_');
            $name = $dish['name'] ?? '';
            $description = $dish['description'] ?? '';
            $price = $dish['price'] ?? 0;
            $category = $dish['category'] ?? 'Piatti';
            $allergens = $dish['allergens'] ?? [];

            if (empty($name)) {
                $failedCount++;
                $results[] = ['success' => false, 'name' => $name, 'error' => 'Nome mancante'];
                continue;
            }

            // Verifica se il piatto esiste già
            $checkStmt = $db->prepare("SELECT id FROM dishes WHERE restaurant_id = ? AND name = ?");
            $checkStmt->execute([$restaurantId, $name]);
            if ($checkStmt->fetch()) {
                $failedCount++;
                $results[] = ['success' => false, 'name' => $name, 'error' => 'Piatto già esistente'];
                continue;
            }

            // Inserisci il piatto
            $stmt = $db->prepare("INSERT INTO dishes (id, restaurant_id, category, name, description, price, available, allergens) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$id, $restaurantId, $category, $name, $description, $price, true, json_encode($allergens)]);

            $importedCount++;
            $results[] = ['success' => true, 'name' => $name, 'id' => $id];
        } catch(Exception $e) {
            $failedCount++;
            $results[] = ['success' => false, 'name' => $dish['name'] ?? 'Sconosciuto', 'error' => $e->getMessage()];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'imported' => $importedCount,
            'failed' => $failedCount,
            'results' => $results
        ]
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * Verifica il sistema OCR disponibile
 */
function checkOCR() {
    echo json_encode([
        'success' => true,
        'tesseract_installed' => false,
        'tesseract_path' => null,
        'tesseract_version' => '',
        'ocr_method' => 'OCR.space (cloud, gratuito)',
        'message' => 'OCR.space API attiva - funziona su hosting condiviso'
    ], JSON_UNESCAPED_UNICODE);
}
