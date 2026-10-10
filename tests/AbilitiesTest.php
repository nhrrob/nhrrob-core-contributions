<?php
namespace Nhrcc\CoreContributions\Tests;

use Nhrcc\CoreContributions\Abilities;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * What AI agents and MCP clients may do is a fixed, reviewed list: adding an
 * ability (or loosening one) must be a deliberate change to this test.
 */
class AbilitiesTest extends TestCase {

	const READ_ONLY = [
		'nhrcc/get-core-contributions',
		'nhrcc/get-gutenberg-contributions',
		'nhrcc/get-contribution-summary',
		'nhrcc/get-profile',
		'nhrcc/get-plugins-and-themes',
	];

	protected function setUp(): void {
		WP_Mock::setUp();
		WP_Mock::userFunction( '__' )->andReturnArg( 0 );
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function testTheAbilityListIsExactlyTheReviewedOne() {
		$this->assertSame( self::READ_ONLY, array_keys( ( new Abilities() )->definitions() ) );
	}

	public function testEveryAbilityIsReadOnlyGatedAndTakesAUsername() {
		$abilities = new Abilities();

		foreach ( $abilities->definitions() as $name => $args ) {
			$this->assertSame( [ $abilities, 'can_read' ], $args['permission_callback'], $name );
			$this->assertSame( Abilities::CATEGORY, $args['category'], $name );
			$this->assertIsCallable( $args['execute_callback'], $name );
			$this->assertTrue( $args['meta']['annotations']['readonly'], $name );
			$this->assertFalse( $args['meta']['annotations']['destructive'], $name );
			$this->assertSame( [ 'username' ], $args['input_schema']['required'], $name );
			$this->assertSame( '^[A-Za-z0-9._-]+$', $args['input_schema']['properties']['username']['pattern'], $name );
		}
	}

	public function testTheGateIsTheEditPostsCapability() {
		WP_Mock::userFunction( 'current_user_can' )->once()->with( 'edit_posts' )->andReturn( false );

		$this->assertFalse( ( new Abilities() )->can_read() );
	}
}
