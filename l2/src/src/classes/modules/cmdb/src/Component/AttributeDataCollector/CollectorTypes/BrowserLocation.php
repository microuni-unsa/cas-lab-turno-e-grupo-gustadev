<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Controller\BrowseLocation;
use idoit\Module\Cmdb\Model\Tree;

class BrowserLocation extends AbstractCollector
{
    /**
     * @var string|null
     */
    private ?string $id = null;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return $this
     */
    public function setId(?string $id): BrowserLocation
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $property->getUi()->getParams()['p_strPopupType'] === 'browser_location';
    }

    /**
     * @param Property $property
     * @param bool     $reformat
     *
     * @return array
     * @throws \isys_exception_database
     */
    protected function fetchData(Property $property, bool $reformat): array
    {
        $params = $property->getUi()->getParams();
        $dbField = $property->getData()->getField();
        $mode = Tree::MODE_PHSYICAL;

        if (strpos($dbField, 'location') === false) {
            $mode = Tree::MODE_LOGICAL;
        }
        $browser = new BrowseLocation(new \isys_module_cmdb());

        $register = new \isys_register();
        $register
            ->set('id', $this->getId() ?? C__OBJ__ROOT_LOCATION)
            ->set('POST', [
                'mode' => $mode,
                'onlyContainer' => $params['containers_only'],
                'considerRights' => !!($params['consider_rights'] ?? false)
            ]);

        $browser->getObjectData($register);
        return $browser->getResponse();
    }
}
