=== Proactive Site Advisor – Privacy‑First Anomaly Alerts ===
Contributors: zheynlab
Tags: anomaly detection, site monitoring, traffic alerts, 404 errors, bot detection
Requires at least: 6.1
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Get early warnings on anomalies like traffic drops, 404 surges, and bot spikes. Privacy‑friendly local monitoring with actionable next steps.

== Description ==

= Your site talks. Don't wait until it screams. =

Most site problems — ranking drops, broken links, bot attacks — don't show up in your dashboard. They simmer silently until a visitor complains or your SEO takes a hit. Proactive Site Advisor acts as your first line of defense, scanning your site daily and alerting you the moment something goes off track.

No Google Analytics needed. No external services. No complicated dashboards. Just clear, actionable alerts inside WordPress.

= Why Proactive Site Advisor? =

**Alerts you can actually act on**
When an anomaly is detected, you don't just get a number. You get a human-readable summary that tells you what changed, what it means, and exactly what to check next.

Here’s a real example of a traffic drop alert:

> **Traffic drop — July 9, 2026**
> Traffic dropped by 44.98% compared to recent days.
>
> *What this means:* Sudden drops are often caused by downtime or recent changes.
>
> *What you should check next:*
> - Check if your site is reachable
> - Review recent plugin or theme changes
> - Look for increases in 404 errors

404 alerts include the top 3 broken URLs with fix suggestions. Bot alerts list the top 3 crawlers by name with visit counts and context.

This is just the beginning. We're actively building new alert types based on real user feedback — slow page detection, server error monitoring, and more — so your site advisor gets smarter over time.

**Built for privacy and performance**
The plugin never phones home. All data is collected, summarized, and stored inside your own database. We use lightweight tables that hold only the last 7 days of aggregated metrics. No personal visitor data is ever saved. No cookies. No front‑end scripts. GDPR‑friendly by design.

**Zero‑configuration monitoring**
Install, activate, done. The plugin starts logging and comparing data from day one. No API keys, no tracking codes, no setup wizard. It just works.

= Features =

* **Bot anomaly detection** – Sudden changes in bot visits (crawlers, scrapers) with top 3 bot names.
* **Human traffic monitoring** – Drops or spikes compared to the previous 7‑day average.
* **404 error surge alerts** – Top 3 broken URLs with hit counts and fix suggestions.
* **Actionable recommendations** – Every alert includes a "What you should check next" list.
* **Daily WP‑Cron scans** – Automatic checks after each full day.
* **100% local processing** – No external APIs, zero data leaves your server.
* **Atomic database operations** – Reliable metric collection without race conditions.
* **Accurate bot detection** – 1,500+ bot signatures, updated regularly.
* **Future‑ready** – More anomaly types (slow pages, server errors, plugin conflicts) are planned.

= Privacy & Performance by Design =

* **Truly self‑hosted** – All statistics stay in your WordPress database. We never see your data.
* **No personal data** – Only daily summaries. No IP addresses, no visitor profiles.
* **Ultra‑light footprint** – Two small database tables, no front‑end scripts, zero impact on page speed.
* **GDPR/CCPA friendly** – No cookies, no cross‑site tracking, no consent banner needed for monitoring.

= Who is Proactive Site Advisor for? =

* **Site owners who hate surprises** – Know the moment traffic dips or errors rise, before clients notice.
* **Agencies managing multiple sites** – Proactive alerts let you fix issues before reports go out.
* **SEO and content teams** – Catch 404s and broken links instantly, protecting your rankings.
* **Privacy‑conscious WordPress users** – Get site insights without giving data to third parties.

= Installation =

1. Download the plugin zip file.
2. In your WordPress dashboard, go to **Plugins → Add New → Upload Plugin**.
3. Choose the zip file and click **Install Now**, then **Activate**.
4. Visit the new **Site Advisor** menu in your admin sidebar.

That's it. No configuration pages to fill out, no API connections to set up. Proactive Site Advisor starts monitoring immediately.

== Frequently Asked Questions ==

