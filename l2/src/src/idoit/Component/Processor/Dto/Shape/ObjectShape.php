<?php

namespace idoit\Component\Processor\Dto\Shape;

use idoit\Component\Processor\Serialization\CmdbStatusFormat;
use idoit\Component\Processor\Serialization\ObjectTypeFormat;
use idoit\Component\Processor\Serialization\StatusFormat;
use Idoit\Dto\Serialization\SerializableTrait;

/**
 * Simplified object shape.
 */
class ObjectShape
{
    use SerializableTrait;

    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $titleRaw,
        #[CmdbStatusFormat]
        public readonly CmdbStatusShape $cmdbStatus,
        #[ObjectTypeFormat]
        public readonly ObjectTypeShape $objectType,
        #[StatusFormat]
        public readonly StatusShape $status
    ) {
    }
}
