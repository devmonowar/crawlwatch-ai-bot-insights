<?php
/**
 * Overview template. Variables: $days, $since, $total, $referrals, $unique, $top_bots, $recent, $score, $wins, $max_hits.
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
			/* translators: %s: start date and time in UTC. */
			echo esc_html( sprintf( __( 'Since %s (UTC)', 'crawlwatch-ai-bot-insights' ), $since ) );
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
						<th><?php esc_html_e( 'Time (UTC)', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Bot', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'Type', 'crawlwatch-ai-bot-insights' ); ?></th>
						<th><?php esc_html_e( 'URL', 'crawlwatch-ai-bot-insights' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent as $r ) : ?>
						<tr>
							<td><?php echo esc_html( $r['log_time'] ); ?></td>
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
