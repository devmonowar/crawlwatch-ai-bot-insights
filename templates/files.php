<?php
/**
 * Files template. Variables: $llms_content, $llms_url, $ai_url, $llms_full_url, $fallback_url, $managed_bots, $rules, $generated_note.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- view template: all variables are provided by the render method and execute in function scope.
?>
<div class="wrap crawlwatch">
	<h1><?php esc_html_e( 'CrawlWatch – Files', 'crawlwatch-ai-bot-insights' ); ?></h1>
	<p class="description"><?php esc_html_e( 'llms.txt tells AI bots what your site is about. robots.txt controls which AI bots may crawl. No file writes, no code needed.', 'crawlwatch-ai-bot-insights' ); ?></p>

	<?php if ( ! empty( $generated_note ) ) : ?>
		<div class="notice notice-success is-dismissible"><p><?php echo esc_html( $generated_note ); ?></p></div>
	<?php endif; ?>

	<div class="crawlwatch-grid">
		<div class="crawlwatch-panel">
			<h2><?php esc_html_e( 'llms.txt', 'crawlwatch-ai-bot-insights' ); ?></h2>
			<p>
				<a href="<?php echo esc_url( $llms_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $llms_url ); ?></a>
				<button class="button" data-crawlwatch-copy="<?php echo esc_attr( $llms_url ); ?>"><?php esc_html_e( 'Copy URL', 'crawlwatch-ai-bot-insights' ); ?></button>
				<br />
				<a href="<?php echo esc_url( $ai_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $ai_url ); ?></a>
				<br />
				<a href="<?php echo esc_url( $llms_full_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $llms_full_url ); ?></a>
				<span class="description"><?php esc_html_e( '(full catalogue with post content)', 'crawlwatch-ai-bot-insights' ); ?></span>
				<br /><br />
				<span class="description"><?php esc_html_e( 'Plain permalinks? Use instead:', 'crawlwatch-ai-bot-insights' ); ?></span>
				<br />
				<a href="<?php echo esc_url( $fallback_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $fallback_url ); ?></a>
			</p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="crawlwatch_save_files" />
				<?php wp_nonce_field( 'crawlwatch_files', 'crawlwatch_files_nonce' ); ?>
				<textarea name="llms_content" rows="18" cols="80" class="large-text code" spellcheck="false"><?php echo esc_textarea( $llms_content ); ?></textarea>
				<p>
					<button class="button button-primary" type="submit" name="op" value="save"><?php esc_html_e( 'Save', 'crawlwatch-ai-bot-insights' ); ?></button>
					<button class="button" type="submit" name="op" value="regenerate"><?php esc_html_e( 'Regenerate from site', 'crawlwatch-ai-bot-insights' ); ?></button>
				</p>
			</form>
		</div>

		<div class="crawlwatch-panel">
			<h2><?php esc_html_e( 'robots.txt – AI bots', 'crawlwatch-ai-bot-insights' ); ?></h2>
			<p><?php esc_html_e( 'Blocked bots are told Disallow. We only append, never overwrite WordPress core rules.', 'crawlwatch-ai-bot-insights' ); ?></p>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="crawlwatch_save_files" />
				<?php wp_nonce_field( 'crawlwatch_files', 'crawlwatch_files_nonce' ); ?>
				<?php foreach ( $managed_bots as $token => $label ) : ?>
					<label style="display:block;margin:4px 0;">
						<input type="checkbox" name="robots_rules[<?php echo esc_attr( $token ); ?>]" value="block" <?php checked( isset( $rules[ $token ] ) && 'block' === $rules[ $token ] ); ?> />
						<?php
						/* translators: %s: AI bot label, e.g. GPTBot. */
						echo esc_html( sprintf( __( 'Block %s', 'crawlwatch-ai-bot-insights' ), $label ) );
						?>
					</label>
				<?php endforeach; ?>
				<p><button class="button button-primary" type="submit" name="op" value="robots"><?php esc_html_e( 'Save robots rules', 'crawlwatch-ai-bot-insights' ); ?></button></p>
			</form>
			<p><a href="<?php echo esc_url( home_url( '/robots.txt' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Preview robots.txt', 'crawlwatch-ai-bot-insights' ); ?></a></p>
		</div>
	</div>
</div>
