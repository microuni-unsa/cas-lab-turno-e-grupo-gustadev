<?php

namespace idoit\Component\Processor\Dto\ObjectTypeGroup;

use Idoit\Dto\Validation\IsArrayOf;
use Idoit\Dto\Validation\OneOf;
use Idoit\Dto\Validation\PositiveInteger;

/**
 * Read DTO for object type groups.
 */
class ReadRequest
{
    public function __construct(
        #[IsArrayOf(new PositiveInteger())]
        public readonly array $ids,
        #[IsArrayOf(new OneOf([C__RECORD_STATUS__BIRTH, C__RECORD_STATUS__NORMAL]))]
        public readonly array $status = []
    ) {
    }
}
