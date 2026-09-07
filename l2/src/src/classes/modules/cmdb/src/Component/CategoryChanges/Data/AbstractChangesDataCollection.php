<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Data;

/**
 * Class AbstractChangesDataCollection
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Data
 */
abstract class AbstractChangesDataCollection extends AbstractData
{
    /**
     * @var array
     */
    private $reformatedData = [];

    /**
     * @return array
     */
    public function getReformatedData(): array
    {
        return $this->reformatedData;
    }

    /**
     * @return static
     */
    public function reformatChangesDataCollection()
    {
        if ($this->hasData()) {
            $objectId = $this->getObjectId();

            /**
             * @var $changesData ChangesData
             */

            foreach ($this->getData() as $changesData) {
                $this->reformatedData += $changesData->getData();
            }
        }

        return $this;
    }
}
