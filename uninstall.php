<?php
/**
 * Fired when the plugin is deleted: removes everything it stored.
 *
 * @package Nhrcc\CoreContributions
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Remove this plugin's options, transients and scheduled events from the current site.
 *
 * @return void
 */
function nhrcc_uninstall_site() {
	global $wpdb;

	// The last two were written by versions before 2.0.
	foreach ( [ 'nhrcc_settings', 'nhrcc_github_token', 'nhrcc_summaries', 'nhrcc_core_contributions_installed', 'nhrcc_core_contributions_version' ] as $option ) {
		delete_option( $option );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off cleanup; transients have no "delete by prefix" API.
	$transients = $wpdb->get_col( $wpdb->prepare( "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( '_transient_nhrcc_' ) . '%' ) );
	foreach ( (array) $transients as $transient ) {
		delete_transient( substr( $transient, strlen( '_transient_' ) ) );
	}

	wp_unschedule_hook( 'nhrcc_sync' );
}

if ( is_multisite() ) {
	foreach ( get_sites(
		[
			'fields' => 'ids',
			'number' => 0,
		]
	) as $nhrcc_site_id ) {
		switch_to_blog( $nhrcc_site_id );
		nhrcc_uninstall_site();
		restore_current_blog();
	}
} else {
	nhrcc_uninstall_site();
}
