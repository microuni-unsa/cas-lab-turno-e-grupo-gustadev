<?php

namespace idoit\Component\Settings;

use idoit\Component\Settings\Types\AbstractSetting;

/**
 * i-doit SettingsCollection class - use this to extend settings.
 *
 * @package   idoit\Component
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
final class SettingsCollection
{
    private string $name;
    private array $settings;

    public function __construct(string $name, array $settings)
    {
        $this->name = $name;
        $this->settings = [];

        foreach ($settings as $setting) {
            $this->addSetting($setting);
        }
    }

    public function addSetting(AbstractSetting $setting): self
    {
        $this->settings[$setting->getKey()] = $setting;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return AbstractSetting[]
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
