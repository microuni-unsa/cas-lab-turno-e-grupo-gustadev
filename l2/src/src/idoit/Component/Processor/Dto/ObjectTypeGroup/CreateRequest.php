<?php

namespace idoit\Component\Processor\Dto\ObjectTypeGroup;

use idoit\Component\Processor\Validation\IsValidConstantString;
use Idoit\Dto\Validation\IsNull;
use Idoit\Dto\Validation\OneOf;
use Idoit\Dto\Validation\OrX;
use Idoit\Dto\Validation\Required;

/**
 * Create DTO for object type groups.
 */
class CreateRequest
{
    public function __construct(
        #[Required]
        public readonly string $title,
        #[OrX(new IsNull(), new IsValidConstantString('C__OBJTYPE_GROUP__'))]
        public readonly string|null $constant = null,
        public readonly int $sort = C__PROPERTY__DEFAULT_SORT,
        #[OrX(new IsNull(), new OneOf([C__RECORD_STATUS__BIRTH, C__RECORD_STATUS__NORMAL]))]
        public readonly int|null $status = null,
    ) {
    }
}
