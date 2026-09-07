<?php
/**
 *
 *
 * @package     i-doit
 * @subpackage
 * @author      Pavel Abduramanov <pabduramanov@i-doit.com>
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

namespace idoit\Module\Console\Console\Command\idoit\Update;

use idoit\AddOn\AddonVerify;
use idoit\Module\Console\Steps\AuthorisationStep;
use idoit\Module\Console\Steps\CollectionStep;
use idoit\Module\Console\Steps\ConstantStep;
use idoit\Module\Console\Steps\FileSystem\FileExistsCheck;
use idoit\Module\Console\Steps\FileSystem\FileMove;
use idoit\Module\Console\Steps\FileSystem\Unzip;
use idoit\Module\Console\Steps\IfCheck;
use idoit\Module\Console\Steps\Message\ErrorLevel;
use idoit\Module\Console\Steps\PhpExtensionCheck;
use idoit\Module\Console\Steps\PhpIniCheck;
use idoit\Module\Console\Steps\Step;
use idoit\Module\Console\Steps\TemplateFile;
use idoit\Module\Console\Steps\Update\CopyUpdateFiles;
use idoit\Module\Console\Steps\VersionAndRevisionCheck;
use idoit\Module\Console\Steps\VersionCheck;
use isys_application;
use isys_convert;
use isys_exception_filesystem;
use isys_update;
use isys_update_log;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * First step of the update - check the environment, unzip, copy files
 */
class UpdateFirstStepCommand extends UpdateBase
{
    /**
     *
     * @throws InvalidArgumentException
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('update-1')
            ->setHidden(true);
    }

    /**
     * @return Step
     */
    protected function createStep()
    {
        global $g_absdir;

        /**
         * @see ID-8231 There has been a change in a symfony console helper which leads to an error when
         *              updating the files before loading the necessary classes.
         *              In order to fix this issue we introduce this "dummy table renderer" which will load
         *              the neccesary classes into memory and we can update the symfony console (to 5.3) in our
         *              next i-doit major update (1.18).
         *              It should be okay to remove this line of code before the 1.18 release - it is only necessary
         *              in the i-doit 1.17.* code :)
         */
        (new SymfonyStyle(new ArrayInput([]), new NullOutput()))->table(['Initialization of table helpers'], [['Bugfix for ID-8231']]);

        include_once $g_absdir . '/updates/constants.inc.php';

        $db = isys_application::instance()->container->get('database_system');

        $g_temp_dir = $g_absdir . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR;
        $g_log_dir = $g_absdir . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR;

        $debugFile = date("Y-m-d") . '-' . $this->getValue('version') . '_idoit_update.log';
        $debugLog = $g_log_dir . $debugFile;

        $log = isys_update_log::get_instance();
        register_shutdown_function(function () use ($log, $debugLog) {
            $log->write_debug(basename($debugLog));
        });

        global $g_admin_auth, $g_crypto_hash, $g_disable_addon_upload, $g_enable_gui_update, $g_license_token, $g_is_cloud, $g_active_features;
        $admin = key($g_admin_auth);

        $environmentChecks = [
            $this->preparePhpChecks(),
            $this->prepareSqlCheck(),
        ];

        $incompatibleAddons = (new AddonVerify())->getIncompatibleAddons();

        foreach ($incompatibleAddons as $addon) {
            $incompatibleAddons[] = "{$addon['title']} (at least version {$addon['compatibleVersion']}, you have {$addon['currentVersion']})";
        }

        if (!empty($incompatibleAddons)) {
            $environmentChecks[] = new ConstantStep('There are incompatible add-ons: ' . implode(', ', $incompatibleAddons), false, ErrorLevel::ERROR);
        }

        $currentState = (new isys_update())->get_isys_info();

        $zipFilePath = $this->getValue('zip') ?? '';
        $updatePath = $this->getValue('update.path') . $this->getValue('version');

        return new CollectionStep('i-doit update', [
            new AuthorisationStep($this->getValue('system.user'), $this->getValue('system.password')),
            new CollectionStep('Environment Check', $environmentChecks),
            new CollectionStep('Process update', [
                new VersionCheck('i-doit Version check', $this->getValue('version'), ['>=' . $currentState['version']], ErrorLevel::ERROR),
                new FileExistsCheck($zipFilePath, true, ErrorLevel::ERROR),
                new Unzip($zipFilePath, $g_absdir),
                new FileExistsCheck($updatePath . '/update_sys.xml', true, ErrorLevel::ERROR),
                new VersionAndRevisionCheck($updatePath . '/update_sys.xml', $currentState, ErrorLevel::ERROR), // @see ID-10810 Additionally check the revision.
                new CopyUpdateFiles($updatePath . '/files'),
                new IfCheck(
                    'Config file should be updated',
                    new FileExistsCheck($updatePath . '/config_template.inc.php', true, ErrorLevel::ERROR),
                    new CollectionStep('Upgrade config', [
                        new FileMove($this->getValue('directory.config'), dirname($this->getValue('directory.config')) . '/config.inc.' . date("Ymdhis") . '.php'),
                        new TemplateFile('Config File', $updatePath . '/config_template.inc.php', $this->getValue('directory.config'), [
                            '%config.db.host%'                              => $db->get_host(),
                            '%config.db.port%'                              => $db->get_port(),
                            '%config.db.username%'                          => $db->get_user(),
                            '%config.db.password%'                          => $db->get_pass(),
                            '%config.db.name%'                              => $db->get_db_name(),
                            '%config.adminauth.username%'                   => $admin,
                            '%config.adminauth.password%'                   => $g_admin_auth[$admin],
                            '%config.license.token%'                        => $g_license_token,
                            '%config.crypt.hash%'                           => $g_crypto_hash,
                            '%config.admin.disable_addon_upload%'           => $g_disable_addon_upload ?: 0,
                            '%config.admin.enable_gui_update%'              => $g_enable_gui_update ?? 1,
                            '%config.security.passwords_encryption_method%' => \isys_helper_crypt::getEncryptionMethod(),
                            '%config.cloud.active%'                         => is_numeric($g_is_cloud) ? (int)$g_is_cloud : 0,
                            '%config.active_features.list%'                 => is_array($g_active_features) ? implode("','", $g_active_features) : '',
                        ]),
                    ])
                ),
            ])
        ]);
    }

