<?php

namespace idoit\Component\Table\Filter\Operation;

use idoit\Component\Property\Property;
use isys_application;
use isys_cmdb_dao_list_objects;

/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class DateOperation extends PropertyOperation
{
    /**
     * @param string $filter
     * @param string $value
     *
     * @return bool
     */
    public function isApplicable($filter, $value): bool
    {
        $property = $this->getProperty($filter);

        return $property[Property::C__PROPERTY__DATA][Property::C__PROPERTY__DATA__TYPE] === C__TYPE__DATE;
    }

    /**
     * Apply Property
     *
     * @param isys_cmdb_dao_list_objects $listDao
     * @param array|Property             $property
     * @param string                     $name
     * @param string                     $value
     *
     * @return bool
     */
    protected function applyProperty(isys_cmdb_dao_list_objects $listDao, $property, $name, $value): bool
    {
        $timestamp = strtotime($value);

        if ($timestamp !== false) {
            $fieldName = $listDao->get_database_component()->escapeColumnName($name);

            // @see ID-9552 Fix date condition.
            // @see ID-9647 We now simply pass both english and german format, because these might both be used, see 'isys_cmdb_dao_category->build_query_date_format()'.
            $formattedDateEnglish = $listDao->convert_sql_text(date('%Y-m-d%', $timestamp));
            $formattedDateGerman = $listDao->convert_sql_text(date('%d.m.Y%', $timestamp));
            $listDao->add_additional_having_conditions("({$fieldName} LIKE {$formattedDateEnglish} OR {$fieldName} LIKE {$formattedDateGerman})");

            return true;
        } elseif (!$this->isBroadsearch) {
            // @see ID-10110 Skip user notification in 'broad search' context.
            isys_application::instance()->container->get('notify')->info(
                isys_application::instance()->container->get('language')->get('LC_CALENDAR_POPUP__WRONG_DATE_FORMAT'),
                ['sticky' => true]
            );
        }

        return false;
    }
}
