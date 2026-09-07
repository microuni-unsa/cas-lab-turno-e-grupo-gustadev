<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\AbstractType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\CableConnectionBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\ConnectorBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\ContactBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\LocationBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\LogicalPortBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\MultiContactBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\MultiObjectBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\MultiSecondSelectObjectBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\ObjectBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser\SecondSelectObjectBrowserType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\CommentaryType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\DateTimeperiodType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\DateTimeType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\DateType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\DescriptionType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\DialogDataType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\DialogListType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\DialogPlusType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\DialogType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\MaskedTextType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\MoneyType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\MultiselectType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TextType;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TimeType;

/**
 * Class TypeProvider
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges
 */
class TypeProvider
{
    /**
     * @var AbstractType[]
     */
    private $types;

    /**
     * @param Property $property
     * @param string   $tag
     *
     * @return AbstractType|null
     */
    public function getType(Property $property, string $tag, string $class)
    {
        if ($property->getProvides()->isVirtual()) {
            return null;
        }

        foreach ($this->types as $object) {
            if ($object->isApplicable($property, $tag, $class)) {
                $object->setProperty($property);

                return clone $object;
            }
        }
        return null;
    }

    /**
     * Loading all available types
     *
     * @return void
     */
    private function loadTypes()
    {
        $this->types = [
            \isys_application::instance()->container->get('idoit.cmdb.category-changes.type.property-provider'),
            new MultiContactBrowserType(),
            new MultiSecondSelectObjectBrowserType(),
            new MultiObjectBrowserType(),
            new LocationBrowserType(),
            new ConnectorBrowserType(),
            new LogicalPortBrowserType(),
            new CableConnectionBrowserType(),
            new SecondSelectObjectBrowserType(),
            new ObjectBrowserType(),
            new ContactBrowserType(),
            new DateType(),
            new DateTimeType(),
            new DateTimePeriodType(),
            new TimeType(),
            new DialogPlusType(),
            new DialogDataType(),
            new DialogType(),
            new DialogListType(),
            new MultiselectType(),
            new DescriptionType(),
            new CommentaryType(),
            new MoneyType(),
            new MaskedTextType(),
            new TextType(),
        ];
    }

    /**
     * TypeProvider constructor.
     */
    public function __construct()
    {
        $this->loadTypes();
    }
}
