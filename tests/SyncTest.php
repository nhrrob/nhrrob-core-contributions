<?php
namespace Nhrcc\CoreContributions\Tests;

use Nhrcc\CoreContributions\Sync;
use PHPUnit\Framework\TestCase;

/**
 * How pages of commits become the stored summary.
 */
class SyncTest extends TestCase {

	private function commit( $date, $component = '' ) {
		return [
			'date'      => $date,
			'component' => $component,
		];
	}

	public function testASinglePageIsCountedAndMovesOnToGutenberg() {
		$job = Sync::add_commits(
			Sync::new_job( [] ),
			[
				'total' => 3,
				'items' => [
					$this->commit( '2025-02-01T00:00:00Z', 'Media' ),
					$this->commit( '2024-12-14T00:00:00Z', 'Media' ),
					$this->commit( '2024-05-27T00:00:00Z' ),
				],
			]
		);

		$this->assertSame( 'gutenberg', $job['stage'] );
		$this->assertSame( 3, $job['total'] );
		$this->assertSame(
			[
				2025 => 1,
				2024 => 2,
			],
			$job['years']
		);
		$this->assertSame( [ 'Media' => 2 ], $job['components'] );
		$this->assertSame( '2025-02-01T00:00:00Z', $job['latest'] );
		$this->assertSame( '2024-05-27T00:00:00Z', $job['first'] );
	}

	public function testMorePagesAreRequestedUntilTheTotalIsCovered() {
		$job = Sync::add_commits(
			Sync::new_job( [] ),
			[
				'total' => 250,
				'items' => [ $this->commit( '2025-02-01T00:00:00Z' ) ],
			]
		);

		$this->assertSame( 'commits', $job['stage'] );
		$this->assertSame( 2, $job['page'] );
	}

	public function testBeyondAThousandCommitsYearsAreCountedOneByOne() {
		$job         = Sync::new_job( [] );
		$job['page'] = 10;
		$job         = Sync::add_commits(
			$job,
			[
				'total' => 2104,
				'items' => [ $this->commit( '2019-02-01T00:00:00Z' ) ],
			]
		);

		$this->assertSame( 'first', $job['stage'] );
	}

	public function testAnEmptyPageEndsPagingInsteadOfLooping() {
		$job = Sync::add_commits(
			Sync::new_job( [] ),
			[
				'total' => 250,
				'items' => [],
			]
		);

		$this->assertSame( 'gutenberg', $job['stage'] );
	}

	public function testTheDefaultUsernameIsNeverEvicted() {
		$all = [ 'owner' => [ 'synced' => 1 ] ];
		for ( $i = 1; $i <= Sync::MAX_USERS + 5; $i++ ) {
			$all[ 'user' . $i ] = [ 'synced' => 1 ];
		}

		$kept = Sync::trim( $all, 'owner' );

		$this->assertCount( Sync::MAX_USERS, $kept );
		$this->assertArrayHasKey( 'owner', $kept );
		$this->assertArrayNotHasKey( 'user6', $kept );
		$this->assertArrayHasKey( 'user7', $kept );
		$this->assertCount( Sync::MAX_USERS, Sync::trim( $all, 'nobody' ) );
	}

	public function testANewJobRechecksOnlyTheNewestTwoReleases() {
		$job = Sync::new_job(
			[
				'releases' => [ '6.5', '6.8' ],
				'checked'  => [ '6.5', '6.6', '6.7', '6.8' ],
			]
		);

		$this->assertSame( [ '6.5', '6.8' ], $job['releases'] );
		$this->assertSame( [ '6.5', '6.6' ], $job['checked'] );
	}
}
