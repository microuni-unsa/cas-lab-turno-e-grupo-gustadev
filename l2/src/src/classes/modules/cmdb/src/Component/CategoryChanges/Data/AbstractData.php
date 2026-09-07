<?php

namespace idoit\Module\Cmdb\Component\CategoryChanges\Data;

/**
 * Class AbstractData
 *
 * @package idoit\Module\Cmdb\Component\CategoryChanges\Data
 */
abstract class AbstractData
{
    /**
     * @var mixed
     */
    protected $data;

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     *
     * @return static
     */
    protected function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return static
     */
    public function resetData()
    {
        $this->data = [];
        return $this;
    }

    /**
     * @param mixed $changesData
     *
     * return static
     */
    public function addData($changesData)
    {
        $this->data[] = $changesData;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasData()
    {
        return !empty($this->data);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function hasKey($key = '')
    {
        return isset($this->data[$key]);
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    public function getByKey($key)
    {
        return $this->data[$key];
    }
}
