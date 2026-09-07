<?php
/**
 * i-doit
 * Static constant not registered by the dynamic constant manager.
 * Please empty this list every major release.
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

// Use the following services, if necessary:
// $systemSettings = isys_application::instance()->container->get('settingsSystem');
// $tenantSettings = isys_application::instance()->container->get('settingsTenant');
// $userSettings = isys_application::instance()->container->get('settingsUser');
// $session = isys_application::instance()->container->get('session');

$constants = [
    // System constants from table 'isys_const_system' (in 'idoit_system').
    'C__RECORD_PROPERTY__NOT_SHOW_IN_LIST' => 16,
    // Allow the update migrations.
    'C__UPDATE_MIGRATION'                  => true, // @todo Will be removed in i-doit 40!
    // Category property's value type. Defaults to 'text'.
    'C__CATEGORY_DATA__FORMAT'             => 'format', // @todo Will be removed in i-doit 40!
    // Unused IP related constants.
    'C__IP__GATEWAY'                       => 3, // @todo Will be removed in i-doit 40!
    'C__IP__NET'                           => 4, // @todo Will be removed in i-doit 40!
    'C__IP__ASSIGNMENT'                    => 5,// @todo Will be removed in i-doit 40!
    'C__IP__IPV6_SCOPE'                    => 6,// @todo Will be removed in i-doit 40!
    'C__IP__IPV6_PREFIX'                   => 7,// @todo Will be removed in i-doit 40!
];

foreach ($constants as $constantName => $constantValue) {
    if (!defined($constantName)) {
        define($constantName, $constantValue);
    }
}
