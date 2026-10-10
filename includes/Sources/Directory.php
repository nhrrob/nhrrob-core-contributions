<?php
/**
 * Plugins and themes a username has published on WordPress.org.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions\Sources;

use Nhrcc\CoreContributions\Remote;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Class
 */
class Directory {

	/**
	 * API URL for a username's plugins or themes.
	 *
	 * @param string $type     'plugins' or 'themes'.
	 * @param string $username WordPress.org username.
	 * @return string
	 */
	public static function url( $type, $username ) {
		$type    = 'themes' === $type ? 'themes' : 'plugins';
		$request = [
			'author'   => $username,
			'per_page' => 100,
			'fields'   => 'themes' === $type
				? [
					'active_installs' => 1,
					'downloaded'      => 1,
				]
				: [ 'icons' => 1 ],
		];

		return 'https://api.wordpress.org/' . $type . '/info/1.2/?' . http_build_query(
			[
				'action'  => 'themes' === $type ? 'query_themes' : 'query_plugins',
				'request' => $request,
			]
		);
	}

	/**
	 * The items, most installed first.
	 *
	 * @param string $type     'plugins' or 'themes'.
	 * @param string $username WordPress.org username.
	 * @param int    $ttl      Cache lifetime in seconds.
	 * @return array|\WP_Error [ 'items' => array ]
	 */
	public static function items( $type, $username, $ttl ) {
		$type = 'themes' === $type ? 'themes' : 'plugins';

		return Remote::get(
			self::url( $type, $username ),
			$ttl,
			function ( $body ) use ( $type ) {
				return self::parse( $body, $type );
			}
		);
	}

	/**
	 * Parse a directory response.
	 *
	 * @param string $body Response body.
	 * @param string $type 'plugins' or 'themes'.
	 * @return array|null
	 */
	public static function parse( $body, $type ) {
		$data = json_decode( $body, true );

		if ( ! isset( $data[ $type ] ) || ! is_array( $data[ $type ] ) ) {
			return null;
		}

		$items = [];
		foreach ( $data[ $type ] as $item ) {
			if ( empty( $item['slug'] ) || empty( $item['name'] ) ) {
				continue;
			}

			$image = '';
			if ( 'themes' === $type ) {
				$image = isset( $item['screenshot_url'] ) ? (string) $item['screenshot_url'] : '';
			} elseif ( ! empty( $item['icons'] ) && is_array( $item['icons'] ) ) {
				foreach ( [ 'svg', '2x', '1x', 'default' ] as $size ) {
					if ( ! empty( $item['icons'][ $size ] ) ) {
						$image = (string) $item['icons'][ $size ];
						break;
					}
				}
			}

			$summary = isset( $item['short_description'] ) ? $item['short_description'] : ( isset( $item['description'] ) ? $item['description'] : '' );

			$items[] = [
				'name'        => html_entity_decode( wp_strip_all_tags( (string) $item['name'] ), ENT_QUOTES, 'UTF-8' ),
				'slug'        => sanitize_title( (string) $item['slug'] ),
				'link'        => 'https://wordpress.org/' . $type . '/' . sanitize_title( (string) $item['slug'] ) . '/',
				// The theme API returns protocol-relative screenshot URLs.
				'image'       => 0 === strpos( $image, '//' ) ? 'https:' . $image : $image,
				'description' => html_entity_decode( wp_strip_all_tags( (string) $summary ), ENT_QUOTES, 'UTF-8' ),
				'installs'    => isset( $item['active_installs'] ) ? (int) $item['active_installs'] : 0,
				'downloads'   => isset( $item['downloaded'] ) ? (int) $item['downloaded'] : 0,
				// The API rates out of 100.
				'rating'      => ! empty( $item['num_ratings'] ) && isset( $item['rating'] ) ? round( (int) $item['rating'] / 20, 1 ) : 0,
			];
		}

		usort(
			$items,
			function ( $a, $b ) {
				return [ $b['installs'], $b['downloads'] ] <=> [ $a['installs'], $a['downloads'] ];
			}
		);

		return [ 'items' => $items ];
	}
}
