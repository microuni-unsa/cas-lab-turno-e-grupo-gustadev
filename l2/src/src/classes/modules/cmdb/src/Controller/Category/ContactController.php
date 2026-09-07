<?php

namespace idoit\Module\Cmdb\Controller\Category;

use isys_application;
use isys_auth;
use isys_auth_cmdb;
use isys_cmdb_dao_category_g_contact;
use isys_event_manager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * CMDB contact category controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ContactController
{
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function changeRole(Request $request): JsonResponse
    {
        $language = isys_application::instance()->container->get('language');
        $database = isys_application::instance()->container->get('database');

        try {
            $objectId = (int)$request->request->get('object-id');
            $entryId = (int)$request->request->get('entry-id');
            $roleId = (int)$request->request->get('role-id');

            isys_auth_cmdb::instance()
                ->check_rights_obj_and_category(isys_auth::EDIT, $objectId, 'C__CATG__CONTACT');

            isys_cmdb_dao_category_g_contact::instance($database)
                ->save_contact_tag($entryId, $roleId);

            $data = [
                'success' => true,
                'data'    => null,
                'message' => $language->get('LC__CATG__CONTACT_HAS_BEEN_UPDATED')
            ];
        } catch (Throwable $e) {
            $data = [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }

        return new JsonResponse($data);
    }
    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function changePrimary(Request $request): JsonResponse
    {
        $language = isys_application::instance()->container->get('language');
        $database = isys_application::instance()->container->get('database');

        try {
            $objectId = (int)$request->request->get('object-id');
            $entryId = (int)$request->request->get('entry-id');

            isys_auth_cmdb::instance()->check_rights_obj_and_category(isys_auth::EDIT, $objectId, 'C__CATG__CONTACT');

            $daoContact = isys_cmdb_dao_category_g_contact::instance($database);

            $fromContact = '';
            $toContact = '';

            $primaryContactId = $daoContact
                ->get_data(null, $objectId, ' AND isys_catg_contact_list__primary_contact = 1 ')
                ->get_row_value('isys_connection__isys_obj__id');

            if ($primaryContactId !== null) {
                $primaryContact = $daoContact->get_object($primaryContactId)->get_row();
                $fromContact = $language->get_in_text("{$primaryContact['isys_obj_type__title']} &raquo; {$primaryContact['isys_obj__title']}");
            }

            $isPrimary = false;

            if (!$daoContact->is_primary($entryId)) {
                $daoContact->make_primary($objectId, $entryId);
                $isPrimary = true;

                $newPrimaryContactId = $daoContact
                    ->get_data($entryId)
                    ->get_row_value('isys_connection__isys_obj__id');

                if ($newPrimaryContactId !== null) {
                    $newPrimaryContact = $daoContact->get_object($newPrimaryContactId)->get_row();
                    $toContact = $language->get_in_text("{$newPrimaryContact['isys_obj_type__title']} &raquo; {$newPrimaryContact['isys_obj__title']}");
                }
            } else {
                $daoContact->reset_primary($objectId);
            }

            // @see ID-9456 Record the previous and the new primary contact.
            isys_event_manager::getInstance()
                ->triggerCMDBEvent(
                    'C__LOGBOOK_EVENT__CATEGORY_CHANGED',
                    $daoContact->get_strLogbookSQL(),
                    $objectId,
                    $daoContact->get_objTypeID($objectId),
                    'LC__CMDB__CATG__CONTACT',
                    serialize([
                        'isys_cmdb_dao_category_g_contact::primary_contact' => [
                            'from' => $fromContact,
                            'to' => $toContact
                        ]
                    ])
                );

            $daoContact->object_changed($objectId);

            $data = [
                'success' => true,
                'data'    => ['isPrimary' => $isPrimary],
                'message' => ''
            ];
        } catch (Throwable $e) {
            $data = [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }

        return new JsonResponse($data);
    }
}
