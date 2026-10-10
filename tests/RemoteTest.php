<?php
namespace Nhrcc\CoreContributions\Tests;

use Nhrcc\CoreContributions\Remote;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * A service that fails is not asked again on every page view, and the GitHub
 * token goes to GitHub only.
 */
class RemoteTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
		WP_Mock::userFunction( '__' )->andReturnArg( 0 );
		WP_Mock::userFunction( 'is_wp_error' )->andReturnUsing(
			function ( $thing ) {
				return $thing instanceof \WP_Error;
			}
		);
		WP_Mock::userFunction( 'get_option' )->with( 'nhrcc_settings', [] )->andReturn( [] );
		WP_Mock::userFunction( 'get_option' )->with( 'nhrcc_github_token', '' )->andReturn( 'secret' );
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	private function parser() {
		return function ( $body ) {
			return 'good' === $body ? [ 'ok' => true ] : null;
		};
	}

	public function testAFailureIsCachedBrieflyAndReturnedAsAnError() {
		WP_Mock::userFunction( 'get_transient' )->once()->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->once()->andReturn( [] );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 403 );
		WP_Mock::userFunction( 'set_transient' )->once()->with( WP_Mock\Functions::type( 'string' ), [ 'nhrcc_error' => 1 ], Remote::FAILURE_TTL );

		$this->assertInstanceOf( \WP_Error::class, Remote::get( 'https://api.github.com/search/commits?q=x', 3600, $this->parser() ) );
	}

	public function testACachedFailureMakesNoRequest() {
		WP_Mock::userFunction( 'get_transient' )->once()->andReturn( [ 'nhrcc_error' => 1 ] );
		WP_Mock::userFunction( 'wp_remote_get' )->never();

		$this->assertInstanceOf( \WP_Error::class, Remote::get( 'https://api.github.com/search/commits?q=x', 3600, $this->parser() ) );
	}

	public function testAGoodAnswerIsCachedForItsLifetimeAndTheTokenGoesToGitHub() {
		WP_Mock::userFunction( 'get_transient' )->once()->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->once()->andReturnUsing(
			function ( $url, $args ) {
				$this->assertSame( 'Bearer secret', $args['headers']['Authorization'] );
				return [];
			}
		);
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 200 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( 'good' );
		WP_Mock::userFunction( 'set_transient' )->once()->with( WP_Mock\Functions::type( 'string' ), [ 'ok' => true ], 3600 );

		$this->assertSame( [ 'ok' => true ], Remote::get( 'https://api.github.com/search/commits?q=x', 3600, $this->parser() ) );
	}

	public function testTheTokenIsNeverSentToAnotherHostAndZeroLifetimeIsNotCached() {
		WP_Mock::userFunction( 'get_transient' )->once()->andReturn( false );
		WP_Mock::userFunction( 'wp_remote_get' )->once()->andReturnUsing(
			function ( $url, $args ) {
				$this->assertArrayNotHasKey( 'Authorization', $args['headers'] );
				return [];
			}
		);
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' )->andReturn( 200 );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' )->andReturn( 'good' );
		WP_Mock::userFunction( 'set_transient' )->never();

		$this->assertSame( [ 'ok' => true ], Remote::get( 'https://api.wordpress.org/core/credits/1.1/?version=6.8', 0, $this->parser() ) );
	}
}
