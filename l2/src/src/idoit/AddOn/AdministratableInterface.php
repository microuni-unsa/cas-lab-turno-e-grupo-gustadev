<?php

namespace idoit\AddOn;

use isys_component_tree;

/**
 * i-doit Module interface for including a menu in the administration area
 *
 * @package     idoit\AddOn
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
interface AdministratableInterface
{
    /**
     * Implement this method in order to show your add-on in the administration area.
     *
     * @param isys_component_tree $tree
     * @param int                 $addonNodeId
     *
     * @return void
     */
    public function buildAdministrationTree(isys_component_tree $tree, int $addonNodeId): void;
}
