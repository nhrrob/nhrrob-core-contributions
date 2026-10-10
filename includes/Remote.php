<?php
/**
 * Cached remote requests.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remote Class
 *
 * Every request the plugin makes goes through get(), so a service that is down
 * or rate limiting is asked again after FAILURE_TTL, not on every page view.
 */
class Remote {

	const FAILURE_TTL = 300;

	/**
	 * Fetch a URL and return what $parse makes of the body.
	 *
	 * @param string   $url   URL.
	 * @param int      $ttl   Seconds to cache a good answer; 0 to not cache it.
	 * @param callable $parse Receives the body, returns an array, or null when unusable.
	 * @return array|\WP_Error
	 */
	public static function get( $url, $ttl, $parse ) {
		$settings = Settings::get();
		$key      = 'nhrcc_' . md5( $url . '|' . $settings['cacheVersion'] );
		$cached   = get_transient( $key );

		if ( is_array( $cached ) ) {
			return isset( $cached['nhrcc_error'] ) ? self::error() : $cached;
		}

		$is_github = 0 === strpos( $url, 'https://api.github.com/' );
		$headers   = [ 'Accept' => $is_github ? 'application/vnd.github+json' : '*/*' ];
		if ( $is_github && '' !== Settings::token() ) {
			$headers['Authorization'] = 'Bearer ' . Settings::token();
		}

		$response = wp_remote_get(
			$url,
			[
				// A lifetime of 0 marks a background request, which can wait longer than a page view.
				'timeout' => $ttl > 0 ? 5 : 10,
				'headers' => $headers,
			]
		);
		$data     = null;

		if ( ! is_wp_error( $response ) && 200 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$data = call_user_func( $parse, (string) wp_remote_retrieve_body( $response ) );
		}

		if ( ! is_array( $data ) ) {
			set_transient( $key, [ 'nhrcc_error' => 1 ], self::FAILURE_TTL );
			return self::error();
		}

		if ( $ttl > 0 ) {
			set_transient( $key, $data, $ttl );
		}

		return $data;
	}

	/**
	 * The one error every failed request maps to.
	 *
	 * @return \WP_Error
	 */
	private static function error() {
		return new \WP_Error( 'nhrcc_unavailable', __( 'Contribution data is unavailable right now. Please try again later.', 'nhrrob-core-contributions' ) );
	}
}
