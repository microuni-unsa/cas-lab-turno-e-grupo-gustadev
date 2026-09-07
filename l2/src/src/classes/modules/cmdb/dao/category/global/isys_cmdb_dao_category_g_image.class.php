<?php

use idoit\Component\Helper\Purify;
use idoit\Component\Property\Exception\UnsupportedConfigurationTypeException;
use idoit\Component\Property\Type\CommentaryProperty;
use idoit\Component\Property\Type\DialogDataProperty;
use idoit\Component\Property\Type\UploadProperty;
use idoit\Context\Context;

/**
 * i-doit
 *
 * DAO: global category for object images
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_g_image extends isys_cmdb_dao_category_global
{
    /**
     * @param isys_component_database $p_db
     */
    public function __construct(isys_component_database $p_db)
    {
        $this->m_category = 'image';

        parent::__construct($p_db);

        $this->categoryTitle = 'LC__CMDB__CATG__IMAGE';
        $this->m_is_purgable = true;
    }

    /**
     * Callback method for property assigned_variant.
     *
     * @return  array
     */
    public function callback_property_image_selection()
    {
        $l_uploadedImages = [];
        $imagesDirectoryPath = isys_application::instance()->getImageDir();
        if (file_exists($imagesDirectoryPath) && is_dir($imagesDirectoryPath)) {
            $l_directory = dir($imagesDirectoryPath);
            while ($l_file = $l_directory->read()) {
                if (strpos($l_file, '.') !== 0) {
                    if (is_dir($imagesDirectoryPath . $l_file)) {
                        $subDirectory = dir($imagesDirectoryPath . $l_file);
                        while ($subDirFile = $subDirectory->read()) {
                            if (strpos($l_file, '.') !== 0 && trim($subDirFile, '.') !== '') {
                                $l_uploadedImages[basename($subDirFile)] = $subDirFile;
                            }
                        }
                    } else {
                        $l_uploadedImages[basename($l_file)] = $l_file;
                    }
                }
            }
        }

        return $l_uploadedImages;
    }

    /**
     * Method for returning the properties.
     *
     * @return array
     * @throws UnsupportedConfigurationTypeException
     */
    protected function properties()
    {
        // @see ID-11242 Set this in order to pass 'SyncMerger' condition to record changes.
        $saveViaGuiContext = in_array(
            Context::instance()->getContextTechnical(),
            [Context::CONTEXT_DAO_UPDATE, Context::CONTEXT_DAO_CREATE],
            true,
        );

        return [
            'image_selection' => (new DialogDataProperty(
                'C__CATG__IMAGE_SELECTION',
                'LC__CMDB__CATG__IMAGE_UPLOADED_IMAGES',
                'isys_catg_image_list__image_link',
                'isys_catg_image_list',
                new isys_callback(['isys_cmdb_dao_category_g_image', 'callback_property_image_selection']),
            ))->mergePropertyData([
                C__PROPERTY__DATA__TYPE => C__TYPE__JSON, // @see API-52
            ])->mergePropertyProvides([
                C__PROPERTY__PROVIDES__REPORT     => false,
                C__PROPERTY__PROVIDES__LIST       => false,
                C__PROPERTY__PROVIDES__VALIDATION => false,
                C__PROPERTY__PROVIDES__EXPORT     => $saveViaGuiContext,
                C__PROPERTY__PROVIDES__IMPORT     => $saveViaGuiContext,
                C__PROPERTY__PROVIDES__SEARCH     => false,
                C__PROPERTY__PROVIDES__MULTIEDIT  => true,
                C__PROPERTY__PROVIDES__VIRTUAL    => false,
            ]),
            'image'           => (new UploadProperty(
                'C__CATG__IMAGE_UPLOAD',
                'LC__CMDB__CATG__IMAGE_OBJ_FILE',
                'isys_catg_image_list__image_link',
                'isys_catg_image_list',
                ['isys_export_helper', 'object_image'],
            ))->mergePropertyProvides([
                C__PROPERTY__PROVIDES__REPORT    => false,
                C__PROPERTY__PROVIDES__LIST      => false,
                C__PROPERTY__PROVIDES__MULTIEDIT => false,
                C__PROPERTY__PROVIDES__VIRTUAL   => true,
            ]),
            'description'     => new CommentaryProperty(
                'C__CMDB__CAT__COMMENTARY_' . $this->m_cat_type . $this->m_category_id,
                'isys_catg_image_list__description',
                'isys_catg_image_list',
            ),
        ];
    }

    /**
     * Sync-method.
     *
     * @param   array   $p_category_data
     * @param   integer $p_object_id
     * @param   integer $p_status
     *
     * @return  boolean
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_image = $p_category_data['properties']['image_selection'][C__DATA__VALUE];

            if (isset($p_category_data['properties']['image'][C__DATA__VALUE])) {
                if (empty($p_category_data['properties']['image']['file_name'])) {
                    // Mapping of allowed mime types to file extension.
                    $allowedMimeTypes = isys_helper::get_image_mimetypes();

                    // Create intermediate image file path
                    $imageFilePath = isys_application::instance()->getImageDir() . 'tempImage-' . uniqid();

                    // Create temporary image file to detect mime type
                    isys_file_put_contents(
                        $imageFilePath,
                        base64_decode($p_category_data['properties']['image'][C__DATA__VALUE])
                    );

                    // Detect mime type
                    $imageExtension = array_search(mime_content_type($imageFilePath), $allowedMimeTypes);

                    // Check whether mime type is allowed and supported
                    if (is_string($imageExtension)) {
                        // Build image file path
                        $l_image = self::prepareFilename((int)$p_object_id, 'object image.' . $imageExtension);

                        $subDirName = isys_application::instance()->getOrCreateUploadImageDir($l_image);

                        // Rename file based on the default naming schema
                        rename($imageFilePath, $subDirName . $l_image);
                    } else {
                        // Unsupported mime type detected: remove image file
                        unlink($imageFilePath);
                    }
                } else {
                    if (!is_string($l_image) || strlen(trim($l_image)) === 0) {
                        if (($l_file_extension = pathinfo($p_category_data['properties']['image'][C__DATA__VALUE], PATHINFO_EXTENSION))) {
                            $l_image = 'object image.' . $l_file_extension;
                        } else {
                            $l_image = 'object image.jpg';
                        }
                    }

                    $l_image = self::prepareFilename((int)$p_object_id, $l_image);
                    $newFileDir = isys_application::instance()->getOrCreateUploadImageDir($l_image);

                    // Source file exists already
                    $prevFilePath = isys_application::instance()->getUploadImagePath($p_category_data['properties']['image']['file_name']);
                    if (file_exists($prevFilePath)) {
                        isys_file_put_contents(
                            $newFileDir . $l_image,
                            file_get_contents($prevFilePath)
                        );
                    } else {
                        // This works only if C__DATA__VALUE is really a base64_encoded string
                        isys_file_put_contents($newFileDir . $l_image, base64_decode($p_category_data['properties']['image'][C__DATA__VALUE]));
                    }
                }
            }

            switch ($p_status) {
                case isys_import_handler_cmdb::C__CREATE:
                    if ($p_object_id > 0) {
                        return $this->create($p_object_id, $l_image, $p_category_data['properties']['description'][C__DATA__VALUE]);
                    }
                    break;
                case isys_import_handler_cmdb::C__UPDATE:
                    if ($p_category_data['data_id'] > 0) {
                        $this->save($p_category_data['data_id'], $l_image, $p_category_data['properties']['description'][C__DATA__VALUE]);

                        return $p_category_data['data_id'];
                    }
                    break;
            }
        }

        return false;
    }

    /**
     * Gets the image name from database by obeject id.
     *
     * @param int $objectId
     *
     * @return string|null
     */
    public function get_image_name_by_object_id($objectId): ?string
    {
        $objectId = $this->convert_sql_id($objectId);

        $query = "SELECT isys_catg_image_list__image_link AS image
			FROM isys_catg_image_list
			WHERE isys_catg_image_list__isys_obj__id = {$objectId}
			LIMIT 1;";

        return $this->retrieve($query)->get_row_value('image');
    }

    /**
     * Delete method.
     *
     * @param   integer $p_id
     *
     * @return  boolean
     */
    public function delete($p_id = null, $p_image_file = null)
    {
        if ($p_id === null && $p_image_file === null) {
            return false;
        }

        $l_file_name = [];

        $l_sql = 'DELETE FROM isys_catg_image_list';

        if ($p_id !== null) {
            $l_data = $this->get_data($p_id)
                ->__to_array();
            $l_file_name[$l_data['isys_catg_image_list__image_link']] = $l_data['isys_catg_image_list__image_link'];

            $l_sql .= ' WHERE (isys_catg_image_list__id = ' . $this->convert_sql_id($p_id) . ');';
        }

        if ($p_image_file !== null) {
            // Check if other objects have this image
            $l_file_name[$p_image_file] = $p_image_file;

            $l_sql = 'SELECT isys_catg_image_list__id FROM isys_catg_image_list ' . 'WHERE isys_catg_image_list__image_link = ' . $this->convert_sql_text($p_image_file) . ';';

            $l_amount = $this->retrieve($l_sql)
                ->num_rows();

            if ($l_amount == 0) {
                foreach ($l_file_name as $l_file) {
                    if (empty($l_file)) {
                        continue;
                    }

                    $fullPath = isys_application::instance()->getUploadImagePath($l_file);

                    if (file_exists($fullPath)) {
                        unlink($fullPath);

                        // @see  ID-7928  Delete the directory, if it's empty.
                        $directory = dirname($fullPath);

                        if (count(glob($directory . '/*')) === 0) {
                            rmdir($directory);
                        }
                    }
                }

                return true;
            }
        }

        if ($this->update($l_sql)) {
            return $this->apply_update();
        }

        return false;
    }

    /**
     * @param string $identifier
     *
     * @return string|null
     * @throws Exception
     */
    public static function getDownloadPath(string $identifier): ?string
    {
        // Identifier string contains the object ID.
        $fileName = self::instance(isys_application::instance()->container->get('database'))->get_image_name_by_object_id((int)$identifier);

        if ($fileName === null) {
            return null;
        }

        return isys_application::instance()->getOrCreateUploadImageDir($fileName) . $fileName;
    }

    /**
     * Save global category image element.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @throws  isys_exception_dao_cmdb
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_data_by_object($_GET[C__CMDB__GET__OBJECT])
            ->__to_array();

        $p_intOldRecStatus = $l_catdata["isys_catg_image_list__status"];
        $this->m_object_id = $_GET[C__CMDB__GET__OBJECT];
        $l_image_file = null;

        if (!empty($_POST['C__CATG__IMAGE_SELECTION']) && $_POST['C__CATG__IMAGE_SELECTION'] != '-1' && $_FILES['C__CATG__IMAGE_UPLOAD']['name'] == '') {
            $l_image_file = basename($_POST['C__CATG__IMAGE_SELECTION']);
        }

        if ($l_catdata['isys_catg_image_list__id'] != "") {
            $this->save($l_catdata["isys_catg_image_list__id"], $l_image_file, $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);
            $this->m_strLogbookSQL = $this->get_last_query();
        } else {
            $l_id = $this->create($this->m_object_id, $l_image_file, $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()]);

            if ($l_id != false) {
                $this->m_strLogbookSQL = $this->get_last_query();
            }
        }

        return null;
    }

    /**
     * Updates the current image.
     *
     * @param   integer $p_cat_level
     * @param   string  $p_link
     * @param   string  $p_description
     *
     * @return  boolean
     */
    public function save($p_cat_level, $p_link, $p_description)
    {
        $l_strSql = "UPDATE isys_catg_image_list SET
			isys_catg_image_list__description = " . $this->convert_sql_text($p_description) . ",
			isys_catg_image_list__image_link  = " . $this->convert_sql_text($p_link) . ",
			isys_catg_image_list__status = " . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . "
			WHERE isys_catg_image_list__id = " . $this->convert_sql_id($p_cat_level) . ";";

        return ($this->update($l_strSql) && $this->apply_update());
    }

    /**
     * Creates a new image.
     *
     * @param   int    $p_object_id
     * @param   string $p_link
     * @param   string $p_description
     *
     * @return  mixed   The newly created ID (integer) or boolean false.
     */
    public function create($p_object_id, $p_link, $p_description)
    {
        $l_id = $this->create_connector('isys_catg_image_list', $p_object_id);
        if ($this->save($l_id, $p_link, $p_description)) {
            return $l_id;
        }

        return false;
    }

    /**
     * @param int    $objectId
     * @param string $filename
     * @return string
     */
    public static function prepareFilename(int $objectId, string $filename): string
    {
        $timestamp = time();

        return Purify::formatFilename("{$objectId}__{$timestamp}__{$filename}");
    }
}
