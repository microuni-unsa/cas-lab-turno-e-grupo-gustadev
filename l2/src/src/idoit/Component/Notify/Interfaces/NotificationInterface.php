<?php

namespace idoit\Component\Notify\Interfaces;

/**
 * This interfaces describes the AbstractNotification look and feel
 */
interface NotificationInterface
{
    /**
     * Return all atrributes
     *
     * @return array
     */
    public function attributes(): array;

    /**
     * Returns the message itself
     *
     * @return string
     */
    public function message(): string;

    /**
     * Returns the Title or Headline of the message
     *
     * @return string
     */
    public function title(): string;
}
