<?php
/**
 * The settings screen under Tools.
 *
 * @package Nhrcc\CoreContributions
 */

namespace Nhrcc\CoreContributions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Class
 */
class Admin {

	const SLUG = 'nhrcc-core-contributions';

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'menu' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( NHRCC_FILE ), [ $this, 'action_links' ] );
	}

	/**
	 * Add the screen.
	 *
	 * @return void
	 */
	public function menu() {
		$hook = add_submenu_page( 'tools.php', __( 'Core Contributions', 'nhrrob-core-contributions' ), __( 'Core Contributions', 'nhrrob-core-contributions' ), 'manage_options', self::SLUG, [ $this, 'page' ] );

		add_action( 'load-' . $hook, [ $this, 'load' ] );
	}

	/**
	 * Enqueue on this screen only.
	 *
	 * @return void
	 */
	public function load() {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
	}

	/**
	 * Enqueue the settings app and the block styles its preview uses.
	 *
	 * @return void
	 */
	public function enqueue() {
		$build = NHRCC_PATH . '/assets/dashboard/build';
		if ( ! file_exists( $build . '/index.asset.php' ) ) {
			return;
		}
		$asset = require $build . '/index.asset.php';

		wp_enqueue_script( 'nhrcc-dashboard', NHRCC_URL . '/assets/dashboard/build/index.js', $asset['dependencies'], $asset['version'], true );
		wp_set_script_translations( 'nhrcc-dashboard', 'nhrrob-core-contributions' );
		wp_enqueue_style( 'nhrcc-dashboard', NHRCC_URL . '/assets/dashboard/build/style-index.css', [ 'wp-components', 'nhrcc-blocks' ], $asset['version'] );
		wp_style_add_data( 'nhrcc-dashboard', 'rtl', 'replace' );
	}

	/**
	 * Print the mount point.
	 *
	 * @return void
	 */
	public function page() {
		echo '<div class="wrap"><h1>' . esc_html__( 'Core Contributions', 'nhrrob-core-contributions' ) . '</h1><div id="nhrcc-admin-settings"></div></div>';
	}

	/**
	 * Add a Settings link on the Plugins screen.
	 *
	 * @param array $links Action links.
	 * @return array
	 */
	public function action_links( $links ) {
		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'tools.php?page=' . self::SLUG ) ), esc_html__( 'Settings', 'nhrrob-core-contributions' ) );

		return $links;
	}
}
