<?php
// image-handler.php - Gestisce upload e processamento immagini piatti

header('Content-Type: application/json; charset=utf-8');

try {
    // Verifica autenticazione
    session_start();
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Non autorizzato']);
        exit;
    }

    // Connessione DB
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'trovapiatto';

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $conn->set_charset("utf8mb4");

    if ($conn->connect_error) {
        throw new Exception("Errore di connessione: " . $conn->connect_error);
    }

    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // Upload immagine piatto
    if ($action === 'upload-dish-image') {
        if (!isset($_FILES['image']) || !isset($_POST['dish_id'])) {
            throw new Exception('File o dish_id mancante');
        }

        $file = $_FILES['image'];
        $dish_id = $_POST['dish_id'];

        // Validazioni
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Errore upload: ' . $file['error']);
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowed_types)) {
            throw new Exception('Tipo file non consentito: ' . $mime);
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('File troppo grande (max 5MB)');
        }

        // Crea cartella se non esiste
        $upload_dir = __DIR__ . '/../uploads/dishes';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Genera nome file univoco
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $dish_id . '_' . time() . '.' . $ext;
        $filepath = $upload_dir . '/' . $filename;

        // Sposta file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new Exception('Errore nel salvataggio del file');
        }

        // URL relativo per il database
        $image_url = '/app/uploads/dishes/' . $filename;

        // Aggiorna database
        $stmt = $conn->prepare("UPDATE dishes SET image_url = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Errore prepare: " . $conn->error);
        }

        $stmt->bind_param("ss", $image_url, $dish_id);
        if (!$stmt->execute()) {
            throw new Exception("Errore update: " . $stmt->error);
        }

        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Immagine caricata con successo',
            'image_url' => $image_url,
            'filename' => $filename
        ]);
        exit;
    }

    // Elimina immagine piatto
    if ($action === 'delete-dish-image') {
        if (!isset($_POST['dish_id'])) {
            throw new Exception('dish_id mancante');
        }

        $dish_id = $_POST['dish_id'];

        // Ottieni immagine attuale
        $stmt = $conn->prepare("SELECT image_url FROM dishes WHERE id = ?");
        $stmt->bind_param("s", $dish_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $dish = $result->fetch_assoc();
        $stmt->close();

        if ($dish && $dish['image_url']) {
            $file_path = __DIR__ . '/../..' . $dish['image_url'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        // Rimuovi dal database
        $stmt = $conn->prepare("UPDATE dishes SET image_url = NULL WHERE id = ?");
        $stmt->bind_param("s", $dish_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Immagine eliminata'
        ]);
        exit;
    }

    // Carica immagine da URL esterno
    if ($action === 'import-image-from-url') {
        if (!isset($_POST['dish_id']) || !isset($_POST['url'])) {
            throw new Exception('dish_id o URL mancante');
        }

        $dish_id = $_POST['dish_id'];
        $image_url = $_POST['url'];

        // Valida URL
        if (!filter_var($image_url, FILTER_VALIDATE_URL)) {
            throw new Exception('URL non valido');
        }

        // Scarica immagine
        $image_data = @file_get_contents($image_url, false, stream_context_create([
            'http' => ['timeout' => 10],
            'https' => ['timeout' => 10]
        ]));

        if ($image_data === false) {
            throw new Exception('Impossibile scaricare l\'immagine');
        }

        // Valida che sia un'immagine
        $upload_dir = __DIR__ . '/../uploads/dishes';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $temp_file = $upload_dir . '/temp_' . time();
        file_put_contents($temp_file, $image_data);

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $temp_file);
        finfo_close($finfo);

        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($mime, $allowed_types)) {
            unlink($temp_file);
            throw new Exception('URL non punta a un\'immagine valida: ' . $mime);
        }

        // Estensione basata su MIME
        $ext_map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif'
        ];
        $ext = $ext_map[$mime] ?? 'jpg';

        $filename = $dish_id . '_' . time() . '.' . $ext;
        $filepath = $upload_dir . '/' . $filename;

        rename($temp_file, $filepath);

        $image_url = '/app/uploads/dishes/' . $filename;

        // Aggiorna database
        $stmt = $conn->prepare("UPDATE dishes SET image_url = ? WHERE id = ?");
        $stmt->bind_param("ss", $image_url, $dish_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Immagine importata con successo',
            'image_url' => $image_url
        ]);
        exit;
    }

    throw new Exception('Azione non riconosciuta');

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
