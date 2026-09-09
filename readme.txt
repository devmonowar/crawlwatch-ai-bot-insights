=== CrawlWatch – AI Bot Insights ===
Contributors: kstmonowar
Tags: ai, analytics, bot, llms-txt, seo
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

See which AI bots read your site, track AI referrals & get AI-ready with llms.txt – fast, private, no API key. 100% free.

== Description ==

CrawlWatch shows you the invisible traffic: GPTBot, ClaudeBot, PerplexityBot, Google-Extended and 40+ AI crawlers visiting your WordPress site.

* AI bot hits + which URLs they read
* AI referrals (visitors coming from ChatGPT, Perplexity, Gemini, Claude)
* One-click llms.txt + ai.txt generator
* robots.txt allow/block per AI bot (preview, no code)
* Readiness Score 0-100 with ranked Quick Wins
* 100% free, no upsell. Data stays on your server. IPs are hashed, never stored raw.

No API key. No external service in V1. Lightweight: normal visitors cause zero extra queries.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install from Plugins > Add New.
2. Activate through the Plugins screen.
3. Go to CrawlWatch in the left menu and follow the 3-step setup.
4. Open `yoursite.com/llms.txt` to verify.

== Frequently Asked Questions ==

= Does this send data outside my site? =
No. Everything is stored locally in one table (`wp_crawlwatch_logs`). IPs are SHA-256 hashed (IPv4 last octet and IPv6 /64 anonymized first). Zero external calls.

= Will it slow my site? =
No. Normal human visits do zero extra queries. Only detected AI hits do one insert. No CSS/JS on the frontend.

= How long are logs kept? =
Default 30 days (7/14/30/90/180 selectable). Daily WP-Cron auto-deletes older rows.

= Does it work without Yoast/RankMath? =
Yes. It only detects whether an SEO plugin is active (for the schema part of the score). No integration needed.

== Screenshots ==

1. Overview dashboard with AI hits, bots, referrals and readiness score.
2. Bots table with filter, search and pagination.
3. llms.txt editor + robots.txt manager.
4. Settings with retention, privacy notice and danger zone.
5. AI-ready llms.txt served to crawlers.
6. llms-full.txt with full post content for deep AI context.
7. Content gaps page with missing excerpts and alt text plus edit links.

== Changelog ==

= 1.0.0 =
* Initial release: AI bot + referral tracker (40+ crawlers), Overview dashboard with score + Quick Wins, Bots filter/search/pagination, llms.txt + ai.txt generator (no file writes), robots.txt AI allow/block manager, Settings (retention/privacy/clear logs), 3-step setup wizard. 100% free, local-only, hashed IPs.

== Privacy ==

CrawlWatch logs AI crawler visits locally: time, bot name, visited URL, referrer domain, truncated user-agent, and SHA-256 hashed IP (IPv4 last octet and IPv6 /64 anonymized before hashing). Raw IPs are never stored. Retention is configurable (default 30 days) and all data is deleted on uninstall if enabled in Settings. No visitor data leaves your site.
