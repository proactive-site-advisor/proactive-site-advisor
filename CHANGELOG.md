# Changelog



= 1.2.0 – 2026-08-21 =

* New: Daily Email Digest – automatic daily email summary after cron run when alerts are detected.

* New: Email content includes total alerts, alert types with percentage changes, recommendations summary, and direct link to alerts dashboard.

* New: Email settings – enable/disable, recipient email (default: admin email), and alert type checkboxes (Traffic, 404, Bot).

* New: Three-level severity for traffic spike alerts – `info`, `warning`, and `critical` (previously only `info`).

* New: Severity now dynamically calculated based on spike intensity relative to user‑defined threshold.



= 1.1.1 – 2026-08-6 =

* New: AcceptEncodingDeflateSignal – dedicated signal for missing `deflate` in browser Accept-Encoding headers.

* New: SecFetchSiteNoneWithRefererSignal – dedicated signal for detecting contradictory Fetch Metadata and Referer headers.

* New: Http2CleartextUpgradeSignal – detects non-browser HTTP/2 cleartext upgrade attempts.

* New: SecFetchUserSignal – detects missing Sec-Fetch-User header on top-level navigations from modern browsers.

* New: Alert patterns – highlights recurring changes over recent days.

* New: Related activity context – shows when multiple anomaly types are detected together.

* New: Centralized alert text definitions into the `alerts.php` configuration file, adding shared alert strings and improving message consistency.

* New: Improved alert explanations with clearer context, severity details, and actionable next steps.



= 1.1.0 – 2026-07-30 =

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



= 1.0.8 – 2026-07-20 =

* New: Atomic rate counter for burst detection without race conditions

* New: Daily fingerprint tracking to retroactively correct bot pageviews

* Improved: Burst detection now reliable under heavy concurrent requests

* Improved: PHP 8.1+ compatibility – fixed pathinfo(null) deprecation

* Improved: Real-time bot traffic correction – human counts no longer inflated

* Database: Added transferPageviewsToBot() for accurate daily corrections

* Retention: Automatic cleanup of expired rate counters and old fingerprints

* Fix: RateCounter no longer returns 0 after immediate read (burst detection reliability)

* Code quality: Centralized fingerprint generation in HeaderReader

* Performance: All rate counting now atomic and fully self‑contained

* New: Detection of browser version mismatch between User-Agent and Sec-CH-UA headers

* New: Detection of IPs rotating multiple User-Agents within a short window

* Improved: Suspicion score threshold lowered to catch more advanced bots



= 1.0.7 – 2026-07-13 =

* Improvement: Fine-tuned bot fingerprinting for better precision.



= 1.0.6 – 2026-07-03 =

* Fix: Daily metrics now stored in durable database instead of volatile cache to prevent data loss on cache clear

* Fix: Resolved incorrect bot classification on local development environments

* Database: Added atomic increment and JSON map update methods to DailyStats model

* Performance: Removed cache-to-database sync cron job (data now written in real-time)

* Stability: Eliminated race conditions in metric collection using atomic database operations

* Improvement: Simplified browser validation on localhost for accurate local development testing

* Improvement: Refined bot detection algorithm for more accurate bot identification and fewer false positives

* Refactor: Unified cache clearing workflow across plugin installation, updates, activation, and deactivation

* Performance: Optimized plugin lifecycle operations for faster and more consistent execution

* Stability: Improved cache invalidation after plugin lifecycle events



= 1.0.5 – 2026-07-01 =

* Fix: Database tables now created correctly on plugin activation (resolved table creation bug)

* Update: Bot detection patterns updated with 1500+ new bot signatures (GPTBot, ClaudeBot, AmazonBot, etc.)

* Improvement: Enhanced bot detection accuracy with better User-Agent parsing and reduced false positives

* Performance: Optimized bot detection function for faster processing

* Stability: Improved error handling during table updates and cron jobs



= 1.0.4 – 2026-06-28 =

* Feat: Bot traffic detection – separate human vs bot pageviews

* Feat: Bot anomaly alerts – spike and drop detection with Top 3 bots

* Dashboard: New "Bot Alerts" KPI card and bot pageviews column in history table

* Dashboard: Bot alert cards with top bot names and actionable recommendations

* Performance: Combined bot pattern regex (1500+ patterns) in single static file

* Database: Added bot_pageviews and top_bots_json columns to daily_stats table

* Improvement: 404 alerts now display Top 3 broken URLs for consistency

* Improvement: FAQ and documentation expanded with bot detection details



= 1.0.3 – 2026-06-25 =

* Fix: Make alert messages translation-ready by removing hardcoded "title" column from database and using WordPress i18n functions

* Database: Remove redundant "title" column to optimize table structure



= 1.0.2 – 2026-06-21 =

* Added: RTL support for WordPress admin dashboard



= 1.0.1 – 2026-06-16 =

* Fix: Prevent duplicate alerts in digest cards

* Feat: Add percentage change for 404 errors



## 1.0.0 – 2026-05-21



### Initial Release



#### Core Features

- Traffic drop detection (completed day vs. last 7 days)

- Traffic spike detection (completed day vs. last 7 days)

- 404 error surge detection with Top 404 URLs (completed day vs. last 7 days)

- 7-day rolling trend analysis



#### Dashboard (Site Advisor menu)

- Critical issues indicator (color‑coded)

- Weekly Digest cards (Critical, Traffic, 404, Total alerts)

- Latest Alerts list (full details: What this means, What to check next, Top 404 URLs)

- 7-Day History table (daily traffic and 404 counts, averages)



#### Alert Structure

- Title and date

- Metric change (e.g., "Traffic dropped by 41%")

- Short description

- "What this means" – impact explanation

- "What you should check next" – actionable recommendations (no automatic fixes)

- Top 404 URLs (for 404 alerts)



#### Technical

- Daily WP-Cron scan (runs after full day completion)

- Local storage with caching (rolling 7-day window)

- No external APIs



#### Notes

- Zero configuration required

- Plugin does not fix anything automatically

- Designed for shared hosting, VPS, dedicated servers

