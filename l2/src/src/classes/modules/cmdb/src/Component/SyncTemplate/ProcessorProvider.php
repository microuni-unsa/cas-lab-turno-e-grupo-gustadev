<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate;

use idoit\Module\Cmdb\Component\SyncTemplate\Processors\AbstractProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\AccountingProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\ApplicationAssignedObjProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\ApplicationProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\AuditProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\ChassisDeviceProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\ClusterMembershipsProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\ClusterMembersProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\ContactProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\CustomFieldsProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\GlobalProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\IpProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\LocationProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\ModelProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\NetProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\OperatingSystemProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\SlaProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\StorageDeviceProcessor;
use idoit\Module\Cmdb\Component\SyncTemplate\Processors\VirtualHostProcessor;

class ProcessorProvider
{
    /**
     * @var array
     */
    protected $processor = [];

    /**
     * @return ProcessorProvider
     * @throws \Exception
     */
    public static function factory(): ProcessorProvider
    {
        return (new static())
          ->addProcessor(ApplicationAssignedObjProcessor::factory())
          ->addProcessor(ApplicationProcessor::factory())
          ->addProcessor(AccountingProcessor::factory())
          ->addProcessor(AuditProcessor::factory())
          ->addProcessor(ChassisDeviceProcessor::factory())
          ->addProcessor(ClusterMembershipsProcessor::factory())
          ->addProcessor(ClusterMembersProcessor::factory())
          ->addProcessor(ContactProcessor::factory())
          ->addProcessor(CustomFieldsProcessor::factory())
          ->addProcessor(GlobalProcessor::factory())
          ->addProcessor(IpProcessor::factory())
          ->addProcessor(LocationProcessor::factory())
          ->addProcessor(ModelProcessor::factory())
          ->addProcessor(NetProcessor::factory())
          ->addProcessor(OperatingSystemProcessor::factory())
          ->addProcessor(SlaProcessor::factory())
          ->addProcessor(StorageDeviceProcessor::factory())
          ->addProcessor(VirtualHostProcessor::factory());
    }

    /**
     * @param AbstractProcessor $processor
     *
     * @return ProcessorProvider
     */
    public function addProcessor(AbstractProcessor $processor): ProcessorProvider
    {
        $this->processor[$processor::getCategoryConst()] = $processor;
        return $this;
    }

    /**
     * @param string $categoryConst
     *
     * @return AbstractProcessor|null
     */
    public function getProcessor(string $categoryConst): ?AbstractProcessor
    {
        if (isset($this->processor[$categoryConst]) && $this->processor[$categoryConst] instanceof AbstractProcessor) {
            return $this->processor[$categoryConst];
        }
        return null;
    }
}
