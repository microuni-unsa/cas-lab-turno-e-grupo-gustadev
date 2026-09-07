<?php

namespace idoit\Module\Cmdb\Controller\Category;

use isys_ajax_handler_quick_info;
use isys_application;
use isys_cmdb_dao_category_s_net;
use isys_component_template_language_manager;
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
class NetController
{
    private isys_cmdb_dao_category_s_net $netDao;
    private isys_component_template_language_manager $language;

    public function __construct()
    {
        $database = isys_application::instance()->container->get('database');

        $this->netDao = isys_cmdb_dao_category_s_net::instance($database);
        $this->language = isys_application::instance()->container->get('language');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function checkNetCollision(Request $request): JsonResponse
    {
        $json = [
            'success' => true,
            'data' => null,
            'message' => '',
        ];

        try {
            $from = $request->request->get('from');
            $to = $request->request->get('to');
            $networkType = (int)$request->request->get('networkType', defined_or_default('C__CATS_NET_TYPE__IPV4'));
            $objectId = (int)$request->request->get('objectId');

            $data = null;
            $result = $this->netDao->find_net_collision($from, $to, $networkType, $objectId);

            if (count($result)) {
                $quickinfo = new isys_ajax_handler_quick_info();

                $data = [
                    'html' => [],
                    'plain' => []
                ];

                while ($l_row = $result->get_row()) {
                    $objectTypeTitle = $this->language->get($l_row['isys_obj_type__title']);
                    $objectTitle = $this->language->get($l_row['isys_obj__title']);
                    $address = $l_row['isys_cats_net_list__address'];
                    $suffix = $l_row['isys_cats_net_list__cidr_suffix'];

                    $data['html'][] = $quickinfo->get_quick_info(
                        $l_row['isys_obj__id'],
                        "{$objectTypeTitle} &raquo; {$objectTitle}",
                        C__LINK__OBJECT
                    );
                    $data['plain'][] = "{$objectTypeTitle} » {$objectTitle}: {$address} / {$suffix}";
                }
            }

            $json['data'] = $data;
        } catch (Throwable $e) {
            $json['success'] = false;
            $json['message'] = $e->getMessage();
        }

        return new JsonResponse($json);
    }

    /**
     * @param Request $request
     * @param int     $objectId
     * @return JsonResponse
     */
    public function getNetInformation(Request $request, int $objectId): JsonResponse
    {
        $json = [
            'success' => true,
            'data' => null,
            'message' => '',
        ];

        try {
            $json['data'] = $this->netDao->get_all_net_information_by_obj_id($objectId);
        } catch (Throwable $e) {
            $json['success'] = false;
            $json['message'] = $e->getMessage();
        }

        return new JsonResponse($json);
    }
}
