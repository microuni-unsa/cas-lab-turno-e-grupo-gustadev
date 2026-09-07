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
class GlobalCategories extends Categories
{
    /**
     * @return $this
     * @throws CategoryDataException
     */
    public function setData()
    {
        $blackListAsString = '';
        if (count($this->getBlacklist())) {
            $blackListIds = implode(',', $this->getBlacklist());
            $blackListAsString = "AND main.isysgui_catg__id NOT IN ({$blackListIds})";
        }
        $supportedCategoriesAsString = implode(',', $this->getSupportedCategoryTypes());
        $container = isys_application::instance()->container;
        $language = $container->get('language');
        $conditionFirstPart = $conditionSecondPart = [];

        try {
            $query = "SELECT *, (SELECT isysgui_catg__title FROM isysgui_catg WHERE isysgui_catg__id = main.isysgui_catg__parent) AS parent FROM isysgui_catg main
                INNER JOIN isys_property_2_cat propCat ON propCat.isys_property_2_cat__isysgui_catg__id = main.isysgui_catg__id
                WHERE main.isysgui_catg__type IN ({$supportedCategoriesAsString}) {$blackListAsString} AND
                !LOCATE('_ROOT', main.isysgui_catg__const) AND !LOCATE('_NAGIOS', main.isysgui_catg__const) %s
                UNION SELECT *, (SELECT isysgui_catg__title FROM isysgui_catg WHERE isysgui_catg__id = main.isysgui_catg__parent) AS parent FROM isysgui_catg main
                LEFT JOIN isys_property_2_cat propCat ON propCat.isys_property_2_cat__isysgui_catg__id = main.isysgui_catg__id
                WHERE main.isysgui_catg__parent IN (SELECT DISTINCT isysgui_catg__parent FROM isysgui_catg) {$blackListAsString} %s";

            $filter = $this->getFilter();

            if (!empty($filter->getObjects())) {
                $conditionFirstPart[] = ' main.isysgui_catg__id IN (
                    SELECT DISTINCT isys_obj_type_2_isysgui_catg__isysgui_catg__id FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id IN (
                        SELECT DISTINCT isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__id IN (' . implode(',', $filter->getObjects()) . ')
                    )
                )';
                $conditionSecondPart[] = ' main.isysgui_catg__parent IN (
                    SELECT DISTINCT isys_obj_type_2_isysgui_catg__isysgui_catg__id FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id
                    IN (
                        SELECT DISTINCT isys_obj__isys_obj_type__id FROM isys_obj WHERE isys_obj__id IN (' . implode(',', $filter->getObjects()) . ')
                    )
                )';
            }

