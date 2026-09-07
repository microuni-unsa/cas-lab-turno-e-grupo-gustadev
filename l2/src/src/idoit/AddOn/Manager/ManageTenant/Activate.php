<?php

namespace idoit\AddOn\Manager\ManageTenant;

use idoit\AddOn\Manager\ManageActionInterface;
use isys_application;

class Activate implements ManageActionInterface
{
    private string $identifier = '';

    /**
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function process(): bool
    {
        /** @var \isys_module_manager $moduleManager */
        $moduleManager = isys_application::instance()->container->get('moduleManager');

        if (!$moduleManager->is_active($this->identifier) && $moduleManager->activateAddOn($this->identifier)) {
            isys_application::instance()
                ->renewCache();

            return true;
        }

        return false;
    }
}
