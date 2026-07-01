=== Proactive Site Advisor – Local, privacy-first site alerts ===
Contributors: zheynlab
Tags: traffic, 404, monitoring, notifications, dashboard
Requires at least: 6.1
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.5
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Don't let traffic drops or 404 surges go unnoticed. Daily monitoring alerts you the moment something's off.

== Description ==

Most WordPress problems stay hidden until they cost you. Proactive Site Advisor watches your site daily, compares activity to the past week, and alerts you the second something deviates from normal. Now with bot detection, you see exactly how much traffic is real vs. crawlers. Traffic crashes, bot anomalies, 404 spikes — all monitored locally, no configuration. More alert types are on the way.

When an anomaly is detected, you see:
- What changed (e.g., "Traffic dropped by 41%")
- What this means for your site
- What you should check next
- Top 3 broken URLs (for 404 alerts)
- Top 3 bot names (for bot alerts)

Dashboard view includes: critical issues indicator, weekly digest cards, latest alerts list, and a 7‑day history table with averages.

How it works: raw visitor, bot, and 404 data is temporarily stored in WordPress transients, processed once daily, then cleared. Only summarized stats (7‑day rolling) and generated alerts are saved in two lightweight tables. No performance overhead.

= Privacy & Performance =

* 100% local — no data leaves your server, no external APIs
* Visitor, bot, and 404 logs are stored in WordPress transients, processed once daily, then cleared
* Only two lightweight tables keep the last 7 days of stats and generated alerts
* No cookies, no tracking across sites, GDPR‑friendly by design

All monitoring runs locally. No external APIs. No data leaves your server. The plugin does not fix anything — it alerts and recommends so you stay in control.

== Key Features ==

* Bot traffic anomaly detection (surge/drop) with Top 3 bots
* Traffic drop/spike detection (7‑day baseline)
* 404 error surge detection with Top 3 broken URLs
* "Site Advisor" dashboard (digest, history, latest alerts)
* Actionable "What you should check next" lists
* Daily WP-Cron scan after day completion (cached)
* Data processed locally — zero external requests
* Future-ready: more anomaly types planned

== Installation ==

1. Upload `proactive-site-advisor` to `/wp-content/plugins/`
2. Activate from Plugins -> Installed Plugins
3. Visit **Site Advisor** menu

== Frequently Asked Questions ==

= Does the plugin fix anything automatically? =
No. It only alerts and recommends.

= Where do the traffic and 404 data come from? =
The plugin logs page views and 404 errors via WordPress hooks. Data is stored temporarily and cleared after daily processing.

= When are scans performed? =
After each full day, via WP-Cron.

= How does it detect anomalies? =
Yesterday's numbers are compared to the average of the previous 7 days.

= What does a 404 alert show? =
The top 3 broken URLs that day, with hit counts and suggestions.

= What does a bot alert show? =
The top 3 bot names (e.g., Googlebot, Bingbot) that visited your site that day, with visit counts and suggestions.

= Will you add other alert types? =
Yes. Future updates will bring more anomaly types (slow pages, server errors, etc.) and integration with popular analytics plugins to pull data instead of logging — always optional.

= Is it free? =
Yes. Licensed GPL-2.0-or-later.

== Screenshots ==

1. Main dashboard with critical issue indicator and weekly digest.
2. Example traffic drop alert — percentage change, impact summary, and action checklist.
3. Example 404 surge alert with top 3 broken URLs and hit counts.
4. Example bot alert with top 3 bot names and percentage change.

== Changelog ==

= 1.0.5 =
* Fix: Resolved database table creation bug that prevented tables from being created on plugin activation
* Update: Bot detection patterns upgraded with 1500+ new bot signatures (GPTBot, ClaudeBot, AmazonBot, etc.)
* Improvement: Enhanced bot detection accuracy with improved User-Agent parsing and reduced false positives
* Performance: Optimized bot detection function for faster processing and lower memory usage
* Stability: Improved error handling during database updates and cron job execution

= 1.0.4 =
* Feat: Bot traffic detection — separate human vs bot pageviews
* Feat: Bot anomaly alerts — spike and drop detection with Top 3 bots
* Dashboard: New "Bot Alerts" KPI card and bot pageviews column in history table
* Dashboard: Bot alert cards with top bot names and actionable recommendations
* Performance: Combined bot pattern regex (1500+ patterns) in single static file
* Database: Added bot_pageviews and top_bots_json columns to daily_stats table

= 1.0.3 =
* Fix: Alert messages now fully translatable via WordPress i18n functions
* Database: Removed redundant "title" column for cleaner table structure

= 1.0.2 =
* Added: RTL support for WordPress admin dashboard

= 1.0.1 =
* Fix: Prevent duplicate alerts in digest cards
* Feat: Add percentage change for 404 errors

= 1.0.0 =
* Initial release
* Traffic drop/spike detection
* 404 surge detection with Top 404 URLs
* Dashboard (critical issues, digest, alerts, 7‑day history)
* Actionable recommendations
* Daily WP-Cron scans

== Upgrade Notice ==

= 1.0.5 =
Fixes a critical table creation bug on activation, updates bot detection patterns with 1500+ new signatures, and improves overall detection accuracy. Safe automatic update. No manual action required.

= 1.0.4 =
Adds bot traffic detection with anomaly alerts, new dashboard cards, and separate bot pageview tracking. Includes database schema changes (new columns). Safe automatic update.

= 1.0.3 =
This update makes alert messages translation-ready and removes a redundant database column. Safe automatic update. No manual action required.

= 1.0.2 =
Added RTL language support for admin dashboard. Safe automatic update.

= 1.0.1 =
Important: Removes duplicate digest entries and adds 404 change percentage. Safe automatic update.

= 1.0.0 =
Initial release.