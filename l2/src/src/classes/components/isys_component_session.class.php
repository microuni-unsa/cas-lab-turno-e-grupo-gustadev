<?php

use idoit\Component\FeatureManager\FeatureManager;
use idoit\Component\Helper\Unserialize;
use idoit\Component\Logger;
use idoit\Component\Login\AbstractUser;
use idoit\Component\Login\SsoUser;
use idoit\Component\Login\Tenant;
use idoit\Component\Login\User;
use idoit\Context\Context;

/**
 * i-doit
 * Session manager. Providers basic session management
 *
 * @package     i-doit
 * @subpackage  Components
 * @author      Andre Woesten <awoesten@i-doit.de>
 * @author      Dennis Stuecken <dstuecken@i-doit.de>
 * @version     0.9
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */
class isys_component_session extends isys_component
{
    const MIN_SESSION_TIME = 60;
    const MAX_SESSION_TIME = 99999;

    /**
     * @var  isys_component_session
     */
    private static $m_instance = null;

    /**
     * @var  array|null
     */
    protected $m_mandator_data = [];

    /**
     * @var  integer|null
     */
    protected $m_mandator_id;

    /**
     * @var  string|null
     */
    protected $m_mandator_name;

    /**
     * Session specific error messages.
     *
     * @var  array
     */
    private $m_err;

    /**
     * @var  string
     */
    private $m_language;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var  boolean
     */
    private $m_logged_in = false;

    /** @var isys_module_ldap|null  */
    private ?isys_module_ldap $moduleLdap = null;

    /**
     * @var  array
     */
    private $m_session_data;

    /**
     * @var  string|null
     */
    private $m_session_id;

    /**
     * @var  integer
     *
     * Set initial value to a very high value, since sessions get cleaned on a login with this default value.
     * The original session time for each mandator is only available AFTER logging in.
     */
    private $m_session_time = 99999999;

    /**
     * @var  integer|null
     */
    private $m_user_id;

    /**
     * @var  array
     */
    private $m_userdata = [];

    /**
     * @var  string|null
     */
    private $m_username = null;

    /**
     * @var AbstractUser|null
     */
    private $userEntity = null;

    /**
     * @var bool|null
     */
    protected $session_started = null;

    /**
     * @throws Exception
     */
    private function __construct()
    {
        // LDAP Module dependency injection.
        if (class_exists('isys_module_ldap') && FeatureManager::isFeatureActive('ldap-login')) {
            $this->moduleLdap = new isys_module_ldap();
        }

        $logPath = isys_tenantsettings::get('system.log.path', BASE_DIR . '/log') . "/failed_logins.log";
        $this->logger = Logger::factory('login', $logPath);

        if (isset($_SESSION['userEntity'])) {
            // @see ID-8760  In order to be able to use '$this->getUserEntity' we'll set it from the session.
            $userEntity = Unserialize::toObject($_SESSION['userEntity'], [User::class, SsoUser::class, Tenant::class]);

            if ($userEntity instanceof AbstractUser) {
                $this->setUserEntity($userEntity);
            }
        }
    }

    /**
     * Get singleton instance.
     *
     * @return  \isys_component_session
     */
    final public static function instance()
    {
        if (!self::$m_instance) {
            self::$m_instance = new self();
        }

        return self::$m_instance;
    }

    /**
     * @param  integer $p_session_time
     */
    public function set_session_time($p_session_time)
    {
        if ($p_session_time > 0) {
            $this->m_session_time = $p_session_time;
        }
    }

    /**
     * @return  integer
     */
    public function get_session_time()
    {
        return $this->m_session_time;
    }

    /**
     * Checks if given session time is a valid value
     *
     * @param $sessionTime
     *
     * @return bool
     * @author Kevin Mauel <kmauel@i-doit.com>
     */
    public static function isValidSessionTime($sessionTime)
    {
        return $sessionTime >= self::MIN_SESSION_TIME && $sessionTime <= self::MAX_SESSION_TIME;
    }

    /**
     * @return  string
     */
    public function get_language()
    {
        return $this->m_language;
    }

