<?php

namespace idoit\Event\System\Settings;

use idoit\Component\Settings\SettingsCollection;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * i-doit
 *
 * Abstract event to extend settings.
 *
 * @package     i-doit
 * @subpackage  System
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @see         ID-11818
 */
abstract class AbstractExtendSettings extends Event
{
    protected array $settingsCollections;

    public function __construct()
    {
        $this->settingsCollections = [];
    }

    public function addSettingsCollection(SettingsCollection $settingsCollection): void
    {
        $this->settingsCollections[] = $settingsCollection;
    }

    /**
     * @return SettingsCollection[]
     */
    public function getSettings(): array
    {
        return $this->settingsCollections;
    }
}
