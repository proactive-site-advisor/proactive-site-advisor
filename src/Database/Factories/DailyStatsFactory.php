<?php

namespace ProactiveSiteAdvisor\Database\Factories;

use ProactiveSiteAdvisor\Abstracts\AbstractFactory;
use ProactiveSiteAdvisor\Models\DailyStats;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class DailyStatsFactory
 *
 * Factory for creating DailyStats records with fake data.
 *
 * @package ProactiveSiteAdvisor\Database\Factories
 * @version 1.0.4
 */
class DailyStatsFactory extends AbstractFactory
{
    /**
     * Model class.
     *
     * @var string
     */
    protected string $model = DailyStats::class;

    /**
     * Common bot names for realistic top bots data.
     *
     * @var array
     */
    private array $commonBotNames = [
        'Googlebot',
        'Bingbot',
        'AhrefsBot',
        'SemrushBot',
        'DuckDuckBot',
        'YandexBot',
        'FacebookBot',
        'Twitterbot',
        'Applebot',
        'PetalBot',
        'Slackbot',
        'TelegramBot',
    ];

    /**
     * Common 404 paths for realistic data.
     *
     * @var array
     */
    private array $common404Paths = [
        '/wp-login.php',
        '/wp-admin/',
        '/xmlrpc.php',
        '/.env',
        '/wp-content/uploads/2024/missing-image.jpg',
        '/old-page-slug/',
        '/products/discontinued-item/',
        '/blog/deleted-post/',
        '/api/v1/deprecated/',
        '/favicon.ico',
        '/.git/config',
        '/wp-config.php.bak',
        '/backup.sql',
        '/admin.php',
        '/login',
    ];

    /**
     * Define default attributes.
     *
     * @return array
     */
    protected function definition(): array
    {
        return [
            'stats_date'    => current_time('Y-m-d'),
            'pageviews'     => $this->randomInt(800, 1500),
            'errors_404'    => $this->randomInt(5, 30),
            'top_404_json'  => null,
            'bot_pageviews' => 0,
            'top_bots_json' => null,
        ];
    }

    /**
     * Create stats for a specific date with pattern-aware data.
     *
     * @param string $date
     * @param int $dayIndex
     * @return DailyStats|null
     */
    public function forDate(string $date, int $dayIndex = 0): ?DailyStats
    {
        if ($this->pattern === 'alerts') {
            $attributes = $this->alertsPattern($date, $dayIndex);
        } else {
            $attributes = $this->realisticPattern($date);
        }

        DailyStats::deleteByDate($date);

        $result = $this->create($attributes);

        return $result instanceof DailyStats ? $result : null;
    }

    /**
     * Create realistic pattern data.
     *
     * Generates natural variance: 800-1500 pageviews, 5-30 404s.
     * Weekend traffic is reduced by 20-40%.
     *
     * @param string $date
     * @return array
     */
    public function realisticPattern(string $date): array
    {
        $basePageviews = $this->randomInt(800, 1500);
        $pageviews     = $this->applyDayOfWeekVariance($date, $basePageviews);

        $errors404  = $this->randomInt(5, 30);
        $top404Json = $this->generateTop404Json($errors404);

        $botPageviews = $this->randomInt(
            (int)($pageviews * 0.2),
            (int)($pageviews * 0.5)
        );
        $topBotsJson  = $this->generateTopBotsJson($botPageviews);

        return [
            'stats_date'    => $date,
            'pageviews'     => $pageviews,
            'errors_404'    => $errors404,
            'top_404_json'  => $top404Json,
            'bot_pageviews' => $botPageviews,
            'top_bots_json' => $topBotsJson,
        ];
    }

    /**
     * Create alerts pattern data.
     *
     * @param string $date
     * @param int $dayIndex
     * @return array
     */
    public function alertsPattern(string $date, int $dayIndex): array
    {
        $baselinePageviews = $this->randomInt(1000, 1200);
        $baseline404       = $this->randomInt(10, 15);
        $baselineBot       = $this->randomInt(300, 600);

        $pageviews    = $baselinePageviews;
        $errors404    = $baseline404;
        $botPageviews = $baselineBot;

        if ($dayIndex === 10) {
            $pageviews = $this->randomInt(300, 500);
        }

        if ($dayIndex === 20) {
            $pageviews = $this->randomInt(2000, 2500);
        }

        if ($dayIndex === 25) {
            $errors404 = $this->randomInt(50, 80);
        }

        if ($dayIndex === 15) {
            $botPageviews = $this->randomInt(2500, 3500);
        }
        if ($dayIndex === 30) {
            $botPageviews = $this->randomInt(5, 15);
        }

        $top404Json  = $this->generateTop404Json($errors404);
        $topBotsJson = $this->generateTopBotsJson($botPageviews);

        return [
            'stats_date'    => $date,
            'pageviews'     => $pageviews,
            'errors_404'    => $errors404,
            'top_404_json'  => $top404Json,
            'bot_pageviews' => $botPageviews,
            'top_bots_json' => $topBotsJson,
        ];
    }

    /**
     * Generate top bots JSON data.
     *
     * @param int $botPageviews
     * @return string|null
     */
    private function generateTopBotsJson(int $botPageviews): ?string
    {
        if ($botPageviews < 3) {
            return null;
        }

        $nameCount = $this->randomInt(1, 3);
        $names     = $this->randomElements($this->commonBotNames, $nameCount);

        $remaining = $botPageviews;
        $topBots   = [];

        foreach ($names as $index => $name) {
            if ($index === count($names) - 1) {
                $count = $remaining;
            } else {
                $count     = $this->randomInt(1, max(1, (int)($remaining * 0.6)));
                $remaining -= $count;
            }

            if ($count > 0) {
                $topBots[] = [$name, $count];
            }
        }

        usort($topBots, static function ($a, $b) {
            return $b[1] - $a[1];
        });

        return wp_json_encode(array_slice($topBots, 0, 3));
    }

    /**
     * Generate top 404 JSON data.
     *
     * @param int $errorCount
     * @return string|null
     */
    private function generateTop404Json(int $errorCount): ?string
    {
        if ($errorCount < 3) {
            return null;
        }

        $pathCount = $this->randomInt(1, 3);
        $paths     = $this->randomElements($this->common404Paths, $pathCount);

        $remaining = $errorCount;
        $top404    = [];

        foreach ($paths as $index => $path) {
            if ($index === count($paths) - 1) {
                $count = $remaining;
            } else {
                $count     = $this->randomInt(1, (int)($remaining * 0.6));
                $remaining -= $count;
            }

            if ($count > 0) {
                $top404[] = [$path, $count];
            }
        }

        usort($top404, static function ($a, $b) {
            return $b[1] - $a[1];
        });

        return wp_json_encode(array_slice($top404, 0, 3));
    }

    /**
     * Apply day-of-week variance to pageviews.
     *
     * Weekends typically have less traffic.
     *
     * @param string $date
     * @param int $basePageviews
     * @return int
     */
    private function applyDayOfWeekVariance(string $date, int $basePageviews): int
    {
        if ($this->isWeekend($date)) {
            $reduction = $this->randomFloat(0.20, 0.40);

            return (int)($basePageviews * (1 - $reduction));
        }

        return $basePageviews;
    }
}
