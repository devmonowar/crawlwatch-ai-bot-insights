=== CrawlWatch – AI Bot Insights ===
Contributors: kstmonowar
Tags: ai bots, llms txt, gptbot, ai crawler, bot traffic
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track AI bot traffic on WordPress – see GPTBot, ClaudeBot & 40+ bots, block unwanted crawlers, generate llms.txt. Free.

== Description ==

CrawlWatch shows you the invisible traffic: GPTBot, ClaudeBot, PerplexityBot, Google-Extended and 40+ AI crawlers visiting your WordPress site.

**[Read the llms.txt guide](https://devmonowar.github.io/blog/llms-txt-wordpress/)** — what llms.txt is, the exact format that works, and the mistakes that make one useless · **[AI bot traffic in WordPress](https://devmonowar.github.io/blog/ai-bot-traffic-wordpress/)** — how to see it, measure it, and decide what to do about it · **[Plugin page](https://devmonowar.github.io/crawlwatch-ai-bot-insights/)** · **[Development on GitHub](https://github.com/devmonowar/crawlwatch-ai-bot-insights)** — report issues or contribute.

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
4. Open `yoursite.com/llms.txt` to verify (plain permalinks or subdirectory installs: use `yoursite.com/?crawlwatch_file=llms.txt`).

== Frequently Asked Questions ==

= Does this send data outside my site? =
No. Everything is stored locally in one table (`wp_crawlwatch_logs`). IPs are SHA-256 hashed (IPv4 last octet and IPv6 /64 anonymized first). Zero external calls.

= Will it slow my site? =
No. Normal human visits do zero extra queries. Only detected AI hits do one insert. No CSS/JS on the frontend.

= How long are logs kept? =
Default 30 days (7/14/30/90/180 selectable). Daily WP-Cron auto-deletes older rows.

= Does it work without Yoast/RankMath? =
Yes. It only detects whether an SEO plugin is active (for the schema part of the score). No integration needed.

= Will blocking a bot hurt my SEO? =
Blocking AI training bots (GPTBot, ClaudeBot) does not affect Google search. Never block regular Googlebot — CrawlWatch only manages AI bots, and Google-Extended asks for confirmation first.

= Why do I see few or zero hits with a cache plugin? =
Tracking runs in PHP. Full-page caches (WP Rocket, LiteSpeed, W3 Total Cache, Cloudflare APO) serve cached pages without running PHP, so those visits are never logged. CrawlWatch shows a notice on its own pages when a known cache is active.

== Screenshots ==

1. Overview dashboard with AI hits, bots, referrals and readiness score.
2. Bots table with filter, search and pagination.
3. llms.txt editor + robots.txt manager.
4. Settings with retention, privacy notice and danger zone.
5. AI-ready llms.txt served to crawlers.
6. llms-full.txt with full post content for deep AI context.
7. AI Readiness page with missing excerpts and alt text plus edit links.

== Changelog ==

= 1.1.1 =
* Packagist distribution (composer.json) + full QA tooling: PHPCS, PHPStan level 5, PHPUnit.

= 1.1.0 =
* Grouped-by-bot tab, daily hits trend chart, score breakdown, Content Gaps renamed to AI Readiness.
* Robots preview fallback for subdirectory/plain-permalink installs, Quick Wins link to AI Readiness, CSV formula guard with site-timezone times, page-cache notice + FAQ, uninstall row-count hint.
* Fixes: grouped-stats prepare notice, Reset keeps active tab, compact chart layout, Overview submenu removed.

= 1.0.0 =
* Initial release: AI bot + referral tracker (40+ crawlers), Overview dashboard with score + Quick Wins, Bots filter/search/pagination, llms.txt + ai.txt generator (no file writes), robots.txt AI allow/block manager, Settings (retention/privacy/clear logs), 3-step setup wizard. 100% free, local-only, hashed IPs.

== Privacy ==

CrawlWatch logs AI crawler visits locally: time, bot name, visited URL, referrer domain, truncated user-agent, and SHA-256 hashed IP (IPv4 last octet and IPv6 /64 anonymized before hashing). Raw IPs are never stored. Retention is configurable (default 30 days) and all data is deleted on uninstall if enabled in Settings. No visitor data leaves your site.
