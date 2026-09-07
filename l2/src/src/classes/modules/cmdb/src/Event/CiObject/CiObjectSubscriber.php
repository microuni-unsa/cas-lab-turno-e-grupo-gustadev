<?php
namespace idoit\Module\Cmdb\Event\CiObject;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CiObjectSubscriber implements EventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            Created::NAME => 'onObjectCreated',
            Updated::NAME => 'onObjectUpdated',
            Ranked::NAME => 'onObjectRanked',
            Purged::NAME => 'onObjectPurged',
        ];
    }

    /**
     * @param Created $event
     *
     * @return void
     */
    public function onObjectCreated(Created $event)
    {
        // Nothing to do at the moment
    }

    /**
     * @param Updated $event
     *
     * @return void
     */
    public function onObjectUpdated(Updated $event)
    {
        // Nothing to do at the moment
    }

    /**
     * @param Ranked $event
     *
     * @return void
     */
    public function onObjectRanked(Ranked $event)
    {
        // Nothing to do at the moment
    }

    /**
     * @param Purged $event
     *
     * @return void
     */
    public function onObjectPurged(Purged $event)
    {
        // Nothing to do at the moment
    }
}
