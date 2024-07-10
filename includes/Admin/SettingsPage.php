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

        // Default values
        $username = isset($_POST['nhrcc_username']) ? sanitize_text_field($_POST['nhrcc_username']) : 'audrasjb';
        $page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        $contributions_per_page = 10; // Define the number of contributions per page

        if ($username) {
            // Get core contributions and total count for the submitted username
            $core_contributions = $this->get_core_contributions($username, $page, $contributions_per_page);
            $total_contribution_count = $this->get_core_contribution_count($username);
            $total_pages = ceil($total_contribution_count / $contributions_per_page);
        } else {
            $core_contributions = [];
            $total_contribution_count = 0;
            $total_pages = 0;
        }

        ob_start();
        include NHRCC_VIEWS_PATH . '/admin/settings/index.php';
        $content = ob_get_clean();
        echo wp_kses($content, $this->allowed_html());
    }
}
