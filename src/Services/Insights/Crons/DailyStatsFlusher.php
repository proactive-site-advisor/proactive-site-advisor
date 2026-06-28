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
     * Execute the daily flush routine.
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

            $humanStats = $this->collectHumanStats($yesterdayKey);
            $botStats   = $this->collectBotStats($yesterdayKey);

            DailyStats::updateDay(
                $yesterdayYmd,
                $humanStats['pageviews'],
                $humanStats['errors404'],
                $humanStats['top404Json'],
                $botStats['pageviews'],
                $botStats['topBotsJson']
            );

            $dailyInsightsHandler = new DailyInsightsHandler();
            $dailyInsightsHandler->handle($yesterdayYmd);

            $this->clearDailyCache($yesterdayKey);

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
     * Collect human traffic statistics for the given date key.
     *
     * @param string $dateKey
     * @return array
     */
    private function collectHumanStats(string $dateKey): array
    {
        $cache = CacheManager::instance();

        $pageviews = (int)$cache->get(CacheKeys::pageviewsForDate($dateKey), 0);
        $errors404 = (int)$cache->get(CacheKeys::notFoundTotalForDate($dateKey), 0);
        $topMapRaw = $cache->get(CacheKeys::notFoundMapForDate($dateKey));

        return [
            'pageviews'  => $pageviews,
            'errors404'  => $errors404,
            'top404Json' => $this->buildTopEntriesJson($topMapRaw),
        ];
    }

    /**
     * Collect bot traffic statistics for the given date key.
     *
     * @param string $dateKey
     * @return array
     */
    private function collectBotStats(string $dateKey): array
    {
        $cache = CacheManager::instance();

        $botPageviews = (int)$cache->get(CacheKeys::pageviewsBotForDate($dateKey), 0);
        $botMapRaw    = $cache->get(CacheKeys::botNameCountsForDate($dateKey));

        return [
            'pageviews'   => $botPageviews,
            'topBotsJson' => $this->buildTopEntriesJson($botMapRaw),
        ];
    }

    /**
     * Remove all daily cache keys for the given date.
     *
     * @param string $dateKey
     * @return void
     */
    private function clearDailyCache(string $dateKey): void
    {
        $cache = CacheManager::instance();

        $cache->delete(CacheKeys::pageviewsForDate($dateKey));
        $cache->delete(CacheKeys::notFoundTotalForDate($dateKey));
        $cache->delete(CacheKeys::notFoundMapForDate($dateKey));

        $cache->delete(CacheKeys::pageviewsBotForDate($dateKey));
        $cache->delete(CacheKeys::botNameCountsForDate($dateKey));
    }

    /**
     * Normalise a raw key‑count map (stored as JSON) into a compact
     * JSON array containing the top 3 entries.
     *
     * @param mixed $raw
     * @return string|null
     */
    private function buildTopEntriesJson($raw): ?string
    {
        if (!is_string($raw) || $raw === '') {
            return null;
        }

        $map = json_decode($raw, true);
        if (!is_array($map) || empty($map)) {
            return null;
        }

        $clean = [];
        foreach ($map as $key => $count) {
            $key = is_string($key) ? sanitize_text_field($key) : '';
            if ($key === '') {
                continue;
            }
            $clean[$key] = max(1, (int)$count);
        }

        if (!$clean) {
            return null;
        }

        arsort($clean);
        $top3 = array_slice($clean, 0, 3, true);

        $list = [];
        foreach ($top3 as $key => $count) {
            $list[] = [$key, $count];
        }

        return wp_json_encode($list);
    }
}