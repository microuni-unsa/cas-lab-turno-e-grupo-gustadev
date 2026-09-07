<?php

namespace idoit\Component\Notify;

use idoit\Component\Notify\Interfaces\NotificationInterface;

/**
 * Class DetailedNotification
 */
class Notification implements NotificationInterface
{
    /**
     * Additional notification attributes
     *
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var string
     */
    protected string $message = '';

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @return array
     */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @param string $message    The notification message
     * @param string $title      The notification title
     * @param array  $attributes Optional attributes
     */
    public function __construct(string $message, string $title, array $attributes = [])
    {
        $this->message = $message;
        $this->title = $title;
        $this->attributes = $attributes;
    }
}
