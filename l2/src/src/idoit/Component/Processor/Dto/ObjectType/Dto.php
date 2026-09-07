<?php

namespace idoit\Component\Processor\Dto\ObjectType;

use idoit\Component\Processor\Dto\Shape\DialogShape;
use idoit\Component\Processor\Dto\Shape\ObjectShape;
use idoit\Component\Processor\Dto\Shape\SpecificCategoryShape;
use idoit\Component\Processor\Dto\Shape\StatusShape;
use idoit\Component\Processor\Serialization\DialogPlusFormat;
use idoit\Component\Processor\Serialization\ObjectFormat;
use idoit\Component\Processor\Serialization\SpecificCategoryFormat;
use idoit\Component\Processor\Serialization\StatusFormat;
use Idoit\Dto\Serialization\SerializableTrait;

/**
 * Object type DTO.
 */
class Dto
{
    use SerializableTrait;

    public function __construct(
        public readonly int $id,
        #[DialogPlusFormat('isys_obj_type_group')]
        public readonly DialogShape|null $objectTypeGroup,
        public readonly string $title,
        public readonly string $titleRaw,
        public readonly string $constant,
        public readonly string|null $description,
        public readonly string $image,
        public readonly string $icon,
        #[SpecificCategoryFormat]
        public readonly SpecificCategoryShape|null $specificCategory,
        public readonly bool $isSelfDefined,
        public readonly bool $isContainer,
        public readonly bool $isPositionableInRack,
        public readonly bool $isVisible,
        public readonly bool $isRelationMaster,
        public readonly bool $hasOverviewPage,
        public readonly int $sort,
        public readonly string $color,
        #[ObjectFormat]
        public readonly ObjectShape|null $defaultTemplate,
        #[StatusFormat]
        public readonly StatusShape $status,
        public readonly string|null $sysidPrefix,
        public readonly array $assignedCategories,
        public readonly array $categoriesOnOverviewPage,
    ) {
    }
}
