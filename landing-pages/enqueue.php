<?php
if ( !defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.


class DT_Porch_Landing_Enqueue
{
    public static function load_scripts() {
        wp_enqueue_style( 'porch-style-css', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'css/landing.css', array(), filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'css/landing.css' ), 'all' );
        wp_register_script( 'porch-site-js', trailingslashit( plugin_dir_url( __FILE__ ) ) . 'js/landing.js', [ 'jquery' ], filemtime( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'js/landing.js' ) );
        wp_enqueue_script( 'porch-site-js' );
    }

    public static function load_allowed_scripts( $allowed_js ) {
        $allowed_js = array_diff( $allowed_js, [ 'site-js', 'shared-functions' ] );

        return array_merge( $allowed_js, [
            'jquery',
            'jquery-ui',
            'porch-site-js',
            'genesis-blocks-block-js',
            'wp-polyfill',
            'themeisle-gutenberg-animation-frontend',
            'wp-embed',
        ]);
    }

    public static function load_allowed_styles( $allowed_css ) {
        return [
            'jquery-ui-site-css',
            'porch-style-css',
            'animate-css', // animation plugin
            'wp-block-library',
            'themeisle-gutenberg-animation-style',
            'genesis-blocks-dismiss-js', // genesis block plugin
            'genesis-blocks-style-css',
            'genesis-blocks-block-editor-css',
            'uagb-block-editor-css', // ultimate Gutenberg editor
            'uagb-block-common-editor-css',
            'uagb-slick-css',
            'uagb-block-css',
            'uagb-style-css',
            'uagb-notice-settings',
            'classic-theme-styles',
        ];
    }
}
