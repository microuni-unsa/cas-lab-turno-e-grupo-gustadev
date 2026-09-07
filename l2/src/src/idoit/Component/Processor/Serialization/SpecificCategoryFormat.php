<?php declare(strict_types=1);

namespace idoit\Component\Processor\Serialization;

use idoit\Component\Processor\Dto\Shape\SpecificCategoryShape;
use Idoit\Dto\Serialization\Format;
use Idoit\Dto\Serialization\Serializer;
use idoit\Exception\Exception;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class SpecificCategoryFormat extends Format
{
    public function toJson(mixed $object): array|null
    {
        if ($object instanceof SpecificCategoryShape) {
            return $object->jsonSerialize();
        }

        return null;
    }

    public function fromJson(mixed $id): SpecificCategoryShape|null
    {
        if (!is_int($id)) {
            return null;
        }

        $language = \isys_application::instance()->container->get('language');
        $dao = \isys_application::instance()->container->get('cmdb_dao');

        $query = "SELECT isysgui_cats__id AS id,
            isysgui_cats__title AS titleRaw,
            isysgui_cats__const AS constant
            FROM isysgui_cats
            WHERE isysgui_cats__id = {$id}
            LIMIT 1;";

        $result = $dao->retrieve($query)->get_row();

        if (empty($result)) {
            return throw new Exception("Given specific category '{$id}' is unknown!");
        }

        $result['title'] = $language->get($result['titleRaw']);

        return Serializer::fromJson(SpecificCategoryShape::class, $result);
    }
}
