<?php declare(strict_types = 1);

namespace Idoit\Dto\Serialization;

use DateTime;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class DateFormat extends Format
{
    public function toJson(mixed $object): string|null
    {
        if ($object instanceof \DateTimeInterface) {
            return $object->format(DateTime::ATOM);
        }
        return null;
    }

    public function fromJson(mixed $string): mixed
    {
        if (!is_string($string)) {
            return null;
        }
        return DateTime::createFromFormat(DateTime::ATOM, $string);
    }
}
