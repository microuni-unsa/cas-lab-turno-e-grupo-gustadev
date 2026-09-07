<?php declare(strict_types=1);

namespace idoit\Component\Processor\Serialization;

use idoit\Component\Processor\Dto\Shape\ObjectTypeShape;
use Idoit\Dto\Serialization\Format;
use Idoit\Dto\Serialization\Serializer;
use idoit\Exception\Exception;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ObjectTypeFormat extends Format
{
    public function toJson(mixed $object): array|null
    {
        if ($object instanceof ObjectTypeShape) {
            return $object->jsonSerialize();
        }

        return null;
    }

    public function fromJson(mixed $id): ObjectTypeShape|null
    {
        if (!is_int($id)) {
            return null;
        }

        $language = \isys_application::instance()->container->get('language');
        $dao = \isys_application::instance()->container->get('cmdb_dao');

        $query = "SELECT isys_obj_type__id AS id,
            isys_obj_type__title AS titleRaw,
            isys_obj_type__const AS constant,
            isys_obj_type__color AS color
            FROM isys_obj_type
            WHERE isys_obj_type__id = {$id}
            LIMIT 1;";

        $result = $dao->retrieve($query)->get_row();

        if (empty($result)) {
            return throw new Exception("Given object type '{$id}' is unknown!");
        }

        $result['title'] = $language->get($result['titleRaw']);

        return Serializer::fromJson(ObjectTypeShape::class, $result);
    }
}
