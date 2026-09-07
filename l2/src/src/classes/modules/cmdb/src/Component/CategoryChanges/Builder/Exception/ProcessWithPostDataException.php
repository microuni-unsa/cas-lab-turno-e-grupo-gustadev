<?php
namespace idoit\Module\Cmdb\Component\CategoryChanges\Builder\Exception;

use isys_exception;

/**
 * Class ProcessWithPostDataException
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Builder\Exception
 */
class ProcessWithPostDataException extends isys_exception
{
    /**
     * ProcessWithPostDataException constructor.
     */
    public function __construct($message)
    {
        parent::__construct('An error occurred while processing with post data: ' . $message);
    }
}
