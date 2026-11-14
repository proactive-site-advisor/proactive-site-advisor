<?php

namespace ProactiveSiteAdvisor\Tests\Unit\Cache;

use PHPUnit\Framework\TestCase;
use ProactiveSiteAdvisor\Cache\CacheKeys;

/**
 * Unit tests for the CacheKeys class.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since   1.2.8
 */
class CacheKeysTest extends TestCase
{
    /** Test admin notices cache key. */
    public function testAdminNoticesKey(): void
    {
        $this->assertSame('admin_notices', CacheKeys::adminNotices());
    }

    /** Test admin flash notices cache key. */
    public function testAdminFlashNoticesKey(): void
    {
        $this->assertSame('admin_flash_notices', CacheKeys::adminFlashNotices());
    }

    /** Test daily cron lock cache key. */
    public function testDailyLockKey(): void
    {
        $this->assertSame('daily_lock', CacheKeys::dailyLock());
    }

    /** Test cache key for tracking User-Agents per IP. */
    public function testIpUserAgentsKey(): void
    {
        $this->assertSame(
            'ip_ua_test_hash',
            CacheKeys::ipUserAgents('test_hash')
        );
    }

    /** Test behavioral history cache key. */
    public function testBehavioralHistoryKey(): void
    {
        $fingerprint = 'test_fingerprint';

        $this->assertSame(
            'behavioral_' . md5($fingerprint),
            CacheKeys::behavioralHistory($fingerprint)
        );
    }

    /** Test navigation behavior cache key. */
    public function testNavigationBehaviorKey(): void
    {
        $this->assertSame(
            'nav_beh_test_fingerprint',
            CacheKeys::navigationBehavior('test_fingerprint')
        );
    }
}