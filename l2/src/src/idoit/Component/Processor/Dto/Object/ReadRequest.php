<?php

namespace idoit\Component\Processor\Dto\Object;

use Idoit\Dto\Validation\IsArrayOf;
use Idoit\Dto\Validation\OneOf;
use Idoit\Dto\Validation\PositiveInteger;

/**
 * Read DTO for objects.
 */
class ReadRequest
{
    public function __construct(
        #[IsArrayOf(new PositiveInteger())]
        public readonly array $ids,
        #[IsArrayOf(new OneOf([C__RECORD_STATUS__NORMAL, C__RECORD_STATUS__ARCHIVED, C__RECORD_STATUS__DELETED]))]
        public readonly array $status = []
    ) {
    }
}
