<?php

namespace idoit\AddOn\Manager;

use idoit\Component\Logger as idoitLogger;
use isys_application;

class Logger extends idoitLogger
{
    /**
     * @param string $type
     * @param string $identifier
     *
     * @return idoitLogger
     */
    public static function getLogger(string $type, string $identifier): idoitLogger
    {
        $name = $identifier . '-' . isys_application::instance()->tenant->name;
        $fileName = BASE_DIR . "/log/{$type}-{$identifier}-" . isys_application::instance()->tenant->name . ".log";

        return idoitLogger::factory(
            $name,
            $fileName
        );
    }
}
