<?php

namespace Nhrcc\CoreContributions\Admin;


/**
 * The Menu handler class
 */
class SettingsPage extends Page
{

    /**
     * Initialize the class
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Handles the settings page
     *
     * @return void
     */
    public function view()
    {
        global $wpdb;

        $core_contributions = $this->get_core_contributions('audrasjb');
        // $this->get_core_contributions('robinwpdeveloper');

        ob_start();
        include NHRCC_VIEWS_PATH . '/admin/settings/index.php';
        $content = ob_get_clean();
        echo wp_kses($content, $this->allowed_html());
    }

}
