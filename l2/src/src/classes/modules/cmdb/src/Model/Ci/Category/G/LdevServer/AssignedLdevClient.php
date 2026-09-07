<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\LdevServer;

use idoit\Module\Cmdb\Model\Ci\Category\AbstractProperty;
use idoit\Module\Cmdb\Model\Ci\Category\SecondListPropertyInterface;
use isys_application;
use isys_cmdb_dao_category_g_ldevclient;
use isys_cmdb_dao_category_g_sanpool;
use isys_format_json;

class AssignedLdevClient extends AbstractProperty implements SecondListPropertyInterface
{
    /**
     * @var string
     */
    protected static string $className = 'isys_cmdb_dao_category_g_sanpool';

    /**
     * @var string
     */
    protected static string $method = 'object_browser2';

    /**
     * @param array|null $parameters
     *
     * @return string
     */
    public static function requestData(?array $parameters = []): string
    {
        // Handle Ajax-Request.
        $dao = isys_cmdb_dao_category_g_sanpool::instance(isys_application::instance()->container->get('database'));
        $language = isys_application::instance()->container->get('language');
        $return = [];

        $result = $dao->get_ldevclients_by_obj($_GET[C__CMDB__GET__OBJECT]);

        while ($row = $result->get_row()) {
            $return[] = [
                '__checkbox__'                  => $row['isys_catg_ldevclient_list__id'],
                $language->get('LC__CATG__ODEP_OBJ') => $row["isys_catg_ldevclient_list__title"],
            ];
        }

        return isys_format_json::encode($return);
    }

    /**
     * @param array|null $parameters
     *
     * @return array
     */
    public static function preparationData(?array $parameters = []): array
    {
        $return = [
            'category' => [],
            'first'    => [],
            'second'   => []
        ];
        $language = isys_application::instance()->container->get('language');
        $dao = isys_application::instance()->container->get('cmdb_dao');

        if (!empty($parameters['preselection'])) {
            if (isys_format_json::is_json_array($parameters['preselection'])) {
                $l_ids = isys_format_json::decode($parameters['preselection']);
            } else {
                $l_ids = explode(',', $parameters['preselection']);
            }

            // Save a bit memory: Only select needed fields!
            $query = "SELECT isys_obj__id, isys_catg_ldevclient_list__id, isys_catg_ldevclient_list__title, isys_obj__title
						FROM isys_catg_relation_list AS rl
						LEFT JOIN isys_catg_ldevclient_list AS ldev ON ldev.isys_catg_ldevclient_list__isys_catg_relation_list__id = rl.isys_catg_relation_list__id
						LEFT JOIN isys_obj AS obj ON rl.isys_catg_relation_list__isys_obj__id__slave = obj.isys_obj__id
						WHERE isys_catg_ldevclient_list__id " . $dao->prepare_in_condition($l_ids);

            $result = $dao->retrieve($query);

            while ($row = $result->get_row()) {
                $return['first'][] = $row['isys_obj__id'];

                $return['second'][] = [
                    $row['isys_catg_ldevclient_list__id'],
                    $row['isys_obj__title'] . ' >> ' . $row['isys_catg_ldevclient_list__title'],
                    $language->get('LC__CMDB__OBJTYPE__RELATION'),
                    $language->get('LC__CMDB__OBJTYPE__RELATION'),
                ];
            }
        }

        return $return;
    }

    /**
     * @param array|null $parameters
     *
     * @return array
     */
    public static function preselectionData(?array $parameters = []): array
    {
        $preselection = [];
        $language = isys_application::instance()->container->get('language');

        if (is_array($parameters['dataIds']) && count($parameters['dataIds'])) {
            $daoLdevClient = isys_cmdb_dao_category_g_ldevclient::instance(isys_application::instance()->container->get('database'));

            foreach ($parameters['dataIds'] as $dataId) {
                $categoryRow = $daoLdevClient
                    ->get_data($dataId)
                    ->get_row();

                $preselection[] = [
                    $categoryRow['isys_catg_port_list__id'],
                    $categoryRow['isys_obj__title'],
                    $language->get($daoLdevClient->get_objtype_name_by_id_as_string($categoryRow['isys_obj__isys_obj_type__title'])),
                    $categoryRow['isys_catg_ldevclient_list__title']
                ];
            }
        }

        return [
            'header' => [
                '__checkbox__',
                $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                $language->get('LC__UNIVERSAL__OBJECT_TYPE'),
                $language->get('LC__CATG__ODEP_OBJ')
            ],
            'data'   => $preselection
        ];
    }

}
