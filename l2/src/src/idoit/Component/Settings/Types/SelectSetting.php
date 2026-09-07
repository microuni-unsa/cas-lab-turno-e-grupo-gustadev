<?php

namespace idoit\Component\Settings\Types;

/**
 * i-doit 'select' setting class - use this to extend settings.
 *
 * @package     idoit\Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class SelectSetting extends AbstractSetting
{
    private array $options;

    public function __construct(string $key, string $name, ?string $description = null, mixed $default = null, array $options = [])
    {
        $this->type = 'select';

        parent::__construct($key, $name, $description, $default);

        $this->options = $options;
    }

    public function toArray(): array
    {
        $setting = parent::toArray();
        $setting['options'] = $this->options;

        return $setting;
    }
}
