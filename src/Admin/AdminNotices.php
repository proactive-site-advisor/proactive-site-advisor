<?php

namespace ProactiveSiteAdvisor\Admin;

use ProactiveSiteAdvisor\Cache\CacheKeys;
use ProactiveSiteAdvisor\Cache\CacheManager;
use ProactiveSiteAdvisor\Components\AjaxComponent;
use ProactiveSiteAdvisor\Config\UserOptions;
use ProactiveSiteAdvisor\Utils\OptionUtils;
use ProactiveSiteAdvisor\Config\PrefixConfig;

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
     * In-memory notice storage
     *
     * @var Notice[]
     */
    private static array $notices = [];

    /**
     * Whether the class has been initialized
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

        add_action('admin_notices', [self::class, 'render']);

        // Add this line to clean the room on our pages
        add_action('admin_head', [self::class, 'sanitizeNotices'], 1);

        // Register AJAX handler for dismissing notices
        AjaxComponent::register('dismiss_notice', [self::class, 'handleDismiss'], false);

        // Load persistent notices from transient
        self::loadFromTransient();

        self::$initialized = true;
    }

    /**
     * Add a notice.
     *
     * @param Notice $notice The notice to add.
     * @return void
     */
    public static function add(Notice $notice): void
    {
        self::$notices[$notice->id] = $notice;

        if ($notice->persistent) {
            self::saveToTransient();
        }
    }

    /**
     * Add a success notice.
     *
     * @param string $message The message.
     * @param bool $dismissible Whether dismissible.
     * @return Notice
     */
    public static function success(string $message, bool $dismissible = true): Notice
    {
        $notice = new Notice($message, Notice::TYPE_SUCCESS);
        $notice->setDismissible($dismissible);
        self::add($notice);
        return $notice;
    }

    /**
     * Add an error notice.
     *
     * @param string $message The message.
     * @param bool $dismissible Whether dismissible.
     * @return Notice
     */
    public static function error(string $message, bool $dismissible = true): Notice
    {
        $notice = new Notice($message, Notice::TYPE_ERROR);
        $notice->setDismissible($dismissible);
        self::add($notice);
        return $notice;
    }

    /**
     * Add a warning notice.
     *
     * @param string $message The message.
     * @param bool $dismissible Whether dismissible.
     * @return Notice
     */
    public static function warning(string $message, bool $dismissible = true): Notice
    {
        $notice = new Notice($message, Notice::TYPE_WARNING);
        $notice->setDismissible($dismissible);
        self::add($notice);
        return $notice;
    }

    /**
     * Add an info notice.
     *
     * @param string $message The message.
     * @param bool $dismissible Whether dismissible.
     * @return Notice
     */
    public static function info(string $message, bool $dismissible = true): Notice
    {
        $notice = new Notice($message, Notice::TYPE_INFO);
        $notice->setDismissible($dismissible);
        self::add($notice);
        return $notice;
    }

    /**
     * Remove a notice by ID.
     *
     * @param string $id Notice ID.
     * @return void
     */
    public static function remove(string $id): void
    {
        unset(self::$notices[$id]);
        self::saveToTransient();
    }

    /**
     * Clear all notices.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$notices = [];
        CacheManager::getInstance()->delete(CacheKeys::adminNotices());
    }

    /**
     * Render all notices.
     *
     * @return void
     */
    public static function render(): void
    {
        foreach (self::$notices as $notice) {
            // Safe: All HTML, classes, and attributes are escaped internally in Notice::render().
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $notice->render();
        }

        // Clear non-persistent notices after rendering
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

        if (empty($noticeId)) {
            AjaxComponent::sendError(__('Invalid notice ID.', 'proactive-site-advisor'));
            return;
        }

        self::markDismissed($noticeId);
        self::remove($noticeId);

        AjaxComponent::sendSuccess([], __('Notice dismissed.', 'proactive-site-advisor'));
    }

    /**
     * Check if a notice has been dismissed by the current user.
     *
     * @param string $id Notice ID.
     * @return bool
     */
    public static function isDismissed(string $id): bool
    {
        $dismissed = OptionUtils::getUserOption(UserOptions::DISMISSED_NOTICES, []);

        if (!is_array($dismissed)) {
            return false;
        }

        return in_array($id, $dismissed, true);
    }

    /**
     * Mark a notice as dismissed for the current user.
     *
     * @param string $id Notice ID.
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
        $stored = CacheManager::getInstance()->get(CacheKeys::adminNotices());

        if (!is_array($stored)) {
            return;
        }

        foreach ($stored as $data) {
            $notice                     = Notice::fromArray($data);
            self::$notices[$notice->id] = $notice;
        }
    }

    /**
     * Save persistent notices to cache.
     *
     * @return void
     */
    private static function saveToTransient(): void
    {
        $persistent = [];

        foreach (self::$notices as $notice) {
            if ($notice->persistent) {
                $persistent[] = $notice->toArray();
            }
        }

        $cache = CacheManager::getInstance();

        if (!empty($persistent)) {
            $cache->set(CacheKeys::adminNotices(), $persistent, HOUR_IN_SECONDS);
        } else {
            $cache->delete(CacheKeys::adminNotices());
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
    }

    /**
     * Suppress third-party notices on plugin pages to maintain a clean UI.
     *
     * @return void
     */
    public static function sanitizeNotices(): void
    {
        $screen = get_current_screen();

        // Only target screens belonging to our plugin
        if (!$screen || strpos($screen->id, PrefixConfig::BASE) === false) {
            return;
        }

        // Clear all core and third-party notice hooks
        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
        remove_all_actions('user_admin_notices');
        remove_all_actions('network_admin_notices');

        // Re-add our internal notice rendering
        add_action('admin_notices', [self::class, 'render']);
    }
}
