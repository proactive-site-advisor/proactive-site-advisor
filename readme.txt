=== Proactive Site Advisor – Local, privacy-first site alerts ===
Contributors: sitealerts
Tags: traffic, 404, monitoring, notifications, dashboard
Requires at least: 6.1
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Don't let traffic drops or 404 surges go unnoticed. Daily monitoring alerts you the moment something's off.

== Description ==

Most WordPress problems stay hidden until they cost you. Proactive Site Advisor watches your site daily, compares activity to the past week, and alerts you the second something deviates from normal. Traffic crashes, 404 spikes, and more alert types are on the way — all monitored locally, no configuration.

When an anomaly is detected, you see:
- What changed (e.g., "Traffic dropped by 41%")
- What this means for your site
- What you should check next
- Top 404 URLs (for 404 alerts)

Dashboard view includes: critical issues indicator, weekly digest cards, latest alerts list, and a 7‑day history table with averages.

How it works: raw visitor and 404 data is temporarily stored in WordPress transients, processed once daily, then cleared. Only summarized stats (7‑day rolling) and generated alerts are saved in two lightweight tables. No performance overhead.

= Privacy & Performance =

* 100% local — no data leaves your server, no external APIs
* Visitor and 404 logs are stored in WordPress transients, processed once daily, then cleared
* Only two lightweight tables keep the last 7 days of stats and generated alerts
* No cookies, no tracking across sites, GDPR‑friendly by design

All monitoring runs locally. No external APIs. No data leaves your server. The plugin does not fix anything — it alerts and recommends so you stay in control.

== Key Features ==

* Traffic drop/spike detection (7‑day baseline)
* 404 error surge detection with Top 404 URLs
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
The most-hit broken URLs that day, with hit counts and suggestions.

= Will you add other alert types? =
Yes. Future updates will bring more anomaly types (slow pages, server errors, etc.) and integration with popular analytics plugins to pull data instead of logging — always optional.

= Is it free? =
Yes. Licensed GPL-2.0-or-later.

== Screenshots ==

1. Main dashboard with critical issue indicator and weekly digest.
2. Example traffic drop alert — percentage change, impact summary, and action checklist.
3. Example 404 surge alert with top broken URLs and hit counts.

== Changelog ==

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

= 1.0.2 =
Added RTL language support for admin dashboard. Safe automatic update.

= 1.0.1 =
Important: Removes duplicate digest entries and adds 404 change percentage. Safe automatic update.

= 1.0.0 =
Initial release.