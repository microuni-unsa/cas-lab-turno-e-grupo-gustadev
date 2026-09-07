<?php

namespace idoit\Component\Settings\Types;

/**
 * i-doit abstract setting class.
 *
 * @package     idoit\Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
abstract class AbstractSetting
{
    protected string $key;

    protected string $name;

    protected ?string $description;

    protected mixed $default;

    protected string $type;

    public function __construct(string $key, string $name, ?string $description = null, mixed $default = null)
    {
        $this->key = $key;
        $this->name = $name;
        $this->description = $description;
        $this->default = $default;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->name,
            'type' => $this->type,
            'description' => $this->description,
            'default' => $this->default,
        ];
    }
}
