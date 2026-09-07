<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Component\Table\Filter\Operation;

use idoit\Component\Helper\Unserialize;
use isys_callback;
use isys_cmdb_dao_list_objects;
use isys_smarty_plugin_f_dialog;

class DialogOrderByOperation extends PropertyOperation
{
    private function getUnsupportedCallbacks()
    {
        return [
            'callback_property_virtual_machine'
        ];
    }

    public function isApplicable($filter, $value): bool
    {
        $property = $this->getProperty($filter);

        // ID-7983 yes-no fields cannot be ordered via this operation
        if (isset($property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1]) && $property[C__PROPERTY__FORMAT][C__PROPERTY__FORMAT__CALLBACK][1] === 'get_yes_or_no') {
            return false;
        }

        if (isset($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) &&
            $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'] instanceof isys_callback
        ) {
            if (in_array($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->getMethod(), $this->getUnsupportedCallbacks())) {
                return false;
            }
        }

        if ($property && isset($property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES])) {
            // if the referenced table is in these formats *_2_*, isys_obj, isys_connection, isys_cat(g|s)_*list then we cannot use the ORDER BY FIELD type
            preg_match_all('/_2_|isys_obj|isys_connection|isys_cat(g|s).*list/', $property[C__PROPERTY__DATA][C__PROPERTY__DATA__REFERENCES][0] ?? '', $matches);
            if (count($matches)) {
                return false;
            }
        }

        if ($property && isset($property[C__PROPERTY__INFO], $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE]) &&
            in_array(
                $property[C__PROPERTY__INFO][C__PROPERTY__INFO__TYPE],
                [C__PROPERTY__INFO__TYPE__DIALOG, C__PROPERTY__INFO__TYPE__DIALOG_PLUS, C__PROPERTY__INFO__TYPE__DIALOG_LIST]
            )) {
            $class = explode('__', $filter)[0];
            if ($class && class_exists($class)) {
                $obj = $class::instance(\isys_application::instance()->container->get('database'));
                return !method_exists($obj, 'is_multivalued') || !$obj->is_multivalued();
            }
        }
        return false;
    }

    /**
     * Apply Property
     *
     * @param isys_cmdb_dao_list_objects $listDao
     * @param                            $property
     * @param                            $name
     * @param                            $value
     *
     * @return bool
     */
    protected function applyProperty(isys_cmdb_dao_list_objects $listDao, $property, $name, $value): bool
    {
        $items = [];
        if (isset($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable']) && $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable']) {
            $dialog = new isys_smarty_plugin_f_dialog();
            $items = $dialog->get_array_data(
                $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_strTable'],
                C__RECORD_STATUS__NORMAL,
                null,
                $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['condition']
            );
        } elseif (isset($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
            if (is_array($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                // If we simply get an array.
                $items = $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'];
            } elseif (is_object($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) &&
                get_class($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']) == 'isys_callback') {
                // If we get an instance of "isys_callback"
                $items = $property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']->execute();
            } elseif (is_string($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData'])) {
                // Or if we get a string (we assume it's serialized).
                $items = Unserialize::toArray($property[C__PROPERTY__UI][C__PROPERTY__UI__PARAMS]['p_arData']);
            }
        }

        if (!empty($items)) {
            asort($items);

            // @see  ID-8181  Convert values, if they are not numeric.
            $sortKeys = array_map(function ($item) use ($listDao) {
                return is_numeric($item)
                    ? $item // @todo  Should this be converted? If so: use convert id, int or float?
                    : $listDao->convert_sql_text($item);
            }, array_keys($items));

            $ids = implode(',', $sortKeys);

            if (isset($property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS])) {
                $orderByField = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD_ALIAS];
            } else {
                $orderByField = $property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD];
            }
            if ($property[C__PROPERTY__DATA][C__PROPERTY__DATA__FIELD] === 'isys_catg_custom_fields_list__field_content') {
                $listDao->set_order_by('isys_cmdb_dao_category_g_custom_fields__' . $orderByField, $value);
            } else {
                $listDao->set_order_by("FIELD({$orderByField}, {$ids})", $value);
            }
            return true;
        }

        return false;
    }
}
