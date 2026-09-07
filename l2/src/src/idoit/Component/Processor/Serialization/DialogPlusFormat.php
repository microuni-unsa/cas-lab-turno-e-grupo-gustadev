<?php declare(strict_types=1);

namespace idoit\Component\Processor\Serialization;

use idoit\Component\Processor\Dto\Shape\DialogShape;
use Idoit\Dto\Serialization\Format;
use Idoit\Dto\Serialization\Serializer;
use idoit\Exception\Exception;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DialogPlusFormat extends Format
{
    public function __construct(private string $table)
    {
    }

    public function toJson(mixed $object): array|null
    {
        if ($object instanceof DialogShape) {
            return $object->jsonSerialize();
        }

        return null;
    }

    public function fromJson(mixed $id): DialogShape|null
    {
        if (!is_int($id)) {
            return null;
        }

        $language = \isys_application::instance()->container->get('language');
        $dao = \isys_application::instance()->container->get('cmdb_dao');

        $query = "SELECT {$this->table}__id AS id,
            {$this->table}__title AS titleRaw,
            {$this->table}__const AS constant
            FROM {$this->table}
            WHERE {$this->table}__id = {$id}
            LIMIT 1;";

        $result = $dao->retrieve($query)->get_row();

        if (empty($result)) {
            return throw new Exception("Given entry '{$id}' in dialog table '{$this->table}' is unknown!");
        }

        $result['title'] = $language->get($result['titleRaw']);

        return Serializer::fromJson(DialogShape::class, $result);
    }
}
