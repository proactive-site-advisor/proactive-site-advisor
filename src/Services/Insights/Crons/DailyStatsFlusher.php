<?php

namespace ProactiveSiteAdvisor\Services\Insights\Crons;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;
use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Config\PluginMeta;
use ProactiveSiteAdvisor\Services\Insights\DailyInsightsHandler;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DailyStatsFlusher
 *
 * Executes the daily statistics routine.
 * Since metrics are now written directly to the database in real-time,
 * this class only triggers insights generation and records the last run.
 *
 * @package ProactiveSiteAdvisor\Services\Cron
 * @version 1.0.0
 */
class DailyStatsFlusher extends AbstractSingleton
{
    /**
     * Execute the daily routine.
     *
     * @return void
     */
    public function run(): void
    {
        $cache = CacheManager::instance();

        $lockKey = CacheKeys::dailyLock();
        if ($cache->get($lockKey)) {
            return;
        }

        $cache->set($lockKey, 1, MINUTE_IN_SECONDS * 5);

        try {
            $now = DateTimeUtils::current();

            $yesterday    = $now->modify('-1 day');
            $yesterdayYmd = $yesterday->format('Y-m-d');

            $dailyInsightsHandler = new DailyInsightsHandler();
            $dailyInsightsHandler->handle($yesterdayYmd);

            OptionUtils::setMeta(
                PluginMeta::LAST_DAILY_RUN,
                DateTimeUtils::timestamp(),
                false
            );
        } finally {
            $cache->delete($lockKey);
        }
    }
}