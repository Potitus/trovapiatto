<?php
// favicon.php - Serve icone PNG ridimensionate dai sorgenti esistenti.
// Uso: /favicon.php?size=32x32 (accetta anche ?size=32)
// Risolve i 404: decine di pagine + site.webmanifest puntano qui.

$srcPng = __DIR__ . '/logo/apple-touch-icon.png';
if (!is_file($srcPng)) $srcPng = __DIR__ . '/logo/logo_ufficiale.png';
$fallbackIco = __DIR__ . '/favicon.ico';

$sizeParam = $_GET['size'] ?? '32x32';
if (preg_match('/^(\\d{2,3})(?:x\\d{2,3})?$/', $sizeParam, $m)) {
    $size = (int)$m[1];
} else {
    $size = 32;
}
$size = max(16, min(512, $size));

header('Cache-Control: public, max-age=31536000, immutable');
header('X-Content-Type-Options: nosniff');

// Se GD non disponibile o sorgente mancante: servi il file statico piu' vicino
if (!function_exists('imagecreatetruecolor') || !is_file($srcPng)) {
    if ($size <= 32 && is_file($fallbackIco)) {
        header('Content-Type: image/x-icon');
        readfile($fallbackIco);
        exit;
    }
    header('Content-Type: image/png');
    readfile(is_file($srcPng) ? $srcPng : $fallbackIco);
    exit;
}

$cacheDir = sys_get_temp_dir() . '/tp_favicons';
if (!is_dir($cacheDir)) @mkdir($cacheDir, 0755, true);
$cacheFile = $cacheDir . "/favicon-{$size}.png";
if (is_file($cacheFile) && filemtime($cacheFile) >= filemtime($srcPng)) {
    header('Content-Type: image/png');
    readfile($cacheFile);
    exit;
}

$info = @getimagesize($srcPng);
if (!$info) {
    header('Content-Type: image/png');
    readfile($srcPng);
    exit;
}
[$srcW, $srcH] = $info;
$mime = $info['mime'] ?? 'image/png';
$srcImg = $mime === 'image/jpeg' ? @imagecreatefromjpeg($srcPng) : @imagecreatefrompng($srcPng);
if (!$srcImg) {
    header('Content-Type: ' . $mime);
    readfile($srcPng);
    exit;
}

$dst = imagecreatetruecolor($size, $size);
imagealphablending($dst, false);
imagesavealpha($dst, true);
$transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
imagefill($dst, 0, 0, $transparent);
// Ritaglia al quadrato centrale poi ridimensiona (evita icone schiacciate)
$side = min($srcW, $srcH);
$sx = (int)(($srcW - $side) / 2);
$sy = (int)(($srcH - $side) / 2);
imagecopyresampled($dst, $srcImg, 0, 0, $sx, $sy, $size, $size, $side, $side);
imagedestroy($srcImg);

header('Content-Type: image/png');
@imagepng($dst, $cacheFile);
imagepng($dst);
imagedestroy($dst);
