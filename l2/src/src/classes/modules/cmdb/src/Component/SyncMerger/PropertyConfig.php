<?php

namespace idoit\Module\Cmdb\Component\SyncMerger;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\ByCustomCategoryDataset;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\ByDataset;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\ByExportHelper;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\ByReference;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\ByUiCallback;
use idoit\Module\Cmdb\Component\SyncMerger\DataRetriever\DataRetrieverInterface;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_g_custom_fields;

class PropertyConfig
{
    /**
     * @var DataRetrieverInterface|null
     */
    private ?DataRetrieverInterface $dataRetriever = null;

    /**
     * @param Property               $property
     * @param isys_cmdb_dao_category $dao
     *
     * @return PropertyConfig
     */
    public static function instance(Property $property, isys_cmdb_dao_category $dao)
    {
        $instance = new PropertyConfig();
        $instance->setDataRetriever($property, $dao);
        return $instance;
    }

    /**
     * @return DataRetrieverInterface|null
     */
    public function getDataRetriever(): ?DataRetrieverInterface
    {
        return $this->dataRetriever;
    }

    /**
     * @param Property               $property
     * @param isys_cmdb_dao_category $dao
     *
     * @return void
     */
    public function setDataRetriever(Property $property, isys_cmdb_dao_category $dao): void
    {
        if (ByExportHelper::isApplicable($property)) {
            $this->dataRetriever = new ByExportHelper();
            return;
        }

        if (ByUiCallback::isApplicable($property)) {
            $this->dataRetriever = new ByUiCallback();
            return;
        }

        if (ByReference::isApplicable($property)) {
            $this->dataRetriever = new ByReference();
            return;
        }

        if (ByDataset::isApplicable($property)) {
            $this->dataRetriever = $dao instanceof isys_cmdb_dao_category_g_custom_fields
                ? new ByCustomCategoryDataset()
                : new ByDataset();
        }
    }
}
