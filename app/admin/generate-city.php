<?php
require_once __DIR__ . '/config.php';
tp_require_setup_access();
/**
 * City Page Generator - Generate landing pages per città
 * Usage: php generate-city.php <city-slug>
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = tp_env('DB_HOST', '31.11.39.157');
$db = tp_env('DB_NAME', 'Sql1658368_5');
$user = tp_env('DB_USER', 'Sql1658368');
$pass = tp_env('DB_PASS', 'TrovaP.2026@');

$templatePath = __DIR__ . '/../city-template.html';
$baseDir = __DIR__ . '/../';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $slug = $argv[1] ?? null;
    if (!$slug) {
        // Generate all cities
        $cities = $pdo->query("SELECT slug FROM cities WHERE active = 1")->fetchAll(PDO::FETCH_COLUMN);
        echo "Generating pages for " . count($cities) . " cities...\n";
        foreach ($cities as $s) {
            generateCityPage($pdo, $s, $templatePath, $baseDir);
        }
        echo "Done!\n";
        exit;
    }

    generateCityPage($pdo, $slug, $templatePath, $baseDir);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

function generateCityPage($pdo, $slug, $templatePath, $baseDir) {
    if (!file_exists($templatePath)) {
        echo "Template not found: $templatePath\n";
        return;
    }

    $template = file_get_contents($templatePath);

    // Get city data
    $stmt = $pdo->prepare("SELECT * FROM cities WHERE slug = ?");
    $stmt->execute([$slug]);
    $city = $stmt->fetch();

    if (!$city) {
        echo "City not found: $slug\n";
        return;
    }

    // Count restaurants and dishes
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM restaurants WHERE city LIKE ?");
    $countStmt->execute([$city['name']]);
    $restCount = $countStmt->fetchColumn();

    $dishStmt = $pdo->prepare("SELECT COUNT(*) FROM dishes d JOIN restaurants r ON d.restaurant_id = r.id WHERE r.city LIKE ?");
    $dishStmt->execute([$city['name']]);
    $dishCount = $dishStmt->fetchColumn();

    // Generate description
    $descriptions = [
        'Bari' => 'La città vecchia, il Lungomare e i migliori ristoranti con menu digitale.',
        'Monopoli' => 'Il centro storico, le acque cristalline e la cucina pugliese autentica.',
        'Roma' => 'La Città Eterna con i suoi ristoranti storici e la cucina tradizionale.',
        'Milano' => 'La capitale della moda con ristoranti di alta cucina e tradizione.',
        'Napoli' => 'La pizza, il mare e i sapori della tradizione napoletana.',
        'Firenze' => 'La culla del Rinascimento con la cucina toscana più autentica.',
        'Bologna' => 'La grassa con la sua tradizione culinaria unica al mondo.',
        'Torino' => 'Eleganza piemontese e sapori delle Langhe.',
        'Palermo' => 'I mercati, le strade e i sapori della Sicilia.',
        'Catania' => 'L\'Etna, il pesce fresco e la tradizione siciliana.',
        'Lecce' => 'Il barocco, i咖啡 e la cucina salentina.',
        'Brindisi' => 'Il vino, il mare e la tradizione pugliese.',
        'Foggia' => 'La Capitanata, i campi di grano e la cucina dauna.',
        'Taranto' => 'La città dei due mari e la tradizione marittima.',
        'Modena' => 'Il balsamico, i motori e la cucina emiliana.',
        'Genova' => 'Il porto, il pesto e la cucina ligure.',
        'Venezia' => 'I canali, il cicchetti e la tradizione veneta.',
        'Verona' => 'L\'Arena, il vino Valpolicella e la cucina veronese.',
        'Padova' => 'Le università, il Prato della Valle e la cucina padovana.',
        'Cagliari' => 'La Sardegna, il mare e i sapori isolani.',
    ];

    $description = $descriptions[$city['name']] ?? "Scopri i menu digitali dei ristoranti a {$city['name']}. Menù, prezzi e piatti su trovapiatto.";

    // Replace placeholders
    $page = str_replace('{CITY_NAME}', $city['name'], $template);
    $page = str_replace('{CITY_SLUG}', $city['slug'], $page);
    $page = str_replace('{CITY_DESCRIPTION}', $description, $page);
    $page = str_replace('{RESTAURANT_COUNT}', $restCount, $page);
    $page = str_replace('{DISH_COUNT}', $dishCount, $page);
    $page = str_replace('{REGION}', $city['region'] ?? '', $page);

    // Create directory
    $dir = $baseDir . $city['slug'];
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    // Write page
    $file = $dir . '/index.html';
    file_put_contents($file, $page);
    echo "Generated: {$city['slug']}/index.html ($restCount restaurants, $dishCount dishes)\n";
}
