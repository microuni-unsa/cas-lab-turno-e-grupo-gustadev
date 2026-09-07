<?php

use idoit\Component\FeatureManager\FeatureCheckInterface;
use idoit\Component\FeatureManager\FeatureManager;

/**
 * i-doit
 *
 * DAO: global category for the ticketing connector.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Selcuk Kekec <skekec@synetics.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_virtual_tickets extends isys_cmdb_dao_category_g_virtual implements FeatureCheckInterface
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var  string
     */
    protected $m_category = 'virtual_tickets';

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        return [];
    }

    /**
     * @return bool
     */
    public static function isFeatureEnabled(): bool
    {
        return FeatureManager::isFeatureActive('tts-category');
    }
}
