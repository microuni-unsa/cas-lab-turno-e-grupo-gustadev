<?php

namespace idoit\Component\Provider;

use Symfony\Component\DependencyInjection\Container;

/**
 * i-doit Container Aware Trait
 *
 * @package     i-doit
 * @subpackage  Component
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
trait DiConstructor
{
    use DiInjectable;

    /**
     * DiConstructor constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
}
