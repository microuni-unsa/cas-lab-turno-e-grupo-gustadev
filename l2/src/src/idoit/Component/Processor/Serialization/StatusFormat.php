<?php declare(strict_types=1);

namespace idoit\Component\Processor\Serialization;

use idoit\Component\Processor\Dto\Shape\StatusShape;
use Idoit\Dto\Serialization\Format;
use Idoit\Dto\Serialization\Serializer;
use idoit\Exception\Exception;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class StatusFormat extends Format
{
    public function toJson(mixed $object): array|null
    {
        if ($object instanceof StatusShape) {
            return $object->jsonSerialize();
        }

        return null;
    }

    /**
     * @param mixed $id
     *
     * @return StatusShape|null
     * @throws Exception
     */
    public function fromJson(mixed $id): StatusShape|null
    {
        if (!is_int($id)) {
            return null;
        }

        $data = match ($id) {
            C__RECORD_STATUS__BIRTH => [
                'titleRaw' => 'LC__CMDB__RECORD_STATUS__BIRTH',
                'constant' => 'C__RECORD_STATUS__BIRTH'
            ],
            C__RECORD_STATUS__NORMAL => [
                'titleRaw' => 'LC__CMDB__RECORD_STATUS__NORMAL',
                'constant' => 'C__RECORD_STATUS__NORMAL'
            ],
            C__RECORD_STATUS__ARCHIVED => [
                'titleRaw' => 'LC__CMDB__RECORD_STATUS__ARCHIVED',
                'constant' => 'C__RECORD_STATUS__ARCHIVED'
            ],
            C__RECORD_STATUS__DELETED => [
                'titleRaw' => 'LC__CMDB__RECORD_STATUS__DELETED',
                'constant' => 'C__RECORD_STATUS__DELETED'
            ],
            C__RECORD_STATUS__TEMPLATE => [
                'titleRaw' => 'LC__CMDB__RECORD_STATUS__TEMPLATE',
                'constant' => 'C__RECORD_STATUS__TEMPLATE'
            ],
            C__RECORD_STATUS__MASS_CHANGES_TEMPLATE => [
                'titleRaw' => 'LC__MASS_CHANGE__CHANGE_TEMPLATE',
                'constant' => 'C__RECORD_STATUS__MASS_CHANGES_TEMPLATE'
            ],
            default => throw new Exception("Given status '{$id}' is unknown!")
        };

        return Serializer::fromJson(StatusShape::class, [
            'id'       => $id,
            'titleRaw' => $data['titleRaw'],
            'title'    => \isys_application::instance()->container->get('language')->get($data['titleRaw']),
            'constant' => $data['constant'],
        ]);
    }
}
