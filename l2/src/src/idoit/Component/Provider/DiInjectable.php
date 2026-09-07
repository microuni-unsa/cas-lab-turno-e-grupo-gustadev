<?php

namespace idoit\Component\Provider;

use isys_application as Application;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * i-doit Container Aware Trait
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
trait DiInjectable
{
    protected ?ContainerInterface $container;

    /**
     * @param ContainerInterface|null $container
     * @return void
     */
    public function setContainer(?ContainerInterface $container = null): void
    {
        $this->container = $container;
    }

    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function setDi(ContainerInterface $container): mixed
    {
        $this->setContainer($container);

        return $this;
    }

    /**
     * @return ContainerInterface
     */
    public function getDi(): ContainerInterface
    {
        if (!$this->container) {
            $this->setContainer(Application::instance()->container);
        }

        return $this->container;
    }
}
