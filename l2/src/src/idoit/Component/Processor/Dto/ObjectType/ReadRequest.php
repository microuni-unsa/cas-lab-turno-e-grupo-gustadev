<?php

namespace idoit\Component\Processor\Dto\ObjectType;

use Idoit\Dto\Validation\IsArrayOf;
use Idoit\Dto\Validation\OneOf;
use Idoit\Dto\Validation\PositiveInteger;

/**
 * Read DTO for object types.
 */
class ReadRequest
{
    public function __construct(
        #[IsArrayOf(new PositiveInteger())]
        public readonly array $ids,
        #[IsArrayOf(new OneOf([C__RECORD_STATUS__NORMAL, C__RECORD_STATUS__DELETED]))]
        public readonly array $status = [],
        public readonly bool $withCategories = false
    ) {
    }
}
