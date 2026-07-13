<?php

namespace ProactiveSiteAdvisor\CLI;

use ProactiveSiteAdvisor\Abstracts\AbstractSingleton;
use ProactiveSiteAdvisor\CLI\Commands\SeedCommand;
use ProactiveSiteAdvisor\CLI\Commands\TruncateCommand;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Manages WP-CLI command registration.
 *
 * @see    \WP_CLI
 * @package ProactiveSiteAdvisor\CLI
 * @since   1.0.0
 */
class CLIManager extends AbstractSingleton
{
    /** Command namespace. */
    private string $namespace = 'proactive-site-advisor';

    /** Registered commands. */
    private array $commands = [
        'seed'     => SeedCommand::class,
        'truncate' => TruncateCommand::class,
    ];

    /** Register CLI commands. */
    public function register(): void
    {
        /**
         * Filters the registered CLI commands.
         *
         * @param array $commands Array of command name => class mappings.
         * @param string $namespace Command namespace.
         * @since  1.0.0
         */
        $commands = apply_filters('proactive_site_advisor_cli_commands', $this->commands, $this->namespace);

        foreach ($commands as $name => $class) {
            if (!class_exists($class)) {
                continue;
            }

            if (class_exists('WP_CLI')) {
                \WP_CLI::add_command("$this->namespace $name", $class);
            }
        }
    }

    /** Add a command. */
    public function addCommand(string $name, string $class): self
    {
        $this->commands[$name] = $class;

        return $this;
    }

    /** Remove a command. */
    public function removeCommand(string $name): self
    {
        unset($this->commands[$name]);

        return $this;
    }

    /** Get registered commands. */
    public function getCommands(): array
    {
        return $this->commands;
    }

    /** Get the command namespace. */
    public function getNamespace(): string
    {
        return $this->namespace;
    }
}