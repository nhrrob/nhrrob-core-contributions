<?php
/**
 * Plugin Name: NHR Core Contributions
 * Plugin URI: https://wordpress.org/plugins/nhrrob-core-contributions/
 * Description: Show a WordPress.org contributor's core, Gutenberg, plugin, theme and team contributions with blocks.
 * Author: Nazmul Hasan Robin
 * Author URI: https://profiles.wordpress.org/nhrrob/
 * Version: 2.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: nhrrob-core-contributions
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package Nhrcc\CoreContributions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class Nhrcc_Core_Contributions {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const VERSION = '2.0.0';

	/**
	 * Class constructor
	 */
	private function __construct() {
		define( 'NHRCC_VERSION', self::VERSION );
		define( 'NHRCC_FILE', __FILE__ );
		define( 'NHRCC_PATH', __DIR__ );
		define( 'NHRCC_URL', plugins_url( '', NHRCC_FILE ) );

		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
	}

	/**
	 * Initialize a singleton instance
	 *
	 * @return \Nhrcc_Core_Contributions
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {
		( new Nhrcc\CoreContributions\Settings() )->init();
		( new Nhrcc\CoreContributions\Sync() )->init();
		( new Nhrcc\CoreContributions\Blocks() )->init();
		( new Nhrcc\CoreContributions\Abilities() )->init();

		if ( is_admin() ) {
			( new Nhrcc\CoreContributions\Admin() )->init();
		}
	}

	/**
	 * Stop the background sync on deactivation
	 *
	 * @return void
	 */
	public function deactivate() {
		wp_unschedule_hook( Nhrcc\CoreContributions\Sync::HOOK );
	}
}

/**
 * Initializes the main plugin
 *
 * @return \Nhrcc_Core_Contributions
 */
function nhrcc_core_contributions() {
	return Nhrcc_Core_Contributions::init();
}

// Call the plugin.
nhrcc_core_contributions();
