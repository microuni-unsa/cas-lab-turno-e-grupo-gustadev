<?php

/**
 * i-doit
 *
 * Helper methods for installation process
 *
 * @package     i-doit
 * @subpackage  Helper
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 * @since       1.18
 */
class isys_helper_install
{
    /**
     * Preparing persons list data array
     *
     * @param string|null $overwriteUser
     * @param string|null $overwritePassword
     * @param string|null $overwriteEmail
     *
     * @return array[]
     */
    private static function getDataForPersonsList(?string $overwriteUser = null, ?string $overwritePassword = null, ?string $overwriteEmail = null)
    {
        // @see ID-10785 Implement additional login user and password.
        if ($overwriteUser !== null && $overwritePassword !== null) {
            return [
                [
                    'isys_cats_person_list__id'                  => 6,
                    'isys_cats_person_list__isys_connection__id' => 6,
                    'isys_cats_person_list__title'               => trim($overwriteUser),
                    'isys_cats_person_list__sort'                => 5,
                    'isys_cats_person_list__const'               => null,
                    'isys_cats_person_list__user_pass'           => trim($overwritePassword),
                    'isys_cats_person_list__last_name'           => '',
                    'isys_cats_person_list__first_name'          => trim($overwriteUser),
                    'isys_cats_person_list__status'              => 2,
                    'isys_cats_person_list__property'            => 0,
                    'isys_cats_person_list__isys_obj__id'        => 9,
                    'isys_cats_person_list__disabled_login'      => 0,
                    'isys_cats_person_list__unmigrated_password' => 0,
                    'isys_cats_person_list__password_reset_email' => trim($overwriteEmail),
                ],
                [
                    'isys_cats_person_list__id'                  => 7,
                    'isys_cats_person_list__isys_connection__id' => null,
                    'isys_cats_person_list__title'               => 'systemapi',
                    'isys_cats_person_list__sort'                => 5,
                    'isys_cats_person_list__const'               => null,
                    'isys_cats_person_list__user_pass'           => 'systemapi',
                    'isys_cats_person_list__last_name'           => 'System',
                    'isys_cats_person_list__first_name'          => 'Api',
                    'isys_cats_person_list__status'              => 2,
                    'isys_cats_person_list__property'            => 0,
                    'isys_cats_person_list__isys_obj__id'        => 22,
                    'isys_cats_person_list__disabled_login'      => 0,
                    'isys_cats_person_list__unmigrated_password' => 0,
                    'isys_cats_person_list__password_reset_email' => null,
                ]
            ];
        }

        return [
            [
                'isys_cats_person_list__id'                  => 1,
                'isys_cats_person_list__isys_connection__id' => 1,
                'isys_cats_person_list__title'               => 'guest',
                'isys_cats_person_list__sort'                => 5,
                'isys_cats_person_list__const'               => null,
                'isys_cats_person_list__user_pass'           => 'guest',
                'isys_cats_person_list__last_name'           => '',
                'isys_cats_person_list__first_name'          => 'guest',
                'isys_cats_person_list__status'              => 2,
                'isys_cats_person_list__property'            => 0,
                'isys_cats_person_list__isys_obj__id'        => 4,
                'isys_cats_person_list__disabled_login'      => 0,
                'isys_cats_person_list__unmigrated_password' => 0,
                'isys_cats_person_list__password_reset_email' => null,
            ],
            [
                'isys_cats_person_list__id'                  => 2,
                'isys_cats_person_list__isys_connection__id' => 2,
                'isys_cats_person_list__title'               => 'reader',
                'isys_cats_person_list__sort'                => 5,
                'isys_cats_person_list__const'               => null,
                'isys_cats_person_list__user_pass'           => 'reader',
                'isys_cats_person_list__last_name'           => '',
                'isys_cats_person_list__first_name'          => 'reader',
                'isys_cats_person_list__status'              => 2,
                'isys_cats_person_list__property'            => 0,
                'isys_cats_person_list__isys_obj__id'        => 5,
                'isys_cats_person_list__disabled_login'      => 0,
                'isys_cats_person_list__unmigrated_password' => 0,
                'isys_cats_person_list__password_reset_email' => null,
            ],
            [
                'isys_cats_person_list__id'                  => 3,
                'isys_cats_person_list__isys_connection__id' => 3,
                'isys_cats_person_list__title'               => 'editor',
                'isys_cats_person_list__sort'                => 5,
                'isys_cats_person_list__const'               => null,
                'isys_cats_person_list__user_pass'           => 'editor',
                'isys_cats_person_list__last_name'           => '',
                'isys_cats_person_list__first_name'          => 'editor',
                'isys_cats_person_list__status'              => 2,
                'isys_cats_person_list__property'            => 0,
                'isys_cats_person_list__isys_obj__id'        => 6,
                'isys_cats_person_list__disabled_login'      => 0,
                'isys_cats_person_list__unmigrated_password' => 0,
                'isys_cats_person_list__password_reset_email' => null,
            ],
            [
                'isys_cats_person_list__id'                  => 4,
                'isys_cats_person_list__isys_connection__id' => 4,
                'isys_cats_person_list__title'               => 'author',
                'isys_cats_person_list__sort'                => 5,
                'isys_cats_person_list__const'               => null,
                'isys_cats_person_list__user_pass'           => 'author',
                'isys_cats_person_list__last_name'           => '',
                'isys_cats_person_list__first_name'          => 'author',
                'isys_cats_person_list__status'              => 2,
                'isys_cats_person_list__property'            => 0,
                'isys_cats_person_list__isys_obj__id'        => 7,
                'isys_cats_person_list__disabled_login'      => 0,
                'isys_cats_person_list__unmigrated_password' => 0,
                'isys_cats_person_list__password_reset_email' => null,
            ],
            [
                'isys_cats_person_list__id'                  => 5,
                'isys_cats_person_list__isys_connection__id' => 5,
                'isys_cats_person_list__title'               => 'archivar',
                'isys_cats_person_list__sort'                => 5,
                'isys_cats_person_list__const'               => null,
                'isys_cats_person_list__user_pass'           => 'archivar',
                'isys_cats_person_list__last_name'           => '',
                'isys_cats_person_list__first_name'          => 'archivar',
                'isys_cats_person_list__status'              => 2,
                'isys_cats_person_list__property'            => 0,
                'isys_cats_person_list__isys_obj__id'        => 8,
                'isys_cats_person_list__disabled_login'      => 0,
                'isys_cats_person_list__unmigrated_password' => 0,
                'isys_cats_person_list__password_reset_email' => null,
            ],
            [
                'isys_cats_person_list__id'                  => 6,
                'isys_cats_person_list__isys_connection__id' => 6,
                'isys_cats_person_list__title'               => 'admin',
                'isys_cats_person_list__sort'                => 5,
                'isys_cats_person_list__const'               => null,
                'isys_cats_person_list__user_pass'           => 'admin',
                'isys_cats_person_list__last_name'           => '',
                'isys_cats_person_list__first_name'          => 'admin',
                'isys_cats_person_list__status'              => 2,
                'isys_cats_person_list__property'            => 0,
                'isys_cats_person_list__isys_obj__id'        => 9,
                'isys_cats_person_list__disabled_login'      => 0,
                'isys_cats_person_list__unmigrated_password' => 0,
                'isys_cats_person_list__password_reset_email' => null,
            ],
            [
                'isys_cats_person_list__id'                  => 7,
                'isys_cats_person_list__isys_connection__id' => null,
                'isys_cats_person_list__title'               => 'systemapi',
                'isys_cats_person_list__sort'                => 5,
                'isys_cats_person_list__const'               => null,
                'isys_cats_person_list__user_pass'           => 'systemapi',
                'isys_cats_person_list__last_name'           => 'System',
                'isys_cats_person_list__first_name'          => 'Api',
                'isys_cats_person_list__status'              => 2,
                'isys_cats_person_list__property'            => 0,
                'isys_cats_person_list__isys_obj__id'        => 22,
                'isys_cats_person_list__disabled_login'      => 0,
                'isys_cats_person_list__unmigrated_password' => 0,
                'isys_cats_person_list__password_reset_email' => null,
            ]
        ];
    }

