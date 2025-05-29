<?php
namespace Unitoc\Core;

use Unitoc\Core\Parser;
use Unitoc\Core\Generator;

class Shortcode {
    public static function init(): void {
        add_shortcode('unitoc', [self::class, 'render']);
    }

    public static function render(array $atts): string {
        $atts = shortcode_atts([
            'depth'         => 4,
            'min_headings'  => 2,
            'wrapper_class' => '',
            'numbering'     => true,
            'collapse'      => false,
        ], $atts, 'unitoc');

        $headings = Parser::getHeadings();
        if (count($headings) < (int) $atts['min_headings']) {
            return '';
        }

        $html     = Generator::generate(
        $headings,
        (int)   $atts['depth'],
        (bool)  $atts['numbering'],
        (bool)  $atts['collapse']
        );

        $wrapper = trim($atts['wrapper_class']);

        if ($wrapper !== '') {
            return sprintf(
                '<div class="%s">%s</div>',
                esc_attr($wrapper),
                $html
            );
        }

        return $html;
    }
}
