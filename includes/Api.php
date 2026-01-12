<?php
namespace Nhrcc\CoreContributions;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * API Class
 */
class Api extends App {

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
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'backgroundColor' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'textColor' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'style' => [
                    'required' => false,
                    'type' => 'object',
                ],
                'linkColor' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'fontSize' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'fontFamily' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'titleColor' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'titleFontSize' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'titleFontWeight' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'titleBackgroundColor' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'accentColor' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'metaColor' => [
                    'required' => false,
                    'type' => 'string',
                ],
                'paginationColor' => [
                    'required' => false,
                    'type' => 'string',
                ],
            ],
        ]);
    }

    public function render_core_contributions($request) {
        $attributes = $request->get_params();
        
        // Render the block using the core function to ensure block context and styles are handled correctly
        $block = [
            'blockName' => 'nhrcc-core-contributions/core-contributions-block',
            'attrs'     => $attributes,
        ];
        
        $content = render_block($block);
        
        return rest_ensure_response([
            'content' => $content,
        ]);
    }
}