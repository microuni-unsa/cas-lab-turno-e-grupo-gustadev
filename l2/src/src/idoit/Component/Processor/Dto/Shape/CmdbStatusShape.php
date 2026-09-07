<?php

namespace idoit\Component\Processor\Dto\Shape;

use Idoit\Dto\Serialization\SerializableTrait;

/**
 * CMDB status shape.
 */
class CmdbStatusShape
{
    use SerializableTrait;

    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $titleRaw,
        public readonly string $constant,
        public readonly string $color
    ) {
    }
}
