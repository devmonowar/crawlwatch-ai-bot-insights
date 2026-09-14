<?php
/**
 * Unit tests for CrawlWatch helper functions.
 *
 * @package CrawlWatch
 */

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase {

	public function test_sanitize_retention_allows_only_known_values() {
		$this->assertSame( 30, crawlwatch_sanitize_retention( 30 ) );
		$this->assertSame( 7, crawlwatch_sanitize_retention( '7' ) );
		$this->assertSame( 30, crawlwatch_sanitize_retention( 45 ) );
		$this->assertSame( 30, crawlwatch_sanitize_retention( 'junk' ) );
	}

	public function test_safe_truncate() {
		$this->assertSame( 'abc', crawlwatch_safe_truncate( 'abcdef', 3 ) );
		$this->assertSame( 'abc', crawlwatch_safe_truncate( 'abc', 10 ) );
	}

	public function test_csv_cell_guards_formula_injection() {
		$this->assertSame( "'=1+1", crawlwatch_csv_cell( '=1+1' ) );
		$this->assertSame( "'+cmd", crawlwatch_csv_cell( '+cmd' ) );
		$this->assertSame( "'-5", crawlwatch_csv_cell( '-5' ) );
		$this->assertSame( "'@user", crawlwatch_csv_cell( '@user' ) );
		$this->assertSame( 'plain', crawlwatch_csv_cell( 'plain' ) );
		$this->assertSame( '', crawlwatch_csv_cell( '' ) );
	}

	public function test_display_time() {
		$this->assertSame( '', crawlwatch_display_time( '' ) );
		$this->assertSame( 'not-a-date', crawlwatch_display_time( 'not-a-date' ) );
		$this->assertSame(
			'2026-09-01 12:00',
			crawlwatch_display_time( '2026-09-01 12:00:00' )
		);
	}

	public function test_hash_ip_is_stable_and_private() {
		$hash = crawlwatch_hash_ip( '192.168.1.55' );
		$this->assertSame( 64, strlen( $hash ) );
		$this->assertSame( $hash, crawlwatch_hash_ip( '192.168.1.55' ) );
		// Last octet zeroed: .55 and .99 hash identically.
		$this->assertSame( $hash, crawlwatch_hash_ip( '192.168.1.99' ) );
		// But a different network hashes differently.
		$this->assertNotSame( $hash, crawlwatch_hash_ip( '10.0.0.5' ) );
		$this->assertSame( '', crawlwatch_hash_ip( '' ) );
	}
}
