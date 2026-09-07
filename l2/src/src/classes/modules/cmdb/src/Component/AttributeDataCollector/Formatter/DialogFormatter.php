<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector\Formatter;

use Exception;
use idoit\Component\Property\Property;
use isys_application;
use isys_cmdb_dao_dialog;

class DialogFormatter
{
    /**
     * @param array         $dialogData
     * @param string        $dialogTable
     * @param Property|null $property
     *
     * @return array
     *
     * @throws Exception
     */
    public static function reformat(array $dialogData, string $dialogTable, ?Property $property = null): array
    {
        $newData = [];
        $dependentProperty = $dependentTable = null;
        $dependentTableData = [];
        
        if ($property !== null) {
            $dependentProperty = $property->getDependency()
                    ->getPropkey() ?? null;
            
            if ($dependentProperty) {
                $dependentTable = $property->getUi()
                        ->getParams()['secTable'] ?? null;
                
                $dialogDao = isys_cmdb_dao_dialog::instance(isys_application::instance()->container->get('database'));
                $dependentTableData = $dialogDao->set_table($dependentTable)
                    ->load()
                    ->get_data();
            }
        }
        
        foreach ($dialogData as $id => $data) {
            $currentData = [
                'id'    => $data[$dialogTable . '__id'],
                'title' => $data['title'] ?? $data[$dialogTable . '__title'],
            ];
            
            if (isset($data[$dialogTable . '__const']) && trim($data[$dialogTable . '__const']) !== '') {
                $currentData['const'] = $data[$dialogTable . '__const'];
            }
            
            if ($dependentTable && !empty($dependentTableData)) {
                $dependentData = array_pop(DialogFormatter::reformat([$dependentTableData[$data[$dialogTable . '__' . $dependentTable . '__id']]], $dependentTable));
                $dependentData['attribute'] = $dependentProperty;
                $currentData['dependentData'] = $dependentData;
            }
            
            $newData[] = $currentData;
        }
        
        return $newData;
    }
}
