<?php

namespace idoit\Module\Report\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * i-doit
 *
 * Delete report event.
 *
 * @package     i-doit
 * @subpackage  Document
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @see         ID-10975
 */
class DeleteReport extends GenericEvent
{
    const NAME = 'report.delete';

    public function __construct(private int $id)
    {

    }

    public function getId(): int
    {
        return $this->id;
    }
}
