<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\LdevClient;

use idoit\Module\Cmdb\Model\Ci\Category\AbstractProperty;
use idoit\Module\Cmdb\Model\Ci\Category\SecondListPropertyInterface;
use isys_application;
use isys_cmdb_dao_category_g_ldevclient;
use isys_cmdb_dao_category_g_sanpool;
use isys_format_json;

class AssignedLdevServer extends AbstractProperty implements SecondListPropertyInterface
{
    /**
     * @var string
     */
    protected static string $className = 'isys_cmdb_dao_category_g_sanpool';

    /**
     * @var string
     */
    protected static string $method = 'object_browser';

    /**
     * @param array|null $parameters
     *
     * @return string
     */
    public static function requestData(?array $parameters = []): string
    {
        $dao = isys_cmdb_dao_category_g_sanpool::instance(isys_application::instance()->container->get('database'));
        $language = isys_application::instance()->container->get('language');
        $return = [];

        $result = $dao->get_ldevserver_by_obj_id_or_ldev_id($_GET[C__CMDB__GET__OBJECT]);

        while ($row = $result->get_row()) {
            $return[] = [
                '__checkbox__'                          => $row["isys_catg_sanpool_list__id"],
                $language->get('LC__CATG__ODEP_OBJ')    => $row["isys_catg_sanpool_list__title"],
                $language->get('LC__CATD__SANPOOL_LUN') => $row['isys_catg_sanpool_list__lun'],
                $language->get('LC__CMDB__OBJTYPE')     => $language->get($row["isys_obj_type__title"])
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
        // Preselection
        $return = [
            'category' => [],
            'first'    => [],
            'second'   => []
        ];

        $dao = isys_cmdb_dao_category_g_sanpool::instance(isys_application::instance()->container->get('database'));

        // Save a bit memory: Only select needed fields!
        $query = "SELECT *
                    FROM isys_catg_sanpool_list AS san
                    LEFT JOIN isys_obj AS obj ON san.isys_catg_sanpool_list__isys_obj__id = obj.isys_obj__id
                    WHERE san.isys_catg_sanpool_list__id = " . $dao->convert_sql_id($parameters['preselection']) . "
                    LIMIT 1;";

        $row = $dao->retrieve($query)->get_row();

        // @see  ID-6220  Also return a 'first' selection.
        $l_preselection['first'][] = $row['isys_obj__id'];

        $return['second'] = [
            $row['isys_catg_sanpool_list__id'],
            $row['isys_catg_sanpool_list__title'],
            $row['isys_catg_sanpool_list__lun'],
            $row['?'],
        ];

        return $return;
    }

    /**
     * @param array|null $parameters
     *
     * @return array
     */
    public static function preselectionData(?array $parameters = []): array
    {
        // @see  ID-5688  New callback case.
        $preselection = [];
        $daoLdevClient = isys_cmdb_dao_category_g_ldevclient::instance(isys_application::instance()->container->get('database'));
        $dao = isys_cmdb_dao_category_g_sanpool::instance(isys_application::instance()->container->get('database'));
        $language = isys_application::instance()->container->get('language');

        if (is_array($parameters['dataIds']) && count($parameters['dataIds'])) {
            foreach ($parameters['dataIds'] as $dataId) {
                $categoryRow = $dao->get_data($dataId)->get_row();

                $preselection[] = [
                    $categoryRow['isys_catg_sanpool_list__id'],
                    $categoryRow['isys_obj__title'],
                    $language->get($daoLdevClient->get_objtype_name_by_id_as_string($categoryRow['isys_obj__isys_obj_type__id'])),
                    $categoryRow['isys_catg_sanpool_list__title'],
                    $categoryRow['isys_catg_sanpool_list__lun']
                ];
            }
        }

        return [
            'header' => [
                '__checkbox__',
                $language->get('LC__UNIVERSAL__OBJECT_TITLE'),
                $language->get('LC__UNIVERSAL__OBJECT_TYPE'),
                $language->get('LC__CATG__ODEP_OBJ'),
                $language->get('LC__CATD__SANPOOL_LUN')
            ],
            'data'   => $preselection
        ];
    }
}
