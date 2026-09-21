# CrawlWatch — AI Bot Insights

> See which AI bots read your site, track AI referrals, block unwanted bots and get AI-ready with llms.txt — fast, private, no API key. 100% free.
>
> **Status:** live on WordPress.org.

| | |
|---|---|
| **Plugin name** | CrawlWatch — AI Bot Insights |
| **Current version** | the `Stable tag` in [`readme.txt`](readme.txt) — no version number is repeated here, so none can go stale |
| **Distribution** | [WordPress.org](https://wordpress.org/plugins/crawlwatch-ai-bot-insights/) · [Packagist](https://packagist.org/packages/devmonowar/crawlwatch-ai-bot-insights) · [GitHub Releases](https://github.com/devmonowar/crawlwatch-ai-bot-insights/releases) |
| **Prefix** | `crawlwatch_` functions · `CrawlWatch_` classes · `CRAWLWATCH_` constants |
| **License** | GPLv2 or later — required by WordPress.org, and not optional: WordPress itself is GPL |

---

## What this repository is

A complete, working WordPress plugin — an AI crawler detector (40+ bots), referral and UTM attribution, dashboard with score and trend chart, AI Readiness page, llms.txt generator, robots.txt AI allow/block manager, and Settings with retention, privacy notice and danger zone. See `readme.txt` for the full feature description and changelog, or the [landing page](https://devmonowar.github.io/crawlwatch-ai-bot-insights/) for how to use it.

Guides: [write llms.txt by hand](https://devmonowar.github.io/blog/llms-txt-wordpress/) · [AI bot traffic in WordPress](https://devmonowar.github.io/blog/ai-bot-traffic-wordpress/).

The plugin is on the [WordPress.org plugin directory](https://wordpress.org/plugins/crawlwatch-ai-bot-insights/), so it installs and updates from inside WordPress like any other plugin.

---

## Installation

1. In WordPress, go to **Plugins → Add New**, search for **CrawlWatch**, and click **Install Now** — or download the ZIP from the [WordPress.org listing](https://wordpress.org/plugins/crawlwatch-ai-bot-insights/).
2. Activate it through the **Plugins** screen.
3. Follow the 3-step setup wizard, then open the new **CrawlWatch** menu in the WordPress admin sidebar.

Developers can install it with `composer require devmonowar/crawlwatch-ai-bot-insights`, or take a tagged ZIP from [GitHub Releases](https://github.com/devmonowar/crawlwatch-ai-bot-insights/releases).

## Requirements

- WordPress 6.2+
- PHP 7.4+

## Development

```bash
composer install
composer test   # dependency-free unit tests — no WordPress or database needed
composer stan   # PHPStan level 5
composer lint   # PHPCS (WordPress Coding Standards)
```

---

## License

GPLv2 or later — see `readme.txt`.
