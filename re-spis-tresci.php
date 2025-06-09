<?php
/**
 * Plugin Name: Re-Spis Tresci
 * Description: Uniwersalny generator spisu treści.
 * Version: 1.0.0
 * Author: dawid Gołis
 * Text Domain: re-spis-tresci
 * Domain Path: /languages
 */

use Unitoc\Core\Parser;
use Unitoc\Core\Generator;
use Unitoc\Core\Shortcode;
use Unitoc\Core\VC_Integration;
use Unitoc\Core\Fallback_Shortcode;
use Unitoc\Admin\SettingsPage;
use Unitoc\Core\Sidebar;
use Unitoc\Core\Widget;

defined('ABSPATH') || exit;

// Autoloader Composer lub alternatywny PSR-4
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    require_once __DIR__ . '/inc/autoload.php';
}

/* --------------------------------------------------
 * Elementor integration – ładowane dopiero gdy Elementor
 * zakończy inicjalizację i posiada Widget_Base.
 * -------------------------------------------------- */
add_action( 'elementor/widgets/register', static function ( $widgets_manager ) {
    require_once __DIR__ . '/inc/Elementor.php';           // definicja widgetu

    if ( class_exists( '\\Unitoc\\Core\\Elementor_Widget_Unitoc' ) ) {
        $widgets_manager->register( new \Unitoc\Core\Elementor_Widget_Unitoc() );
    }
}, 20 );

Shortcode::init();
Fallback_Shortcode::init();
add_action( 'widgets_init', [ Sidebar::class, 'register' ] );
add_action( 'wp_body_open', [ Sidebar::class, 'output' ] );

add_action( 'init', function () {
    // wymuś załadowanie klasy, co zarejestruje widget przed widgets_init
    class_exists( Widget::class ); 
}, 5 );                          // priorytet < widgets_init

add_action('init', function() {
    register_block_type( __DIR__ . '/block.json' );
});


if ( is_admin() ) {
    SettingsPage::init();
}
add_filter(
    'elementor/frontend/the_content',            // treść po renderze Elementora
    static function ( string $content ): string {
        return \Unitoc\Core\Parser::filterContent( $content ); // dodaj id do nagłówków
    },
    20                                           // później niż domyślne hooki Elementora
);

add_action( 'init', [ Parser::class, 'init' ] );
add_action( 'vc_before_init', [ VC_Integration::class, 'init' ] );

add_filter(
    'the_content',
    function ( string $content ): string {
        // 1. Sprawdź, czy automatyczne wstawianie jest włączone globalnie
        $auto_insert_enabled = (bool) get_option('unitoc_auto_insert', true);
        if (!$auto_insert_enabled) {
            return $content;
        }

        // 2. Sprawdź, czy dla tego typu treści ma być generowany spis
        // Parser::filterContent (priorytet 8) już oznaczył nagłówki, jeśli is_singular() etc.
        // ale tutaj dodatkowo sprawdzamy ustawienia użytkownika
        $allowed_post_types = (array) get_option('unitoc_content_types', ['post', 'page']);
        if (!is_singular($allowed_post_types) || !in_the_loop() || !is_main_query()) {
            // Jeśli Parser nie zadziałał lub nie jesteśmy w głównym query dozwolonego typu, nie rób nic
            // Lub jeśli Parser zadziałał, ale ten typ nie jest w $allowed_post_types z opcji
            if (!in_array(get_post_type(), $allowed_post_types, true)) {
                return $content;
            }
        }

        $headings = Parser::getHeadings();

        // 3. Sprawdź minimalną liczbę nagłówków
        $min_headings_option = (int) get_option('unitoc_min_headings', 2);
        if (empty($headings) || count($headings) < $min_headings_option) {
            return $content;
        }

        // 4. Pobierz pozostałe opcje
        $max_depth_option    = (int) get_option('unitoc_max_depth', 4);
        $show_title_option   = (bool) get_option('unitoc_show_title', true);
        $toc_title_text      = get_option('unitoc_toc_title', __('Spis treści', 're-spis-tresci'));
        $wrapper_class_option= sanitize_html_class(get_option('unitoc_wrapper_class', ''));
        $list_class_option   = sanitize_html_class(get_option('unitoc_list_class', '')); // Przekażemy do Generatora
        $insert_position     = get_option('unitoc_insert_position', 'before_content');

        // 5. Wygeneruj HTML spisu treści
       if ( 'theme' === get_option('unitoc_style_source','default') ) {
    // przekaz pustą list_class, nie dodawaj wrapper_class
        $toc_html = Generator::generate($headings, $max_depth_option, '');
    } else {
        $toc_html = Generator::generate($headings, $max_depth_option, $list_class_option);
    }

        if (empty($toc_html)) {
            return $content;
        }

        // 6. Dodaj tytuł, jeśli włączony
        $full_toc_html = '';
        if ($show_title_option && !empty($toc_title_text)) {
            // Możesz chcieć opakować tytuł w np. H2 lub div z klasą
            $full_toc_html .= '<h2 class="unitoc-title">' . esc_html($toc_title_text) . '</h2>';
        }
        $full_toc_html .= $toc_html;

        // 7. Dodaj wrapper, jeśli zdefiniowano klasę
        if (!empty($wrapper_class_option)) {
            $full_toc_html = '<div class="' . esc_attr($wrapper_class_option) . '">' . $full_toc_html . '</div>';
        }

        // 8. Wstaw spis treści w odpowiednim miejscu
        switch ($insert_position) {
            case 'after_first_heading':
                // To jest bardziej skomplikowane i wymaga znalezienia pierwszego nagłówka w $content
                // Użyjemy prostego regexa, ale może wymagać dopracowania dla złożonych przypadków
                // Zakładamy, że Parser::filterContent już dodał ID do nagłówków
                $pattern = '/(<h[1-6][^>]*id="[^"]*"[^>]*>.*?<\/h[1-6]>)/i';
                if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $first_heading_html = $matches[0][0];
                    $offset = $matches[0][1] + strlen($first_heading_html);
                    return substr($content, 0, $offset) . $full_toc_html . substr($content, $offset);
                }
                // Fallback na "before_content" jeśli nie znaleziono nagłówka
                return $full_toc_html . $content;
            case 'after_content':
                return $content . $full_toc_html;
            case 'before_content':
            default:
                return $full_toc_html . $content;
        }
    },
    12 // >8 gwarantuje, że analiza jest gotowa
);

