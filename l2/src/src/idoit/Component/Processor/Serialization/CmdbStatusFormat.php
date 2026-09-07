<?php declare(strict_types=1);

namespace idoit\Component\Processor\Serialization;

use idoit\Component\Processor\Dto\Shape\CmdbStatusShape;
use Idoit\Dto\Serialization\Format;
use Idoit\Dto\Serialization\Serializer;
use idoit\Exception\Exception;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class CmdbStatusFormat extends Format
{
    public function toJson(mixed $object): array|null
    {
        if ($object instanceof CmdbStatusShape) {
            return $object->jsonSerialize();
        }

        return null;
    }

    public function fromJson(mixed $id): CmdbStatusShape|null
    {
        if (!is_int($id)) {
            return null;
        }

        $language = \isys_application::instance()->container->get('language');
        $dao = \isys_application::instance()->container->get('cmdb_dao');

        $query = "SELECT isys_cmdb_status__id AS id,
            isys_cmdb_status__title AS titleRaw,
            isys_cmdb_status__const AS constant,
            isys_cmdb_status__color AS color
            FROM isys_cmdb_status
            WHERE isys_cmdb_status__id = {$id}
            LIMIT 1;";

        $result = $dao->retrieve($query)->get_row();

        if (empty($result)) {
            return throw new Exception("Given CMDB status '{$id}' is unknown!");
        }

        $result['title'] = $language->get($result['titleRaw']);

        return Serializer::fromJson(CmdbStatusShape::class, $result);
    }
}
