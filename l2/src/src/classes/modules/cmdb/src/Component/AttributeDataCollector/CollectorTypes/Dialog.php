<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes;

use Exception;
use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\AttributeDataCollector\Formatter\DialogFormatter;
use isys_application;
use isys_cmdb_dao_dialog;

class Dialog extends AbstractCollector
{
    /**
     * @param string   $sourceTable
     * @param Property $property
     *
     * @return isys_cmdb_dao_dialog
     *
     * @throws Exception
     */
    public static function getDialogInstance(string &$sourceTable, Property $property)
    {
        $dialogInstance = isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'));

        if ($sourceTable === 'isys_catg_custom_fields_list') {
            $sourceTable = 'isys_dialog_plus_custom';
        }

        if ($sourceTable === 'isys_dialog_plus_custom') {
            $dialogTableCondition = $property->getUi()->getParams()['condition'];
            [, $value] = explode('=', str_replace([' ', '\''], '', $dialogTableCondition));
            $dialogInstance->setAdditionFieldsToCondition(['identifier' => $value]);
        }

        return $dialogInstance;
    }

    /**
     * @param Property $property
     * @param bool     $reformat
     *
     * @return array
     * @throws Exception
     */
    protected function fetchData(Property $property, bool $reformat): array
    {
        $sourceTable = $property->getData()
            ->getReferences()[0];
        $dialogInstance = self::getDialogInstance($sourceTable, $property);

        $dialogData = $dialogInstance->set_table($sourceTable)
            ->load()
            ->get_data();

        if (!$dialogData) {
            return [];
        }

        return $reformat ? DialogFormatter::reformat($dialogData, $sourceTable, $property) : $dialogData;
    }

    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        $references = $property->getData()
            ->getReferences();
        
        return $property->getInfo()
                ->getType() === Property::C__PROPERTY__INFO__TYPE__DIALOG && is_array($references) && !empty($references);
    }
}
