<?php
/**
 * WordPress release credits.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions\Sources;

use Nhrcc\CoreContributions\Remote;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Credits Class
 */
class Credits {

	/**
	 * The credits API starts at this release.
	 */
	const FIRST = '3.2';

	/**
	 * Every released major version that has credits, oldest first.
	 *
	 * @return array|\WP_Error [ 'versions' => string[] ]
	 */
	public static function majors() {
		return Remote::get( 'https://api.wordpress.org/core/stable-check/1.0/', 86400, [ __CLASS__, 'parse_majors' ] );
	}

	/**
	 * Parse the list of released versions into majors.
	 *
	 * @param string $body Response body.
	 * @return array|null
	 */
	public static function parse_majors( $body ) {
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || ! $data ) {
			return null;
		}

		$majors = [];
		foreach ( array_keys( $data ) as $version ) {
			if ( preg_match( '/^(\d+\.\d+)/', (string) $version, $match ) && version_compare( $match[1], self::FIRST, '>=' ) ) {
				$majors[ $match[1] ] = true;
			}
		}

		$majors = array_keys( $majors );
		usort( $majors, 'version_compare' );

		return [ 'versions' => array_map( 'strval', $majors ) ];
	}

	/**
	 * Whether a username is credited in a release. Not cached: Sync stores the answer.
	 *
	 * @param string $version  Major version, e.g. "6.8".
	 * @param string $username WordPress.org username.
	 * @return array|\WP_Error [ 'credited' => bool ]
	 */
	public static function credited( $version, $username ) {
		return Remote::get(
			'https://api.wordpress.org/core/credits/1.1/?version=' . rawurlencode( $version ),
			0,
			function ( $body ) use ( $username ) {
				return self::parse_credited( $body, $username );
			}
		);
	}

	/**
	 * Look a username up in a credits response.
	 *
	 * @param string $body     Response body.
	 * @param string $username WordPress.org username.
	 * @return array|null
	 */
	public static function parse_credited( $body, $username ) {
		$data = json_decode( $body, true );

		if ( ! isset( $data['groups'] ) || ! is_array( $data['groups'] ) ) {
			return null;
		}

		foreach ( $data['groups'] as $group ) {
			// The libraries group lists software, not people.
			if ( isset( $group['type'], $group['data'] ) && 'libraries' !== $group['type'] && is_array( $group['data'] ) ) {
				foreach ( array_keys( $group['data'] ) as $credited ) {
					if ( strtolower( (string) $credited ) === $username ) {
						return [ 'credited' => true ];
					}
				}
			}
		}

		return [ 'credited' => false ];
	}
}