    /**
     * Prepares the checks of Sql version
     *
     * @return Step
     */
    private function prepareSqlCheck()
    {
        $db = isys_application::instance()->container->get('database_system');

        $result = $db->query('SELECT VERSION() AS v;');
        $row = $db->fetch_row_assoc($result);
        $rawDbVersion = $row['v'];
        $dbVersion = getVersion($rawDbVersion);
        $isMariaDb = stripos($rawDbVersion, 'maria') !== false;

        $dbTitle = $isMariaDb ? 'MariaDB' : 'MySQL';
        $dbMinimumVersion = $isMariaDb ? UPDATE_MARIADB_VERSION_MINIMUM : UPDATE_MYSQL_VERSION_MINIMUM;
        $dbMaximumVersion = $isMariaDb ? UPDATE_MARIADB_VERSION_MAXIMUM : UPDATE_MYSQL_VERSION_MAXIMUM;
        $dbRecommendedVersion = $isMariaDb ? UPDATE_MARIADB_VERSION_MINIMUM_RECOMMENDED : UPDATE_MYSQL_VERSION_MINIMUM_RECOMMENDED;
        $dbDeprecatedBelowVersion = $isMariaDb ? UPDATE_MARIADB_VERSION_DEPRECATED_BELOW : UPDATE_MYSQL_VERSION_DEPRECATED_BELOW;

        $checks = [
            new VersionCheck($dbTitle, $dbVersion, [
                '>=' . $dbMinimumVersion,
                '<=' . $dbMaximumVersion,
            ]),
            new VersionCheck($dbTitle . ' recommended check', $dbVersion, [
                '>=' . $dbRecommendedVersion,
            ], ErrorLevel::NOTIFICATION),
            new VersionCheck($dbTitle . ' deprecated check', $dbVersion, [
                '>' . $dbDeprecatedBelowVersion,
            ]),
        ];

        return new CollectionStep('Sql Check', $checks);
    }

    /**
     * Prepare the checks of the PHP environment
     *
     * @return CollectionStep
     *
     * @throws isys_exception_filesystem
     */
    private function preparePhpChecks()
    {
        $phpVersion = implode('.', [PHP_MAJOR_VERSION, PHP_MINOR_VERSION, PHP_RELEASE_VERSION]);

        $configurationChecks = [
            new PhpIniCheck('magic_quotes_gpc: off', function ($value) {
                return !$value;
            }, ErrorLevel::NOTIFICATION),
            new PhpIniCheck('max_input_vars > 10000', function ($value) {
                return 0 === $value || $value >= 10000;
            }, ErrorLevel::NOTIFICATION),
            new PhpIniCheck('post_max_size > 128M', function ($value) {
                $bytes = isys_convert::to_bytes($value);

                return 0 === $bytes || $bytes >= isys_convert::to_bytes('128M');
            }, ErrorLevel::NOTIFICATION)
        ];
        $configurationChecks = array_merge($configurationChecks, array_map(function ($dependency) {
            return new PhpExtensionCheck($dependency);
        }, array_keys(isys_update::get_module_dependencies())));
        $configurationChecks = array_merge($configurationChecks, array_map(function ($dependency) {
            return new PhpExtensionCheck($dependency, ErrorLevel::NOTIFICATION);
        }, array_keys(isys_update::get_module_dependencies(null, 'apache'))));

        return new CollectionStep('PHP Check', [
            new VersionCheck('PHP', $phpVersion, [
                '>=' . UPDATE_PHP_VERSION_MINIMUM,
                '<=' . UPDATE_PHP_VERSION_MAXIMUM
            ]),
            new VersionCheck('PHP deprecated check', $phpVersion, [
                '>=' . UPDATE_PHP_VERSION_DEPRECATED_BELOW,
            ], ErrorLevel::NOTIFICATION),
            new VersionCheck('PHP recommended check', $phpVersion, [
                '>=' . UPDATE_PHP_VERSION_MINIMUM_RECOMMENDED,
            ], ErrorLevel::NOTIFICATION),
            new CollectionStep('Configuration', $configurationChecks, true, false)
        ]);
    }
}
