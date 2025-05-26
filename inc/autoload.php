<?php
/**
 * PSR-4 Autoloader dla klas core w namespace Unitoc\Core
 */
spl_autoload_register(function ($class) {
    $prefix   = 'ReSpis\\Core\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);

    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
