<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type\Browser;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\Type\TypeInterface;
use isys_application;
use isys_cmdb_dao;
use isys_popup_browser_location;

/**
 * Class LocationBrowserType
 *
 * Special type for location browser
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
class LocationBrowserType extends ObjectBrowserType implements TypeInterface, ObjectBrowserTypeInterface
{
    /**
     * @param Property    $property
     * @param string      $tag
     * @param string|null $class
     *
     * @return bool
     */
    public function isApplicable(Property $property, string $tag, ?string $class)
    {
        return $property->getInfo()->getType() === Property::C__PROPERTY__INFO__TYPE__OBJECT_BROWSER &&
            $property->getUi()->getParams()['p_strPopupType'] === 'browser_location';
    }

    /**
     * @param int           $objectId
     * @param isys_cmdb_dao $dao
     *
     * @return array
     * @throws \isys_exception_database
     */
    public function getObjectTitleAsArray(int $objectId, isys_cmdb_dao $dao)
    {
        $db = isys_application::instance()->container->get('database');

        return [
            self::OBJECT_TITLE_WITH_ADDITION => isys_popup_browser_location::instance($db)
                ->set_format_as_text(true)
                ->format_selection($objectId, true),
            self::OBJECT_TITLE_WITHOUT_ADDITION => isys_cmdb_dao::instance($db)->get_obj_name_by_id_as_string($objectId)
        ];
    }
}
