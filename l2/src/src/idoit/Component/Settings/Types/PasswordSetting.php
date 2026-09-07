<?php

namespace idoit\Component\Settings\Types;

/**
 * i-doit 'password' setting class - use this to extend settings.
 *
 * @package     idoit\Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class PasswordSetting extends AbstractSetting
{
    public function __construct(string $key, string $name, ?string $description = null, mixed $default = null)
    {
        $this->type = 'password';

        parent::__construct($key, $name, $description, $default);
    }
}
