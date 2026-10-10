<?php
/**
 * Commits that credit a WordPress.org username, from GitHub commit search.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions\Sources;

use Nhrcc\CoreContributions\Remote;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Commits Class
 */
class Commits {

	/**
	 * GitHub search never returns more than its first 1,000 results.
	 */
	const MAX_RESULTS = 1000;

	const REPOS = [
		'core'      => 'WordPress/wordpress-develop',
		'gutenberg' => 'WordPress/gutenberg',
	];

	/**
	 * Build the search URL.
	 *
	 * Core commits name contributors in a "Props" line; Gutenberg commits carry
	 * a "Co-authored-by: … <username@git.wordpress.org>" trailer per contributor.
	 * The username is quoted so one with a dot or hyphen is matched whole.
	 *
	 * @param string $source   'core' or 'gutenberg'.
	 * @param string $username WordPress.org username.
	 * @param int    $page     Page, from 1.
	 * @param int    $per_page Results per page, up to 100.
	 * @param string $order    'desc' or 'asc' by commit date.
	 * @param int    $year     Limit to one year; 0 for all.
	 * @return string
	 */
	public static function url( $source, $username, $page = 1, $per_page = 10, $order = 'desc', $year = 0 ) {
		$source = isset( self::REPOS[ $source ] ) ? $source : 'core';
		$query  = 'repo:' . self::REPOS[ $source ] . ' ' . ( 'core' === $source ? 'props "' . $username . '"' : '"' . $username . '@git.wordpress.org"' );

		if ( $year > 0 ) {
			$query .= sprintf( ' committer-date:%1$d-01-01..%1$d-12-31', $year );
		}

		return 'https://api.github.com/search/commits?' . http_build_query(
			[
				'q'        => $query,
				'sort'     => 'committer-date',
				'order'    => 'asc' === $order ? 'asc' : 'desc',
				'per_page' => min( 100, max( 1, (int) $per_page ) ),
				'page'     => max( 1, (int) $page ),
			]
		);
	}

	/**
	 * One page of commits and the total.
	 *
	 * @param string $source   'core' or 'gutenberg'.
	 * @param string $username WordPress.org username.
	 * @param int    $page     Page, from 1.
	 * @param int    $per_page Results per page.
	 * @param int    $ttl      Cache lifetime in seconds; 0 to not cache.
	 * @param string $order    'desc' or 'asc'.
	 * @param int    $year     Limit to one year; 0 for all.
	 * @return array|\WP_Error [ 'total' => int, 'items' => array ]
	 */
	public static function page( $source, $username, $page, $per_page, $ttl, $order = 'desc', $year = 0 ) {
		return Remote::get(
			self::url( $source, $username, $page, $per_page, $order, $year ),
			$ttl,
			function ( $body ) use ( $source ) {
				return self::parse( $body, $source );
			}
		);
	}

	/**
	 * How many pages can actually be requested.
	 *
	 * @param int $total    Total results.
	 * @param int $per_page Results per page.
	 * @return int
	 */
	public static function pages( $total, $per_page ) {
		return (int) ceil( min( (int) $total, self::MAX_RESULTS ) / max( 1, (int) $per_page ) );
	}

	/**
	 * Parse a search response.
	 *
	 * @param string $body   Response body.
	 * @param string $source 'core' or 'gutenberg'.
	 * @return array|null
	 */
	public static function parse( $body, $source ) {
		$data = json_decode( $body, true );

		if ( ! isset( $data['total_count'], $data['items'] ) || ! is_array( $data['items'] ) ) {
			return null;
		}

		$items = [];
		foreach ( $data['items'] as $item ) {
			$items[] = self::parse_commit( (array) $item, $source );
		}

		return [
			'total' => (int) $data['total_count'],
			'items' => $items,
		];
	}

	/**
	 * Reduce a commit to what the plugin shows.
	 *
	 * @param array  $item   Commit from the search response.
	 * @param string $source 'core' or 'gutenberg'.
	 * @return array
	 */
	public static function parse_commit( array $item, $source ) {
		$message = isset( $item['commit']['message'] ) ? (string) $item['commit']['message'] : '';
		$title   = trim( (string) strtok( $message, "\n" ) );
		$time    = isset( $item['commit']['committer']['date'] ) ? strtotime( (string) $item['commit']['committer']['date'] ) : false;
		$commit  = [
			'link'        => isset( $item['html_url'] ) ? (string) $item['html_url'] : '',
			'changeset'   => 0,
			'description' => $title,
			'component'   => '',
			'ticket'      => 0,
			'date'        => $time ? gmdate( 'Y-m-d\TH:i:s\Z', $time ) : '',
		];

		if ( 'gutenberg' === $source ) {
			// Squash merges end the title with the pull request number.
			if ( preg_match( '/\s*\(#(\d+)\)$/', $title, $match ) ) {
				$commit['ticket']      = (int) $match[1];
				$commit['link']        = 'https://github.com/' . self::REPOS['gutenberg'] . '/pull/' . $commit['ticket'];
				$commit['description'] = trim( substr( $title, 0, -strlen( $match[0] ) ) );
			}
			return $commit;
		}

		// Every commit in the core mirror carries its Trac changeset number.
		if ( preg_match( '/git-svn-id:\s*\S+@(\d+)/', $message, $match ) ) {
			$commit['changeset'] = (int) $match[1];
			$commit['link']      = 'https://core.trac.wordpress.org/changeset/' . $commit['changeset'];
		}

		if ( preg_match( '/(?:Fixes|See) #(\d+)/i', $message, $match ) ) {
			$commit['ticket'] = (int) $match[1];
		}

		// "Component: What changed."
		if ( preg_match( '/^([A-Z][A-Za-z0-9 \/&.,-]{1,40}): (.+)$/', $title, $match ) ) {
			$commit['component']   = $match[1];
			$commit['description'] = $match[2];
		}

		return $commit;
	}
}
