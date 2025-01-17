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
        
        $this->dispatch_actions();
        new Admin\Menu( );
    }

    /**
     * Dispatch and bind actions
     *
     * @return void
     */
    public function dispatch_actions( ) {
        $settingsPage = new SettingsPage();

        add_filter('plugin_action_links', array($this, 'plugin_actions_links'), 10, 2);

        // Settings Page
        add_action('admin_init', [ $settingsPage, 'register_settings' ]);
        add_action('rest_api_init', [ $settingsPage, 'register_rest_routes' ]);


    }

    /**
     * Add settings page link on plugins page
     *
     * @param array $links
     * @param string $file
     *
     * @return array
     * @since 1.0.1
     */
    public function plugin_actions_links($links, $file) {
        $nhrcc_plugin = plugin_basename(NHRCC_FILE);

        if ($file == $nhrcc_plugin && current_user_can('manage_options')) {
            $links[] = sprintf('<a href="%s">%s</a>', admin_url("tools.php?page={$this->page_slug}"), __('Core Contributions', 'nhrrob-core-contributions'));
        }

        return $links;
    }
}
