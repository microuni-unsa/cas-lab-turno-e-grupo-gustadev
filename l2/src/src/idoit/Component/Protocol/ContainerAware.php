<?php

namespace idoit\Component\Protocol;

use Symfony\Component\DependencyInjection\Container;

/**
 * i-doit Protocols
 *
 * @package     idoit\Component\Protocol
 * @author      Dennis Stücken <dstuecken@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface ContainerAware
{

    /**
     * @return Container
     */
    public function getDi(): Container;

    /**
     * @param Container $container
     *
     * @return mixed
     */
    public function setDi(Container $container): mixed;
}
