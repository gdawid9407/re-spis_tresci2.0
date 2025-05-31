<?php
namespace Unitoc\Admin;

defined('ABSPATH')||exit;

class SettingsPage {
    const CAP             = 'manage_options';
    const SLUG            = 'unitoc_settings';
    const OPTION_GROUP    = 'unitoc_settings_group';

    static function init() {
        add_action('admin_menu',     [self::class, 'add']);
        add_action('admin_init',     [self::class, 'register_settings']);
    }

    static function add() {
        add_menu_page(
            'Re-Spis Treści',
            'Re-Spis Treści',
            self::CAP,
            self::SLUG,
            [self::class, 'render'],
            'dashicons-list-view',
            80
        );
    }

    static function register_settings() {
        // 9.2.1 Typy treści do analizy
        register_setting(
            self::OPTION_GROUP,
            'unitoc_content_types',
            [
                'type'              => 'array',
                'sanitize_callback' => [self::class, 'sanitize_content_types'],
                'default'           => ['post', 'page'],
            ]
        );

        // 9.2.2 Maksymalna głębokość spisu (1–4)
        register_setting(
            self::OPTION_GROUP,
            'unitoc_max_depth',
            [
                'type'              => 'integer',
                'sanitize_callback' => [self::class, 'sanitize_integer'],
                'default'           => 4,
            ]
        );

        // 9.2.3 Minimalna liczba nagłówków
        register_setting(
            self::OPTION_GROUP,
            'unitoc_min_headings',
            [
                'type'              => 'integer',
                'sanitize_callback' => [self::class, 'sanitize_integer'],
                'default'           => 2,
            ]
        );

        // 9.2.4 Wrapper class
        register_setting(
            self::OPTION_GROUP,
            'unitoc_wrapper_class',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );

        // 9.2.4 List class
        register_setting(
            self::OPTION_GROUP,
            'unitoc_list_class',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
            ]
        );
    }

    // Sanitize array of content types
    static function sanitize_content_types($input) {
        if (!is_array($input)) {
            return [];
        }
        $allowed = ['post', 'page']; // rozszerzyć o custom types w kolejnych krokach
        return array_values(array_intersect($allowed, $input));
    }

    // Sanitize integer values
    static function sanitize_integer($input) {
        return absint($input);
    }

    static function render() {
        if (!current_user_can(self::CAP)) {
            wp_die('Brak uprawnień');
        }
        echo '<div class="wrap"><h1>Re-Spis Treści – Ustawienia</h1><form method="post" action="options.php">';
        settings_fields(self::OPTION_GROUP);
        do_settings_sections(self::SLUG);
        submit_button();
        echo '</form></div>';
    }
}

// inicjalizacja panelu
SettingsPage::init();
