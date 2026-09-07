<?php

namespace idoit\Component\Settings\Types;

/**
 * i-doit 'textarea' setting class - use this to extend settings.
 *
 * @package     idoit\Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class TextareaSetting extends AbstractSetting
{
    private string $placeholder;

    public function __construct(string $key, string $name, ?string $description = null, mixed $default = null, string $placeholder = '')
    {
        $this->type = 'textarea';

        parent::__construct($key, $name, $description, $default);

        $this->placeholder = $placeholder;
    }

    public function toArray(): array
    {
        $setting = parent::toArray();
        $setting['placeholder'] = $this->placeholder;

        return $setting;
    }
}
