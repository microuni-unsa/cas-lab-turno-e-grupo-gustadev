<?php

namespace idoit\Component\Processor\Dto\Shape;

use Idoit\Dto\Serialization\SerializableTrait;

/**
 * Simplified category shape.
 */
class SpecificCategoryShape
{
    use SerializableTrait;

    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $titleRaw,
        public readonly string $constant
    ) {
    }
}
