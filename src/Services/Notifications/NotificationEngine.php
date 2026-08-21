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

        $filteredAlerts = $this->filterAlerts($alerts, $settings);

        if (empty($filteredAlerts)) {
            return;
        }

        $channels = NotificationChannelConfig::getChannels();

        foreach ($channels as $channelClass) {
            /** @var NotificationChannelInterface $channel */
            $channel = new $channelClass();
            if ($channel->isEnabled($settings)) {
                $channel->send($filteredAlerts, $date, $settings);
            }
        }
    }

    /** Gets notification settings from the database. */
    private function getSettings(): array
    {
        return OptionUtils::getSection(PluginSettings::SECTION_NOTIFICATIONS);
    }

    /** Filters alerts based on user settings. */
    private function filterAlerts(array $alerts, array $settings): array
    {

        return array_filter($alerts, static function ($alert) use ($settings) {
            $typeToSettingKey = [
                'traffic_drop'  => PluginSettings::DIGEST_INCLUDE_TRAFFIC,
                'traffic_spike' => PluginSettings::DIGEST_INCLUDE_TRAFFIC,
                '404_spike'     => PluginSettings::DIGEST_INCLUDE_404,
                'bot_spike'     => PluginSettings::DIGEST_INCLUDE_BOT,
                'bot_drop'      => PluginSettings::DIGEST_INCLUDE_BOT,
            ];
            $type             = $alert['type'];

            if (isset($typeToSettingKey[$type])) {
                return !empty($settings[$typeToSettingKey[$type]]);
            }

            return true;
        });
    }
}