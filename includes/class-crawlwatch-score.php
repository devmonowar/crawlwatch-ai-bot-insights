<?php
/**
 * Readiness score 0-100 with breakdown + quick wins. Cached 12h.
 *
 * @package CrawlWatch
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class CrawlWatch_Score
 */
class CrawlWatch_Score {

	/**
	 * Get score + breakdown (cached).
	 *
	 * @param bool $force Force rescan.
	 * @return array
	 */
	public static function get( $force = false ) {
		if ( ! $force ) {
			$cached = get_transient( 'crawlwatch_score_cache' );
			if ( is_array( $cached ) && isset( $cached['score'] ) && isset( $cached['parts'] ) ) {
				return $cached;
			}
		}
		$result = self::compute();
		set_transient( 'crawlwatch_score_cache', $result, 12 * HOUR_IN_SECONDS );
		return $result;
	}

	/**
	 * Clear cache (call after llms save / settings change).
	 *
	 * @return void
	 */
	public static function clear() {
		delete_transient( 'crawlwatch_score_cache' );
	}

	/**
	 * Compute all dimensions.
	 *
	 * @return array
	 */
	private static function compute() {
		$wins  = array();
		$parts = array();
		$score = 0;

		$files_url = add_query_arg( array( 'page' => 'crawlwatch-files' ), admin_url( 'admin.php' ) );
		$gaps_url  = add_query_arg( array( 'page' => 'crawlwatch-gaps' ), admin_url( 'admin.php' ) );

		// 1. llms.txt present (20).
		$llms = get_option( 'crawlwatch_llms_content', '' );
		if ( '' !== trim( (string) $llms ) ) {
			$score  += 20;
			$parts[] = array(
				'label'  => __( 'llms.txt catalogue', 'crawlwatch-ai-bot-insights' ),
				'earned' => 20,
				'max'    => 20,
				'url'    => $files_url,
			);
		} else {
			$parts[] = array(
				'label'  => __( 'llms.txt catalogue', 'crawlwatch-ai-bot-insights' ),
				'earned' => 0,
				'max'    => 20,
				'url'    => $files_url,
			);
			$wins[]  = array(
				'points' => 20,
				'text'   => __( 'Generate llms.txt so AI bots find your catalogue in one file.', 'crawlwatch-ai-bot-insights' ),
				'url'    => add_query_arg( array( 'page' => 'crawlwatch-files' ), admin_url( 'admin.php' ) ),
			);
		}

		// 2. Manual excerpts as description proxy (20). NOTE: get_the_excerpt()
		// auto-generates from content, so check the raw post_excerpt field.
		$posts = get_posts(
			array(
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'numberposts'   => 20,
				'no_found_rows' => true,
				'fields'        => 'ids',
			)
		);
		if ( empty( $posts ) ) {
			$parts[] = array(
				'label'  => __( 'Post excerpts as AI descriptions', 'crawlwatch-ai-bot-insights' ),
				'earned' => 0,
				'max'    => 20,
				'url'    => admin_url( 'post-new.php' ),
			);
			$wins[]  = array(
				'points' => 10,
				'text'   => __( 'Publish your first post so AI has something to cite.', 'crawlwatch-ai-bot-insights' ),
				'url'    => admin_url( 'post-new.php' ),
			);
		} else {
			$with_excerpt = 0;
			foreach ( $posts as $pid ) {
				if ( '' !== trim( (string) get_post_field( 'post_excerpt', $pid ) ) ) {
					++$with_excerpt;
				}
			}
			$pct      = (int) round( ( $with_excerpt / count( $posts ) ) * 100 );
			$earned_2 = (int) round( ( $pct / 100 ) * 20 );
			$score   += $earned_2;
			$parts[]  = array(
				'label'  => __( 'Post excerpts as AI descriptions', 'crawlwatch-ai-bot-insights' ),
				'earned' => $earned_2,
				'max'    => 20,
				'url'    => $gaps_url,
			);
			if ( $pct < 100 ) {
				$missing = count( $posts ) - $with_excerpt;
				$wins[]  = array(
					'points' => 20 - (int) round( ( $pct / 100 ) * 20 ),
					/* translators: 1: number of posts still missing excerpts, 2: total posts sampled. */
					'text'   => sprintf( __( 'Add excerpts as AI descriptions (%1$d remaining of last %2$d posts).', 'crawlwatch-ai-bot-insights' ), $missing, count( $posts ) ),
					'url'    => $gaps_url,
				);
			}
		}

		// 3. Schema/SEO plugin present (20).
		if ( self::has_seo_plugin() ) {
			$score  += 20;
			$parts[] = array(
				'label'  => __( 'Schema via SEO plugin', 'crawlwatch-ai-bot-insights' ),
				'earned' => 20,
				'max'    => 20,
				'url'    => admin_url( 'plugins.php' ),
			);
		} else {
			$score  += 5;
			$parts[] = array(
				'label'  => __( 'Schema via SEO plugin', 'crawlwatch-ai-bot-insights' ),
				'earned' => 5,
				'max'    => 20,
				'url'    => admin_url( 'plugin-install.php?s=schema&tab=search&type=term' ),
			);
			$wins[]  = array(
				'points' => 15,
				'text'   => __( 'Install Yoast SEO or Rank Math for Article/Product schema AI reads.', 'crawlwatch-ai-bot-insights' ),
				'url'    => admin_url( 'plugin-install.php?s=schema&tab=search&type=term' ),
			);
		}

		// 4. Image alts: sample 20 attachments (10).
		$atts = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'post_status'    => 'inherit',
				'numberposts'    => 20,
				'no_found_rows'  => true,
				'fields'         => 'ids',
			)
		);
		if ( empty( $atts ) ) {
			$parts[] = array(
				'label'  => __( 'Image alt text', 'crawlwatch-ai-bot-insights' ),
				'earned' => 0,
				'max'    => 10,
				'url'    => admin_url( 'media-new.php' ),
			);
			$wins[]  = array(
				'points' => 5,
				'text'   => __( 'Upload images with alt text so AI can understand your visuals.', 'crawlwatch-ai-bot-insights' ),
				'url'    => admin_url( 'media-new.php' ),
			);
		} else {
			$with_alt = 0;
			foreach ( $atts as $aid ) {
				if ( '' !== trim( (string) get_post_meta( $aid, '_wp_attachment_image_alt', true ) ) ) {
					++$with_alt;
				}
			}
			$pct      = (int) round( ( $with_alt / count( $atts ) ) * 100 );
			$earned_4 = (int) round( ( $pct / 100 ) * 10 );
			$score   += $earned_4;
			$parts[]  = array(
				'label'  => __( 'Image alt text', 'crawlwatch-ai-bot-insights' ),
				'earned' => $earned_4,
				'max'    => 10,
				'url'    => admin_url( 'upload.php' ),
			);
			if ( $pct < 100 ) {
				$wins[] = array(
					'points' => 10 - (int) round( ( $pct / 100 ) * 10 ),
					/* translators: 1: number of images missing alt text, 2: total images sampled. */
					'text'   => sprintf( __( '%1$d of last %2$d images miss alt text.', 'crawlwatch-ai-bot-insights' ), count( $atts ) - $with_alt, count( $atts ) ),
					'url'    => admin_url( 'upload.php' ),
				);
			}
		}

		// 5. robots AI-friendly (15).
		$settings = get_option( 'crawlwatch_settings', array() );
		$rules    = isset( $settings['robots_rules'] ) && is_array( $settings['robots_rules'] ) ? $settings['robots_rules'] : array();
		$blocked  = count( $rules );
		if ( 0 === $blocked ) {
			$score  += 15;
			$parts[] = array(
				'label'  => __( 'AI-friendly robots.txt', 'crawlwatch-ai-bot-insights' ),
				'earned' => 15,
				'max'    => 15,
				'url'    => $files_url,
			);
		} elseif ( $blocked <= 2 ) {
			$score  += 10;
			$parts[] = array(
				'label'  => __( 'AI-friendly robots.txt', 'crawlwatch-ai-bot-insights' ),
				'earned' => 10,
				'max'    => 15,
				'url'    => $files_url,
			);
		} else {
			$score  += 5;
			$parts[] = array(
				'label'  => __( 'AI-friendly robots.txt', 'crawlwatch-ai-bot-insights' ),
				'earned' => 5,
				'max'    => 15,
				'url'    => $files_url,
			);
			$wins[]  = array(
				'points' => 10,
				/* translators: %d: number of AI bots blocked in robots.txt. */
				'text'   => sprintf( __( '%d AI bots blocked in robots.txt – unblock the ones you want citations from.', 'crawlwatch-ai-bot-insights' ), $blocked ),
				'url'    => add_query_arg( array( 'page' => 'crawlwatch-files' ), admin_url( 'admin.php' ) ),
			);
		}

		// 6. Tracking health: any AI hit last 7d (15).
		$since = gmdate( 'Y-m-d H:i:s', time() - ( 7 * DAY_IN_SECONDS ) );
		$hits  = CrawlWatch_Logger::count_since( $since );
		if ( $hits > 0 ) {
			$score  += 15;
			$parts[] = array(
				'label'  => __( 'AI tracking active', 'crawlwatch-ai-bot-insights' ),
				'earned' => 15,
				'max'    => 15,
				'url'    => add_query_arg( array( 'page' => 'crawlwatch-bots' ), admin_url( 'admin.php' ) ),
			);
		} else {
			$score  += 5;
			$parts[] = array(
				'label'  => __( 'AI tracking active', 'crawlwatch-ai-bot-insights' ),
				'earned' => 5,
				'max'    => 15,
				'url'    => add_query_arg( array( 'page' => 'crawlwatch-bots' ), admin_url( 'admin.php' ) ),
			);
			$wins[]  = array(
				'points' => 10,
				'text'   => __( 'No AI hits in 7 days – share a link so crawlers discover you, then re-check.', 'crawlwatch-ai-bot-insights' ),
				'url'    => add_query_arg( array( 'page' => 'crawlwatch-bots' ), admin_url( 'admin.php' ) ),
			);
		}

		$score = max( 0, min( 100, (int) $score ) );
		usort(
			$wins,
			function ( $a, $b ) {
				return $b['points'] - $a['points'];
			}
		);

		return array(
			'score' => $score,
			'wins'  => array_slice( $wins, 0, 5 ),
			'parts' => $parts,
		);
	}

	/**
	 * Detect common SEO/schema plugins.
	 *
	 * @return bool
	 */
	public static function has_seo_plugin() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$slugs = array(
			'wordpress-seo/wp-seo.php',
			'seo-by-rank-math/rank-math.php',
			'seopress/seopress.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
		);
		foreach ( $slugs as $s ) {
			if ( is_plugin_active( $s ) ) {
				return true;
			}
		}
		return false;
	}
}
