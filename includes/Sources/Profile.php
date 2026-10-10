<?php
/**
 * A contributor's public WordPress.org profile: name, avatar and badges.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions\Sources;

use Nhrcc\CoreContributions\Remote;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Profile Class
 *
 * WordPress.org has no profile API, so this reads the profile page. Anything
 * the page stops providing is simply left out.
 */
class Profile {

	/**
	 * Profile URL of a username.
	 *
	 * @param string $username WordPress.org username.
	 * @return string
	 */
	public static function url( $username ) {
		return 'https://profiles.wordpress.org/' . rawurlencode( $username ) . '/';
	}

	/**
	 * The profile, cached for a day.
	 *
	 * @param string $username WordPress.org username.
	 * @return array|\WP_Error [ 'name', 'avatar', 'since', 'badges' => [ [ 'slug', 'label', 'since' ] ] ]
	 */
	public static function get( $username ) {
		return Remote::get( self::url( $username ), 86400, [ __CLASS__, 'parse' ] );
	}

	/**
	 * Parse the profile page.
	 *
	 * @param string $html Page HTML.
	 * @return array|null
	 */
	public static function parse( $html ) {
		if ( ! preg_match( '/<title>\s*(.*?)\s*\(@[^)]+\)/s', $html, $name ) ) {
			return null;
		}

		$profile = [
			'name'   => html_entity_decode( $name[1], ENT_QUOTES, 'UTF-8' ),
			'avatar' => '',
			'since'  => '',
			'badges' => [],
		];

		if ( preg_match( '#https://secure\.gravatar\.com/avatar/[a-f0-9]+#', $html, $avatar ) ) {
			$profile['avatar'] = $avatar[0];
		}

		if ( preg_match( '#id="user-member-since">.*?<strong>(.*?)</strong>#s', $html, $since ) ) {
			$profile['since'] = html_entity_decode( wp_strip_all_tags( $since[1] ), ENT_QUOTES, 'UTF-8' );
		}

		preg_match_all( '#<span class="medal badge-([a-z0-9-]+)" title="([^"]*)">.*?<span class="mn">([^<]*)</span>#s', $html, $badges, PREG_SET_ORDER );
		foreach ( $badges as $badge ) {
			$title                          = html_entity_decode( $badge[2], ENT_QUOTES, 'UTF-8' );
			$parts                          = explode( ' since ', $title, 2 );
			$profile['badges'][ $badge[1] ] = [
				'slug'  => $badge[1],
				'label' => html_entity_decode( trim( $badge[3] ), ENT_QUOTES, 'UTF-8' ),
				'since' => isset( $parts[1] ) ? trim( $parts[1] ) : '',
			];
		}
		$profile['badges'] = array_values( $profile['badges'] );

		return $profile;
	}
}
