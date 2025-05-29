<?php
/**
 * VC Integration for Unitoc Plugin
 *
 * This file registers the "Spis treści" element in WPBakery Page Builder,
 * mapping the existing shortcode 'unitoc_shortcode' to a visual component.
 * It enables dynamic parameters (wrapper selector, smooth scroll, custom levels)
 * and custom JS view for live preview in the editor.
 *
 * Placement: wp-content/plugins/re-spis_tresci/inc/vc-integration.php
 * Load via main plugin bootstrap (e.g., in bootstrap.php or Parser.php):
 *   add_action('vc_before_init', ['\Unitoc\Core\VC_Integration', 'init']);
 */

namespace Unitoc\Core;

// Exit if WPBakery is not active
add_action('vc_before_init', [__CLASS__, 'init']);

final class VC_Integration {
    /**
     * Initialize VC integration by mapping shortcode parameters.
     */
    public static function init(): void {
        if (!function_exists('vc_map')) {
            return;
        }

        vc_map([
            'name'    => __('Spis treści', 'unitoc'),
            'base'    => 'unitoc_shortcode',
            'icon'    => 'icon-wpb-unitoc',
            'category'=> __('Content', 'unitoc'),
            'params'  => self::get_params(),
            'js_view' => 'VcUnitocView', // enables live preview
        ]);
    }

    /**
     * Define parameters for the VC element, matching shortcode attributes.
     * Supports dynamic groups for custom heading levels.
     */
    private static function get_params(): array {
        return [
            [
                'type'        => 'textfield',
                'heading'     => __('Wrapper selector', 'unitoc'),
                'param_name'  => 'selector',
                'value'       => 'h2,h3',
                'admin_label' => true,
            ],
            [
                'type'       => 'dropdown',
                'heading'    => __('Smooth Scroll', 'unitoc'),
                'param_name' => 'smooth_scroll',
                'value'      => [
                    __('Yes', 'unitoc') => 'yes',
                    __('No', 'unitoc')  => 'no',
                ],
            ],
            [
                'type'        => 'param_group',
                'heading'     => __('Custom Levels', 'unitoc'),
                'param_name'  => 'levels',
                'params'      => [
                    [
                        'type'       => 'dropdown',
                        'heading'    => __('Heading tag', 'unitoc'),
                        'param_name' => 'tag',
                        'value'      => ['H2'=>'h2','H3'=>'h3','H4'=>'h4'],
                    ],
                ],
            ],
        ];
    }
}
