<?php

namespace idoit\Component\Browser;

use idoit\Module\Cmdb\Component\Browser\Filter\AuthFilter;
use idoit\Module\Cmdb\Model\Ci\Category\G\Location\LocationPath;
use isys_application as App;
use isys_cmdb_dao_category as daoCategory;
use isys_cmdb_dao_category_g_custom_fields;
use isys_cmdb_dao_category_g_custom_fields as daoCategoryCustom;
use isys_cmdb_dao_category_property_ng as daoProperty;
use isys_tenantsettings;

class Retriever
{
    /**
     * @var ConditionInterface
     */
    private $condition;

    /**
     * @var boolean
     */
    private $showCategoryNames = false;

    /**
     * @var integer
     */
    private $contextObjectId;

    /**
     * @var array
     */
    private $attributes = [
        'C__CATG__GLOBAL::title',
        'C__CATG__GLOBAL::type',
        'C__CATG__GLOBAL::sysid'
    ];

    /**
     * @param ConditionInterface $condition
     *
     * @return $this
     */
    public function setCondition(ConditionInterface $condition): static
    {
        $this->condition = $condition;

        return $this;
    }

    /**
     * @param array $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @param integer $contextObjectId
     *
     * @return $this
     */
    public function setContextObjectId($contextObjectId): static
    {
        $this->contextObjectId = (int)$contextObjectId;

        return $this;
    }

    /**
     * @param  boolean $showCategoryNames
     *
     * @return Retriever
     */
    public function showCategoryNames($showCategoryNames): Retriever
    {
        $this->showCategoryNames = (bool)$showCategoryNames;

        return $this;
    }

    /**
     * Retrieve the attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return array
     */
    public function getOverviewData(): array
    {
        if ($this->contextObjectId !== null) {
            $this->condition->setContextObjectId($this->contextObjectId);
        }

        // @see  ID-#  Apply the AuthFilter to only display allowed objects.
        if (isys_tenantsettings::get('auth.use-in-object-browser', false)) {
            $this->condition->registerFilter(new AuthFilter(App::instance()->container->get('database')));
        }

        return $this->condition->retrieveOverview();
    }

    /**
     * @return array
     */
    public function getObjects(): array
    {
        if ($this->contextObjectId !== null) {
            $this->condition->setContextObjectId($this->contextObjectId);
        }

        // @see  ID-#  Apply the AuthFilter to only display allowed objects.
        if (isys_tenantsettings::get('auth.use-in-object-browser', false)) {
            $this->condition->registerFilter(new AuthFilter(App::instance()->container->get('database')));
        }

        // Retrieve objects by condition and process the visitors.
        return $this->condition->processVisitors($this->condition->retrieveObjects());
    }

    /**
     * Method to return formatted object data by defined attributes and condition.
     *
     * @param bool  $returnAsArray
     * @param array $objectIds
     *
     * @return \isys_component_dao_result|array
     * @throws \isys_exception_database
     * @throws \idoit\Exception\JsonException
     */
    public function getObjectData($returnAsArray = true, array $objectIds = []): \isys_component_dao_result|array
    {
        $db = App::instance()->container->get('database');
        $lang = App::instance()->container->get('language');

        $propertyDao = new daoProperty($db);
        $attributes = [];

        foreach ($this->attributes as $attribute) {
            [$categoryConstant, $propertyTitle] = explode('::', $attribute);

            $sql = 'SELECT isys_property_2_cat__id
                FROM isys_property_2_cat
                WHERE isys_property_2_cat__cat_const = ' . $propertyDao->convert_sql_text($categoryConstant) . '
                AND isys_property_2_cat__prop_key = ' . $propertyDao->convert_sql_text($propertyTitle) . '
                LIMIT 1;';

            $propertyId = $propertyDao->retrieve($sql)
                ->get_row_value('isys_property_2_cat__id');

            if (is_numeric($propertyId)) {
                $attributes[] = (int)$propertyId;
            }
        }

        if (count($objectIds)) {
            $objects = $objectIds;
        } else {
            $objects = $this->getObjects();
        }

        $sql = $propertyDao->create_property_query_for_lists($attributes, null, $objects, [], true, true);

        if ($this->condition->retainObjectOrder() && count($objects)) {
            $sql .= ' ORDER BY FIELD(obj_main.isys_obj__id, ' . implode(',', $objects) . ');';
        }

        if (!$returnAsArray) {
            return $propertyDao->retrieve($sql);
        }

        $return = ['data' => []];
        $result = $propertyDao->retrieve($sql);

        while ($row = $result->get_row()) {
            if (!isset($return['header'])) {
                $return['header'] = array_map(function ($value) use ($db, $lang, $propertyDao) {
                    if ($value === '__id__') {
                        return '__checkbox__';
                    }

                    [$daoClass, $attributeKey] = explode('__', $value);

                    if (class_exists($daoClass) && is_a($daoClass, daoCategory::class, true)) {
                        $daoInstance = $daoClass::instance($db);

                        if ($daoInstance instanceof daoCategoryCustom) {
                            // @see ID-8404 'shorter than three' because the key would at least need to look like this 'c_1'.
                            // @see ID-9938 Re-apply a fix to get the correct type and key.
                            [, $fieldKey] = isys_cmdb_dao_category_g_custom_fields::getTypeAndKey($attributeKey);

                            if ($fieldKey) {
                                $sql = 'SELECT isys_catg_custom_fields_list__isysgui_catg_custom__id AS customCategoryId
                                    FROM isys_catg_custom_fields_list
                                    WHERE isys_catg_custom_fields_list__field_key = ' . $propertyDao->convert_sql_text($fieldKey);

                                $daoInstance->set_catg_custom_id($propertyDao->retrieve($sql)
                                    ->get_row_value('customCategoryId'));
                            }
                        }

                        $property = $daoInstance->get_property_by_key($attributeKey);

                        // @see  ID-6426  Decode the HTML "&raquo;" (because I don't want to use UTF8 characters in code).
                        return ($this->showCategoryNames ? $lang->get($daoInstance->getCategoryTitle()) . isys_glob_html_entity_decode(' &raquo; ') : '') .
                            $lang->get($property[C__PROPERTY__INFO][C__PROPERTY__INFO__TITLE]);
                    }

                    // @todo  This should never happen - throw an exception?
                    return $value;
                }, array_keys($row));
            }

            $return['data'][] = array_map(function ($key, $value) use ($lang) {
                // Check whether value is null and convert it to an empty string instead
                if ($value === null) {
                    return '';
                }

                // @see ID-11174 Use specific callback for location path.
                if ($key === 'isys_cmdb_dao_category_g_location__location_path' && is_numeric($value)) {
                    return LocationPath::render($value);
                }

                if (strpos($value, 'LC_') === false) {
                    return $value;
                }

                if (strpos($value, 'LC_') !== 0 || substr_count($value, 'LC_') > 1) {
                    return $lang->get_in_text($value);
                }

                return $lang->get($value);
            }, array_keys($row), array_values($row));
        }

        return $return;
    }
}
