<?php

namespace ProactiveSiteAdvisor\Tests\Integration\Services\Insights\Config;

use ProactiveSiteAdvisor\Config\PluginOptions;
use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Services\Insights\Config\MetricConfig;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use WP_UnitTestCase;

/**
 * Integration tests for the MetricConfig class.
 *
 * @package ProactiveSiteAdvisor\Tests
 * @since   1.2.8
 */
class MetricConfigTest extends WP_UnitTestCase
{
    /** Clean plugin options before each test. */
    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PluginOptions::OPTION_NAME);
    }

    /** Clean plugin options after each test. */
    protected function tearDown(): void
    {
        delete_option(PluginOptions::OPTION_NAME);

        parent::tearDown();
    }

    /** Test traffic metric defaults. */
    public function testTrafficConfigDefaults(): void
    {
        $config = MetricConfig::get(MetricConfig::METRIC_TRAFFIC);

        $this->assertSame('traffic', $config['metric']);
        $this->assertSame(MetricConfig::DIRECTION_TWO_SIDED, $config['direction']);
        $this->assertSame(0.01, $config['alpha']);
        $this->assertSame(0.02, $config['min_rel']);
        $this->assertTrue($config['auto_tune']);
        $this->assertSame(10, $config['min_abs_manual']);
        $this->assertSame('pageviews', $config['baseline_key']);
        $this->assertSame('todayPv', $config['today_key']);
    }

    /** Test 404 metric defaults. */
    public function testError404ConfigDefaults(): void
    {
        $config = MetricConfig::get(MetricConfig::METRIC_404);

        $this->assertSame(MetricConfig::DIRECTION_SPIKE, $config['direction']);
        $this->assertSame('errors_404', $config['baseline_key']);
        $this->assertSame('today404', $config['today_key']);
        $this->assertSame(3, $config['min_abs_manual']);
    }

    /** Test bot metric defaults. */
    public function testBotConfigDefaults(): void
    {
        $config = MetricConfig::get(MetricConfig::METRIC_BOT);

        $this->assertSame(MetricConfig::DIRECTION_TWO_SIDED, $config['direction']);
        $this->assertSame('bot_pageviews', $config['baseline_key']);
        $this->assertSame('todayBotPv', $config['today_key']);
        $this->assertSame(10, $config['min_abs_manual']);
    }

    /** Test high sensitivity maps to stricter alpha and min_rel. */
    public function testHighSensitivityMapping(): void
    {
        OptionUtils::setOption(OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::SENSITIVITY_LEVEL), 'high');

        $config = MetricConfig::get(MetricConfig::METRIC_TRAFFIC);

        $this->assertSame(0.05, $config['alpha']);
        $this->assertSame(0.01, $config['min_rel']);
    }

    /** Test low sensitivity mapping. */
    public function testLowSensitivityMapping(): void
    {
        OptionUtils::setOption(OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::SENSITIVITY_LEVEL), 'low');

        $config = MetricConfig::get(MetricConfig::METRIC_TRAFFIC);

        $this->assertSame(0.001, $config['alpha']);
        $this->assertSame(0.05, $config['min_rel']);
    }

    /** Test invalid sensitivity level falls back to normal. */
    public function testInvalidSensitivityFallsBackToNormal(): void
    {
        OptionUtils::setOption(OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::SENSITIVITY_LEVEL), 'bogus');

        $config = MetricConfig::get(MetricConfig::METRIC_TRAFFIC);

        $this->assertSame(0.01, $config['alpha']);
        $this->assertSame(0.02, $config['min_rel']);
    }

    /** Test auto_tune setting is respected. */
    public function testAutoTuneSetting(): void
    {
        OptionUtils::setOption(OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::SENSITIVITY_AUTO_TUNE), 0);

        $this->assertFalse(MetricConfig::get(MetricConfig::METRIC_TRAFFIC)['auto_tune']);
    }

    /** Test manual min_abs values come from settings. */
    public function testManualMinAbsFromSettings(): void
    {
        OptionUtils::setOption(OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::TRAFFIC_MIN_ABS), 25);
        OptionUtils::setOption(OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::ERROR_404_MIN_ABS), 7);
        OptionUtils::setOption(OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::BOT_MIN_ABS), 15);

        $this->assertSame(25, MetricConfig::get(MetricConfig::METRIC_TRAFFIC)['min_abs_manual']);
        $this->assertSame(7, MetricConfig::get(MetricConfig::METRIC_404)['min_abs_manual']);
        $this->assertSame(15, MetricConfig::get(MetricConfig::METRIC_BOT)['min_abs_manual']);
    }

    /** Test minRel helper reflects sensitivity level. */
    public function testMinRelHelper(): void
    {
        $this->assertSame(0.02, MetricConfig::minRel());

        OptionUtils::setOption(OptionUtils::makeKey(PluginSettings::SECTION_SENSITIVITY, PluginSettings::SENSITIVITY_LEVEL), 'high');

        $this->assertSame(0.01, MetricConfig::minRel());
    }

    /** Test baselineKey and todayKey helpers. */
    public function testKeyHelpers(): void
    {
        $this->assertSame('pageviews', MetricConfig::baselineKey(MetricConfig::METRIC_TRAFFIC));
        $this->assertSame('todayPv', MetricConfig::todayKey(MetricConfig::METRIC_TRAFFIC));
        $this->assertSame('errors_404', MetricConfig::baselineKey(MetricConfig::METRIC_404));
        $this->assertSame('today404', MetricConfig::todayKey(MetricConfig::METRIC_404));
    }
}