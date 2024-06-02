<?php

namespace NhrrobCoreContributions;

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
            'nhrrob-core-contributions-script' => [
                'src'     => NHRROB_CORE_CONTRIBUTIONS_ASSETS . '/js/frontend.js',
                'version' => filemtime( NHRROB_CORE_CONTRIBUTIONS_PATH . '/assets/js/frontend.js' ),
                'deps'    => [ 'jquery' ]
            ],
            'nhrrob-core-contributions-admin-script' => [
                'src'     => NHRROB_CORE_CONTRIBUTIONS_ASSETS . '/js/admin.js',
                'version' => filemtime( NHRROB_CORE_CONTRIBUTIONS_PATH . '/assets/js/admin.js' ),
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
            'nhrrob-core-contributions-style' => [
                'src'     => NHRROB_CORE_CONTRIBUTIONS_ASSETS . '/css/frontend.css',
                'version' => filemtime( NHRROB_CORE_CONTRIBUTIONS_PATH . '/assets/css/frontend.css' )
            ],
            'nhrrob-core-contributions-admin-style' => [
                'src'     => NHRROB_CORE_CONTRIBUTIONS_ASSETS . '/css/admin.css',
                'version' => filemtime( NHRROB_CORE_CONTRIBUTIONS_PATH . '/assets/css/admin.css' )
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

        wp_localize_script( 'nhrrob-core-contributions-admin-script', 'nhrrobCoreContributions', [
            'nonce' => wp_create_nonce( 'nhrrob-core-contributions-admin-nonce' ),
            'confirm' => __( 'Are you sure?', 'nhrrob-core-contributions' ),
            'error' => __( 'Something went wrong', 'nhrrob-core-contributions' ),
        ] );
    }
}
