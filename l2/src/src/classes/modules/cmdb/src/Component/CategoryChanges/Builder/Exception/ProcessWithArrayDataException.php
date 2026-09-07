<?php
namespace idoit\Module\Cmdb\Component\CategoryChanges\Builder\Exception;

use isys_exception;

/**
 * Class ProcessWithArrayDataException
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Builder\Exception
 */
class ProcessWithArrayDataException extends isys_exception
{
    /**
     * ProcessWithArrayDataException constructor.
     */
    public function __construct($message)
    {
        parent::__construct('An error occurred while processing with array data: ' . $message);
    }
}
