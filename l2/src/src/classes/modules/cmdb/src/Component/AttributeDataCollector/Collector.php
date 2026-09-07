<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\AttributeDataCollector;

use idoit\Component\Property\Property;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\AbstractCollector;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\BrowserSecondList;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\BrowserConnector;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\BrowserLocation;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\BrowserObject;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\BrowserSecondListObject;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\Dialog;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\DialogCallbackData;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\DialogData;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\DialogList;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\DialogPlus;
use idoit\Module\Cmdb\Component\AttributeDataCollector\CollectorTypes\Multiselect;

class Collector
{
    /**
     * @var AbstractCollector[]
     */
    private $collectorTypes = [];

    /**
     * @var AbstractCollector
     */
    private $applicableType;

    public function __construct()
    {
        $this->loadTypes();
    }

    /**
     * @return void
     */
    private function loadTypes(): void
    {
        $this->collectorTypes = [
            new BrowserSecondList(),
            new BrowserLocation(),
            new BrowserObject(),
            new DialogPlus(),
            new DialogData(),
            new DialogCallbackData(),
            new Dialog(),
            new DialogList(),
            new Multiselect(),
        ];
    }

    /**
     * @param Property $property
     *
     * @return bool
     */
    public function isApplicable(Property $property): bool
    {
        foreach ($this->collectorTypes as $collector) {
            if ($collector->isApplicable($property)) {
                $this->applicableType = $collector;
                return true;
            }
        }

        return false;
    }

    /**
     * @return AbstractCollector
     */
    public function getApplicableType(): AbstractCollector
    {
        return $this->applicableType;
    }
}
