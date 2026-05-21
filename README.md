# Proactive Site Advisor

Proactive Site Advisor monitors your WordPress site daily and alerts you when traffic or 404 errors deviate from the pattern of the last 7 days.

The plugin adds a "Site Advisor" dashboard in your admin with:

- Critical issues indicator (color‑coded)
- Weekly digest cards (Critical, Traffic, 404, Total alerts)
- Latest alerts list (what changed, why it matters, what to check next, Top 404 URLs)
- 7‑day history table (daily traffic and 404 errors, plus averages)

Alerts are created after each full day is completed. The plugin does not fix anything automatically — it only alerts and recommends what to check.

## Features

- Traffic drop detection (completed day vs. last 7 days)
- Traffic spike detection (completed day vs. last 7 days)
- 404 error surge detection with Top 404 URLs
- Clear "What this means" explanations
- Actionable "What you should check next" lists
- Daily WP-Cron scan (lightweight, cached)
- Dedicated dashboard with alert history and digest
- Zero configuration – works on activation
- Fully local – no external APIs, no data sharing

## Requirements

- WordPress 6.1 or higher
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB equivalent

## Installation

1. Upload `proactive-site-advisor` to `/wp-content/plugins/`
2. Activate through Plugins -> Installed Plugins
3. Go to **Site Advisor** menu to see alerts

## Frequently Asked Questions

### Does the plugin fix problems automatically?
No. It only alerts and recommends. You decide what to do.

### When does the plugin scan?
After each full day is completed. The current day is not included.

### How does it detect abnormal?
It compares the completed day's data against the pattern of the last 7 days. Significant deviations trigger an alert.

### What does a 404 alert include?
Date, change percentage, explanation, "What this means", "What you should check next", and a Top 404 URLs list with hit counts.

### Does this affect performance?
No. Daily WP-Cron, cached, optimized.

### Does it use external services?
No. Everything runs locally.