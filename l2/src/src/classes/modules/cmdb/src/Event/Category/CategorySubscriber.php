<?php

namespace idoit\Module\Cmdb\Event\Category;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CategorySubscriber implements EventSubscriberInterface
{
    /**
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            Created::NAME => 'onCategoryCreated',
            Updated::NAME => 'onCategoryUpdated',
            Ranked::NAME => 'onCategoryRanked',
            Purged::NAME => 'onCategoryPurged',
        ];
    }

    /**
     * @param Created $event
     *
     * @return void
     */
    public function onCategoryCreated(Created $event)
    {
        // Nothing to do at the moment
    }

    /**
     * @param Updated $event
     *
     * @return void
     */
    public function onCategoryUpdated(Updated $event)
    {
        // Nothing to do at the moment
    }

    /**
     * @param Ranked $event
     *
     * @return void
     */
    public function onCategoryRanked(Ranked $event)
    {
        // Nothing to do at the moment
    }

    /**
     * @param Purged $event
     *
     * @return void
     */
    public function onCategoryPurged(Purged $event)
    {
        // Nothing to do at the moment
    }
}
