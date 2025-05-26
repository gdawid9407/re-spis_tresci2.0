<?php
/**
 * Plugin Name: Re Spis Tresci
 * Description: Uniwersalny generator spisu treści.
 * Version: 1.0.0
 * Author: dawid Gołis
 * Text Domain: re-spis_tresci
 * Domain Path: /languages
 */

defined('ABSPATH') || exit;

// Autoloader Composer lub alternatywny PSR-4
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    require_once __DIR__ . '/inc/autoload.php';
}

// Dalsze inicjalizacje wtyczki
