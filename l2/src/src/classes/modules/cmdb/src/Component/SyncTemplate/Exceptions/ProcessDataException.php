<?php declare(strict_types = 1);

namespace idoit\Module\Cmdb\Component\SyncTemplate\Exceptions;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class ProcessDataException extends Exception
{
    /**
     * @var array
     */
    private $issues = [];

    /**
     * @return array
     */
    public function getIssues(): array
    {
        return $this->issues;
    }

    /**
     * @param array $issues
     */
    public function setIssues(array $issues): void
    {
        $this->issues = $issues;
    }

    /**
     * @param array $issues
     *
     * @return ProcessDataException
     */
    public static function SyncIssues(array $issues): ProcessDataException
    {
        $exception = new self("There are some issues while preparing the data before the sync.", Response::HTTP_BAD_REQUEST);
        $exception->setIssues($issues);

        return $exception;
    }
}
