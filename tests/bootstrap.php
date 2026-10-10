<?php
/**
 * PHPUnit bootstrap.
 *
 * Runs under the CLI test runner, never through WordPress. The plugin's files
 * exit without ABSPATH, so it is defined here before the autoloader loads them.
 *
 * @package Nhrcc\CoreContributions
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__ ) . '/vendor/autoload.php';

WP_Mock::bootstrap();

if ( ! class_exists( 'WP_Error' ) ) {
	/**
	 * Minimal WP_Error.
	 */
	class WP_Error {

		/**
		 * Error code.
		 *
		 * @var string
		 */
		public $code;

		/**
		 * Constructor.
		 *
		 * @param string $code    Code.
		 * @param string $message Message.
		 */
		public function __construct( $code = '', $message = '' ) {
			$this->code = $code;
		}
	}
}
