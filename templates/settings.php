<?php
/**
 * Settings template. Variables: $settings, $rows_count.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- view template: all variables are provided by the render method and execute in function scope.
?>
<div class="wrap crawlwatch">
	<h1><?php esc_html_e( 'CrawlWatch – Settings', 'crawlwatch-ai-bot-insights' ); ?></h1>

	<?php
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only saved flag, no state change.
	$saved_raw = isset( $_GET['updated'] ) ? sanitize_key( wp_unslash( $_GET['updated'] ) ) : '';
	if ( '1' === sanitize_key( $saved_raw ) ) :
		?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Settings saved.', 'crawlwatch-ai-bot-insights' ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="crawlwatch_save_settings" />
		<?php wp_nonce_field( 'crawlwatch_settings', 'crawlwatch_settings_nonce' ); ?>

		<table class="form-table">
			<tr>
				<th><?php esc_html_e( 'Tracking', 'crawlwatch-ai-bot-insights' ); ?></th>
				<td>
					<label><input type="checkbox" name="logging_enabled" value="1" <?php checked( ! empty( $settings['logging_enabled'] ) ); ?> /> <?php esc_html_e( 'Log AI crawler visits (local only)', 'crawlwatch-ai-bot-insights' ); ?></label><br />
					<label><input type="checkbox" name="llms_enabled" value="1" <?php checked( ! empty( $settings['llms_enabled'] ) ); ?> /> <?php esc_html_e( 'Serve /llms.txt and /ai.txt', 'crawlwatch-ai-bot-insights' ); ?></label>
				</td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Keep logs for', 'crawlwatch-ai-bot-insights' ); ?></th>
				<td>
					<?php foreach ( crawlwatch_allowed_retentions() as $d => $label ) : ?>
						<label style="display:block;"><input type="radio" name="retention_days" value="<?php echo esc_attr( $d ); ?>" <?php checked( (int) $settings['retention_days'], (int) $d ); ?> /> <?php echo esc_html( $label ); ?></label>
					<?php endforeach; ?>
					<p class="description">
						<?php
						/* translators: %s: stored log row count, formatted. */
						echo esc_html( sprintf( __( 'Stored rows right now: %s. 90/180 days can grow the DB on busy sites.', 'crawlwatch-ai-bot-insights' ), number_format_i18n( $rows_count ) ) );
						?>
					</p>
				</td>
			</tr>
		<tr>
			<th><?php esc_html_e( 'Weekly digest', 'crawlwatch-ai-bot-insights' ); ?></th>
			<td>
				<label><input type="checkbox" name="digest_enabled" value="1" <?php checked( ! empty( $settings['digest_enabled'] ) ); ?> /> <?php esc_html_e( 'Email me a weekly AI summary', 'crawlwatch-ai-bot-insights' ); ?></label><br />
				<label><?php esc_html_e( 'Send to', 'crawlwatch-ai-bot-insights' ); ?> <input type="email" name="digest_email" value="<?php echo esc_attr( isset( $settings['digest_email'] ) ? $settings['digest_email'] : '' ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email', '' ) ); ?>" class="regular-text" /></label>
				<p class="description"><?php esc_html_e( 'Off by default. Empty address uses the site admin email.', 'crawlwatch-ai-bot-insights' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'On uninstall', 'crawlwatch-ai-bot-insights' ); ?></th>
				<td><label><input type="checkbox" name="delete_on_uninstall" value="1" <?php checked( ! empty( $settings['delete_on_uninstall'] ) ); ?> /> <?php esc_html_e( 'Delete all logs and settings', 'crawlwatch-ai-bot-insights' ); ?></label></td>
			</tr>
		</table>

		<p class="description"><?php esc_html_e( 'Privacy: IPs are SHA-256 hashed after anonymizing (IPv4 last octet, IPv6 /64). Nothing leaves your server.', 'crawlwatch-ai-bot-insights' ); ?></p>

		<p><button class="button button-primary" type="submit"><?php esc_html_e( 'Save settings', 'crawlwatch-ai-bot-insights' ); ?></button></p>
	</form>

	<hr />

	<div class="crawlwatch-danger-zone">
		<h2><?php esc_html_e( 'Danger zone', 'crawlwatch-ai-bot-insights' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Delete all logs now?', 'crawlwatch-ai-bot-insights' ) ); ?>');">
			<input type="hidden" name="action" value="crawlwatch_clear_logs" />
			<?php wp_nonce_field( 'crawlwatch_settings', 'crawlwatch_settings_nonce' ); ?>
			<p><button class="button crawlwatch-danger-button" type="submit"><?php esc_html_e( 'Clear all logs now', 'crawlwatch-ai-bot-insights' ); ?></button></p>
		</form>
	</div>
</div>
