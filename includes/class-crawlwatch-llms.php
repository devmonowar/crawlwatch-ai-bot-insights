<?php
/**
 * Generate and serve llms.txt / ai.txt / llms-full.txt without file writes.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Llms
 */
class CrawlWatch_Llms {

	/**
	 * Init: serve on template_redirect early + query-var fallback.
	 *
	 * @return void
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'serve' ), 1 );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
	}

	/**
	 * Auto-refresh allowed? On by default; never touches manually edited content.
	 * Unknown origin (pre-1.1.0 content) counts as manual: hands off.
	 *
	 * @return bool
	 */
	public static function is_auto() {
		$settings = get_option( 'crawlwatch_settings', array() );
		$auto     = isset( $settings['llms_auto'] ) ? ! empty( $settings['llms_auto'] ) : true;
		$manual   = isset( $settings['llms_manual'] ) ? ! empty( $settings['llms_manual'] ) : true;
		return $auto && ! $manual;
	}

	/**
	 * Regenerate llms contents when site content changes (save/trash/delete).
	 * Debounced: bulk/quick edits and imports schedule one refresh instead of
	 * rebuilding (full post content + 4 option writes) on every single save.
	 *
	 * @param int $post_id Changed post id.
	 * @return void
	 */
	public static function maybe_auto_refresh( $post_id ) {
		if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
			return;
		}
		$type = get_post_type( $post_id );
		if ( false !== $type && ! in_array( $type, array( 'post', 'page' ), true ) ) {
			return;
		}
		if ( 'auto-draft' === get_post_status( $post_id ) ) {
			return;
		}
		$settings = get_option( 'crawlwatch_settings', array() );
		if ( empty( $settings['llms_enabled'] ) ) {
			return;
		}
		if ( ! self::is_auto() ) {
			return;
		}
		if ( wp_next_scheduled( 'crawlwatch_llms_refresh' ) ) {
			return;
		}
		wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'crawlwatch_llms_refresh' );
	}

	/**
	 * Run the debounced llms rebuild (single-event cron callback).
	 *
	 * @return void
	 */
	public static function refresh_now() {
		$settings = get_option( 'crawlwatch_settings', array() );
		if ( empty( $settings['llms_enabled'] ) || ! self::is_auto() ) {
			return;
		}
		self::store( 'crawlwatch_llms_content', self::generate() );
		self::store( 'crawlwatch_llms_full_content', self::generate( true ) );
	}

	/**
	 * Whitelist query var so ?crawlwatch_file=llms.txt works on plain permalinks.
	 *
	 * @param array $vars Vars.
	 * @return array
	 */
	public static function query_vars( $vars ) {
		$vars[] = 'crawlwatch_file';
		return $vars;
	}

	/**
	 * Allowed file keys.
	 *
	 * @return array
	 */
	private static function allowed_files() {
		return array( 'llms.txt', 'ai.txt', 'llms-full.txt' );
	}

	/**
	 * Generate content from site data.
	 *
	 * @param bool $full Include full post content (for llms-full.txt).
	 * @return string
	 */
	public static function generate( $full = false ) {
		$site_name = get_bloginfo( 'name' );
		$tagline   = get_bloginfo( 'description' );
		$home      = home_url( '/' );

		$lines   = array();
		$lines[] = '# ' . wp_strip_all_tags( $site_name );
		if ( '' !== trim( (string) $tagline ) ) {
			$lines[] = '> ' . wp_strip_all_tags( $tagline );
		}
		$lines[] = '';
		$lines[] = 'Home: ' . esc_url_raw( $home );
		$lines[] = '';

		$posts = get_posts(
			array(
				'post_type'        => 'post',
				'post_status'      => 'publish',
				'numberposts'      => $full ? 100 : 50,
				'orderby'          => 'date',
				'order'            => 'DESC',
				'no_found_rows'    => true,
				'suppress_filters' => false,
			)
		);
		if ( ! empty( $posts ) ) {
			$lines[] = '## Posts';
			foreach ( $posts as $p ) {
				$title = wp_strip_all_tags( get_the_title( $p ) );
				if ( '' === trim( $title ) ) {
					continue;
				}
				$lines[] = '- ' . $title . ': ' . esc_url_raw( get_permalink( $p ) );
				if ( $full ) {
					$body = wp_strip_all_tags( strip_shortcodes( $p->post_content ) );
					$body = trim( preg_replace( '/\s+/', ' ', $body ) );
					if ( '' !== $body ) {
						$lines[] = '  ' . crawlwatch_safe_truncate( $body, 5000 );
					}
				}
			}
			$lines[] = '';
		}

		$pages = get_pages(
			array(
				'sort_column' => 'post_date',
				'sort_order'  => 'DESC',
				'number'      => 20,
				'post_status' => 'publish',
			)
		);
		if ( ! empty( $pages ) ) {
			$lines[] = '## Pages';
			foreach ( $pages as $p ) {
				$title = wp_strip_all_tags( get_the_title( $p ) );
				if ( '' === trim( $title ) ) {
					continue;
				}
				$lines[] = '- ' . $title . ': ' . esc_url_raw( get_permalink( $p->ID ) );
			}
			$lines[] = '';
		}

		$cats = get_categories(
			array(
				'hide_empty' => true,
				'number'     => 20,
			)
		);
		if ( ! empty( $cats ) && ! is_wp_error( $cats ) ) {
			$lines[] = '## Topics';
			foreach ( $cats as $c ) {
				$lines[] = '- ' . wp_strip_all_tags( $c->name ) . ': ' . esc_url_raw( get_category_link( $c ) );
			}
			$lines[] = '';
		}

		$lines[] = 'Generated by CrawlWatch – AI Bot Insights (100% free).';

		/**
		 * Filter final content.
		 *
		 * @param string $content Generated markdown.
		 */
		return apply_filters( 'crawlwatch_llms_content', implode( "\n", $lines ) );
	}

	/**
	 * Store content without autoload (can be large on big sites).
	 *
	 * @param string $key     Option name.
	 * @param string $content Markdown.
	 * @return void
	 */
	public static function store( $key, $content ) {
		delete_option( $key );
		add_option( $key, $content, '', false );
	}

	/**
	 * Serve /llms.txt and /ai.txt without file writes.
	 *
	 * @return void
	 */
	public static function serve() {
		if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
			return;
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- path parsed, no output.
		$uri_raw = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$path    = strtolower( trim( (string) wp_parse_url( $uri_raw, PHP_URL_PATH ) ) );

		// Plain-permalink fallback FIRST: ?crawlwatch_file=llms.txt works
		// regardless of install path, before any path matching can return.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only file serving, no state change.
		$qv_raw = isset( $_GET['crawlwatch_file'] ) ? sanitize_text_field( wp_unslash( $_GET['crawlwatch_file'] ) ) : '';
		$qv     = strtolower( trim( (string) $qv_raw ) );
		if ( '' !== $qv && ! in_array( $qv, self::allowed_files(), true ) ) {
			return;
		}

		// Support subdirectory installs (e.g. /test/llms.txt).
		// Skipped entirely when a valid ?crawlwatch_file= is present.
		$rel = trim( $path, '/' );
		if ( '' === $qv ) {
			$home_path = strtolower( trim( (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH ), '/' ) );
			if ( '' !== $home_path ) {
				$prefix = $home_path . '/';
				if ( 0 === strpos( $rel . '/', $prefix ) ) {
					$rel = substr( $rel, strlen( $prefix ) );
				} else {
					return;
				}
			}
		}
		// Fallback already validated above; path requests are matched here.
		if ( '' === $qv ) {
			if ( ! in_array( $rel, self::allowed_files(), true ) ) {
				return;
			}
			$is_full = ( 'llms-full.txt' === $rel );
		} else {
			$is_full = ( 'llms-full.txt' === $qv );
		}

		$settings = get_option( 'crawlwatch_settings', array() );
		if ( isset( $settings['llms_enabled'] ) && empty( $settings['llms_enabled'] ) ) {
			status_header( 404 );
			exit;
		}

		$option_key = $is_full ? 'crawlwatch_llms_full_content' : 'crawlwatch_llms_content';
		$content    = get_option( $option_key, '' );
		if ( '' === trim( (string) $content ) ) {
			$content = self::generate( $is_full );
			// add_option wins the first-write race; on failure re-read winner.
			if ( ! add_option( $option_key, $content, '', false ) ) {
				$stored = get_option( $option_key, '' );
				if ( '' !== trim( (string) $stored ) ) {
					$content = $stored;
				}
			}
		}

		// WP flags unknown paths as 404 before template_redirect; override since we serve real content.
		status_header( 200 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		// Content is admin-authored markdown; strip tags for plain-text safety.
		echo wp_strip_all_tags( (string) $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- text/plain output, tags stripped.
		exit;
	}
}
