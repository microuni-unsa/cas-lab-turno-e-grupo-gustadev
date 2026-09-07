<?php

namespace idoit\Module\Cmdb\Event;

class EventListenerCollection
{
    /**
     * @var EventListenerContainer[]
     */
    private array $eventListeners = [];

    /**
     * @param EventListenerContainer $eventListenerContainer
     *
     * @return $this
     */
    public function addEventListener(EventListenerContainer $eventListenerContainer): EventListenerCollection
    {
        $this->eventListeners[] = $eventListenerContainer;
        return $this;
    }

    /**
     * @return EventListenerContainer[]
     */
    public function getEventListeners(): array
    {
        return $this->eventListeners;
    }
}
