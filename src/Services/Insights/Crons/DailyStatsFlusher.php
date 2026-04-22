<?php

namespace ProactiveSiteAdvisor\Services\Insights\Crons;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;
use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Config\PluginMeta;
use ProactiveSiteAdvisor\Models\DailyStats;
use ProactiveSiteAdvisor\Services\Insights\DailyInsightsHandler;
use ProactiveSiteAdvisor\Utils\DateTimeUtils;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DailyStatsFlusher
 *
 * Executes the daily statistics flush routine.
 *
 * @package ProactiveSiteAdvisor\Services\Cron
 * @version 1.0.0
 */
class DailyStatsFlusher extends AbstractSingleton
{
    /**
     * Executes the daily flush routine.
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
            $yesterdayKey = $yesterday->format('Ymd');

            DailyStats::ensureDayExists($yesterdayYmd);

            $pageviews = (int)$cache->get(CacheKeys::pageviewsForDate($yesterdayKey), 0);
            $errors404 = (int)$cache->get(CacheKeys::notFoundTotalForDate($yesterdayKey), 0);
            $topMapRaw = $cache->get(CacheKeys::notFoundMapForDate($yesterdayKey));

            $topJson = $this->buildTop404Json($topMapRaw);

            DailyStats::updateDay($yesterdayYmd, $pageviews, $errors404, $topJson);

            $dailyInsightsHandler = new DailyInsightsHandler();
            $dailyInsightsHandler->handle($yesterdayYmd);

            $cache->delete(CacheKeys::pageviewsForDate($yesterdayKey));
            $cache->delete(CacheKeys::notFoundTotalForDate($yesterdayKey));
            $cache->delete(CacheKeys::notFoundMapForDate($yesterdayKey));

            OptionUtils::setMeta(
                PluginMeta::LAST_DAILY_RUN,
                DateTimeUtils::timestamp(),
                false
            );

        } finally {
            $cache->delete($lockKey);
        }
    }

    /**
     * Normalizes raw 404 path map data into a compact JSON format.
     *
     * @param $topMapRaw
     *
     * @return string|null
     */
    private function buildTop404Json($topMapRaw): ?string
    {
        if (!is_string($topMapRaw) || $topMapRaw === '') {
            return null;
        }

        $map = json_decode($topMapRaw, true);
        if (!is_array($map) || empty($map)) {
            return null;
        }

        $clean = [];

        foreach ($map as $path => $count) {

            $path = is_string($path) ? sanitize_text_field($path) : '';

            if ($path === '') {
                continue;
            }

            $clean[$path] = max(1, (int)$count);
        }

        if (!$clean) {
            return null;
        }

        arsort($clean);

        $top3 = array_slice($clean, 0, 3, true);

        $list = [];

        foreach ($top3 as $path => $count) {
            $list[] = [$path, $count];
        }

        return wp_json_encode($list);
    }
}
