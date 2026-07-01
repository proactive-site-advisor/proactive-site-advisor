\# Changelog

= 1.0.5 – 2026-07-01 =

\* Fix: Database tables now created correctly on plugin activation (resolved table creation bug)

\* Update: Bot detection patterns updated with 1500+ new bot signatures (GPTBot, ClaudeBot, AmazonBot, etc.)

\* Improvement: Enhanced bot detection accuracy with better User-Agent parsing and reduced false positives

\* Performance: Optimized bot detection function for faster processing

\* Stability: Improved error handling during table updates and cron jobs



= 1.0.4 – 2026-06-28 =

\* Feat: Bot traffic detection – separate human vs bot pageviews

\* Feat: Bot anomaly alerts – spike and drop detection with Top 3 bots

\* Dashboard: New "Bot Alerts" KPI card and bot pageviews column in history table

\* Dashboard: Bot alert cards with top bot names and actionable recommendations

\* Performance: Combined bot pattern regex (1500+ patterns) in single static file

\* Database: Added bot\_pageviews and top\_bots\_json columns to daily\_stats table

\* Improvement: 404 alerts now display Top 3 broken URLs for consistency

\* Improvement: FAQ and documentation expanded with bot detection details



= 1.0.3 – 2026-06-25 =

\* Fix: Make alert messages translation-ready by removing hardcoded "title" column from database and using WordPress i18n functions

\* Database: Remove redundant "title" column to optimize table structure



= 1.0.2 – 2026-06-21 =

\* Added: RTL support for WordPress admin dashboard



= 1.0.1 – 2026-06-16 =

\* Fix: Prevent duplicate alerts in digest cards

\* Feat: Add percentage change for 404 errors



\## 1.0.0 – 2026-05-21



\### Initial Release



\#### Core Features

\- Traffic drop detection (completed day vs. last 7 days)

\- Traffic spike detection (completed day vs. last 7 days)

\- 404 error surge detection with Top 404 URLs (completed day vs. last 7 days)

\- 7-day rolling trend analysis



\#### Dashboard (Site Advisor menu)

\- Critical issues indicator (color‑coded)

\- Weekly Digest cards (Critical, Traffic, 404, Total alerts)

\- Latest Alerts list (full details: What this means, What to check next, Top 404 URLs)

\- 7-Day History table (daily traffic and 404 counts, averages)



\#### Alert Structure

\- Title and date

\- Metric change (e.g., "Traffic dropped by 41%")

\- Short description

\- "What this means" – impact explanation

\- "What you should check next" – actionable recommendations (no automatic fixes)

\- Top 404 URLs (for 404 alerts)



\#### Technical

\- Daily WP-Cron scan (runs after full day completion)

\- Local storage with caching (rolling 7-day window)

\- No external APIs



\#### Notes

\- Zero configuration required

\- Plugin does not fix anything automatically

\- Designed for shared hosting, VPS, dedicated servers

