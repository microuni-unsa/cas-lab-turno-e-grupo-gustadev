<?php
/**
 * i-doit
 *
 * Module initializer
 *
 * @package     modules
 * @subpackage  auth
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// @see ID-9320 Add listeners to empty relevant caches.
isys_application::instance()->container->get('signals')
    ->connect('mod.cmdb.processMenuTreeLinks', ['isys_module_auth', 'process_menu_tree_links'])
    ->connect('mod.cmdb.afterCreateCategoryEntry', ['isys_module_auth', 'cleanupRightsAfterCategoryCreate'])
    ->connect('mod.cmdb.afterCategoryEntrySave', ['isys_module_auth', 'cleanupRightsAfterCategorySave'])
    ->connect('mod.cmdb.beforeRankRecord', ['isys_module_auth', 'cleanupRightsAfterCategoryRank']);
