<?php

namespace idoit\Component\Processor\Dto\Object;

use idoit\Component\Processor\Validation\IsConstant;
use idoit\Component\Processor\Validation\IsValidConstantString;
use Idoit\Dto\Validation\IsNull;
use Idoit\Dto\Validation\OneOf;
use Idoit\Dto\Validation\OrX;
use Idoit\Dto\Validation\PositiveInteger;
use Idoit\Dto\Validation\Required;

/**
 * Object read DTO. Will be returned when reading objects via processor.
 */
class CreateRequest
{
    public function __construct(
        #[Required]
        public readonly string $title = '',
        #[Required]
        #[OrX(new PositiveInteger(), new IsConstant('C__OBJTYPE__'))]
        public readonly int|string $objectType = 0,
        #[OrX(new IsNull(), new IsValidConstantString())]
        public readonly string|null $constant = null,
        public readonly string|null $description = null,
        public readonly string|null $image = null,
        #[OrX(new IsNull(), new OneOf([C__RECORD_STATUS__NORMAL, C__RECORD_STATUS__ARCHIVED, C__RECORD_STATUS__DELETED, C__RECORD_STATUS__TEMPLATE, C__RECORD_STATUS__MASS_CHANGES_TEMPLATE]))]
        public readonly int|null $status = null,
        public readonly string|null $sysid = null,
        #[OrX(new IsNull(), new PositiveInteger(), new IsConstant('C__CMDB_STATUS__'))]
        public readonly int|string|null $cmdbStatus = null,
        #[OrX(new IsNull(), new PositiveInteger(), new IsConstant())]
        public readonly int|string|null $category = null,
        #[OrX(new IsNull(), new PositiveInteger(), new IsConstant())]
        public readonly int|string|null $purpose = null
    ) {
    }
}
