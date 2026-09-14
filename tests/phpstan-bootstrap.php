<?php
/**
 * PHPStan bootstrap: constants the plugin defines at runtime.
 *
 * @package CrawlWatch
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals -- these mirror runtime constants defined elsewhere.

define( 'CRAWLWATCH_FILE', __DIR__ . '/../crawlwatch-ai-bot-insights.php' );
define( 'CRAWLWATCH_DIR', __DIR__ . '/../' );
define( 'CRAWLWATCH_PATH', __DIR__ . '/../' );
define( 'CRAWLWATCH_URL', 'https://example.com/wp-content/plugins/crawlwatch-ai-bot-insights/' );
define( 'CRAWLWATCH_BASENAME', 'crawlwatch-ai-bot-insights/crawlwatch-ai-bot-insights.php' );
define( 'CRAWLWATCH_SLUG', 'crawlwatch-ai-bot-insights' );
define( 'CRAWLWATCH_VERSION', '0.0.0' );
define( 'CRAWLWATCH_DB_VERSION', '2' );
define( 'WP_UNINSTALL_PLUGIN', 'crawlwatch-ai-bot-insights/crawlwatch-ai-bot-insights.php' );

// $wpdb result shapes (defined at runtime in wp-includes/wp-db.php).
define( 'OBJECT', 'OBJECT' );
define( 'OBJECT_K', 'OBJECT_K' );
define( 'ARRAY_A', 'ARRAY_A' );
define( 'ARRAY_N', 'ARRAY_N' );

// WordPress time constants (defined at runtime in wp-includes/default-constants.php).
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS', 3600 );
define( 'DAY_IN_SECONDS', 86400 );
define( 'WEEK_IN_SECONDS', 604800 );
define( 'MONTH_IN_SECONDS', 2592000 );
define( 'YEAR_IN_SECONDS', 31536000 );
