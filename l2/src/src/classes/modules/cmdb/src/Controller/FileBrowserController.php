<?php

namespace idoit\Module\Cmdb\Controller;

use isys_application;
use isys_auth;
use isys_cmdb_dao;
use isys_cmdb_dao_dialog;
use isys_component_dao_result;
use isys_component_database;
use isys_component_session;
use isys_component_template;
use isys_component_template_language_manager;
use isys_convert;
use isys_module_cmdb;
use isys_tenantsettings;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * File browser controller.
 *
 * @package   Modules
 * @copyright synetics GmbH
 * @license   http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class FileBrowserController
{
    private isys_cmdb_dao $cmdbDao;

    private isys_component_database $database;

    private isys_component_template_language_manager $language;

    private isys_component_session $session;

    private isys_component_template $template;

    public function __construct()
    {
        $this->cmdbDao = isys_application::instance()->container->get('cmdb_dao');
        $this->database = isys_application::instance()->container->get('database');
        $this->language = isys_application::instance()->container->get('language');
        $this->session = isys_application::instance()->container->get('session');
        $this->template = isys_application::instance()->container->get('template');
    }

    public function getCategories(Request $request): Response
    {
        $this->session->write_close();

        try {
            $categories = isys_cmdb_dao_dialog::instance($this->database)
                ->set_table('isys_file_category')
                ->get_data();

            // Reduce the data to the necessary information.
            $categories = array_map(fn ($item) => [
                'id'    => (int)$item['isys_file_category__id'],
                'title' => $item['isys_file_category__title']
            ], $categories ?? []);

            // Sort it.
            usort($categories, fn ($a, $b) => strnatcasecmp($a['title'], $b['title']));

            $response = [
                'success' => true,
                'data'    => array_values($categories),
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
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function getFiles(Request $request): Response
    {
        $this->session->write_close();

        try {
            $files = [];
            $fileResult = $this->retrieveFiles(
                (int)$request->get('category'),
                $request->query->get('order-by', 'fileName'),
                $request->query->get('order-direction', 'asc') == 'desc'
            );
            $useAuth = (bool)isys_tenantsettings::get('auth.use-in-file-browser', false);
            $auth = isys_module_cmdb::getAuth();
            $hidden = '[' . $this->language->get('LC__UNIVERSAL__HIDDEN') . ']';

            while ($fileRow = $fileResult->get_row()) {
                $allowedToSeeFile = true;
                $allowedToSeePerson = true;

                $fileRow['objectId'] = (int)$fileRow['objectId'];
                $fileRow['uploadUserObjectId'] = (int)$fileRow['uploadUserObjectId'];
                $fileRow['uploadUserObjectTypeId'] = (int)$fileRow['uploadUserObjectTypeId'];

                if ($useAuth && !$auth->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $fileRow['objectId'])) {
                    $allowedToSeeFile = false;
                    $allowedToSeePerson = $auth->is_allowed_to(isys_auth::VIEW, 'OBJ_ID/' . $fileRow['uploadUserObjectId']);
                }

                $fileRow['categoryTitle'] = $this->language->get($fileRow['categoryTitle']);
                $fileRow['fileName'] = $allowedToSeeFile ? $fileRow['fileName'] : $hidden;
                $fileRow['objectTitle'] = $allowedToSeeFile ? $fileRow['objectTitle'] : $hidden;
                $fileRow['uploadUserObjectTitle'] = $allowedToSeePerson ? $this->language->get($fileRow['uploadUserObjectTitle']) : $hidden;
                $fileRow['uploadUserObjectTypeTitle'] = $allowedToSeePerson ? $this->language->get($fileRow['uploadUserObjectTypeTitle']) : $hidden;

                $files[] = $fileRow;
            }

            $response = [
                'success' => true,
                'data'    => $files,
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
     * @param int    $category
     * @param string $orderBy
     * @param bool   $orderDescending
     *
     * @return isys_component_dao_result
     * @throws \isys_exception_database
     */
    private function retrieveFiles(int $category, string $orderBy, bool $orderDescending): isys_component_dao_result
    {
        $recordStatusNormal = $this->cmdbDao->convert_sql_int(C__RECORD_STATUS__NORMAL);

        $categoryCondition = $category !== 0 ? 'AND isys_cats_file_list__isys_file_category__id = ' . $this->cmdbDao->convert_sql_id($category) : '';

        $orderMap = [
            'fileName'                  => 'physical.isys_file_physical__filename_original',
            'objectId'                  => 'fileObject.isys_obj__id',
            'objectTitle'               => 'fileObject.isys_obj__title',
            'categoryId'                => 'category.isys_file_category__id',
            'categoryTitle'             => 'category.isys_file_category__title',
            'uploadUserObjectId'        => 'userObject.isys_obj__id',
            'uploadUserObjectTitle'     => 'userObject.isys_obj__title',
            'uploadUserObjectTypeId'    => 'userObjectType.isys_obj_type__id',
            'uploadUserObjectTypeTitle' => 'userObjectType.isys_obj_type__title',
            'revision'                  => 'version.isys_file_version__revision',
            'uploadDate'                => 'physical.isys_file_physical__date_uploaded'
        ];

        $orderBy = $orderMap[$orderBy] ?? 'physical.isys_file_physical__filename_original';
        $order = $orderDescending === true ? 'DESC' : 'ASC';
        $selectMap = implode(', ', array_map(fn ($alias, $field) => "{$field} AS {$alias}", array_keys($orderMap), array_values($orderMap)));

        $query = "SELECT {$selectMap}
            FROM isys_cats_file_list
            INNER JOIN isys_file_version AS version ON version.isys_file_version__id = isys_cats_file_list__isys_file_version__id
            INNER JOIN isys_file_physical AS physical ON physical.isys_file_physical__id = version.isys_file_version__isys_file_physical__id
            INNER JOIN isys_obj AS fileObject ON isys_cats_file_list__isys_obj__id = fileObject.isys_obj__id
            INNER JOIN isys_obj AS userObject ON physical.isys_file_physical__user_id_uploaded = userObject.isys_obj__id
            INNER JOIN isys_obj_type AS userObjectType ON userObject.isys_obj__isys_obj_type__id = userObjectType.isys_obj_type__id
            LEFT JOIN isys_file_category AS category ON category.isys_file_category__id = isys_cats_file_list__isys_file_category__id
            WHERE isys_cats_file_list__status = {$recordStatusNormal}
            AND fileObject.isys_obj__status = {$recordStatusNormal}
            {$categoryCondition}
            GROUP BY isys_cats_file_list__id
            ORDER BY {$orderBy} {$order};";

        return $this->cmdbDao->retrieve($query);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \SmartyException
     */
    public function getUploadModal(Request $request): Response
    {
        $this->session->write_close();

        $maxUploadBytes = max([
            isys_convert::to_bytes(ini_get('upload_max_filesize')),
            isys_convert::to_bytes(ini_get('post_max_size'))
        ]);

        $rules = [
            'selected-file'          => [
                'p_bReadonly'       => true,
                'disableInputGroup' => true,
                'p_bInfoIconSpacer' => 0,
                'p_strClass'        => 'input-block',
            ],
            'selected-file-name'     => [
                'p_strClass'        => 'input-block',
                'p_bInfoIconSpacer' => 0,
            ],
            'selected-file-category' => [
                'p_strClass'        => 'input-block',
                'p_bInfoIconSpacer' => 0,
                'p_strTable'        => 'isys_file_category',
                'p_strSelectedID'   => (int)$request->query->get('selectedCategory')
            ]
        ];

        $templateContent = $this->template->activate_editmode()
            ->assign('uploadMaxSize', isys_convert::memory($maxUploadBytes, 'C__MEMORY_UNIT__MB', C__CONVERT_DIRECTION__BACKWARD) . ' MB')
            ->smarty_tom_add_rules('tom.popup.upload-modal', $rules)
            ->fetch(isys_module_cmdb::getPath() . 'templates/modal/upload-modal.tpl');

        return new Response($templateContent);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws \Exception
     */
    public function getSuggestion(Request $request): Response
    {
        $this->session->write_close();

        try {
            $results = [];

            $searchTerm = $this->cmdbDao->convert_sql_text('%' . trim($request->request->get('search')) . '%');
            $recordStatusNormal = $this->cmdbDao->convert_sql_int(C__RECORD_STATUS__NORMAL);

            $query = "SELECT
                    physical.isys_file_physical__filename_original AS 'fileName',
                    fileObject.isys_obj__id AS 'objectId',
                    fileObject.isys_obj__title AS 'objectTitle',
                    fileObjectType.isys_obj_type__title AS 'objectTypeTitle'
                FROM isys_cats_file_list
                INNER JOIN isys_file_version AS version ON version.isys_file_version__id = isys_cats_file_list__isys_file_version__id
                INNER JOIN isys_file_physical AS physical ON physical.isys_file_physical__id = version.isys_file_version__isys_file_physical__id
                INNER JOIN isys_obj AS fileObject ON isys_cats_file_list__isys_obj__id = fileObject.isys_obj__id
                INNER JOIN isys_obj_type AS fileObjectType ON fileObject.isys_obj__isys_obj_type__id = fileObjectType.isys_obj_type__id
                WHERE isys_cats_file_list__status = {$recordStatusNormal}
                AND fileObject.isys_obj__status = {$recordStatusNormal}
                AND (fileObject.isys_obj__title LIKE {$searchTerm} OR physical.isys_file_physical__filename_original LIKE {$searchTerm})
                GROUP BY isys_cats_file_list__id;";

            $result = $this->cmdbDao->retrieve($query);

            while ($row = $result->get_row()) {
                $results[$row['objectId']] = $this->language->get($row['objectTypeTitle']) . ' &raquo; ' . $row['objectTitle'] . ' (' . $row['fileName'] . ')';
            }

            return new JsonResponse([
                'success' => true,
                'data'    => $results,
                'message' => ''
            ]);
        } catch (Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'data'    => null,
                'message' => $e->getMessage()
            ]);
        }
    }
}
