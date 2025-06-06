<?php
namespace Unitoc\Admin;

defined('ABSPATH') || exit;

/**
 * Panel konfiguracji Re‑Spis Treści.
 */
final class SettingsPage {
    public const CAP          = 'manage_options';
    public const SLUG         = 'unitoc_settings';
    public const OPTION_GROUP = 'unitoc_settings_group';

    /** Bootstrap */
    public static function init(): void {
        add_action('admin_menu',  [self::class, 'add']);
        add_action('admin_init',  [self::class, 'register']);
    }

    /** Dodaj stronę do głównego menu administratora */
    public static function add(): void {
        add_menu_page(
            __('Re‑Spis Treści Ustawienia', 're-spis-tresci'),
            __('Re‑Spis Treści', 're-spis-tresci'),
            self::CAP,
            self::SLUG,
            [self::class, 'render'],
            'dashicons-list-view',
            26
        );
    }

    /** Rejestracja opcji, sekcji i pól */
    public static function register(): void {
        /* ===== Istniejące Ustawienia ===== */
        register_setting(
            self::OPTION_GROUP,
            'unitoc_content_types',
            [
                'type'              => 'array',
                'sanitize_callback' => [self::class, 'sanitize_content_types'],
                'default'           => ['post', 'page'],
                'show_in_rest'      => [
                    'schema' => [
                        'type'  => 'array',
                        'items' => ['type' => 'string'],
                    ],
                ],
            ]
        );
        register_setting(
            self::OPTION_GROUP,
            'unitoc_max_depth',
            [
                'type'              => 'integer',
                'sanitize_callback' => [self::class, 'sanitize_depth'],
                'default'           => 4,
                'show_in_rest'      => true,
            ]
        );
        register_setting(
            self::OPTION_GROUP,
            'unitoc_min_headings',
            [
                'type'              => 'integer',
                'sanitize_callback' => [self::class, 'sanitize_min_headings'],
                'default'           => 2,
                'show_in_rest'      => true,
            ]
        );
        register_setting(
            self::OPTION_GROUP,
            'unitoc_wrapper_class',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
                'show_in_rest'      => true,
            ]
        );
        register_setting(
            self::OPTION_GROUP,
            'unitoc_list_class',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default'           => '',
                'show_in_rest'      => true,
            ]
        );

        /* ===== NOWE Ustawienia (9.4, 9.5) ===== */
        register_setting(
            self::OPTION_GROUP,
            'unitoc_auto_insert',
            [
                'type'              => 'boolean',
                'sanitize_callback' => [self::class, 'sanitize_boolean'],
                'default'           => true,
                'show_in_rest'      => true,
            ]
        );
        register_setting(
            self::OPTION_GROUP,
            'unitoc_insert_position',
            [
                'type'              => 'string',
                'sanitize_callback' => [self::class, 'sanitize_insert_position'],
                'default'           => 'before_content',
                'show_in_rest'      => true,
            ]
        );
        register_setting(
            self::OPTION_GROUP,
            'unitoc_show_title',
            [
                'type'              => 'boolean',
                'sanitize_callback' => [self::class, 'sanitize_boolean'],
                'default'           => true,
                'show_in_rest'      => true,
            ]
        );
        register_setting(
            self::OPTION_GROUP,
            'unitoc_toc_title',
            [
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field', // Można użyć wp_kses_post dla większej elastyczności HTML
                'default'           => __('Spis treści', 're-spis-tresci'),
                'show_in_rest'      => true,
            ]
        );
        register_setting(
        self::OPTION_GROUP,
        'unitoc_style_source',
            [
            'type'              => 'string',
            'sanitize_callback' => [self::class, 'sanitize_style_source'],
            'default'           => 'default',
            'show_in_rest'      => true,
            ]
        );


        /* ===== Sekcja (pozostaje ta sama) ===== */
        add_settings_section(
            'unitoc_section_general',
            __('Ustawienia spisu treści', 're-spis-tresci'),
            '__return_false',
            self::SLUG
        );

        /* ===== Istniejące Pola ===== */
        add_settings_field(
            'unitoc_content_types',
            __('Typy treści do analizy', 're-spis-tresci'),
            [self::class, 'field_content_types'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_content_types_post']
        );
        // ... (pozostałe istniejące add_settings_field bez zmian) ...
        add_settings_field(
            'unitoc_max_depth',
            __('Maksymalna głębokość', 're-spis-tresci'),
            [self::class, 'field_max_depth'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_max_depth_field']
        );
        add_settings_field(
            'unitoc_min_headings',
            __('Minimalna liczba nagłówków', 're-spis-tresci'),
            [self::class, 'field_min_headings'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_min_headings_field']
        );
        
        
        add_settings_field(
            'unitoc_wrapper_class',
            __('Klasa CSS dla kontenera (wrappera)', 're-spis-tresci'),
            [self::class, 'field_wrapper_class'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_wrapper_class_field']
        );
        add_settings_field(
            'unitoc_list_class',
            __('Klasa CSS dla głównej listy (ul)', 're-spis-tresci'),
            [self::class, 'field_list_class'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_list_class_field']
        );
        
        add_settings_field(
            'unitoc_style_source',
            __('Źródło stylów TOC', 're-spis-tresci'),
            [self::class, 'field_style_source'],
            self::SLUG,
            'unitoc_section_general'
        );



        /* ===== NOWE Pola (9.4) ===== */
        add_settings_field(
            'unitoc_auto_insert',
            __('Automatyczne wstawianie', 're-spis-tresci'),
            [self::class, 'field_auto_insert'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_auto_insert_field']
        );
        add_settings_field(
            'unitoc_insert_position',
            __('Pozycja automatycznego wstawiania', 're-spis-tresci'),
            [self::class, 'field_insert_position'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_insert_position_field']
        );
        add_settings_field(
            'unitoc_show_title',
            __('Wyświetlaj tytuł spisu treści', 're-spis-tresci'),
            [self::class, 'field_show_title'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_show_title_field']
        );
        add_settings_field(
            'unitoc_toc_title',
            __('Tekst tytułu spisu treści', 're-spis-tresci'),
            [self::class, 'field_toc_title'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_toc_title_field']
        );
    }

    /* ==== Istniejące Sanitizers ==== */
    public static function sanitize_content_types($input): array {
        if (!is_array($input)) {
            return [];
        }
        $all_public_types = array_keys(get_post_types(['public' => true]));
        return array_values(array_intersect($input, $all_public_types));
    }
    
    public static function sanitize_style_source($input): string {
        return in_array($input, ['default', 'theme'], true) ? $input : 'default';
    }

    
    
    
    public static function sanitize_depth($input): int {
        $depth = absint($input);
        return max(1, min(6, $depth));
    }
    public static function sanitize_min_headings($input): int {
        $min = absint($input);
        return max(1, $min);
    }

    /* ==== NOWE Sanitizers (9.5) ==== */
    public static function sanitize_boolean($input): bool {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }

    public static function sanitize_insert_position($input): string {
        $valid_positions = [
            'before_content'        => __('Przed treścią', 're-spis-tresci'),
            'after_first_heading'   => __('Po pierwszym nagłówku (H1-H6)', 're-spis-tresci'),
            'after_content'         => __('Po treści (niezalecane dla auto)', 're-spis-tresci'),
        ];
        return array_key_exists($input, $valid_positions) ? $input : 'before_content'; // Domyślna wartość, jeśli niepoprawna
    }


    /* ==== Istniejące Field callbacks ==== */
    public static function field_content_types(): void {
        $selected_types = (array) get_option('unitoc_content_types', ['post', 'page']);
        $post_types     = get_post_types(['public' => true], 'objects');
        if (empty($post_types)) {
            echo '<p>' . esc_html__('Nie znaleziono publicznych typów treści.', 're-spis-tresci') . '</p>';
            return;
        }
        foreach ($post_types as $slug => $obj) {
            $id = 'unitoc_content_types_' . esc_attr($slug);
            printf(
                '<label for="%s"><input type="checkbox" name="unitoc_content_types[]" id="%s" value="%s" %s> %s</label><br>',
                $id, $id, esc_attr($slug), checked(in_array($slug, $selected_types, true), true, false), esc_html($obj->labels->singular_name)
            );
        }
        echo '<p class="description">' . esc_html__('Wybierz typy treści, dla których automatycznie generowany będzie spis treści (jeśli włączone).', 're-spis-tresci') . '</p>';
    }
    public static function field_max_depth(): void {
        $value = (int) get_option('unitoc_max_depth', 4);
        echo '<input type="number" min="1" max="6" step="1" name="unitoc_max_depth" id="unitoc_max_depth_field" value="' . esc_attr($value) . '" class="small-text" />';
        echo '<p class="description">' . esc_html__('Maksymalny poziom nagłówków (np. H1-H4) do uwzględnienia w spisie. Wartość od 1 do 6.', 're-spis-tresci') . '</p>';
    }
    public static function field_min_headings(): void {
        $value = (int) get_option('unitoc_min_headings', 2);
        echo '<input type="number" min="1" step="1" name="unitoc_min_headings" id="unitoc_min_headings_field" value="' . esc_attr($value) . '" class="small-text" />';
        echo '<p class="description">' . esc_html__('Minimalna liczba znalezionych nagłówków, aby spis treści został wyświetlony.', 're-spis-tresci') . '</p>';
    }
    public static function field_wrapper_class(): void {
        $value = get_option('unitoc_wrapper_class', '');
        echo '<input type="text" class="regular-text" name="unitoc_wrapper_class" id="unitoc_wrapper_class_field" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Opcjonalna klasa CSS dodawana do głównego kontenera (div) spisu treści.', 're-spis-tresci') . '</p>';
    }
    public static function field_list_class(): void {
        $value = get_option('unitoc_list_class', '');
        echo '<input type="text" class="regular-text" name="unitoc_list_class" id="unitoc_list_class_field" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Opcjonalna klasa CSS dodawana do głównego elementu listy <ul> spisu treści.', 're-spis-tresci') . '</p>';
    }

    /* ==== NOWE Field callbacks (9.4) ==== */
    public static function field_auto_insert(): void {
        $value = (bool) get_option('unitoc_auto_insert', true);
        echo '<input type="checkbox" name="unitoc_auto_insert" id="unitoc_auto_insert_field" value="1" ' . checked($value, true, false) . ' />';
        echo '<p class="description">' . esc_html__('Zaznacz, aby automatycznie dodawać spis treści do wybranych typów treści.', 're-spis-tresci') . '</p>';
    }

    public static function field_insert_position(): void {
        $current_value = get_option('unitoc_insert_position', 'before_content');
        $positions = [
            'before_content'        => __('Przed treścią', 're-spis-tresci'),
            'after_first_heading'   => __('Po pierwszym nagłówku (H1-H6)', 're-spis-tresci'),
            // Można dodać więcej opcji, np. po X akapitach, ale to wymagałoby bardziej złożonej logiki w Parserze
            'after_content'         => __('Po treści (zazwyczaj używane z shortcode/blokiem)', 're-spis-tresci'),
        ];

        echo '<select name="unitoc_insert_position" id="unitoc_insert_position_field">';
        foreach ($positions as $value => $label) {
            echo '<option value="' . esc_attr($value) . '" ' . selected($current_value, $value, false) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__('Wybierz, gdzie spis treści ma być automatycznie wstawiony. Działa, jeśli "Automatyczne wstawianie" jest zaznaczone.', 're-spis-tresci') . '</p>';
    }

    public static function field_show_title(): void {
        $value = (bool) get_option('unitoc_show_title', true);
        echo '<input type="checkbox" name="unitoc_show_title" id="unitoc_show_title_field" value="1" ' . checked($value, true, false) . ' />';
        echo '<p class="description">' . esc_html__('Zaznacz, aby wyświetlić tytuł nad spisem treści.', 're-spis-tresci') . '</p>';
    }

    public static function field_toc_title(): void {
        $value = get_option('unitoc_toc_title', __('Spis treści', 're-spis-tresci'));
        echo '<input type="text" class="regular-text" name="unitoc_toc_title" id="unitoc_toc_title_field" value="' . esc_attr($value) . '" />';
        echo '<p class="description">' . esc_html__('Wprowadź tekst, który ma być wyświetlany jako tytuł spisu treści (jeśli opcja powyżej jest zaznaczona).', 're-spis-tresci') . '</p>';
    }
    public static function field_style_source(): void {
    $current = get_option('unitoc_style_source', 'default');
    echo '<label><input type="radio" name="unitoc_style_source" value="default" '
            . checked($current, 'default', false) . '> '
            . esc_html__('Domyślny styl wtyczki', 're-spis-tresci') . '</label><br>';
    echo '<label><input type="radio" name="unitoc_style_source" value="theme" '
            . checked($current, 'theme', false) . '> '
            . esc_html__('Styl motywu', 're-spis-tresci') . '</label>';
}


    /** Ekran opcji */
    public static function render(): void {
        if (!current_user_can(self::CAP)) {
            wp_die(esc_html__('Nie masz wystarczających uprawnień, aby uzyskać dostęp do tej strony.', 're-spis-tresci'));
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections(self::SLUG);
                submit_button(__('Zapisz zmiany', 're-spis-tresci'));
                ?>
            </form>
        </div>
        <?php
    }
}