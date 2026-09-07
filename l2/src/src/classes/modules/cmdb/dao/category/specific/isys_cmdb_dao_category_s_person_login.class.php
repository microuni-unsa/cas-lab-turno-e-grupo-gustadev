<?php

use idoit\Component\Property\Property;
use idoit\Component\Property\Type\DateProperty;
use idoit\Component\Property\Type\DynamicProperty;

/**
 * i-doit
 *
 * DAO: specific category for person logins
 *
 * @package     i-doit
 * @subpackage  CMDB_Categories
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_cmdb_dao_category_s_person_login extends isys_cmdb_dao_category_s_person
{
    /**
     * Category's name. Will be used for the identifier, constant, main table, and many more.
     *
     * @var   string
     */
    protected $m_category = 'person_login';

    /**
     * Category's talbe.
     *
     * @var     string
     * @fixme   No standard behavior!
     */
    protected $m_table = 'isys_cats_person_list';

    /**
     * Method for saving new user-login data.
     *
     * @param   integer $p_id
     * @param   string  $p_username
     * @param   string  $p_pass
     * @param string $emailAddress
     * @param   string  $p_description
     * @param   integer $p_status
     * @param   boolean $p_generate_md5
     * @param   integer $disabledLogin
     *
     * @return  boolean
     */
    public function save(
        $p_id,
        $p_username,
        $p_pass,
        $emailAddress,
        $p_description,
        $p_status = C__RECORD_STATUS__NORMAL,
        $p_generate_md5 = true,
        $disabledLogin = 0,
        $is_password_migration = false
    ) {
        return parent::save_login($p_id, $p_username, $p_pass, $emailAddress, $p_description, $p_status, $p_generate_md5, $disabledLogin, $is_password_migration);
    }

    /**
     * Save specific category monitor.
     *
     * @param   integer $p_cat_level
     * @param   integer &$p_intOldRecStatus
     *
     * @return  mixed
     */
    public function save_element($p_cat_level, &$p_intOldRecStatus)
    {
        $l_catdata = $this->get_general_data();
        $l_bRet = false;

        $p_intOldRecStatus = $l_catdata["isys_cats_person_list__status"];

        $l_list_id = $l_catdata["isys_cats_person_list__id"];

        if (empty($l_list_id)) {
            $l_list_id = $this->create_connector("isys_cats_person_list", $_GET[C__CMDB__GET__OBJECT]);
        }

        $password = '';

        if ($_POST['C__CONTACT__PERSON_PASSWORD__action'] === 'set') {
            $password = $_POST["C__CONTACT__PERSON_PASSWORD"];
        }

        if ($_POST['C__CONTACT__PERSON_PASSWORD__action'] === 'clear') {
            $password = null;
        }

        if ($l_list_id) {
            $l_bRet = $this->save(
                $l_list_id,
                $_POST["C__CONTACT__PERSON_USER_NAME"],
                $password,
                $_POST["C__CONTACT__PERSON_PASSWORD_RESET_EMAIL"],
                $_POST["C__CMDB__CAT__COMMENTARY_" . $this->get_category_type() . $this->get_category_id()],
                C__RECORD_STATUS__NORMAL,
                true,
                (int) $_POST['C__CONTACT__PERSON__DISABLED_LOGIN']
            );

            $this->m_strLogbookSQL = $this->get_last_query();
        }

        return ($l_bRet == true) ? $l_list_id : -1;
    }

    /**
     * Method for simply changing a persons password.
     *
     * @param   integer $p_cat_id
     * @param   string  $p_password
     *
     * @return  boolean
     * @author  Leonard Fischer <lfischer@i-doit.com>
     */
    public function change_password($p_cat_id, $p_password)
    {
        $password = isys_helper_crypt::encryptPassword($p_password);

        $l_sql = 'UPDATE isys_cats_person_list
			SET isys_cats_person_list__user_pass = ' . $this->convert_sql_text($password) . '
			WHERE isys_cats_person_list__id = ' . $this->convert_sql_id($p_cat_id) . ';';

        return ($this->update($l_sql) && $this->apply_update());
    }

    /**
     * @param int    $entryId
     * @param string $emailAddress
     *
     * @return bool
     * @throws isys_exception_dao
     */
    public function updatePasswordResetEmail(int $entryId, string $emailAddress): bool
    {
        if (trim($emailAddress) !== '' && filter_var($emailAddress, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        $emailAddress = $this->convert_sql_text($emailAddress);

        $updateQuery = "UPDATE isys_cats_person_list
            SET isys_cats_person_list__password_reset_email = {$emailAddress}
            WHERE isys_cats_person_list__id = {$entryId}
            LIMIT 1;";

        return $this->update($updateQuery) && $this->apply_update();
    }

    /**
     * @return array
     */
    public function custom_properties()
    {
        return [];
    }

    /**
     * @param $row
     *
     * @return mixed|string
     * @throws isys_exception_database
     */
    public function showLastLogin($row)
    {
        if (!isset($row['isys_cats_person_list__id']) || (int)isys_tenantsettings::get('logging.user.last-login') === 0) {
            return isys_tenantsettings::get('gui.empty_value', '-');
        }

        $query = 'SELECT isys_cats_person_list__last_login FROM isys_cats_person_list
            WHERE isys_cats_person_list__id = ' . $this->convert_sql_id($row['isys_cats_person_list__id']);
        $lastLogin = $this->retrieve($query)->get_row_value('isys_cats_person_list__last_login');

        return isys_application::instance()->container->get('locales')
                ->fmt_datetime($lastLogin);
    }

    /**
     * @return DynamicProperty[]
     * @throws \idoit\Component\Property\Exception\UnsupportedConfigurationTypeException
     */
    public function dynamic_properties()
    {
        return [
            '_last_login' => new DynamicProperty(
                'LC__CONTACT__PERSON_LAST_LOGIN',
                'isys_cats_person_list__id',
                'isys_cats_person_list',
                [
                    $this,
                    'showLastLogin'
                ]
            )
        ];
    }

    /**
     * Method for returning the properties.
     *
     * @return  array
     */
    protected function properties()
    {
        $lastLogin = (new DateProperty(
            'C__CONTACT__PERSON_LAST_LOGIN',
            'LC__CONTACT__PERSON_LAST_LOGIN',
            'isys_cats_person_list__last_login',
            'isys_cats_person_list',
            true,
            true
        ))->mergePropertyProvides([
            Property::C__PROPERTY__PROVIDES__SEARCH       => false,
            Property::C__PROPERTY__PROVIDES__IMPORT       => false,
            Property::C__PROPERTY__PROVIDES__EXPORT       => false,
            Property::C__PROPERTY__PROVIDES__REPORT       => false,
            Property::C__PROPERTY__PROVIDES__LIST         => true,
            Property::C__PROPERTY__PROVIDES__MULTIEDIT    => false,
            Property::C__PROPERTY__PROVIDES__VALIDATION   => false,
            Property::C__PROPERTY__PROVIDES__VIRTUAL      => true,
            Property::C__PROPERTY__PROVIDES__SEARCH_INDEX => false,
            Property::C__PROPERTY__PROVIDES__FILTERABLE   => false,
        ])->mergePropertyUiParams([
            'p_strClass' => 'input-small',
            'p_strStyle' => 'width:70%;',
        ]);

        $condition = [];
        if ((int) isys_tenantsettings::get('logging.user.last-login', 0) === 0) {
            $condition[] = 'isys_cats_person_list__id = FALSE';
        }

        $select = $lastLogin->getData()->getSelect();
        $select->setSelectCondition(idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory($condition));

        return [
            'disabled_login' => array_replace_recursive(isys_cmdb_dao_category_pattern::dialog(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_LOGIN_DISABLED',
                    C__PROPERTY__INFO__DESCRIPTION => 'LC__CONTACT__PERSON_LOGIN_DISABLED'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD  => 'isys_cats_person_list__disabled_login',
                    C__PROPERTY__DATA__SELECT => idoit\Module\Report\SqlQuery\Structure\SelectSubSelect::factory(
                        'SELECT (CASE WHEN isys_cats_person_list__disabled_login = \'1\' THEN ' .
                        $this->convert_sql_text('LC__UNIVERSAL__YES') . ' WHEN isys_cats_person_list__disabled_login = \'0\' THEN ' . $this->convert_sql_text('LC__UNIVERSAL__NO') . ' END)
                                FROM isys_cats_person_list',
                        'isys_cats_person_list',
                        'isys_cats_person_list__id',
                        'isys_cats_person_list__isys_obj__id',
                        '',
                        '',
                        idoit\Module\Report\SqlQuery\Structure\SelectCondition::factory([]),
                        idoit\Module\Report\SqlQuery\Structure\SelectGroupBy::factory(['isys_cats_person_list__isys_obj__id'])
                    ),
                    C__PROPERTY__DATA__JOIN   => [
                        idoit\Module\Report\SqlQuery\Structure\SelectJoin::factory('isys_cats_person_list', 'LEFT', 'isys_cats_person_list__isys_obj__id', 'isys_obj__id')
                    ]
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON__DISABLED_LOGIN',
                    C__PROPERTY__UI__PARAMS => [
                        'p_arData' => get_smarty_arr_YES_NO()
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false
                ],
                C__PROPERTY__FORMAT   => [
                    C__PROPERTY__FORMAT__CALLBACK => [
                        'isys_export_helper',
                        'get_yes_or_no'
                    ]
                ]
            ]),
            'title'       => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO  => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_USER_NAME',
                    C__PROPERTY__INFO__DESCRIPTION => 'User name'
                ],
                C__PROPERTY__DATA  => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__title'
                ],
                C__PROPERTY__UI    => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON_USER_NAME',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-small'
                    ]
                ],
                C__PROPERTY__CHECK => [
                    C__PROPERTY__CHECK__UNIQUE_GLOBAL => true
                ]
            ]),
            'user_pass'   => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CONTACT__PERSON_PASSWORD',
                    C__PROPERTY__INFO__DESCRIPTION => 'Password'
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__user_pass'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON_PASSWORD',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-small',
                        'p_bReadonly' => true,
                        'hide-set-empty' => true,
                        'p_strPlaceholder' => 'LC__LOGIN__PASSWORD_NOT_SET'
                    ],
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                    C__PROPERTY__PROVIDES__REPORT => false,
                    C__PROPERTY__PROVIDES__LIST   => false,
                    C__PROPERTY__PROVIDES__EXPORT => false
                ]
            ]),
            'email_address' => array_replace_recursive(isys_cmdb_dao_category_pattern::text(), [
                C__PROPERTY__INFO     => [
                    C__PROPERTY__INFO__TITLE => 'LC__CONTACT__PERSON_PASSWORD_RESET_EMAIL',
                ],
                C__PROPERTY__DATA     => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__password_reset_email'
                ],
                C__PROPERTY__UI       => [
                    C__PROPERTY__UI__ID     => 'C__CONTACT__PERSON_PASSWORD_RESET_EMAIL',
                    C__PROPERTY__UI__PARAMS => [
                        'p_strClass' => 'input-small'
                    ],
                ],
                C__PROPERTY__CHECK    => [
                    C__PROPERTY__CHECK__VALIDATION => [
                        FILTER_VALIDATE_EMAIL,
                        []
                    ]
                ],
                C__PROPERTY__PROVIDES => [
                    C__PROPERTY__PROVIDES__SEARCH => false,
                ]
            ]),
            'last_login' => $lastLogin,
            'description' => array_replace_recursive(isys_cmdb_dao_category_pattern::commentary(), [
                C__PROPERTY__INFO => [
                    C__PROPERTY__INFO__TITLE       => 'LC__CMDB__LOGBOOK__DESCRIPTION',
                    C__PROPERTY__INFO__DESCRIPTION => 'Description'
                ],
                C__PROPERTY__DATA => [
                    C__PROPERTY__DATA__FIELD => 'isys_cats_person_list__description'
                ],
                C__PROPERTY__UI   => [
                    C__PROPERTY__UI__ID => 'C__CMDB__CAT__COMMENTARY_' . C__CMDB__CATEGORY__TYPE_SPECIFIC . defined_or_default('C__CATS__PERSON_LOGIN', 'C__CATS__PERSON_LOGIN')
                ]
            ])
        ];
    }

    /**
     * Synchronizes properties from an import with the database.
     *
     * @param   array   $p_category_data Values of category data to be saved.
     * @param   integer $p_object_id     Current object identifier (from database)
     * @param   integer $p_status        Decision whether category data should be created or just updated.
     *
     * @return  mixed  Returns category data identifier (int) on success, true (bool) if nothing had to be done, otherwise false.
     */
    public function sync($p_category_data, $p_object_id, $p_status = 1 /* isys_import_handler_cmdb::C__CREATE */)
    {
        $l_indicator = false;
        if (is_array($p_category_data) && isset($p_category_data['properties'])) {
            $l_data = $this->get_data_by_object($p_object_id);
            if ($p_status === isys_import_handler_cmdb::C__CREATE && $l_data->num_rows() == 0) {
                $p_category_data['data_id'] = $this->create_connector('isys_cats_person_list', $p_object_id);
            } else {
                $l_data = $l_data->__to_array();
                $p_category_data['data_id'] = $l_data['isys_cats_person_list__id'];
            }

            if ($p_status === isys_import_handler_cmdb::C__CREATE || $p_status === isys_import_handler_cmdb::C__UPDATE) {
                // Save category data.
                $l_indicator = $this->save(
                    $p_category_data['data_id'],
                    $p_category_data['properties']['title'][C__DATA__VALUE],
                    $p_category_data['properties']['user_pass'][C__DATA__VALUE],
                    $p_category_data['properties']['email_address'][C__DATA__VALUE],
                    $p_category_data['properties']['description'][C__DATA__VALUE],
                    C__RECORD_STATUS__NORMAL,
                    true,
                    $p_category_data['properties']['disabled_login'][C__DATA__VALUE]
                );
            }
        }

        return ($l_indicator === true) ? $p_category_data['data_id'] : false;
    }

    /**
     * Verifiy posted data, save set_additional_rules and validation state for further usage.
     *
     * @param   array $p_data
     * @param   mixed $p_prepend_table_field
     *
     * @return  boolean|array
     * @author  Leonard Fischer <lfischer@i-doit.org>
     */
    public function validate(array $p_data = [], $p_prepend_table_field = false)
    {
        $l_return = [];

        $l_password_minlength = (int)isys_tenantsettings::get('minlength.login.password', 4);

        // @todo  Please observe if this works correctly for all situations (API, GUI, Import, ...)
        foreach ($p_data as $key => $value) {
            if (is_array($value) && array_key_exists(C__DATA__VALUE, $value)) {
                $value = $value[C__DATA__VALUE];
            }

            $p_data[$key] = $value;
        }
        if (is_string($p_data['user_pass']) && mb_strlen($p_data['user_pass']) < $l_password_minlength && $p_data['user_pass'] != '') {
            $l_return['user_pass'] = isys_application::instance()->container->get('language')
                ->get("LC__LOGIN__SAVE_ERROR", $l_password_minlength);
        }

        // @see ID-10013 Implement custom logic to check if the username is 'unique'.
        if (is_string($p_data['title']) && isset($this->m_object_id) && trim($p_data['title']) !== '' && $this->m_object_id) {
            $objectId = $this->convert_sql_id($this->m_object_id);
            $username = $this->convert_sql_text($p_data['title']);

            // Check if the given username was used in any other object (without checking for status or anything).
            $sql = "SELECT isys_cats_person_list__id
                FROM isys_cats_person_list
                WHERE isys_cats_person_list__isys_obj__id != {$objectId}
                AND isys_cats_person_list__title = {$username}
                LIMIT 1";

            if ($this->retrieve($sql)->count()) {
                $l_return['title'] = isys_application::instance()->container->get('language')->get('LC__LOGIN__USERNAME_NEEDS_TO_BE_UNIQUE');
            }
        }

        if (count($l_return)) {
            return $l_return;
        }

        return parent::validate($p_data);
    }

    /**
     * @param $username
     *
     * @return bool
     * @throws isys_exception_database
     */
    public function canLogin($username): bool
    {
        return $this->retrieve('
            SELECT isys_obj__id FROM isys_cats_person_list
            INNER JOIN isys_obj ON isys_obj.isys_obj__id = isys_cats_person_list__isys_obj__id
            WHERE isys_cats_person_list__disabled_login = 0
              AND isys_cats_person_list__status = ' . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
              AND isys_obj__status = '  . $this->convert_sql_int(C__RECORD_STATUS__NORMAL) . '
              AND isys_cats_person_list__title = ' . $this->convert_sql_text($username))->count() >= 1;
    }

    /**
     * Do not remove entry just unset username and password
     *
     * @param int    $objectId
     * @param string $categoryTable
     * @param bool   $hasRelation
     *
     * @return bool|void
     */
    public function clear_data($objectId, $categoryTable, $hasRelation = true)
    {
        $update = 'UPDATE isys_cats_person_list SET
            isys_cats_person_list__title = null,
            isys_cats_person_list__user_pass = null,
            isys_cats_person_list__disabled_login = 0
            WHERE isys_cats_person_list__isys_obj__id = ' . $this->convert_sql_id($objectId);

        return $this->update($update) && $this->apply_update();
    }
}
