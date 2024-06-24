<?php

namespace Nhrcc\CoreContributions;

/**
 * Assets handler class
 */
class Assets {

    /**
     * Class constructor
     */
    function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );
    }

    /**
     * All available scripts
     *
     * @return array
     */
    public function get_scripts() {
        return [
            'nhrcc-core-contributions-script' => [
                'src'     => NHRCC_ASSETS . '/js/frontend.js',
                'version' => filemtime( NHRCC_PATH . '/assets/js/frontend.js' ),
                'deps'    => [ 'jquery' ]
            ],
            'nhrcc-core-contributions-admin-script' => [
                'src'     => NHRCC_ASSETS . '/js/admin.js',
                'version' => filemtime( NHRCC_PATH . '/assets/js/admin.js' ),
                'deps'    => [ 'jquery', 'wp-util' ]
            ],
        ];
    }

    /**
     * All available styles
     *
     * @return array
     */
    public function get_styles() {
        return [
            'nhrcc-core-contributions-style' => [
                'src'     => NHRCC_ASSETS . '/css/frontend.css',
                'version' => filemtime( NHRCC_PATH . '/assets/css/frontend.css' )
            ],
            'nhrcc-core-contributions-admin-style' => [
                'src'     => NHRCC_ASSETS . '/css/admin.css',
                'version' => filemtime( NHRCC_PATH . '/assets/css/admin.css' )
            ],
        ];
    }

    /**
     * Register scripts and styles
     *
     * @return void
     */
    public function register_assets() {
        $scripts = $this->get_scripts();
        $styles  = $this->get_styles();

        foreach ( $scripts as $handle => $script ) {
            $deps = isset( $script['deps'] ) ? $script['deps'] : false;

            wp_register_script( $handle, $script['src'], $deps, $script['version'], true );
        }

        foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;

            wp_register_style( $handle, $style['src'], $deps, $style['version'] );
        }

        wp_localize_script( 'nhrcc-core-contributions-admin-script', 'nhrccCoreContributions', [
            'nonce' => wp_create_nonce( 'nhrcc-core-contributions-admin-nonce' ),
            'confirm' => __( 'Are you sure?', 'nhrrob-core-contributions' ),
            'error' => __( 'Something went wrong', 'nhrrob-core-contributions' ),
        ] );
    }
}
