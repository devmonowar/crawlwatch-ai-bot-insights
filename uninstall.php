<?php
/**
 * Uninstall: always clear cron; remove data only if user opted in.
 * Multisite: loops every site on network-wide uninstall.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Clean one blog: cron always, data only when opted in.
 *
 * @return void
 */
function crawlwatch_uninstall_blog() {
	wp_clear_scheduled_hook( 'crawlwatch_daily_cleanup' );
	wp_clear_scheduled_hook( 'crawlwatch_weekly_digest' );

	$crawlwatch_settings = get_option( 'crawlwatch_settings', array() );
	$crawlwatch_delete   = isset( $crawlwatch_settings['delete_on_uninstall'] ) ? (int) $crawlwatch_settings['delete_on_uninstall'] : 1;

	if ( 1 !== $crawlwatch_delete ) {
		return;
	}

	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- uninstall cleanup of own fixed table.
	$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}crawlwatch_logs`" );

	delete_option( 'crawlwatch_settings' );
	delete_option( 'crawlwatch_db_version' );
	delete_option( 'crawlwatch_llms_content' );
	delete_option( 'crawlwatch_llms_full_content' );
	delete_option( 'crawlwatch_schema_report' );
	delete_transient( 'crawlwatch_activation_redirect' );
	delete_transient( 'crawlwatch_score_cache' );
}

if ( is_multisite() ) {
	$crawlwatch_site_ids = get_sites(
		array(
			'fields' => 'ids',
			'number' => 0,
		)
	);
	foreach ( $crawlwatch_site_ids as $crawlwatch_site_id ) {
		switch_to_blog( $crawlwatch_site_id );
		crawlwatch_uninstall_blog();
	}
	restore_current_blog();
} else {
	crawlwatch_uninstall_blog();
}
