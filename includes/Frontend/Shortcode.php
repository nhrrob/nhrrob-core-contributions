<?php

namespace Nhrcc\CoreContributions\Frontend;

use Nhrcc\CoreContributions\App;

/**
 * Shortcode handler class
 */
class Shortcode extends App
{

    /**
     * Initialize the class
     */
    function __construct()
    {
        add_shortcode('nhrcc_core_contributions', [$this, 'render_shortcode']);
    }

    /**
     * Shortcode handler class
     *
     * @param  array $atts
     * @param  string $content
     *
     * @return string
     */
    public function render_shortcode($atts, $content = '')
    {
        wp_enqueue_script('nhrcc-script');
        wp_enqueue_style('nhrcc-style');
        wp_enqueue_style('nhrcc-admin-style');

        // Extract shortcode attributes if needed (not used in this example)
        $atts = shortcode_atts(array(
            // Define any attributes you might need
            'username' => 'nhrrob', // Default username
        ), $atts, 'nhrcc_core_contributions');

        $total_contribution_count = 0;
        $core_contributions = [];
        $username = sanitize_text_field( $atts['username'] );
        $page = 1;
        
        // Verify the nonce before processing form data
        if (isset( $_REQUEST['nhrcc_form_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nhrcc_form_nonce'] ) ), 'nhrcc_form_action') ) {
            $page = isset($_GET['front_paged']) ? absint($_GET['front_paged']) : 1;
            // Initialize variables
            $username = isset($_REQUEST['nhrcc_username']) ? sanitize_text_field($_REQUEST['nhrcc_username']) : sanitize_text_field( $username );
        }

        // Check if username is provided and retrieve data
        if ($username) {
            $core_contributions = $this->get_core_contributions($username, $page);
            $total_contribution_count = $this->get_core_contribution_count($username);

            $total_contribution_count = is_wp_error( $total_contribution_count ) ? 0 : $total_contribution_count;
        }

        // Buffer output HTML
        ob_start();
        include NHRCC_VIEWS_PATH . '/admin/settings/index.php';
        $content = ob_get_clean();
        
        return wp_kses($content, $this->allowed_html());
    }
}
