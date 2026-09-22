<?php
/**
 * Woo basic: which product pages AI reads. Auto-detect only, no integration.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Woo
 */
class CrawlWatch_Woo {

	/**
	 * Is WooCommerce active.
	 *
	 * @return bool
	 */
	public static function is_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Top AI-read products in the last 30 days.
	 * Cached 12h like the Score: url_to_postid() does a full rewrite
	 * match + DB query per URL, and Overview loads on every page view.
	 *
	 * @param int $limit Max products.
	 * @return array Rows: id, title, hits, edit_url.
	 */
	public static function top_products( $limit = 5 ) {
		$limit = max( 1, min( 10, (int) $limit ) );
		if ( ! self::is_active() ) {
			return array();
		}

		$key    = 'crawlwatch_woo_top_' . $limit;
		$cached = get_transient( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$since = gmdate( 'Y-m-d H:i:s', time() - ( 30 * DAY_IN_SECONDS ) );
		$urls  = CrawlWatch_Logger::top_urls( $since, 20 );
		if ( empty( $urls ) ) {
			return array();
		}

		$home   = wp_parse_url( home_url( '/' ) );
		$prefix = ( isset( $home['scheme'] ) ? $home['scheme'] : 'http' ) . '://' . ( isset( $home['host'] ) ? $home['host'] : '' );

		$out  = array();
		$seen = array();
		foreach ( $urls as $row ) {
			$path = isset( $row['url'] ) ? $row['url'] : '';
			if ( '' === $path || '/' === $path ) {
				continue;
			}
			$pid = url_to_postid( $prefix . $path );
			if ( ! $pid || isset( $seen[ $pid ] ) ) {
				continue;
			}
			if ( 'product' !== get_post_type( $pid ) ) {
				continue;
			}
			$seen[ $pid ] = true;
			$out[]        = array(
				'id'       => (int) $pid,
				'title'    => get_the_title( $pid ),
				'hits'     => isset( $row['hits'] ) ? (int) $row['hits'] : 0,
				'edit_url' => get_edit_post_link( $pid, 'raw' ),
			);
			if ( count( $out ) >= $limit ) {
				break;
			}
		}
		set_transient( $key, $out, 12 * HOUR_IN_SECONDS );
		return $out;
	}

	/**
	 * Clear cached top products (call when content changes).
	 *
	 * @return void
	 */
	public static function clear() {
		for ( $limit = 1; $limit <= 10; $limit++ ) {
			delete_transient( 'crawlwatch_woo_top_' . $limit );
		}
	}
}
