<?php
namespace Unitoc\Core;

use Unitoc\Core\Parser;
use Unitoc\Core\Generator;

class Widget extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'unitoc_widget',
            __('Spis treści', 're-spis-tresci'),
            ['description' => __('Widget generujący spis treści', 're-spis-tresci')]
        );
    }

    // Wyświetlenie widgetu na froncie
    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        $depth   = isset($instance['depth']) ? absint($instance['depth']) : 4;
        $min     = isset($instance['min_headers']) ? absint($instance['min_headers']) : 2;
        $wrapper = sanitize_html_class($instance['wrapper_class'] ?? '');
        $headings = Parser::getHeadings();
        if (count($headings) >= $min) {
            $html = Generator::generate($headings, $depth);
            if ($wrapper !== '') {
                echo "<div class=\"{$wrapper}\">{$html}</div>";
            } else {
                echo $html;
            }
        }
        echo $args['after_widget'];
    }

    // Formularz w panelu administracyjnym
    public function form($instance)
    {
        $depth   = $instance['depth'] ?? 4;
        $min     = $instance['min_headers'] ?? 2;
        $wrapper = esc_attr($instance['wrapper_class'] ?? '');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('depth'); ?>"><?php _e('Głębokość:', 're-spis-tresci'); ?></label>
            <input id="<?php echo $this->get_field_id('depth'); ?>"
                   name="<?php echo $this->get_field_name('depth'); ?>"
                   type="number" value="<?php echo $depth; ?>" class="widefat">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('min_headers'); ?>"><?php _e('Minimalna liczba nagłówków:', 're-spis-tresci'); ?></label>
            <input id="<?php echo $this->get_field_id('min_headers'); ?>"
                   name="<?php echo $this->get_field_name('min_headers'); ?>"
                   type="number" value="<?php echo $min; ?>" class="widefat">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('wrapper_class'); ?>"><?php _e('Klasa wrappera:', 're-spis-tresci'); ?></label>
            <input id="<?php echo $this->get_field_id('wrapper_class'); ?>"
                   name="<?php echo $this->get_field_name('wrapper_class'); ?>"
                   type="text" value="<?php echo $wrapper; ?>" class="widefat">
        </p>
        <?php
    }

    // Zapis ustawień widgetu
    public function update($new, $old)
    {
        $instance = [];
        $instance['depth']         = isset($new['depth']) ? absint($new['depth']) : 4;
        $instance['min_headers']   = isset($new['min_headers']) ? absint($new['min_headers']) : 2;
        $instance['wrapper_class'] = sanitize_text_field($new['wrapper_class'] ?? '');
        return $instance;
    }
}

add_action('widgets_init', function() {
    register_widget(Widget::class);
});