= Will the plugin fix problems automatically? =
No. It's an advisor, not an autopilot. It tells you what's wrong and what to check, so you stay in full control.

= Where does the traffic and 404 data come from? =
The plugin hooks into WordPress to log page views and 404 errors. Data is aggregated daily and old raw logs are deleted – only the summarized metrics stay.

= When do the scans happen? =
At the end of each day, via WordPress Cron. You don't need to click anything.

= How does it know something is wrong? =
It compares yesterday's numbers to the average of the previous 7 days. A significant deviation triggers an alert.

= What exactly does a 404 alert show? =
The three most-hit broken URLs from that day, with the number of hits and a plain‑English suggestion (e.g., "Set up a redirect from /old-page to /new-page").

= What does a bot alert show? =
The top three bot names (like Googlebot, AhrefsBot) with visit counts and context on whether their activity is unusual.

= Will you add more alert types? =
Absolutely. Planned additions include slow page alerts, server error detection, and optional privacy‑friendly integration with popular analytics plugins — always keeping your data local.

= Is it free? =
The core plugin is and will remain free, licensed under GPL-2.0-or-later. A Pro version with advanced features is planned for the future, which will help support ongoing development.

= Does it affect site speed? =
No. The plugin has zero front‑end footprint. All processing happens in the background after page load, using efficient database queries. Your visitors won't notice it.

== Screenshots ==

1. Main dashboard with critical issue indicator and weekly digest.
2. Traffic drop alert – percentage change, impact summary, and action checklist.
3. 404 surge alert with top 3 broken URLs and hit counts.
4. Bot alert with top 3 bot names and percentage change.

== Changelog ==

= 1.1.0 =
* New: Admin settings page with Alerts and Thresholds sections
* Improved: Settings validation and sanitization with range limits (5–100%)
* New: BehavioralSignal – detects bots with unnaturally regular request timing
* New: Definitive bot detection for IPs rotating 4+ distinct User-Agents
* Improved: Accurate burst rate detection without timestamp interference
* Removed: Redundant scoring logic from BrowserHeadersSignal
* Bot detection: Separated hard bot signals from suspicion scoring – high‑confidence signals now trigger direct bot classification.
* Firefox support: Extended missing client‑hints detection to modern Firefox browsers.
* Tuning: Improved burst detection sensitivity for 3‑request‑in‑2‑second patterns.
* Performance: Removed redundant per‑request scoring cache.

= 1.0.8 =
* New: Atomic rate counter for burst detection without race conditions
* New: Daily fingerprint tracking to retroactively correct bot pageviews
* Improved: Burst detection now reliable under heavy concurrent requests
* Improved: PHP 8.1+ compatibility – fixed pathinfo(null) deprecation
* Improved: Real-time bot traffic correction – human counts no longer inflated
* Database: Added transferPageviewsToBot() for accurate daily corrections
* Retention: Automatic cleanup of expired rate counters and old fingerprints (7‑day window)
* Fix: RateCounter no longer returns 0 after immediate read (burst detection reliability)
* Code quality: Centralized fingerprint generation in HeaderReader
* Performance: All rate counting now atomic and fully self‑contained
* New: Detection of browser version mismatch between User-Agent and Sec-CH-UA headers
* New: Detection of IPs rotating multiple User-Agents within a short window
* Improved: Suspicion score threshold lowered to catch more advanced bots

= 1.0.7 =
* Improvement: Fine-tuned bot fingerprinting for better precision.

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

= 1.1.0 =
New settings page for managing alerts and thresholds, plus major bot detection improvements including behavioral analysis, Firefox client-hints support, and a cleaner scoring architecture. Safe automatic update – review the new settings to customize your alert thresholds.

= 1.0.8 =
Atomic burst detection, retroactive bot pageview correction, and PHP 8.1+ fixes. Safe automatic update.

= 1.0.7 =
Fine-tuned bot fingerprinting for better precision. Safe automatic update – no action needed.

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

== Source Code ==
Source code and build tools are available at: https://github.com/proactive-site-advisor/proactive-site-advisor