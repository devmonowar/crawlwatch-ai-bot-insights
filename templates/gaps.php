<?php
/**
 * Gaps template. Variables: $no_excerpt (post ids), $no_alt (attachment ids).
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- view template: all variables are provided by the render method and execute in function scope.
?>
<div class="wrap crawlwatch">
	<h1><?php esc_html_e( 'CrawlWatch – AI Readiness', 'crawlwatch-ai-bot-insights' ); ?></h1>
	<p class="description"><?php esc_html_e( 'Posts missing excerpts and images missing alt text. Fix them where they live — nothing is auto-changed.', 'crawlwatch-ai-bot-insights' ); ?></p>

	<div class="crawlwatch-panel">
		<h2><?php esc_html_e( 'Posts without excerpts', 'crawlwatch-ai-bot-insights' ); ?></h2>
		<?php if ( empty( $no_excerpt ) ) : ?>
			<p>✓ <?php esc_html_e( 'Every recent post has an excerpt.', 'crawlwatch-ai-bot-insights' ); ?></p>
		<?php else : ?>
			<p>
				<?php
				/* translators: %s: number of posts, formatted. */
				echo esc_html( sprintf( _n( '%s post needs excerpts (latest 50 checked).', '%s posts need excerpts (latest 50 checked).', count( $no_excerpt ), 'crawlwatch-ai-bot-insights' ), number_format_i18n( count( $no_excerpt ) ) ) );
				?>
			</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Post', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Date', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Fix', 'crawlwatch-ai-bot-insights' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $no_excerpt as $pid ) : ?>
						<tr>
							<td><?php echo esc_html( get_the_title( $pid ) ); ?></td>
							<td><?php echo esc_html( get_the_date( '', $pid ) ); ?></td>
							<td><a href="<?php echo esc_url( get_edit_post_link( $pid, 'raw' ) ); ?>"><?php esc_html_e( 'Edit →', 'crawlwatch-ai-bot-insights' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>

	<div class="crawlwatch-panel">
		<h2><?php esc_html_e( 'Images without alt text', 'crawlwatch-ai-bot-insights' ); ?></h2>
		<?php if ( empty( $no_alt ) ) : ?>
			<p>✓ <?php esc_html_e( 'Every recent image has alt text.', 'crawlwatch-ai-bot-insights' ); ?></p>
		<?php else : ?>
			<p>
				<?php
				/* translators: %s: number of images, formatted. */
				echo esc_html( sprintf( _n( '%s image needs alt text (latest 50 checked).', '%s images need alt text (latest 50 checked).', count( $no_alt ), 'crawlwatch-ai-bot-insights' ), number_format_i18n( count( $no_alt ) ) ) );
				?>
			</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Image', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Date', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Fix', 'crawlwatch-ai-bot-insights' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $no_alt as $aid ) : ?>
						<tr>
							<td><?php echo esc_html( get_the_title( $aid ) ); ?></td>
							<td><?php echo esc_html( get_the_date( '', $aid ) ); ?></td>
							<td><a href="<?php echo esc_url( get_edit_post_link( $aid, 'raw' ) ); ?>"><?php esc_html_e( 'Edit →', 'crawlwatch-ai-bot-insights' ); ?></a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
