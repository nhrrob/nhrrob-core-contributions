<?php

namespace Nhrcc\CoreContributions\Admin;

use Nhrcc\CoreContributions\App;

/**
 * The Menu handler class
 */
class Menu extends App {

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
        $parent_slug = esc_html( 'nhrcc-core-contributions' );
        $capability = esc_html( 'manage_options' );

        // $hook = add_menu_page( __( 'NHR Core Contributions', 'nhrrob-core-contributions' ), __( 'NHR Core Contributions', 'nhrrob-core-contributions' ), $capability, $parent_slug, [ $this, 'settings_page' ], 'dashicons-welcome-learn-more' );
        // add_submenu_page( $parent_slug, __( 'Resource Book', 'nhrrob-core-contributions' ), __( 'Resource Book', 'nhrrob-core-contributions' ), $capability, $parent_slug, [ $this, 'plugin_page' ] );
        // add_submenu_page( $parent_slug, __( 'Settings', 'nhrrob-core-contributions' ), __( 'Settings', 'nhrrob-core-contributions' ), $capability, 'nhrcc-core-contributions-settings', [ $this, 'settings_page' ] );

        $hook = add_submenu_page( 'tools.php', __( 'Core Contributions', 'nhrrob-core-contributions' ), __( 'Core Contributions', 'nhrrob-core-contributions' ), $capability, $parent_slug, [ $this, 'settings_page' ] );
        add_action( 'admin_head-' . $hook, [ $this, 'enqueue_assets' ] );
    }

    /**
     * Handles the settings page
     *
     * @return void
     */
    public function settings_page() {
        $settings_page = new SettingsPage();
        
        ob_start();
        $settings_page->view();
        $content = ob_get_clean();
        
        echo wp_kses( $content, $this->allowed_html() );
    }

    /**
     * Enqueue scripts and styles
     *
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'nhrcc-admin-style' );
        wp_enqueue_script( 'nhrcc-admin-script' );
    }
}
