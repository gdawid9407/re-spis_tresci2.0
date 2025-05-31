<?php
/**
 * Plugin Name: Re-Spis Tresci
 * Description: Uniwersalny generator spisu treści.
 * Version: 1.0.0
 * Author: dawid Gołis
 * Text Domain: re-spis-tresci
 * Domain Path: /languages
 */

use Unitoc\Core\Parser;
use Unitoc\Core\Generator;
use Unitoc\Core\Shortcode;
use Unitoc\Core\VC_Integration;
use Unitoc\Core\Fallback_Shortcode;
use Unitoc\Admin\SettingsPage;


defined('ABSPATH') || exit;

// Autoloader Composer lub alternatywny PSR-4
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    require_once __DIR__ . '/inc/autoload.php';
}

Shortcode::init();
Fallback_Shortcode::init();

if ( is_admin() ) {
    SettingsPage::init();
}

add_action( 'init', [ Parser::class, 'init' ] );
add_action( 'vc_before_init', [ VC_Integration::class, 'init' ] );

add_filter(
    'the_content',
    function ( string $content ): string {
        // Parser::filterContent (priorytet 8) już oznaczył nagłówki.
        $headings = Parser::getHeadings();

        if ( empty( $headings ) ) {
            return $content;
        }

        return Generator::generate( $headings ) . $content;
    },
    12 // >8 gwarantuje, że analiza jest gotowa
);

// Rejestracja bloku Gutenberg i tłumaczeń
add_action( 'init', function(): void {
    load_plugin_textdomain(
        're-spis-tresci',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
    register_block_type( __DIR__ . '/block.json' );
});

// Rejestracja i enqueue stylów oraz skryptów
add_action( 'wp_enqueue_scripts', function(): void {
    $url_base  = plugin_dir_url( __FILE__ );
    $path_base = plugin_dir_path( __FILE__ );

    $css = 'assets/css/style.css';
    $js  = 'assets/js/toc.js';

    wp_register_style(
        're-spis-tresci-style',
        $url_base . $css,
        [],
        file_exists( $path_base . $css ) ? filemtime( $path_base . $css ) : false
    );

    wp_register_script(
        're-spis-tresci-script',
        $url_base . $js,
        [ 'jquery' ],
        file_exists( $path_base . $js ) ? filemtime( $path_base . $js ) : false,
        true
    );

    wp_enqueue_style( 're-spis-tresci-style' );
    wp_enqueue_script( 're-spis-tresci-script' );
} );
