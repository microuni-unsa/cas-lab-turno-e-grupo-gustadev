<?php

namespace idoit\Module\Cmdb\Event;

interface CiEventListenerInterface
{
    /**
     * @return EventListenerCollection
     */
    public static function getEventListeners(): EventListenerCollection;
}
