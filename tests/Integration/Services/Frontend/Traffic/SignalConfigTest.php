<?php

namespace ProactiveSiteAdvisor\Tests\Integration\Services\Frontend\Traffic;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Config\SignalConfig;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\ScoreSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals\BotAgentSignal;
use WP_UnitTestCase;

/**
 * Integration tests for the SignalConfig class.
 *
 * @package ProactiveSiteAdvisor\Tests\Integration\Services\Frontend\Traffic
 * @since   1.2.8
 */
class SignalConfigTest extends WP_UnitTestCase
{
    /** Test that all configured bot signals implement the bot signal interface. */
    public function testBotSignalsImplementBotSignalInterface(): void
    {
        foreach (SignalConfig::getBotSignals() as $class) {
            $this->assertTrue(
                in_array(BotSignalInterface::class, class_implements($class), true),
                sprintf('%s must implement %s', $class, BotSignalInterface::class)
            );
        }
    }

    /** Test that all configured score signals implement the score signal interface. */
    public function testScoreSignalsImplementScoreSignalInterface(): void
    {
        foreach (SignalConfig::getScoreSignals() as $class) {
            $this->assertTrue(
                in_array(ScoreSignalInterface::class, class_implements($class), true),
                sprintf('%s must implement %s', $class, ScoreSignalInterface::class)
            );
        }
    }

    /** Test that the bot signals filter can modify the configured signal list. */
    public function testBotSignalsFilterCanModifyList(): void
    {
        $filter = static function (): array {
            return [BotAgentSignal::class];
        };

        add_filter('proactive_site_advisor_bot_signals', $filter);

        try {
            $this->assertSame([BotAgentSignal::class], SignalConfig::getBotSignals());
        } finally {
            remove_filter('proactive_site_advisor_bot_signals', $filter);
        }
    }

    /** Test that the score signals filter can modify the configured signal list. */
    public function testScoreSignalsFilterCanModifyList(): void
    {
        $filter = static function (): array {
            return [BotAgentSignal::class];
        };

        add_filter('proactive_site_advisor_score_signals', $filter);

        try {
            $this->assertSame([BotAgentSignal::class], SignalConfig::getScoreSignals());
        } finally {
            remove_filter('proactive_site_advisor_score_signals', $filter);
        }
    }
}
