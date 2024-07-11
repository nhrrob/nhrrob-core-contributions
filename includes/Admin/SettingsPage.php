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

        $total_contribution_count = 0;
        $core_contributions = [];

        $username = sanitize_text_field('nhrrob');
        $page = 1;
        print_r($_REQUEST);

        if (isset($_REQUEST['nhrcc_form_nonce']) && wp_verify_nonce($_REQUEST['nhrcc_form_nonce'], 'nhrcc_form_action')) {
            // Default values
            $username = ! empty($_REQUEST['nhrcc_username']) ? sanitize_text_field($_REQUEST['nhrcc_username']) : sanitize_text_field( $username );
            $page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
        }

        if ($username) {
            $core_contributions = $this->get_core_contributions($username, $page);
            $total_contribution_count = $this->get_core_contribution_count($username);
        }

        ob_start();
        include NHRCC_VIEWS_PATH . '/admin/settings/index.php';
        $content = ob_get_clean();
        echo wp_kses($content, $this->allowed_html());
    }
}
