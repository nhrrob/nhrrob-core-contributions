<?php
namespace Nhrcc\CoreContributions\Tests;

use Nhrcc\CoreContributions\Sources\Commits;
use PHPUnit\Framework\TestCase;

/**
 * What a commit becomes, and how far GitHub lets a list page.
 */
class CommitsTest extends TestCase {

	const CORE_MESSAGE = "Formatting: Check the result of `preg_split()`.\n\nProps audrasjb, nhrrob.\nSee #51019.\n\ngit-svn-id: https://develop.svn.wordpress.org/trunk@59515 602fd350-edb4-49c9-b593-d223f7449a82";

	private function item( $message, $url = 'https://github.com/x/commit/abc' ) {
		return [
			'html_url' => $url,
			'commit'   => [
				'message'   => $message,
				'committer' => [ 'date' => '2024-12-14T23:51:09.000Z' ],
			],
		];
	}

	public function testCoreCommitIsSplitIntoComponentTitleTicketAndChangeset() {
		$this->assertSame(
			[
				'link'        => 'https://core.trac.wordpress.org/changeset/59515',
				'changeset'   => 59515,
				'description' => 'Check the result of `preg_split()`.',
				'component'   => 'Formatting',
				'ticket'      => 51019,
				'date'        => '2024-12-14T23:51:09Z',
			],
			Commits::parse_commit( $this->item( self::CORE_MESSAGE ), 'core' )
		);
	}

	public function testCoreCommitWithoutChangesetOrComponentKeepsItsGitHubLink() {
		$commit = Commits::parse_commit( $this->item( 'Bump the version' ), 'core' );

		$this->assertSame( 'https://github.com/x/commit/abc', $commit['link'] );
		$this->assertSame( 0, $commit['changeset'] );
		$this->assertSame( '', $commit['component'] );
		$this->assertSame( 'Bump the version', $commit['description'] );
	}

	public function testAComponentListWithCommasIsStillAComponent() {
		$commit = Commits::parse_commit( $this->item( 'Options, Meta APIs: Correct `$count` argument.' ), 'core' );

		$this->assertSame( 'Options, Meta APIs', $commit['component'] );
		$this->assertSame( 'Correct `$count` argument.', $commit['description'] );
	}

	public function testGutenbergCommitLinksToItsPullRequest() {
		$commit = Commits::parse_commit( $this->item( "feat: Enhance Connectors page (#77521)\n\nCo-authored-by: a <a@git.wordpress.org>" ), 'gutenberg' );

		$this->assertSame( 'https://github.com/WordPress/gutenberg/pull/77521', $commit['link'] );
		$this->assertSame( 77521, $commit['ticket'] );
		$this->assertSame( 'feat: Enhance Connectors page', $commit['description'] );
		$this->assertSame( '', $commit['component'] );
	}

	public function testAnUnusableResponseIsRejected() {
		$this->assertNull( Commits::parse( '{"message":"API rate limit exceeded"}', 'core' ) );
		$this->assertNull( Commits::parse( 'not json', 'core' ) );
		$this->assertSame(
			[
				'total' => 0,
				'items' => [],
			],
			Commits::parse( '{"total_count":0,"items":[]}', 'core' )
		);
	}

	public function testPagesStopAtTheThousandResultsGitHubReturns() {
		$this->assertSame( 1, Commits::pages( 6, 10 ) );
		$this->assertSame( 0, Commits::pages( 0, 10 ) );
		$this->assertSame( 100, Commits::pages( 2104, 10 ) );
		$this->assertSame( 10, Commits::pages( 2104, 100 ) );
	}

	public function testEachSourceSearchesItsOwnRepositoryAndCreditFormat() {
		parse_str( (string) parse_url( Commits::url( 'core', 'nhrrob', 2, 500 ), PHP_URL_QUERY ), $core );
		parse_str( (string) parse_url( Commits::url( 'gutenberg', 'nhrrob', 1, 1, 'asc', 2024 ), PHP_URL_QUERY ), $gutenberg );

		$this->assertSame( 'repo:WordPress/wordpress-develop props "nhrrob"', $core['q'] );
		$this->assertSame( '100', $core['per_page'] );
		$this->assertSame( '2', $core['page'] );
		$this->assertSame( 'repo:WordPress/gutenberg "nhrrob@git.wordpress.org" committer-date:2024-01-01..2024-12-31', $gutenberg['q'] );
		$this->assertSame( 'asc', $gutenberg['order'] );
	}
}
