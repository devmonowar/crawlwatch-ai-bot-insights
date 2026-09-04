<?php
/**
 * Logger: insert + read helpers. All queries prepared + cached where sensible.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Logger
 */
class CrawlWatch_Logger {

	/**
	 * Insert one log row. Rate-limits identical hits to 1/min.
	 *
	 * @param string $bot_name Canonical bot name.
	 * @param string $bot_type crawl|referral.
	 * @param string $url      Request path (max 255).
	 * @param string $referrer Full referrer (max 255).
	 * @param string $ua       User agent (max 255).
	 * @param string $ip_hash  SHA-256 hash.
	 * @return int|false Insert id or false.
	 */
	public static function insert( $bot_name, $bot_type, $url, $referrer, $ua, $ip_hash ) {
		global $wpdb;

		$bot_name = crawlwatch_safe_truncate( sanitize_text_field( $bot_name ), 50 );
		$bot_type = 'referral' === $bot_type ? 'referral' : 'crawl';
		$url      = crawlwatch_safe_truncate( $url, 255 );
		$referrer = crawlwatch_safe_truncate( $referrer, 255 );
		$ua       = crawlwatch_safe_truncate( $ua, 255 );
		$ip_hash  = substr( preg_replace( '/[^a-f0-9]/', '', strtolower( $ip_hash ) ), 0, 64 );

		if ( '' === $bot_name || '' === $url ) {
			return false;
		}

		// Rate-limit: same bot+type+url+hash within 60s -> skip.
		// Type is part of the key so a crawl and a referral for the same
		// bot+URL count as two distinct events.
		$since = gmdate( 'Y-m-d H:i:s', time() - 60 );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- dedupe check before insert.
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table is internal prefix + fixed string.
				"SELECT id FROM `{$wpdb->prefix}crawlwatch_logs` WHERE bot_name = %s AND bot_type = %s AND url = %s AND ip_hash = %s AND log_time > %s LIMIT 1",
				$bot_name,
				$bot_type,
				$url,
				$ip_hash,
				$since
			)
		);
		if ( $exists ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- logging write, no cache.
		$ok = $wpdb->insert(
			$wpdb->prefix . 'crawlwatch_logs',
			array(
				'log_time'   => current_time( 'mysql', true ),
				'bot_name'   => $bot_name,
				'bot_type'   => $bot_type,
				'url'        => $url,
				'referrer'   => $referrer,
				'ip_hash'    => $ip_hash,
				'user_agent' => $ua,
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
		return $ok ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Total hits since date.
	 *
	 * @param string $since MySQL datetime GMT.
	 * @return int
	 */
	public static function count_since( $since ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- stats read.
		$n = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- internal table.
				"SELECT COUNT(*) FROM `{$wpdb->prefix}crawlwatch_logs` WHERE log_time >= %s",
				$since
			)
		);
		return (int) $n;
	}

	/**
	 * Top bots since date.
	 *
	 * @param string $since MySQL datetime GMT.
	 * @param int    $limit Max rows.
	 * @return array
	 */
	public static function top_bots( $since, $limit = 5 ) {
		global $wpdb;
		$limit = max( 1, min( 20, (int) $limit ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- stats read.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- internal table, limit is int-cast.
				"SELECT bot_name, COUNT(*) AS hits FROM `{$wpdb->prefix}crawlwatch_logs` WHERE log_time >= %s GROUP BY bot_name ORDER BY hits DESC LIMIT %d",
				$since,
				$limit
			),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Count by type since date.
	 *
	 * @param string $since MySQL datetime GMT.
	 * @param string $type  crawl|referral.
	 * @return int
	 */
	public static function count_by_type( $since, $type ) {
		global $wpdb;
		$type = 'referral' === $type ? 'referral' : 'crawl';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- stats read.
		$n = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- internal table.
				"SELECT COUNT(*) FROM `{$wpdb->prefix}crawlwatch_logs` WHERE log_time >= %s AND bot_type = %s",
				$since,
				$type
			)
		);
		return (int) $n;
	}

	/**
	 * Unique bot count since date.
	 *
	 * @param string $since MySQL datetime GMT.
	 * @return int
	 */
	public static function count_unique_bots( $since ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- stats read.
		$n = $wpdb->get_var(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- internal table.
				"SELECT COUNT(DISTINCT bot_name) FROM `{$wpdb->prefix}crawlwatch_logs` WHERE log_time >= %s",
				$since
			)
		);
		return (int) $n;
	}

	/**
	 * Count filtered rows (bots page).
	 *
	 * @param string $bot Bot name or empty for all.
	 * @param string $q   URL search or empty.
	 * @return int
	 */
	public static function count_filtered( $bot, $q ) {
		global $wpdb;
		if ( '' !== $bot && '' !== $q ) {
			$like = '%' . $wpdb->esc_like( $q ) . '%';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, fixed name.
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$wpdb->prefix}crawlwatch_logs` WHERE bot_name = %s AND url LIKE %s",
					$bot,
					$like
				)
			);
		}
		if ( '' !== $bot ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, fixed name.
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$wpdb->prefix}crawlwatch_logs` WHERE bot_name = %s",
					$bot
				)
			);
		}
		if ( '' !== $q ) {
			$like = '%' . $wpdb->esc_like( $q ) . '%';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, fixed name.
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM `{$wpdb->prefix}crawlwatch_logs` WHERE url LIKE %s",
					$like
				)
			);
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, fixed name, no user input.
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$wpdb->prefix}crawlwatch_logs`" );
	}

	/**
	 * Get filtered rows paged (bots page).
	 *
	 * @param string $bot    Bot name or empty.
	 * @param string $q      URL search or empty.
	 * @param int    $limit  Per page.
	 * @param int    $offset Offset.
	 * @return array
	 */
	public static function get_filtered( $bot, $q, $limit, $offset ) {
		global $wpdb;
		$limit  = max( 1, min( 100, (int) $limit ) );
		$offset = max( 0, (int) $offset );
		if ( '' !== $bot && '' !== $q ) {
			$like = '%' . $wpdb->esc_like( $q ) . '%';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, fixed name.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT log_time, bot_name, bot_type, url, referrer FROM `{$wpdb->prefix}crawlwatch_logs` WHERE bot_name = %s AND url LIKE %s ORDER BY id DESC LIMIT %d OFFSET %d",
					$bot,
					$like,
					$limit,
					$offset
				),
				ARRAY_A
			);
			return is_array( $rows ) ? $rows : array();
		}
		if ( '' !== $bot ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, fixed name.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT log_time, bot_name, bot_type, url, referrer FROM `{$wpdb->prefix}crawlwatch_logs` WHERE bot_name = %s ORDER BY id DESC LIMIT %d OFFSET %d",
					$bot,
					$limit,
					$offset
				),
				ARRAY_A
			);
			return is_array( $rows ) ? $rows : array();
		}
		if ( '' !== $q ) {
			$like = '%' . $wpdb->esc_like( $q ) . '%';
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, fixed name.
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT log_time, bot_name, bot_type, url, referrer FROM `{$wpdb->prefix}crawlwatch_logs` WHERE url LIKE %s ORDER BY id DESC LIMIT %d OFFSET %d",
					$like,
					$limit,
					$offset
				),
				ARRAY_A
			);
			return is_array( $rows ) ? $rows : array();
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, fixed name, limits int-cast.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT log_time, bot_name, bot_type, url, referrer FROM `{$wpdb->prefix}crawlwatch_logs` ORDER BY id DESC LIMIT %d OFFSET %d",
				$limit,
				$offset
			),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Distinct bot names for filter dropdown.
	 *
	 * @return array
	 */
	public static function distinct_bots() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- small fixed list from own table, no user input.
		$rows = $wpdb->get_col( "SELECT DISTINCT bot_name FROM `{$wpdb->prefix}crawlwatch_logs` ORDER BY bot_name ASC LIMIT 100" );
		return is_array( $rows ) ? array_map( 'sanitize_text_field', $rows ) : array();
	}

	/**
	 * Recent rows.
	 *
	 * @param int $limit Max rows.
	 * @return array
	 */
	public static function recent( $limit = 20 ) {
		global $wpdb;
		$limit = max( 1, min( 100, (int) $limit ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- admin list.
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- internal table.
				"SELECT log_time, bot_name, bot_type, url, referrer FROM `{$wpdb->prefix}crawlwatch_logs` ORDER BY id DESC LIMIT %d",
				$limit
			),
			ARRAY_A
		);
		return is_array( $rows ) ? $rows : array();
	}
}
