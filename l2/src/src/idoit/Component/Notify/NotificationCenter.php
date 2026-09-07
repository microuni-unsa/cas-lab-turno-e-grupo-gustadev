<?php

namespace idoit\Component\Notify;

use idoit\Component\Notify\Interfaces\HandlerInterface;
use idoit\Component\Notify\Interfaces\NotificationInterface;

/**
 * Class NotificationCenter
 */
class NotificationCenter
{
    /**
     * Debug
     */
    const DEBUG = 0;

    /**
     * Informative
     */
    const INFO = 1;

    /**
     * Notices
     */
    const NOTICE = 3;

    /**
     * Warnings or Exceptions
     */
    const WARNING = 4;

    /**
     * Runtime errors
     */
    const ERROR = 2;

    /**
     * Critical problems
     */
    const CRITICAL = 2;

    /**
     * Alerts
     */
    const ALERT = 2;

    /**
     * Problematic issues
     */
    const EMERGENCY = 2;

    /**
     * The handler stack
     *
     * @var HandlerInterface[]
     */
    protected array $handlers;

    /**
     * @var NotificationInterface[]
     */
    protected array $notifications = [];

    /**
     * Factory method for a better method chaining experience
     *
     * @param array $handlers
     *
     * @return NotificationCenter
     */
    public static function factory(array $handlers = []): self
    {
        return new self($handlers);
    }

    /**
     * Push handler to stack.
     *
     * @param HandlerInterface $handler
     *
     * @return $this
     */
    public function addHandler(HandlerInterface $handler): self
    {
        array_unshift($this->handlers, $handler);

        return $this;
    }

    /**
     * Handles notification for every handler
     *
     * @param NotificationInterface $notification the notification instanceitself
     * @param int                   $level        Level of this notification
     *
     * @return void
     * @deprecated This method will be changed to 'private'
     */
    public function notify(NotificationInterface $notification, int $level = self::INFO): void
    {
        foreach ($this->handlers as $handler) {
            $handler->handle($notification, $level);
        }
    }

    /**
     * Alert level notification
     *
     * @param string $message notification message (body)
     * @param array  $context notification parameters
     *
     * @return void
     * @deprecated Please refer to 'error' method.
     */
    public function alert(string $message, array $context = []): void
    {
        $this->notify(new Notification($message, '', $context), static::ALERT);
    }

    /**
     * Critical level notification
     *
     * @param string $message notification message (body)
     * @param array  $context notification parameters
     *
     * @return void
     * @deprecated Please refer to 'error' method.
     */
    public function critical(string $message, array $context = []): void
    {
        $this->notify(new Notification($message, '', $context), static::CRITICAL);
    }

    /**
     * Debug level notification
     *
     * @param string $message notification message (body)
     * @param array  $context notification parameters
     *
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->notify(new Notification($message, '', $context), static::DEBUG);
    }

    /**
     * Emergency level notification
     *
     * @param string $message notification message (body)
     * @param array  $context notification parameters
     *
     * @return void
     * @deprecated Please refer to 'error' method.
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->notify(new Notification($message, '', $context), static::EMERGENCY);
    }

    /**
     * Error level notification
     *
     * @param string $message notification message (body)
     * @param array  $context notification parameters
     *
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->notify(new Notification($message, '', $context), static::ERROR);
    }

    /**
     * Info level notification
     *
     * @param string $message notification message (body)
     * @param array  $context notification parameters
     *
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->notify(new Notification($message, '', $context), static::INFO);
    }

    /**
     * Adds a new notification message to the queue as an attribute aware notification
     *
     * @param int    $level   The log level
     * @param string $message notification message (body)
     * @param array  $context notification parameters
     *
     * @return void
     * @deprecated
     */
    public function log(int $level, string $message, array $context = []): void
    {
        $this->notify(new Notification($message, '', $context), $level);
    }

    /**
     * Notice level notification
     *
     * @param string $message notification message (body)
     * @param array  $context notification parameters
     *
     * @return void
     */
    public function notice(string $message, array $context = []): void
    {
        $this->notify(new Notification($message, '', $context), static::NOTICE);
    }

    /**
     * Warning level notification
     *
     * @param string $message notification message (body)
     * @param array  $context notification parameters
     *
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->notify(new Notification($message, '', $context), static::WARNING);
    }

    /**
     * @param HandlerInterface[] $handlers Optional stack of handlers, the first one in the array is called first, etc.
     */
    public function __construct(array $handlers = [])
    {
        $this->handlers = $handlers;
    }
}
