<?php
namespace Unitoc\Core;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Unitoc\Core\Parser;
use Unitoc\Core\Generator;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Elementor_Widget_Unitoc extends Widget_Base
{
    public function get_name() { return 'unitoc_toc'; }
    public function get_title() { return __('Spis treści', 're-spis-tresci'); }
    public function get_icon() { return 'eicon-editor-list-ul'; }
    public function get_categories() { return ['basic']; }

    // Definicja panelu kontrolnego
    protected function _register_controls()
    {
        $this->start_controls_section(
            'section_settings',
            ['label' => __('Ustawienia', 're-spis-tresci')]
        );
        $this->add_control(
            'depth',
            [
                'label' => __('Głębokość', 're-spis-tresci'),
                'type' => Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
            ]
        );
        $this->add_control(
            'min_headings',
            [
                'label' => __('Min. nagłówków', 're-spis-tresci'),
                'type' => Controls_Manager::NUMBER,
                'default' => 2,
                'min' => 1,
            ]
        );
        $this->add_control(
            'wrapper_class',
            [
                'label' => __('Klasa wrappera', 're-spis-tresci'),
                'type' => Controls_Manager::TEXT,
                'default' => '',
            ]
        );
        $this->end_controls_section();
    }

    // Renderowanie widgetu
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $headings = Parser::getHeadings();
        if (count($headings) < $settings['min_headings']) { return; }
        $html     = Generator::generate($headings, $settings['depth']);
        if ($settings['wrapper_class']) {
            echo "<div class=\"{$settings['wrapper_class']}\">{$html}</div>";
        } else {
            echo $html;
        }
    }
}

// Rejestracja w Elementorze
add_action(
    'elementor/widgets/widgets_registered',
    function() {
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(
            new Elementor_Widget_Unitoc()
        );
    }
);
