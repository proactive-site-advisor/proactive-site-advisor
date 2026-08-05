<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\PageviewSignal;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Records per-fingerprint navigation behavior for session-based bot detection.
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Recorders
 * @since   1.0.0
 */
class NavigationBehaviorRecorder
{
    /** Time-to-live for navigation state in seconds. */
    private const TTL = 300;

    /** Maximum number of events to retain per fingerprint. */
    private const MAX_EVENTS = 20;

    /** Records navigation behaviour for the current request. */
    public function record(): void
    {
        if (!PageviewSignal::shouldCollect()) {
            return;
        }

        $fingerprint = HeaderReader::getFingerprint();

        if ($fingerprint === '') {
            return;
        }

        $cache = CacheManager::instance();
        $key   = CacheKeys::navigationBehavior($fingerprint);
        $state = $cache->get($key);

        if (!is_array($state)) {
            $state = [
                'pages'        => 0,
                'unique_pages' => 0,
                'repeat_pages' => 0,
                'timestamps'   => [],
                'intervals'    => [],
                'paths'        => [],
                'empty_refers' => 0,
                'external_ref' => 0,
            ];
        } elseif (!isset($state['intervals']) || !is_array($state['intervals'])) {
            $state['intervals'] = [];
        }

        $now = time();

        if (!empty($state['timestamps'])) {
            $last     = end($state['timestamps']);
            $interval = $now - $last;

            $state['intervals'][] = $interval;

            if (count($state['intervals']) > self::MAX_EVENTS) {
                array_shift($state['intervals']);
            }
        }

        $state['timestamps'][] = $now;

        if (count($state['timestamps']) > self::MAX_EVENTS) {
            array_shift($state['timestamps']);
        }

        $state['pages']++;

        $path = $this->currentPath();

        if (in_array($path, $state['paths'], true)) {
            $state['repeat_pages']++;
        } else {
            $state['unique_pages']++;
            $state['paths'][] = $path;

            if (count($state['paths']) > self::MAX_EVENTS) {
                array_shift($state['paths']);
            }
        }

        $referer = HeaderReader::getReferer();

        if ($referer === '') {
            $state['empty_refers']++;
        } else {
            $state['external_ref']++;
        }

        $cache->set($key, $state, self::TTL);
    }

    /** Returns the current request path without query string. */
    private function currentPath(): string
    {
        return strtok(HeaderReader::getRequestUri(), '?');
    }
}