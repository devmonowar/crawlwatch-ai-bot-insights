<?php
/**
 * Bots template. Variables: $bot, $q, $paged, $per, $bots_list, $total, $pages, $rows, $robots_rules, $physical, $tab, $grouped.
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
	<p class="description"><?php esc_html_e( 'crawl = an AI bot fetched a page. referral = a visitor came from an AI chat. Block sets one global robots.txt rule for that bot.', 'crawlwatch-ai-bot-insights' ); ?></p>

	<?php
	$visits_url = add_query_arg(
		array(
			'page' => 'crawlwatch-bots',
			'tab'  => 'visits',
			'bot'  => $bot,
			'q'    => $q,
		),
		admin_url( 'admin.php' )
	);
	$bots_url   = add_query_arg(
		array(
			'page' => 'crawlwatch-bots',
			'tab'  => 'bots',
			'bot'  => $bot,
			'q'    => $q,
		),
		admin_url( 'admin.php' )
	);
	?>
	<h2 class="nav-tab-wrapper">
		<a class="nav-tab <?php echo 'visits' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $visits_url ); ?>"><?php esc_html_e( 'Visits', 'crawlwatch-ai-bot-insights' ); ?></a>
		<a class="nav-tab <?php echo 'bots' === $tab ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( $bots_url ); ?>"><?php esc_html_e( 'Grouped by bot', 'crawlwatch-ai-bot-insights' ); ?></a>
	</h2>

	<form method="get" action="">
		<input type="hidden" name="page" value="crawlwatch-bots" />
		<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
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
			<a class="button" href="<?php echo esc_url( add_query_arg( array( 'page' => 'crawlwatch-bots', 'tab' => $tab ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Reset', 'crawlwatch-ai-bot-insights' ); ?></a>
		<?php endif; ?>
	</form>

	<?php if ( 'bots' === $tab ) : ?>
		<?php if ( empty( $grouped ) ) : ?>
			<p><?php esc_html_e( 'No bots in this view yet.', 'crawlwatch-ai-bot-insights' ); ?></p>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Bot', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Hits', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Last seen', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Top URL', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Action', 'crawlwatch-ai-bot-insights' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $grouped as $g ) : ?>
						<?php
						$g_name    = isset( $g['bot_name'] ) ? $g['bot_name'] : '';
						$g_token   = CrawlWatch_Robots::token_for_bot( $g_name );
						$g_blocked = '' !== $g_token && isset( $robots_rules[ $g_token ] ) && 'block' === $robots_rules[ $g_token ];
						$g_detail  = add_query_arg(
							array(
								'page' => 'crawlwatch-bots',
								'tab'  => 'visits',
								'bot'  => $g_name,
							),
							admin_url( 'admin.php' )
						);
						?>
						<tr>
							<td><a href="<?php echo esc_url( $g_detail ); ?>"><?php echo esc_html( $g_name ); ?></a></td>
							<td><?php echo esc_html( number_format_i18n( isset( $g['hits'] ) ? (int) $g['hits'] : 0 ) ); ?></td>
							<td><?php echo esc_html( crawlwatch_display_time( isset( $g['last_seen'] ) ? $g['last_seen'] : '' ) ); ?></td>
							<td><?php echo esc_html( isset( $g['top_url'] ) ? $g['top_url'] : '' ); ?></td>
							<td>
								<?php if ( '' === $g_token ) : ?>
									—
								<?php elseif ( $physical ) : ?>
									<button class="button button-small" type="button" disabled="disabled" title="<?php echo esc_attr__( 'Physical robots.txt found — virtual rules do not apply.', 'crawlwatch-ai-bot-insights' ); ?>"><?php echo $g_blocked ? esc_html__( 'Blocked', 'crawlwatch-ai-bot-insights' ) : esc_html__( 'Block', 'crawlwatch-ai-bot-insights' ); ?></button>
								<?php else : ?>
									<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo ( ! $g_blocked && 'Google-Extended' === $g_token ) ? ' onsubmit="return confirm(\'' . esc_js( __( 'Blocking this bot can affect SEO. Continue?', 'crawlwatch-ai-bot-insights' ) ) . '\');"' : ''; ?>>
										<input type="hidden" name="action" value="crawlwatch_toggle_bot" />
										<input type="hidden" name="token" value="<?php echo esc_attr( $g_token ); ?>" />
										<input type="hidden" name="bot" value="<?php echo esc_attr( $bot ); ?>" />
										<input type="hidden" name="q" value="<?php echo esc_attr( $q ); ?>" />
										<input type="hidden" name="paged" value="<?php echo esc_attr( $paged ); ?>" />
								<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
										<?php wp_nonce_field( 'crawlwatch_toggle_bot', 'crawlwatch_toggle_nonce' ); ?>
										<button class="button button-small" type="submit"><?php echo $g_blocked ? esc_html__( 'Unblock', 'crawlwatch-ai-bot-insights' ) : esc_html__( 'Block', 'crawlwatch-ai-bot-insights' ); ?></button>
									</form>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	<?php else : ?>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice flags.
	$toggled_raw = isset( $_GET['toggled'] ) ? wp_unslash( $_GET['toggled'] ) : '';
	$toggled     = is_string( $toggled_raw ) ? $toggled_raw : '';
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice flag.
	$state_raw = isset( $_GET['state'] ) ? sanitize_key( wp_unslash( $_GET['state'] ) ) : '';
	if ( '' !== $toggled && array_key_exists( $toggled, CrawlWatch_Robots::managed_bots() ) && in_array( $state_raw, array( 'blocked', 'unblocked' ), true ) ) :
		?>
		<div class="notice notice-success inline"><p>
			<?php
			if ( 'blocked' === $state_raw ) {
				/* translators: %s: bot token. */
				echo esc_html( sprintf( __( '%s is now blocked in robots.txt. Respectful bots honor this; it is a request, not a firewall.', 'crawlwatch-ai-bot-insights' ), $toggled ) );
			} else {
				/* translators: %s: bot token. */
				echo esc_html( sprintf( __( '%s is unblocked again.', 'crawlwatch-ai-bot-insights' ), $toggled ) );
			}
			?>
		</p></div>
	<?php endif; ?>

	<p>
		<?php
		/* translators: %s: number of visits, formatted. */
		echo esc_html( sprintf( __( '%s visits found.', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $total ) ) );
		?>
	</p>

	<?php if ( empty( $rows ) ) : ?>
		<?php if ( '' === $bot && '' === $q ) : ?>
			<p><?php echo wp_kses_post( sprintf( /* translators: %s: Files page URL. */ __( 'No AI visits logged yet. They appear here automatically — meanwhile check your <a href="%s">llms.txt</a>.', 'crawlwatch-ai-bot-insights' ), esc_url( add_query_arg( array( 'page' => 'crawlwatch-files' ), admin_url( 'admin.php' ) ) ) ) ); ?></p>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing found for these filters.', 'crawlwatch-ai-bot-insights' ); ?></p>
		<?php endif; ?>
	<?php else : ?>
		<table class="widefat striped">
			<thead>
				<tr>
					<th>
						<?php
						/* translators: %s: site timezone label, e.g. +06:00. */
						echo esc_html( sprintf( __( 'Time (%s)', 'crawlwatch-ai-bot-insights' ), crawlwatch_tz_label() ) );
						?>
					</th>
					<th><?php esc_html_e( 'Bot', 'crawlwatch-ai-bot-insights' ); ?></th>
					<th><?php esc_html_e( 'Type', 'crawlwatch-ai-bot-insights' ); ?></th>
					<th><?php esc_html_e( 'URL', 'crawlwatch-ai-bot-insights' ); ?></th>
					<th><?php esc_html_e( 'Referrer', 'crawlwatch-ai-bot-insights' ); ?></th>
					<th><?php esc_html_e( 'Action', 'crawlwatch-ai-bot-insights' ); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ( $rows as $r ) : ?>
				<?php
				$row_token = ( isset( $r['bot_type'] ) && 'crawl' === $r['bot_type'] && isset( $r['bot_name'] ) )
					? CrawlWatch_Robots::token_for_bot( $r['bot_name'] )
					: '';
				$row_blocked = '' !== $row_token && isset( $robots_rules[ $row_token ] ) && 'block' === $robots_rules[ $row_token ];
				?>
				<tr>
					<td><?php echo esc_html( crawlwatch_display_time( isset( $r['log_time'] ) ? $r['log_time'] : '' ) ); ?></td>
					<td><?php echo esc_html( $r['bot_name'] ); ?></td>
					<td><?php echo esc_html( $r['bot_type'] ); ?></td>
					<td><?php echo esc_html( $r['url'] ); ?></td>
					<td><?php echo esc_html( $r['referrer'] ); ?></td>
					<td>
						<?php if ( '' === $row_token ) : ?>
							—
						<?php elseif ( $physical ) : ?>
							<button class="button button-small" type="button" disabled="disabled" title="<?php echo esc_attr__( 'Physical robots.txt found — virtual rules do not apply.', 'crawlwatch-ai-bot-insights' ); ?>"><?php echo $row_blocked ? esc_html__( 'Blocked', 'crawlwatch-ai-bot-insights' ) : esc_html__( 'Block', 'crawlwatch-ai-bot-insights' ); ?></button>
						<?php else : ?>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"<?php echo ( ! $row_blocked && 'Google-Extended' === $row_token ) ? ' onsubmit="return confirm(\'' . esc_js( __( 'Blocking this bot can affect SEO. Continue?', 'crawlwatch-ai-bot-insights' ) ) . '\');"' : ''; ?>>
								<input type="hidden" name="action" value="crawlwatch_toggle_bot" />
								<input type="hidden" name="token" value="<?php echo esc_attr( $row_token ); ?>" />
								<input type="hidden" name="bot" value="<?php echo esc_attr( $bot ); ?>" />
								<input type="hidden" name="q" value="<?php echo esc_attr( $q ); ?>" />
								<input type="hidden" name="paged" value="<?php echo esc_attr( $paged ); ?>" />
								<input type="hidden" name="tab" value="<?php echo esc_attr( $tab ); ?>" />
								<?php wp_nonce_field( 'crawlwatch_toggle_bot', 'crawlwatch_toggle_nonce' ); ?>
								<button class="button button-small" type="submit"><?php echo $row_blocked ? esc_html__( 'Unblock', 'crawlwatch-ai-bot-insights' ) : esc_html__( 'Block', 'crawlwatch-ai-bot-insights' ); ?></button>
							</form>
						<?php endif; ?>
					</td>
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
	<?php endif; ?>
</div>
