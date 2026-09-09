<?php
/**
 * Bots template. Variables: $bot, $q, $paged, $per, $bots_list, $total, $pages, $rows.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- view template: all variables are provided by the render method and execute in function scope.
?>
<div class="wrap crawlwatch">
	<h1><?php esc_html_e( 'CrawlWatch – Bots', 'crawlwatch-ai-bot-insights' ); ?></h1>

	<form method="get" action="">
		<input type="hidden" name="page" value="crawlwatch-bots" />
		<label>
			<?php esc_html_e( 'Bot:', 'crawlwatch-ai-bot-insights' ); ?>
			<select name="bot">
				<option value=""><?php esc_html_e( 'All bots', 'crawlwatch-ai-bot-insights' ); ?></option>
				<?php foreach ( $bots_list as $name ) : ?>
					<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $bot, $name ); ?>><?php echo esc_html( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</label>
		<label>
			<?php esc_html_e( 'URL contains:', 'crawlwatch-ai-bot-insights' ); ?>
			<input type="search" name="q" value="<?php echo esc_attr( $q ); ?>" maxlength="100" />
		</label>
		<button class="button button-primary" type="submit"><?php esc_html_e( 'Filter', 'crawlwatch-ai-bot-insights' ); ?></button>
		<?php wp_nonce_field( 'crawlwatch_export', 'crawlwatch_export_nonce' ); ?>
		<button class="button" type="submit" formaction="<?php echo esc_url( add_query_arg( array( 'action' => 'crawlwatch_export_csv' ), admin_url( 'admin-post.php' ) ) ); ?>"><?php esc_html_e( 'Export CSV (up to 5000 rows)', 'crawlwatch-ai-bot-insights' ); ?></button>
		<?php if ( '' !== $bot || '' !== $q ) : ?>
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'crawlwatch-bots' ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Reset', 'crawlwatch-ai-bot-insights' ); ?></a>
		<?php endif; ?>
	</form>

	<p>
		<?php
		/* translators: %s: number of visits, formatted. */
		echo esc_html( sprintf( __( '%s visits found.', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $total ) ) );
		?>
	</p>

	<?php if ( empty( $rows ) ) : ?>
		<p><?php esc_html_e( 'Nothing found for these filters.', 'crawlwatch-ai-bot-insights' ); ?></p>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Time (UTC)', 'crawlwatch-ai-bot-insights' ); ?></th>
					<th><?php esc_html_e( 'Bot', 'crawlwatch-ai-bot-insights' ); ?></th>
					<th><?php esc_html_e( 'Type', 'crawlwatch-ai-bot-insights' ); ?></th>
					<th><?php esc_html_e( 'URL', 'crawlwatch-ai-bot-insights' ); ?></th>
					<th><?php esc_html_e( 'Referrer', 'crawlwatch-ai-bot-insights' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rows as $r ) : ?>
					<tr>
						<td><?php echo esc_html( $r['log_time'] ); ?></td>
						<td><?php echo esc_html( $r['bot_name'] ); ?></td>
						<td><?php echo esc_html( $r['bot_type'] ); ?></td>
						<td><?php echo esc_html( $r['url'] ); ?></td>
						<td><?php echo esc_html( $r['referrer'] ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<div class="tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num">
					<?php
					/* translators: 1: current page number, 2: total pages, both formatted. */
					echo esc_html( sprintf( __( 'Page %1$s of %2$s', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $paged ), number_format_i18n( $pages ) ) );
					?>
				</span>
				<?php
				$crawlwatch_prev_url = add_query_arg(
					array(
						'page'  => 'crawlwatch-bots',
						'bot'   => $bot,
						'q'     => $q,
						'paged' => $paged - 1,
					),
					admin_url( 'admin.php' )
				);
				$crawlwatch_next_url = add_query_arg(
					array(
						'page'  => 'crawlwatch-bots',
						'bot'   => $bot,
						'q'     => $q,
						'paged' => $paged + 1,
					),
					admin_url( 'admin.php' )
				);
				?>
				<?php if ( $paged > 1 ) : ?>
					<a class="button" href="<?php echo esc_url( $crawlwatch_prev_url ); ?>">‹</a>
				<?php endif; ?>
				<?php if ( $paged < $pages ) : ?>
					<a class="button" href="<?php echo esc_url( $crawlwatch_next_url ); ?>">›</a>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