    /**
     * Fill the persons list table in DB.
     *
     * @param string     $db_host
     * @param string     $db_username
     * @param string     $db_password
     * @param string     $db_database_name
     * @param string|int $db_port
     *
     * @return array
     */
    public static function insertPersons($db_host, $db_username, $db_password, $db_database_name, $db_port): array
    {
        try {
            $dbLink = new mysqli(
                $db_host,
                $db_username,
                $db_password,
                $db_database_name,
                $db_port
            );
            $dbLink->query("SET sql_mode=''");
            $query = self::generatePersonsListDataQuery();
            $dbLink->query($query);
            $dbLink->close();
        } catch (Exception  $e) {
            return [
                'result' => false,
                'message' => $e->getMessage()
            ];
        }
        return [
            'result' => true,
            'message' => 'Success'
        ];
    }

    /**
     * Generate SQL query for persons list.
     *
     * @param string|null $overwriteUser
     * @param string|null $overwritePassword
     *
     * @return string
     */
    public static function generatePersonsListDataQuery(?string $overwriteUser = null, ?string $overwritePassword = null, ?string $overwriteRecoveryEmail = null): string
    {
        $personData = array_filter(self::getDataForPersonsList($overwriteUser, $overwritePassword, $overwriteRecoveryEmail));
        $tableFields = array_keys($personData[0]);
        $valuesToInsert = [];

        foreach ($personData as $personDataItem) {
            $valuesToInsert[] = self::generateValuesStringForQuery($personDataItem);
        }

        return "INSERT INTO isys_cats_person_list (" . implode(',', $tableFields) . ") VALUES " . implode(',', $valuesToInsert) .";";
    }

    /**
     * Generate values string from data to insert persons.
     *
     * @param array $data
     *
     * @return string
     */
    private static function generateValuesStringForQuery(array $data): string
    {
        $data['isys_cats_person_list__user_pass'] = isys_helper_crypt::encryptPassword($data['isys_cats_person_list__user_pass']);

        $queryParts = [];
        foreach ($data as $value) {
            if (!empty($value) || (is_numeric($value) && $value >= 0)) {
                $queryParts[] = "'" . $value . "'";
            } else {
                $queryParts[] = 'NULL';
            }
        }

        return "(" . implode(',', $queryParts) . ")";
    }
}
