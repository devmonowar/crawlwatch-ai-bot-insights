<?php
/**
 * Unit tests for CrawlWatch_Detector (UA + referrer + UTM matching).
 *
 * @package CrawlWatch
 */

use PHPUnit\Framework\TestCase;

class DetectorTest extends TestCase {

	public function test_match_crawler_finds_known_bot() {
		$this->assertSame(
			'GPTBot',
			CrawlWatch_Detector::match_crawler( 'Mozilla/5.0; compatible; GPTBot/1.2; +https://openai.com/gptbot' )
		);
	}

	public function test_match_crawler_is_case_insensitive() {
		$this->assertSame(
			'Claude',
			CrawlWatch_Detector::match_crawler( 'claudebot/1.0' )
		);
	}

	public function test_match_crawler_longest_first() {
		// GPTBot-Image must resolve to GPTBot, not confuse the shorter key order.
		$this->assertSame(
			'GPTBot',
			CrawlWatch_Detector::match_crawler( 'GPTBot-Image/1.0' )
		);
	}

	public function test_match_crawler_finds_search_and_agent_bots() {
		$expected = array(
			'Mozilla/5.0; compatible; Claude-SearchBot/1.0'                       => 'Claude-SearchBot',
			'Mozilla/5.0 AppleWebKit/537.36; compatible; OAI-AdsBot/1.0'          => 'OAI-AdsBot',
			'meta-webindexer/1.1 (+https://developers.facebook.com/docs/sharing)' => 'Meta',
			'meta-externalfetcher/1.1'                                            => 'Meta',
			'meta-externalads/1.1'                                                => 'Meta',
			'Mozilla/5.0; compatible; Amzn-SearchBot/0.1'                         => 'Amazonbot',
			'Mozilla/5.0; compatible; Amzn-User/0.1'                              => 'Amazonbot',
			'Mozilla/5.0; compatible; MistralAI-Index/1.0'                        => 'Mistral',
			'Mozilla/5.0; compatible; MistralAI-Training/1.0'                     => 'Mistral',
			'Mozilla/5.0; compatible; Google-CloudVertexBot'                      => 'Google-CloudVertexBot',
			'Mozilla/5.0; compatible; Google-Agent'                               => 'Google-Agent',
		);
		foreach ( $expected as $ua => $name ) {
			$this->assertSame( $name, CrawlWatch_Detector::match_crawler( $ua ), $ua );
		}
	}

	public function test_match_crawler_keeps_existing_bots_after_additions() {
		$this->assertSame( 'Claude', CrawlWatch_Detector::match_crawler( 'compatible; Claude-User/1.0' ) );
		$this->assertSame( 'Claude', CrawlWatch_Detector::match_crawler( 'compatible; ClaudeBot/1.0' ) );
		$this->assertSame( 'OAI-SearchBot', CrawlWatch_Detector::match_crawler( 'compatible; OAI-SearchBot/1.4' ) );
		$this->assertSame( 'Mistral', CrawlWatch_Detector::match_crawler( 'compatible; MistralAI-User/1.0' ) );
		$this->assertSame( 'GoogleOther', CrawlWatch_Detector::match_crawler( 'GoogleOther-Video/1.0' ) );
	}

	public function test_match_crawler_returns_empty_for_humans() {
		$this->assertSame(
			'',
			CrawlWatch_Detector::match_crawler( 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/126.0' )
		);
		$this->assertSame( '', CrawlWatch_Detector::match_crawler( '' ) );
	}

	public function test_match_referrer_finds_ai_domain() {
		$this->assertSame(
			'ChatGPT',
			CrawlWatch_Detector::match_referrer( 'https://chat.openai.com/share/abc123' )
		);
	}

	public function test_match_referrer_accepts_subdomains() {
		$label = CrawlWatch_Detector::match_referrer( 'https://www.perplexity.ai/search?q=test' );
		$this->assertNotSame( '', $label );
	}

	public function test_match_referrer_rejects_lookalike_domains() {
		// notchatgpt.com must NOT match chatgpt's domain rule.
		$this->assertSame( '', CrawlWatch_Detector::match_referrer( 'https://notchatgpt.com/' ) );
		$this->assertSame( '', CrawlWatch_Detector::match_referrer( '' ) );
	}

	public function test_is_ai_utm() {
		$this->assertTrue( CrawlWatch_Detector::is_ai_utm( 'ai_agent' ) );
		$this->assertTrue( CrawlWatch_Detector::is_ai_utm( ' AI_Agent ' ) );
		$this->assertFalse( CrawlWatch_Detector::is_ai_utm( 'newsletter' ) );
		$this->assertFalse( CrawlWatch_Detector::is_ai_utm( '' ) );
		$this->assertFalse( CrawlWatch_Detector::is_ai_utm( array( 'ai_agent' ) ) );
	}
}
