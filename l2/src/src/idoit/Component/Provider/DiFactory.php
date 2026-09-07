<?php

namespace idoit\Component\Provider;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * i-doit Container Aware Factory Trait
 *
 * @package     i-doit
 * @subpackage  Component
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @deprecated
 */
trait DiFactory
{
    use DiInjectable;

    /**
     * Factory with $container parameter
     *
     * @param Container $container
     *
     * @return static
     */
    public static function factory(ContainerInterface $container): static
    {
        $instance = new self();

        if (method_exists($instance, 'setDi')) {
            $instance->setDi($container);
        }

        return $instance;
    }
}
