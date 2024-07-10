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

        // Check if a username is submitted via the form
        $username = isset($_POST['nhrcc_username']) ? sanitize_text_field($_POST['nhrcc_username']) : '';

        if ($username) {
            // Get core contributions and total count for the submitted username
            $core_contributions = $this->get_core_contributions($username);
            $total_contribution_count = $this->get_core_contribution_count($username);
        } else {
            $core_contributions = [];
            $total_contribution_count = 0;
        }


        ob_start();
        include NHRCC_VIEWS_PATH . '/admin/settings/index.php';
        $content = ob_get_clean();
        echo wp_kses($content, $this->allowed_html());
    }
}
