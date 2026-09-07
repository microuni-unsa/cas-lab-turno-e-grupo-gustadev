<?php

namespace idoit\Module\Cmdb\Controller;

use idoit\Component\Helper\Unserialize;
use isys_application;
use isys_cmdb_dao_category;
use isys_cmdb_dao_category_g_custom_fields;
use isys_exception_api_validation;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * CMDB relevant validation controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class ValidationController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function validateValue(Request $request): Response
    {
        try {
            $response = [
                'success' => true,
                'data'    => $this->validate(
                    $request->request->get('identifier'),
                    trim($request->request->get('value')),
                    (int)$request->request->get('objectTypeId'),
                    (int)$request->request->get('objectId'),
                    (int)$request->request->get('customCategoryId'),
                    (int)$request->request->get('categoryEntryId')
                ),
                'message' => ''
            ];
        } catch (Throwable $e) {
            $response = [
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * @param string $identifier
     * @param string $value
     * @param int    $objectTypeId
     * @param int    $objectId
     * @param int    $customCategoryId
     * @param int    $categoryEntryId
     *
     * @return string
     * @throws isys_exception_api_validation
     */
    private function validate(string $identifier, string $value, int $objectTypeId, int $objectId, int $customCategoryId, int $categoryEntryId): string
    {
        [$className, $propertyKey] = explode('::', $identifier);

        if (!class_exists($className) || !is_a($className, isys_cmdb_dao_category::class, true)) {
            return 'DAO class ("' . $className . '") could not be found';
        }

        $dao = $className::instance(isys_application::instance()->container->get('database'));

        if (!method_exists($dao, 'validate')) {
            return '';
        }

        if ($dao instanceof isys_cmdb_dao_category_g_custom_fields) {
            if ($customCategoryId <= 0) {
                $customCategoryId = $this->getCustomCategoryId($dao, $propertyKey);
            }

            if ($customCategoryId > 0) {
                $dao->set_catg_custom_id($customCategoryId);
            }
        }

        // This might happen on the overview-page.
        if (str_starts_with($propertyKey, 'C_')) {
            $l_properties = $dao->get_properties();

            foreach ($l_properties as $l_key => $l_property) {
                if ($l_property[C__PROPERTY__UI][C__PROPERTY__UI__ID] == $propertyKey) {
                    $propertyKey = $l_key;

                    break;
                }
            }
        }

        $l_validated = $dao->set_object_id($objectId)
            ->set_list_id($categoryEntryId)
            ->set_object_type_id($objectTypeId)
            ->validate([$propertyKey => $value]);

        if (is_array($l_validated)) {
            throw new isys_exception_api_validation($l_validated[$propertyKey], $l_validated);
        }

        return '';
    }

    /**
     * @param isys_cmdb_dao_category_g_custom_fields $dao
     * @param string                                 $propertyKey
     *
     * @return int|null
     * @throws \isys_exception_database
     */
    private function getCustomCategoryId(isys_cmdb_dao_category_g_custom_fields $dao, string $propertyKey): ?int
    {
        // @ID-9825 The custom category ID was not passed - let's find it...
        $result = $dao->retrieve('SELECT isysgui_catg_custom__id AS id, isysgui_catg_custom__config AS config FROM isysgui_catg_custom;');

        while ($row = $result->get_row()) {
            $categoryConfig = Unserialize::toArray($row['config']);

            foreach ($categoryConfig as $fieldKey => $fieldConfig) {
                if ($fieldConfig['type'] . '_' . $fieldKey === $propertyKey) {
                    return (int)$row['id'];
                }
            }
        }

        return null;
    }
}
