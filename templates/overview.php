<?php
/**
 * Overview template. Variables: $days, $since, $total, $prev_total, $trend, $referrals, $unique, $top_bots, $recent, $score, $wins, $parts, $max_hits, $has_seo, $schema, $woo_active, $woo_top.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- view template: all variables are provided by the render method and execute in function scope.
?>
<div class="wrap crawlwatch">
	<h1><?php esc_html_e( 'CrawlWatch – AI Bot Insights', 'crawlwatch-ai-bot-insights' ); ?></h1>
	<p class="description">
		<?php esc_html_e( 'Which AI bots read your site, which AI referrals arrived, and how AI-ready you are. 100% free.', 'crawlwatch-ai-bot-insights' ); ?>
	</p>

<?php
$crawlwatch_days7_url  = add_query_arg(
	array(
		'page' => 'crawlwatch',
		'days' => 7,
	),
	admin_url( 'admin.php' )
);
$crawlwatch_days30_url = add_query_arg(
	array(
		'page' => 'crawlwatch',
		'days' => 30,
	),
	admin_url( 'admin.php' )
);
?>
	<div class="crawlwatch-filters">
		<a class="button <?php echo 7 === $days ? 'button-primary' : ''; ?>" href="<?php echo esc_url( $crawlwatch_days7_url ); ?>"><?php esc_html_e( 'Last 7 days', 'crawlwatch-ai-bot-insights' ); ?></a>
		<a class="button <?php echo 30 === $days ? 'button-primary' : ''; ?>" href="<?php echo esc_url( $crawlwatch_days30_url ); ?>"><?php esc_html_e( 'Last 30 days', 'crawlwatch-ai-bot-insights' ); ?></a>
		<span class="crawlwatch-since">
			<?php
			/* translators: %s: start date and time in site timezone. */
			echo esc_html( sprintf( __( 'Since %s', 'crawlwatch-ai-bot-insights' ), crawlwatch_display_time( $since ) ) );
			?>
		</span>
		<span class="crawlwatch-since">
			<?php
			if ( $prev_total > 0 ) {
				$pct = $total >= $prev_total
					? (int) round( ( ( $total - $prev_total ) / $prev_total ) * 100 )
					: -(int) round( ( ( $prev_total - $total ) / $prev_total ) * 100 );
				/* translators: 1: previous period hits, formatted. 2: change percent with sign, e.g. +20% or -10%. 3: days count. */
				$change = ( $pct >= 0 ? '+' : '' ) . $pct . '%';
				echo esc_html( sprintf( __( 'vs %1$s (%2$s) in the previous %3$d days', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $prev_total ), $change, $days ) );
			} elseif ( $total > 0 ) {
				/* translators: %d: days count. */
				echo esc_html( sprintf( __( 'all new in the last %d days', 'crawlwatch-ai-bot-insights' ), $days ) );
			}
			?>
		</span>
	</div>

	<div class="crawlwatch-cards">
		<div class="crawlwatch-card">
			<div class="crawlwatch-num"><?php echo esc_html( number_format_i18n( $total ) ); ?></div>
			<div class="crawlwatch-label"><?php esc_html_e( 'Total AI hits', 'crawlwatch-ai-bot-insights' ); ?></div>
		</div>
		<div class="crawlwatch-card">
			<div class="crawlwatch-num"><?php echo esc_html( number_format_i18n( $unique ) ); ?></div>
			<div class="crawlwatch-label"><?php esc_html_e( 'Unique bots', 'crawlwatch-ai-bot-insights' ); ?></div>
		</div>
		<div class="crawlwatch-card">
			<div class="crawlwatch-num"><?php echo esc_html( number_format_i18n( $referrals ) ); ?></div>
			<div class="crawlwatch-label"><?php esc_html_e( 'AI referrals', 'crawlwatch-ai-bot-insights' ); ?></div>
		</div>
		<div class="crawlwatch-card crawlwatch-score">
			<div class="crawlwatch-num"><?php echo esc_html( number_format_i18n( $score ) ); ?>/100</div>
			<div class="crawlwatch-label"><?php esc_html_e( 'Readiness score', 'crawlwatch-ai-bot-insights' ); ?></div>
		</div>
	</div>

	<?php
	$trend_max = ! empty( $trend ) ? max( 1, max( array_map( 'intval', array_values( $trend ) ) ) ) : 1;
	$trend_n   = ! empty( $trend ) ? count( $trend ) : 0;
	$trend_bw  = $trend_n > 0 ? 600 / $trend_n : 600;
	$trend_i   = 0;
	?>
	<div class="crawlwatch-panel">
		<h2><?php esc_html_e( 'AI hits per day', 'crawlwatch-ai-bot-insights' ); ?></h2>
		<?php if ( 0 === $trend_n ) : ?>
			<p><?php esc_html_e( 'No data in this period.', 'crawlwatch-ai-bot-insights' ); ?></p>
		<?php else : ?>
			<svg class="crawlwatch-chart" viewBox="0 0 600 110" width="100%" role="img" aria-label="<?php echo esc_attr__( 'AI hits per day chart', 'crawlwatch-ai-bot-insights' ); ?>">
				<line x1="0" y1="80" x2="600" y2="80" stroke="#c3c4c7" stroke-width="1" />
				<?php foreach ( $trend as $trend_date => $trend_hits ) : ?>
					<?php
					$trend_h   = (int) round( ( (int) $trend_hits / $trend_max ) * 64 );
					$trend_x   = (int) round( $trend_i * $trend_bw + 1 );
					$trend_w   = max( 1, (int) round( $trend_bw - 2 ) );
					$trend_tip = $trend_date . ': ' . number_format_i18n( (int) $trend_hits );
					++$trend_i;
					?>
					<rect x="<?php echo esc_attr( $trend_x ); ?>" y="<?php echo esc_attr( 80 - $trend_h ); ?>" width="<?php echo esc_attr( $trend_w ); ?>" height="<?php echo esc_attr( max( $trend_h, (int) $trend_hits > 0 ? 2 : 0 ) ); ?>" fill="#2271b1"><title><?php echo esc_html( $trend_tip ); ?></title></rect>
				<?php endforeach; ?>
				<text x="0" y="96" font-size="13" fill="#646970"><?php echo esc_html( array_key_first( $trend ) ); ?></text>
				<text x="600" y="96" font-size="13" fill="#646970" text-anchor="end"><?php echo esc_html( array_key_last( $trend ) ); ?></text>
			</svg>
		<?php endif; ?>
	</div>

	<?php if ( 0 === (int) $total ) : ?>
	<div class="crawlwatch-panel">
		<h2><?php esc_html_e( 'No AI visits yet — here is how they arrive', 'crawlwatch-ai-bot-insights' ); ?></h2>
		<ol>
			<li><?php echo wp_kses_post( sprintf( /* translators: %s: Files page URL. */ __( 'Check your <a href="%s">llms.txt</a> — this is what AI reads first.', 'crawlwatch-ai-bot-insights' ), esc_url( add_query_arg( array( 'page' => 'crawlwatch-files' ), admin_url( 'admin.php' ) ) ) ) ); ?></li>
			<li><?php esc_html_e( 'Share a post link anywhere — AI crawlers follow links to discover you.', 'crawlwatch-ai-bot-insights' ); ?></li>
			<li><?php esc_html_e( 'Meanwhile, raise your Readiness score below so AI cites you well.', 'crawlwatch-ai-bot-insights' ); ?></li>
		</ol>
	</div>
	<?php endif; ?>

	<div class="crawlwatch-panel">
		<h2><?php esc_html_e( 'Score breakdown', 'crawlwatch-ai-bot-insights' ); ?></h2>
		<?php if ( empty( $parts ) ) : ?>
			<p><?php esc_html_e( 'Breakdown unavailable.', 'crawlwatch-ai-bot-insights' ); ?></p>
		<?php else : ?>
			<ul>
				<?php foreach ( $parts as $part ) : ?>
					<?php
					$part_earned = isset( $part['earned'] ) ? (int) $part['earned'] : 0;
					$part_max    = isset( $part['max'] ) ? (int) $part['max'] : 0;
					$part_dot    = $part_earned >= $part_max ? '#46b450' : '#dba617';
					?>
					<li>
						<span style="color:<?php echo esc_attr( $part_dot ); ?>;">●</span>
						<?php echo esc_html( isset( $part['label'] ) ? $part['label'] : '' ); ?>
						<strong><?php echo esc_html( number_format_i18n( $part_earned ) . '/' . number_format_i18n( $part_max ) ); ?></strong>
						<?php if ( ! empty( $part['url'] ) ) : ?>
							<a href="<?php echo esc_url( $part['url'] ); ?>"><?php esc_html_e( 'Fix →', 'crawlwatch-ai-bot-insights' ); ?></a>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<div class="crawlwatch-panel">
		<h2><?php esc_html_e( 'Quick Wins – ranked by impact', 'crawlwatch-ai-bot-insights' ); ?></h2>
		<?php if ( empty( $wins ) ) : ?>
			<p>✓ <?php esc_html_e( 'All checks pass. Nice!', 'crawlwatch-ai-bot-insights' ); ?></p>
		<?php else : ?>
			<ol>
				<?php foreach ( $wins as $w ) : ?>
					<li>
						<strong>+<?php echo esc_html( number_format_i18n( (int) $w['points'] ) ); ?></strong>
						<?php echo esc_html( $w['text'] ); ?>
						<a href="<?php echo esc_url( $w['url'] ); ?>"><?php esc_html_e( 'Fix →', 'crawlwatch-ai-bot-insights' ); ?></a>
					</li>
				<?php endforeach; ?>
			</ol>
		<?php endif; ?>
	</div>

	<div class="crawlwatch-grid">
		<div class="crawlwatch-panel">
			<h2><?php esc_html_e( 'Top bots', 'crawlwatch-ai-bot-insights' ); ?></h2>
			<?php if ( empty( $top_bots ) ) : ?>
				<p><?php esc_html_e( 'No AI hits yet in this period. Share a post on social or generate llms.txt so AI can find you.', 'crawlwatch-ai-bot-insights' ); ?></p>
			<?php else : ?>
				<?php foreach ( $top_bots as $row ) : ?>
					<?php
					$bot  = isset( $row['bot_name'] ) ? $row['bot_name'] : '';
					$hits = isset( $row['hits'] ) ? (int) $row['hits'] : 0;
					$pct  = $max_hits > 0 ? round( ( $hits / $max_hits ) * 100 ) : 0;
					?>
					<div class="crawlwatch-bar-row">
						<span class="crawlwatch-bar-name"><?php echo esc_html( $bot ); ?></span>
						<span class="crawlwatch-bar-track"><span class="crawlwatch-bar-fill" style="width:<?php echo esc_attr( $pct ); ?>%"></span></span>
						<span class="crawlwatch-bar-hits"><?php echo esc_html( number_format_i18n( $hits ) ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div class="crawlwatch-panel crawlwatch-wide">
			<h2><?php esc_html_e( 'Schema gaps', 'crawlwatch-ai-bot-insights' ); ?></h2>
			<?php
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice flag.
			$schema_msg = isset( $_GET['cw_msg'] ) ? sanitize_key( wp_unslash( $_GET['cw_msg'] ) ) : '';
			if ( 'schema' === $schema_msg ) :
				?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'Schema report updated.', 'crawlwatch-ai-bot-insights' ); ?></p></div>
			<?php endif; ?>
			<?php if ( $has_seo ) : ?>
				<p class="description"><?php esc_html_e( 'An SEO plugin is active, so schema is likely covered. The scan below verifies each post.', 'crawlwatch-ai-bot-insights' ); ?></p>
			<?php endif; ?>
			<?php
			$schema_rows = isset( $schema['rows'] ) && is_array( $schema['rows'] ) ? $schema['rows'] : array();
			if ( empty( $schema_rows ) ) :
				?>
				<p><?php esc_html_e( 'Not scanned yet. The scan fetches your latest 10 posts and checks each for schema markup.', 'crawlwatch-ai-bot-insights' ); ?></p>
			<?php else : ?>
				<?php
				$schema_missing = array();
				$schema_unreach = 0;
				foreach ( $schema_rows as $sr ) {
					if ( empty( $sr['reachable'] ) ) {
						++$schema_unreach;
					} elseif ( empty( $sr['has_schema'] ) ) {
						$schema_missing[] = $sr;
					}
				}
				$schema_ok = count( $schema_rows ) - count( $schema_missing ) - $schema_unreach;
				/* translators: 1: posts with schema, 2: posts scanned. */
				echo '<p>' . esc_html( sprintf( __( '%1$d of %2$d scanned posts have schema markup.', 'crawlwatch-ai-bot-insights' ), $schema_ok, count( $schema_rows ) ) ) . '</p>';
				if ( $schema_unreach > 0 ) {
					/* translators: %s: number of unreachable posts, formatted. */
					echo '<p class="description">' . esc_html( sprintf( __( '%s posts could not be fetched and were skipped.', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $schema_unreach ) ) ) . '</p>';
				}
				?>
				<?php if ( ! empty( $schema_missing ) ) : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Post', 'crawlwatch-ai-bot-insights' ); ?></th>
								<th><?php esc_html_e( 'Type', 'crawlwatch-ai-bot-insights' ); ?></th>
								<th><?php esc_html_e( 'Fix', 'crawlwatch-ai-bot-insights' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $schema_missing as $sm ) : ?>
								<tr>
									<td><?php echo esc_html( isset( $sm['title'] ) ? $sm['title'] : '' ); ?></td>
									<td><?php echo esc_html( isset( $sm['type'] ) ? $sm['type'] : '' ); ?></td>
									<td>
										<?php if ( ! empty( $sm['edit_url'] ) ) : ?>
											<a href="<?php echo esc_url( $sm['edit_url'] ); ?>"><?php esc_html_e( 'Edit →', 'crawlwatch-ai-bot-insights' ); ?></a>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p>✓ <?php esc_html_e( 'Every reachable post has schema markup.', 'crawlwatch-ai-bot-insights' ); ?></p>
				<?php endif; ?>
				<p class="description">
					<?php
					/* translators: %s: human time difference, e.g. "3 hours". */
					echo esc_html( sprintf( __( 'Scanned %s ago.', 'crawlwatch-ai-bot-insights' ), human_time_diff( isset( $schema['scanned_at'] ) ? (int) $schema['scanned_at'] : time() ) ) );
					?>
				</p>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="crawlwatch_scan_schema" />
				<?php wp_nonce_field( 'crawlwatch_schema', 'crawlwatch_schema_nonce' ); ?>
				<button class="button" type="submit"><?php echo empty( $schema_rows ) ? esc_html__( 'Scan now', 'crawlwatch-ai-bot-insights' ) : esc_html__( 'Rescan', 'crawlwatch-ai-bot-insights' ); ?></button>
			</form>
		</div>

		<?php if ( $woo_active ) : ?>
		<div class="crawlwatch-panel">
			<h2><?php esc_html_e( 'Top AI-read products', 'crawlwatch-ai-bot-insights' ); ?></h2>
			<?php if ( empty( $woo_top ) ) : ?>
				<p><?php esc_html_e( 'No AI visits on products yet in the last 30 days.', 'crawlwatch-ai-bot-insights' ); ?></p>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Product', 'crawlwatch-ai-bot-insights' ); ?></th>
							<th><?php esc_html_e( 'AI hits', 'crawlwatch-ai-bot-insights' ); ?></th>
							<th><?php esc_html_e( 'Fix', 'crawlwatch-ai-bot-insights' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $woo_top as $wp ) : ?>
							<tr>
								<td><?php echo esc_html( isset( $wp['title'] ) ? $wp['title'] : '' ); ?></td>
								<td><?php echo esc_html( number_format_i18n( isset( $wp['hits'] ) ? (int) $wp['hits'] : 0 ) ); ?></td>
								<td>
									<?php if ( ! empty( $wp['edit_url'] ) ) : ?>
										<a href="<?php echo esc_url( $wp['edit_url'] ); ?>"><?php esc_html_e( 'Edit →', 'crawlwatch-ai-bot-insights' ); ?></a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="crawlwatch-panel">
			<h2><?php esc_html_e( 'Status', 'crawlwatch-ai-bot-insights' ); ?></h2>
			<ul>
				<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'crawlwatch-files' ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Manage llms.txt + robots.txt →', 'crawlwatch-ai-bot-insights' ); ?></a></li>
				<li><a href="<?php echo esc_url( add_query_arg( array( 'page' => 'crawlwatch-settings' ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Tracking + retention settings →', 'crawlwatch-ai-bot-insights' ); ?></a></li>
			</ul>
		</div>
	</div>

	<div class="crawlwatch-panel">
		<h2><?php esc_html_e( 'Recent AI visits', 'crawlwatch-ai-bot-insights' ); ?></h2>
		<?php if ( empty( $recent ) ) : ?>
			<p><?php esc_html_e( 'Nothing logged yet.', 'crawlwatch-ai-bot-insights' ); ?></p>
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
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent as $r ) : ?>
						<tr>
							<td><?php echo esc_html( crawlwatch_display_time( isset( $r['log_time'] ) ? $r['log_time'] : '' ) ); ?></td>
							<td><?php echo esc_html( $r['bot_name'] ); ?></td>
							<td><?php echo esc_html( $r['bot_type'] ); ?></td>
							<td><?php echo esc_html( $r['url'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
</div>
