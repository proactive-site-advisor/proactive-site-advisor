=== Proactive Site Advisor – Privacy‑First Anomaly Alerts ===
Contributors: zheynlab
Tags: anomaly detection, site monitoring, traffic alerts, 404 errors, bot detection
Requires at least: 6.1
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Get early warnings on traffic drops, 404 surges, and bot spikes. Privacy‑friendly anomaly detection that tells you when, why, and what to check next.

== Description ==

= Your site talks. Don't wait until it screams. =

Most site problems — ranking drops, broken links, bot attacks — don't show up in your dashboard. They simmer silently until a visitor complains or your SEO takes a hit. Proactive Site Advisor acts as your first line of defense. It starts collecting daily traffic, 404, and bot data immediately after activation, builds a 7-day baseline to understand your site's normal patterns, and then alerts you as soon as something deviates from that baseline.

No Google Analytics needed. No external services. No complicated dashboards. Just clear, actionable alerts inside WordPress.

= What Proactive Site Advisor Does (and Doesn't Do) =

**Does:**
* Detects anomalies in human traffic, 404 errors, and bot activity.
* Separates human traffic from bot traffic so your metrics stay accurate.
* Tells you when something changed, why it likely happened, and what to check next.

**Doesn't:**
* Block bots or change how requests are handled.
* Fix broken links, traffic drops, or server errors automatically.
* Send your data to external services or third parties.

= Why Proactive Site Advisor? =

**Alerts you can actually act on**
When an anomaly is detected, you don't just get a number. You get a human-readable summary that tells you what changed, what it means, and exactly what to check next.

Here’s a real example of a traffic drop alert:

> **Traffic — August 2, 2026**
>
> **Traffic dropped 41%**
> Your human traffic decreased sharply compared to recent activity.
>
> *What this means:*
> A decrease in human traffic means fewer real visitors reached your site compared to your normal activity. This does not always indicate a problem and can happen after website changes, availability issues, visibility changes, broken links, or changes in visitor behavior.
>
> *Why this alert?*
> The decrease exceeded your configured threshold of 30% by a significant margin, indicating an unusual deviation from your recent traffic pattern.
>
> Today: 445 · 7-day average: 754 · Change: -41%
>
> *Pattern:*
> This alert has appeared for the second consecutive day.
>
> *Related activity:*
> Detected together with: 404 Errors, Bot Activity.
>
> *What you should check next:*
> - Verify that your most important pages are available and responding correctly.
> - Review major recent changes, migrations, deployments, or settings updates that may have affected visitor access.
> - Check that your site is loading normally and important pages are accessible.
> - Review recent content updates, deleted posts, or changes to important pages.

404 alerts include the top 3 broken URLs with fix suggestions. Bot alerts list the top 3 crawlers by name with visit counts and context.

This is just the beginning. The free plugin will keep improving with new read‑only integrations and detection refinements, while more advanced anomaly types — like slow page detection and server error surge detection — are planned for the Pro version.

**Built for privacy and performance**
The plugin never phones home. All data is collected, summarized, and stored inside your own database. We use lightweight tables that hold only the last 7 days of aggregated metrics. No personal visitor data is ever saved. No cookies. No front‑end scripts. GDPR‑friendly by design.

**Zero‑configuration monitoring**
Install, activate, done. The plugin starts logging data from day one and begins anomaly detection after a 7-day baseline period. No API keys, no tracking codes, no setup wizard. Optional settings are available under **Site Advisor → Settings** if you want to customize alert thresholds or email notifications.

= Features =

* **Bot anomaly detection** – Detects unusual crawler activity with top bot names and auto-corrected traffic counts.
* **Bot-aware traffic correction** – Detects bot traffic and separates it from human visits, so your metrics stay accurate and anomaly detection remains reliable.
* **Human traffic monitoring** – Drops or spikes compared to the previous 7‑day average.
* **404 error surge alerts** – Top 3 broken URLs with hit counts and fix suggestions.
* **Actionable recommendations** – Every alert includes a "What you should check next" list.
* **Daily WP‑Cron scans** – Automatic checks after each full day.
* **100% local processing** – No external APIs, zero data leaves your server.
* **Atomic database operations** – Reliable metric collection without race conditions.
* **Accurate bot detection** – 1,500+ built-in bot signatures, refined with each plugin release.
* **Future‑ready** – Free roadmap adds read‑only analytics and security integrations; advanced anomaly types like slow pages and server errors planned for Pro.

= Privacy & Performance by Design =

* **Truly self‑hosted** – All statistics stay in your WordPress database. We never see your data.
* **No personal data** – Only daily summaries. No IP addresses, no cookies.
* **Ultra‑light footprint** – Lightweight background processing, zero front-end impact.
* **GDPR/CCPA friendly** – No cookies, no cross‑site tracking, no consent banner needed for monitoring.

= Who is Proactive Site Advisor for? =

* **Site owners who hate surprises** – Know the moment traffic dips or errors rise, before clients notice.
* **Agencies managing multiple sites** – Proactive alerts let you fix issues before reports go out.
* **SEO and content teams** – Catch 404s and broken links instantly, protecting your rankings.
* **Privacy‑conscious WordPress users** – Get site insights without giving data to third parties.

= Watch a Quick Demo =

[Watch the demo](https://youtu.be/m6ZQkGUT8e0)

== Installation ==

1. Download the plugin zip file.
2. In your WordPress dashboard, go to **Plugins → Add New → Upload Plugin**.
3. Choose the zip file and click **Install Now**, then **Activate**.
4. Visit the new **Site Advisor** menu in your admin sidebar.

That's it. No required configuration, no API connections to set up. Proactive Site Advisor starts collecting data immediately. Optional settings are available under **Site Advisor → Settings** if you want to customize thresholds or email alerts.

== Frequently Asked Questions ==

= Will the plugin fix problems automatically? =
No. It's an advisor, not an autopilot. It tells you what's wrong and what to check, so you stay in full control.

= Where does the traffic and 404 data come from? =
The plugin hooks into WordPress to log page views and 404 errors. Data is aggregated daily and old raw logs are deleted – only the summarized metrics stay.

= When do the scans happen? =
At the end of each day, via WordPress Cron. You don't need to click anything.

= How does it know something is wrong? =
It compares yesterday's numbers to the average of the previous 7 days. A significant deviation triggers an alert. During the first 7 days after activation, the plugin only builds the baseline and does not generate alerts. Anomaly alerts can appear from day 8 onward.

= Why don't I see alerts immediately after activation? =
The plugin needs 7 full days of data to understand your site's normal traffic, 404, and bot patterns. After that baseline is built, it starts comparing daily values and generating alerts only when thresholds are exceeded.

= What exactly does a 404 alert show? =
The three most-hit broken URLs from that day, with the number of hits and a plain‑English suggestion (e.g., "Set up a redirect from /old-page to /new-page").

= What does a bot alert show? =
The top three bot names (like Googlebot, AhrefsBot) with visit counts and context on whether their activity is unusual.

= Will you add more alert types and integrations? =
Absolutely. Upcoming free releases will add read‑only integrations with popular analytics plugins such as WP Statistics, Burst Statistics, MonsterInsights, and Site Kit by Google. Later free security signal integrations are planned for Wordfence and Solid Security. Advanced anomaly types like slow page detection and server error surge detection are planned for the Pro version.

= Is it free? =
The core plugin is and will remain free, licensed under GPL-2.0-or-later. A Pro version with advanced features is planned for the future, which will help support ongoing development.

= Does it affect site speed? =
No. The plugin has zero front‑end footprint. All processing happens in the background after page load, using efficient database queries. Your visitors won't notice it.

== Screenshots ==

1. Main dashboard with critical issue indicator and weekly digest.
2. Traffic drop alert – percentage change, impact summary, and action checklist.
3. 404 surge alert with top 3 broken URLs and hit counts.
4. Bot alert with top 3 bot names and percentage change.
5. Detection thresholds and alert toggles in the settings screen.
6. Daily email digest settings and sample email content.

== Changelog ==

= 1.2.1 =
* Improved: Email notifications now use a dedicated sender name and email address for clearer and more consistent email identification.

= 1.2.0 =
* New: Daily Email Digest – automatic daily email summary after cron run when alerts are detected.
* New: Email content includes total alerts, alert types with percentage changes, recommendations summary, and direct link to alerts dashboard.
* New: Email settings – enable/disable, recipient email (default: admin email), and alert type checkboxes (Traffic, 404, Bot).
* New: Three-level severity for traffic spike alerts – `info`, `warning`, and `critical` (previously only `info`).
* New: Severity now dynamically calculated based on spike intensity relative to user‑defined threshold.

= 1.1.1 =
* New: AcceptEncodingDeflateSignal – dedicated signal for missing `deflate` in browser Accept-Encoding headers.
* New: SecFetchSiteNoneWithRefererSignal – dedicated signal for detecting contradictory Fetch Metadata and Referer headers.
* New: Http2CleartextUpgradeSignal – detects non-browser HTTP/2 cleartext upgrade attempts.
* New: SecFetchUserSignal – detects missing Sec-Fetch-User header on top-level navigations from modern browsers.
* New: Alert patterns – highlights recurring changes over recent days.
* New: Related activity context – shows when multiple anomaly types are detected together.
* New: Centralized alert text definitions into the `alerts.php` configuration file, adding shared alert strings and improving message consistency.
* New: Improved alert explanations with clearer context, severity details, and actionable next steps.

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
* New: ClientHintsSignal – dedicated signal for Sec-CH-UA header analysis
* New: AcceptLanguageSignal – dedicated signal for Accept-Language analysis
* New: FetchHeadersSignal – dedicated signal for Sec-Fetch-* header analysis
* New: DistinctUserAgentSignal – dedicated signal for multi-UA detection
* New: MissingHeadersSignal – score for missing common browser headers
* New: BrowserHelper – centralized browser detection utility
* New: ClientHintsHelper – centralized Client Hints analysis utility
* Improved: Cleaner signal architecture with focused, single-responsibility classes
* Improved: Better code maintainability and testability through class decomposition
* Removed: Monolithic FingerprintSignal – split into 5 focused signals
* Code Quality: Refactored signal architecture following SOLID principles
* Code Quality: Centralized browser and client hints logic in dedicated helpers
* Fixed: Opera browser no longer incorrectly flagged as suspicious due to Client Hints version mismatch.
* Fixed: RefererConsistencySignal no longer penalizes cross‑site navigations with missing Referer header.
* Fixed: Baseline calculation now includes the fully completed day (`stats_date <= %s`) instead of excluding it, ensuring accurate 7‑day averages for anomaly detection.
* Fixed: Dashboard no longer displays incomplete current day data – history and averages now only include fully completed days.
* Fixed: Alert severity (`critical`/`warning`) is now dynamically calculated based on user‑defined thresholds, making severity levels consistent with each site’s custom settings.
* Fixed: Empty `top_404_json` or `top_bots_json` values no longer result in malformed alert metadata – now properly handled as empty arrays.
* Fixed: `FOR UPDATE` locking in `markAsBot()` is now wrapped in an explicit transaction, eliminating race conditions under concurrent requests.
* Removed: Deprecated and unused analyzer classes (`TrafficAnalyzer`, `Error404Analyzer`, `BotTrafficAnalyzer`) – their logic is fully covered by the new Generator architecture.

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

= 1.2.1 =
Improved email sender identification for daily digest notifications with a dedicated sender name and email address. Safe automatic update.

= 1.2.0 =
New daily email digest and three-level severity for traffic spike alerts. Safe automatic update.

= 1.1.1 =
New bot detection signals, alert patterns, and related-activity context. Safe automatic update – review alert threshold settings.

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
Source code and build tools are available at: [GitHub Repository](https://github.com/proactive-site-advisor/proactive-site-advisor)
