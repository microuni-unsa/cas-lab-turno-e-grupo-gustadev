<?php

namespace idoit\Component\Processor\Dto\ObjectType;

use idoit\Component\Processor\Validation\IsConstant;
use idoit\Component\Processor\Validation\IsValidConstantString;
use idoit\Component\Processor\Validation\IsValidHexColor;
use Idoit\Dto\Validation\IsNull;
use Idoit\Dto\Validation\OrX;
use Idoit\Dto\Validation\PositiveInteger;
use Idoit\Dto\Validation\Required;

/**
 * Create DTO for object type groups.
 */
class CreateRequest
{
    public function __construct(
        #[Required]
        public readonly string $title = '',
        #[Required]
        #[OrX(new PositiveInteger(), new IsConstant('C__OBJTYPE_GROUP__'))]
        public readonly int|string $objectTypeGroup = 0,
        #[OrX(new IsNull(), new IsValidConstantString('C__OBJTYPE__SD_'))]
        public readonly string|null $constant = null,
        public readonly string|null $description = null,
        public readonly string|null $image = null,
        public readonly string|null $icon = null,
        #[OrX(new IsNull(), new PositiveInteger(), new IsConstant('C__CATS__'))]
        public readonly string|int|null $specificCategory = null,
        public readonly bool $isContainer = false,
        public readonly bool $isPositionableInRack = false,
        public readonly bool $isVisible = true,
        public readonly bool $isRelationMaster = false,
        public readonly bool $hasOverviewPage = false,
        public readonly int $sort = C__PROPERTY__DEFAULT_SORT,
        #[IsValidHexColor]
        public readonly string $color = '#ffffff',
        public readonly int|null $defaultTemplate = null,
        public readonly string|null $sysidPrefix = null
    ) {
    }
}
