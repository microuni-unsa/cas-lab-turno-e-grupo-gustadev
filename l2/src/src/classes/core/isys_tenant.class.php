<?php

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_tenant
{
    public string $cache_dir = '';
    public string $database = '';
    public string $description = '';
    public int $id;
    public string $name = '';

    /**
     * @param string $p_name
     * @param string $p_description
     * @param int    $p_id
     * @param string $p_database
     * @param string $p_cache_dir
     */
    public function __construct($p_name, $p_description, $p_id, $p_database, $p_cache_dir)
    {
        $this->name = $p_name;
        $this->description = $p_description;
        $this->id = (int)$p_id;
        $this->database = $p_database;
        $this->cache_dir = $p_cache_dir;
    }
}
