<?php
/**
 * Plugin Name: CrawlWatch – AI Bot Insights
 * Plugin URI: https://wordpress.org/plugins/crawlwatch-ai-bot-insights/
 * Description: See which AI bots read your site, track AI referrals, block unwanted bots & get AI-ready with llms.txt – fast, private, no API key. 100% free.
 * Version: 1.0.0
 * Requires at least: 6.2
 * Tested up to: 7.1
 * Requires PHP: 7.4
 * Author: Monowar Hossain
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: crawlwatch-ai-bot-insights
 * Domain Path: /languages
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CRAWLWATCH_VERSION', '1.0.0' );
define( 'CRAWLWATCH_DB_VERSION', '2' );
define( 'CRAWLWATCH_SLUG', 'crawlwatch-ai-bot-insights' );
define( 'CRAWLWATCH_FILE', __FILE__ );
define( 'CRAWLWATCH_PATH', plugin_dir_path( __FILE__ ) );
define( 'CRAWLWATCH_URL', plugin_dir_url( __FILE__ ) );

require_once CRAWLWATCH_PATH . 'includes/helpers.php';
require_once CRAWLWATCH_PATH . 'includes/bot-list.php';
require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-detector.php';
require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-logger.php';
require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-llms.php';
require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-robots.php';
require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-score.php';
require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-schema.php';
require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-woo.php';
require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-digest.php';
require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-activator.php';

CrawlWatch_Llms::init();
CrawlWatch_Robots::init();

if ( is_admin() ) {
	require_once CRAWLWATCH_PATH . 'includes/class-crawlwatch-admin.php';
	CrawlWatch_Admin::init();
}

/**
 * Get default settings.
 *
 * @return array
 */
function crawlwatch_get_default_settings() {
	return array(
		'version'             => CRAWLWATCH_VERSION,
		'db_version'          => CRAWLWATCH_DB_VERSION,
		'logging_enabled'     => 1,
		'llms_enabled'        => 1,
		'retention_days'      => 30,
		'delete_on_uninstall' => 1,
		'digest_enabled'      => 0,
		'digest_email'        => '',
		'llms_auto'           => 1,
		'llms_manual'         => 0,
	);
}

/**
 * Activation: create table + defaults + schedule cleanup.
 * Network-wide: loop every site so sub-sites get their table too.
 *
 * @param bool $network_wide Whether network-activated.
 * @return void
 */
function crawlwatch_activate( $network_wide = false ) {
	if ( is_multisite() && $network_wide ) {
		$site_ids = get_sites(
			array(
				'fields' => 'ids',
				'number' => 0,
			)
		);
		foreach ( $site_ids as $site_id ) {
			switch_to_blog( $site_id );
			CrawlWatch_Activator::activate();
			restore_current_blog();
		}
		return;
	}
	CrawlWatch_Activator::activate();
}
register_activation_hook( __FILE__, 'crawlwatch_activate' );

/**
 * Clear scheduled cron events for the current blog.
 *
 * @return void
 */
function crawlwatch_clear_cron() {
	$timestamp = wp_next_scheduled( 'crawlwatch_daily_cleanup' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'crawlwatch_daily_cleanup' );
	}
	$digest = wp_next_scheduled( CrawlWatch_Digest::HOOK );
	if ( $digest ) {
		wp_unschedule_event( $digest, CrawlWatch_Digest::HOOK );
	}
}
/**
 * Deactivation: clear scheduled cron only (data stays).
 * Network-wide: loop every site so no orphan cron remains.
 *
 * @param bool $network_wide Whether network-deactivated.
 * @return void
 */
function crawlwatch_deactivate( $network_wide = false ) {
	if ( is_multisite() && $network_wide ) {
		$site_ids = get_sites(
			array(
				'fields' => 'ids',
				'number' => 0,
			)
		);
		foreach ( $site_ids as $site_id ) {
			switch_to_blog( $site_id );
			crawlwatch_clear_cron();
			restore_current_blog();
		}
		return;
	}
	crawlwatch_clear_cron();
}
register_deactivation_hook( __FILE__, 'crawlwatch_deactivate' );

/**
 * New sub-site after network activation: create its table too.
 *
 * @param WP_Site $site New site object.
 * @return void
 */
function crawlwatch_new_site( $site ) {
	if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	if ( ! is_plugin_active_for_network( plugin_basename( CRAWLWATCH_FILE ) ) ) {
		return;
	}
	switch_to_blog( $site->blog_id );
	CrawlWatch_Activator::activate();
	restore_current_blog();
}
add_action( 'wp_initialize_site', 'crawlwatch_new_site' );

/**
 * Daily cleanup: delete logs older than retention days.
 *
 * @return void
 */
