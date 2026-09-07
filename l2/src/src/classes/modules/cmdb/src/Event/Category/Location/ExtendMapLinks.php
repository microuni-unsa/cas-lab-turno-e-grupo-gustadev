<?php

namespace idoit\Module\Cmdb\Event\Category\Location;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * i-doit event to extend location map links.
 *
 * @package   i-doit
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ExtendMapLinks extends GenericEvent
{
    const NAME = 'cmdb.category.location.extendMapLinks';

    private array $additionalLinks = [];

    private int $objectId;

    private float $latitude;

    private float $longitude;

    public function __construct(int $objectId, float $latitude, float $longitude)
    {
        $this->objectId = $objectId;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getObjectId(): int
    {
        return $this->objectId;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function getMapLinks(): array
    {
        return $this->additionalLinks;
    }

    public function addMapLink(string $label, string $icon, string $url): void
    {
        $this->additionalLinks[] = [
            'label' => $label,
            'icon' => $icon,
            'url' => $url
        ];
    }
}
