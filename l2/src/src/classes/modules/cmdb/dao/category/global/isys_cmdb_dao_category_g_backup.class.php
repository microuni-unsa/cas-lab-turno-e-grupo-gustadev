<?php

use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogPlusProperty;
use idoit\Component\Property\Type\ObjectBrowserConnectionProperty;
use idoit\Component\Property\Type\TextProperty;

/**
 * i-doit
 *
 * DAO: global category for backup.
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_backup extends isys_cmdb_dao_category_global
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        // @see ID-11486 This needs to be set BEFORE calling the parent constructor.
        $this->m_category = 'backup';
        $this->m_multivalued = true;

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATG__BACKUP';
        $this->m_connected_object_id_field = 'isys_connection__isys_obj__id';
        $this->m_has_relation = true;
        $this->m_object_id_field = 'isys_catg_backup_list__isys_obj__id';
    }

    /**
     * Method for retrieving category properties.
     *
     * @return  array
     */
    public function properties()
    {
        return [
            'title'        => new TextProperty(
                'C__CATG__BACKUP_TITLE',
                'LC__CATD__TITLE',
                'isys_catg_backup_list__title',
                'isys_catg_backup_list'
            ),
            'backup'       => (new ObjectBrowserConnectionProperty(
                'C__CATG__BACKUP__ASSIGNED_OBJECT',
                'LC__CMDB__CATG__BACKUP__IS_BACKUPEP',
                'isys_catg_backup_list__isys_connection__id',
                'isys_catg_backup_list'
            ))->setPropertyDataRelationType(defined_or_default('C__RELATION_TYPE__BACKUP'))
                ->setPropertyDataRelationHandler(new isys_callback(
                    ['isys_cmdb_dao_category_g_backup', 'callback_property_relation_handler'],
                    ['isys_cmdb_dao_category_g_backup', true]
                )),
            'backup_type'  => (new DialogPlusProperty(
                'C__CATG__BACKUP__TYPE',
                'LC__CMDB__CATG__BACKUP__BACKUP_TYPE',
                'isys_catg_backup_list__isys_backup_type__id',
                'isys_catg_backup_list',
                'isys_backup_type'
            ))->mergePropertyUiParams([
                'p_onChange' => 'idoit.callbackManager.triggerCallback(\'backup__show_path_to_save\', this.value);'
            ]),
            'cycle'        => new DialogPlusProperty(
                'C__CATG__BACKUP__CYCLE',
                'LC__CMDB__CATG__BACKUP__CYCLE',
                'isys_catg_backup_list__isys_backup_cycle__id',
                'isys_catg_backup_list',
                'isys_backup_cycle'
            ),
            'path_to_save' => new TextProperty(
                'C__CATG__BACKUP__PATH_TO_SAVE',
                'LC__CMDB__CATG__BACKUP__PATH_TO_SAVE',
                'isys_catg_backup_list__path_to_save',
                'isys_catg_backup_list'
            ),
            'description'  => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_GLOBAL . defined_or_default('C__CATG__BACKUP', 'C__CATG__BACKUP'),
                'isys_catg_backup_list__description',
                'isys_catg_backup_list'
            )
        ];
    }

    /**
     * @inheritDoc
     */
    public function create_data($data)
    {
        if ($data['backup_type'] != defined_or_default('C__CMDB__BACKUP_TYPE__FILE')) {
            $data['path_to_save'] = null;
        }

        $connectedObjectId = (int)$data['backup'];
        $data['backup'] = isys_cmdb_dao_connection::instance($this->get_database_component())->add_connection($connectedObjectId);

        $entryId = parent::create_data($data);

        isys_cmdb_dao_category_g_relation::instance($this->get_database_component())->handle_relation(
            $entryId,
            'isys_catg_backup_list',
            defined_or_default('C__RELATION_TYPE__BACKUP'),
            null,
            $connectedObjectId,
            $data['isys_obj__id']
        );

        return $entryId;
    }

    /**
     * @inheritDoc
     */
    public function save_data($entryId, $data)
    {
        if ($data['backup_type'] != defined_or_default('C__CMDB__BACKUP_TYPE__FILE')) {
            $data['path_to_save'] = null;
        }

        $currentData = $this->get_data($entryId)->get_row();

        $connectedObjectId = (int)$data['backup'];
        $data['backup'] = $this->handle_connection($entryId, $connectedObjectId);

        $success = parent::save_data($entryId, $data);

        isys_cmdb_dao_category_g_relation::instance($this->get_database_component())->handle_relation(
            $entryId,
            'isys_catg_backup_list',
            defined_or_default('C__RELATION_TYPE__BACKUP'),
            $currentData['isys_catg_backup_list__isys_catg_relation_list__id'],
            $connectedObjectId,
            $data['isys_obj__id']
        );

        return $success;
    }
}
