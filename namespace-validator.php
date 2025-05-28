<?php
declare(strict_types=1);

// Skrypt weryfikujący zgodność namespace PSR-4 z plikiem composer.json
$composerPath = __DIR__ . '/composer.json';
if (!file_exists($composerPath)) {
    fwrite(STDERR, "composer.json nie znaleziony\n");
    exit(1);
}
$composer = json_decode(file_get_contents($composerPath), true);
$map = $composer['autoload']['psr-4'] ?? [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }
    $path = $file->getRealPath();
    foreach ($map as $prefix => $dir) {
        $baseDir = realpath(__DIR__ . '/' . rtrim($dir, '/'));
        if (strpos($path, $baseDir) !== 0) {
            continue;
        }
        $relative = substr($path, strlen($baseDir) + 1);
        $expectedNs = rtrim($prefix, '\\');
        $parts = explode(DIRECTORY_SEPARATOR, $relative);
        array_pop($parts); // usunięcie nazwy pliku
        if ($parts) {
            $expectedNs .= '\\' . implode('\\', $parts);
        }
        $content = file_get_contents($path);
        if (!preg_match('/^namespace\s+([^;]+);/m', $content, $m)) {
            echo "Brak deklaracji namespace w: $relative\n";
            break;
        }
        $actualNs = trim($m[1]);
        if ($actualNs !== $expectedNs) {
            echo "Niepoprawny namespace w: $relative    Oczekiwany: $expectedNs    Znaleziony: $actualNs\n";
        }
        break;
    }
}
