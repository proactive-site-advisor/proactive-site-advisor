<?php

namespace ProactiveSiteAdvisor\Admin\Notices;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Components\AjaxComponent;
use ProactiveSiteAdvisor\Config\UserOptions;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Utils\PluginUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class AdminNotices
 *
 * Centralized admin notice management with dismissibility support
 * and transient-based persistence.
 *
 * @package ProactiveSiteAdvisor\Admin
 * @version 1.0.0
 */
class AdminNotices
{
    /**
     * In-memory notice storage.
     *
     * @var Notice[]
     */
    private static array $notices = [];

    /**
     * Whether the class has been initialized.
     *
     * @var bool
     */
    private static bool $initialized = false;

    /**
     * Register hooks for admin notices.
     *
     * @return void
     */
    public static function register(): void
    {
        if (self::$initialized) {
            return;
        }

        add_action('admin_head', [self::class, 'sanitizeNotices'], 1);

        AjaxComponent::register('dismiss_notice', [self::class, 'handleDismiss'], false);

        self::loadFromTransient();

        // Save persistent notices at the end of request (safe, prevents early save bugs)
        add_action('shutdown', [self::class, 'saveToTransient'], 9999);

        self::$initialized = true;
    }

    /**
     * Add a notice.
     *
     * @param Notice $notice The notice to add.
     *
     * @return void
     */
    public static function add(Notice $notice): void
    {
        self::$notices[$notice->id] = $notice;
    }

    /**
     * Helper to create and add a notice.
     *
     * @param string $message
     * @param string $type
     * @param bool $dismissible
     *
     * @return Notice
     */
    private static function make(string $message, string $type, bool $dismissible): Notice
    {
        $notice = (new Notice($message, $type))
            ->setDismissible($dismissible);

        self::add($notice);

        return $notice;
    }

    /**
     * Add a generic notice.
     *
     * @param string $message
     * @param string $type
     * @param bool $dismissible
     *
     * @return Notice
     */
    public static function notice(string $message, string $type = Notice::TYPE_INFO, bool $dismissible = true): Notice
    {
        return self::make($message, $type, $dismissible);
    }

    /**
     * Add a success notice.
     *
     * @param string $message
     * @param bool $dismissible
     *
     * @return Notice
     */
    public static function success(string $message, bool $dismissible = true): Notice
    {
        return self::notice($message, Notice::TYPE_SUCCESS, $dismissible);
    }

    /**
     * Add an error notice.
     *
     * @param string $message
     * @param bool $dismissible
     *
     * @return Notice
     */
    public static function error(string $message, bool $dismissible = true): Notice
    {
        return self::notice($message, Notice::TYPE_ERROR, $dismissible);
    }

    /**
     * Add a warning notice.
     *
     * @param string $message
     * @param bool $dismissible
     *
     * @return Notice
     */
    public static function warning(string $message, bool $dismissible = true): Notice
    {
        return self::notice($message, Notice::TYPE_WARNING, $dismissible);
    }

    /**
     * Add an info notice.
     *
     * @param string $message
     * @param bool $dismissible
     *
     * @return Notice
     */
    public static function info(string $message, bool $dismissible = true): Notice
    {
        return self::notice($message, Notice::TYPE_INFO, $dismissible);
    }

    /**
     * Remove a notice by ID.
     *
     * @param string $id
     *
     * @return void
     */
    public static function remove(string $id): void
    {
        unset(self::$notices[$id]);
    }

    /**
     * Clear all notices.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$notices = [];
        CacheManager::instance()->delete(CacheKeys::adminNotices());
    }

    /**
     * Render all notices.
     *
     * @return void
     */
    public static function render(): void
    {
        foreach (self::$notices as $notice) {
            // render() returns already escaped HTML.
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $notice->render();
        }

        self::clearNonPersistent();
    }

