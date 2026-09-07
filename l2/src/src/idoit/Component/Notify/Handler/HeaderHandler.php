<?php

namespace idoit\Component\Notify\Handler;

use idoit\Component\Notify\Interfaces\HandlerInterface;
use idoit\Component\Notify\Interfaces\NotificationInterface;

/**
 * Class HeaderHandler
 *
 * Sends various HTTP Header Notifications to be interpreted by a javascript implementation.
 */
class HeaderHandler implements HandlerInterface
{
    /**
     * @var int
     */
    protected static int $messageindex = 0;

    /**
     * Handle a notification.
     *
     * @param NotificationInterface $notification
     * @param int                   $level
     *
     * @return void
     */
    public function handle(NotificationInterface $notification, int $level): void
    {
        if (headers_sent()) {
            return;
        }

        $options = $notification->attributes();
        $options['header'] = $notification->title();

        $key = 'X-i-doit-Notification-' . (self::$messageindex++);
        $value = \isys_format_json::encode([
            'message' => $notification->message(),
            'type'    => $level,
            'options' => $options
        ]);

        header($key . ':' . $value);
    }
}
