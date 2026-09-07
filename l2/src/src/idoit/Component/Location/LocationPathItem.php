<?php

namespace idoit\Component\Location;

class LocationPathItem
{
    private const HELLIP = '…'; // Use UTF8 character instead of HTML "&hellip;".

    private ?int $objectId;
    private string $label;

    public function __construct(?int $objectId, string $label)
    {
        $this->objectId = $objectId;
        $this->label = $label;
    }

    public function getObjectId(): ?int
    {
        return $this->objectId;
    }

    public function getLabel(?int $maxTitleLength = null): string
    {
        if ($maxTitleLength !== null && mb_strlen($this->label) > $maxTitleLength) {
            return mb_substr($this->label, 0, $maxTitleLength) . self::HELLIP;
        }

        return $this->label;
    }
}
