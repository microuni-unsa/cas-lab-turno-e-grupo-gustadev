<?php

/**
 * i-doit core classes
 *
 * @package     i-doit
 * @subpackage  Core
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_settings implements isys_settings_interface
{
    use isys_settings_trait;

    /** @var isys_component_dao_settings */
    protected static isys_component_dao_settings $dao;

    /** @var array */
    protected static array $definition = [
        'User interface'    => [
            'gui.wiki-url'            => [
                'title'       => 'Wiki URL',
                'type'        => 'text',
                'placeholder' => 'https://wikipedia.org/wiki/'
            ],
            'login.tenantlist.sortby' => [
                'title'   => 'LC__SYSTEM_SETTINGS__TENANT_SORT_FUNCTION',
                'type'    => 'select',
                'options' => [
                    'isys_mandator__title' => 'LC__UNIVERSAL__TITLE',
                    'isys_mandator__sort'  => 'LC__SYSTEM_SETTINGS__TENANT_SORT_FUNCTION__CUSTOM'
                ]
            ]
        ],
        'Session'           => [
            'session.time' => [
                'title'       => 'Session timeout',
                'type'        => 'int',
                'placeholder' => 300,
                'default'     => 300,
                'description' => 'LC__CMDB__UNIT_OF_TIME__SECOND'
            ]
        ],
        'Single Sign On'    => [
            'session.sso.active'          => [
                'title'   => 'LC__UNIVERSAL__ACTIVE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'session.sso.mandator-id'     => [
                'title' => 'LC__SYSTEM_SETTINGS__DEFAULT_MANDATOR',
                'type'  => 'multiselect'
            ],
            'session.sso.use-domain-part' => [
                'title'   => 'LC__UNIVERSAL__USE_DOMAIN_PART',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
        ],
        'System Parameters' => [
            'cmdb.connector.suffix-schema' => [
                'title'  => '',
                'type'   => 'select',
                'hidden' => true
            ],
            'system.timezone'              => [
                'title'       => 'LC__SYSTEM_SETTINGS__PHP_TIMEZONE',
                'type'        => 'text',
                'placeholder' => 'Europe/Berlin',
                'description' => '<a href="https://php.net/manual/timezones.php">https://php.net/manual/timezones.php</a>'
            ],
            'system.devmode'               => [
                'title'   => 'Developer mode',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ],
                'hidden'  => true
            ],
            'system.dir.file-upload'       => [
                'title'       => 'LC__SYSTEM_SETTINGS__FILE_UPLOAD_DIRECTORY',
                'placeholder' => '/path/to/i-doit/upload/files/',
                'type'        => 'text'
            ],
            'system.dir.image-upload'      => [
                'title'       => 'LC__SYSTEM_SETTINGS__IMAGE_UPLOAD_DIRECTORY',
                'placeholder' => '/path/to/i-doit/upload/images/',
                'type'        => 'text'
            ]
        ],
        'Proxy'             => [
            'proxy.active'   => [
                'title'   => 'LC__UNIVERSAL__ACTIVE',
                'type'    => 'select',
                'options' => [
                    '0' => 'LC__UNIVERSAL__NO',
                    '1' => 'LC__UNIVERSAL__YES'
                ]
            ],
            'proxy.host'     => [
                'title'       => 'LC__SYSTEM_SETTINGS__HOST_IP_ADDRESS',
                'type'        => 'text',
                'placeholder' => 'proxy.i-doit.com'
            ],
            'proxy.port'     => [
                'title'       => 'Port',
                'type'        => 'int',
                'placeholder' => 3128
            ],
            'proxy.username' => [
                'title' => 'LC__LOGIN__USERNAME',
                'type'  => 'text'
            ],
            'proxy.password' => [
                'title' => 'LC__LOGIN__PASSWORD',
                'type'  => 'password'
            ]
        ],
        'Security'          => [
            'system.security.csrf' => [
                'title'       => 'CSRF-Token',
                'description' => 'LC__SYSTEM_SETTINGS__SECURITY__CSRF_IN_LOGIN',
                'type'        => 'select',
                'options'     => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO'
                ],
                'default'     => '0'
            ]
        ],
        'Login'             => [
            'system.login.welcome-message' => [
                'title' => 'LC__SYSTEM_SETTINGS__LOGIN_WELCOME_MESSAGE',
                'type'  => 'textarea',
            ]
        ],
        'SMTP'              => [
            'system.email.smtp-host'          => [
                'title' => 'SMTP Host',
                'type'  => 'text',
            ],
            'system.email.port'               => [
                'title'       => 'SMTP Port',
                'type'        => 'int',
            ],
            'system.email.username'           => [
                'title' => 'SMTP Username',
                'type'  => 'text',
            ],
            'system.email.password'           => [
                'title' => 'SMTP Password',
                'type'  => 'password',
            ],
            'system.email.smtp-auto-tls'      => [
                'title'   => 'SMTP TLS',
                'type'    => 'select',
                'default' => '0',
                'options' => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO',
                ],
            ],
            'system.email.from'               => [
                'title' => 'LC__SYSTEM_SETTINGS__SENDER',
                'type'  => 'text',
            ],
            'system.email.name'               => [
                'title' => 'Name',
                'type'  => 'text',
            ],
            'system.email.connection-timeout' => [
                'title'       => 'Timeout',
                'type'        => 'int',
                'default'     => '60',
            ],
            'system.email.smtpdebug'          => [
                'title'   => 'SMTP Debug',
                'type'    => 'select',
                'default' => '0',
                'options' => [
                    '1' => 'LC__UNIVERSAL__YES',
                    '0' => 'LC__UNIVERSAL__NO',
                ],
            ],
            'system.email.subject-prefix'     => [
                'title' => 'LC__SYSTEM_SETTINGS__SUBJET_PREFIX',
                'type'  => 'text',
            ],
        ],
    ];

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
                'isys_settings',
                'shutdown'
            ]);

        try {
            self::$dao = new isys_component_dao_settings($database);
            self::$settings = self::$dao->get_settings();
            self::$isInitialized = true;
        } catch (Exception $e) {
            if (isys_application::instance()->container->has('logger')) {
                isys_application::instance()->container->get('logger')
                    ->error('System settings initialization error: ' . $e->getMessage());
            }
        }
    }

    /**
     * @return array
     */
    public static function regenerate(): array
    {
        return self::$settings = self::$dao->get_settings();
    }
}
