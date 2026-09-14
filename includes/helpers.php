<?php
/**
 * Shared helpers.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get allowed retention options (days => label).
 *
 * @return array
 */
function crawlwatch_allowed_retentions() {
	return array(
		7   => __( '7 days', 'crawlwatch-ai-bot-insights' ),
		14  => __( '14 days', 'crawlwatch-ai-bot-insights' ),
		30  => __( '30 days (recommended)', 'crawlwatch-ai-bot-insights' ),
		90  => __( '90 days (large DB warning)', 'crawlwatch-ai-bot-insights' ),
		180 => __( '180 days (large DB warning)', 'crawlwatch-ai-bot-insights' ),
	);
}

/**
 * Sanitize retention days to nearest allowed value.
 *
 * @param mixed $value Raw value.
 * @return int
 */
function crawlwatch_sanitize_retention( $value ) {
	$value   = absint( $value );
	$allowed = array_keys( crawlwatch_allowed_retentions() );
	if ( ! in_array( $value, $allowed, true ) ) {
		return 30;
	}
	return $value;
}

/**
 * Multibyte-safe truncate (mb_substr when available, else substr).
 *
 * @param string $text  Input.
 * @param int    $limit Max characters.
 * @return string
 */
function crawlwatch_safe_truncate( $text, $limit ) {
	$text  = (string) $text;
	$limit = max( 1, (int) $limit );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, 0, $limit, 'UTF-8' );
	}
	return substr( $text, 0, $limit );
}

/**
 * Format a GMT log time in the site timezone (Y-m-d H:i).
 *
 * @param string $gmt MySQL datetime GMT.
 * @return string
 */
function crawlwatch_display_time( $gmt ) {
	$gmt = trim( (string) $gmt );
	if ( '' === $gmt ) {
		return '';
	}
	$ts = strtotime( $gmt . ' UTC' );
	if ( false === $ts ) {
		return $gmt;
	}
	return wp_date( 'Y-m-d H:i', $ts );
}

/**
 * Short site timezone label for table headers (e.g. +06:00, BST).
 *
 * @return string
 */
function crawlwatch_tz_label() {
	return wp_date( 'T' );
}

/**
 * Make a value safe for CSV export (OWASP formula injection guard).
 * Cells starting with = + - @ get a leading single quote so
 * spreadsheet apps treat them as text, never as formulas.
 *
 * @param string $text Input.
 * @return string
 */
function crawlwatch_csv_cell( $text ) {
	$text = (string) $text;
	if ( '' !== $text && in_array( $text[0], array( '=', '+', '-', '@' ), true ) ) {
		return "'" . $text;
	}
	return $text;
}

/**
 * Hash an IP address (privacy: never store raw IP).
 * IPv4: last octet zeroed. IPv6: last 64 bits zeroed. Then SHA-256 with salt.
 *
 * @param string $ip Raw IP.
 * @return string SHA-256 hash or empty string.
 */
function crawlwatch_hash_ip( $ip ) {
	$ip = trim( (string) $ip );
	if ( '' === $ip ) {
		return '';
	}
	if ( strpos( $ip, '.' ) !== false && strpos( $ip, ':' ) === false ) {
		$parts = explode( '.', $ip );
		if ( count( $parts ) === 4 ) {
			$parts[3] = '0';
			$ip       = implode( '.', $parts );
		}
	} elseif ( strpos( $ip, ':' ) !== false ) {
		// IPv6: keep first 4 hextets (/64), zero the rest (interface identifier).
		$ip     = preg_replace( '/%[a-zA-Z0-9]+$/', '', $ip ); // Strip zone id (%eth0).
		$packed = false;
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			$packed = inet_pton( $ip );
		}
		if ( false !== $packed && 16 === strlen( $packed ) ) {
			$ip = inet_ntop( substr( $packed, 0, 8 ) . str_repeat( "\x00", 8 ) );
		} else {
			$groups = explode( ':', $ip );
			$ip     = implode( ':', array_slice( $groups, 0, 4 ) ) . '::';
		}
	}
	return hash( 'sha256', $ip . '|' . wp_salt( 'auth' ) );
}
