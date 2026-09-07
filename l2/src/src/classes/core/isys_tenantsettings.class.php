<?php

use idoit\Component\FeatureManager\FeatureManager;
use idoit\Module\SystemSettings\Settings\Tenant\LastLogin;

/**
 * i-doit core classes.
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_tenantsettings implements isys_settings_interface
{
    use isys_settings_trait;

    const NMAP_OPTIONS = [
        'PE' => 'PE/PP/PM: ICMP echo, timestamp, and netmask request discovery probes',
        'sP' => 'sP: Ping Scan - go no further than determining if host is online',
        'PR' => 'PR: ARP Scan',
        'sT' => 'sT: TCP Connect Scan'
    ];

    /** @var isys_component_dao_tenant_settings */
    public static isys_component_dao_tenant_settings $dao;

    /**
     * Settings register.
     * Constant C__TREE__TITLE__MAXLEN is not used
     *
     * @var array
     */
    public static array $definition = [
        'LC__SETTINGS__SYSTEM__PASSWORD_RESET' => [
            'system.passwort-reset.enabled' => [
                'title'       => 'LC__SETTINGS__SYSTEM__PASSWORD_RESET__ENABLED',
                'type'        => 'select',
                'options'     => [
                    1 => 'LC__UNIVERSAL__ENABLED',
                    0 => 'LC__UNIVERSAL__DISABLED',
                ],
                'description' => 'LC__SETTINGS__SYSTEM__PASSWORD_RESET__ENABLED_DESCRIPTION',
                'hidden'      => true,
            ]
        ],
        'LC__SETTINGS__SYSTEM__URL_SETTINGS'   => [
            'system.base.uri' => [
                'title'       => 'LC__SETTINGS__SYSTEM__URL_SETTINGS__IDOIT_URL',
                'default'     => '',
                'placeholder' => 'https://i-doit.int/',
                'type'        => 'text',
                'description' => 'LC__SETTINGS__SYSTEM__URL_SETTINGS__IDOIT_URL_DESCRIPTION'
            ]
        ],
        'LC__UNIVERSAL__CMDB'                                => [
            'system.csv-export-delimiter'              => [
                'title'   => 'LC__SETTINGS__CMDB__EXPORT__CSV_DELIMITER',
                'type'    => 'select',
                'options' => [
                    ','  => 'LC__UNIVERSAL__COMMA',
                    ';'  => 'LC__UNIVERSAL__SEMICOLON',
                    '#'  => 'LC__UNIVERSAL__HASH',
                    "\t" => 'LC__UNIVERSAL__TAB'
                ],
                'default' => ';'
            ],
            'cmdb.gui.objectlist.direct-edit-mode'     => [
                'title'   => 'LC__SETTINGS__CMDB__OBJECT_LISTS__DIRECT_EDIT_MODE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '0'
            ],
            'cmdb.sysid.prefix'                        => [
                'title'   => 'LC__SYSTEM_SETTINGS__SYSID_PREFIX',
                'type'    => 'text',
                'default' => 'SYSID_',
            ],
            'cmdb.cable.change-cmdb-status-on-attach'  => [
                'title'   => 'LC__SETTINGS__CMDB__CHANGE_CABLE_ATTACH_CMDB_STATUS',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'cmdb.cable.change-cmdb-status-on-detach'  => [
                'title'   => 'LC__SETTINGS__CMDB__CHANGE_CABLE_DETACH_CMDB_STATUS',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'cmdb.rack.segment-template-object-type'   => [
                'title'       => 'LC__SETTINGS__CMDB__RACK_SEGMENT_TEMPLATE__OBJ_TYPE_ID',
                'type'        => 'text',
                'default'     => 'C__OBJTYPE__RACK_SEGMENT',
                'placeholder' => 'C__OBJTYPE__RACK_SEGMENT'
            ],
            'cmdb.rack.vertical-slot-sorting'          => [
                'title'   => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SORTING',
                'type'    => 'select',
                'options' => [
                    '1' => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SORTING__A',
                    '2' => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SORTING__B',
                    '3' => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SORTING__C'
                ],
                'default' => '1'
            ],
            'cmdb.rack.vertical-slot-rear-mirrored'    => [
                'title'   => 'LC__SETTINGS__CMDB__RACK_VERTICAL_SLOTS_MIRRORED_FOR_REAR',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'cmdb.rack.slot-assignment-sort-direction' => [
                'title'   => 'LC__SETTINGS__CMDB__RACK_ASSIGNMENT_SORT_DIRECTION',
                'type'    => 'select',
                'options' => [
                    'asc'  => 'LC__SETTINGS__CMDB__RACK_ASSIGNMENT_SORT_DIRECTION_ASC',
                    'desc' => 'LC__SETTINGS__CMDB__RACK_ASSIGNMENT_SORT_DIRECTION_DESC'
                ],
                'default' => 'asc'
            ],
            'cmdb.rack.rank-detached-segment-objects'  => [
                'title'   => 'LC__SETTINGS__CMDB__RACK_RANK_DETACHED_SEGMENT_OBJECTS',
                'type'    => 'select',
                'options' => [
                    C__RACK_DETACH_SEGMENT_ACTION__NONE    => 'LC__SETTINGS__CMDB__RACK_RANK_DETACHED_SEGMENT_OBJECTS_NO_ACTION',
                    C__RACK_DETACH_SEGMENT_ACTION__ARCHIVE => 'LC__SETTINGS__CMDB__RACK_RANK_DETACHED_SEGMENT_OBJECTS_ARCHIVE',
                    C__RACK_DETACH_SEGMENT_ACTION__PURGE   => 'LC__SETTINGS__CMDB__RACK_RANK_DETACHED_SEGMENT_OBJECTS_PURGE'
                ],
                'default' => C__RACK_DETACH_SEGMENT_ACTION__NONE
            ],
            'cmdb.chassis.handle-location-changes'     => [
                'title'       => 'LC__SETTINGS__CMDB__CHASSIS__HANDLE_LOCATION_CHANGES',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default'     => '0',
                'description' => 'LC__SETTINGS__CMDB__CHASSIS__HANDLE_LOCATION_CHANGES_DESCRIPTION'
            ],
            'cmdb.logical-location.object-type-filter' => [
                'title'       => 'LC__SETTINGS__CMDB__CATEGORY_LOGICAL_UNIT_OBJECT_TYPE_FILTER',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default'     => '1',
                'description' => 'LC__SETTINGS__CMDB__CATEGORY_LOGICAL_UNIT_OBJECT_TYPE_FILTER_DESCRIPTION'
            ],
            'cmdb.csv-export.remove-object-ids'        => [
                'title'   => 'LC__SETTINGS__CMDB__TABLE__CSV_EXPORT__REMOVE_OBJECT_IDS',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '0'
            ],
            'cmdb.validation.mac-address'              => [
                'title'   => 'LC__SETTINGS__CMDB__VALIDATION__DISABLE_MAC_ADRESS',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '0',
            ],
            'cmdb.registry.sysid_readonly'             => [
                'title'   => 'LC__SETTINGS__CMDB__SYSID_READONLY',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '0',
            ],
            'cmdb.release-ip-on-archive-delete'        => [
                'title'   => 'LC__MANDATOR_SETTING__IP_HANDLING',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '0',
            ],
            'import.object.keep-status'                => [
                'title'       => 'LC__SYSTEM_SETTINGS__IMPORT_OBJECT_KEEP',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__IMPORT_OBJECT_KEEP_STATUS_DESCRIPTION'
            ],
            'cmdb.object.title.cable-prefix'           => [
                'title' => 'LC__SYSTEM_SETTINGS__OBJECT_CABLE_PREFIX',
                //'Object cable prefix',
                'type'  => 'text',
            ],
            'cmdb.quickpurge'                          => [
                'title'       => 'LC__SYSTEM_SETTINGS__QUICKPURGE',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__QUICKPURGE_DESCRIPTION'
            ],
            'gui.wysiwyg'                              => [
                'title'   => 'LC__SYSTEM_SETTINGS__WYSIWYG_EDITOR',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'gui.wysiwyg-all-controls'                 => [
                'title'   => 'LC__SYSTEM_SETTINGS__WYSIWYG_EDITOR_FULL_CONTROL',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
        ],
        'Display Limits'                                     => [
            'cmdb.limits.obj-browser.objects-in-viewmode'     => [
                'title'       => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECTS_IN_VIEWMODE',
                'type'        => 'int',
                'placeholder' => 8,
                'default'     => 8,
                'description' => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECTS_IN_VIEWMODE_DESCRIPTION'
            ],
            'cmdb.limits.obj-browser.objects-rendering'       => [
                'title'   => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECT_RENDERING_IN_VIEWMODE',
                'type'    => 'select',
                'options' => [
                    'comma' => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECT_RENDERING_IN_VIEWMODE__COMMA',
                    'list'  => 'LC__SETTINGS__CMDB__OBJ_BROWSER__OBJECT_RENDERING_IN_VIEWMODE__LIST'
                ],
                'default' => 'comma'
            ],
            'gui.lists.preload-pages'                         => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__PRELOAD_PAGES_TITLE',
                'type'        => 'int',
                'placeholder' => 30,
                'default'     => 30
            ],
            'cmdb.lists.field-length-limit'                   => [
                'title'       => 'LC__SETTINGS__CMDB__FIELD_LENGTH_LIMIT',
                'type'        => 'int',
                'placeholder' => 75,
                'default'     => 0,
                'description' => 'LC__SETTINGS__CMDB__FIELD_LENGTH_LIMIT_DESCRIPTION'
            ],
            'cmdb.object-browser.max-objects'                 => [
                'title'       => 'LC__SYSTEM_SETTINGS__OBJECT_BROWSER_RESULT_LIMIT',
                'type'        => 'int',
                'placeholder' => 1500
            ],
            'cmdb.limits.port-lists-vlans'                    => [
                'title'       => 'LC__SETTINGS__CMDB__VLAN_LIMIT_IN_PORT_LISTS',
                'type'        => 'int',
                'placeholder' => 5,
                'default'     => 5
            ],
            'cmdb.limits.port-lists-layer2'                   => [
                'title'       => 'LC__SETTINGS__CMDB__LAYER2_LIMIT_IN_LOGICAL_PORT_LISTS',
                'placeholder' => 5,
                'default'     => 5,
                'type'        => 'int'
            ],
            'cmdb.limits.port-overview-default-vlan-only'     => [
                'title'   => 'LC__SETTINGS__CMDB__PORT_OVERVIEW_DEFAULT_VLAN_ONLY',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'cmdb.limits.connector-lists-assigned_connectors' => [
                'title'       => 'LC__SETTINGS__CMDB__ASSIGNED_CONNECTOR_LIMIT_IN_CONNECTOR_LISTS',
                'type'        => 'int',
                'placeholder' => 5,
                'default'     => 5
            ],
            'cmdb.limits.ip-lists'                            => [
                'title'       => 'LC__SETTINGS__CMDB__IP_LISTS_LIMIT',
                'type'        => 'int',
                'placeholder' => 5,
                'default'     => 5
            ],
            'cmdb.limits.cmdb-explorer-service-browser'       => [
                'title'       => 'LC__SETTINGS__CMDB_EXPLORER__SERVICE_BROWSER_LIMIT',
                'type'        => 'int',
                'placeholder' => 2500,
                'default'     => 2500
            ],
            'cmdb.limits.location-path'                       => [
                'title'       => 'LC__SETTINGS__CMDB__LOCATION_PATH_LIMIT',
                'type'        => 'int',
                'placeholder' => 5,
                'default'     => 5
            ],
            'cmdb.registry.mytask_entries'                    => [
                'title'       => 'LC__SYSTEM__REGISTRY__MYTASK',
                'type'        => 'int',
                'placeholder' => 8,
                'default'     => 8
            ]
        ],
        'LC__SYSTEM_SETTINGS__TENANT__IP_LIST'               => [
            'cmdb.ip-list.cache-lifetime' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__IP_LIST__CACHE_LIFETIME',
                'type'        => 'int',
                'default'     => 86400,
                'placeholder' => 86400
            ],
            'cmdb.ip-list.ping-method'    => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__IP_LIST__PING_METHOD',
                'type'    => 'select',
                'options' => [
                    'nmap'  => 'Ping via NMAP',
                    'fping' => 'Ping via FPING'
                ]
            ],
            'cmdb.ip-list.nmap-parameter' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__IP_LIST__NMAP_PARAMETER',
                'type'    => 'select',
                'options' => self::NMAP_OPTIONS
            ]
        ],
        'LC__TENANT_SETTINGS__UNIQUE_CHECKS'                 => [
            'cmdb.unique.object-title' => [
                'title'   => 'LC__UNIVERSAL__OBJECT_TITLE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ],
            'cmdb.unique.layer-2-net'  => [
                'title'   => 'LC__REPORT__VIEW__LAYER2_NETS__TITLE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ],
            'cmdb.unique.ip-address'   => [
                'title'   => 'LC__REPORT__VIEW__LAYER2_NETS__IP_ADDRESSES',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ],
            'cmdb.unique.hostname'     => [
                'title'   => 'Hostname',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
            ]
        ],
        'Barcodes'                                           => [
            'barcode.enabled' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__BARCODE_ENABLED',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'barcode.type'    => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__BARCODE_FORM',
                'type'    => 'select',
                'options' => [
                    'qr'     => 'QR-Code',
                    'code39' => 'Code39'
                ],
                'default' => 'qr'
            ]
        ],
        'LC__SYSTEM_SETTINGS__TENANT__GUI'                   => [
            'gui.empty_value'                   => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__GUI__EMPTY_VALUES',
                'type'        => 'text',
                'placeholder' => '-',
                'default'     => '-'
            ],
            'gui.separator.location'            => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__GUI__LOCATION_SEPARATOR',
                'type'        => 'text',
                'placeholder' => ' > ',
                'default'     => ' > '
            ],
            'gui.location_path.direction.rtl'   => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__GUI__LOCATION_DIRECTION',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__SYSTEM_SETTINGS__TENANT__GUI__LOCATION_DIRECTION_LTR',
                    '1' => 'LC__SYSTEM_SETTINGS__TENANT__GUI__LOCATION_DIRECTION_RTL'
                ],
                'default' => '0'
            ],
            'gui.separator.connector'           => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__GUI__CONNECTOR_SEPARATOR',
                'type'        => 'text',
                'placeholder' => ' > ',
                'default'     => ' > '
            ],
            'cmdb.registry.object_dragndrop'    => [
                'title'   => 'LC__SYSTEM__REGISTRY__DD_OBJECTS',
                'type'    => 'select',
                'options' => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default' => '1'
            ],
            'cmdb.registry.object_type_sorting' => [
                'title'   => 'LC__CMDB__SETTINGS__CMDB__OBJECTTYPE_SORTING',
                'type'    => 'select',
                'options' => [
                    C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC => 'LC__CMDB__TREE_VIEW__OBJECTTYPE_SORTING__ALPHABETICALLY',
                    C__CMDB__VIEW__OBJECTTYPE_SORTING__MANUAL    => 'LC__CMDB__TREE_VIEW__OBJECTTYPE_SORTING__MANUAL'
                ],
                'default' => C__CMDB__VIEW__OBJECTTYPE_SORTING__AUTOMATIC
            ]
        ],
        'LC__SYSTEM_SETTINGS__TENANT__MAXLENGTH'             => [
            'maxlength.dialog_plus'      => [
                'title'       => 'Dialog-Plus',
                'type'        => 'int',
                'placeholder' => 110,
                'default'     => 110
            ],
            'maxlength.location.objects' => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__MAXLENGTH_OBJECTS_IN_TREE',
                'type'        => 'int',
                'placeholder' => 16,
                'default'     => 16
            ],
            'maxlength.location.path'    => [
                'title'       => 'LC__SYSTEM__SETTINGS__TENANT__MAXLENGTH_LOCATION_PATH',
                'type'        => 'int',
                'placeholder' => 100,
                'default'     => 100
            ],
        ],
        'LC__SYSTEM_SETTINGS__TENANT__LOGBOOK'               => [
            'logbook.changes'                => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__LOGBOOK__LOGGING',
                'type'    => 'select',
                'options' => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default' => '1'
            ],
            'cmdb.registry.quicksave'        => [
                'title'   => 'LC__CMDB__SYSTEM_SETTING__QUICK_SAVE_BUTTON',
                'type'    => 'select',
                'options' => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default' => '1'
            ],
            'logbook.delete-on-object-purge' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT__LOGBOOK__DELETE_ON_OBJECT_PURGE',
                'type'    => 'select',
                'options' => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default' => '0'
            ]
        ],
        'LC__CMDB__TREE__SYSTEM__SETTINGS_SYSTEM__TEMPLATES' => [
            'cmdb.template.status'      => [
                'title'   => 'LC__TEMPLATES__SHOW_FILTER_STATUS',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '0'
            ],
            'cmdb.template.colors'      => [
                'title'   => 'LC__TEMPLATES__COLORIZE_TEMPLATE_ASSIGNMENTS',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default' => '1'
            ],
            'cmdb.template.color_value' => [
                'title'   => 'LC__TEMPLATES__IN_COLOR',
                'type'    => 'color',
                'default' => 'CC0000'
            ]
        ],
        'LC__SYSTEM_SETTINGS__TENANT__SECURITY'              => [
            'auth.active'                       => [
                'title'   => 'LC__MODULE__AUTH',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__MODULE__QCW__INACTIVE',
                    '1' => 'LC__NOTIFICATIONS__NOTIFICATION_STATUS'
                ],
                'default' => 1
            ],
            'minlength.login.password'          => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__PASSWORD_MINLENGTH',
                'type'        => 'int',
                'placeholder' => 4,
                'default'     => 4
            ],
            'password.decrypt.in-export-import' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__PASSWORD_AS_CLEAR_TEXT',
                'type'        => 'select',
                'options'     => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default'     => '0',
                'description' => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__PASSWORD_AS_CLEAR_TEXT__DESCRIPTION'
            ],
            'logging.user.last-login'           => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__LAST_LOGIN',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__INACTIVE',
                    '1' => 'LC__UNIVERSAL__ACTIVE'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__LAST_LOGIN__DESCRIPTION',
                'observe'     => [
                    'event'         => 'change',
                    'condition'     => "$('logging.user.last-login').value == 0",
                    'declineAction' => "$('logging.user.last-login').selectedIndex = 1;",
                    'message'       => 'LC__SYSTEM_SETTINGS__TENANT__SECURITY__LAST_LOGIN__CONFIRMATION'
                ],
                'callback'    => [LastLogin::class, 'execute']
            ],
            'cmdb.registry.sanitize_input_data' => [
                'title'       => 'LC__CMDB__SYSTEM_SETTING__SANITIZE_INPUT_DATA',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default'     => '1',
                'description' => 'LC__CMDB__SYSTEM_SETTING__SANITIZE_INPUT_DATA__DESCRIPTION'
            ]
        ],
        'Logging'                                            => [
            'auth.logging'              => [
                'title'       => 'LC__SYSTEM_SETTINGS__SYSTEM__AUTH_LOG',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'default'     => '0',
                'description' => 'LC__SYSTEM_SETTINGS__SYSTEM__AUTH_LOGGING_ENABLED'
            ],
            'logging.system.exceptions' => [
                'title'       => 'Exception Log',
                'type'        => 'select',
                'options'     => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__SYSTEM__LOGGING_ENABLED'
            ],
            'logging.cmdb.import'       => [
                'title'   => 'CMDB Import',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ]
        ],
        'Quickinfo (Link mouseover)'                         => [
            'cache.quickinfo.expiration'       => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_EXPIRATION',
                'type'        => 'select',
                'default'     => isys_convert::DAY,
                'options'     => [
                    isys_convert::MINUTE => 'LC__UNIVERSAL__MINUTE',
                    isys_convert::HOUR   => 'LC__UNIVERSAL__HOUR',
                    isys_convert::DAY    => 'LC__UNIVERSAL__DAY'
                ],
                'description' => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_EXPIRATION__DESCRIPTION'
            ],
            'cmdb.quickinfo.rows-per-category' => [
                'title'       => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_ROWS_PER_CATEGORY',
                'type'        => 'int',
                'placeholder' => 15,
                'default'     => 15,
                'description' => 'LC__SYSTEM_SETTINGS__TENANT__QUICKINFO_ROWS_PER_CATEGORY__DESCRIPTION'
            ]
        ]
    ];

    /**
     * @param string $param
     *
     * @return array
     */
    public static function getLike(string $param): array
    {
        $settings = [];

        foreach (self::$settings as $key => $value) {
            if (str_contains($key, $param)) {
                $settings[$key] = $value;
            }
        }

        return $settings;
    }

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

        return isys_settings::get($key, $default);
    }

    /**
     * @param isys_component_database $database
     * @param                         $tenant
     *
     * @return void
     * @throws Exception
     */
    public static function initialize(isys_component_database $database, $tenant = null, $forceInitialize = false): void
    {
        if (self::$isInitialized === true && $forceInitialize === false) {
            return;
        }

        // @see  ID-7107  If there is no "PRO" indicator, we remove the barcode definition.
        if (!file_exists(BASE_DIR . '/src/classes/modules/pro/init.php')) {
            unset(self::$definition['Barcodes']);
        }

        if (!FeatureManager::isFeatureActive('idoit.feature.tenant-settings.default-url')) {
            unset(self::$definition['LC__SETTINGS__SYSTEM__URL_SETTINGS']);
        }

        isys_component_signalcollection::get_instance()
            ->connect('system.shutdown', [
                'isys_tenantsettings',
                'shutdown'
            ]);

        try {
            self::$dao = new isys_component_dao_tenant_settings($database, $tenant);
            self::$settings = self::$dao->get_settings();
            self::$isInitialized = true;
        } catch (Exception $e) {
            if (isys_application::instance()->container->has('logger')) {
                isys_application::instance()->container->get('logger')
                    ->error('Tenant settings initialization error: ' . $e->getMessage());
            }
        }
    }

    /**
     * @return array
     * @throws \idoit\Exception\JsonException
     * @throws isys_exception_database
     */
    public static function regenerate(): array
    {
        return self::$settings = self::$dao->get_settings();
    }
}
