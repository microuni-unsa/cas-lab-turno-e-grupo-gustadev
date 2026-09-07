<?php

namespace idoit\Module\Cmdb\Event;

class EventListenerContainer
{
    /**
     * @var string
     */
    private string $listenToEvent;

    /**
     * @var string
     */
    private string $className;

    /**
     * @var string
     */
    private string $method;

    /**
     * @param string $listenToEvent
     * @param string $method
     * @param string $className
     */
    public function __construct(string $listenToEvent, string $method, string $className)
    {
        $this->listenToEvent = $listenToEvent;
        $this->method = $method;
        $this->className = $className;
    }

    /**
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getListenToEvent(): string
    {
        return $this->listenToEvent;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
