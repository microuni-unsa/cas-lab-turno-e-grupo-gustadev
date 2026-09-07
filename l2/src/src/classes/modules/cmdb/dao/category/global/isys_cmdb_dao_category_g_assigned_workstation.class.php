<?php

use idoit\Module\Cmdb\Interfaces\CollectionInterface;
use idoit\Module\Cmdb\Interfaces\ObjectBrowserAssignedEntries;
use idoit\Module\Cmdb\Model\Entry\ObjectCollection;
use idoit\Module\Cmdb\Model\Entry\ObjectEntry;
use idoit\Module\Report\SqlQuery\Structure\SelectSubSelect;

/**
 * i-doit
 *
 * DAO: logical unit extension: assigned workstation.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_assigned_workstation extends isys_cmdb_dao_category_g_logical_unit implements ObjectBrowserAssignedEntries
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'assigned_workstation';
        $this->m_category_const = 'C__CATG__ASSIGNED_WORKSTATION';
        $this->m_ui = 'isys_cmdb_ui_category_g_assigned_workstation';
        $this->m_table = 'isys_catg_logical_unit_list';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATG__ASSIGNED_WORKSTATION';
    }

    /**
     * Method for returning the properties. Unused because reverse category.
     * Why is it unused? We need the properties to use all necessary generic functions,
     * otherwise all important functions must be defined in this class.
     *
     * @return  array
     */
    protected function properties()
    {
        $l_properties = parent::properties();

        /** @var SelectSubSelect $selectSubSelect */
        $selectSubSelect = $l_properties['parent'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT];
        $l_selectCondition = $selectSubSelect->setSelectLimit(1)->getSelectCondition();
        $l_selectCondition->setCondition([
            'isys_obj__isys_obj_type__id IN (
                SELECT isys_obj_type_2_isysgui_catg__isys_obj_type__id FROM isys_obj_type_2_isysgui_catg
                INNER JOIN isysgui_catg ON isysgui_catg__id = isys_obj_type_2_isysgui_catg__isysgui_catg__id
                WHERE isysgui_catg__const = \'C__CATG__ASSIGNED_LOGICAL_UNIT\'
            )'
        ]);
        $l_properties['parent'][C__PROPERTY__DATA][C__PROPERTY__DATA__SELECT] = $selectSubSelect->setSelectCondition($l_selectCondition);
        $parentUiParams = $l_properties['parent'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS];
        $parentUiParams[isys_popup_browser_object_ng::C__CAT_FILTER] = 'C__CATG__ASSIGNED_LOGICAL_UNIT';
        $parentUiParams[isys_popup_browser_object_ng::C__DATARETRIEVAL] = new isys_callback([
            'isys_cmdb_dao_category_g_assigned_workstation',
            'getEntriesByRequestObject'
        ]);
        $l_properties['parent'][C__PROPERTY__UI][C__PROPERTY__UI__PARAMS] = $parentUiParams;
        $l_properties['parent'][C__PROPERTY__INFO][C__PROPERTY__INFO__BACKWARD_PROPERTY] = 'isys_cmdb_dao_category_g_assigned_logical_unit::assigned_object';
        $l_properties['description'][C__PROPERTY__UI][C__PROPERTY__UI__ID] = 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__ASSIGNED_WORKSTATION', 'C__CATG__ASSIGNED_WORKSTATION');

        return $l_properties;
    }

    /**
     * @param int|int[] $ids
     * @param string    $tag
     * @param false     $asId
     *
     * @return CollectionInterface
     * @throws isys_exception_database
     */
    public function getAttachedEntries($ids, $tag = '', $asId = false): CollectionInterface
    {
        $collection = new ObjectCollection();

        if (is_numeric($ids)) {
            $ids = [$ids];
        }

        if (empty($ids)) {
            return $collection;
        }

        $query = 'SELECT isys_catg_logical_unit_list__id as id, isys_obj__id as objId, isys_obj__title as title, isys_obj__sysid as sysid, isys_obj_type__title as objType
            FROM isys_catg_logical_unit_list
            INNER JOIN isys_obj ON isys_obj__id = isys_catg_logical_unit_list__isys_obj__id__parent
            INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
            WHERE isys_catg_logical_unit_list__isys_obj__id ' . $this->prepare_in_condition($ids). ';';

        $result = $this->retrieve($query);
        $language = isys_application::instance()->container->get('language');

        while ($row = $result->get_row()) {
            $collection->addEntry(ObjectEntry::factory(
                $row['objId'],
                $row['title'],
                $row['sysid'],
                $language->get($row['objType']),
                $row
            ));
        }

        return $collection;
    }
}
