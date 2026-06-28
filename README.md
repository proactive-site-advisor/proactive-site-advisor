# Proactive Site Advisor

Don't let traffic drops or 404 surges go unnoticed. Daily monitoring alerts you the moment something's off.

Most WordPress problems stay hidden until they cost you. Proactive Site Advisor watches your site daily, compares activity to the past week, and alerts you the second something deviates from normal. **Now with bot detection, you see exactly how much traffic is real vs. crawlers.** Traffic crashes, bot anomalies, 404 spikes — all monitored locally, no configuration. More alert types are on the way.

The plugin adds a "Site Advisor" dashboard in your admin with:

- Critical issues indicator (color‑coded)
- Weekly digest cards (Critical, Traffic, Bot, 404 alerts)
- Latest alerts list (what changed, why it matters, what to check next, Top 3 broken URLs or Top 3 bot names)
- 7‑day history table (daily human traffic, bot traffic, and 404 errors, plus averages)

When an anomaly is detected, each alert includes:

- What changed (e.g., "Traffic dropped by 41%")
- What this means for your site
- What you should check next
- Top 3 broken URLs (for 404 alerts)
- Top 3 bot names (for bot alerts)

## Privacy & Performance

- 100% local – no data leaves your server, no external APIs
- Visitor, bot, and 404 logs are stored in WordPress transients, processed once daily, then cleared
- Only two lightweight tables keep the last 7 days of stats and generated alerts
- No cookies, no tracking across sites, GDPR‑friendly by design

The plugin does **not** fix anything automatically — it only alerts and recommends so you stay in control.

## Features

- Bot traffic anomaly detection (surge/drop) with Top 3 bots
- Traffic drop/spike detection (completed day vs. last 7 days)
- 404 error surge detection with Top 3 broken URLs
- "Site Advisor" dashboard (digest, history, latest alerts)
- Clear "What this means" explanations
- Actionable "What you should check next" lists
- Daily WP-Cron scan (cached, lightweight)
- Zero configuration – works on activation
- Fully local – no external APIs, no data sharing
- Future-ready: more anomaly types planned

## Requirements

- WordPress 6.1 or higher
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB equivalent

## Installation

1. Upload `proactive-site-advisor` to `/wp-content/plugins/`
2. Activate through Plugins → Installed Plugins
3. Go to **Site Advisor** menu to see alerts

## Frequently Asked Questions

### Does the plugin fix anything automatically?
No. It only alerts and recommends. You decide what to do.

### Where do the traffic and 404 data come from?
The plugin logs page views and 404 errors via WordPress hooks. Data is stored temporarily and cleared after daily processing.

### When does the plugin scan?
After each full day is completed, via WP-Cron. The current day is not included.

### How does it detect anomalies?
Yesterday's numbers are compared to the average of the previous 7 days. Significant deviations trigger an alert.

### What does a 404 alert show?
The top 3 broken URLs that day, with hit counts and suggestions.

### What does a bot alert show?
The top 3 bot names (e.g., Googlebot, Bingbot) that visited your site that day, with visit counts and suggestions.

### Will you add other alert types?
Yes. Future updates will bring more anomaly types (slow pages, server errors, etc.) and integration with popular analytics plugins — always optional.

### Does this affect performance?
No. Raw data is temporarily cached, processed once daily, and cleared. Only two lightweight database tables persist, with zero impact on page load.

### Is it free?
Yes. Licensed under GPL-2.0-or-later.