// Rejestracja bloku Gutenberg i tłumaczeń
add_action( 'init', function(): void {
    load_plugin_textdomain(
        're-spis-tresci',
        false,
        dirname( plugin_basename( __FILE__ ) ) . '/languages'
    );
    
});

/* === Style & JS === */
add_action( 'wp_enqueue_scripts', function (): void {
    $src = get_option( 'unitoc_style_source', 'default' );

    if ( $src === 'default' ) {
        // CSS z wtyczki
        $path = plugin_dir_path( __FILE__ ) . 'assets/css/style.css';
        wp_enqueue_style(
            're-spis-tresci',
            plugin_dir_url( __FILE__ ) . 'assets/css/style.css',
            [],
            filemtime( $path )
        );
    } elseif ( $src === 'theme' ) {
        // 1) priorytet: ogólny arkusz w motywie, jeśli istnieje
        $gen = get_stylesheet_directory() . '/unitoc-general.css';
        if ( file_exists( $gen ) ) {
            wp_enqueue_style(
                're-spis-tresci',
                get_stylesheet_directory_uri() . '/unitoc-general.css',
                [],
                filemtime( $gen )
            );
        }
        // 2) brak unitoc-general.css → polegamy na theme/style.css (WordPress ładuje go sam)
    }

    // JS (jeden plik, bez duplikatów)
    $js = plugin_dir_path( __FILE__ ) . 'assets/js/dynamic-headings.js';
    wp_enqueue_script(
        're-spis-tresci',
        plugin_dir_url( __FILE__ ) . 'assets/js/dynamic-headings.js',
        ['jquery'],
        filemtime( $js ),
        true
    );
} );

/* === Ostrzeżenie, gdy motyw nie ma stylów === */
add_action( 'admin_notices', function (): void {
    if ( get_option( 'unitoc_style_source', 'default' ) !== 'theme' ) {
        return; // nie dotyczy
    }

    $has_general = file_exists( get_stylesheet_directory() . '/unitoc-general.css' );
    $has_style   = file_exists( get_stylesheet_directory() . '/style.css' ); // powinien istnieć, ale sprawdzamy na wszelki wypadek

    if ( ! $has_general && ! $has_style ) {
        echo '<div class="notice notice-warning"><p>'
           . esc_html__( 'Motyw nie zawiera własnych stylów – wczytywany jest domyślny arkusz wtyczki.', 're-spis-tresci' )
           . '</p></div>';
    }
} );
