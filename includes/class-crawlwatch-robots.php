<?php
/**
 * Append per-bot Disallow rules for blocked AI crawlers.
 * Never overwrites core output; adds nothing when nothing is blocked.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Robots
 */
class CrawlWatch_Robots {

	/**
	 * Managed bot list for checkboxes (UA token => label).
	 *
	 * @return array
	 */
	public static function managed_bots() {
		return array(
			'GPTBot'          => 'GPTBot (OpenAI)',
			'ChatGPT-User'    => 'ChatGPT-User',
			'OAI-SearchBot'   => 'OAI-SearchBot',
			'ClaudeBot'       => 'ClaudeBot',
			'PerplexityBot'   => 'PerplexityBot',
			'Google-Extended' => 'Google-Extended',
			'CCBot'           => 'CCBot (Common Crawl)',
			'Bytespider'      => 'Bytespider (TikTok)',
		);
	}

	/**
	 * Map a logged bot name to its robots token. Explicit aliases only.
	 *
	 * @param string $bot_name Canonical name from the log.
	 * @return string Token or empty when not manageable.
	 */
	public static function token_for_bot( $bot_name ) {
		$managed = self::managed_bots();
		if ( isset( $managed[ $bot_name ] ) ) {
			return $bot_name;
		}
		$aliases = array(
			'Claude'       => 'ClaudeBot',
			'ChatGPT'      => 'ChatGPT-User',
			'Perplexity'   => 'PerplexityBot',
			'CommonCrawl'  => 'CCBot',
		);
		if ( isset( $aliases[ $bot_name ] ) && isset( $managed[ $aliases[ $bot_name ] ] ) ) {
			return $aliases[ $bot_name ];
		}
		return '';
	}

	/**
	 * Init filter.
	 *
	 * @return void
	 */
	public static function init() {
		add_filter( 'robots_txt', array( __CLASS__, 'append' ), 20, 2 );
	}

	/**
	 * Append rules.
	 *
	 * @param string $output    Current robots output.
	 * @param bool   $is_public Blog public flag.
	 * @return string
	 */
	public static function append( $output, $is_public ) {
		if ( ! $is_public ) {
			return $output; // Site discourages crawling: do not invite AI bots.
		}
		$settings = get_option( 'crawlwatch_settings', array() );
		$rules    = isset( $settings['robots_rules'] ) && is_array( $settings['robots_rules'] ) ? $settings['robots_rules'] : array();

		// Print only blocked bots. Allowed bots need no lines (default allow).
		$blocked = array();
		foreach ( self::managed_bots() as $token => $label ) {
			if ( isset( $rules[ $token ] ) && 'block' === $rules[ $token ] ) {
				$blocked[] = $token;
			}
		}
		if ( empty( $blocked ) ) {
			return $output;
		}

		$out = "\n# CrawlWatch – AI Bot Insights\n";
		foreach ( $blocked as $token ) {
			$out .= 'User-agent: ' . $token . "\nDisallow: /\n";
		}
		return $output . $out;
	}
}
