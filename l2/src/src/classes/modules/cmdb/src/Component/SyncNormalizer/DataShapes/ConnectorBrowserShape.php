<?php

namespace idoit\Module\Cmdb\Component\SyncNormalizer\DataShapes;

use idoit\Module\Cmdb\Component\SyncNormalizer\DataNormalizerProviderConfig;
use isys_application;
use isys_tenantsettings;

class ConnectorBrowserShape extends AbstractBrowserShape
{
    /**
     * @param $value
     *
     * @return bool
     */
    public function isApplicable($value): bool
    {
        if (is_array($value)) {
            return false;
        }

        $separator = isys_tenantsettings::get('gui.separator.connector', ' > ');
        $data = [];

        if (strpos($value, ' > ') !== false) {
            $data = explode(' > ', $value);
        }

        return count($data) == 2;
    }

    /**
     * @param DataNormalizerProviderConfig $config
     * @param string                       $propertyKey
     * @param array                        $requestData
     *
     * @return void
     */
    public function handle(DataNormalizerProviderConfig $config, string $propertyKey, array $requestData)
    {
        $separator = isys_tenantsettings::get('gui.separator.connector', ' > ');
        [$objectTitle, $connectorTitle] = explode(' > ', $this->getValue());

        $property = $config->getProperties()[$propertyKey];
        $tableJoins = $property->getData()->getJoins();
        $referenceTable = $tableJoins[0]->getTable();

        $dao = isys_application::instance()->container->get('cmdb_dao');
        $query = "SELECT {$referenceTable}__id FROM {$referenceTable}
            INNER JOIN isys_obj ON isys_obj__id = {$referenceTable}__isys_obj__id
            WHERE isys_obj__title = {$dao->convert_sql_text($objectTitle)}
              AND {$referenceTable}__title = {$dao->convert_sql_text($connectorTitle)} limit 1;";

        $result = $dao->retrieve($query);

        if ($result->count()) {
            $this->setValue((int) $result->get_row_value("{$referenceTable}__id"));
        }
    }
}
