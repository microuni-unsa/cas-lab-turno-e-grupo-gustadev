<?php

namespace idoit\Component\Processor\Dto\ObjectTypeGroup;

use idoit\Component\Processor\Dto\Shape\StatusShape;
use idoit\Component\Processor\Serialization\StatusFormat;
use Idoit\Dto\Serialization\SerializableTrait;

/**
 * Object type group DTO.
 */
class Dto
{
    use SerializableTrait;

    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $titleRaw,
        public readonly string $constant,
        public readonly int $sort,
        #[StatusFormat]
        public readonly StatusShape $status
    ) {
    }
}
