<?php
/**
 * Admin: menu + overview page. Assets load only on our screens.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Admin
 */
class CrawlWatch_Admin {

	/**
	 * Hook suffixes for asset gating.
	 *
	 * @var array
	 */
	private static $hooks = array();

	/**
	 * Init hooks.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( CRAWLWATCH_FILE ), array( __CLASS__, 'action_links' ) );
		add_action( 'admin_post_crawlwatch_save_files', array( __CLASS__, 'handle_save_files' ) );
		add_action( 'admin_post_crawlwatch_save_settings', array( __CLASS__, 'handle_save_settings' ) );
		add_action( 'admin_post_crawlwatch_clear_logs', array( __CLASS__, 'handle_clear_logs' ) );
		add_action( 'admin_post_crawlwatch_export_csv', array( __CLASS__, 'handle_export_csv' ) );
		add_action( 'admin_post_crawlwatch_scan_schema', array( __CLASS__, 'handle_scan_schema' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect_setup' ) );
	}

	/**
	 * Register menu.
	 *
	 * @return void
	 */
	public static function menu() {
		$hook          = add_menu_page(
			__( 'CrawlWatch', 'crawlwatch-ai-bot-insights' ),
			__( 'CrawlWatch', 'crawlwatch-ai-bot-insights' ),
			'manage_options',
			'crawlwatch',
			array( __CLASS__, 'render_overview' ),
			'dashicons-visibility',
			30
		);
		self::$hooks[] = $hook;

		$sub           = add_submenu_page(
			'crawlwatch',
			__( 'Overview', 'crawlwatch-ai-bot-insights' ),
			__( 'Overview', 'crawlwatch-ai-bot-insights' ),
			'manage_options',
			'crawlwatch',
			array( __CLASS__, 'render_overview' )
		);
		self::$hooks[] = $sub;

		$bots          = add_submenu_page(
			'crawlwatch',
			__( 'Bots', 'crawlwatch-ai-bot-insights' ),
			__( 'Bots', 'crawlwatch-ai-bot-insights' ),
			'manage_options',
			'crawlwatch-bots',
			array( __CLASS__, 'render_bots' )
		);
		self::$hooks[] = $bots;

		$gaps          = add_submenu_page(
			'crawlwatch',
			__( 'Content Gaps', 'crawlwatch-ai-bot-insights' ),
			__( 'Content Gaps', 'crawlwatch-ai-bot-insights' ),
			'manage_options',
			'crawlwatch-gaps',
			array( __CLASS__, 'render_gaps' )
		);
		self::$hooks[] = $gaps;

		$files         = add_submenu_page(
			'crawlwatch',
			__( 'Files', 'crawlwatch-ai-bot-insights' ),
			__( 'Files', 'crawlwatch-ai-bot-insights' ),
			'manage_options',
			'crawlwatch-files',
			array( __CLASS__, 'render_files' )
		);
		self::$hooks[] = $files;

		$settings      = add_submenu_page(
			'crawlwatch',
			__( 'Settings', 'crawlwatch-ai-bot-insights' ),
			__( 'Settings', 'crawlwatch-ai-bot-insights' ),
			'manage_options',
			'crawlwatch-settings',
			array( __CLASS__, 'render_settings' )
		);
		self::$hooks[] = $settings;

		$setup         = add_submenu_page(
			null,
			__( 'CrawlWatch Setup', 'crawlwatch-ai-bot-insights' ),
			__( 'CrawlWatch Setup', 'crawlwatch-ai-bot-insights' ),
			'manage_options',
			'crawlwatch-setup',
			array( __CLASS__, 'render_setup' )
		);
		self::$hooks[] = $setup;
	}

