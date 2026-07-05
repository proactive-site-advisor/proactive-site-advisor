=== Proactive Site Advisor – Local, privacy-first site alerts ===
Contributors: zheynlab
Tags: traffic, 404, monitoring, notifications, dashboard
Requires at least: 6.1
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.6
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Never miss a traffic drop, 404 surge, or bot anomaly. Daily, local monitoring alerts you instantly — with more alert types on the way.

== Description ==

Most WordPress issues stay hidden until they hurt you. **Proactive Site Advisor** silently watches your site every day, compares activity to the previous week, and immediately alerts you when something changes. It now separates real visitors from bots, so you see exactly how much traffic is human vs. crawler.

Everything stays 100% local—no external APIs, no data leaving your server. The plugin logs, processes, and summarizes data using lightweight database tables and atomic operations. Zero performance overhead.

**When an anomaly is detected you get:**
- What changed (e.g., "Traffic dropped by 41%")
- What it means for your site
- What you should check next
- Top 3 broken URLs (for 404 surges)
- Top 3 bot names (for bot anomalies)

**Dashboard view includes:**
- Critical issue indicator
- Weekly digest cards
- Latest alerts list
- 7‑day history table with averages

### Privacy & Performance
- **100% local** – No data leaves your server, no external APIs
- Raw visitor, bot, and 404 data stored in transients, processed once daily, then cleared
- Only two lightweight tables keep the last 7 days of stats and generated alerts
- No cookies, no cross-site tracking, GDPR‑friendly by design

The plugin does **not** fix anything—it alerts and recommends so you stay in full control.

== Key Features ==

* Bot traffic anomaly detection (surge/drop) with Top 3 bots
* Human traffic drop/spike detection (7‑day baseline)
* 404 error surge detection with Top 3 broken URLs
* Unified "Site Advisor" dashboard (digest, history, latest alerts)
* Actionable "What you should check next" lists
* Daily WP-Cron scan after day completion
* 100% local data processing – zero external requests
* Atomic database operations for reliable metric collection
* Accurate bot detection with 1500+ signatures
* Future-ready: more anomaly types planned

== Installation ==

1. Upload `proactive-site-advisor` to `/wp-content/plugins/`
2. Activate from Plugins → Installed Plugins
3. Visit **Site Advisor** menu

== Frequently Asked Questions ==

= Does the plugin fix anything automatically? =
No. It only alerts and gives actionable recommendations.

= Where do traffic and 404 data come from? =
The plugin logs page views and 404 errors via WordPress hooks. Data is stored temporarily and cleared after daily processing.

= When are scans performed? =
After each full day, via WP-Cron.

= How does it detect anomalies? =
Yesterday’s numbers are compared to the average of the previous 7 days.

= What does a 404 alert show? =
The top 3 broken URLs that day, with hit counts and fix suggestions.

= What does a bot alert show? =
The top 3 bot names (e.g., Googlebot, Bingbot) that visited that day, with visit counts and recommendations.

= Will you add other alert types? =
Yes. Future updates will bring slow page alerts, server error detection, and optional integration with popular analytics plugins—always privacy‑first.

= Is it free? =
Yes. Licensed GPL-2.0-or-later.

== Screenshots ==

1. Main dashboard with critical issue indicator and weekly digest.
2. Traffic drop alert – percentage change, impact summary, and action checklist.
3. 404 surge alert with top 3 broken URLs and hit counts.
4. Bot alert with top 3 bot names and percentage change.

== Changelog ==

= 1.0.6 =
* Fix: Daily metrics now stored in durable database (prevents data loss on cache clear)
* Fix: Incorrect bot classification on local development environments
* Database: Added atomic increment and JSON map update methods for reliable metric collection
* Performance: Removed cache-to-database sync cron (data written in real‑time now)
* Stability: Eliminated race conditions using atomic database operations
* Improvement: Simplified browser validation for accurate localhost testing
* Improvement: Enhanced bot detection accuracy with refined User-Agent analysis (reduced false positives)
* Performance: Unified cache clearing on install, update, activation, and deactivation
* Performance: Optimized lifecycle operations for better reliability

= 1.0.5 =
* Fix: Database table creation bug on activation resolved
* Update: Bot detection patterns upgraded with 1500+ new signatures (GPTBot, ClaudeBot, AmazonBot, etc.)
* Improvement: Better User-Agent parsing and reduced false positives
* Performance: Faster bot detection with lower memory usage
* Stability: Improved error handling for DB updates and cron jobs

= 1.0.4 =
* Feat: Bot traffic detection – separate human vs bot pageviews
* Feat: Bot anomaly alerts – spike/drop with Top 3 bots
* Dashboard: New “Bot Alerts” KPI card and bot pageviews column in history
* Dashboard: Bot alert cards with top bot names and recommendations
* Performance: Combined 1500+ bot pattern regex in a single static file
* Database: Added bot_pageviews and top_bots_json columns to daily_stats

= 1.0.3 =
* Fix: Alert messages now fully translatable via WordPress i18n
* Database: Removed redundant “title” column

= 1.0.2 =
* Added RTL support for admin dashboard

= 1.0.1 =
* Fix: Prevented duplicate alerts in digest cards
* Feat: Added percentage change for 404 errors

= 1.0.0 =
* Initial release
* Traffic drop/spike detection
* 404 surge detection with Top 404 URLs
* Dashboard (critical issues, digest, alerts, 7‑day history)
* Actionable recommendations
* Daily WP-Cron scans

== Upgrade Notice ==

= 1.0.6 =
Daily metrics now stored in durable DB to prevent data loss on cache clear. Fixed bot classification for local dev. Improved bot detection accuracy with fewer false positives. Optimized cache handling. Safe auto-update – no action needed.

= 1.0.5 =
Fixes a critical table creation bug on activation and adds 1500+ new bot signatures. Detection accuracy improved. Safe automatic update – no manual action required.

= 1.0.4 =
Adds bot traffic detection, bot anomaly alerts, and new dashboard cards. Includes database schema changes (new columns). Safe automatic update.

= 1.0.3 =
Makes alert messages translation‑ready and removes a redundant database column. Safe automatic update.

= 1.0.2 =
Added RTL support for the admin dashboard. Safe automatic update.

= 1.0.1 =
Removes duplicate digest entries and adds 404 change percentage. Safe automatic update.

= 1.0.0 =
Initial release.