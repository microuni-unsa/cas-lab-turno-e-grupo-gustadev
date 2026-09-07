<?php

namespace idoit\Component;

use isys_application;
use isys_component_session;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator as SymfonyUrlGenerator;

/**
 * UrlGenerator class to create URLs based on symfony routes,
 * including some customized behavior for some routes.
 *
 * @package   Component
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class UrlGenerator extends SymfonyUrlGenerator
{
    public function generate(string $name, array $parameters = [], int $referenceType = parent::ABSOLUTE_PATH): string
    {
        /** @var isys_component_session|null $session */
        $session = isys_application::instance()->container->get('session', ContainerInterface::NULL_ON_INVALID_REFERENCE);

        // @see ID-12121 Apply tenant parameter.
        if ($session !== null && !isset($parameters[C__CMDB__GET__TENANT])) {
            $parameters[C__CMDB__GET__TENANT] = $session->get_mandator_id();
        }

        return parent::generate($name, $parameters, $referenceType);
    }
}
