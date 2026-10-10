<?php
/**
 * Abilities for AI agents and MCP clients.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions;

use Nhrcc\CoreContributions\Sources\Commits;
use Nhrcc\CoreContributions\Sources\Directory;
use Nhrcc\CoreContributions\Sources\Profile;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abilities Class
 *
 * Registers read-only abilities with the WordPress Abilities API (WordPress
 * 6.9+), so AI agents and MCP clients can read what the blocks show. The hooks
 * below never fire on older versions.
 */
class Abilities {

	const CATEGORY = 'nhrcc';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'wp_abilities_api_categories_init', [ $this, 'register_category' ] );
		add_action( 'wp_abilities_api_init', [ $this, 'register_abilities' ] );
	}

	/**
	 * Register the ability category.
	 *
	 * @return void
	 */
	public function register_category() {
		// The hook only exists on 6.9+; the check is for Plugin Check ("Requires at least" is 6.6).
		if ( function_exists( 'wp_register_ability_category' ) ) {
			wp_register_ability_category(
				self::CATEGORY,
				[
					'label'       => __( 'Core Contributions', 'nhrrob-core-contributions' ),
					'description' => __( 'WordPress contributions of a WordPress.org user.', 'nhrrob-core-contributions' ),
				]
			);
		}
	}

	/**
	 * Register the abilities.
	 *
	 * @return void
	 */
	public function register_abilities() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			return;
		}

		foreach ( $this->definitions() as $name => $args ) {
			wp_register_ability( $name, $args );
		}
	}

	/**
	 * Anyone who can add the blocks can read what they would show.
	 *
	 * @return bool
	 */
	public function can_read() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Every ability, by name. All are read-only and share one permission check.
	 *
	 * @return array
	 */
	public function definitions() {
		$paged = [
			'page' => [
				'type'        => 'integer',
				'description' => __( 'Page of results, starting at 1.', 'nhrrob-core-contributions' ),
				'minimum'     => 1,
			],
		];
		$list  = [
			'username'      => [ 'type' => 'string' ],
			'page'          => [ 'type' => 'integer' ],
			'total'         => [ 'type' => 'integer' ],
			'contributions' => [ 'type' => 'array' ],
		];

		return [
			'nhrcc/get-core-contributions'      => $this->ability(
				__( 'Get core contributions', 'nhrrob-core-contributions' ),
				__( 'Returns the WordPress core contributions (changesets that give props) of a WordPress.org username: the total count and one page of changesets with link, changeset, component, description, ticket and date. Data comes from the WordPress core mirror on GitHub and is cached.', 'nhrrob-core-contributions' ),
				'get_core_contributions',
				$paged,
				$list
			),
			'nhrcc/get-gutenberg-contributions' => $this->ability(
				__( 'Get Gutenberg contributions', 'nhrrob-core-contributions' ),
				__( 'Returns the Gutenberg commits that credit a WordPress.org username as co-author: the total count and one page of commits with link, description, pull request number (as ticket) and date. Data comes from the Gutenberg repository on GitHub and is cached.', 'nhrrob-core-contributions' ),
				'get_gutenberg_contributions',
				$paged,
				$list
			),
			'nhrcc/get-contribution-summary'    => $this->ability(
				__( 'Get contribution summary', 'nhrrob-core-contributions' ),
				__( 'Returns the stored summary of a WordPress.org username: core and Gutenberg totals, core contributions per year, top components, first and latest contribution dates and the WordPress releases that credit them. The summary exists for the default username and for usernames a block on this site shows; "ready" is false for any other username and until the first background run has finished.', 'nhrrob-core-contributions' ),
				'get_summary',
				[],
				[
					'username' => [ 'type' => 'string' ],
					'ready'    => [ 'type' => 'boolean' ],
					'summary'  => [ 'type' => 'object' ],
				]
			),
			'nhrcc/get-profile'                 => $this->ability(
				__( 'Get WordPress.org profile', 'nhrrob-core-contributions' ),
				__( 'Returns the public WordPress.org profile of a username: display name, member-since date and profile badges (team, WordCamp, translation and other contribution badges).', 'nhrrob-core-contributions' ),
				'get_profile',
				[],
				[
					'username' => [ 'type' => 'string' ],
					'name'     => [ 'type' => 'string' ],
					'since'    => [ 'type' => 'string' ],
					'badges'   => [ 'type' => 'array' ],
				]
			),
			'nhrcc/get-plugins-and-themes'      => $this->ability(
				__( 'Get plugins and themes', 'nhrrob-core-contributions' ),
				__( 'Returns the plugins and themes a username has published on WordPress.org, with link, description, active installs, downloads and rating.', 'nhrrob-core-contributions' ),
				'get_plugins_and_themes',
				[],
				[
					'username' => [ 'type' => 'string' ],
					'plugins'  => [ 'type' => 'array' ],
					'themes'   => [ 'type' => 'array' ],
				]
			),
		];
	}

	/**
	 * Build one read-only ability that takes a username.
	 *
	 * @param string $label       Label.
	 * @param string $description Description.
	 * @param string $method      Method of this class that executes it.
	 * @param array  $input       Input properties besides the username.
	 * @param array  $output      Output properties.
	 * @return array
	 */
	private function ability( $label, $description, $method, array $input, array $output ) {
		return [
			'label'               => $label,
			'description'         => $description,
			'category'            => self::CATEGORY,
			'execute_callback'    => [ $this, $method ],
			'permission_callback' => [ $this, 'can_read' ],
			'input_schema'        => [
				'type'       => 'object',
				'properties' => [
					'username' => [
						'type'        => 'string',
						'description' => __( 'WordPress.org username, e.g. nhrrob.', 'nhrrob-core-contributions' ),
						'pattern'     => '^[A-Za-z0-9._-]+$',
						'maxLength'   => 60,
					],
				] + $input,
				'required'   => [ 'username' ],
			],
			'output_schema'       => [
				'type'       => 'object',
				'properties' => $output,
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
		];
	}

	/**
	 * Core contributions of a username.
	 *
	 * @param array $input Ability input.
	 * @return array|\WP_Error
	 */
	public function get_core_contributions( $input ) {
		return $this->commits( 'core', $input );
	}

	/**
	 * Gutenberg contributions of a username.
	 *
	 * @param array $input Ability input.
	 * @return array|\WP_Error
	 */
	public function get_gutenberg_contributions( $input ) {
		return $this->commits( 'gutenberg', $input );
	}

	/**
	 * One page of commits from a source.
	 *
	 * @param string $source 'core' or 'gutenberg'.
	 * @param array  $input  Ability input.
	 * @return array|\WP_Error
	 */
	private function commits( $source, $input ) {
		$settings = Settings::get();
		$username = Settings::username( $input['username'] );
		$page     = isset( $input['page'] ) ? max( 1, absint( $input['page'] ) ) : 1;
		$data     = Commits::page( $source, $username, $page, $settings['postsPerPage'], $settings['cacheDuration'] );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		return [
			'username'      => $username,
			'page'          => $page,
			'total'         => $data['total'],
			'contributions' => $data['items'],
		];
	}

	/**
	 * The stored summary of a username.
	 *
	 * @param array $input Ability input.
	 * @return array
	 */
	public function get_summary( $input ) {
		$username = Settings::username( $input['username'] );
		$settings = Settings::get();
		// Only a username the site already shows is gathered; asking about another one starts no background work.
		$summary = $username === $settings['username'] || Sync::stored( $username ) ? Sync::summary( $username ) : [];
		unset( $summary['job'], $summary['checked'] );

		return [
			'username' => $username,
			'ready'    => (bool) $summary,
			'summary'  => (object) $summary,
		];
	}

	/**
	 * The WordPress.org profile of a username.
	 *
	 * @param array $input Ability input.
	 * @return array|\WP_Error
	 */
	public function get_profile( $input ) {
		$username = Settings::username( $input['username'] );
		$profile  = Profile::get( $username );

		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		return [
			'username' => $username,
			'name'     => $profile['name'],
			'since'    => $profile['since'],
			'badges'   => $profile['badges'],
		];
	}

	/**
	 * The plugins and themes of a username.
	 *
	 * @param array $input Ability input.
	 * @return array|\WP_Error
	 */
	public function get_plugins_and_themes( $input ) {
		$settings = Settings::get();
		$username = Settings::username( $input['username'] );
		$plugins  = Directory::items( 'plugins', $username, $settings['cacheDuration'] );
		$themes   = Directory::items( 'themes', $username, $settings['cacheDuration'] );

		if ( is_wp_error( $plugins ) ) {
			return $plugins;
		}

		return [
			'username' => $username,
			'plugins'  => $plugins['items'],
			'themes'   => is_wp_error( $themes ) ? [] : $themes['items'],
		];
	}
}
