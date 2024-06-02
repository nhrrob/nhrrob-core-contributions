<?php

namespace NhrrobCoreContributions\Admin;

/**
 * The Menu handler class
 */
class Menu {

    /**
     * Initialize the class
     */
    function __construct( ) {
        add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    /**
     * Register admin menu
     *
     * @return void
     */
    public function admin_menu() {
        $parent_slug = 'nhrrob-core-contributions';
        $capability = 'manage_options';

        $hook = add_menu_page( __( 'Nhrrob Core Contributions', 'nhrrob-core-contributions' ), __( 'Nhrrob Core Contributions', 'nhrrob-core-contributions' ), $capability, $parent_slug, [ $this, 'settings_page' ], 'dashicons-welcome-learn-more' );
        // add_submenu_page( $parent_slug, __( 'Resource Book', 'nhrrob-core-contributions' ), __( 'Resource Book', 'nhrrob-core-contributions' ), $capability, $parent_slug, [ $this, 'plugin_page' ] );
        // add_submenu_page( $parent_slug, __( 'Settings', 'nhrrob-core-contributions' ), __( 'Settings', 'nhrrob-core-contributions' ), $capability, 'nhrrob-core-contributions-settings', [ $this, 'settings_page' ] );

        add_action( 'admin_head-' . $hook, [ $this, 'enqueue_assets' ] );
    }

    /**
     * Handles the settings page
     *
     * @return void
     */
    public function settings_page() {
        _e('Settings Page Content', 'wp-plugin_template');
    }

    /**
     * Enqueue scripts and styles
     *
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'nhrrob-core-contributions-admin-style' );
        wp_enqueue_script( 'nhrrob-core-contributions-admin-script' );
    }
}
