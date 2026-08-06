<?php

namespace ProactiveSiteAdvisor\Utils;

if (!defined('ABSPATH')) {
    exit;
}

use ProactiveSiteAdvisor\Config\PrefixConfig;

/**
 * Plugin-specific logging utility with multiple log levels and file-based output.
 *
 * phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- File logging requires direct file writes.
 * phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Error logging is intentionally used for debug output.
 *
 * @package ProactiveSiteAdvisor\Utils
 * @since   1.0.0
 */
class Logger
{
    /** Log level constants */
    public const LEVEL_DEBUG    = 'debug';
    public const LEVEL_INFO     = 'info';
    public const LEVEL_WARNING  = 'warning';
    public const LEVEL_ERROR    = 'error';
    public const LEVEL_CRITICAL = 'critical';

    /** Log level priorities (lower = more verbose) */
    public const LEVEL_PRIORITIES = [
        self::LEVEL_DEBUG    => 0,
        self::LEVEL_INFO     => 1,
        self::LEVEL_WARNING  => 2,
        self::LEVEL_ERROR    => 3,
        self::LEVEL_CRITICAL => 4,
    ];

    /** Log file path. */
    private static string $logFile;

    /** Minimum log level to record. */
    private static string $minLevel = self::LEVEL_DEBUG;

    /** Whether logging is enabled. */
    private static bool $enabled = true;

    /** Maximum log file size in bytes (5MB). */
    private static int $maxFileSize = 5242880;

    /** Number of rotated log files to keep. */
    private static int $maxFiles = 3;

    /** Initialize the logger. */
    public static function init(): void
    {
        self::$logFile = self::getLogPath();
        self::createLogDirectory();

        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            self::$minLevel = self::LEVEL_WARNING;
        }
    }

    /** Set the minimum log level. */
    public static function setMinLevel(string $level): void
    {
        if (isset(self::LEVEL_PRIORITIES[$level])) {
            self::$minLevel = $level;
        }
    }

    /** Enable logging. */
    public static function enable(): void
    {
        self::$enabled = true;
    }

    /** Disable logging. */
    public static function disable(): void
    {
        self::$enabled = false;
    }

    /** Log a debug message. */
    public static function debug(string $message, array $context = []): void
    {
        self::log(self::LEVEL_DEBUG, $message, $context);
    }

    /** Log an info message. */
    public static function info(string $message, array $context = []): void
    {
        self::log(self::LEVEL_INFO, $message, $context);
    }

    /** Log a warning message. */
    public static function warning(string $message, array $context = []): void
    {
        self::log(self::LEVEL_WARNING, $message, $context);
    }

    /** Log an error message. */
    public static function error(string $message, array $context = []): void
    {
        self::log(self::LEVEL_ERROR, $message, $context);
    }

    /** Log a critical message. */
    public static function critical(string $message, array $context = []): void
    {
        self::log(self::LEVEL_CRITICAL, $message, $context);
    }

    /** Log a message with a specific level. */
    public static function log(string $level, string $message, array $context = []): void
    {
        if (!self::$enabled || !self::shouldLog($level)) {
            return;
        }

        self::rotateLogs();

        $formattedMessage = self::formatMessage($level, $message, $context);

        self::writeToFile($formattedMessage);

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            self::writeToErrorLog($level, $message, $context);
        }
    }

    /** Get the log file path. */
    public static function getLogPath(): string
    {
        $uploadDir = wp_upload_dir();

        $directory = $uploadDir['basedir'] . '/' . PrefixConfig::handle('logs');

        return $directory . '/' . PrefixConfig::handle('plugin') . '.log';
    }

    /** Get the log directory path. */
    public static function getLogDirectory(): string
    {
        $uploadDir = wp_upload_dir();
        return $uploadDir['basedir'] . '/' . PrefixConfig::handle('logs');
    }

    /** Clear the log file. */
    public static function clearLog(): bool
    {
        if (empty(self::$logFile)) {
            return true;
        }

        if (file_exists(self::$logFile)) {
            return file_put_contents(self::$logFile, '') !== false;
        }
        return true;
    }

    /** Get log file contents. */
    public static function getLogContents(int $lines = 100): array
    {
        if (empty(self::$logFile) || !file_exists(self::$logFile)) {
            return [];
        }

        $file = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($file === false) {
            return [];
        }

        return array_slice($file, -$lines);
    }

    /** Rotate log files if needed. */
    public static function rotateLogs(): void
    {
        if (empty(self::$logFile) || !file_exists(self::$logFile)) {
            return;
        }

        if (filesize(self::$logFile) < self::$maxFileSize) {
            return;
        }

        global $wp_filesystem;

        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        for ($i = self::$maxFiles - 1; $i >= 1; $i--) {
            $oldFile = self::$logFile . '.' . $i;
            $newFile = self::$logFile . '.' . ($i + 1);

            if (file_exists($oldFile)) {
                if ($i === self::$maxFiles - 1) {
                    wp_delete_file($oldFile);
                } else {
                    $wp_filesystem->move($oldFile, $newFile, true);
                }
            }
        }

        $wp_filesystem->move(self::$logFile, self::$logFile . '.1', true);
    }

    /** Check if a message should be logged based on level. */
    private static function shouldLog(string $level): bool
    {
        $levelPriority = self::LEVEL_PRIORITIES[$level] ?? 0;
        $minPriority   = self::LEVEL_PRIORITIES[self::$minLevel] ?? 0;

        return $levelPriority >= $minPriority;
    }

    /** Format a log message. */
    private static function formatMessage(string $level, string $message, array $context): string
    {
        $timestamp  = current_time('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);

        $formatted = "[$timestamp] [$levelUpper] $message";

        if (!empty($context)) {
            $formatted .= ' | Context: ' . wp_json_encode($context, JSON_UNESCAPED_SLASHES);
        }

        return $formatted . PHP_EOL;
    }

    /** Write a message to the log file. */
    private static function writeToFile(string $message): void
    {
        if (empty(self::$logFile)) {
            self::init();
        }

        file_put_contents(self::$logFile, $message, FILE_APPEND | LOCK_EX);
    }

    /** Write a message to the PHP error log. */
    private static function writeToErrorLog(string $level, string $message, array $context): void
    {
        $slug       = PrefixConfig::SLUG;
        $contextStr = !empty($context) ? ' | ' . wp_json_encode($context) : '';

        /** @noinspection ForgottenDebugOutputInspection */
        error_log("$slug [$level] $message$contextStr");
    }

    /** Create the log directory with security measures. */
    private static function createLogDirectory(): void
    {
        $logDir = self::getLogDirectory();

        if (!is_dir($logDir)) {
            wp_mkdir_p($logDir);

            $htaccess = $logDir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, 'deny from all');
            }

            $index = $logDir . '/index.php';
            if (!file_exists($index)) {
                file_put_contents($index, '<?php // Silence is golden.');
            }
        }
    }
}