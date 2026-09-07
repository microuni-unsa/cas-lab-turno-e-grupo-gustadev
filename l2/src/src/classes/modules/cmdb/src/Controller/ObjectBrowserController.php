<?php

namespace idoit\Module\Cmdb\Controller;

use Exception;
use isys_application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 * Object browser relevant controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ObjectBrowserController
{
    public function getMetaData(Request $request): JsonResponse
    {
        $language = isys_application::instance()->container->get('language');
        $objectIds = array_slice(array_unique(array_map('intval', explode(',', $request->query->get('objects')))), 0, 20);

        try {
            if (count($objectIds) === 0) {
                throw new Exception($language->get('LC__UNIVERSAL__NO_OBJECTS_FOUND'));
            }

            $metaData = [];
            $objectList = implode(', ', $objectIds);

            $query = "SELECT isys_obj_type__title AS objectTypeTitle, isys_obj__title AS objectTitle
                FROM isys_obj
                INNER JOIN isys_obj_type ON isys_obj_type__id = isys_obj__isys_obj_type__id
                WHERE isys_obj__id IN ({$objectList})";

            $result = isys_application::instance()->container->get('cmdb_dao')->retrieve($query);

            while ($row = $result->get_row()) {
                $metaData[] = [
                    'objectTypeTitle' => $language->get($row['objectTypeTitle']),
                    'objectTitle' => $row['objectTitle']
                ];
            }

            $data = [
                'success' => true,
                'data'    => $metaData,
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
