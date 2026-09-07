<?php

namespace idoit\Console\Command;

use Exception;
use idoit\Component\FeatureManager\FeatureManager;
use idoit\Component\Security\Hash\PasswordVerify;
use idoit\Console\Exception\InvalidCredentials;
use idoit\Exception\ExpiredLicenseException;
use isys_application;
use isys_auth;
use isys_auth_system;
use isys_component_session;
use isys_exception_auth;
use isys_module_licence;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractCommand extends Command implements LoginAwareInterface
{
    /**
     * Require login by default, may be overwritten by subclasses
     */
    const REQUIRES_LOGIN = true;

    const AUTH_DOMAIN_TENANT = 'tenant';
    const AUTH_DOMAIN_SYSTEM = 'system';

    /**
     * Which auth domain should be used?
     */
    const AUTH_DOMAIN = self::AUTH_DOMAIN_TENANT;
    const HIDE_COMMAND = false;

    /**
     * @var isys_auth_system
     */
    protected $auth;

    /**
     * @var isys_component_session
     */
    protected $session;

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function setAuth(isys_auth_system $auth)
    {
        $this->auth = $auth;
    }

    public function setSession(isys_component_session $session)
    {
        $this->session = $session;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * Get name for command
     *
     * @return string
     */
    abstract public function getCommandName();

    /**
     * Get description for command
     *
     * @return string
     */
    abstract public function getCommandDescription();

    /**
     * Retrieve Command InputDefinition
     *
     * @return InputDefinition
     */
    abstract public function getCommandDefinition();

    /**
     * Checks if a command can have a config file via --config
     *
     * @return bool
     */
    abstract public function isConfigurable();

    /**
     * Returns an array of command usages
     *
     * @return string[]
     */
    abstract public function getCommandUsages();

    /**
     * @return array
     */
    public function getCommandAliases()
    {
        return [];
    }

    /**
     * Pre configure child commands
     */
    protected function configure()
    {
        $this->setName($this->getCommandName());
        $this->setDescription($this->getCommandDescription());
        $this->setHidden($this::HIDE_COMMAND);
        $this->setAliases($this->getCommandAliases());

        $commandDefinition = $this->getCommandDefinition();

        if ($this->requiresLogin()) {
            // ensure login arguments
            $commandDefinition->addOption(new InputOption('user', 'u', InputOption::VALUE_REQUIRED, 'User'));
            $commandDefinition->addOption(new InputOption('password', 'p', InputOption::VALUE_REQUIRED, 'Password'));
            $commandDefinition->addOption(new InputOption('tenantId', 'i', InputOption::VALUE_REQUIRED, 'Tenant ID', 1));
        }

        if ($this->isConfigurable()) {
            $commandDefinition->addOption(new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Config File'));
        }

        $this->setDefinition($commandDefinition);

        foreach ($this->getCommandUsages() as $usage) {
            $this->addUsage((string)$usage);
        }
    }

    /**
     * Retrieves a config file
     *
     * @param InputInterface $input
     *
     * @return string|null
     */
    public function getConfigFile(InputInterface $input)
    {
        return $input->getOption('config');
    }

    /**
     * @return void
     * @throws Exception
     * @see ID-11449 Re-run the i-doit bootstrapping to include all add-on services.
     */
    private function rerunModuleLoader(): void
    {
        isys_application::instance()
            ->bootstrap();
    }

    /**
     * Login an user with User, Password and tenantId as requirements
     *
     * @param InputInterface $input
     *
     * @return bool
     * @throws ExpiredLicenseException
     * @throws isys_exception_auth
     */
    public function login(InputInterface $input)
    {
        $checkCommandRight = true;
        $hasUsername = (bool)$input->getOption('user');
        $hasPassword = (bool)$input->getOption('password');

        if (static::AUTH_DOMAIN === static::AUTH_DOMAIN_SYSTEM) {
            global $g_admin_auth;

            if ($hasUsername === false || $hasPassword === false) {
                throw new Exception('Missing credentials for login, please provide: ' .
                    ($hasUsername ? '' : '--user ') .
                    ($hasPassword ? '' : '--password '));
            }

            $username = $input->getOption('user');
            $password = $input->getOption('password');

            $pw = is_array($g_admin_auth) && isset($g_admin_auth[$username]) ? $g_admin_auth[$username] : '';

            if (!$pw) {
                throw new InvalidCredentials("Unable to find user with username '{$username}'.");
            }

            if (!PasswordVerify::instance()
                ->verify($password, $pw)) {
                throw new InvalidCredentials("Unable to authorize user with given credentials.");
            }

            $this->rerunModuleLoader();
            return true;
        }

        if (!$input->getOption('tenantId')) {
            throw new Exception('Missing credentials for login, please provide: --tenantId');
        }

        // @see ID-10989 Implement specific login logic for cloud users.
        if (FeatureManager::isCloud() && $hasUsername === false && $hasPassword === false) {
            // Process the 'system user' login.
            if (!$this->cloudLogin((int)$input->getOption('tenantId'))) {
                throw new InvalidCredentials(
                    'Unable to login with given user credentials. Please check --user and --password for validity and try again.'
                );
            }

            $checkCommandRight = false;
        } else {
            // Notify the user if 'username' or 'password' is missing.
            if ($hasUsername === false || $hasPassword === false) {
                throw new Exception('Missing credentials for login, please provide: ' .
                    ($hasUsername ? '' : '--user ') .
                    ($hasPassword ? '' : '--password '));
            }

            // Process the login.
            if (!$this->session->weblogin($input->getOption('user'), $input->getOption('password'), $input->getOption('tenantId'))) {
                throw new InvalidCredentials(
                    'Unable to login with given user credentials. Please check --user and --password for validity and try again.'
                );
            }
        }

        if (class_exists(isys_module_licence::class)) {
            $licence = new isys_module_licence();
            $licence->verify();

            if ($licence->isThisTheEnd()) {
                // @see ID-9166 Use new exception here to catch it (if necessary).
                throw new ExpiredLicenseException('Your i-doit license is missing for over 30 days. Please install a valid license in the i-doit admin center.');
            }
        }

        $this->session->include_mandator_cache();

        if (!$this->auth) {
            $this->auth = isys_auth_system::instance();
        }

        if ($checkCommandRight) {
            $authRight = 'COMMAND/' . strtolower(substr(static::class, strrpos(static::class, '\\') + 1));

            if (!$this->auth->is_allowed_to(isys_auth::EXECUTE, $authRight)) {
                throw new isys_exception_auth('Not allowed to execute: ' . static::class . "\n\r" . "You'll need the right " . $authRight .
                    ' in order to execute the command.');
            }
        }

        // @see ID-11738 Fetch the current language. This was set properly by login and session setup.
        $loadedLanguage = isys_application::instance()->container->get('language')->get_loaded_language();

        $this->rerunModuleLoader();

        /** @var \isys_component_template_language_manager $newLanguageInstance */
        $newLanguageInstance = isys_application::instance()->container->get('language');

        // @see ID-11738 Set the previously loaded language. This might differ because the container was re-built.
        if ($loadedLanguage !== $newLanguageInstance->get_loaded_language()) {
            $newLanguageInstance->load($loadedLanguage);
        }

        return true;
    }

    /**
     * @param int $tenantId
     *
     * @return bool
     * @throws Exception
     * @see ID-10989 Implement new logic to do a 'cloud login' which works similar to the 'apikey_login' method.
     */
    private function cloudLogin(int $tenantId): bool
    {
        $db = isys_application::instance()->container->get('database');
        $this->session->connect_mandator($tenantId);

        if (!$db->is_connected()) {
            throw new Exception('Could not connect tenant database.');
        }

        $this->session->renewSessionId();
        $this->session->delete_current_session();
        $this->session->start_dbsession();

        $normalStatus = (int)C__RECORD_STATUS__NORMAL;
        $query = "SELECT isys_cats_person_list__title AS username
            FROM isys_obj
            INNER JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_obj__id
            LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
            WHERE isys_obj__const = 'C__OBJ__PERSON_API_SYSTEM'
            AND isys_cats_person_list__disabled_login = 0
            AND isys_obj__status = {$normalStatus}
            LIMIT 1;";

        $result = $db->query($query);

        if ($result === null || $db->num_rows($result) === 0) {
            throw new Exception('Your i-doit system (API) user is not available, please check its status and if the user is allowed to login.');
        }

        $userData = $db->fetch_row_assoc($result);

        return $this->session->login($db, $userData['username'], '', true, false, true);
    }

    /**
     * Logout an user
     *
     * @return boolean
     */
    public function logout()
    {
        return $this->session->logout();
    }

    /**
     * Requires command login via session ?
     *
     * @return boolean
     */
    public function requiresLogin()
    {
        return $this::REQUIRES_LOGIN;
    }
}