    /**
     * Handle AJAX dismiss request.
     *
     * @return void
     */
    public static function handleDismiss(): void
    {
        // Safe: Only updates current user's data; nonce is verified and user capability is checked in AjaxComponent::register().
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $noticeId = isset($_POST['notice_id']) ? sanitize_text_field(wp_unslash($_POST['notice_id'])) : '';
        $notice   = self::$notices[$noticeId] ?? null;

        if (!$notice) {
            AjaxComponent::sendError(__('Invalid notice ID.', 'proactive-site-advisor'));
        }

        if (!$notice->persistent) {
            self::remove($noticeId);
            AjaxComponent::sendSuccess([], __('Notice dismissed.', 'proactive-site-advisor'));
        }

        self::markDismissed($noticeId);
        self::remove($noticeId);

        AjaxComponent::sendSuccess([], __('Notice dismissed.', 'proactive-site-advisor'));
    }

    /**
     * Check if a notice has been dismissed by the current user.
     *
     * @param string $id
     *
     * @return bool
     */
    public static function isDismissed(string $id): bool
    {
        $dismissed = OptionUtils::getUserOption(UserOptions::DISMISSED_NOTICES, []);

        return is_array($dismissed) && in_array($id, $dismissed, true);
    }

    /**
     * Mark a notice as dismissed for the current user.
     *
     * @param string $id
     *
     * @return void
     */
    public static function markDismissed(string $id): void
    {
        $dismissed = OptionUtils::getUserOption(UserOptions::DISMISSED_NOTICES, []);

        if (!is_array($dismissed)) {
            $dismissed = [];
        }

        if (!in_array($id, $dismissed, true)) {
            $dismissed[] = $id;
            OptionUtils::setUserOption(UserOptions::DISMISSED_NOTICES, $dismissed);
        }
    }

    /**
     * Reset dismissed notices for the current user.
     *
     * @return void
     */
    public static function resetDismissed(): void
    {
        OptionUtils::deleteUserOption(UserOptions::DISMISSED_NOTICES);
    }

    /**
     * Load notices from cache.
     *
     * @return void
     */
    private static function loadFromTransient(): void
    {
        $stored      = CacheManager::instance()->get(CacheKeys::adminNotices());
        $flashStored = CacheManager::instance()->get(CacheKeys::adminFlashNotices());

        if (is_array($stored)) {
            foreach ($stored as $data) {
                $notice                     = Notice::fromArray($data);
                self::$notices[$notice->id] = $notice;
            }
        }

        if (is_array($flashStored)) {
            foreach ($flashStored as $data) {
                $notice                     = Notice::fromArray($data);
                $notice->flash              = true;
                self::$notices[$notice->id] = $notice;
            }
        }
    }

    /**
     * Save persistent notices to cache.
     *
     * @return void
     */
    public static function saveToTransient(): void
    {
        $persistent = [];
        $flash      = [];

        foreach (self::$notices as $notice) {
            if ($notice->persistent) {
                $persistent[] = $notice->toArray();
            }

            if ($notice->flash) {
                $flash[] = $notice->toArray();
            }
        }

        $cache = CacheManager::instance();

        if ($persistent) {
            $cache->set(CacheKeys::adminNotices(), $persistent, HOUR_IN_SECONDS);
        } else {
            $cache->delete(CacheKeys::adminNotices());
        }

        if ($flash) {
            $cache->set(CacheKeys::adminFlashNotices(), $flash, MINUTE_IN_SECONDS * 5);
        } else {
            $cache->delete(CacheKeys::adminFlashNotices());
        }
    }

    /**
     * Clear non-persistent notices.
     *
     * @return void
     */
    private static function clearNonPersistent(): void
    {
        foreach (self::$notices as $id => $notice) {
            if (!$notice->persistent) {
                unset(self::$notices[$id]);
            }
        }

        CacheManager::instance()->delete(CacheKeys::adminFlashNotices());
    }

    /**
     * Suppress third-party notices on plugin pages.
     *
     * @return void
     */
    public static function sanitizeNotices(): void
    {
        if (!PluginUtils::isPluginScreen()) {
            return;
        }

        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('user_admin_notices');
        remove_all_actions('network_admin_notices');
    }
}