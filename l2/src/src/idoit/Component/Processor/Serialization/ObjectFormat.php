<?php declare(strict_types=1);

namespace idoit\Component\Processor\Serialization;

use idoit\Component\Processor\Dto\Shape\ObjectShape;
use Idoit\Dto\Serialization\Format;
use Idoit\Dto\Serialization\Serializer;
use idoit\Exception\Exception;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ObjectFormat extends Format
{
    public function toJson(mixed $object): array|null
    {
        if ($object instanceof ObjectShape) {
            return $object->jsonSerialize();
        }

        return null;
    }

    public function fromJson(mixed $id): ObjectShape|null
    {
        if (!is_int($id)) {
            return null;
        }

        $language = \isys_application::instance()->container->get('language');
        $dao = \isys_application::instance()->container->get('cmdb_dao');

        $query = "SELECT isys_obj__id AS id,
            isys_obj__title AS titleRaw,
            isys_obj__isys_cmdb_status__id AS cmdbStatus,
            isys_obj__isys_obj_type__id AS objectType,
            isys_obj__status AS status
            FROM isys_obj
            WHERE isys_obj__id = {$id}
            LIMIT 1;";

        $result = $dao->retrieve($query)->get_row();

        if (empty($result)) {
            return throw new Exception("Given object '{$id}' is unknown!");
        }

        $result['title'] = $language->get($result['titleRaw']);
        $result['cmdbStatus'] = (int)$result['cmdbStatus'];
        $result['objectType'] = (int)$result['objectType'];
        $result['status'] = (int)$result['status'];

        return Serializer::fromJson(ObjectShape::class, $result);
    }
}
