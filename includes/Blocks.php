<?php

namespace Nhrcc\CoreContributions;

/**
 * The admin class
 */
class Blocks extends App {

    /**
     * Class constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Register blocks
     *
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, 'register_blocks' ] );
    }

    /**
     * Register the blocks
     *
     * @return void
     */
    public function register_blocks() {
        register_block_type( NHRCC_PATH . '/assets/blocks/build/core-contributions-block', 
            [
				'render_callback' => [ $this, 'core_contributions_block_callback' ],
            ]
        );
    }

    public function core_contributions_block_callback( $attributes = [] ){
        if (empty($attributes['username'])) {
            return '<p>Please set a username in the block settings.</p>';
        }
        
        $username = sanitize_text_field($attributes['username']);
        $preset = isset($attributes['preset']) ? sanitize_text_field($attributes['preset']) : 'default';
        
        wp_enqueue_script('nhrcc-script');
        wp_enqueue_style('nhrcc-style');
        wp_enqueue_style('nhrcc-admin-style');

        $core_contributions = [];
        $total_contribution_count = 0;

        $page = ! empty($_GET['front_paged']) ? intval( $_GET['front_paged']) : 1;

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
            
            return wp_kses($content, $this->allowed_html());
        } catch (\Exception $e) {
            return new \WP_Error(
                'fetch_error',
                $e->getMessage(),
                ['status' => 500]
            );
        }
    }
}
