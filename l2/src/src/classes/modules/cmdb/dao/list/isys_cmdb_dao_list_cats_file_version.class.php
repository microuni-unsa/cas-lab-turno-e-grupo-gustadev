<?php

/**
 * i-doit
 *
 * DAO: ObjectType list for manuals
 *
 * @package     i-doit
 * @subpackage  CMDB_Category_lists
 * @author      Niclas Potthast <npotthast@i-doit.org>
 * @author      Leonard Fischer <lfischer@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_list_cats_file_version extends isys_component_dao_category_table_list
{
    /**
     * Return category constant.
     *
     * @return  integer
     */
    public function get_category()
    {
        return defined_or_default('C__CATS__FILE_VERSIONS');
    }

    /**
     * Return category type constant.
     *
     * @return  integer
     */
    public function get_category_type()
    {
        return C__CMDB__CATEGORY__TYPE_SPECIFIC;
    }

    /**
     * Modify row method.
     *
     * @param   array &$p_arrRow
     *
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function modify_row(&$p_arrRow)
    {
        $language = isys_application::instance()->container->get('language');
        $dao = isys_application::instance()->container->get('cmdb_dao');

        $p_arrRow['isys_file_size'] = isys_tenantsettings::get('gui.empty_value', '-');
        $l_filepath = $p_arrRow["isys_file_physical__filename"] ? isys_application::instance()->getUploadFilePath($p_arrRow["isys_file_physical__filename"]) : '';

        if ($l_filepath && file_exists($l_filepath)) {
            $l_filesize = filesize($l_filepath);

            if ($l_filesize > 0) {
                $downloadUrl = isys_application::instance()->container->get('route_generator')
                    ->generate('system.file-download', ['type' => 'cmdb.global-file-category', 'identifier' => $p_arrRow["isys_file_version__isys_file_physical__id"]]);

                $p_arrRow['isys_download'] = '<a target="_blank" href="' . $downloadUrl . '">' .
                    '<img src="' . isys_application::instance()->www_path . 'images/axialis/basic/symbol-download.svg" alt="" class="vam" />' .
                    '<span class="ml5 vam">' . $language->get('LC__UNIVERSAL__DOWNLOAD_FILE') . '</span>' .
                    '</a>';

                if ($l_filesize < 100000) {
                    $p_arrRow['isys_file_size'] = isys_convert::memory($l_filesize, 'C__MEMORY_UNIT__KB', C__CONVERT_DIRECTION__BACKWARD);
                    $p_arrRow['isys_file_size'] = isys_convert::formatNumber($p_arrRow['isys_file_size']) . ' ' . $language->get('LC__CMDB__MEMORY_UNIT__KB');
                } else {
                    $p_arrRow['isys_file_size'] = isys_convert::memory($l_filesize, 'C__MEMORY_UNIT__MB', C__CONVERT_DIRECTION__BACKWARD);
                    $p_arrRow['isys_file_size'] = isys_convert::formatNumber($p_arrRow['isys_file_size']) . ' ' . $language->get('LC__CMDB__MEMORY_UNIT__MB');
                }
            }
        }

        // Formatting the upload-date.
        $p_arrRow["isys_file_physical__date_uploaded"] = isys_application::instance()->container->get('locales')
            ->fmt_date($p_arrRow["isys_file_physical__date_uploaded"]);

        if (!empty($p_arrRow['isys_file_physical__filename'])) {
            $p_arrRow['directory'] = basename(isys_application::instance()->getOrCreateUploadFileDir($p_arrRow['isys_file_physical__filename']));
        }

        if ($p_arrRow['isys_file_physical__user_id_uploaded']) {
            $uploadedBy = $dao->get_object($p_arrRow['isys_file_physical__user_id_uploaded'])->get_row();

            $p_arrRow['isys_file_physical__user_id_uploaded'] = "{$uploadedBy['isys_obj_type__title']} &raquo; {$uploadedBy['isys_obj__title']} {{$uploadedBy['isys_obj__id']}}";
        }
    }

    /**
     *
     * @return  array
     */
    public function get_fields()
    {
        return [
            'isys_file_version__title'              => 'LC__CMDB__CATS__FILE_TITLE',
            'isys_file_version__description'        => 'LC__CMDB__CATS__FILE_VERSION_DESCRIPTION',
            'isys_file_physical__filename_original' => 'LC__CMDB__CATS__FILE_NAME',
            'isys_file_physical__md5'               => 'LC__CMDB__CATS__FILE_MD5',
            'directory'                             => 'LC__CMDB__CATS__FILE_DIRECTORY',
            'isys_file_version__revision'           => 'LC__CMDB__CATS__FILE_REVISION',
            'isys_file_physical__user_id_uploaded'  => 'LC__CMDB__CATS__FILE_UPLOAD_FROM',
            'isys_file_physical__date_uploaded'     => 'LC__CMDB__CATS__FILE_UPLOAD_DATE',
            'isys_file_size'                        => 'LC__CMDB__CATS__FILE__SIZE',
            'isys_download'                         => 'LC__CMDB__CATS__FILE_DOWNLOAD',
            'isys_file_version__description'        => 'LC__CMDB__CATS__FILE_VERSION_DESCRIPTION'
        ];
    }

    /**
     * The isys_component_dao_object_table_list constructor differentiates if $p_cat is an instance of isys_cmdb_dao_category or isys_component database.
     *
     * @param  isys_component_database $p_db
     */
    public function __construct($p_db)
    {
        $this->set_rec_status_list(false);
        parent::__construct($p_db);
    }
}
