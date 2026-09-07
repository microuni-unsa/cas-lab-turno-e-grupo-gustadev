<?php
namespace idoit\Module\Multiedit\Model;

use idoit\Module\Multiedit\Component\Multiedit\Exception\CategoryDataException;
use isys_application;
use isys_component_database;

/**
 * @package     Modules
 * @subpackage  multiedit
 * @author      Van Quyen Hoang <qhoang@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class CustomCategories extends Categories
{
    /**
     * @return $this
     * @throws CategoryDataException
     */
    public function setData()
    {
        try {
            $container = isys_application::instance()->container;
            $language = $container->get('language');
            $condition = [];

            $query = "SELECT *,
            (
                SELECT GROUP_CONCAT(isys_obj_type__title SEPARATOR ', ') FROM isys_obj_type
                INNER JOIN `isys_obj_type_2_isysgui_catg_custom` ON `isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id` = isys_obj_type__id
                WHERE isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id = isysgui_catg_custom__id
                AND isys_obj_type__const != 'C__OBJTYPE__GENERIC_TEMPLATE'
            ) AS objTypes
            FROM isysgui_catg_custom
            WHERE TRUE";

            $filter = $this->getFilter();

            if (!empty($filter->getObjects())) {
                $condition[] = ' isysgui_catg_custom__id IN (
                    SELECT DISTINCT isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id FROM isys_obj_type_2_isysgui_catg_custom WHERE isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id IN (
                        SELECT DISTINCT isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__id IN (' . implode(',', $filter->getObjects()) . ')
                    ))';
            }

            if (empty($filter->getObjects()) && !empty($filter->getObjectTypes())) {
                $objectTypes = $filter->getObjectTypes();
                $objectTypes = array_filter($objectTypes, 'defined');

                if (!empty($objectTypes)) {
                    $objectTypes = array_map('defined_or_default', $objectTypes);

                    $condition[] = ' isysgui_catg_custom__id IN (
                        SELECT DISTINCT isys_obj_type_2_isysgui_catg_custom__isysgui_catg_custom__id FROM isys_obj_type_2_isysgui_catg_custom WHERE isys_obj_type_2_isysgui_catg_custom__isys_obj_type__id IN (
                            ' . implode(', ', $objectTypes) . '
                        ))';
                }
            }

            $categories = $filter->getCategories();

            if (!empty($categories) && !in_array('*', $categories)) {
                $categoryCondition = ' isysgui_catg_custom__const IN (\'' . implode('\',\'', $categories) . '\')';
                if (is_numeric($categories[0])) {
                    $categoryCondition = ' isysgui_catg_custom__id IN (' . implode(',', $categories) . ')';
                }
                $condition[] = $categoryCondition;
            }

            $result = $this->retrieve($query . (!empty($condition) ? ' AND ' . implode(' AND ', $condition) : ''));

            while ($row = $result->get_row()) {
                $objectTypes = $language->get_in_text($row['objTypes']);
                $categoryTitle = isys_glob_htmlentities($language->get($row['isysgui_catg_custom__title']));

                if ($objectTypes) {
                    $categoryTitle .= ' (' . $objectTypes . ')';
                }

                $this->data[$this->getType() . '_' . $row['isysgui_catg_custom__id'] . ':' . $row['isysgui_catg_custom__class_name']] = $categoryTitle;
                $this->increment();
                if ($row['isysgui_catg_custom__list_multi_value'] > 0) {
                    $this->addToMultivalueCategories($row['isysgui_catg_custom__id']);
                }
            }
        } catch (\Exception $e) {
            throw new CategoryDataException('Collecting custom categories failed in File : ' . $e->getFile() . ' on Line: ' . $e->getLine() . ' with Message: ' . $e->getMessage());
        }

        return $this;
    }

    public function __construct(isys_component_database $p_db)
    {
        $this->setType(C__CMDB__CATEGORY__TYPE_CUSTOM);
        $this->setSupportedCategoryTypes([
            \isys_cmdb_dao_category::TYPE_EDIT,
            \isys_cmdb_dao_category::TYPE_ASSIGN,
        ]);
        parent::__construct($p_db);
    }
}
