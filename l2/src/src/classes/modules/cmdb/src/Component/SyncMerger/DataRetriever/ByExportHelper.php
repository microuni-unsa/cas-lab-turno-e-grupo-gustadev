<?php
namespace idoit\Module\Cmdb\Component\SyncMerger\DataRetriever;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncMerger\ExportHelperFilter\ExportHelperFilter;
use isys_application;
use isys_cmdb_dao_category;
use isys_export_helper;
use isys_request;

class ByExportHelper implements DataRetrieverInterface
{
    /**
     * @var array
     */
    private static $ignoredCallbackMethods = [
        'dialog',
        'dialog_plus',
        'get_reference_value',
        'object_image',
        'cable_connection',
        'datetime',
        'money_format',
    ];

    /**
     * @var isys_export_helper[]
     */
    private static $helperClasses = [];

    /**
     * @param Property $property
     *
     * @return bool
     */
    public static function isApplicable(Property $property)
    {
        $callback = $property->getFormat()
            ->getCallback();

        if (is_array($callback) && !empty($callback) && !in_array($callback[1], self::$ignoredCallbackMethods)) {
            return true;
        }
        return false;
    }

    /**
     * @param string            $propertyKey
     * @param Property          $property
     * @param array             $properties
     * @param array             $categoryData
     * @param array             $currentData
     * @param isys_request|null $request
     *
     * @return array|mixed|null
     * @throws \Exception
     */
    public function retrieveValue(
        string $propertyKey,
        Property $property,
        array $properties,
        array $categoryData,
        array $currentData,
        isys_cmdb_dao_category $dao,
        ?isys_request $request = null
    ) {
        $callback = $property->getFormat()->getCallback();
        $dbField = $property->getData()->getField();
        $language = isys_application::instance()->container->get('language');

        if ($dbField === 'isys_catg_custom_fields_list__field_content') {
            $dbField = $property->getData()->getFieldAlias();
        }

        $helperClass = $callback[0];
        if (isset(self::$helperClasses[$helperClass])) {
            self::$helperClasses[$helperClass]->set_row($categoryData);
            self::$helperClasses[$helperClass]->set_reference_info($property->getData());
            self::$helperClasses[$helperClass]->set_format_info($property->getFormat());
            self::$helperClasses[$helperClass]->set_ui_info($property->getUi());
        } else {
            self::$helperClasses[$helperClass] = new $helperClass(
                $categoryData,
                \isys_application::instance()->container->get('database'),
                $property->getData(),
                $property->getFormat(),
                $property->getUi()
            );
        }

        if (($unitPropertyKey = $property->getFormat()
            ->getUnit())) {
            $unitProperty = $properties[$unitPropertyKey];
            $unitDbField = $unitProperty->getData()
                ->getField();

            if (isset($categoryData[$unitDbField])) {
                self::$helperClasses[$helperClass]->set_unit_const($categoryData[$unitDbField]);
            }
        }

        $exportMethod = $callback[1];
        $exportValue = self::$helperClasses[$helperClass]->$exportMethod($categoryData[$dbField]);

        if (is_object($exportValue) && $exportValue instanceof \isys_export_data) {
            $exportValue = $exportValue->get_data();
        }

        // @see ID-12299 Prevent empty values from further processing.
        if (is_array($exportValue) && empty($exportValue)) {
            return null;
        }

        if (is_array($exportValue) && ExportHelperFilter::isApplicable($exportValue, $exportMethod, $dao, $propertyKey)) {
            return ExportHelperFilter::filterValue($exportValue, $exportMethod, $dao, $propertyKey);
        }

        return $exportValue;
    }
}
