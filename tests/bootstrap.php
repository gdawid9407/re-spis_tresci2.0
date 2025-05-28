<?php
// Autoload dla PHPUnit Polyfills i innych dev‐zależności
require __DIR__ . '/../vendor/autoload.php';

// Ścieżka do biblioteki testów WP
$wp_tests_dir = getenv('WP_TESTS_DIR') ?: dirname(__DIR__) . '/vendor/wp-phpunit/wp-phpunit';

// Załaduj wtyczkę przed testami
function _load_re_spis_tresci_plugin() {
    require dirname( __DIR__ ) . '/re-spis_tresci.php';
}
tests_add_filter( 'muplugins_loaded', '_load_re_spis_tresci_plugin' );

// Inicjalizacja środowiska testowego
require_once $wp_tests_dir . '/includes/bootstrap.php';

if ( ! file_exists( $wp_tests_dir . '/includes/functions.php' ) ) {
    fwrite( STDERR, "WP test suite not found\n" );
    exit( 1 );
}