	/**
	 * Add Settings link on Plugins list row.
	 *
	 * @param array $links Existing links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = add_query_arg( array( 'page' => 'crawlwatch-settings' ), admin_url( 'admin.php' ) );
		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'crawlwatch-ai-bot-insights' ) . '</a>'
		);
		return $links;
	}

	/**
	 * Enqueue only on our pages.
	 *
	 * @param string $hook Current hook.
	 * @return void
	 */
	public static function assets( $hook ) {
		if ( ! in_array( $hook, self::$hooks, true ) ) {
			return;
		}
		wp_enqueue_style(
			'crawlwatch-admin',
			CRAWLWATCH_URL . 'assets/css/admin.css',
			array(),
			CRAWLWATCH_VERSION
		);
		wp_enqueue_script(
			'crawlwatch-admin',
			CRAWLWATCH_URL . 'assets/js/admin.js',
			array(),
			CRAWLWATCH_VERSION,
			true
		);
		wp_localize_script(
			'crawlwatch-admin',
			'crawlwatchAdmin',
			array(
				/* translators: Button confirmation after copying a URL. */
				'copiedLabel' => __( 'Copied!', 'crawlwatch-ai-bot-insights' ),
			)
		);
	}

	/**
	 * Allowed day ranges.
	 *
	 * @return array
	 */
	private static function allowed_days() {
		return array( 7, 30 );
	}

