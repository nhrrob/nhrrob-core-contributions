<?php
/**
 * Plugin settings and their REST routes.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Class
 */
class Settings {

	const OPTION         = 'nhrcc_settings';
	const TOKEN_OPTION   = 'nhrcc_github_token';
	const REST_NAMESPACE = 'nhrcc-core-contributions/v1';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
	}

	/**
	 * All settings, sanitized, with defaults filled in.
	 *
	 * @return array
	 */
	public static function get() {
		return self::sanitize( (array) get_option( self::OPTION, [] ) );
	}

	/**
	 * Sanitize a settings array.
	 *
	 * @param array $input Raw settings.
	 * @return array
	 */
	public static function sanitize( array $input ) {
		$per_page = isset( $input['postsPerPage'] ) ? (int) $input['postsPerPage'] : 10;
		$duration = isset( $input['cacheDuration'] ) ? (int) $input['cacheDuration'] : 12 * 3600;

		return [
			'username'      => self::username( isset( $input['username'] ) ? $input['username'] : '' ),
			// GitHub returns at most 100 results per page.
			'postsPerPage'  => min( 100, max( 1, $per_page ) ),
			'cacheDuration' => min( 7 * 86400, max( 3600, $duration ) ),
			// Part of every cache key: raising it abandons everything cached so far.
			'cacheVersion'  => isset( $input['cacheVersion'] ) ? max( 1, (int) $input['cacheVersion'] ) : 1,
		];
	}

	/**
	 * Reduce a value to a WordPress.org username.
	 *
	 * @param mixed $raw Raw value.
	 * @return string
	 */
	public static function username( $raw ) {
		return trim( substr( (string) preg_replace( '/[^a-z0-9._-]/', '', strtolower( is_scalar( $raw ) ? (string) $raw : '' ) ), 0, 60 ), '.' );
	}

	/**
	 * The stored GitHub token, if any.
	 *
	 * @return string
	 */
	public static function token() {
		return (string) get_option( self::TOKEN_OPTION, '' );
	}

	/**
	 * Whether the current user may manage the settings.
	 *
	 * @return bool
	 */
	public function can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Register the REST routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			self::REST_NAMESPACE,
			'/settings',
			[
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'rest_get' ],
					'permission_callback' => [ $this, 'can_manage' ],
				],
				[
					'methods'             => 'POST',
					'callback'            => [ $this, 'rest_update' ],
					'permission_callback' => [ $this, 'can_manage' ],
				],
			]
		);

		register_rest_route(
			self::REST_NAMESPACE,
			'/refresh',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'rest_refresh' ],
				'permission_callback' => [ $this, 'can_manage' ],
			]
		);
	}

	/**
	 * What the settings screen receives. The token itself never leaves the server.
	 *
	 * @return array
	 */
	public function rest_get() {
		$settings = self::get();
		$summary  = '' !== $settings['username'] ? Sync::stored( $settings['username'] ) : [];

		unset( $settings['cacheVersion'] );
		$settings['hasToken'] = '' !== self::token();
		$settings['synced']   = ! empty( $summary['synced'] ) ? (int) $summary['synced'] : 0;

		return $settings;
	}

	/**
	 * Save the settings.
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return array
	 */
	public function rest_update( $request ) {
		$params  = (array) $request->get_params();
		$current = self::get();

		// A request that sends some settings leaves the others as they are.
		update_option( self::OPTION, self::sanitize( array_merge( $params, array_diff_key( $current, $params ), [ 'cacheVersion' => $current['cacheVersion'] ] ) ) );

		// An absent key leaves the token alone; an empty one removes it.
		if ( isset( $params['githubToken'] ) ) {
			$token = preg_replace( '/[^A-Za-z0-9_]/', '', (string) $params['githubToken'] );
			if ( '' === $token ) {
				delete_option( self::TOKEN_OPTION );
			} else {
				update_option( self::TOKEN_OPTION, $token, false );
			}
		}

		return $this->rest_get();
	}

	/**
	 * Drop everything cached and gathered, so the next view fetches again.
	 *
	 * @return array
	 */
	public function rest_refresh() {
		$settings = self::get();
		++$settings['cacheVersion'];

		update_option( self::OPTION, $settings );
		delete_option( Sync::OPTION );

		if ( '' !== $settings['username'] ) {
			Sync::schedule( $settings['username'], 0 );
		}

		return $this->rest_get();
	}
}
