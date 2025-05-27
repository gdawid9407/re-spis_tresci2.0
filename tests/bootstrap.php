<?php
// Autoload dla PHPUnit Polyfills i innych dev‐zależności
require __DIR__ . '/../vendor/autoload.php';

// Ścieżka do biblioteki testów WP
$wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib';
require_once $wp_tests_dir . '/includes/functions.php';

// Załaduj wtyczkę przed testami
function _load_re_spis_tresci_plugin() {
    require dirname( __DIR__ ) . '/re-spis_tresci.php';
}
tests_add_filter( 'muplugins_loaded', '_load_re_spis_tresci_plugin' );

// Inicjalizacja środowiska testowego
require_once $wp_tests_dir . '/includes/bootstrap.php';
