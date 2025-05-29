<?php
/**
 * Fallback Shortcode for WP <5.0
 *
 * Registers the existing unitoc_shortcode when Gutenberg unavailable.
 * Placement: wp-content/plugins/re-spis-tresci/inc/fallback-shortcode.php
 * Load via main plugin file after autoloader.
 */

namespace Unitoc\Core;

final class Fallback_Shortcode {
    /**
     * Detects WP version and registers shortcode fallback.
     */
    public static function init(): void {
        global $wp_version;

        if (version_compare($wp_version, '5.0', '<')) {
            add_shortcode('unitoc_shortcode', [Shortcode::class, 'render']);
        }
    }
}

// invoke fallback registration
Fallback_Shortcode::init();
