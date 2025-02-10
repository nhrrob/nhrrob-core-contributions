<?php

namespace Nhrcc\CoreContributions;

/**
 * API Class
 */
class API extends App {

    /**
     * Initialize the class
     */
    public function __construct() {
        parent::__construct();
    }

    public function init() {
        add_action( 'rest_api_init', [ $this, 'register_api' ] );
    }

    /**
     * Register the API
     *
     * @return void
     */
    public function register_api() {
        register_rest_route('nhrcc-core-contributions/v1', '/core-contributions/render', [
            'methods' => \WP_REST_Server::CREATABLE,
            'callback' => [$this, 'render_core_contributions'],
            'permission_callback' => function() {
                    return current_user_can('manage_options');
            },
            'args' => [
                'username' => [
                    'required' => true,
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'preset' => [
                    'required' => false,
                    'type' => 'string',
                    'default' => 'default',
                    'enum' => ['default', 'minimal'],
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    public function render_core_contributions($request) {
        wp_enqueue_script('nhrcc-script');
        wp_enqueue_style('nhrcc-style');
        wp_enqueue_style('nhrcc-admin-style');

        $username   = sanitize_text_field($request->get_param('username')) ?? '';
        $preset     = sanitize_text_field($request->get_param('preset')) ?? 'default';
        $page       = absint($request->get_param('front_paged')) ?? 1;

        $core_contributions = [];
        $total_contribution_count = 0;

        $page = 1;

        try {
            if ($username) {
                $core_contributions = $this->get_core_contributions($username, $page);
                $total_contribution_count = $this->get_core_contribution_count($username);
    
                $total_contribution_count = is_wp_error( $total_contribution_count ) ? 0 : $total_contribution_count;
            }

            // Buffer output HTML
            ob_start();
            include NHRCC_VIEWS_PATH . '/blocks/core-contributions-block/index.php';
            $content = ob_get_clean();
            
            return rest_ensure_response([
                'content' => wp_kses($content, $this->allowed_html()),
            ]);
        } catch (\Exception $e) {
            return new \WP_Error(
                'fetch_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }
}