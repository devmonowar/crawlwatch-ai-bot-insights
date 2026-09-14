<?php
/**
 * PHPUnit bootstrap for CrawlWatch's dependency-free unit tests.
 *
 * These tests exercise pure logic (bot matching, sanitising, CSV guard,
 * IP hashing) without a WordPress install or database. The handful of
 * WordPress helper functions that logic touches are shimmed below with
 * faithful minimal implementations — enough to test our own behaviour,
 * not WordPress's.
 *
 * @package CrawlWatch
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.WP.AlternativeFunctions, Universal.Files.SeparateFunctionsFromOO

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $hook, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $n ) {
		return abs( (int) $n );
	}
}

if ( ! function_exists( 'wp_parse_url' ) ) {
	function wp_parse_url( $url, $component = -1 ) {
		return parse_url( (string) $url, $component );
	}
}

if ( ! function_exists( 'wp_salt' ) ) {
	function wp_salt( $scheme = 'auth' ) {
		return 'test-salt';
	}
}

if ( ! function_exists( 'wp_date' ) ) {
	function wp_date( $format, $timestamp = null ) {
		return gmdate( (string) $format, null === $timestamp ? time() : (int) $timestamp );
	}
}

// Plugin constants used by the loaded files.
define( 'CRAWLWATCH_VERSION', '0.0.0-test' );
define( 'CRAWLWATCH_DB_VERSION', '2' );
define( 'CRAWLWATCH_SLUG', 'crawlwatch-ai-bot-insights' );

// Load just the files the unit tests need (no full plugin bootstrap).
require_once dirname( __DIR__ ) . '/includes/helpers.php';
require_once dirname( __DIR__ ) . '/includes/bot-list.php';
require_once dirname( __DIR__ ) . '/includes/class-crawlwatch-detector.php';
