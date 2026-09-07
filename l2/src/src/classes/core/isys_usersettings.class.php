<?php

/**
 * i-doit core classes.
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_usersettings implements isys_settings_interface
{
    use isys_settings_trait;

    /** @var isys_component_dao_user_settings */
    protected static isys_component_dao_user_settings $dao;

    /** @var array */
    protected static array $definition = [
        'Quickinfo (Link mouseover)'                                         => [
            'gui.quickinfo.active' => [
                'title'   => 'LC__USER_SETTINGS__QUICKINFO_ACTIVE',
                'type'    => 'select',
                'default' => '1',
                'options' => [
                    0 => 'LC__UNIVERSAL__NO',
                    1 => 'LC__UNIVERSAL__YES'
                ]
            ],
            'gui.quickinfo.delay'  => [
                'title'       => 'LC__UNIVERSAL__DELAY',
                'type'        => 'float',
                'default'     => 0.5,
                'placeholder' => 0.5
            ]
        ],
        'LC__MODULE__SYSTEM__TREE__USER_SETTINGS__VIEWS__LISTS__OBJECT_LIST' => [
            'gui.objectlist.remember-filter' => [
                'title'       => 'LC__CMDB__TREE__SYSTEM__OBJECT_LIST__FILTER_MEMORIZE',
                'type'        => 'int',
                'default'     => 300,
                'placeholder' => 0,
                'description' => 'LC__CMDB__TREE__SYSTEM__OBJECT_LIST__FILTER_MEMORIZE_DESCRIPTION'
            ],
            'gui.objectlist.rows-per-page'   => [
                'title'       => 'LC__SYSTEM__REGISTRY__PAGELIMIT',
                'type'        => 'int',
                'default'     => 50,
                'placeholder' => 50
            ]
        ],
        'LC__MODULE__QCW__CATEGORIES'                                        => [
            'gui.category.cabling.directly-open-cabling-addon' => [
                'title'   => 'LC__CABLING__NEW_VISUALIZATION__ADDON_READY_DIRECTLY_OPEN_IN_ADDON_SETTING',
                'type'    => 'select',
                'options' => [
                    0 => 'LC__UNIVERSAL__NO',
                    1 => 'LC__UNIVERSAL__YES'
                ]
            ]
        ],
        'LC__USER_SETTINGS__HEADER__MAIN_MENU'                               => [
            'gui.show-my-doit'                        => [
                'title'   => 'LC__USER_SETTINGS__SHOW_MY_DOIT',
                'type'    => 'select',
                'options' => [
                    0 => 'LC__UNIVERSAL__NO',
                    1 => 'LC__UNIVERSAL__YES'
                ]
            ],
            'gui.show-object-type-groups-as-dropdown' => [
                'title'   => 'LC__USER_SETTINGS__OBJECT_TYPE_GROUPS_AS_DROPDOWN',
                'type'    => 'select',
                'options' => [
                    0 => 'LC__UNIVERSAL__NO',
                    1 => 'LC__UNIVERSAL__YES',
                    2 => 'LC__USER_SETTINGS__OBJECT_TYPE_GROUPS_AS_DROPDOWN_AUTOMATIC'
                ]
            ]
        ],
        'LC__USER_SETTINGS__HEADER__OBJECT_NAVIGATION'                       => [
            'gui.default-tree-view'            => [
                'title'   => 'LC__CMDB__SETTINGS__USER__DEFAULT_TREEVIEW',
                'type'    => 'select',
                'options' => [
                    C__CMDB__VIEW__TREE_OBJECTTYPE => 'LC__CMDB__OBJECT_VIEW',
                    C__CMDB__VIEW__TREE_LOCATION   => 'LC__CMDB__MENU_TREE_VIEW'
                ]
            ],
            'gui.default-tree-type'            => [
                'title'   => 'LC__CMDB__SETTINGS__USER__DEFAULT_TREETYPE',
                'type'    => 'select',
                'options' => [
                    C__CMDB__VIEW__TREE_LOCATION__LOCATION      => 'LC__CMDB__TREE_VIEW__LOCATION',
                    C__CMDB__VIEW__TREE_LOCATION__LOGICAL_UNITS => 'LC__CMDB__TREE_VIEW__LOGICAL_UNIT',
                    C__CMDB__VIEW__TREE_LOCATION__COMBINED      => 'LC__CMDB__TREE_VIEW__COMBINED',
                ]
            ],
            'gui.tree.hide-empty-object-types' => [
                'title'   => 'LC__USER_SETTINGS__TREE_HIDE_EMPTY_OBJECT_TYPES',
                'type'    => 'select',
                'options' => [
                    0 => 'LC__UNIVERSAL__NO',
                    1 => 'LC__UNIVERSAL__YES',
                ]
            ],
            'gui.tree.hide-empty-categories'   => [
                'title'   => 'LC__USER_SETTINGS__TREE_HIDE_EMPTY_CATEGORIES',
                'type'    => 'select',
                'options' => [
                    0 => 'LC__UNIVERSAL__NO',
                    1 => 'LC__UNIVERSAL__YES',
                ]
            ]
        ],
        'LC__USER_SETTINGS__HEADER__SPACING'                                 => [
            // @see ID-8965 Save user custom row padding.
            'gui.category.padding' => [
                'title'   => 'LC__USER_SETTINGS__CATEGORY_ROW_PADDING',
                'type'    => 'select',
                'options' => [
                    's' => 'LC__USER_SETTINGS__CATEGORY_ROW_PADDING__SMALL',
                    'm' => 'LC__USER_SETTINGS__CATEGORY_ROW_PADDING__MEDIUM',
                    'l' => 'LC__USER_SETTINGS__CATEGORY_ROW_PADDING__LARGE'
                ],
                'default' => 'l',
            ],
            // @see ID-9164 Save tree spacing
            'gui.tree.spacing'     => [
                'title'   => 'LC__USER_SETTINGS__TREE_SPACING',
                'type'    => 'select',
                'options' => [
                    's' => 'LC__USER_SETTINGS__CATEGORY_ROW_PADDING__SMALL',
                    'm' => 'LC__USER_SETTINGS__CATEGORY_ROW_PADDING__MEDIUM',
                    'l' => 'LC__USER_SETTINGS__CATEGORY_ROW_PADDING__LARGE'
                ],
                'default' => 'l',
            ],
            // @see ID-8965 Save if the user wants to see spacers.
            'gui.category.spacer'  => [
                'title'   => 'LC__USER_SETTINGS__CATEGORY_SPACER',
                'type'    => 'select',
                'options' => [
                    0 => 'LC__UNIVERSAL__NO',
                    1 => 'LC__UNIVERSAL__YES',
                ],
                'default' => 1,
            ],
        ]
    ];

    /**
     * @param string|null $key
     * @param mixed       $default
     *
     * @return mixed
     */
    public static function get(string|null $key = null, mixed $default = ''): mixed
    {
        if ($key === null) {
            return self::$settings;
        }

        if (isset(self::$settings[$key]) && self::$settings[$key] !== '') {
            return self::$settings[$key];
        }

        return isys_tenantsettings::get($key, $default);
    }

    /**
     * @param isys_component_database $database
     *
     * @return void
     * @throws Exception
     */
    public static function initialize(isys_component_database $database): void
    {
        if (self::$isInitialized) {
            return;
        }

        isys_component_signalcollection::get_instance()
            ->connect('system.shutdown', [
                'isys_usersettings',
                'shutdown'
            ]);

        try {
            self::$dao = new isys_component_dao_user_settings($database);
            self::$settings = self::$dao->get_settings();
            self::$isInitialized = true;
        } catch (Exception $e) {
            if (isys_application::instance()->container->has('logger')) {
                isys_application::instance()->container->get('logger')
                    ->error('User settings initialization error: ' . $e->getMessage());
            }
        }
    }

    /**
     * @param $userId
     *
     * @return array
     * @throws Exception
     */
    public static function regenerate($userId = null): array
    {
        if ($userId === null) {
            $userId = isys_application::instance()->container->get('session')->get_user_id();
        }

        return self::$settings = self::$dao->get_settings();
    }
}
