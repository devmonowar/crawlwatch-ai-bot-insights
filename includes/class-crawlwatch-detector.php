<?php
/**
 * Detector: UA + referrer matching. No DB here.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Detector
 */
class CrawlWatch_Detector {

	/**
	 * Match user-agent to canonical bot name.
	 *
	 * @param string $ua User agent.
	 * @return string Empty if no match.
	 */
	public static function match_crawler( $ua ) {
		if ( '' === $ua ) {
			return '';
		}
		$patterns = crawlwatch_get_crawler_patterns();
		foreach ( $patterns as $fragment => $name ) {
			if ( stripos( $ua, (string) $fragment ) !== false ) {
				return (string) $name;
			}
		}
		return '';
	}

	/**
	 * Match referrer URL host to AI label.
	 *
	 * @param string $referrer Full referrer URL.
	 * @return string Empty if no match.
	 */
	public static function match_referrer( $referrer ) {
		if ( '' === $referrer ) {
			return '';
		}
		$host = strtolower( (string) wp_parse_url( $referrer, PHP_URL_HOST ) );
		if ( '' === $host ) {
			return '';
		}
		$domains = crawlwatch_get_referral_domains();
		foreach ( $domains as $domain => $label ) {
			$domain = strtolower( (string) $domain );
			if ( $host === $domain || substr( $host, -( strlen( $domain ) + 1 ) ) === '.' . $domain ) {
				return (string) $label;
			}
		}
		return '';
	}

	/**
	 * Check UTM medium=ai_agent (case-insensitive).
	 *
	 * @param mixed $raw Raw $_GET value (already unslashed by caller).
	 * @return bool
	 */
	public static function is_ai_utm( $raw ) {
		if ( ! is_string( $raw ) ) {
			return false;
		}
		return strtolower( trim( $raw ) ) === 'ai_agent';
	}
}
