# Proactive Site Advisor

**Never miss a traffic drop, 404 surge, or bot anomaly. Daily, local monitoring alerts you instantly — with more alert types on the way.**

Most WordPress issues stay hidden until they hurt you. **Proactive Site Advisor** silently watches your site every day, compares activity to the previous week, and immediately alerts you when something changes. It now separates real visitors from bots, so you see exactly how much traffic is human vs. crawler.

Everything stays 100% local—no external APIs, no data leaving your server. The plugin logs, processes, and summarizes data using lightweight database tables and atomic operations. Zero performance overhead.

The plugin adds a **"Site Advisor"** dashboard in your admin with:

- **Critical issues indicator** (color‑coded)
- **Weekly digest cards** (Critical, Traffic, Bot, 404 alerts)
- **Latest alerts list** – what changed, why it matters, what to check next, and Top 3 broken URLs or Top 3 bot names
- **7‑day history table** – daily human traffic, bot traffic, and 404 errors, plus averages

**When an anomaly is detected, each alert includes:**
- What changed (e.g., "Traffic dropped by 41%")
- What this means for your site
- What you should check next
- Top 3 broken URLs (for 404 alerts)
- Top 3 bot names (for bot alerts)

## Privacy & Performance

- **100% local** – no data leaves your server, no external APIs
- Stores only daily summarized metrics - no personal or visitor data is ever saved
- Only two lightweight tables keep the last 7 days of stats and generated alerts
- No cookies, no cross‑site tracking, GDPR‑friendly by design
- Atomic database operations prevent race conditions and data loss

The plugin does **not** fix anything automatically — it only alerts and recommends so you stay in full control.

## Key Features

- **Bot traffic anomaly detection** (surge/drop) with Top 3 bots
- **Human traffic drop/spike detection** (completed day vs. 7‑day baseline)
- **404 error surge detection** with Top 3 broken URLs
- Actionable "What you should check next" lists
- Daily WP‑Cron scan (lightweight, cached)
- Accurate bot detection powered by **1500+ bot signatures** (Googlebot, GPTBot, ClaudeBot, etc.)
- Fully local – zero configuration, no external requests
- Future‑ready: more anomaly types planned

## Requirements

- WordPress 6.1 or higher
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB equivalent

## Installation

1. Upload `proactive-site-advisor` to `/wp-content/plugins/`
2. Activate through **Plugins → Installed Plugins**
3. Go to the **Site Advisor** menu to see your alerts

## Frequently Asked Questions

### Does the plugin fix anything automatically?
No. It only alerts and recommends. You decide what to do.

### Where do the traffic and 404 data come from?
The plugin logs page views and 404 errors via WordPress hooks. Data is stored temporarily and cleared after daily processing.

### When does the plugin scan?
After each full day is completed, via WP‑Cron. The current day is not included.

### How does it detect anomalies?
Yesterday’s numbers are compared to the average of the previous 7 days. Significant deviations trigger an alert.

### What does a 404 alert show?
The top 3 broken URLs that day, with hit counts and fix suggestions.

### What does a bot alert show?
The top 3 bot names (e.g., Googlebot, Bingbot) that visited that day, with visit counts and recommendations.

### Will you add other alert types?
Yes. Future updates will bring more anomaly types (slow pages, server errors, etc.) and optional integration with popular analytics plugins — always privacy‑first.

### Does this affect performance?
No. Raw data is temporarily cached, processed once daily, and cleared. Only two lightweight database tables persist, with zero impact on page load.

### Is it free?
Yes. Licensed under **GPL-2.0-or-later**.