function crawlwatch_daily_cleanup() {
	$settings = get_option( 'crawlwatch_settings', crawlwatch_get_default_settings() );
	$days     = isset( $settings['retention_days'] ) ? absint( $settings['retention_days'] ) : 30;
	if ( $days < 7 ) {
		$days = 7;
	}
	if ( $days > 180 ) {
		$days = 180;
	}

	global $wpdb;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- cron cleanup, no cache needed.
	$wpdb->query(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is $wpdb->prefix + fixed string.
			"DELETE FROM `{$wpdb->prefix}crawlwatch_logs` WHERE log_time < %s",
			gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) )
		)
	);
}
add_action( 'crawlwatch_daily_cleanup', 'crawlwatch_daily_cleanup' );
add_action( CrawlWatch_Digest::HOOK, array( 'CrawlWatch_Digest', 'send' ) );

/**
 * Score cache invalidation: content or plugin set changed.
 * clear() takes no required args, so direct callbacks are safe.
 */
add_action( 'save_post', array( 'CrawlWatch_Score', 'clear' ), 10, 0 );
add_action( 'save_post', array( 'CrawlWatch_Llms', 'maybe_auto_refresh' ), 10, 1 );
add_action( 'save_post', array( 'CrawlWatch_Schema', 'clear' ), 10, 0 );
add_action( 'deleted_post', array( 'CrawlWatch_Score', 'clear' ), 10, 0 );
add_action( 'deleted_post', array( 'CrawlWatch_Llms', 'maybe_auto_refresh' ), 10, 1 );
add_action( 'deleted_post', array( 'CrawlWatch_Schema', 'clear' ), 10, 0 );
add_action( 'activated_plugin', array( 'CrawlWatch_Score', 'clear' ), 10, 0 );
add_action( 'deactivated_plugin', array( 'CrawlWatch_Score', 'clear' ), 10, 0 );

/**
 * DB updates for existing users: run dbDelta when version rises.
 * Admin-only: subscribers also hit admin_init (e.g. profile.php).
 *
 * @return void
 */
function crawlwatch_maybe_update_db() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$saved = get_option( 'crawlwatch_db_version', '' );
	if ( CRAWLWATCH_DB_VERSION === $saved ) {
		return;
	}
	CrawlWatch_Activator::update_db();
}
add_action( 'admin_init', 'crawlwatch_maybe_update_db', 20 );

/**
 * Frontend tracker: 0 queries for normal humans, 1 insert for AI hits.
 * Hooked on template_redirect (frontend only).
 *
 * @return void
 */
function crawlwatch_track() {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}
	if ( is_preview() || is_customize_preview() ) {
		return;
	}

	$settings = get_option( 'crawlwatch_settings', crawlwatch_get_default_settings() );
	if ( empty( $settings['logging_enabled'] ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below with explicit wrappers.
	$ua_raw = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) : '';
	$ua     = crawlwatch_safe_truncate( sanitize_text_field( $ua_raw ), 255 );

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized below.
	$uri_raw = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$url     = crawlwatch_safe_truncate( sanitize_text_field( $uri_raw ), 255 );
	if ( '' === $url ) {
		$url = '/';
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- host extracted via wp_parse_url, stored truncated.
	$ref_raw  = isset( $_SERVER['HTTP_REFERER'] ) ? wp_unslash( $_SERVER['HTTP_REFERER'] ) : '';
	$referrer = crawlwatch_safe_truncate( esc_url_raw( $ref_raw ), 255 );

	$bot_name = CrawlWatch_Detector::match_crawler( $ua );
	$bot_type = 'crawl';

	if ( '' === $bot_name ) {
		$bot_name = CrawlWatch_Detector::match_referrer( $referrer );
		$bot_type = '' !== $bot_name ? 'referral' : '';
	}

	if ( '' === $bot_name ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only UTM attribution, no state change except own log.
		$utm_raw = isset( $_GET['utm_medium'] ) ? sanitize_text_field( wp_unslash( $_GET['utm_medium'] ) ) : '';
		if ( CrawlWatch_Detector::is_ai_utm( $utm_raw ) ) {
			$bot_name = 'AI-Referral';
			$bot_type = 'referral';
		}
	}

	if ( '' === $bot_name ) {
		return; // Normal human: zero queries.
	}

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REMOTE_ADDR is server var, hashed not stored.
	$ip_raw  = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
	$ip_hash = crawlwatch_hash_ip( sanitize_text_field( $ip_raw ) );

	CrawlWatch_Logger::insert( $bot_name, $bot_type, $url, $referrer, $ua, $ip_hash );
}
add_action( 'template_redirect', 'crawlwatch_track', 99 );
