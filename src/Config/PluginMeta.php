<?php

namespace ProactiveSiteAdvisor\Config;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin metadata keys.
 *
 * @package ProactiveSiteAdvisor\Config
 * @version 1.0.0
 */
class PluginMeta
{
    /**
     * Current plugin version option key.
     */
    public const VERSION = 'version';

    /**
     * Database schema version option key.
     */
    public const DB_VERSION = 'db_version';

    /**
     * Last daily cron execution timestamp key.
     */
    public const LAST_DAILY_RUN = 'last_daily_run';
}