<?php
/**
 * Schema gap report: on-demand scan of latest posts for schema markup.
 * Report only — never modifies content.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Schema
 */
class CrawlWatch_Schema {

	/**
	 * Option holding the last report.
	 *
	 * @var string
	 */
	const OPTION = 'crawlwatch_schema_report';

	/**
	 * How many posts to scan.
	 *
	 * @var int
	 */
	const SAMPLE = 10;

	/**
	 * Get the cached report.
	 *
	 * @return array Empty when never scanned.
	 */
	public static function get_report() {
		$report = get_option( self::OPTION, array() );
		return is_array( $report ) ? $report : array();
	}

	/**
	 * Clear the cached report (call when content changes).
	 *
	 * @return void
	 */
	public static function clear() {
		delete_option( self::OPTION );
	}

	/**
	 * Scan latest posts/pages for schema markup in their frontend HTML.
	 *
	 * @return array Report with scanned_at + rows.
	 */
	public static function scan() {
		$ids = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'numberposts'    => self::SAMPLE,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$rows = array();
		foreach ( $ids as $pid ) {
			$url       = get_permalink( $pid );
			$reachable = false;
			$has       = false;
			if ( $url ) {
				$res = wp_remote_get(
					$url,
					array(
						'timeout'     => 3,
						'redirection' => 2,
					)
				);
				if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
					$reachable = true;
					$body      = (string) wp_remote_retrieve_body( $res );
					$has       = false !== stripos( $body, 'application/ld+json' ) || false !== stripos( $body, 'schema.org' );
				}
			}
			$rows[] = array(
				'id'        => (int) $pid,
				'title'     => get_the_title( $pid ),
				'type'      => get_post_type( $pid ),
				'edit_url'  => get_edit_post_link( $pid, 'raw' ),
				'reachable' => $reachable,
				'has_schema' => $has,
			);
		}

		$report = array(
			'scanned_at' => time(),
			'rows'       => $rows,
		);
		update_option( self::OPTION, $report, false );
		return $report;
	}
}
