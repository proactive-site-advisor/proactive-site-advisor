# Proactive Site Advisor – Privacy‑First Anomaly Alerts

**Get early warnings on traffic drops, 404 surges, and bot spikes. Privacy‑friendly anomaly detection that tells you when, why, and what to check next.**

Your site talks. Don't wait until it screams. Most WordPress issues — ranking drops, broken links, bot attacks — stay hidden until they hurt you. **Proactive Site Advisor** acts as your first line of defense. It starts collecting daily traffic, 404, and bot data immediately after activation, builds a 7-day baseline to understand your site's normal patterns, and then alerts you as soon as something deviates from that baseline. It separates real visitors from bots, so you see exactly how much traffic is human vs. crawler.

Everything stays 100% local — no external APIs, no data leaving your server. The plugin logs, processes, and summarizes data using lightweight database tables and atomic operations. Zero performance overhead. No cookies. No front‑end scripts.

## What Proactive Site Advisor Does (and Doesn't Do)

**Does:**
- Detects anomalies in human traffic, 404 errors, and bot activity.
- Separates human traffic from bot traffic so your metrics stay accurate.
- Tells you when something changed, why it likely happened, and what to check next.

**Doesn't:**
- Block bots or change how requests are handled.
- Fix broken links, traffic drops, or server errors automatically.
- Send your data to external services or third parties.

The plugin adds a **"Site Advisor"** dashboard in your admin with:

- **Critical issues indicator** (color‑coded)
- **Weekly digest cards** (Critical, Traffic, Bot, 404 alerts)
- **Latest alerts list** – each alert answers: what changed, why it matters, and what to check next
- **Top 3 broken URLs** (for 404 alerts) and **Top 3 bot names** (for bot alerts)
- **7‑day history table** – daily human traffic, bot traffic, and 404 errors, plus averages

**Here’s a real example of a traffic drop alert:**

> **Traffic — August 2, 2026**
>
> **Traffic dropped 41%**
>
> Your human traffic decreased sharply compared to recent activity.
>
> *What this means*
>
> A decrease in human traffic means fewer real visitors reached your site compared to your normal activity. This does not always indicate a problem and can happen after website changes, availability issues, visibility changes, broken links, or changes in visitor behavior.
>
> *Why this alert?*
>
> The decrease exceeded your configured threshold of 30% by a significant margin, indicating an unusual deviation from your recent traffic pattern.
>
> Today: 445 · 7-day average: 754 · Change: -41%
>
> *Pattern*
>
> This alert has appeared for the second consecutive day.
>
> *Related activity*
>
> Detected together with: 404 Errors, Bot Activity.
>
> *What you should check next*
>
> - Verify that your most important pages are available and responding correctly.
> - Review major recent changes, migrations, deployments, or settings updates that may have affected visitor access.
> - Check that your site is loading normally and important pages are accessible.
> - Review recent content updates, deleted posts, or changes to important pages.

## Why Proactive Site Advisor?

**Alerts you can actually act on**
You don't just get a number. You get a human-readable summary that tells you what changed, what it means, and exactly what to check next.

404 alerts include the top 3 broken URLs with fix suggestions. Bot alerts list the top 3 crawlers by name with visit counts and context.

This is just the beginning. The free plugin will keep improving with new read‑only integrations and detection refinements, while more advanced anomaly types — like slow page detection and server error surge detection — are planned for the Pro version.

**Built for privacy and performance**
The plugin never phones home. All data is collected, summarized, and stored inside your own database. We use lightweight tables that hold only the last 7 days of aggregated metrics. No personal visitor data is ever saved. No cookies. No front‑end scripts. GDPR‑friendly by design.

**Zero‑configuration monitoring**
Install, activate, done. The plugin starts collecting data from day one and begins anomaly detection after a 7-day baseline period. No API keys, no tracking codes, no setup wizard. Optional settings are available under **Site Advisor → Settings** if you want to customize alert thresholds or email notifications.

## Key Features