            if (empty($filter->getObjects()) && !empty($filter->getObjectTypes())) {
                $objectTypes = $filter->getObjectTypes();
                $objectTypes = array_filter($objectTypes, 'defined');

                if (!empty($objectTypes)) {
                    $objectTypes = array_map('defined_or_default', $objectTypes);

                    $conditionFirstPart[] = ' main.isysgui_catg__id IN (
                        SELECT DISTINCT isys_obj_type_2_isysgui_catg__isysgui_catg__id FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id IN (
                            ' . implode(',', $objectTypes) . '
                        ))';
                    $conditionSecondPart[] = ' main.isysgui_catg__parent IN (
                        SELECT DISTINCT isys_obj_type_2_isysgui_catg__isysgui_catg__id FROM isys_obj_type_2_isysgui_catg WHERE isys_obj_type_2_isysgui_catg__isys_obj_type__id
                        IN (
                            ' . implode(',', $objectTypes) . '
                        ))';
                }
            }

            $categories = $filter->getCategories();

            if (!empty($categories) && !in_array('*', $categories)) {
                $categoryFilterCondition = ' main.isysgui_catg__const IN (\'' . implode('\',\'', $categories) . '\')';
                if (is_numeric($categories[0])) {
                    $categoryFilterCondition = ' main.isysgui_catg__id IN (' . implode(',', $categories) . ')';
                }
                $conditionFirstPart[] = $categoryFilterCondition;
                $conditionSecondPart[] = $categoryFilterCondition;
            }

            $result = $this->retrieve(
                sprintf(
                    $query,
                    (!empty($conditionFirstPart) ? ' AND ' . implode(' AND ', $conditionFirstPart) : ''),
                    (!empty($conditionSecondPart) ? ' AND ' . implode(' AND ', $conditionSecondPart) : '')
                )
            );
            while ($row = $result->get_row()) {
                // @see  ID-6622  This should prevent errors after categories have been removed.
                if (!class_exists($row['isysgui_catg__class_name'])) {
                    continue;
                }

                $categoryTitle = $language->get($row['isysgui_catg__title']);

                if (!empty($row['parent'])) {
                    $categoryTitle .= ' (' . $language->get('LC__MULTIEDIT__SUBCATEGORY_OF') . ' ' . $language->get($row['parent']) . ')';
                }

                $this->data[$this->getType() . '_' . $row['isysgui_catg__id'] . ':' . $row['isysgui_catg__class_name']] = $categoryTitle;
                $this->increment();

                $checkSql = 'SELECT isys_property_2_cat__prop_key FROM isys_property_2_cat
                    WHERE isys_property_2_cat__cat_const = ' . $this->convert_sql_text($row['isysgui_catg__const']) . '
                        AND isys_property_2_cat__prop_type = ' . $this->convert_sql_int(C__PROPERTY_TYPE__STATIC) . '
                        AND isys_property_2_cat__prop_key != ' . $this->convert_sql_text('description') . '
                        AND isys_property_2_cat__prop_provides & ' . $this->convert_sql_int(C__PROPERTY__PROVIDES__MULTIEDIT);

                $checkResult = $this->retrieve($checkSql);
                $numProperties = $checkResult->num_rows();

                if ($numProperties === 1) {
                    $propKey = $checkResult->get_row_value('isys_property_2_cat__prop_key');
                    $catDao = $row['isysgui_catg__class_name']::instance($container->get('database'));
                    $properties = $catDao->get_properties(C__PROPERTY__WITH__VALIDATION);
                    $property = $properties[$propKey];

                    if ($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === C__PROPERTY__INFO__TYPE__OBJECT_BROWSER ||
                        $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE] === C__PROPERTY__INFO__TYPE__N2M) {
                        continue;
                    }
                } elseif ($numProperties === 0) {
                    // Remove category from list, because there are no properties which are usable for the multiedit list
                    unset($this->data[$this->getType() . '_' . $row['isysgui_catg__id'] . ':' . $row['isysgui_catg__class_name']]);
                    $this->decrement();
                    continue;
                }

                if ($row['isysgui_catg__list_multi_value'] > 0) {
                    $this->addToMultivalueCategories($row['isysgui_catg__id']);
                }
            }

            return $this;
        } catch (\Exception $e) {
            throw new CategoryDataException('Collecting global categories failed in File : ' . $e->getFile() . ' on Line: ' . $e->getLine() . ' with Message: ' . $e->getMessage());
        }
    }

    public function __construct(isys_component_database $p_db)
    {
        $this->setType(C__CMDB__CATEGORY__TYPE_GLOBAL);

        $blacklist = filter_defined_constants([
            'C__CATG__CLUSTER_SERVICE',
            'C__CATG__ITS_LOGBOOK',
            'C__CATG__IT_SERVICE_RELATIONS',
            'C__CATG__CLUSTER_SHARED_STORAGE',
            'C__CATG__CLUSTER_SHARED_VIRTUAL_SWITCH ',
            'C__CATG__LOGBOOK',
            'C__CATG__RELATION',
            'C__CATG__SNMP',
            'C__CATG__SOA_STACKS',
            'C__CATG__VIRTUAL_TICKETS',
            'C__CATG__VIRTUAL_DEVICE',
            'C__CATG__VIRTUAL_SWITCH',
            'C__CATG__WORKFLOW',
            'C__CATG__CABLING',
            'C__CATG__DATABASE_ASSIGNMENT',
            'C__CATG__GUEST_SYSTEMS',
            'C__CATG__STORAGE',
            'C__CATG__SANPOOL',
            'C__CATG__NETWORK',
            'C__CATG__VOIP_PHONE_LINE',
            'C__CATG__BACKUP__ASSIGNED_OBJECTS',
//            'C__CATG__CONTRACT_ASSIGNMENT,
            'C__CATG__VIRTUAL_AUTH',
            'C__CATG__LDAP_DN',
            'C__CATG__CMK_TAG',
            'C__CATG__CMK',
            'C__CATG__CMK2_TAG',
        ]);

        $this->setBlacklist($blacklist);
        $this->setSupportedCategoryTypes([
            \isys_cmdb_dao_category::TYPE_EDIT,
            \isys_cmdb_dao_category::TYPE_FOLDER,
            \isys_cmdb_dao_category::TYPE_ASSIGN,
            \isys_cmdb_dao_category::TYPE_REAR
        ]);
        parent::__construct($p_db);
    }
}
