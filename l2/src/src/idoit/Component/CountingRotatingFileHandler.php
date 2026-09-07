<?php

namespace idoit\Component;

use Monolog\Handler\RotatingFileHandler;
use Monolog\LogRecord;

class CountingRotatingFileHandler extends RotatingFileHandler
{
    private int $writeCount = 0;

    /**
     * {@inheritDoc}
     */
    protected function write(array|LogRecord $record): void
    {
        $this->writeCount++;

        parent::write($record);
    }

    /**
     * Get the total number of writes to the log file.
     *
     * @return int Number of log writes
     */
    public function getWriteCount(): int
    {
        return $this->writeCount;
    }
}
