<?php

namespace idoit\Event\System;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * i-doit
 *
 * Event to extend the 'Help' menu with custom entries.
 *
 * @package     i-doit
 * @subpackage  System
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @see         ID-11341
 */
class ExtendHelpMenu extends Event
{
    const NAME = 'extend.help-menu';

    private array $entries;

    public function __construct()
    {
        $this->entries = [];
    }

    public function addEntry(string $html): void
    {
        $this->entries[] = $html;
    }

    public function getEntries(): array
    {
        return $this->entries;
    }
}
