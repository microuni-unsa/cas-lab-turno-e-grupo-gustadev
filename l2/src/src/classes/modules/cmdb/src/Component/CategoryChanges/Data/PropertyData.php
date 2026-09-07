<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Data;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\CategoryChanges\TypeProvider;
use isys_cmdb_dao_category_g_custom_fields;

/**
 * Class PropertyData
 *
 * Register for each property the corresponding Type
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Data
 */
class PropertyData extends AbstractData
{
    const PROPERTY_TYPE_DATA = 'propertyData';
    const PROPERTY_TYPE      = 'propertyType';
    const LOGBOOK_TAG = 'logbookTag';

    const UNSUPPORTED_UI_TYPES = [
        Property::C__PROPERTY__UI__TYPE__HTML,
        Property::C__PROPERTY__UI__TYPE__SCRIPT,
        Property::C__PROPERTY__UI__TYPE__HR
    ];

    /**
     * @var \isys_cmdb_dao_category
     */
    private $dao;

    /**
     * @return \isys_cmdb_dao_category
     */
    public function getDao()
    {
        return $this->dao;
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     */
    public function setDao(\isys_cmdb_dao_category $dao)
    {
        $this->dao = $dao;
    }

    /**
     * @return $this
     */
    protected function mapProperties()
    {
        $properties = $this->dao->get_properties();
        $categoryClass = get_class($this->dao);
        $customCategory = $this->dao instanceof isys_cmdb_dao_category_g_custom_fields ?: false;
        $typeProvider = new TypeProvider();
        $propertyTypeProvider = \isys_application::instance()->container->get('idoit.cmdb.category-changes.type.property-provider');

        foreach ($properties as $tag => $propertyData) {
            $propertyData = (is_array($propertyData) ? Property::createInstanceFromArray($propertyData): $propertyData);

            if ($customCategory === true) {
                // @see  ID-8266  Only replace the UI ID if it's not a description field.
                if ($tag !== 'description') {
                    $propertyData->mergePropertyUi([
                        C__PROPERTY__UI__ID => 'C__CATG__CUSTOM__' . substr($tag, strpos($tag, '_c_') + 1, strlen($tag))
                    ]);
                }
            } elseif ($tag === 'description' && !empty($postData[$propertyData->getUi()->getId()])) {
                $propertyData->mergePropertyUi([
                    C__PROPERTY__UI__DEFAULT => '<br>'
                ]);
            }

            if (!$propertyTypeProvider->isApplicable($propertyData, $tag, get_class($this->dao)) &&
                (empty($propertyData->getUi()->getId()) ||
                in_array($propertyData->getUi()->getType(), self::UNSUPPORTED_UI_TYPES))
            ) {
                continue;
            }

            $categoryIdentifier = $categoryClass . ($customCategory === true ? '::' . $this->dao->get_catg_custom_id() : '');
            $logbookTag = $categoryClass . '::' . $tag . ($customCategory === true ? '::' . $this->dao->get_catg_custom_id() : '');

            $propertyType = $typeProvider->getType(
                $propertyData,
                $tag,
                $categoryClass
            );

            if ($propertyType !== null) {
                $this->data[$categoryIdentifier][$tag] = [
                    self::LOGBOOK_TAG => $logbookTag,
                    self::PROPERTY_TYPE_DATA => $propertyType
                ];
            }
        }

        return $this;
    }

    /**
     * @param \isys_cmdb_dao_category $dao
     *
     * @return PropertyData|static
     */
    public static function factory(\isys_cmdb_dao_category $dao)
    {
        $object = new static();
        $object->setDao($dao);
        $object->mapProperties();

        return $object;
    }
}
