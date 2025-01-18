<?php

namespace Nhrcc\CoreContributions;

use Nhrcc\CoreContributions\Admin\SettingsPage;

/**
 * The admin class
 */
class Admin extends App {

    /**
     * Initialize the class
     */
    function __construct() {
        parent::__construct();
        
        new Admin\Menu( );
    }

    /**
     * Dispatch and bind actions
     *
     * @return void
     */
    public static function dispatch_actions( ) {
        $settingsPage = new SettingsPage();

        // Settings Page
        add_action('admin_init', [ $settingsPage, 'register_settings' ]);
        add_action('rest_api_init', [ $settingsPage, 'register_rest_routes' ]);
    }
}