- **Bot anomaly detection** – Detects unusual crawler activity with top bot names and auto-corrected traffic counts.
- **Bot-aware traffic correction** – Detects bot traffic and separates it from human visits, so your metrics stay accurate and anomaly detection remains reliable.
- **Human traffic monitoring** – Drops or spikes compared to the previous 7‑day average
- **404 error surge alerts** – Top 3 broken URLs with hit counts and fix suggestions
- **Actionable recommendations** – Every alert includes a "What you should check next" list
- **Daily WP‑Cron scans** – Automatic checks after each full day
- **100% local processing** – No external APIs, zero data leaves your server
- **Atomic database operations** – Reliable metric collection without race conditions
- **Accurate bot detection** – 1,500+ built-in bot signatures, refined with each plugin release
- **Future‑ready** – Free roadmap adds read‑only analytics and security integrations; advanced anomaly types like slow pages and server errors planned for Pro.

## Privacy & Performance by Design

- **Truly self‑hosted** – All statistics stay in your WordPress database. We never see your data.
- **No personal data** – Only daily summaries. No IP addresses, no cookies.
- **Ultra‑light footprint** – Lightweight background processing, zero front-end impact.
- **GDPR/CCPA friendly** – No cookies, no cross‑site tracking, no consent banner needed for monitoring.

The plugin does **not** fix anything automatically — it only alerts and recommends so you stay in full control.

## Who is Proactive Site Advisor for?

- **Site owners who hate surprises** – Know the moment traffic dips or errors rise, before clients notice.
- **Agencies managing multiple sites** – Proactive alerts let you fix issues before reports go out.
- **SEO and content teams** – Catch 404s and broken links instantly, protecting your rankings.
- **Privacy‑conscious WordPress users** – Get site insights without giving data to third parties.

## Requirements

- WordPress 6.1 or higher
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB equivalent

## Installation

1. Upload `proactive-site-advisor` to `/wp-content/plugins/`
2. Activate through **Plugins → Installed Plugins**
3. Visit the **Site Advisor** menu in your admin sidebar

That's it. No required configuration, no API connections to set up. Proactive Site Advisor starts collecting data immediately. Optional settings are available under **Site Advisor → Settings** if you want to customize thresholds or email alerts.

## Frequently Asked Questions

### Will the plugin fix problems automatically?
No. It's an advisor, not an autopilot. It tells you what's wrong and what to check, so you stay in full control.

### Where do the traffic and 404 data come from?
The plugin hooks into WordPress to log page views and 404 errors. Data is aggregated daily and old raw logs are deleted – only the summarized metrics stay.

### When does the plugin scan?
At the end of each day, via WordPress Cron. You don't need to click anything.

### How does it know something is wrong?
It compares yesterday's numbers to the average of the previous 7 days. A significant deviation triggers an alert. During the first 7 days after activation, the plugin only builds the baseline and does not generate alerts. Anomaly alerts can appear from day 8 onward.

### Why don't I see alerts immediately after activation?
The plugin needs 7 full days of data to understand your site's normal traffic, 404, and bot patterns. After that baseline is built, it starts comparing daily values and generating alerts only when thresholds are exceeded.

### What exactly does a 404 alert show?
The three most-hit broken URLs from that day, with the number of hits and a plain‑English suggestion (e.g., "Set up a redirect from /old-page to /new-page").

### What does a bot alert show?
The top three bot names (like Googlebot, AhrefsBot) with visit counts and context on whether their activity is unusual.

### Will you add more alert types and integrations?
Absolutely. Upcoming free releases will add read‑only integrations with popular analytics plugins such as WP Statistics, Burst Statistics, MonsterInsights, and Site Kit by Google. Later free security signal integrations are planned for Wordfence and Solid Security. Advanced anomaly types like slow page detection and server error surge detection are planned for the Pro version.

### Is it free?
The core plugin is and will remain free, licensed under GPL-2.0-or-later. A Pro version with advanced features is planned for the future, which will help support ongoing development.

### Does it affect site speed?
No. The plugin has zero front‑end footprint. All processing happens in the background after page load, using efficient database queries. Your visitors won't notice it.