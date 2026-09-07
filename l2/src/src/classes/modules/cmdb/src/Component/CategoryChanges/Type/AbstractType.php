<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Type;

use idoit\Component\Property\Property;
use isys_cmdb_dao_category;

/**
 * Class AbstractType
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Type
 */
abstract class AbstractType
{
    /**
     * @var Property
     */
    protected $property;

    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var \isys_component_template_language_manager
     */
    protected $language;

    /**
     * AbstractType constructor.
     */
    public function __construct()
    {
        $this->language = \isys_application::instance()->container->get('language');
    }

    /**
     * @return \isys_component_database_proxy|mixed|object|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return Property
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @param Property $property
     */
    public function setProperty(Property $property)
    {
        $this->property = $property;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param int $objectId
     */
    public function setObjectId(int $objectId)
    {
        $this->objectId = $objectId;
    }

    /**
     * @param $tag
     * @param $fromValue
     * @param $toValue
     *
     * @return array
     */
    public function setChanges($tag, $fromValue, $toValue)
    {
        return [
            $tag =>
                [
                    TypeInterface::CHANGES_FROM => $fromValue,
                    TypeInterface::CHANGES_TO => $toValue
                ]
        ];
    }

    /**
     * @param isys_cmdb_dao_category $dao
     * @param string                 $tag
     *
     * @return string
     */
    public function getCurrentPropertyTag(isys_cmdb_dao_category $dao, string $tag)
    {
        $currentPropertyTag = get_class($dao) . '::' . $tag;

        if ($dao instanceof \isys_cmdb_dao_category_g_custom_fields) {
            $currentPropertyTag .= '::' . $dao->get_catg_custom_id();
        }

        return $currentPropertyTag;
    }
}
