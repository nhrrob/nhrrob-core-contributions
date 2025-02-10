<?php

namespace Nhrcc\CoreContributions;

/**
 * Assets handler class
 */
class Assets {

    protected $asset_file;

    /**
     * Class constructor
     */
    function __construct() {
        
    }

    public function init() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ] );
        // add_action( 'enqueue_block_editor_assets', [ $this, 'register_assets' ] ); // editor only
        // add_action( 'enqueue_block_assets', [ $this, 'register_assets' ] ); // front and editor
    }

    /**
     * All available scripts
     *
     * @return array
     */
    public function get_scripts() {
        return [
            'nhrcc-script' => [
                'src'     => NHRCC_ASSETS . '/js/frontend.js',
                'version' => filemtime( NHRCC_PATH . '/assets/js/frontend.js' ),
                'deps'    => [ 'jquery' ]
            ],
            'nhrcc-admin-script' => [
                'src'     => NHRCC_ASSETS . '/js/admin.js',
                'version' => filemtime( NHRCC_PATH . '/assets/js/admin.js' ),
                'deps'    => [ 'jquery', 'wp-util' ]
            ],
            'nhrcc-admin-settings-script' => [
                'src'     => NHRCC_ASSETS . '/dashboard/build/index.js',
                'version' => filemtime( NHRCC_PATH . '/assets/dashboard/build/index.js' ),
                'deps'    => ['wp-element', 'wp-api-fetch']
            ],
            'nhrcc-common-script' => [
                'src'     => NHRCC_ASSETS . '/js/common.js',
                'version' => filemtime( NHRST_PATH . '/assets/js/common.js' ),
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
            'nhrcc-style' => [
                'src'     => NHRCC_ASSETS . '/css/frontend.css',
                'version' => filemtime( NHRCC_PATH . '/assets/css/frontend.css' )
            ],
            'nhrcc-admin-style' => [
                'src'     => NHRCC_ASSETS . '/css/admin.out.css',
                'version' => filemtime( NHRCC_PATH . '/assets/css/admin.out.css' )
            ],
            // 'nhrcc-admin-settings-style' => [
            //     'src'     => NHRCC_ASSETS . '/dashboard/build/index.css',
            //     'version' => filemtime( NHRCC_PATH . '/assets/dashboard/build/index.css' )
            // ],
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

        wp_localize_script( 'nhrcc-admin-script', 'nhrccCoreContributions', [
            'nonce' => wp_create_nonce( 'nhrcc-admin-nonce' ),
            'confirm' => __( 'Are you sure?', 'nhrrob-core-contributions' ),
            'error' => __( 'Something went wrong', 'nhrrob-core-contributions' ),
            'apiUrl' => esc_url_raw(rest_url('nhrcc-core-contributions/v1/settings')),
        ] );

        // 
        $nhrcc_settings = get_option('nhrcc_settings');

        $default_username = ! empty( $nhrcc_settings['username'] ) ? sanitize_text_field( $nhrcc_settings['username']) : '';
        $default_preset = ! empty( $nhrcc_settings['preset'] ) ? sanitize_text_field( $nhrcc_settings['preset']) : 'default';

        $default_settings = [
            'username' => $default_username,
            'preset' => $default_preset,
        ];

        wp_enqueue_script( 'nhrcc-common-script' );

        wp_localize_script( 'nhrcc-common-script', 'nhrccCoreContributionsCommonObj', [
            'nhrccSettings' => $default_settings,
        ] );
    }
}
