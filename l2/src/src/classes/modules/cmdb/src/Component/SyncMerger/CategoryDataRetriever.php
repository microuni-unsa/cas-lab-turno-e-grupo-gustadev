<?php

namespace idoit\Module\Cmdb\Component\SyncMerger;

use isys_cmdb_dao_category_g_custom_fields;
use isys_request;

class CategoryDataRetriever
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $categoryData = [];

    /**
     * @var int
     */
    private $count = 0;

    /**
     * @var isys_request[]
     */
    private static $requestObjects = [];

    /**
     * CategoryDataRetriever constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param Config $config
     *
     * @return CategoryDataRetriever
     */
    public static function instance(Config $config)
    {
        $instance = new self($config);
        $instance->setData();
        $instance->setRequest();
        return $instance;
    }

    /**
     * @param null $dataId
     *
     * @return array
     */
    public function getCategoryData($dataId = null)
    {
        if ($dataId !== null) {
            // @see  ID-8643  We cast to array because line 129 ("$result->get_row();") might result in "null".
            return (array)$this->categoryData[$dataId];
        }

        return $this->categoryData;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @return void
     */
    private function setRequest()
    {
        if ($this->count === 0) {
            return;
        }

        foreach ($this->getCategoryData() as $dataSetId => $categoryData) {
            $requestObject = new isys_request($categoryData);
            $requestObject->set_category_data_id($dataSetId);
            $requestObject->set_object_id($this->config->getObjectId());
            self::$requestObjects[$dataSetId] = $requestObject;
        }
    }

    /**
     * @param $dataId
     *
     * @return isys_request|null
     */
    public function getRequestObject($dataId = null)
    {
        if ($dataId !== null) {
            return self::$requestObjects[$dataId];
        }

        return null;
    }

    /**
     * Sets category data for the current data id, if its a single value category and data id is not set use object id to retrieve category data
     *
     * @return void
     */
    private function setData()
    {
        $dataSet = [];
        $objectId = $this->config->getObjectId();
        $dataId = $this->config->getDataId();
        $dao = $this->config->getCategoryDao();

        if ($dataId === null && !empty($objectId)
            && $this->config->isMultivalueCategory() === false
        ) {
            if ($dao instanceof isys_cmdb_dao_category_g_custom_fields) {
                $dataId = $dao->get_data_id_by_object_id($dao->get_catg_custom_id(), $objectId) ?? null;
            } else {
                $table = $dao->get_table();
                $dataId = $dao->get_data(null, $objectId)->get_row()[$table . '__id'] ?? null;
            }
        }

        if ($dataId === null) {
            $this->count = 0;
            $this->config->setDataId(null);
            $this->categoryData = [];

            return;
        }

        $result = $dao->get_data($dataId);
        if (is_countable($result) && count($result) > 0) {
            if ($dao instanceof isys_cmdb_dao_category_g_custom_fields) {
                $config = $dao->get_config($dao->get_catg_custom_id());
                while ($currentData = $result->get_row()) {
                    if (empty($dataSet)) {
                        $dataSet[$dataId] = $currentData;
                    }
                    $key = $currentData['isys_catg_custom_fields_list__field_type'] ===
                    'commentary' ? 'description' : $currentData['isys_catg_custom_fields_list__field_type'] . '_' .
                        $currentData['isys_catg_custom_fields_list__field_key'];

                    $isMultiselection = $config[$currentData['isys_catg_custom_fields_list__field_key']]['multiselection'] ?? false;

                    if ($isMultiselection) {
                        $dataSet[$dataId][$key][] = $currentData['isys_catg_custom_fields_list__field_content'];
                        continue;
                    }

                    if (isset($dataSet[$dataId][$key])) {
                        if (!is_array($dataSet[$dataId][$key])) {
                            $dataSet[$dataId][$key] = [$dataSet[$dataId][$key]];
                        }
                        $dataSet[$dataId][$key][] = $currentData['isys_catg_custom_fields_list__field_content'];
                        continue;
                    }
                    $dataSet[$dataId][$key] = $currentData['isys_catg_custom_fields_list__field_content'];
                }
            } else {
                $dataSet[$dataId] = $result->get_row();
            }
        }

        $this->categoryData = $dataSet;
        $this->count = count($dataSet);
        $this->config->setDataId($dataId);
    }
}
