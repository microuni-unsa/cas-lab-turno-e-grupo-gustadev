<?php

namespace idoit\Module\System\Cleanup;

use idoit\Component\Logger;
use isys_application;
use Symfony\Component\DependencyInjection\Container;

/**
 * Class AbstractCleanup
 *
 * @package idoit\Module\System\Cleanup
 */
abstract class AbstractCleanup
{
    /** @var Container|null */
    protected ?Container $container;

    /** @var Logger */
    protected Logger $logger;

    /**
     * AbstractCleanup constructor.
     */
    public function __construct()
    {
        $this->container = isys_application::instance()->container;

        $logPath = $this->container->get('settingsTenant')->get('system.log.path', BASE_DIR . '/log') . "/cleanup.log";
        $this->logger = Logger::factory('Clean up', $logPath);
    }

    /**
     * Method for starting the cleanup process.
     */
    abstract public function process();
}
