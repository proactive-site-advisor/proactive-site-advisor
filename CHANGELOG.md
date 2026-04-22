# Changelog

## 1.0.0 – 2026-02-07

### Initial Release

- Added top-level **“Proactive Site Advisor”** admin menu for centralized access.  
- Added core scanning engine responsible for evaluating key site environment metrics.  
- Added initial set of environment and configuration checks, including:  
  - WordPress version and general system information  
  - Basic PHP configuration values and common misconfigurations  
  - File permission accessibility checks for essential directories  
  - Detection of missing or inconsistent configuration constants  
- Added admin alerts and notice system that displays proactive recommendations directly in the dashboard.  
- Added structured notification severity levels for clearer prioritization of issues.  
- Added lightweight, optimized check routines designed to run efficiently on all hosting environments.  
- Added automated execution of daily scans using WP‑Cron for consistent monitoring.  
- Added foundational architecture allowing future expansion of additional check types and modules.  
- Improved consistency and reliability of checks in cases where environment data is incomplete or unavailable.  