	/**
	 * Render overview.
	 *
	 * @return void
	 */
	public static function render_overview() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'crawlwatch-ai-bot-insights' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only date filter, no state change.
		$days_raw = isset( $_GET['days'] ) ? absint( wp_unslash( $_GET['days'] ) ) : 7;
		$days     = in_array( (int) $days_raw, self::allowed_days(), true ) ? (int) $days_raw : 7;
		$since    = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );

		$total      = CrawlWatch_Logger::count_since( $since );
		$referrals  = CrawlWatch_Logger::count_by_type( $since, 'referral' );
		$unique     = CrawlWatch_Logger::count_unique_bots( $since );
		$top_bots   = CrawlWatch_Logger::top_bots( $since, 5 );
		$recent     = CrawlWatch_Logger::recent( 10 );
		$score_data = CrawlWatch_Score::get();
		$score      = isset( $score_data['score'] ) ? (int) $score_data['score'] : 0;
		$wins       = isset( $score_data['wins'] ) ? $score_data['wins'] : array();
		$has_seo    = CrawlWatch_Score::has_seo_plugin();
		$schema     = CrawlWatch_Schema::get_report();
		$woo_active = CrawlWatch_Woo::is_active();
		$woo_top    = $woo_active ? CrawlWatch_Woo::top_products() : array();
		$max_hits   = 0;
		foreach ( $top_bots as $row ) {
			$max_hits = max( $max_hits, (int) $row['hits'] );
		}

		require CRAWLWATCH_PATH . 'templates/overview.php';
	}

	/**
	 * Render bots page: filter + search + pagination.
	 *
	 * @return void
	 */
	public static function render_bots() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'crawlwatch-ai-bot-insights' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filters, no state change.
		$bot_raw = isset( $_GET['bot'] ) ? sanitize_text_field( wp_unslash( $_GET['bot'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filters.
		$q_raw = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only paging.
		$paged_raw = isset( $_GET['paged'] ) ? absint( wp_unslash( $_GET['paged'] ) ) : 1;

		$bot   = crawlwatch_safe_truncate( sanitize_text_field( $bot_raw ), 50 );
		$q     = crawlwatch_safe_truncate( sanitize_text_field( $q_raw ), 100 );
		$paged = max( 1, absint( $paged_raw ) );
		$per   = 20;

		$bots_list = CrawlWatch_Logger::distinct_bots();
		if ( '' !== $bot && ! in_array( $bot, $bots_list, true ) ) {
			$bot = '';
		}

		$total = CrawlWatch_Logger::count_filtered( $bot, $q );
		$pages = max( 1, (int) ceil( $total / $per ) );
		if ( $paged > $pages ) {
			$paged = $pages;
		}
		$rows = CrawlWatch_Logger::get_filtered( $bot, $q, $per, ( $paged - 1 ) * $per );

		require CRAWLWATCH_PATH . 'templates/bots.php';
	}

	/**
	 * Render content gaps page: posts missing excerpts + images missing alt.
	 *
	 * @return void
	 */
	public static function render_gaps() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'crawlwatch-ai-bot-insights' ) );
		}

		$post_ids = get_posts(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'numberposts'   => 50,
				'no_found_rows' => true,
				'fields'        => 'ids',
				'orderby'       => 'date',
				'order'         => 'DESC',
			)
		);
		$no_excerpt = array();
		foreach ( $post_ids as $pid ) {
			if ( '' === trim( (string) get_post_field( 'post_excerpt', $pid ) ) ) {
				$no_excerpt[] = $pid;
			}
		}

		$att_ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'numberposts'    => 50,
				'no_found_rows'  => true,
				'fields'         => 'ids',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);
		$no_alt = array();
		foreach ( $att_ids as $aid ) {
			if ( '' === trim( (string) get_post_meta( $aid, '_wp_attachment_image_alt', true ) ) ) {
				$no_alt[] = $aid;
			}
		}

		require CRAWLWATCH_PATH . 'templates/gaps.php';
	}

	/**
	 * Render files page (llms.txt + robots).
	 *
	 * @return void
	 */
	public static function render_files() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'crawlwatch-ai-bot-insights' ) );
		}

		$llms_content = get_option( 'crawlwatch_llms_content', '' );
		if ( '' === trim( (string) $llms_content ) ) {
			$llms_content = CrawlWatch_Llms::generate();
		}
		$llms_url      = home_url( '/llms.txt' );
		$ai_url        = home_url( '/ai.txt' );
		$llms_full_url = home_url( '/llms-full.txt' );
		$fallback_url  = add_query_arg( 'crawlwatch_file', 'llms.txt', home_url( '/' ) );
		$managed_bots  = CrawlWatch_Robots::managed_bots();
		$settings      = get_option( 'crawlwatch_settings', array() );
		$rules         = isset( $settings['robots_rules'] ) && is_array( $settings['robots_rules'] ) ? $settings['robots_rules'] : array();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only notice flag.
		$msg_raw        = isset( $_GET['cw_msg'] ) ? sanitize_key( wp_unslash( $_GET['cw_msg'] ) ) : '';
		$msg            = sanitize_key( $msg_raw );
		$generated_note = '';
		if ( 'saved' === $msg ) {
			$generated_note = __( 'Saved.', 'crawlwatch-ai-bot-insights' );
		} elseif ( 'regenerated' === $msg ) {
			$generated_note = __( 'Regenerated from your site.', 'crawlwatch-ai-bot-insights' );
		} elseif ( 'robots' === $msg ) {
			$generated_note = __( 'Robots rules saved.', 'crawlwatch-ai-bot-insights' );
		}

		require CRAWLWATCH_PATH . 'templates/files.php';
	}

	/**
	 * Redirect helper: falls back to a continue link when another
	 * plugin already printed output (else wp_safe_redirect dies blank).
	 *
	 * @param string $url Destination.
	 * @return void
	 */
	private static function safe_redirect( $url ) {
		if ( ! headers_sent() ) {
			wp_safe_redirect( $url );
			exit;
		}
		wp_die(
			'<p>' . esc_html__( 'Saved.', 'crawlwatch-ai-bot-insights' ) . '</p><p><a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Continue', 'crawlwatch-ai-bot-insights' ) . '</a></p>',
			esc_html__( 'CrawlWatch', 'crawlwatch-ai-bot-insights' ),
			array( 'back_link' => true )
		);
	}

	/**
	 * Handle files form (admin-post).
	 *
	 * @return void
	 */
	public static function handle_save_files() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission.', 'crawlwatch-ai-bot-insights' ) );
		}
		check_admin_referer( 'crawlwatch_files', 'crawlwatch_files_nonce' );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized per-branch below.
		$op_raw = isset( $_POST['op'] ) ? wp_unslash( $_POST['op'] ) : 'save';
		$op     = sanitize_key( $op_raw );

		$settings = get_option( 'crawlwatch_settings', crawlwatch_get_default_settings() );

		if ( 'regenerate' === $op ) {
			CrawlWatch_Llms::store( 'crawlwatch_llms_content', CrawlWatch_Llms::generate() );
			CrawlWatch_Llms::store( 'crawlwatch_llms_full_content', CrawlWatch_Llms::generate( true ) );
			$msg = 'regenerated';
		} elseif ( 'robots' === $op ) {
			$allowed_tokens = array_keys( CrawlWatch_Robots::managed_bots() );
			$new_rules      = array();
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- keys/values validated against allowlist.
			$posted = isset( $_POST['robots_rules'] ) && is_array( $_POST['robots_rules'] ) ? wp_unslash( $_POST['robots_rules'] ) : array();
			foreach ( $allowed_tokens as $token ) {
				if ( isset( $posted[ $token ] ) && 'block' === sanitize_key( $posted[ $token ] ) ) {
					$new_rules[ $token ] = 'block';
				}
			}
			$settings['robots_rules'] = $new_rules;
			update_option( 'crawlwatch_settings', $settings );
			$msg = 'robots';
		} else {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized with textarea sanitizer.
			$raw = isset( $_POST['llms_content'] ) ? wp_unslash( $_POST['llms_content'] ) : '';
			CrawlWatch_Llms::store( 'crawlwatch_llms_content', crawlwatch_safe_truncate( sanitize_textarea_field( $raw ), 100000 ) );
			$msg = 'saved';
		}

		CrawlWatch_Score::clear();

		self::safe_redirect(
			add_query_arg(
				array(
					'page'   => 'crawlwatch-files',
					'cw_msg' => $msg,
				),
				admin_url( 'admin.php' )
			)
		);
	}

	/**
	 * Render settings page.
	 *
	 * @return void
	 */
	public static function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'crawlwatch-ai-bot-insights' ) );
		}
		$settings   = get_option( 'crawlwatch_settings', crawlwatch_get_default_settings() );
		$rows_count = CrawlWatch_Logger::count_since( '2000-01-01 00:00:00' );
		require CRAWLWATCH_PATH . 'templates/settings.php';
	}

	/**
	 * Handle settings save (admin-post).
	 *
	 * @return void
	 */
	public static function handle_save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission.', 'crawlwatch-ai-bot-insights' ) );
		}
		check_admin_referer( 'crawlwatch_settings', 'crawlwatch_settings_nonce' );

		$settings = get_option( 'crawlwatch_settings', crawlwatch_get_default_settings() );

		$settings['logging_enabled']     = isset( $_POST['logging_enabled'] ) ? 1 : 0;
		$settings['llms_enabled']        = isset( $_POST['llms_enabled'] ) ? 1 : 0;
		$settings['delete_on_uninstall'] = isset( $_POST['delete_on_uninstall'] ) ? 1 : 0;

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- validated via allowlist sanitizer.
		$ret_raw                    = isset( $_POST['retention_days'] ) ? wp_unslash( $_POST['retention_days'] ) : 30;
		$settings['retention_days'] = crawlwatch_sanitize_retention( $ret_raw );

		$settings['digest_enabled'] = isset( $_POST['digest_enabled'] ) ? 1 : 0;
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized with email sanitizer below.
		$digest_raw              = isset( $_POST['digest_email'] ) ? wp_unslash( $_POST['digest_email'] ) : '';
		$digest_email            = sanitize_email( $digest_raw );
		$settings['digest_email'] = ( '' !== $digest_email && is_email( $digest_email ) ) ? $digest_email : '';

		update_option( 'crawlwatch_settings', $settings );
		CrawlWatch_Score::clear();
		CrawlWatch_Digest::maybe_schedule();

		self::safe_redirect(
			add_query_arg(
				array(
					'page'    => 'crawlwatch-settings',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
	}

	/**
	 * Handle clear logs (admin-post).
	 *
	 * @return void
	 */
	public static function handle_clear_logs() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission.', 'crawlwatch-ai-bot-insights' ) );
		}
		check_admin_referer( 'crawlwatch_settings', 'crawlwatch_settings_nonce' );

		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- explicit user-requested wipe of own fixed table.
		$wpdb->query( "DELETE FROM `{$wpdb->prefix}crawlwatch_logs`" );

		self::safe_redirect(
			add_query_arg(
				array(
					'page'    => 'crawlwatch-settings',
					'updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
	}

	/**
	 * Handle CSV export (admin-post). Streams up to 5000 filtered rows.
	 *
	 * @return void
	 */
	public static function handle_export_csv() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission.', 'crawlwatch-ai-bot-insights' ) );
		}
		check_admin_referer( 'crawlwatch_export', 'crawlwatch_export_nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified above via check_admin_referer.
		$bot_raw = isset( $_GET['bot'] ) ? sanitize_text_field( wp_unslash( $_GET['bot'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- nonce verified above.
		$q_raw = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';

		$bot = crawlwatch_safe_truncate( sanitize_text_field( $bot_raw ), 50 );
		$q   = crawlwatch_safe_truncate( sanitize_text_field( $q_raw ), 100 );

		$bots_list = CrawlWatch_Logger::distinct_bots();
		if ( '' !== $bot && ! in_array( $bot, $bots_list, true ) ) {
			$bot = '';
		}

		$rows = CrawlWatch_Logger::get_export_rows( $bot, $q, 5000 );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=crawlwatch-export-' . gmdate( 'Ymd-His' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		fputcsv( $out, array( 'Time (UTC)', 'Bot', 'Type', 'URL', 'Referrer' ) );
		foreach ( $rows as $r ) {
			fputcsv(
				$out,
				array(
					isset( $r['log_time'] ) ? $r['log_time'] : '',
					isset( $r['bot_name'] ) ? $r['bot_name'] : '',
					isset( $r['bot_type'] ) ? $r['bot_type'] : '',
					isset( $r['url'] ) ? $r['url'] : '',
					isset( $r['referrer'] ) ? $r['referrer'] : '',
				)
			);
		}
		fclose( $out );
		exit;
	}

	/**
	 * Handle schema scan (admin-post). Runs on click only, caches report.
	 *
	 * @return void
	 */
	public static function handle_scan_schema() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission.', 'crawlwatch-ai-bot-insights' ) );
		}
		check_admin_referer( 'crawlwatch_schema', 'crawlwatch_schema_nonce' );

		CrawlWatch_Schema::scan();

		self::safe_redirect(
			add_query_arg(
				array(
					'page'   => 'crawlwatch',
					'cw_msg' => 'schema',
				),
				admin_url( 'admin.php' )
			)
		);
	}

	/**
	 * One-time redirect to setup wizard after activation.
	 *
	 * @return void
	 */
	public static function maybe_redirect_setup() {
		if ( is_network_admin() ) {
			return; // Setup page lives in site admin, not network admin.
		}
		if ( ! get_transient( 'crawlwatch_activation_redirect' ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( wp_doing_ajax() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only bulk-activation guard, no state change.
		if ( isset( $_GET['activate-multi'] ) ) {
			delete_transient( 'crawlwatch_activation_redirect' );
			return;
		}
		delete_transient( 'crawlwatch_activation_redirect' );
		self::safe_redirect(
			add_query_arg(
				array(
					'page' => 'crawlwatch-setup',
					'step' => 1,
				),
				admin_url( 'admin.php' )
			)
		);
	}

	/**
	 * Render setup wizard.
	 *
	 * @return void
	 */
	public static function render_setup() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'crawlwatch-ai-bot-insights' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only step nav.
		$step_raw = isset( $_GET['step'] ) ? absint( wp_unslash( $_GET['step'] ) ) : 1;
		$step     = max( 1, min( 3, absint( $step_raw ) ) );

		// Step 2 writes via POST only (no state change on GET).
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- method compared case-insensitively, no output.
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
		if ( 2 === $step && 'POST' === $method ) {
			check_admin_referer( 'crawlwatch_setup', 'crawlwatch_setup_nonce' );
			if ( '' === trim( (string) get_option( 'crawlwatch_llms_content', '' ) ) ) {
				CrawlWatch_Llms::store( 'crawlwatch_llms_content', CrawlWatch_Llms::generate() );
				CrawlWatch_Score::clear();
			}
			// Render step 3 inline instead of redirecting: if any plugin
			// already printed output, wp_safe_redirect would die blank.
			$step = 3;
		}

		$score_data = CrawlWatch_Score::get();
		$score      = isset( $score_data['score'] ) ? (int) $score_data['score'] : 0;

		require CRAWLWATCH_PATH . 'templates/setup-wizard.php';
	}
}
