<?php
namespace Nhrcc\CoreContributions\Tests;

use Nhrcc\CoreContributions\Settings;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * Settings never leave their allowed ranges, whatever is stored or posted.
 */
class SettingsTest extends TestCase {

	public function testDefaults() {
		$this->assertSame(
			[
				'username'      => '',
				'postsPerPage'  => 10,
				'cacheDuration' => 43200,
				'cacheVersion'  => 1,
			],
			Settings::sanitize( [] )
		);
	}

	public function testValuesAreClampedToWhatTheSourcesAccept() {
		$settings = Settings::sanitize(
			[
				'username'      => ' NHRrob<script> ',
				'postsPerPage'  => 5000,
				'cacheDuration' => 1,
				'cacheVersion'  => -3,
				'preset'        => 'modern',
			]
		);

		$this->assertSame( 'nhrrobscript', $settings['username'] );
		$this->assertSame( 100, $settings['postsPerPage'] );
		$this->assertSame( 3600, $settings['cacheDuration'] );
		$this->assertSame( 1, $settings['cacheVersion'] );
		$this->assertArrayNotHasKey( 'preset', $settings );
		$this->assertSame( 1, Settings::sanitize( [ 'postsPerPage' => 0 ] )['postsPerPage'] );
	}

	public function testUsernameKeepsOnlyProfileSlugCharacters() {
		$this->assertSame( 'john.doe-1_x', Settings::username( 'John.Doe-1_x' ) );
		$this->assertSame( '', Settings::username( [ 'nhrrob' ] ) );
		$this->assertSame( 'a..b', Settings::username( 'a/../b?' ) );
		$this->assertSame( '', Settings::username( '../..' ) );
		$this->assertSame( 60, strlen( Settings::username( str_repeat( 'a', 200 ) ) ) );
	}

	public function testAPartialUpdateKeepsTheOtherSettings() {
		WP_Mock::setUp();
		WP_Mock::userFunction( 'get_option' )->with( Settings::OPTION, [] )->andReturn(
			[
				'username'     => 'nhrrob',
				'postsPerPage' => 25,
				'cacheVersion' => 4,
			]
		);
		WP_Mock::userFunction( 'get_option' )->with( Settings::TOKEN_OPTION, '' )->andReturn( '' );
		WP_Mock::userFunction( 'get_option' )->with( 'nhrcc_summaries', [] )->andReturn( [] );
		WP_Mock::userFunction( 'update_option' )->once()->with(
			Settings::OPTION,
			[
				'username'      => 'nhrrob',
				'postsPerPage'  => 50,
				'cacheDuration' => 43200,
				'cacheVersion'  => 4,
			]
		);

		$request = \Mockery::mock( 'WP_REST_Request' );
		$request->shouldReceive( 'get_params' )->andReturn(
			[
				'postsPerPage' => 50,
				'cacheVersion' => 99,
			]
		);

		( new Settings() )->rest_update( $request );
		WP_Mock::tearDown();
		$this->addToAssertionCount( 1 );
	}
}
