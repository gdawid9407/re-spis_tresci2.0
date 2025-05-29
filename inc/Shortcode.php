<?php
namespace Unitoc;

class Shortcode {
    public static function init(): void {
        add_shortcode('unitoc', [self::class, 'render']);
    }

    public static function render(array $atts): string {
        $atts = shortcode_atts([
            'depth'         => 4,
            'min_headings'  => 2,
            'wrapper_class' => '',
        ], $atts, 'unitoc');

        $headings = Core\Parser::getHeadings();
        if (count($headings) < (int) $atts['min_headings']) {
            return '';
        }

        $html = Core\Generator::generate($headings, (int) $atts['depth']);
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
