<?php

namespace Nhrcc\CoreContributions\Admin;

use WP_REST_Response;

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

        if (isset( $_REQUEST['nhrcc_form_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['nhrcc_form_nonce'] ) ), 'nhrcc_form_action' )) {
            // Default values
            $username = ! empty($_REQUEST['nhrcc_username']) ? sanitize_text_field( wp_unslash( $_REQUEST['nhrcc_username'] ) ) : sanitize_text_field( $username );
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

    // Register REST API endpoints
    public function register_rest_routes() {
        register_rest_route('nhrcc-core-contributions/v1', '/settings', array(
            array(
                'methods' => 'GET',
                'callback' => [$this, 'get_settings' ],
                'permission_callback' => function() {
                    return current_user_can('manage_options');
                }
            ),
            array(
                'methods' => 'POST',
                'callback' => [$this, 'update_settings' ],
                'permission_callback' => function() {
                    return current_user_can('manage_options');
                }
            )
        ));
    }

    // GET settings callback
    public function get_settings($request) {
        $settings = array(
            'username' => get_option('nhrcc_default_username', ''),
            'cacheDuration' => get_option('nhrcc_cache_duration', 3600),
            // 'showAvatars' => get_option('nhrcc_show_avatars', true),
            'postsPerPage' => get_option('nhrcc_posts_per_page', 10),
            // 'displayStyle' => get_option('nhrcc_display_style', 'grid'),
            // 'enableAnalytics' => get_option('nhrcc_enable_analytics', false)
        );

        return rest_ensure_response($settings);
    }

    // POST settings callback
    public function update_settings($request) {
        $params = $request->get_params();
        
        update_option('nhrcc_default_username', sanitize_text_field($params['username']));
        update_option('nhrcc_cache_duration', absint($params['cacheDuration']));
        // update_option('nhrcc_show_avatars', (bool) $params['showAvatars']);
        update_option('nhrcc_posts_per_page', absint($params['postsPerPage']));
        // update_option('nhrcc_display_style', sanitize_text_field($params['displayStyle']));
        // update_option('nhrcc_enable_analytics', (bool) $params['enableAnalytics']);
        
        return rest_ensure_response(['message' => 'Settings saved successfully!']);
    }

}
