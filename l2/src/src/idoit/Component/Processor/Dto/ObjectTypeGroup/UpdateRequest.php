<?php

namespace idoit\Component\Processor\Dto\ObjectTypeGroup;

use Idoit\Dto\Validation\IsNull;
use Idoit\Dto\Validation\OneOf;
use Idoit\Dto\Validation\OrX;
use Idoit\Dto\Validation\PositiveInteger;
use Idoit\Dto\Validation\Required;

/**
 * Update DTO for object type groups.
 */
class UpdateRequest
{
    public function __construct(
        #[Required]
        #[PositiveInteger]
        public readonly int $id = 0,
        public readonly string|null $title = null,
        public readonly int|null $sort = null,
        #[OrX(new IsNull(), new OneOf([C__RECORD_STATUS__BIRTH, C__RECORD_STATUS__NORMAL]))]
        public readonly int|null $status = null
    ) {
    }
}
