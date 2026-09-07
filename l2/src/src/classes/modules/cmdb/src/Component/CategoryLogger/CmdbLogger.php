<?php

namespace idoit\Module\Cmdb\Component\CategoryLogger;

use isys_module_logbook;

class CmdbLogger
{
    /**
     * @var CmdbLogger|null
     */
    private static $loggerInstance = null;

    /**
     * @var isys_module_logbook
     */
    protected isys_module_logbook $logbookDao;

    /**
     * @var CmdbRecord[]
     */
    private array $records = [];

    /**
     * @param isys_module_logbook $logbookDao
     */
    public function __construct(isys_module_logbook $logbookDao)
    {
        $this->logbookDao = $logbookDao;
    }

    /**
     * @return isys_module_logbook
     */
    public function getLogbookDao(): isys_module_logbook
    {
        return $this->logbookDao;
    }

    /**
     * @return CmdbLogger
     */
    public static function instance(): CmdbLogger
    {
        if (!isset(self::$loggerInstance)) {
            self::$loggerInstance = new self(new isys_module_logbook());
        }

        return self::$loggerInstance;
    }

    /**
     * @return array
     */
    public function getRecords(): array
    {
        return $this->records;
    }

    /**
     * @param CmdbRecord $record
     *
     * @return CmdbLogger
     */
    public function addRecord(CmdbRecord $record): CmdbLogger
    {
        $this->records[] = $record;
        return $this;
    }

    /**
     * @param int|null $source
     *
     * @return $this
     */
    public function writeCmdbLog(?int $source = null): CmdbLogger
    {
        // Write into category
        $eventManager = \isys_event_manager::getInstance();

        foreach ($this->records as $cmdbRecord) {
            $eventManager->triggerCMDBEvent(
                $cmdbRecord->getEventConstant(),
                $cmdbRecord->getEventDescription(),
                $cmdbRecord->getObjectId(),
                $cmdbRecord->getObjectTypeId(),
                $cmdbRecord->getCategoryTitle(),
                serialize($cmdbRecord->getChanges()),
                $cmdbRecord->getComment(),
                $cmdbRecord->getReason(),
                $cmdbRecord->getObjectTitle(),
                null,
                count($cmdbRecord->getChanges() ?: []),
                $source
            );
        }

        return $this;
    }


}
