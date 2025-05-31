<?php
namespace Unitoc\Admin;

defined('ABSPATH') || exit;

/**
 * Panel konfiguracji Re‑Spis Treści.
 * Spełnia wymagania 9.1‑9.3: menu w Ustawieniach, rejestracja opcji, sekcje i pola.
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

    /** Dodaj stronę do głównego menu administratora */ // Zmieniony komentarz dla jasności
    public static function add(): void {
        add_menu_page(
            __('Re‑Spis Treści Ustawienia', 're-spis-tresci'), // Tytuł strony (widoczny w tagu <title> przeglądarki)
            __('Re‑Spis Treści', 're-spis-tresci'),            // Tytuł wyświetlany w menu
            self::CAP,                                         // Wymagane uprawnienia do zobaczenia tej pozycji menu
            self::SLUG,                                        // Unikalny identyfikator (slug) tego menu
            [self::class, 'render'],                           // Funkcja, która wygeneruje zawartość strony
            'dashicons-list-view',                             // Ikona dla menu (używamy Dashicons, np. 'dashicons-list-view')
            26                                                 // Pozycja w menu (liczba; niższa = wyżej. Np. 26 umieści ją poniżej "Komentarze")
        );
    }

    /** Rejestracja opcji, sekcji i pól */
    public static function register(): void {
        /* ===== Ustawienia ===== */
        register_setting(
            self::OPTION_GROUP,
            'unitoc_content_types',
            [
                'type'              => 'array',
                'sanitize_callback' => [self::class, 'sanitize_content_types'],
                'default'           => ['post', 'page'],
                'show_in_rest'      => [ // <<< POPRAWKA TUTAJ
                    'schema' => [
                        'type'  => 'array', // Typ główny to tablica
                        'items' => [       // Definicja dla elementów tablicy
                            'type' => 'string', // Każdy element tablicy (slug typu posta) jest stringiem
                        ],
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

        /* ===== Sekcja ===== */
        add_settings_section(
            'unitoc_section_general',
            __('Ustawienia spisu treści', 're-spis-tresci'),
            '__return_false', // Można tu dać callback wyświetlający opis sekcji
            self::SLUG
        );

        /* ===== Pola ===== */
        add_settings_field(
            'unitoc_content_types',
            __('Typy treści do analizy', 're-spis-tresci'),
            [self::class, 'field_content_types'],
            self::SLUG,
            'unitoc_section_general',
            ['label_for' => 'unitoc_content_types_post'] // Etykieta dla pierwszego checkboxa
        );
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
    }

    /* ==== Sanitizers ==== */
    public static function sanitize_content_types($input): array {
        if (!is_array($input)) {
            return [];
        }
        $all_public_types = array_keys(get_post_types(['public' => true]));
        return array_values(array_intersect($input, $all_public_types));
    }

    public static function sanitize_depth($input): int {
        $depth = absint($input);
        return max(1, min(6, $depth)); // Zwiększyłem do 6, bo H1-H6 to standard
    }

    public static function sanitize_min_headings($input): int {
        $min = absint($input);
        return max(1, $min);
    }

    /* ==== Field callbacks ==== */
    public static function field_content_types(): void {
        $selected_types = (array) get_option('unitoc_content_types', ['post', 'page']);
        $post_types     = get_post_types(['public' => true], 'objects');
        $first = true;

        if (empty($post_types)) {
            echo '<p>' . esc_html__('Nie znaleziono publicznych typów treści.', 're-spis-tresci') . '</p>';
            return;
        }

        foreach ($post_types as $slug => $obj) {
            $id = 'unitoc_content_types_' . esc_attr($slug);
            printf(
                '<label for="%s"><input type="checkbox" name="unitoc_content_types[]" id="%s" value="%s" %s> %s</label><br>',
                $id,
                $id,
                esc_attr($slug),
                checked(in_array($slug, $selected_types, true), true, false),
                esc_html($obj->labels->singular_name)
            );
            if ($first) {
                // `label_for` w add_settings_field wskazuje na ID pierwszego checkboxa,
                // więc technicznie nie potrzebujemy tu dodatkowego ID, ale dla spójności może być.
                $first = false;
            }
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
