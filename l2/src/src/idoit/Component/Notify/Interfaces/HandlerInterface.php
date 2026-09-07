<?php

namespace idoit\Component\Notify\Interfaces;

/**
 * This interfaces is responsible for doing stuff with the notifications
 */
interface HandlerInterface
{
    /**
     * Handle a notification
     *
     * @param NotificationInterface $notification
     * @param int                   $level
     *
     * @return void
     */
    public function handle(NotificationInterface $notification, int $level): void;
}
