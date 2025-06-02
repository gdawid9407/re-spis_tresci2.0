<?php
/**
 * Re-Spis Treści – dedykowany sidebar
 */
namespace Unitoc\Core;

class Sidebar {

    const ID = 'unitoc-sidebar';

    /** Rejestracja obszaru widgetów */
    public static function register() {
        register_sidebar( [
            'name'          => __( 'Re-Spis Treści', 're-spis-tresci' ),
            'id'            => self::ID,
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ] );
    }

    /** Wstrzyknięcie sidebara tuż po <body> */
    public static function output() {
        if ( is_active_sidebar( self::ID ) ) {
            echo '<aside class="unitoc-fixed">';
            dynamic_sidebar( self::ID );
            echo '</aside>';
        }
    }
}

// bootstrap
add_action( 'widgets_init', [ Sidebar::class, 'register' ] );
add_action( 'wp_body_open', [ Sidebar::class, 'output' ] );
