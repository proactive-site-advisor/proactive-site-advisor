<?php

namespace ProactiveSiteAdvisor\Services\Notifications;

use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\DataProviders\AlertsDataProvider;
use ProactiveSiteAdvisor\Services\Notifications\Config\NotificationChannelConfig;
use ProactiveSiteAdvisor\Services\Notifications\Contracts\NotificationChannelInterface;
use ProactiveSiteAdvisor\Utils\OptionUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dispatches notifications to active channels.
 *
 * @package ProactiveSiteAdvisor\Services\Notifications
 * @since   1.0.0
 */
class NotificationEngine
{
    /** Alerts data provider instance. */
    private AlertsDataProvider $alertsDataProvider;

    /** Constructor. */
    public function __construct()
    {
        $this->alertsDataProvider = new AlertsDataProvider();
    }

    /** Sends the daily digest for the given date. */
    public function send(string $date): void
    {
        $settings = $this->getSettings();

        if (empty($settings[PluginSettings::ENABLE_DAILY_DIGEST])) {
            return;
        }

        $alerts = $this->alertsDataProvider->getAlertsByDate($date);

        if (empty($alerts)) {
            return;
        }

        $channels = NotificationChannelConfig::getChannels();

        foreach ($channels as $channelClass) {
            /** @var NotificationChannelInterface $channel */
            $channel = new $channelClass();
            if ($channel->isEnabled($settings)) {
                $channel->send($alerts, $date, $settings);
            }
        }
    }

    /** Gets notification settings from the database. */
    private function getSettings(): array
    {
        return OptionUtils::getSection(PluginSettings::SECTION_NOTIFICATIONS);
    }
}