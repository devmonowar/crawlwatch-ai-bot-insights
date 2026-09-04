<?php
/**
 * Bot + referral lists. Longest-first matching.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI crawler user-agent fragments => canonical bot name.
 * Ordered longest-first to avoid GPTBot vs GPTBot-Image confusion.
 *
 * @return array
 */
function crawlwatch_get_crawler_patterns() {
	$map = array(
		'OAI-SearchBot-Image' => 'OAI-SearchBot',
		'GoogleOther-Image'   => 'GoogleOther',
		'Applebot-Extended'   => 'Applebot',
		'Perplexity-User'     => 'Perplexity',
		'Claude-User'         => 'Claude',
		'ChatGPT-User'        => 'ChatGPT',
		'OAI-SearchBot'       => 'OAI-SearchBot',
		'Google-Extended'     => 'Google-Extended',
		'Meta-ExternalAgent'  => 'Meta',
		'Anthropic-ai'        => 'Claude',
		'PerplexityBot'       => 'Perplexity',
		'MistralAI-User'      => 'Mistral',
		'DeepSeekBot'         => 'DeepSeek',
		'Bytespider'          => 'Bytespider',
		'TikTokSpider'        => 'Bytespider',
		'GPTBot-Image'        => 'GPTBot',
		'ClaudeBot'           => 'Claude',
		'DuckAssistBot'       => 'DuckDuckGo',
		'Amazonbot'           => 'Amazonbot',
		'Applebot'            => 'Applebot',
		'BingPreview'         => 'Bing',
		'Cohere-Train'        => 'Cohere',
		'cohere-ai'           => 'Cohere',
		'FacebookBot'         => 'Meta',
		'GoogleOther'         => 'GoogleOther',
		'CopilotBot'          => 'Copilot',
		'Copilot-User'        => 'Copilot',
		'Bravebot'            => 'Brave',
		'PhindBot'            => 'Phind',
		'GrokBot'             => 'Grok',
		'xAI-Bot'             => 'Grok',
		'QwenBot'             => 'Qwen',
		'YouBot'              => 'You.com',
		'AndiBot'             => 'Andi',
		'CCBot'               => 'CommonCrawl',
		'GPTBot'              => 'GPTBot',
		'Bingbot'             => 'Bing',
	);

	/**
	 * Filter patterns (key = UA fragment, value = canonical name).
	 *
	 * @param array $map Patterns.
	 */
	return apply_filters( 'crawlwatch_bot_patterns', $map );
}

/**
 * AI referrer domains => label.
 *
 * @return array
 */
function crawlwatch_get_referral_domains() {
	$map = array(
		'chatgpt.com'           => 'ChatGPT',
		'chat.openai.com'       => 'ChatGPT',
		'perplexity.ai'         => 'Perplexity',
		'gemini.google.com'     => 'Gemini',
		'bard.google.com'       => 'Gemini',
		'ai.google.dev'         => 'Gemini',
		'copilot.microsoft.com' => 'Copilot',
		'claude.ai'             => 'Claude',
		'you.com'               => 'You.com',
		'phind.com'             => 'Phind',
		'poe.com'               => 'Poe',
	);

	/**
	 * Filter referral domains.
	 *
	 * @param array $map Domains.
	 */
	return apply_filters( 'crawlwatch_referral_domains', $map );
}
