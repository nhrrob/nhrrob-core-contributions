<?php
namespace Nhrcc\CoreContributions;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Abilities Class
 *
 * Registers the plugin's one ability with the WordPress Abilities API
 * (WordPress 6.9+), so AI agents and MCP clients can read what the block shows.
 * The hooks below never fire on older versions.
 */
class Abilities extends App {

    /**
     * Initialize the class
     */
    public function __construct() {
        parent::__construct();
    }

    public function init() {
        add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
        add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
    }

    /**
     * Register the ability category
     *
     * @return void
     */
    public function register_category() {
        wp_register_ability_category( 'nhrcc', [
            'label'       => __( 'Core Contributions', 'nhrrob-core-contributions' ),
            'description' => __( 'WordPress core contributions of a WordPress.org user.', 'nhrrob-core-contributions' ),
        ] );
    }

    /**
     * Register the abilities
     *
     * @return void
     */
    public function register_abilities() {
        wp_register_ability( 'nhrcc/get-core-contributions', [
            'label'               => __( 'Get core contributions', 'nhrrob-core-contributions' ),
            'description'         => __( 'Returns the WordPress core contributions (changesets that give props) of a WordPress.org username: the total count and one page of changesets with link, description, ticket and date. Data comes from WordPress Core Trac and is cached.', 'nhrrob-core-contributions' ),
            'category'            => 'nhrcc',
            'execute_callback'    => [ $this, 'get_contributions' ],
            // Anyone who can add the block can read what it would show.
            'permission_callback' => function() {
                return current_user_can( 'edit_posts' );
            },
            'input_schema'        => [
                'type'       => 'object',
                'properties' => [
                    'username' => [
                        'type'        => 'string',
                        'description' => __( 'WordPress.org username, e.g. nhrrob.', 'nhrrob-core-contributions' ),
                        'pattern'     => '^[A-Za-z0-9._-]+$',
                        'maxLength'   => 60,
                    ],
                    'page'     => [
                        'type'        => 'integer',
                        'description' => __( 'Page of results, starting at 1.', 'nhrrob-core-contributions' ),
                        'minimum'     => 1,
                    ],
                ],
                'required'   => [ 'username' ],
            ],
            'output_schema'       => [
                'type'       => 'object',
                'properties' => [
                    'username'      => [ 'type' => 'string' ],
                    'page'          => [ 'type' => 'integer' ],
                    'total'         => [ 'type' => 'integer' ],
                    'contributions' => [ 'type' => 'array' ],
                ],
            ],
            'meta'                => [
                'annotations'  => [
                    'readonly'    => true,
                    'destructive' => false,
                    'idempotent'  => true,
                ],
                'public'       => true,
                // WordPress 6.9 has no `public` flag; the MCP Adapter reads `mcp.public`.
                'show_in_rest' => true,
                'mcp'          => [ 'public' => true ],
            ],
        ] );
    }

    public function get_contributions( $input ) {
        $username = sanitize_text_field( $input['username'] );
        $page     = isset( $input['page'] ) ? absint( $input['page'] ) : 1;
        $total    = $this->get_core_contribution_count( $username );

        // The count comes back as a message when Trac gave no usable answer.
        if ( ! is_int( $total ) ) {
            return new \WP_Error( 'nhrcc_unavailable', wp_strip_all_tags( $total ) );
        }

        return [
            'username'      => $username,
            'page'          => $page,
            'total'         => $total,
            'contributions' => $this->get_core_contributions( $username, $page ),
        ];
    }
}
