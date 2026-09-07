<?php

namespace idoit\Component\Processor\Dto\ObjectType;

use idoit\Component\Processor\Validation\IsConstant;
use idoit\Component\Processor\Validation\IsValidHexColor;
use Idoit\Dto\Validation\IsInt;
use Idoit\Dto\Validation\IsNull;
use Idoit\Dto\Validation\OrX;
use Idoit\Dto\Validation\PositiveInteger;
use Idoit\Dto\Validation\Required;

/**
 * Update DTO for object type.
 */
class UpdateRequest
{
    public function __construct(
        #[Required]
        #[PositiveInteger]
        public readonly int $id = 0,
        public readonly string|null $title = null,
        #[OrX(new IsNull(), new PositiveInteger(), new IsConstant('C__OBJTYPE_GROUP__'))]
        public readonly int|string|null $objectTypeGroup = null,
        public readonly string|null $description = null,
        public readonly string|null $image = null,
        public readonly string|null $icon = null,
        #[OrX(new IsNull(), new IsInt(), new IsConstant('C__CATS__'))]
        public readonly string|int|null $specificCategory = null,
        public readonly bool|null $isContainer = null,
        public readonly bool|null $isPositionableInRack = null,
        public readonly bool|null $isVisible = null,
        public readonly bool|null $isRelationMaster = null,
        public readonly bool|null $hasOverviewPage = null,
        public readonly int|null $sort = null,
        #[OrX(new IsNull(), new IsValidHexColor())]
        public readonly string|null $color = null,
        public readonly int|null $defaultTemplate = null,
        public readonly string|null $sysidPrefix = null
    ) {
    }
}
