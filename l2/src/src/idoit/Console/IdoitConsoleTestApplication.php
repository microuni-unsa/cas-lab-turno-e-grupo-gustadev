<?php

namespace idoit\Console;

use isys_application;

class IdoitConsoleTestApplication extends IdoitConsoleApplication
{
    /**
     * IdoitConsoleApplication constructor.
     */
    public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN')
    {
        parent::__construct('i-doit console utility', isys_application::instance()->info['version']);

        $this->setCatchExceptions(false);
        $this->setAutoExit(false);
    }
}
