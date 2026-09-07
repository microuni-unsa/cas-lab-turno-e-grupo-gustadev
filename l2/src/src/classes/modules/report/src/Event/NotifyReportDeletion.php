<?php

namespace idoit\Module\Report\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * i-doit
 *
 * Delete report notification event.
 * This can be used in order to send messages to the user, before a report gets deleted.
 *
 * @package     i-doit
 * @subpackage  Document
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @see         ID-10975
 */
class NotifyReportDeletion extends GenericEvent
{
    const NAME = 'report.delete.notification';

    private array $ids;

    private array $messages;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
        $this->messages = [];
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function addMessage(string $message): void
    {
        $this->messages[] = $message;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }
}
