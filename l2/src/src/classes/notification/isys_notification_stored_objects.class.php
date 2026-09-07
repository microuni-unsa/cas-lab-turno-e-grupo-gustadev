<?php

/**
 * i-doit
 *
 * Notification: Count objects by their CMDB status
 *
 * @package     i-doit
 * @subpackage  Notifications
 * @author      Benjamin Heisig <bheisig@i-doit.org>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_notification_stored_objects extends isys_notification_count_objects_by_cmdb_status
{
    /**
     * Initiates notification.
     *
     * @param array $p_type Information about this notification type
     */
    public function init($p_type)
    {
        $cmdbStatusStored = 0;

        if (defined('C__CMDB_STATUS__STORED')) {
            $cmdbStatusStored = (int)constant('C__CMDB_STATUS__STORED');
        }

        if (!defined('C__CMDB_STATUS__STORED')) {
            $cmdbStatusStored = (int)isys_cmdb_dao_status::instance($this->m_db)
                ->get_cmdb_status_by_const('C__CMDB_STATUS__STORED')
                ->get_row_value('isys_cmdb_status__id');
        }

        if (!$cmdbStatusStored) {
            throw new isys_exception_cmdb('The "stored" CMDB status could not be found, please be sure that the CMDB status exists and uses the constant "C__CMDB_STATUS__STORED".');
        }

        $this->m_cmdb_status = $cmdbStatusStored;

        parent::init($p_type);
    }
}
