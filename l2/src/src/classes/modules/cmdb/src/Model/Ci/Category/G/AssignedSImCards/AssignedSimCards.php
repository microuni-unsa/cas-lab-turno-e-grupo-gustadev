<?php

namespace idoit\Module\Cmdb\Model\Ci\Category\G\AssignedSImCards;

use idoit\Module\Cmdb\Model\Ci\Category\AbstractProperty;
use idoit\Module\Cmdb\Model\Ci\Category\SecondListPropertyInterface;
use isys_application;
use isys_cmdb_dao_category_g_assigned_sim_cards;
use isys_cmdb_dao_category_g_cards;
use isys_format_json;

class AssignedSimCards extends AbstractProperty implements SecondListPropertyInterface
{
    /**
     * @var string
     */
    protected static string $className = 'isys_cmdb_dao_category_g_assigned_sim_cards';

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
        $objId = $_GET[C__CMDB__GET__OBJECT] ?? $parameters[C__CMDB__GET__OBJECT] ?? null;

        $cardsDao = isys_cmdb_dao_category_g_cards::instance(isys_application::instance()->container->get('database'));
        $result = $cardsDao->get_data(null, $objId, '', null, C__RECORD_STATUS__NORMAL);
        $return = [];

        if ($result->num_rows() > 0) {
            $language = isys_application::instance()->container->get('language');
            while ($row = $result->get_row()) {
                $return[] = [
                    '__checkbox__'                              => $row["isys_catg_cards_list__id"],
                    $language->get('LC__CMDB__CATG__CARDS__TITLE') => $row["isys_catg_cards_list__title"]
                ];
            }
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

        if (isset($parameters['preselection'])) {
            $preselection = isys_format_json::is_json_array($parameters['preselection'])? isys_format_json::decode($parameters['preselection']): $parameters['preselection'];

            $dao = isys_cmdb_dao_category_g_cards::instance(isys_application::instance()->container->get('database'));
            $result = $dao->get_data(null, null, 'AND isys_catg_cards_list.isys_catg_cards_list__id IN (' . (is_array($preselection) ? implode(',', $preselection): $preselection) . ')');
        } else {
            $objId = (!empty($_GET[C__CMDB__GET__OBJECT])) ? $_GET[C__CMDB__GET__OBJECT] : $parameters[C__CMDB__GET__OBJECT];

            // Create this class, because we can't just use "this" or we'll get an exception "Database component not loaded!".
            $l_dao = isys_cmdb_dao_category_g_assigned_sim_cards::instance(isys_application::instance()->container->get('database'));
            $result = $l_dao->get_data(null, $objId, '', null, C__RECORD_STATUS__NORMAL);
        }

        while ($row = $result->get_row()) {
            // @see  ID-6220  Also return a 'first' selection.
            $return['first'][] = (int)$row['isys_catg_cards_list__isys_obj__id'];

            $return['second'][] = [
                $row['isys_catg_cards_list__id'],
                $row['isys_catg_cards_list__title']
            ];
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

        if (is_array($parameters['dataIds']) && count($parameters['dataIds'])) {
            $dao = isys_cmdb_dao_category_g_cards::instance(isys_application::instance()->container->get('database'));
            foreach ($parameters['dataIds'] as $dataId) {
                $categoryRow = $dao->get_data($dataId)->get_row();

                $preselection[] = [
                    $categoryRow['isys_catg_cards_list__id'],
                    $categoryRow['isys_catg_cards_list__title']
                ];
            }
        }

        return [
            'header' => [
                '__checkbox__',
                isys_application::instance()->container->get('language')->get('LC__CMDB__CATG__CARDS__TITLE')
            ],
            'data' => $preselection
        ];
    }
}
