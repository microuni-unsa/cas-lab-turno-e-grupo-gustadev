<?php

namespace idoit\Component;

use isys_application;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;

/**
 * i-doit Logger - extends the brilliant Monolog class with a few own methods (mostly used for the GUI).
 *
 * @package    i-doit
 * @subpackage Component
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class Logger extends \Monolog\Logger
{
    /** @deprecated Use \Monolog\Level::Debug */
    public const DEBUG = 100;

    /** @deprecated Use \Monolog\Level::Info */
    public const INFO = 200;

    /** @deprecated Use \Monolog\Level::Notice */
    public const NOTICE = 250;

    /** @deprecated Use \Monolog\Level::Warning */
    public const WARNING = 300;

    /** @deprecated Use \Monolog\Level::Error */
    public const ERROR = 400;

    /** @deprecated Use \Monolog\Level::Critical */
    public const CRITICAL = 500;

    /** @deprecated Use \Monolog\Level::Alert */
    public const ALERT = 550;

    /** @deprecated Use \Monolog\Level::Emergency */
    public const EMERGENCY = 600;

    /**
     * @param string   $name
     * @param string   $logPath
     * @param int|null $level
     *
     * @return Logger
     */
    public static function factory($name, $logPath, int|null $level = null): Logger
    {
        $logHandler = new CountingRotatingFileHandler($logPath, 0, $level ?? Level::Warning->value);
        $logHandler->setFilenameFormat('{filename}_{date}', CountingRotatingFileHandler::FILE_PER_DAY);

        // Keep the defaults, but allow line breaks and ignore empty 'context' and 'extra'.
        $logHandler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s', true, true));

        $log = new self($name);
        $log->pushHandler($logHandler);

        return $log;
    }

    /**
     * Method for retrieving a fitting icon for every log level.
     *
     * @param integer $level
     *
     * @static
     * @return string|array
     */
    public static function getLevelIcons($level = null): string|array
    {
        global $g_dirs;

        $icons = [
            Level::Debug->value     => $g_dirs["images"] . 'axialis/development/bug.svg',
            Level::Info->value      => $g_dirs["images"] . 'axialis/basic/button-info.svg',
            Level::Notice->value    => $g_dirs["images"] . 'axialis/basic/comment-empty.svg',
            Level::Warning->value   => $g_dirs["images"] . 'axialis/basic/warning.svg',
            Level::Error->value     => $g_dirs["images"] . 'axialis/basic/button-info-yellow.svg',
            Level::Critical->value  => $g_dirs["images"] . 'axialis/basic/button-info-red.svg',
            Level::Alert->value     => $g_dirs["images"] . 'axialis/basic/button-remove.svg',
            Level::Emergency->value => $g_dirs["images"] . 'axialis/basic/symbol-forbidden.svg'
        ];

        if ($level !== null) {
            return $icons[$level];
        }

        return $icons;
    }

    /**
     * Method for retrieving a fitting text-color (via CSS class) for every log level.
     *
     * @param integer $level
     *
     * @static
     * @return string|array
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public static function getLevelColors($level = null): string|array
    {
        $colors = [
            Level::Debug->value     => 'green',
            Level::Info->value      => 'blue',
            Level::Notice->value    => '',
            Level::Warning->value   => 'yellow',
            Level::Error->value     => 'red',
            Level::Critical->value  => 'red',
            Level::Alert->value     => 'red',
            Level::Emergency->value => 'red'
        ];

        if ($level !== null) {
            return $colors[$level];
        }

        return $colors;
    }

    /**
     * Gets log level as string.
     *
     * @param int $level
     *
     * @static
     * @return string|array
     * @author Leonard Fischer <lfischer@i-doit.com>
     */
    public static function getLevelNames($level = null): string|array
    {
        $language = isys_application::instance()->container->get('language');

        $names = [
            Level::Debug->value     => $language->get('LC_UNIVERSAL__LOG_LEVEL__DEBUG'),
            Level::Info->value      => $language->get('LC_UNIVERSAL__LOG_LEVEL__INFO'),
            Level::Notice->value    => $language->get('LC_UNIVERSAL__LOG_LEVEL__NOTICE'),
            Level::Warning->value   => $language->get('LC_UNIVERSAL__LOG_LEVEL__WARNING'),
            Level::Error->value     => $language->get('LC_UNIVERSAL__LOG_LEVEL__ERROR'),
            Level::Critical->value  => $language->get('LC_UNIVERSAL__LOG_LEVEL__CRITICAL'),
            Level::Alert->value     => $language->get('LC_UNIVERSAL__LOG_LEVEL__FATAL_ERROR'),
            Level::Emergency->value => $language->get('LC_UNIVERSAL__LOG_LEVEL__FATAL_ERROR'),
        ];

        if ($level !== null) {
            return $names[$level];
        }

        return $names;
    }

    /**
     * Method for compability with isys_log
     *
     * @param bool $print
     * @param bool $standalone
     *
     * @return bool
     *
     * @deprecated needs to be removed after everything has been refactored to Monolog
     */
    public function flush_verbosity($print = true, $standalone = true): bool
    {
        return true;
    }

    /**
     * Method for compability with isys_log
     *
     * @param $mode
     *
     * @return bool
     *
     * @deprecated needs to be removed after everything has been refactored to Monolog
     */
    public function set_destruct_flush($mode): bool
    {
        return true;
    }
}
