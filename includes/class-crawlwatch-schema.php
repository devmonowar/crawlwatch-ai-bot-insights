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
	 * How many URLs to fetch per request. A full 10-URL scan (3s timeout
	 * each) can hit a 30s max_execution_time mid-request and save nothing,
	 * so each click scans one batch and the button keeps its place.
	 *
	 * @var int
	 */
	const BATCH = 5;

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
	 * One batch per call; rows accumulate in the option until SAMPLE done.
	 *
	 * @return array Report with scanned_at + rows + done flag.
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

		$report = self::get_report();
		$rows   = isset( $report['rows'] ) && is_array( $report['rows'] ) ? $report['rows'] : array();
		if ( ! empty( $report['done'] ) ) {
			// Fresh rescan: a done report scans nothing otherwise (regression).
			$rows = array();
		}
		if ( empty( $rows ) ) {
			$report['scanned_at'] = time();
		} elseif ( ! isset( $report['scanned_at'] ) ) {
			$report['scanned_at'] = time();
		}

		$todo = array();
		foreach ( $ids as $pid ) {
			if ( ! isset( $rows[ (int) $pid ] ) ) {
				$todo[] = (int) $pid;
			}
		}
		$todo = array_slice( $todo, 0, self::BATCH );

		foreach ( $todo as $pid ) {
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
			$rows[ $pid ] = array(
				'id'        => $pid,
				'title'     => get_the_title( $pid ),
				'type'      => get_post_type( $pid ),
				'edit_url'  => get_edit_post_link( $pid, 'raw' ),
				'reachable' => $reachable,
				'has_schema' => $has,
			);
		}

		$remaining = 0;
		foreach ( $ids as $pid ) {
			if ( ! isset( $rows[ (int) $pid ] ) ) {
				++$remaining;
			}
		}

		$report['rows'] = $rows;
		$report['done'] = 0 === $remaining;
		update_option( self::OPTION, $report, false );
		return $report;
	}
}
