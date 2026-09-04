<?php
/**
 * Setup wizard template. Variables: $step, $score.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- view template: all variables are provided by the render method and execute in function scope.
$crawlwatch_step2_url = add_query_arg(
	array(
		'page' => 'crawlwatch-setup',
		'step' => 2,
	),
	admin_url( 'admin.php' )
);
?>
<div class="wrap crawlwatch">
	<h1><?php esc_html_e( 'Welcome to CrawlWatch', 'crawlwatch-ai-bot-insights' ); ?></h1>

	<?php if ( 1 === $step ) : ?>
		<p><?php esc_html_e( 'AI bots like GPTBot, Claude and Perplexity visit your site to learn and recommend it. CrawlWatch tracks them locally – no API key, 100% free.', 'crawlwatch-ai-bot-insights' ); ?></p>
		<p><a class="button button-primary button-hero" href="<?php echo esc_url( $crawlwatch_step2_url ); ?>"><?php esc_html_e( 'Start scan', 'crawlwatch-ai-bot-insights' ); ?></a></p>
	<?php elseif ( 2 === $step ) : ?>
		<p><?php esc_html_e( 'We will scan your content and generate llms.txt. This runs once.', 'crawlwatch-ai-bot-insights' ); ?></p>
		<form method="post" action="<?php echo esc_url( $crawlwatch_step2_url ); ?>">
			<?php wp_nonce_field( 'crawlwatch_setup', 'crawlwatch_setup_nonce' ); ?>
			<button class="button button-primary button-hero" type="submit"><?php esc_html_e( 'Generate & continue', 'crawlwatch-ai-bot-insights' ); ?></button>
		</form>
	<?php else : ?>
		<p>
			<?php
			/* translators: %d: readiness score from 0 to 100. */
			echo esc_html( sprintf( __( 'You are AI-ready! Score: %d/100', 'crawlwatch-ai-bot-insights' ), $score ) );
			?>
		</p>
		<p>
			<a class="button button-primary" href="<?php echo esc_url( add_query_arg( array( 'page' => 'crawlwatch' ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'See Quick Wins', 'crawlwatch-ai-bot-insights' ); ?></a>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'crawlwatch-bots' ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'See bot visits', 'crawlwatch-ai-bot-insights' ); ?></a>
		</p>
	<?php endif; ?>
</div>
