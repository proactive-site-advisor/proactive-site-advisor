<?php

namespace ProactiveSiteAdvisor\Services\Notifications\Channels;

use ProactiveSiteAdvisor\Builders\AlertBuilder;
use ProactiveSiteAdvisor\Config\PluginSettings;
use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\DataProviders\AlertsDataProvider;
use ProactiveSiteAdvisor\Services\Notifications\Contracts\NotificationChannelInterface;
use ProactiveSiteAdvisor\Utils\DisplayUtils;
use ProactiveSiteAdvisor\Utils\MenuUtils;
use ProactiveSiteAdvisor\Utils\TemplateUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email notification channel.
 *
 * @package ProactiveSiteAdvisor\Services\Notifications\Channels
 * @since   1.0.0
 */
class EmailChannel implements NotificationChannelInterface
{
    /** Alert builder instance. */
    private AlertBuilder $alertBuilder;

    /** Alerts data provider instance. */
    private AlertsDataProvider $alertsDataProvider;

    /** Constructor. */
    public function __construct()
    {
        $this->alertBuilder       = new AlertBuilder();
        $this->alertsDataProvider = new AlertsDataProvider();
    }

    /** {@inheritDoc} */
    public function isEnabled(array $settings): bool
    {
        return !empty($settings[PluginSettings::DIGEST_RECIPIENT_EMAIL]);
    }

    /** {@inheritDoc} */
    public function send(array $alerts, string $date, array $settings): void
    {
        $recipient = $settings[PluginSettings::DIGEST_RECIPIENT_EMAIL];

        if (empty($recipient) || empty($alerts)) {
            return;
        }

        $subject = $this->buildSubject($date, count($alerts));
        $body    = $this->buildBody($alerts, $date);

        wp_mail($recipient, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);
    }

    /** Builds the email subject. */
    private function buildSubject(string $date, int $count): string
    {
        return sprintf(
        /* translators: 1: Number of alerts, 2: Date of the digest */
            __('Daily Digest: %1$d alerts on %2$s', 'proactive-site-advisor'),
            $count,
            $date
        );
    }

    /** Builds the email body using template. */
    private function buildBody(array $alerts, string $date): string
    {
        $preparedAlerts  = [];
        $repetitionData  = [];
        $concurrencyData = [];

        foreach ($alerts as $alert) {
            $id        = $alert['id'];
            $type      = $alert['type'];
            $alertDate = $alert['alert_date'];

            $repetitionData[$id]  = $this->alertsDataProvider->getRepetitionCount($type, $alertDate);
            $concurrencyData[$id] = $this->alertsDataProvider->getConcurrentTypes($alertDate, $type);
        }

        foreach ($alerts as $alert) {
            $alertData = $this->alertBuilder->build(
                $alert,
                $repetitionData[$alert['id']],
                $concurrencyData[$alert['id']]
            );

            $alertData['icon_url'] = $this->getAlertIconUrl($alertData['type'], $alertData['severity']);

            $preparedAlerts[] = $alertData;
        }

        $isRtl   = is_rtl();
        $homeUrl = home_url();

        return TemplateUtils::renderTemplate(
            'notifications/email/daily-digest',
            [
                'date'         => $date,
                'siteUrl'      => $homeUrl,
                'siteHost'     => DisplayUtils::siteHost($homeUrl),
                'dashboardUrl' => MenuUtils::getUrl(PrefixConfig::SLUG),
                'settingsUrl'  => MenuUtils::getUrl('settings#notifications'),
                'alerts'       => $preparedAlerts,
                'totalAlerts'  => count($preparedAlerts),
                'rtl'          => $isRtl ? 'rtl' : 'ltr',
                'locale'       => get_locale(),
                'brandIcon'    => $this->getBrandIconUrl($isRtl),
            ]
        );
    }

    /** Get alert icon image URL based on alert type, severity and theme. */
    private function getAlertIconUrl(string $type, string $severity): string
    {
        $map = [
            'traffic_drop'  => 'traffic-drop',
            'traffic_spike' => 'traffic-spike',
            '404_spike'     => '404-spike',
            'bot_spike'     => 'bot-spike',
            'bot_drop'      => 'bot-drop',
        ];

        $filename = $map[$type];

        return PROACTIVE_SITE_ADVISOR_ASSETS . 'img/email-icons/' . 'light' . '/' . $severity . '/' . $filename . '.png';
    }

    /** Gets the brand icon URL based on the current direction. */
    private function getBrandIconUrl(bool $isRtl): string
    {
        $filename = $isRtl ? 'brand-icon-rtl.png' : 'brand-icon.png';
        return PROACTIVE_SITE_ADVISOR_ASSETS . 'img/email-icons/' . $filename;
    }
}