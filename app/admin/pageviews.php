<?php
require __DIR__ . '/auth-check.php';

// Path to the JSON file used by log_pageview.php
$jsonFile = __DIR__ . '/page_views.json';

// Helper: load data safely
function load_pageviews($path) {
    if (!file_exists($path)) return [];
    $content = @file_get_contents($path);
    if (!$content) return [];
    $data = json_decode($content, true);
    if (!is_array($data)) return [];
    return $data;
}

$data = load_pageviews($jsonFile);

// If requested, send CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="pageviews.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['path', 'count', 'last']);
    if (isset($data['counts']) && is_array($data['counts'])) {
        foreach ($data['counts'] as $path => $info) {
            $count = isset($info['count']) ? $info['count'] : 0;
            $last = isset($info['last']) ? $info['last'] : '';
            fputcsv($out, [$path, $count, $last]);
        }
    }
    fclose($out);
    exit;
}

// Render simple admin page
?><!doctype html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Pageviews - Admin</title>
    <style>
        body{font-family:Arial,Helvetica,sans-serif;margin:20px;color:#222}
        table{border-collapse:collapse;width:100%;max-width:1100px}
        th,td{border:1px solid #ddd;padding:8px;text-align:left}
        th{background:#f4f4f4}
        .meta{margin-bottom:12px}
        .small{color:#666;font-size:0.9em}
        .actions{margin-bottom:12px}
        .btn{display:inline-block;padding:8px 12px;background:#0078d4;color:#fff;text-decoration:none;border-radius:4px}
    </style>
</head>
<body>
    <h1>Conteggi visite pagine</h1>
    <p class="small">Questa pagina è protetta e visibile solo agli amministratori autenticati.</p>

    <div class="actions">
        <a class="btn" href="?export=csv">Scarica CSV</a>
    </div>

    <?php if (empty($data) || empty($data['counts'])): ?>
        <p>Nessun dato disponibile ancora. Il logger `log_pageview.php` registra le visite in `page_views.json`.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Path</th>
                    <th>Conteggio</th>
                    <th>Ultima visita</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['counts'] as $path => $info):
                    $count = isset($info['count']) ? intval($info['count']) : 0;
                    $last = isset($info['last']) ? $info['last'] : '';
                ?>
                    <tr>
                        <td><?php echo htmlspecialchars($path); ?></td>
                        <td><?php echo $count; ?></td>
                        <td><?php echo htmlspecialchars($last); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>
