<?php
/**
 * Activator: creates table + defaults + cron schedule.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Activator
 */
class CrawlWatch_Activator {

	/**
	 * Run on activation.
	 *
	 * @return void
	 */
	public static function activate() {
		self::create_table();
		self::add_defaults();
		self::schedule_cron();

		// One-time flag for the setup wizard redirect (consumed on next admin page).
		set_transient( 'crawlwatch_activation_redirect', 1, 60 );
	}

	/**
	 * Create logs table with dbDelta.
	 *
	 * @return void
	 */
	private static function create_table() {
		global $wpdb;

		$table           = $wpdb->prefix . 'crawlwatch_logs';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE `{$table}` (
			id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
			log_time DATETIME NOT NULL,
			bot_name VARCHAR(50) NOT NULL,
			bot_type VARCHAR(20) NOT NULL,
			url VARCHAR(255) NOT NULL,
			referrer VARCHAR(255) NULL,
			ip_hash CHAR(64) NULL,
			user_agent VARCHAR(255) NULL,
			PRIMARY KEY  (id),
			KEY log_time (log_time),
			KEY bot_name (bot_name),
			KEY url (url(191))
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Add default options if missing.
	 *
	 * @return void
	 */
	private static function add_defaults() {
		$defaults = crawlwatch_get_default_settings();
		$existing = get_option( 'crawlwatch_settings', null );
		if ( ! is_array( $existing ) ) {
			add_option( 'crawlwatch_settings', $defaults );
		}
		add_option( 'crawlwatch_db_version', CRAWLWATCH_DB_VERSION );
	}

	/**
	 * Run schema updates for existing users when DB version rises.
	 *
	 * @return void
	 */
	public static function update_db() {
		self::create_table();
		update_option( 'crawlwatch_db_version', CRAWLWATCH_DB_VERSION );
	}

	/**
	 * Schedule daily cleanup if not scheduled.
	 *
	 * @return void
	 */
	private static function schedule_cron() {
		if ( ! wp_next_scheduled( 'crawlwatch_daily_cleanup' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'crawlwatch_daily_cleanup' );
		}
	}
}
