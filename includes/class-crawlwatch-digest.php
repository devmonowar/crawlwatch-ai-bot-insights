<?php
/**
 * Weekly email digest: last-7-days AI summary via wp_mail. Opt-in only.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Digest
 */
class CrawlWatch_Digest {

	/**
	 * Cron hook name.
	 *
	 * @var string
	 */
	const HOOK = 'crawlwatch_weekly_digest';

	/**
	 * Sync schedule with settings: schedule when opted in, else unschedule.
	 *
	 * @return void
	 */
	public static function maybe_schedule() {
		$settings = get_option( 'crawlwatch_settings', crawlwatch_get_default_settings() );
		$enabled  = ! empty( $settings['digest_enabled'] ) && '' !== self::recipient( $settings );
		if ( $enabled ) {
			if ( ! wp_next_scheduled( self::HOOK ) ) {
				wp_schedule_event( time() + DAY_IN_SECONDS, 'weekly', self::HOOK );
			}
			return;
		}
		$timestamp = wp_next_scheduled( self::HOOK );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, self::HOOK );
		}
	}

	/**
	 * Resolve recipient: saved address or site admin email.
	 *
	 * @param array $settings Settings array.
	 * @return string Valid email or empty.
	 */
	private static function recipient( $settings ) {
		$saved = isset( $settings['digest_email'] ) ? sanitize_email( $settings['digest_email'] ) : '';
		if ( '' !== $saved && is_email( $saved ) ) {
			return $saved;
		}
		$admin = get_option( 'admin_email', '' );
		return is_email( $admin ) ? $admin : '';
	}

	/**
	 * Build + send the digest. Re-checks opt-in (cron may outlive a toggle).
	 *
	 * @return bool Sent or not.
	 */
	public static function send() {
		$settings = get_option( 'crawlwatch_settings', crawlwatch_get_default_settings() );
		if ( empty( $settings['digest_enabled'] ) ) {
			return false;
		}
		$to = self::recipient( $settings );
		if ( '' === $to ) {
			return false;
		}

		$since     = gmdate( 'Y-m-d H:i:s', time() - ( 7 * DAY_IN_SECONDS ) );
		$total     = CrawlWatch_Logger::count_since( $since );
		$crawls    = CrawlWatch_Logger::count_by_type( $since, 'crawl' );
		$referrals = CrawlWatch_Logger::count_by_type( $since, 'referral' );
		$unique    = CrawlWatch_Logger::count_unique_bots( $since );
		$top_bots  = CrawlWatch_Logger::top_bots( $since, 5 );
		$top_urls  = CrawlWatch_Logger::top_urls( $since, 5 );

		$score_data = CrawlWatch_Score::get();
		$score      = isset( $score_data['score'] ) ? (int) $score_data['score'] : 0;

		$site = get_bloginfo( 'name' );
		/* translators: %s: site name. */
		$subject = sprintf( __( '[%s] Weekly AI bot digest', 'crawlwatch-ai-bot-insights' ), $site );

		$lines   = array();
		$lines[] = '<h2>' . esc_html( $site ) . '</h2>';
		/* translators: %s: number of hits, formatted. */
		$lines[] = '<p>' . esc_html( sprintf( __( 'AI hits in the last 7 days: %s', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $total ) ) ) . '</p>';
		$lines[] = '<ul>';
		/* translators: %s: number of crawls, formatted. */
		$lines[] = '<li>' . esc_html( sprintf( __( 'Crawls: %s', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $crawls ) ) ) . '</li>';
		/* translators: %s: number of referrals, formatted. */
		$lines[] = '<li>' . esc_html( sprintf( __( 'AI referrals: %s', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $referrals ) ) ) . '</li>';
		/* translators: %s: number of bots, formatted. */
		$lines[] = '<li>' . esc_html( sprintf( __( 'Unique bots: %s', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $unique ) ) ) . '</li>';
		/* translators: %s: score 0-100. */
		$lines[] = '<li>' . esc_html( sprintf( __( 'Readiness score: %s/100', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $score ) ) ) . '</li>';
		$lines[] = '</ul>';

		if ( ! empty( $top_bots ) ) {
			$lines[] = '<h3>' . esc_html__( 'Top bots', 'crawlwatch-ai-bot-insights' ) . '</h3><ul>';
			foreach ( $top_bots as $row ) {
				/* translators: 1: bot name, 2: hit count, formatted. */
				$lines[] = '<li>' . esc_html( sprintf( __( '%1$s: %2$s hits', 'crawlwatch-ai-bot-insights' ), $row['bot_name'], number_format_i18n( (int) $row['hits'] ) ) ) . '</li>';
			}
			$lines[] = '</ul>';
		}

		if ( ! empty( $top_urls ) ) {
			$lines[] = '<h3>' . esc_html__( 'Top crawled URLs', 'crawlwatch-ai-bot-insights' ) . '</h3><ul>';
			foreach ( $top_urls as $row ) {
				/* translators: 1: URL, 2: hit count, formatted. */
				$lines[] = '<li>' . esc_html( sprintf( __( '%1$s: %2$s hits', 'crawlwatch-ai-bot-insights' ), $row['url'], number_format_i18n( (int) $row['hits'] ) ) ) . '</li>';
			}
			$lines[] = '</ul>';
		}

		if ( 0 === $total ) {
			$lines[] = '<p>' . esc_html__( 'No AI visits this week. Your llms.txt is ready for when they come.', 'crawlwatch-ai-bot-insights' ) . '</p>';
		}

		$sent = wp_mail( $to, $subject, implode( "\n", $lines ), array( 'Content-Type: text/html; charset=UTF-8' ) );
		return (bool) $sent;
	}
}
