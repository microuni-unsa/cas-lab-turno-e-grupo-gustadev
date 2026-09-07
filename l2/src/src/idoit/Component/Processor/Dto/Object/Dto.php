<?php

namespace idoit\Component\Processor\Dto\Object;

use DateTime;
use idoit\Component\Processor\Dto\Shape\CmdbStatusShape;
use idoit\Component\Processor\Dto\Shape\DialogShape;
use idoit\Component\Processor\Dto\Shape\ObjectShape;
use idoit\Component\Processor\Dto\Shape\ObjectTypeShape;
use idoit\Component\Processor\Dto\Shape\StatusShape;
use idoit\Component\Processor\Serialization\CmdbStatusFormat;
use idoit\Component\Processor\Serialization\DialogPlusFormat;
use idoit\Component\Processor\Serialization\ObjectFormat;
use idoit\Component\Processor\Serialization\ObjectTypeFormat;
use idoit\Component\Processor\Serialization\StatusFormat;
use Idoit\Dto\Serialization\DateFormat;

/**
 * Object read DTO. Will be returned when reading objects via processor.
 */
class Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string|null $constant,
        public readonly string $description,
        public readonly string $imageUrl,
        #[DateFormat]
        public readonly DateTime $created,
        public readonly string $createdBy,
        #[DateFormat]
        public readonly DateTime $updated,
        public readonly string $updatedBy,
        #[StatusFormat]
        public readonly StatusShape $status,
        public readonly string $sysid,
        public readonly bool $undeletable,
        #[ObjectFormat]
        public readonly ObjectShape|null $ownerId,
        #[CmdbStatusFormat]
        public readonly CmdbStatusShape $cmdbStatus,
        #[DialogPlusFormat('isys_catg_global_category')]
        public readonly DialogShape|null $category,
        #[DialogPlusFormat('isys_purpose')]
        public readonly DialogShape|null $purpose,
        #[ObjectTypeFormat]
        public readonly ObjectTypeShape $objectType
    ) {
    }
}
