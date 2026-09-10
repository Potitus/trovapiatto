<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
/**
 * Genera una pagina menu da template.
 * Uso: php generate-menu.php <slug> [Nome Ristorante]
 * Esempio: php generate-menu.php la-battigia "La Battigia"
 */

if ($argc < 2) {
    echo "Uso: php generate-menu.php <slug> [Nome Ristorante]\n";
    exit(1);
}

$slug = $argv[1];
$name = $argv[2] ?? ucwords(str_replace('-', ' ', $slug));

$templateFile = __DIR__ . '/../../menu/menu-template.html';
$outputDir = __DIR__ . '/../../menu/' . $slug;
$outputFile = $outputDir . '/index.html';

if (!file_exists($templateFile)) {
    echo "ERRORE: Template al-pescatore non trovato\n";
    exit(1);
}

// Try to get restaurant logo from DB
$logoUrl = 'https://www.trovapiatto.it/logo/default-ristorante.svg';
try {
    $pdo = tp_db_connect();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("SELECT logo_url FROM restaurants WHERE slug = ?");
    $stmt->execute([$slug]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['logo_url'])) {
        $logoUrl = $row['logo_url'];
    }
} catch (Exception $e) {
    // Use default logo
}

$content = file_get_contents($templateFile);

// Replace placeholders
$content = str_replace('__RESTAURANT_NAME__', $name, $content);
$content = str_replace('__RESTAURANT_SLUG__', $slug, $content);
$content = str_replace('__RESTAURANT_LOGO__', $logoUrl, $content);
$content = str_replace("RESTAURANT_SLUG = 'al-pescatore'", "RESTAURANT_SLUG = '$slug'", $content);

// Create directory and write file
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}
file_put_contents($outputFile, $content);

echo "OK: $outputFile\n";
echo "Slug: $slug\n";
echo "Nome: $name\n";
echo "Logo: $logoUrl\n";
