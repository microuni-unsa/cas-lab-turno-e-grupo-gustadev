<?php

namespace idoit\Module\Cmdb\Controller;

use isys_application;
use isys_cmdb_dao_category_g_global;
use isys_component_signalcollection;
use isys_event_manager;
use isys_exception_cmdb;
use isys_helper;
use isys_helper_link;
use isys_module_templates;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * CMDB relevant object controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ObjectController
{
    /**
     * This controller is a workaround for the OLD object-controller so that the URLs still work.
     *
     * @param Request $request
     * @param int     $objectId
     *
     * @return RedirectResponse
     */
    public function redirectToObject(Request $request, int $objectId): Response
    {
        $url = isys_helper_link::create_url([C__CMDB__GET__OBJECT => $objectId], true);

        return new RedirectResponse($url);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function createObject(Request $request): JsonResponse
    {
        $language = isys_application::instance()->container->get('language');
        $objectTypeId = (int)$request->request->get('object-type-id', 0);
        $objectTitle = isys_helper::sanitize_text($request->request->get('object-title', ''));

        try {
            if (trim($objectTitle) === '') {
                throw new isys_exception_cmdb($language->get('LC__CMDB__OBJECT_BROWSER__NOTIFY__NO_OBJECT_TITLE'));
            }

            if ($objectTypeId <= 0) {
                throw new isys_exception_cmdb($language->get('LC__CMDB__OBJECT_BROWSER__NOTIFY__NO_OBJECT_TYPE'));
            }

            $cmdbDao = isys_application::instance()->container->get('cmdb_dao');
            $database = isys_application::instance()->container->get('database');

            $defaultTemplateId = $request->request->get('use-default-template') ? $cmdbDao->get_default_template_by_obj_type($objectTypeId) : null;

            if (is_numeric($defaultTemplateId) && $defaultTemplateId > 0) {
                $objectId = (new isys_module_templates())->create_from_template([$defaultTemplateId], $objectTypeId, $objectTitle, null, false);
            } else {
                $objectId = $cmdbDao->insert_new_obj($objectTypeId, null, $objectTitle, null, C__RECORD_STATUS__NORMAL);

                $entryId = (int)$cmdbDao
                    ->retrieve("SELECT isys_catg_global_list__id FROM isys_catg_global_list WHERE isys_catg_global_list__isys_obj__id = {$objectId} LIMIT 1;")
                    ->get_row_value('isys_catg_global_list__id');

                // Emit category signal afterCategoryEntrySave
                isys_component_signalcollection::get_instance()
                    ->emit(
                        'mod.cmdb.afterCategoryEntrySave',
                        isys_cmdb_dao_category_g_global::instance($database),
                        $entryId,
                        ($entryId > 0),
                        $objectId,
                        $request->request->all(),
                        []
                    );
            }

            isys_event_manager::getInstance()
                ->triggerCMDBEvent(
                    'C__LOGBOOK_EVENT__OBJECT_CREATED',
                    '-object initialized-',
                    $objectId,
                    $objectTypeId,
                    null,
                    serialize([
                        'isys_cmdb_dao_category_g_global::title' => [
                            'from' => '',
                            'to'   => $objectTitle
                        ]
                    ])
                );

            $data = [
                'success' => true,
                'data'    => $objectId,
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
