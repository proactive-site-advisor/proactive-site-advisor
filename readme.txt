=== Proactive Site Advisor ===
Contributors: sitealerts
Tags: traffic, 404, monitoring, notifications, dashboard
Requires at least: 6.1
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Alerts for traffic drops, spikes, and 404 surges — based on completed day vs. last 7 days.

== Description ==

Proactive Site Advisor adds a "Site Advisor" dashboard to your WordPress admin. A daily scan runs after each full day and compares that day's data with the previous 7 days.

When a significant deviation is detected, you see an alert with:
- What changed (e.g., "Traffic dropped by 41%")
- What this means for your site
- What you should check next
- Top 404 URLs (for 404 alerts)

Dashboard includes: critical issues indicator, weekly digest cards, latest alerts list, and 7‑day history table with averages.

The plugin does NOT fix anything. It only alerts and recommends.

All monitoring runs locally. No external APIs, no data sharing.

== Key Features ==

* Traffic drop/spike detection (7‑day baseline)
* 404 error surge detection with Top 404 URLs
* "Site Advisor" dashboard (digest, history, latest alerts)
* Actionable "What you should check next" lists
* Daily WP-Cron scan after day completion (cached)
* Zero configuration

== Installation ==

1. Upload `proactive-site-advisor` to `/wp-content/plugins/`
2. Activate from Plugins -> Installed Plugins
3. Visit **Site Advisor** menu

== Frequently Asked Questions ==

= Does the plugin fix anything automatically? =
No. It only alerts and recommends.

= When are scans performed? =
After each full day is completed.

= How does it detect anomalies? =
Compares completed day vs. last 7 days.

= What does a 404 alert show? =
Top 404 URLs with hit counts, plus recommendations.

= Is it free? =
Yes. GPL-2.0-or-later.

== Changelog ==

= 1.0.0 =
* Initial release
* Traffic drop/spike detection (completed day vs. last 7 days)
* 404 surge detection with Top 404 URLs
* Site Advisor dashboard (critical issues, digest, latest alerts, 7‑day history)
* Actionable recommendations
* Daily WP-Cron scans

== Upgrade Notice ==

= 1.0.0 =
Initial release.