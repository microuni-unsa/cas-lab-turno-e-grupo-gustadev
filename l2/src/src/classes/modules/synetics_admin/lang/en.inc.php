<?php
/**
 * i-doit
 * "Admin Center 2.0" language file
 *
 * @package   synetics_admin
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

return [
    'LC__SYNETICS_ADMIN'                                                           => 'Add-on & Subscription Center',
    'LC__SYNETICS_ADMIN__ERROR__GENERAL_HEADER'                                    => 'An error has occurred',
    'LC__SYNETICS_ADMIN__ERROR__GENERAL_MESSAGE'                                   => 'There was an issue during communication with the Add-on & Subscription Center: ',
    'LC__SYNETICS_ADMIN__ERROR__PERMISSION_HEADER'                                 => 'You are not allowed to access',
    'LC__SYNETICS_ADMIN__ERROR__PERMISSION_MESSAGE'                                => 'You do not have necessary permissions to access this page.<br />Please ask your administrator for required rights and try again.',
    'LC__SYNETICS_ADMIN__ERROR__SYSTEM_OFFLINE_HEADER'                             => 'System is offline',
    'LC__SYNETICS_ADMIN__ERROR__SYSTEM_OFFLINE_MESSAGE'                            => 'You system is currently not connected to the internet.<br />Check your internet connection and refresh the page to try again.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_HEADER'                      => 'License token missing',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE'                     => 'In order to access the Add-on & Subscription Center you need a valid license token.<br />Please type your license token in the field below:',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE_SUFFIX'              => 'Don’t have a license token?',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE_REQUEST_A_TOKEN'     => 'Where can I find my license token?',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE_REQUEST_A_TOKEN_URL' => 'https://kb.i-doit.com/en/system-administration/reset-token.html',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_FIELD_PLACEHOLDER'           => 'Please enter your license token...',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR'                               => 'License token',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_NO_PERMISSION'                 => 'You are not allowed to set a license token.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_GENERIC'                       => 'Unable to set license token.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_EMPTY'                         => 'License token is empty.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_INVALID'                       => 'Given license token is invalid and should consists only of alphanumeric characters.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_UNABLE_TO_CONNECT'             => 'Unable to connect to license server.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_UNABLE_TO_AUTHENTICATE'        => 'Unable to authenticate against license server. Please check your license token!',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_UNABLE_TO_FIND'                => 'Unable to find any referenced licenses.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_ALREADY_EXISTS'                => 'License already exists.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_INVALID_LICENSE'               => 'Invalid license found.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_PARSE'                         => 'Unable to parse licenses.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_UNKNOWN'                       => 'An unknown error occured.',
    'LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_SAVE'                                => 'Save',
    'LC__SYNETICS_ADMIN__ERROR__PORTAL_CONNECTION_FAILED_HEADER'                   => 'Connection failed',
    'LC__SYNETICS_ADMIN__ERROR__PORTAL_CONNECTION_FAILED_MESSAGE'                  => 'The connection failed. The exact error message is "%s". Please verify that your system is online and can connect to "%s". Please check if your proxy configuration is correct, if needed.',
    'LC__SYNETICS_ADMIN__ERROR__PORTAL_AUTH_FAILED_HEADER'                         => 'Access denied',
    'LC__SYNETICS_ADMIN__ERROR__PORTAL_AUTH_FAILED_MESSAGE'                        => 'Your license token does not reference an active contract. Please check your license token and ensure its validity or enter a new license token below:',
    'LC__SYNETICS_ADMIN__ERROR__PORTAL_AUTH_FAILED_TRIAL_MESSAGE'                  => 'Please note that the access to this page is not possible during trial of i-doit.',

    'LC__SYNETICS_ADMIN__AUTH__OPEN_ADMIN_CENTER' => 'Open Add-on & Subscription Center',
    'LC__SYNETICS_ADMIN__AUTH__MANAGE_ADDONS'     => 'Manage add-ons',
    'LC__SYNETICS_ADMIN__AUTH__MANAGE_LICENSE'    => 'Manage license',
    'LC__SYNETICS_ADMIN__AUTH__ADDON_INFO'        => 'View add-on information',

    'LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_INSTALL'         => 'You are not allowed to install add-ons.',
    'LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_INSTALL_CLOUD'   => 'You can not install add-ons in cloud environment.',
    'LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_UNINSTALL'       => 'You are not allowed to uninstall add-ons.',
    'LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_UNINSTALL_CLOUD' => 'You can not uninstall add-ons in cloud environment.',
    'LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_MANAGE'          => 'You are not allowed to manage add-ons.',
    'LC__SYNETICS_ADMIN__AUTH__ADDON__CAN_NOT_MANAGE_CLOUD'    => 'You can not manage add-ons in cloud environment.',

    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_HEADER'    => 'New i-doit feature',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_A' => 'Explore our new Add-on & Subscription Center',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_B' => 'In our new Add-on & Subscription Center, you can:',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_C' => 'Access your subscription data and invoices',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_D' => 'Perform on-the-fly i-doit updates',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_E' => 'Manage your add-ons',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_F' => 'Get more add-ons to customize your i-doit experience',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_G' => 'Find help and support options',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_BUTTON'    => 'Explore our Add-on & Subscription Center',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_LATER'     => 'Later',
    'LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS'                  => 'Explore the new "Add-on & Subscription Center" for better subscription and add-on management.',
];
