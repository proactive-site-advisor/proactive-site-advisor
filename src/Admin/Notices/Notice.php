<?php

namespace ProactiveSiteAdvisor\Admin\Notices;

use ProactiveSiteAdvisor\Config\PrefixConfig;
use ProactiveSiteAdvisor\Utils\TemplateUtils;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Notice
 *
 * Represents a single admin notice with configuration options.
 *
 * @package ProactiveSiteAdvisor\Admin
 * @version 1.1.0
 */
class Notice
{
    /**
     * Notice types
     */
    public const TYPE_SUCCESS = 'success';
    public const TYPE_ERROR   = 'error';
    public const TYPE_WARNING = 'warning';
    public const TYPE_INFO    = 'info';

    /**
     * Unique notice identifier
     *
     * @var string
     */
    public string $id;

    /**
     * Notice message
     *
     * @var string
     */
    public string $message;

    /**
     * Notice type (success, error, warning, info)
     *
     * @var string
     */
    public string $type;

    /**
     * Whether the notice can be dismissed
     *
     * @var bool
     */
    public bool $dismissible;

    /**
     * Whether the notice persists across page loads
     *
     * @var bool
     */
    public bool $persistent;

    /**
     * Optional notice title
     *
     * @var string|null
     */
    public ?string $title = null;

    /**
     * Optional icon class
     *
     * @var string|null
     */
    public ?string $icon = null;

    /**
     * Extra CSS classes for styling (template-level)
     *
     * @var array
     */
    public array $extraClasses = [];

    /**
     * Additional CSS classes (logic-level)
     *
     * @var array
     */
    public array $classes = [];

    /**
     * Limit notice to specific admin screen
     *
     * @var string|null
     */
    public ?string $screen = null;

    /**
     * Limit notice to users with specific capability
     *
     * @var string|null
     */
    public ?string $capability = null;

    /**
     * @var bool
     */
    public bool $flash = false;

    /**
     * Notice constructor.
     *
     * @param string $message The notice message.
     * @param string $type The notice type.
     */
    public function __construct(string $message, string $type = self::TYPE_INFO)
    {
        $this->id          = md5($message . '|' . $type . '|' . microtime(true));
        $this->message     = $message;
        $this->type        = $type;
        $this->dismissible = true;
        $this->persistent  = false;
    }

    /**
     * Set the notice ID.
     *
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Set whether the notice is dismissible.
     *
     * @param bool $dismissible
     *
     * @return $this
     */
    public function setDismissible(bool $dismissible): self
    {
        $this->dismissible = $dismissible;
        return $this;
    }

    /**
     * Set whether the notice persists across page loads.
     *
     * @param bool $persistent
     *
     * @return $this
     */
    public function setPersistent(bool $persistent): self
    {
        $this->persistent = $persistent;
        return $this;
    }

    /**
     * Limit notice to specific admin screen.
     *
     * @param string $screen
     *
     * @return $this
     */
    public function setScreen(string $screen): self
    {
        $this->screen = $screen;
        return $this;
    }

    /**
     * Limit notice to users with specific capability.
     *
     * @param string $capability
     *
     * @return $this
     */
    public function setCapability(string $capability): self
    {
        $this->capability = $capability;
        return $this;
    }

    /**
     * Add CSS classes to the notice.
     *
     * @param array $classes
     *
     * @return $this
     */
    public function addClasses(array $classes): self
    {
        $this->classes = array_unique(array_merge($this->classes, $classes));
        return $this;
    }

    /**
     * Replace extra CSS classes
     *
     * @param array $classes
     *
     * @return $this
     */
    public function setExtraClasses(array $classes): self
    {
        $this->extraClasses = $classes;
        return $this;
    }

    /**
     * Set optional notice title.
     *
     * @param string|null $title
     *
     * @return $this
     */
    public function setTitle(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Set optional notice icon.
     *
     * @param string|null $icon
     *
     * @return $this
     */
    public function setIcon(?string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Check if the notice should be displayed.
     *
     * @return bool
     */
    public function shouldDisplay(): bool
    {
        if ($this->capability && !current_user_can($this->capability)) {
            return false;
        }

        if ($this->screen) {
            $currentScreen = get_current_screen();
            if (!$currentScreen || $currentScreen->id !== $this->screen) {
                return false;
            }
        }

        return !($this->dismissible && AdminNotices::isDismissed($this->id));
    }

    /**
     * Render the notice HTML.
     *
     * @return string
     */
    public function render(): string
    {
        if (!$this->shouldDisplay()) {
            return '';
        }

        return TemplateUtils::renderTemplate(
            'admin/components/notices/simple-notice.php',
            [
                'id'           => $this->id,
                'type'         => $this->type,
                'message'      => $this->message,
                'title'        => $this->title,
                'icon'         => $this->getIcon(),
                'dismissible'  => $this->dismissible,
                'extraClasses' => $this->extraClasses,
                'persistent'   => $this->persistent,
                'flash'        => $this->flash,
            ]
        );
    }

    /**
     * Convert notice to array for storage.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'message'      => $this->message,
            'type'         => $this->type,
            'title'        => $this->title,
            'icon'         => $this->icon,
            'dismissible'  => $this->dismissible,
            'persistent'   => $this->persistent,
            'screen'       => $this->screen,
            'capability'   => $this->capability,
            'classes'      => $this->classes,
            'extraClasses' => $this->extraClasses,
            'flash'        => $this->flash,
        ];
    }

    /**
     * Create notice from array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $notice = new self(
            $data['message'] ?? '',
            $data['type'] ?? self::TYPE_INFO
        );

        if (isset($data['id'])) {
            $notice->setId($data['id']);
        }
        if (isset($data['dismissible'])) {
            $notice->setDismissible($data['dismissible']);
        }
        if (isset($data['persistent'])) {
            $notice->setPersistent($data['persistent']);
        }
        if (isset($data['screen'])) {
            $notice->setScreen($data['screen']);
        }
        if (isset($data['capability'])) {
            $notice->setCapability($data['capability']);
        }
        if (isset($data['classes'])) {
            $notice->addClasses($data['classes']);
        }
        if (isset($data['title'])) {
            $notice->setTitle($data['title']);
        }
        if (isset($data['icon'])) {
            $notice->setIcon($data['icon']);
        }
        if (isset($data['extraClasses'])) {
            $notice->setExtraClasses($data['extraClasses']);
        }
        if (isset($data['flash'])) {
            $notice->flash = $data['flash'];
        }

        return $notice;
    }

    /**
     * Mark notice as flash.
     *
     * @return $this
     */
    public function flash(): self
    {
        $this->flash       = true;
        $this->persistent  = false;
        $this->dismissible = false;

        return $this;
    }

    /**
     * Get resolved icon for the notice.
     *
     * @return string|null
     */
    public function getIcon(): ?string
    {
        if ($this->icon) {
            return $this->icon;
        }

        // default icons resolved at runtime
        $defaultIcons = [
            self::TYPE_SUCCESS => PrefixConfig::css('icon--check-circle'),
            self::TYPE_ERROR   => PrefixConfig::css('icon--critical'),
            self::TYPE_WARNING => PrefixConfig::css('icon--alert'),
            self::TYPE_INFO    => PrefixConfig::css('icon--info'),
        ];

        return $defaultIcons[$this->type] ?? null;
    }
}