    /**
     * @param string $key
     * @param mixed  $fallback
     *
     * @return mixed|null
     */
    public function get(string $key, $fallback = null)
    {
        if ($this->has($key)) {
            return $_SESSION['data'][$key];
        }

        // @todo This is fallback behaviour and should probably be updated - look for usages of 'get'.
        return $_SESSION[$key] ?? $fallback;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION['data'][$key]);
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $_SESSION['data'][$key] = $value;
    }

    /**
     * @param string $key
     *
     * @return void
     */
    public function unset(string $key): void
    {
        if ($this->has($key)) {
            unset($_SESSION['data'][$key]);
        }
    }

    /**
     * @param   string $p_language
     *
     * @return  $this
     */
    public function set_language($p_language)
    {
        global $g_idoit_language_short;

        if (!$p_language) {
            // Initialize a default language (since $g_idoit_language_short is used by the init.php scripts to include the language file).
            $p_language = 'en';
        }

        // @see ID-11762 Set a flag if the language was changed.
        if ($_SESSION["lang"] !== $p_language) {
            $_SESSION['recordLanguageChanged'] = true;
        }

        $g_idoit_language_short = $_SESSION["lang"] = $this->m_language = $p_language;

        isys_application::instance()
            ->language($p_language);

        return $this;
    }

    /**
     * @return array
     */
    public function get_userdata()
    {
        return $this->m_userdata;
    }

    /**
     * @return mixed
     */
    public function get_current_username()
    {
        return $this->m_username;
    }

    /**
     * @return array
     */
    public function get_mandator_data()
    {
        return $this->m_mandator_data;
    }

    /**
     * @return mixed
     */
    public function get_user_id()
    {
        if (!$this->m_user_id) {
            $l_sessdata = $this->get_session_data();

            $this->m_user_id = $l_sessdata["isys_user_session__isys_obj__id"];
        }

        return $this->m_user_id;
    }

    /**
     * @param      $p_msg
     * @param null $p_key
     */
    public function add_error($p_msg, $p_key = null)
    {
        if (!is_null($p_key)) {
            $this->m_err[$p_key] = $p_msg;
        } else {
            $this->m_err[] = $p_msg;
        }
    }

    /**
     * @return array
     */
    public function get_errors()
    {
        return $this->m_err;
    }

    /**
     * @param $p_mname
     *
     * @return isys_component_session
     */
    public function set_mandator_name($p_mname)
    {
        $this->m_mandator_name = $p_mname;

        return $this;
    }

    /**
     * @return mixed
     */
    public function get_mandator_name()
    {
        return $this->m_mandator_name;
    }

    /**
     * @return mixed
     */
    public function get_mandator_id()
    {
        return $this->m_mandator_id;
    }

    /**
     * @return mixed
     */
    public function get_user_session_id()
    {
        $l_sessdata = $this->get_session_data();

        return $l_sessdata["isys_user_session__id"];
    }

    /**
     * Is the user logged in? TRUE, if yes, FALSE, if error or no.
     *
     * @return  boolean
     */
    public function is_logged_in()
    {
        if (is_array($this->m_session_data) && isset($this->m_session_data["isys_user_session__isys_obj__id"])) {
            // Returns true, if session is _not_ binded to a guest user.
            return true;
        } else {
            if (!count($_SESSION)) {
                return false;
            }

            if ($this->m_session_data) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $userData
     *
     * @throws Exception
     */
    private function processPostLogin($userData): void
    {
        $db = isys_application::instance()->container->get('database');
        $this->post_init_session($userData);

        // @see ID-10059 Always regenerate the session ID.
        $this->renewSessionId();

        if ((int)isys_tenantsettings::get('logging.user.last-login', 0) !== 0) {
            $db->query('UPDATE isys_cats_person_list SET isys_cats_person_list__last_login = NOW() WHERE isys_cats_person_list__isys_obj__id = ' .
                (int)$userData["isys_obj__id"] . ';');
        }
    }

    /**
     * Change current mandator.
     * Works only if username and password are same for the new mandator. Uses current user to double check.
     *
     * @param   integer $p_mandator_id
     *
     * @return  boolean
     * @throws  Exception
     */
    public function change_mandator($p_mandator_id)
    {
        if (!isset($_SESSION['userEntity'])) {
            return false;
        }

        $userEntity = Unserialize::toObject($_SESSION['userEntity'], [User::class, SsoUser::class, Tenant::class]);

        if (!$userEntity instanceof AbstractUser) {
            return false;
        }

        if (!$userEntity->hasTenant((int) $p_mandator_id)) {
            return false;
        }

        $db = isys_application::instance()->container->get('database');
        $currentMandatorId = $this->get_mandator_id();

        if (is_object($db)
            && $db->is_connected()
            && $p_mandator_id > 0
        ) {
            try {
                // @see ID-8635 Connecto to new mandator before retrieving the userdata
                $this->connect_mandator($p_mandator_id);

                // @see ID-10014 Pass the status when fetching a user.
                $l_data = isys_cmdb_dao_category_s_person_master::instance($db)
                    ->get_person_by_username($this->get_current_username(), C__RECORD_STATUS__NORMAL)
                    ->get_row();

                if ($l_data) {
                    $this->delete_current_session();
                    $this->start_dbsession();
                    $this->processPostLogin($l_data);

                    if (isys_application::isPro() && defined('C__ENABLE__LICENCE') && C__ENABLE__LICENCE === true && class_exists('isys_module_licence')) {
                        // Overwrite License related keys in session from previous mandator
                        // todo licensing 2.0
                        $l_licence = new isys_module_licence();
                        $l_licence->verify();

                        if ($l_licence->isThisTheEnd() && !$l_licence->isTrial()) {
                            $adminCenterUrl = isys_application::instance()->www_path . 'admin';

                            echo '<div class="box-red p5">' .
                                'Your i-doit license is missing for over 30 days.<br />' .
                                'Please install a valid license in the i-doit <a href="' . $adminCenterUrl . '">admin center</a>.' .
                                '</div>';

                            $this->logout();
                            die;
                        }
                    }
                    return true;
                } else {
                    // @see ID-8635 User does not exist connect to the old tenant and prevent using the mandator
                    $userEntity->removeFromAvailableTenants($p_mandator_id);
                    $this->connect_mandator($currentMandatorId);
                }
            } catch (Exception $e) {
                throw $e;
            }
        }

        return false;
    }

    /**
     * Returns dao for mandator.
     *
     * @param    integer $p_mandator_id
     *
     * @return   resource
     * @version  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function get_mandator_dao($p_mandator_id)
    {
        return (new isys_component_dao_mandator)->get_mandator_query($p_mandator_id);
    }

    /**
     * Does a complete weblogin, including session storage,
     * session initialization and mandator selection
     *
     * @author Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function weblogin($p_user, $p_pass, $p_mandator, $p_md5 = false)
    {
        $db = isys_application::instance()->container->get('database');

        if (!$this->is_logged_in() && $p_user && $p_pass) {
            if (is_numeric($p_mandator)) {
                if ($this->connect_mandator($p_mandator) !== null) {
                    if (is_object($db) && $db->is_connected()) {
                        $this->renewSessionId();
                        $this->delete_current_session();
                        $this->start_dbsession();

                        return $this->login($db, $p_user, $p_pass, true, $p_md5);
                    }
                } else {
                    throw new Exception('Could not connect to tenant with id ' . $p_mandator);
                }
            } else {
                throw new Exception("Login failed. Either username (" . $p_user . "), password or tenant (" . $p_mandator . ") information is not correct.");
            }
        }

        return $this->is_logged_in();
    }

    /**
     * Does a mandator login based on its apikey
     *
     * @param       $p_apikey
     * @param array $p_userdata
     * @param int   $p_session_id
     *
     * @throws \Exception
     * @throws \isys_exception_api
     * @return bool
     */
    public function apikey_login($p_apikey, $p_userdata = null, $p_session_id = null)
    {
        $db = isys_application::instance()->container->get('database');

        if (!$this->is_logged_in() && !empty($p_apikey)) {
            foreach ($this->fetchTenants($p_apikey) as $mandator) {
                $this->connect_mandator($mandator['isys_mandator__id']);

                if ($db->is_connected()) {
                    // Check for an existing session
                    if ($p_session_id && strlen($p_session_id) > 2) {
                        // Try to connect to an existing session
                        $l_session_data = $this->get_session_data($p_session_id);

                        if ($this->is_logged_in()) {
                            // Post init session and write session data to $_SESSION
                            $this->post_init_session($l_session_data);

                            return true;
                        }
                    }

                    $this->delete_current_session();
                    $this->start_dbsession();

                    if ($p_userdata) {
                        if ($this->login($db, $p_userdata['username'], $p_userdata['password'], true)) {
                            $l_object_data = $db->fetch_row_assoc($this->query_session($db, null, $this->m_session_id));
                            $this->post_init_session($l_object_data);
                            return true;
                        }
                        return false;
                    } else {
                        // @see API-399 Revert to system setting.
                        if (isys_settings::get('api.authenticated-users-only', 1)) {
                            // API option 'api.authenticated-users-only' is activated and no user is specified
                            throw new isys_exception_api(
                                'Please specify a user by RPC Session header or HTTP Basic Authentication.',
                                -32604
                            );
                        }

                        $normalStatus = (int)C__RECORD_STATUS__NORMAL;

                        // @see ID-9113 Check if the API user is in status normal and the login is not disabled.
                        $apiUserQuery = "SELECT
                                isys_obj__id,
                                isys_cats_person_list__isys_obj__id,
                                isys_cats_person_list__title,
                                isys_cats_person_list__first_name,
                                isys_cats_person_list__last_name,
                                isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address
                            FROM isys_obj
                            INNER JOIN isys_cats_person_list ON isys_cats_person_list__isys_obj__id = isys_obj__id
                            LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
                            WHERE isys_obj__const = 'C__OBJ__PERSON_API_SYSTEM'
                            AND isys_cats_person_list__disabled_login = 0
                            AND isys_obj__status = {$normalStatus}
                            LIMIT 1;";

                        $l_object = $db->query($apiUserQuery);

                        if ($l_object !== null && $db->num_rows($l_object)) {
                            $l_object_data = $db->fetch_row_assoc($l_object);

                            if ($l_object_data && isset($l_object_data['isys_cats_person_list__isys_obj__id'])) {
                                // Take over session from "Root-Location", retrieve object data and post-init the session
                                $this->write_userid($l_object_data['isys_obj__id']);
                                $l_object_data = $db->fetch_row_assoc($this->query_session($db, null, $this->m_session_id));
                                $this->post_init_session($l_object_data);

                                return true;
                            }
                        } else {
                            // @see ID-9336 Give the user a proper error message.
                            throw new Exception('Your i-doit system API user is not available, please check its status and if the user is allowed to login.');
                        }
                    }
                }
                throw new Exception('Could not connect tenant database.');
            }

            return false;
        }

        return $this->is_logged_in();
    }

    /**
     * @param array $p_cats_person_data
     *
     * @return void
     */
    public function write_userdata($p_cats_person_data)
    {
        if (isset($p_cats_person_data["isys_cats_person_list__isys_obj__id"])) {
            $this->m_logged_in = true;

            $this->write_userid($p_cats_person_data["isys_cats_person_list__isys_obj__id"]);

            $_SESSION["username"] = $this->m_username = $p_cats_person_data['isys_cats_person_list__title'];
            $this->m_userdata = [
                'name'  => $p_cats_person_data["isys_cats_person_list__first_name"] . ' ' . $p_cats_person_data["isys_cats_person_list__last_name"],
                'email' => isset($p_cats_person_data["isys_cats_person_list__mail_address"]) ? $p_cats_person_data["isys_cats_person_list__mail_address"] : '',
                'id'    => $p_cats_person_data["isys_cats_person_list__isys_obj__id"]
            ];
        }
    }

    /**
     * @return array|NULL
     *
     * @param integer $p_mandator_id
     *
     * @desc Connects to a mandator specified by $p_mandator_id, writes session info
     *       and saves the database access object on container's database
     */
    public function connect_mandator($p_mandator_id)
    {
        global $g_mandator_info, $g_crypto_hash;

        $database_system = isys_application::instance()->container->get('database_system');
        $db = isys_application::instance()->container->get('database');

        $l_res = $this->get_mandator_dao($p_mandator_id);

        if ($database_system->num_rows($l_res)) {
            $l_dbdata = $database_system->fetch_row_assoc($l_res);

            if ($l_dbdata) {
                try {
                    // @see ID-11766 Use tenant Crypto hash
                    $g_crypto_hash = $l_dbdata['isys_mandator__crypto_hash'] ?? $g_crypto_hash;
                    /**
                     * Create tenant database
                     *
                     * @return isys_component_database
                     * @throws Exception
                     */
                    $db->setDatabase(isys_component_database::get_database(
                        $this->getSystemDbType(),
                        $l_dbdata["isys_mandator__db_host"],
                        $l_dbdata["isys_mandator__db_port"],
                        $l_dbdata["isys_mandator__db_user"],
                        isys_component_dao_mandator::getPassword($l_dbdata),
                        $l_dbdata["isys_mandator__db_name"]
                    ));

                    // Create connection to mandator DB
                    $g_mandator_info = $l_dbdata;

                    $this->set_mandator_name($l_dbdata["isys_mandator__title"]);

                    $this->m_mandator_data = $l_dbdata;
                    $this->m_mandator_id = (int)$p_mandator_id;
                    $_SESSION["user_mandator"] = (int)$p_mandator_id;
                    $this->m_logged_in = $this->is_logged_in();

                    return $l_dbdata;
                } catch (isys_exception_database $e) {
                    isys_application::instance()->container->get('notify')->error($e->getMessage());
                }
            }
        }

        return null;
    }

    /**
     * @return array
     *
     * @param string $p_username
     * @param string $p_password
     * @param bool   $p_md5_pass
     *
     * @desc On login, we need to know the mandators, to which
     *       a user is allowed to connect. This function returns
     *       an array with an option array for Smarty in this
     *       format:
     *
     *       <code>
     *        array(
     *         idoit_system.isys_mandator.isys_mandator__id =>
     *          idoit_system.isys_mandator.isys_mandator__title
     *        );
     *       </code>
     *
     *       There can't be real failure, but if there is one,
     *       the array length is also 0. Take a look at the debugger
     *       to see occured errors/warnings.
     */
    public function fetch_mandators($p_username, $p_password, $p_md5_pass = false, $is_tennant_switch = false)
    {
        global $g_crypto_hash;

        $l_mandants = [];
        $currentDb = null;

        $mandators = $this->fetchTenants();

        if (!empty($mandators)) {
            $this->userEntity->setUsername($p_username);

            // @see ID-8309 if we fetch the mandators do not loose the current db session
            if ($this->m_logged_in) {
                $currentDb = clone isys_application::instance()->container->get('database');
            }

            $currentHash = $g_crypto_hash;
            foreach ($mandators as $mandator) {
                $g_crypto_hash = $mandator['isys_mandator__crypto_hash'] ?? $g_crypto_hash;

                // Create connection to mandator DB
                $db = isys_component_database::get_database(
                    $this->getSystemDbType(),
                    $mandator["isys_mandator__db_host"],
                    $mandator["isys_mandator__db_port"],
                    $mandator["isys_mandator__db_user"],
                    isys_component_dao_mandator::getPassword($mandator),
                    $mandator["isys_mandator__db_name"]
                );

                // Setting database component in proxy
                isys_application::instance()->container->get('database')->setDatabase($db);
                if ($db->is_connected()) {
                    /* Get user dao and user data */
                    $l_user_dao = new isys_cmdb_dao_category_s_person_master($db);
                    $l_person_dao = new isys_cmdb_dao_category_s_person_login($db);
                    $tenant = new Tenant($mandator['isys_mandator__id'], $mandator['isys_mandator__title']);
                    $l_userdata = $l_user_dao->get_person_by_username($p_username, C__RECORD_STATUS__NORMAL)
                        ->get_row();

                    $l_user_id = -1;
                    /* Try other auths, if user does not exist in database. */
                    if ($l_userdata) {
                        // @See ID-8670 add user to the list if it is a sso login
                        if ($this->userEntity instanceof SsoUser
                            && (int) $l_userdata['isys_cats_person_list__disabled_login'] === 0
                        ) {
                            $l_user_id = $l_userdata["isys_obj__id"];
                        }

                        // Set user id if password was accepted.
                        if ($l_user_id === -1
                            && isys_helper_crypt::checkPassword(
                                $p_password,
                                $l_userdata['isys_cats_person_list__user_pass'],
                                $l_userdata['isys_cats_person_list__unmigrated_password']
                            )
                        ) {
                            $l_user_id = $l_userdata["isys_obj__id"];
                            //update user password encryption if was not updated
                            if ($l_userdata['isys_cats_person_list__unmigrated_password'] || isys_helper_crypt::isPasswordNeedsRehash($l_userdata['isys_cats_person_list__user_pass'])) {
                                $l_person_dao->save(
                                    $l_userdata['isys_cats_person_list__id'],
                                    $l_userdata['isys_cats_person_list__title'],
                                    $p_password,
                                    $l_userdata['isys_cats_person_list__password_reset_email'],
                                    $l_userdata['isys_cats_person_list__description'],
                                    $l_userdata['isys_cats_person_list__status'],
                                    0,
                                    0,
                                    true
                                );
                            }
                        }
                    }

                    if ($l_user_id === -1) {
                        if (!isset($l_ldap_done[$mandator["isys_mandator__db_name"]]) && FeatureManager::isFeatureActive('ldap-login')) {
                            $l_user_id = $this->ldap_login($p_username, $p_password, $l_user_dao);
                            $tenant->setIsLdapLogin(true);
                        }
                    }

                    // Check if user_id is allowed to login.
                    if ($l_user_id > 0) {
                        $tenant->setUserId($l_user_id);
                        $this->userEntity->addToAvailableTenants($tenant);
                        // Removed: isys_rs_system, check if the given user owns at least one group / role.
                        $l_mandants[$mandator["isys_mandator__id"]] = [
                            'id'      => $mandator["isys_mandator__id"],
                            'user_id' => $l_user_id,
                            'title'   => $mandator["isys_mandator__title"]
                        ];
                    }

                    $l_ldap_done[$mandator["isys_mandator__db_name"]] = true;
                }
            }
            $g_crypto_hash = $currentHash;

            // @see ID-8309 write the current db session to the container
            if ($this->m_logged_in && $currentDb instanceof isys_component_database) {
                isys_application::instance()->container->get('database')
                    ->setDatabase($currentDb);
            }

            if (count($l_mandants) === 0) {
                // Log failed login:
                $this->logger->warning('Login failed for user ' . $p_username, [
                    'username'   => $p_username,
                    'ip-address' => $this->getClientIp(),
                    'user-agent' => $_SERVER['HTTP_USER_AGENT']
                ]);
            }
            // Set reachable mandators for user
            $_SESSION['userEntity'] = serialize($this->userEntity);

            return $l_mandants;
        }

        return null;
    }

    /**
     * @param string                                 $username
     * @param string                                 $password
     * @param isys_cmdb_dao_category_s_person_master $userDao
     *
     * @return int
     * @throws Exception
     */
    private function ldap_login(string $username, string $password, isys_cmdb_dao_category_s_person_master $userDao): int
    {
        // Check if module is installed.
        if ($this->moduleLdap === null) {
            return 0;
        }

        try {
            // Call session login in ldap module.
            if ($this->moduleLdap->session_login($username, $password, null, $userDao)) {
                $objectId = $userDao->getObjectIdByUsername($username);

                if (!$objectId) {
                    throw new Exception('Error: User was improperly created.');
                }

                // @see  ID-7673  Remove the auth cache of the logged in user and immediately renew it.
                isys_auth::invalidateUserCache($objectId);

                return $objectId;
            }
        } catch (Exception $e) {
            $this->moduleLdap->debug($e->getMessage());
        }

        return 0;
    }

    /**
     * Logs a user with $p_username and $p_password in to the database specified by $p_db.
     *
     *
     * @param   isys_component_database $p_db
     * @param   string                  $p_username
     * @param   string                  $p_password
     * @param   boolean                 $p_retbool
     * @param   boolean                 $p_md5pass
     * @param   bool                    $directLogin - defines if the user can login without more checks
     *
     * @return  boolean
     * @throws Exception
     * @author  Dennis Stuecken <dstuecken@i-doit.org>
     */
    public function login(isys_component_database $p_db, $p_username, $p_password, $p_retbool = false, $p_md5pass = false, $directLogin = false)
    {
        // Search the user.
        $l_res = $this->query_session($p_db, $p_username);
        if ($this->userEntity === null) {
            $this->userEntity = new User();
        }

        if ($this->userEntity->getUsername() === '') {
            $this->userEntity->setUsername($p_username);
        }

        if ($p_db->num_rows($l_res) >= 1) {
            $this->m_logged_in = $directLogin;

            $l_row = $p_db->fetch_row_assoc($l_res);
            if (!$this->m_logged_in) {
                if (isys_helper_crypt::checkPassword($p_password, $l_row["isys_cats_person_list__user_pass"], $l_row['isys_cats_person_list__unmigrated_password'])) {
                    $this->m_logged_in = true;
                    if ($l_row['isys_cats_person_list__unmigrated_password'] || isys_helper_crypt::isPasswordNeedsRehash($l_row['isys_cats_person_list__user_pass'])) {
                        $l_person_dao = new isys_cmdb_dao_category_s_person_login($p_db);
                        $l_person_dao->save(
                            $l_row['isys_cats_person_list__id'],
                            $l_row['isys_cats_person_list__title'],
                            $p_password,
                            $l_row['isys_cats_person_list__password_reset_email'],
                            $l_row['isys_cats_person_list__description'],
                            $l_row['isys_cats_person_list__status'],
                            0,
                            0,
                            true
                        );
                    }
                } else {
                    if (is_object($this->moduleLdap) && FeatureManager::isFeatureActive('ldap-login')) {
                        if ($this->moduleLdap->ldap_login($p_db, $p_username, $p_password, null, null, $l_row["isys_obj__id"])) {
                            $this->m_logged_in = true;
                        }
                    }
                }
            }

            if ($this->m_logged_in) {
                $this->processPostLogin($l_row);

                return ($p_retbool) ? true : $l_row;
            }
        }
        // Log failed login:
        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        if (Context::instance()->getOrigin() == Context::ORIGIN_CONSOLE) {
            $user_agent = Context::ORIGIN_CONSOLE;
        }
        $this->logger->warning('Login failed for user ' . $p_username, [
            'username'   => $p_username,
            'ip-address' => $this->getClientIp(),
            'user-agent' => $user_agent
        ]);

        return false;
    }

    /**
     * Renew the session id to prevent session fixation attack
     *
     * @return void
     * @throws Exception
     */
    public function renewSessionId(): void
    {
        $db = isys_application::instance()->container->get('database');

        $oldId = $db->escape_string(session_id());

        // DO NOT remove this condition, it will lead to failing API logins (broke due to ID-10059).
        if (defined('WEB_CONTEXT') && WEB_CONTEXT) {
            session_regenerate_id(true);
        }

        $newId = $db->escape_string(session_id());

        // Replace the old session key in the database.
        if (is_object($db) && $db->is_connected()) {
            $query = "UPDATE isys_user_session
                SET isys_user_session__php_sid ='{$newId}'
                WHERE isys_user_session__php_sid = '{$oldId}'
                LIMIT 1;";

            $db->begin();
            $db->query($query);
            $db->commit();
        }
    }

    /**
     * Destroys the current session.
     *
     * @return boolean
     */
    public function destroy(): bool
    {
        try {
            // Session can only be regenerated if session is active
            if (session_status() !== PHP_SESSION_ACTIVE) {
                return false;
            }

            if (headers_sent()) {
                return false;
            }

            // Unset the session (similar to $_SESSION = [];)
            session_unset();

            // Destroy the current session.
            session_destroy();

            // And immediately regenerate the session ID (while destroying the old one).
            session_regenerate_id(true);

            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);

            return true;
        } catch (ErrorException $e) {
            // session already closed
        }

        return false;
    }

    /**
     * session_write_close wrapper
     */
    public function write_close()
    {
        @session_write_close();

        return $this;
    }

    /**
     * @return string
     */
    public function get_tenant_cache_dir()
    {
        if ($this->m_mandator_data && isset($this->m_mandator_data["isys_mandator__dir_cache"])) {
            return $this->m_mandator_data["isys_mandator__dir_cache"];
        }

        return '';
    }

    /**
     * @return mixed
     */
    public function include_mandator_cache()
    {
        if ($this->m_mandator_data["isys_mandator__dir_cache"]) {
            return isys_component_constant_manager::instance()
                ->include_dcm($this->m_mandator_data["isys_mandator__dir_cache"]);
        } else {
            throw new Exception('Error: Tenant cache directory in system database (dir_cache) is not set');
        }
    }

    /**
     * @return boolean
     * @desc Perform user logout, returns a boolean result:
     *       true for success and false for failure.
     */
    public function logout()
    {
        $db = isys_application::instance()->container->get('database');
        /* Cache Session ID before calling session_destroy() */
        $l_SesID = $this->get_session_id();

        /* Drop current session from database */
        $this->delete_current_session();

        if ($this->destroy()) {
            $this->m_username = null;
            $this->m_logged_in = false;
            $this->m_mandator_id = null;
            $this->m_mandator_name = null;
            $this->m_mandator_data = null;
            $this->m_session_id = null;
            $this->m_user_id = null;
            $this->resetSessionData();
        }

        //delete temporary tables which are not used currently
        if (is_object($db) && $db->is_connected()) {
            $l_objDAOTable = new isys_component_dao_table($db);
            $l_objDAOTable->clean_temp_tables_at_logout($l_SesID);
        }

        return true;
    }

    /**
     * @return void
     */
    public function resetSessionData()
    {
        unset($this->m_session_data);
    }

    /**
     * Starts a session.
     *
     * @return  boolean
     */
    public function start_session()
    {
        if (isset($this->session_started) && $this->session_started) {
            return true;
        }

        if (!headers_sent()) {
            $l_session_name = ini_get('session.name');

            if (isset($_COOKIE[$l_session_name]) && $_COOKIE[$l_session_name] == '') {
                unset($_COOKIE[$l_session_name]);
            }

            $l_session_params = session_get_cookie_params();
            if ((empty($l_session_params['path']) || $l_session_params['path'] === '/') && isset($_SERVER['PHP_SELF'])) {
                $l_session_params['path'] = str_replace("\\", "/", dirname(htmlspecialchars($_SERVER['PHP_SELF'])));
            }

            // Check if the ini settings have been set - if not, try to set force it.
            if (session_status() !== PHP_SESSION_ACTIVE && (!$l_session_params['httponly'] || !$l_session_params['secure'])) {
                $useSecureCookies = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
                    || !!isys_application::instance()->getEnv('FORCE_SECURE_COOKIES');

                session_set_cookie_params(
                    $l_session_params['lifetime'] ?: 0,
                    $l_session_params['path'] ?: '/',
                    $l_session_params['domain'] ?: '',
                    $useSecureCookies,
                    true
                );
            }

            // special handling of session id for API call
            if (isset($_SERVER['HTTP_X_RPC_AUTH_SESSION'])) {
                session_id($_SERVER['HTTP_X_RPC_AUTH_SESSION']);
            }
            $l_ret = session_start();
            $_SESSION['recordLanguageChanged'] = false;
            $this->m_username = $_SESSION["username"];

            // @see ID-9144 Implement system setting to disable the behaviour.
            if (isys_settings::get('system.verify-ip-between-requests', 1) && isset($_SESSION['ip'], $_SESSION['user_agent'])) {
                if ($_SESSION['ip'] !== $this->getClientIp() || $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
                    // Then it destroys the session.
                    $this->destroy();
                }
            }
            $this->remember_user();
        } else {
            $l_ret = false;
        }

        $this->session_started = $l_ret;

        return $l_ret;
    }

    /**
     * Load Mandator Info from db and save into session
     *
     * @param isys_component_database $databaseSystem
     *
     * @throws Exception
     */
    public function initMandatorSession(isys_component_database $databaseSystem)
    {
        if ($this->start_session()) {
            // Override session language - At this point, $this->language is only set when isys_application::instance()->set_language('xyz') was called beforehand.
            if (isys_application::instance()->language) {
                $this->set_language(isys_application::instance()->language);
            }

            isys_application::instance()->container->get('database_system')
                ->setDatabase($databaseSystem);

            // Check if mandator is set yet and connects database for current mandator.
            if ($userMandatorId = $this->get("user_mandator")) {
                //connect_mandator function without creating database service
                $l_res = $this->get_mandator_dao($userMandatorId);

                if ($databaseSystem->num_rows($l_res)) {
                    $l_dbdata = $databaseSystem->fetch_row_assoc($l_res);

                    if ($l_dbdata) {
                        $this->set_mandator_name($l_dbdata["isys_mandator__title"]);

                        $this->m_mandator_data = $l_dbdata;
                        $this->m_mandator_id = $userMandatorId;
                        $_SESSION["user_mandator"] = $userMandatorId;
                        $this->m_logged_in = $this->is_logged_in();
                        $this->connect_mandator($this->m_mandator_id);
                    }
                }
            }
        } else {
            $l_err = "Unable to start session!";
            if (headers_sent()) {
                $l_err .= "\nHeaders already sent. There should not be any output before the session starts!";
            }

            throw new Exception($l_err);
        }
    }

    /**
     * Remember client's ip address and user agent
     *
     * Used to prevent session hijacking attacks
     *
     * @return $this
     */
    public function remember_user()
    {
        // Remember ip address and user agent.
        $_SESSION['ip'] = $this->getClientIp();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

        return $this;
    }

    /**
     * Initialize a session in the database.
     *
     * @return  integer
     */
    public function start_dbsession()
    {
        global $_SERVER;
        $db = isys_application::instance()->container->get('database');

        if (is_object($db) && $db->is_connected()) {
            $l_res = $this->query_session($db, null, $this->get_session_id());

            // Is a user session existent ..?
            if ($db->num_rows($l_res) == 0) {
                // NO - so add the session.
                $l_query = "INSERT INTO isys_user_session SET
				    isys_user_session__isys_obj__id = 1,
				    isys_user_session__php_sid = '" . $db->escape_string($this->get_session_id()) . "',
				    isys_user_session__time_login = CURRENT_TIMESTAMP,
				    isys_user_session__time_last_action = CURRENT_TIMESTAMP,
				    isys_user_session__description = '" . $db->escape_string($_SERVER['REQUEST_URI']) . "',
				    isys_user_session__ip = '" . $db->escape_string($this->getClientIp()) . "';";

                if ($db->query($l_query)) {
                    return $db->get_last_insert_id();
                }
            } else {
                // YES - update and use existing session.
                $l_query = "UPDATE isys_user_session SET
					isys_user_session__time_last_action = CURRENT_TIMESTAMP,
					isys_user_session__description = '" . $db->escape_string($_SERVER['REQUEST_URI']) . "'
					WHERE isys_user_session__php_sid = '" . $db->escape_string($this->get_session_id()) . "'";

                $l_query = $db->limit_update($l_query, 1);

                if ($db->query($l_query)) {
                    return $this->get_session_id();
                }
            }
        }

        return null;
    }

    /**
     * Returns the session record as associative array.
     *
     * @param   string $p_sessionid
     *
     * @return  array
     */
    public function get_session_data($p_sessionid = null)
    {
        if (!$this->m_session_data) {
            $db = isys_application::instance()->container->get('database');

            if ($p_sessionid === null) {
                $p_sessionid = $this->get_session_id();
            }

            if (strlen($p_sessionid) > 0) {
                if ($db && $db->is_connected()) {
                    $l_res = $this->query_session($db, null, $p_sessionid);

                    if ($db->num_rows($l_res)) {
                        // User is logged in, so post initialize session.
                        $this->post_init_session($db->fetch_array($l_res));
                    }
                }
            }
        }

        return $this->m_session_data;
    }

    /**
     * @param $p_session_id
     *
     * @return $this
     */
    public function set_session_id($p_session_id)
    {
        $this->m_session_id = session_id($p_session_id);

        return $this;
    }

    /**
     * Returns the current session id.
     *
     * @return  string
     */
    public function get_session_id()
    {
        if (!$this->m_session_id) {
            $this->m_session_id = session_id();
        }

        return $this->m_session_id;
    }

    /**
     * Delete the expired sessions.
     *
     * @return  $this
     */
    public function delete_expired_sessions()
    {
        $db = isys_application::instance()->container->get('database');
        $sessionTimeoutTime = $db->date_sub('SECOND', $this->m_session_time, 'NOW()');

        if (is_object($db) && $db->is_connected()) {
            $query = <<<SQL
DELETE FROM isys_user_session
  WHERE
    isys_user_session__time_last_action < {$sessionTimeoutTime}
SQL;
            $db->begin();
            $db->query($query);
            $db->commit();

            if ($this->is_logged_in()) {
                $query = <<<SQL
SELECT isys_user_session__id FROM isys_user_session
  WHERE
    isys_user_session__time_last_action > {$sessionTimeoutTime}
    AND isys_user_session__isys_obj__id = {$this->get_user_id()}
SQL;
                // Check if user still has a valid session after possibly outdated sessions were deleted
                if ($db->num_rows($db->query($query)) === 0) {
                    // .. and do a logout if not.
                    $this->logout();  // @see ID-3670

                    if (isset($_GET["ajax"])) {
                        // Relocate via javascript if this is an ajax request
                        echo "<script type=\"text/javascript\">document.location='?timeout';</script>";
                        die();
                    } else {
                        // Relocate via Apache if not.
                        header('Location: ?timeout');
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Delete the current session from database.
     *
     * @return  void
     */
    public function delete_current_session()
    {
        $db = isys_application::instance()->container->get('database');

        $_SESSION['groups'] = null;
        $_SESSION['lang'] = null;
        $_SESSION[isys_locale::C__SESSION_CACHE_KEY] = null;
        $this->m_language = null;

        if (is_object($db) && $db->is_connected()) {
            $db->begin();
            $db->query('DELETE FROM isys_user_session WHERE (isys_user_session__php_sid = "' . $db->escape_string($this->get_session_id()) . '");');
            $db->commit();
        }
    }

    /**
     * Initialization, which is only possible after user has logged in
     *
     * @param $p_session_data
     */
    protected function post_init_session($p_session_data)
    {
        // Store session data (very important to do this here)
        $this->m_session_data = $p_session_data;
        $this->write_userdata($p_session_data);

        // Include and write mandator cache
        $this->include_mandator_cache();

        // Initialize user settings.
        try {
            //@todo: settings inits can be removed soon because it's already inited in DI
            isys_usersettings::initialize(isys_application::instance()->container->get('database'));
            isys_tenantsettings::initialize(isys_application::instance()->container->get('database_system'), $this->get_mandator_id());

            // Now that the settings are initialized, we can set the session time for this tenant
            $this->set_session_time(isys_tenantsettings::get('session.time', 300));

            // Delete expired sessions
            $this->delete_expired_sessions();
        } catch (Exception $e) {
            isys_glob_display_error($e->getMessage());
            die();
        }

        // Re-set the language (if necessary).
        $l_lang = $this->get_language();

        if (!$l_lang) {
            if (isset($_SESSION['lang']) && $_SESSION['lang']) {
                $l_lang = $_SESSION['lang'];
            } else {
                $locale = isys_application::instance()->container->get('locales');

                // @see ID-11738 Fetch the current user language or the system default.
                $l_lang = $locale->resolve_language_by_constant($locale->get_setting(LC_LANG, null))
                    ?: isys_tenantsettings::get('system.default-language', C__USE_BROWSER_DEFAULT);

                // @see ID-11762 React to 'browser detection' setting.
                if ($l_lang === null || $l_lang == C__USE_BROWSER_DEFAULT) {
                    $l_lang = isys_application::instance()->container->get('locales')->getPreferredLanguage();
                }
            }
        }

        global $g_comp_template_language_manager;

        /** @var isys_component_template_language_manager $lang */
        $lang = isys_application::instance()->container->get('language');
        $lang->load($l_lang);

        // Assign language manager from container to global for legacy calls
        $g_comp_template_language_manager = $lang;
        $this->set_language($l_lang);
        isys_module_manager::instance()
            ->init(isys_module_request::get_instance());
    }

    /**
     * @return boolean
     *
     * @param integer $p_userid
     *
     * @desc Write a User-ID to a session. It is private and used by the
     *       method 'login'.
     */
    private function write_userid($p_userid)
    {
        $db = isys_application::instance()->container->get('database');

        $this->m_user_id = $p_userid;

        if ($p_userid > 0) {
            $l_query = "UPDATE isys_user_session SET isys_user_session__isys_obj__id ='" . $db->escape_string($p_userid) . "' " . "WHERE isys_user_session__php_sid='" .
                $this->get_session_id() . "';";

            return (bool)$db->query($l_query);
        }

        throw new Exception('There was a problem writing your user id to session.');
    }

    /**
     * Query isys_mandator in system database.
     *
     * @param   string  $p_apikey
     * @param   boolean $p_onlyactive
     *
     * @return  mixed
     */
    private function fetchTenants($p_apikey = null, $p_onlyactive = true)
    {
        $systemDb = isys_application::instance()->container->get('database_system');

        $l_condition = '';

        if ($p_apikey) {
            $l_condition .= ' AND (isys_mandator__apikey = \'' . $systemDb->escape_string($p_apikey) . '\')';
        }

        if ($p_onlyactive) {
            $l_condition .= ' AND (isys_mandator__active = 1)';
        }

        if (!($l_sortby = isys_tenantsettings::get('login.tenantlist.sortby'))) {
            $l_sortby = 'isys_mandator__title';
        }

        $res = $systemDb->query('SELECT * FROM isys_mandator WHERE TRUE ' . $l_condition . ' ORDER BY ' . $l_sortby . ' ASC');
        $result = [];
        while ($row = $systemDb->fetch_row_assoc($res)) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Query isys_user_session in mandator database
     *
     * @param $p_session_id
     *
     * @return mixed
     */
    private function query_session(isys_component_database &$p_db, $p_username = null, $p_session_id = null, $p_condition = '')
    {
        $l_condition = $p_condition;

        if ($p_session_id) {
            $l_condition .= ' AND isys_user_session__php_sid = "' . $p_db->escape_string($p_session_id) . '"';
        }

        if ($p_username) {
            $l_condition .= ' AND ( BINARY LOWER(isys_cats_person_list__title) = LOWER(\'' . $p_db->escape_string($p_username) . '\'))';
        }

        if ($p_db->is_field_existent('isys_cats_person_list', 'isys_cats_person_list__disabled_login')) {
            $l_condition .= ' AND isys_cats_person_list__disabled_login = 0';
        }

        $query = 'SELECT isys_obj__id, isys_obj__title, isys_cats_person_list.*, isys_catg_mail_addresses_list__title AS isys_cats_person_list__mail_address, isys_user_session.*
            FROM isys_cats_person_list
            INNER JOIN isys_obj ON isys_cats_person_list__isys_obj__id = isys_obj__id
            LEFT JOIN isys_catg_mail_addresses_list ON isys_catg_mail_addresses_list__isys_obj__id = isys_obj__id AND isys_catg_mail_addresses_list__primary = 1
            LEFT JOIN isys_user_session ON isys_user_session__isys_obj__id = isys_obj__id
            WHERE isys_obj__status = ' . ((int)C__RECORD_STATUS__NORMAL) .
            $l_condition . ';';

        return $p_db->query($query);
    }

    /**
     * Private wakeup method to prevent multiple instances via unserialization.
     * @see  ID-8568  Needs to be 'public'
     */
    public function __wakeup()
    {
        ;
    }

    /**
     * Private clone method to prevent multiple instances.
     */
    private function __clone()
    {
        ;
    }

    /**
     * Get the type of database
     *
     * @return string
     */
    private function getSystemDbType()
    {
        global $g_db_system;

        return $g_db_system["type"] ? $g_db_system["type"] : 'mysql';
    }

    /**
     * @return AbstractUser|null
     */
    public function getUserEntity(): ?AbstractUser
    {
        return $this->userEntity;
    }

    /**
     * @param AbstractUser|null $userEntity
     *
     * @return isys_component_session
     */
    public function setUserEntity(?AbstractUser $userEntity): isys_component_session
    {
        $this->userEntity = $userEntity;
        return $this;
    }

    /**
     * @return string
     * @see ID-9144 Implement proper logic to find correct client IP
     */
    private function getClientIp(): string
    {
        $ipValue = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? $_SERVER['HTTP_CLIENT_IP'] ?? '';

        // In case of 'HTTP_X_FORWARDED_FOR' we can receive multiple IPs - so we clean those values.
        $ips = array_values(array_filter(array_map('trim', explode(',', $ipValue))));

        // Fetch the very first entry.
        return reset($ips);
    }
}
