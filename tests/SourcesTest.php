<?php
namespace Nhrcc\CoreContributions\Tests;

use Nhrcc\CoreContributions\Sources\Credits;
use Nhrcc\CoreContributions\Sources\Directory;
use Nhrcc\CoreContributions\Sources\Profile;
use PHPUnit\Framework\TestCase;
use WP_Mock;

/**
 * Parsing of the WordPress.org responses.
 */
class SourcesTest extends TestCase {

	protected function setUp(): void {
		WP_Mock::setUp();
		WP_Mock::userFunction( 'wp_strip_all_tags' )->andReturnUsing( 'strip_tags' );
		WP_Mock::userFunction( 'sanitize_title' )->andReturnUsing(
			function ( $title ) {
				return preg_replace( '/[^a-z0-9-]/', '', strtolower( $title ) );
			}
		);
	}

	protected function tearDown(): void {
		WP_Mock::tearDown();
	}

	public function testMajorsStartWhereTheCreditsApiDoesAndAreSorted() {
		$majors = Credits::parse_majors( '{"6.10.1":"latest","3.1.4":"insecure","3.2":"insecure","6.8.2":"outdated","6.8":"outdated"}' );

		$this->assertSame( [ '3.2', '6.8', '6.10' ], $majors['versions'] );
		$this->assertNull( Credits::parse_majors( '[]' ) );
	}

	public function testCreditsAreMatchedByUsernameInPeopleGroupsOnly() {
		$body = '{"groups":{"core-developers":{"type":"titles","data":{"matt":["Matt","h","matt","Lead"]}},"props":{"type":"list","data":{"NHRrob":"Nazmul"}},"libraries":{"type":"libraries","data":{"jquery":["jQuery","https://jquery.com"]}}}}';

		$this->assertTrue( Credits::parse_credited( $body, 'nhrrob' )['credited'] );
		$this->assertTrue( Credits::parse_credited( $body, 'matt' )['credited'] );
		$this->assertFalse( Credits::parse_credited( $body, 'jquery' )['credited'] );
		$this->assertNull( Credits::parse_credited( 'Bad request.', 'nhrrob' ) );
	}

	public function testProfilePageIsReducedToNameAvatarAndBadges() {
		$html = "<title>Nazmul Hasan Robin (@nhrrob) &#8211; WordPress user profile | WordPress.org</title>"
			. "<img src='https://secure.gravatar.com/avatar/df1895ff?s=224&#038;d=mm' class='avatar'/>"
			. '<span class="medal badge-code" title="Core Contributor · since April 2024"><span class="mi"></span> <span class="mn">Core Contributor</span></span>'
			. '<span class="medal badge-plugins" title="Plugin Developer"><span class="mi"></span> <span class="mn">Plugin Developer</span></span>'
			. '<li id="user-member-since"><span>Member Since:</span><strong>November 28th, 2014</strong></li>';

		$this->assertSame(
			[
				'name'   => 'Nazmul Hasan Robin',
				'avatar' => 'https://secure.gravatar.com/avatar/df1895ff',
				'since'  => 'November 28th, 2014',
				'badges' => [
					[
						'slug'  => 'code',
						'label' => 'Core Contributor',
						'since' => 'April 2024',
					],
					[
						'slug'  => 'plugins',
						'label' => 'Plugin Developer',
						'since' => '',
					],
				],
			],
			Profile::parse( $html )
		);
	}

	public function testAPageThatIsNotAProfileIsRejected() {
		$this->assertNull( Profile::parse( '<title>Just a moment...</title>' ) );
	}

	public function testPluginsAreSortedByInstallsAndStrippedOfMarkup() {
		$body = '{"plugins":[{"name":"Small &amp; <b>Tidy</b>","slug":"small","short_description":"<i>Hi</i>","active_installs":10,"downloaded":50,"rating":90,"num_ratings":2,"icons":{"1x":"https://ps.w.org/small/icon.png"}},{"name":"Big","slug":"big","active_installs":1000,"rating":0,"num_ratings":0},{"name":"No slug"}]}';
		$items = Directory::parse( $body, 'plugins' )['items'];

		$this->assertSame( [ 'big', 'small' ], array_column( $items, 'slug' ) );
		$this->assertSame( 'Small & Tidy', $items[1]['name'] );
		$this->assertSame( 'Hi', $items[1]['description'] );
		$this->assertSame( 'https://wordpress.org/plugins/small/', $items[1]['link'] );
		$this->assertSame( 'https://ps.w.org/small/icon.png', $items[1]['image'] );
		$this->assertSame( 4.5, $items[1]['rating'] );
		$this->assertSame( 0, $items[0]['rating'] );
	}

	public function testThemeScreenshotsGetAScheme() {
		$items = Directory::parse( '{"themes":[{"name":"T","slug":"t","screenshot_url":"//ts.w.org/t.png","description":"A theme"}]}', 'themes' )['items'];

		$this->assertSame( 'https://ts.w.org/t.png', $items[0]['image'] );
		$this->assertSame( 'https://wordpress.org/themes/t/', $items[0]['link'] );
		$this->assertNull( Directory::parse( '{"plugins":[]}', 'themes' ) );
	}
}
