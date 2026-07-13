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
 * Executes the daily statistics routine.
 *
 * @package ProactiveSiteAdvisor\Services\Insights\Crons
 * @since   1.0.0
 */
class DailyStatsFlusher extends AbstractSingleton
{
    /** Execute the daily routine. */
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