<?php

namespace ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals;

use ProactiveSiteAdvisor\Services\Frontend\Traffic\Contracts\BotSignalInterface;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\DataLoader;
use ProactiveSiteAdvisor\Services\Frontend\Traffic\Helpers\HeaderReader;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class ScannerPatternSignal
 *
 * @package ProactiveSiteAdvisor\Services\Frontend\Traffic\Signals
 * @version 1.0.0
 */
class ScannerPatternSignal implements BotSignalInterface
{
    /**
     * @var array|null
     */
    private static ?array $patterns = null;

    /**
     * {@inheritDoc}
     */
    public function isBot(): bool
    {
        if (!is_404() || is_user_logged_in()) {
            return false;
        }

        $uri = HeaderReader::getRequestUri();

        foreach ($this->getPatterns() as $pattern) {
            if ($pattern !== '' && @preg_match('#' . $pattern . '#i', $uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns scanner patterns.
     *
     * @return array
     */
    private function getPatterns(): array
    {
        if (self::$patterns !== null) {
            return self::$patterns;
        }

        $patterns = DataLoader::loadScanner404Patterns();

        /**
         * Filter scanner 404 patterns.
         *
         * @param string[] $patterns
         */
        $patterns = apply_filters('proactive_site_advisor_scanner_404_patterns', $patterns);

        if (!is_array($patterns)) {
            $patterns = [];
        }

        self::$patterns = array_values(array_filter(
            array_map('trim', $patterns),
            static fn(string $pattern): bool => $pattern !== ''
        ));

        return self::$patterns;
    }
}