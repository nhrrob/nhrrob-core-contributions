<?php
/**
 * Background gathering of a contributor's summary.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions;

use Nhrcc\CoreContributions\Sources\Commits;
use Nhrcc\CoreContributions\Sources\Credits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sync Class
 *
 * Totals per year, top components and credited releases take many requests, so
 * they are gathered by WP-Cron a few requests at a time and stored. A page view
 * only ever reads what is stored.
 */
class Sync {

	const OPTION = 'nhrcc_summaries';
	const HOOK   = 'nhrcc_sync';

	/**
	 * GitHub allows 10 unauthenticated searches a minute; one run stays well below.
	 */
	const GITHUB_PER_RUN = 5;
	const WPORG_PER_RUN  = 20;

	/**
	 * Seconds after which a run starts no further request, to stay inside PHP's time limit.
	 */
	const RUN_SECONDS = 15;

	const MAX_USERS = 20;
	const MAX_AGE   = 86400;

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( self::HOOK, [ $this, 'run' ] );
	}

	/**
	 * What is stored for a username, without scheduling anything.
	 *
	 * @param string $username WordPress.org username.
	 * @return array
	 */
	public static function stored( $username ) {
		$all = (array) get_option( self::OPTION, [] );

		return isset( $all[ $username ] ) && is_array( $all[ $username ] ) ? $all[ $username ] : [];
	}

	/**
	 * The finished summary of a username, or an empty array while the first one
	 * is still being gathered. Schedules a run when there is none or it is old.
	 *
	 * @param string $username WordPress.org username.
	 * @return array
	 */
	public static function summary( $username ) {
		$summary = self::stored( $username );
		$synced  = isset( $summary['synced'] ) ? (int) $summary['synced'] : 0;

		if ( time() - $synced > self::MAX_AGE ) {
			self::schedule( $username, 0 );
		}

		return $synced ? $summary : [];
	}

	/**
	 * Queue a run unless one is queued already.
	 *
	 * @param string $username WordPress.org username.
	 * @param int    $delay    Seconds from now.
	 * @return void
	 */
	public static function schedule( $username, $delay ) {
		if ( ! wp_next_scheduled( self::HOOK, [ $username ] ) ) {
			wp_schedule_single_event( time() + $delay, self::HOOK, [ $username ] );
		}
	}

	/**
	 * Do one run for a username and queue the next one if work remains.
	 *
	 * @param string $username WordPress.org username.
	 * @return void
	 */
	public function run( $username ) {
		$username = Settings::username( $username );
		if ( '' === $username ) {
			return;
		}

		$summary = self::stored( $username );

		// A leftover event for a summary that is already fresh has nothing to do.
		if ( ! isset( $summary['job'] ) && isset( $summary['synced'] ) && time() - (int) $summary['synced'] <= self::MAX_AGE ) {
			return;
		}

		$job     = isset( $summary['job'] ) ? $summary['job'] : self::new_job( $summary );
		$budget  = [
			'github' => self::GITHUB_PER_RUN,
			'wporg'  => self::WPORG_PER_RUN,
		];
		$delay   = 60;
		$started = time();

		while ( 'done' !== $job['stage'] ) {
			$service = 'releases' === $job['stage'] ? 'wporg' : 'github';
			if ( $budget[ $service ]-- <= 0 || time() - $started > self::RUN_SECONDS ) {
				break;
			}

			$next = $this->step( $username, $job );
			if ( is_wp_error( $next ) ) {
				// Remote caches the failure for FAILURE_TTL; come back after it.
				$delay = 2 * Remote::FAILURE_TTL;
				break;
			}
			$job = $next;
		}

		if ( 'done' === $job['stage'] ) {
			unset( $job['stage'], $job['page'], $job['year'] );
			arsort( $job['components'] );
			$job['components'] = array_slice( $job['components'], 0, 10, true );
			ksort( $job['years'] );
			$job['synced'] = time();
			$summary       = $job;
		} else {
			$summary['job'] = $job;
			self::schedule( $username, $delay );
		}

		$this->store( $username, $summary );
	}

	/**
	 * A fresh job. Releases already checked are kept, except the newest two,
	 * whose credits can still change.
	 *
	 * @param array $summary The previous summary, if any.
	 * @return array
	 */
	public static function new_job( array $summary ) {
		return [
			'stage'      => 'commits',
			'page'       => 1,
			'year'       => 0,
			'total'      => 0,
			'gutenberg'  => 0,
			'first'      => '',
			'latest'     => '',
			'years'      => [],
			'components' => [],
			'releases'   => isset( $summary['releases'] ) ? (array) $summary['releases'] : [],
			'checked'    => isset( $summary['checked'] ) ? array_slice( (array) $summary['checked'], 0, -2 ) : [],
		];
	}

	/**
	 * Make one request and fold its answer into the job.
	 *
	 * @param string $username WordPress.org username.
	 * @param array  $job      Job state.
	 * @return array|\WP_Error The new job state.
	 */
	public function step( $username, array $job ) {
		switch ( $job['stage'] ) {
			case 'commits':
				$data = Commits::page( 'core', $username, $job['page'], 100, 0 );
				if ( is_wp_error( $data ) ) {
					return $data;
				}
				return self::add_commits( $job, $data );

			case 'first':
				// More commits than GitHub lets us page through: find where they start.
				$data = Commits::page( 'core', $username, 1, 1, 0, 'asc' );
				if ( is_wp_error( $data ) ) {
					return $data;
				}
				$job['first'] = ! empty( $data['items'][0]['date'] ) ? $data['items'][0]['date'] : $job['first'];
				$job['year']  = (int) substr( $job['first'], 0, 4 );
				$job['years'] = [];
				$job['stage'] = $job['year'] ? 'years' : 'gutenberg';
				return $job;

			case 'years':
				$data = Commits::page( 'core', $username, 1, 1, 0, 'desc', $job['year'] );
				if ( is_wp_error( $data ) ) {
					return $data;
				}
				if ( $data['total'] ) {
					$job['years'][ $job['year'] ] = $data['total'];
				}
				if ( ++$job['year'] > (int) gmdate( 'Y' ) ) {
					$job['stage'] = 'gutenberg';
				}
				return $job;

			case 'gutenberg':
				$data = Commits::page( 'gutenberg', $username, 1, 1, 0 );
				if ( is_wp_error( $data ) ) {
					return $data;
				}
				$job['gutenberg'] = $data['total'];
				$job['stage']     = 'releases';
				return $job;

			default:
				$majors = Credits::majors();
				if ( is_wp_error( $majors ) ) {
					return $majors;
				}
				$todo = array_values( array_diff( $majors['versions'], $job['checked'] ) );
				if ( ! $todo ) {
					$job['releases'] = array_values( array_intersect( $majors['versions'], $job['releases'] ) );
					$job['stage']    = 'done';
					return $job;
				}
				$data = Credits::credited( $todo[0], $username );
				if ( is_wp_error( $data ) ) {
					return $data;
				}
				$job['checked'][] = $todo[0];
				if ( $data['credited'] ) {
					$job['releases'][] = $todo[0];
				}
				return $job;
		}
	}

	/**
	 * Fold one page of core commits (newest first) into the job.
	 *
	 * @param array $job  Job state.
	 * @param array $data [ 'total' => int, 'items' => array ].
	 * @return array
	 */
	public static function add_commits( array $job, array $data ) {
		$job['total'] = $data['total'];

		foreach ( $data['items'] as $item ) {
			if ( '' === $item['date'] ) {
				continue;
			}
			$year                  = (int) substr( $item['date'], 0, 4 );
			$job['years'][ $year ] = isset( $job['years'][ $year ] ) ? $job['years'][ $year ] + 1 : 1;
			$job['latest']         = '' === $job['latest'] ? $item['date'] : $job['latest'];
			$job['first']          = $item['date'];

			if ( '' !== $item['component'] ) {
				$job['components'][ $item['component'] ] = isset( $job['components'][ $item['component'] ] ) ? $job['components'][ $item['component'] ] + 1 : 1;
			}
		}

		if ( $job['page'] < Commits::pages( $data['total'], 100 ) && $data['items'] ) {
			++$job['page'];
		} else {
			$job['stage'] = $data['total'] > Commits::MAX_RESULTS ? 'first' : 'gutenberg';
		}

		return $job;
	}

	/**
	 * Store a summary, keeping at most MAX_USERS of them.
	 *
	 * @param string $username WordPress.org username.
	 * @param array  $summary  Summary.
	 * @return void
	 */
	private function store( $username, array $summary ) {
		$all = (array) get_option( self::OPTION, [] );
		unset( $all[ $username ] );
		$all[ $username ] = $summary;
		$settings         = Settings::get();

		update_option( self::OPTION, self::trim( $all, $settings['username'] ), false );
	}

	/**
	 * Keep the newest MAX_USERS summaries. The default username's is never dropped,
	 * so looking up other usernames cannot empty the site's own blocks.
	 *
	 * @param array  $all      Summaries by username, oldest first.
	 * @param string $username The default username.
	 * @return array
	 */
	public static function trim( array $all, $username ) {
		$keep = isset( $all[ $username ] ) ? [ $username => $all[ $username ] ] : [];
		unset( $all[ $username ] );

		return $keep + array_slice( $all, count( $keep ) - self::MAX_USERS, null, true );
	}
}
