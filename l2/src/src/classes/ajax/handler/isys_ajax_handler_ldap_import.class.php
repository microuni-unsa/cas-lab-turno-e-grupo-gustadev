<?php

use idoit\Context\Context;

/**
 * AJAX
 *
 * @package     i-doit
 * @subpackage  General
 * @author      Van Quyen Hoang <qhoang@i-doit.org>
 * @version     1.0
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       0.9.9-9
 */
class isys_ajax_handler_ldap_import extends isys_ajax_handler
{
    /**
     * Init method, which gets called from the framework.
     *
     * @author  Van Quyen Hoang <qhoang@i-doit.org>
     */
    public function init()
    {
        switch ($_GET['func']) {
            case 'filter':
                $this->process_filter();
                break;
            case 'import':
                $this->process_import();
                break;
        }

        // End the request.
        $this->_die();
    }

    public function process_import()
    {
        Context::instance()
            ->setContextTechnical(Context::CONTEXT_LDAP_IMPORT)
            ->setGroup(Context::CONTEXT_GROUP_IMPORT)
            ->setContextCustomer(Context::CONTEXT_LDAP_IMPORT)
            ->setImmutable(true);

        try {
            $database = isys_application::instance()->container->get('database');
            $language = isys_application::instance()->container->get('language');

            $l_dn_array = isys_format_json::decode($_POST['ids']);

            $l_ldap_server_id = $_POST['ldap_server'];
            $l_ldap_dn_string = trim($_POST['ldap_dn']);
            $l_connection_info = null;

            isys_module_ldap::get_logger()->setHandlers([]);

            $ldapLib = (new isys_module_ldap)->get_library_by_id($l_ldap_server_id, $l_connection_info);

            $l_ret = false;

            if ($ldapLib) {
                $cookie = '';
                $ldapImportObject = (new isys_ldap_dao_import_active_directory($database, $ldapLib))->setEntries($l_ldap_dn_string);

                $entries = $ldapImportObject->getData();

                if (!empty($entries)) {
                    switch ($l_connection_info['isys_ldap_directory__const']) {
                        case 'C__LDAP__AD':
                            isys_module_ldap::debug('Using "Active Directory"!');
                            $ldapImportObject = new isys_ldap_dao_import_active_directory($database, $ldapLib);
                            $l_ret = $ldapImportObject->set_root_dn($l_ldap_dn_string)
                                ->set_dn_data($l_dn_array)
                                ->prepare($entries)
                                ->import();
                            break;

                        case 'C__LDAP__OPENLDAP':
                            isys_module_ldap::debug('Using "Open LDAP" ... Sorry, this is currently unsupported.');
                            echo $language->get('LC__MODULE__LDAP__DIRECTORY_UNSUPPORTED');
                            break;

                        case 'C__LDAP__NDS':
                            isys_module_ldap::debug('Using "Novell Directory Services" ... Sorry, this is currently unsupported.');
                            echo $language->get('LC__MODULE__LDAP__DIRECTORY_UNSUPPORTED');
                            break;
                    }
                }

                if (!$l_ret) {
                    echo $language->get('LC__MODULE__LDAP__LDAP_OBJECTS_ERROR_MSG');
                }
            }
        } catch (isys_exception_ldap $e) {
            isys_notify::error($e->getMessage());
        }
    }

    /**
     * Method for processing the LDAP Filter.
     *
     * @return void
     * @throws Exception
     */
    public function process_filter()
    {
        isys_core::send_header('Content-Type', 'application/json');

        $database = isys_application::instance()->container->get('database');

        try {
            $l_ldap_server_id = $_POST['ldap_server'];
            $l_ldap_dn_string = trim($_POST['ldap_dn']);
            $l_connection_info = null;

            $ldapLib = (new isys_module_ldap)->get_library_by_id($l_ldap_server_id, $l_connection_info);

            if ($l_connection_info['isys_ldap_directory__const'] !== 'C__LDAP__AD') {
                return;
            }

            if ($ldapLib) {
                $data = (new isys_ldap_dao_import_active_directory($database, $ldapLib))
                    ->setEntries($l_ldap_dn_string)
                    ->getEntriesFromData();

                if (!empty($data)) {
                    echo isys_format_json::encode($data);
                }
            }
        } catch (isys_exception_ldap $e) {
            isys_notify::error($e->getMessage());
        }

        $this->_die();
    }
}
