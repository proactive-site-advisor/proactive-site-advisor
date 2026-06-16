\# Changelog



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

