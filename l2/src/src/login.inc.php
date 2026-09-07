<?php
/**
 * i-doit
 *
 * Login
 *
 * @package    i-doit
 * @subpackage General
 * @copyright  synetics GmbH
 * @license    http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Module\License\LicenseServiceFactory;

global $g_template;

try {
    $session = isys_application::instance()->container->get('session');
    $session->setUserEntity(new idoit\Component\Login\User());
    /**
     * Initialize CMDB Module, because it is needed for retrieving login users
     */
    include_once(__DIR__ . '/classes/modules/cmdb/init.php');

    // Load tenants template, because this is an ajax request.
    if (isset($_POST['login_submit'])) {
        $g_template['start_page'] = 'content/mandants.tpl';
    }

    // Check if username and password was entered.
    if (empty($_POST['login_username'])) {
        $l_error = 'No username specified!<br />';
    } elseif (empty($_POST['login_password'])) {
        $l_error = 'No password specified!<br />';
    } elseif (isset($_POST['login_mandant_id']) && is_numeric($_POST['login_mandant_id'])) {
        // Instantiate isys_application::instance()->database.
        if ($session->connect_mandator($_POST['login_mandant_id'])) {
            // Insert Session Entry to database.
            if ($session->start_dbsession() != null) {
                $session->delete_expired_sessions();

                // Do the real login.
                $loginResult = $session->login(
                    isys_application::instance()->container->get('database'),
                    $_POST['login_username'],
                    $_POST['login_password'],
                    false // Write new userID to session
                );

                if ($loginResult) {
                    $session->renewSessionId();
                    unset($_GET["logout"]);

                    // Prepare module request, because a module dao is needed in ->checkLicense method and $g_modman->init

                    // Initialize module manager.
                    $moduleManager = isys_module_manager::instance()
                        ->init(isys_module_request::get_instance());

                    // Check if license check exists.
                    if (class_exists("isys_module_licence")) {
                        $l_licence = new isys_module_licence();
                        $l_licence->verify();

                        // @see ID-8917 Force to re-process the add-ons, once the license has been verified.
                        $moduleManager->processAddonLicenses();

                        if ($l_licence->isThisTheEnd() && !$l_licence->isTrial()) {
                            $adminCenterUrl = isys_application::instance()->www_path . 'admin';

                            isys_glob_display_error(
                                'Your i-doit license is missing for over 30 days.<br />' .
                                'Please install a valid license in the i-doit <a href="' . $adminCenterUrl . '">admin center</a>.'
                            );

                            $session->logout();
                            die;
                        }
                    }

                    // Delete temp tables.
                    try {
                        $l_dao_tables = new isys_component_dao_table(isys_application::instance()->container->get('database'));
                        $l_dao_tables->clean_temp_tables();
                    } catch (isys_exception_dao $l_e) {
                        ; // Ignore it...
                    }

                    // @see ID-10059 Redirect after login in order to start with a fresh session ID.
                    header('Location: ' . isys_helper_link::create_url($_GET));
                    die;
                } else {
                    $l_error = "Login attempt failed. Please try again.";
                }
            } else {
                $l_error = "Could not add session to database.";
            }
        } else {
            $l_error = "Could not connect to mandator database.";
        }
    } else {
        // PREPARE MANDATOR LIST FOR LOGIN
        // This block is executed after the initial login. User entered username password and we fetch the available mandantors for him now.
        $l_mandator_data = $session->fetch_mandators($_POST["login_username"], $_POST["login_password"]);

        $l_token = new \Symfony\Component\Security\Csrf\CsrfToken('i-doitCSRFTokenID', $_POST['_csrf_token']);

        if (isys_settings::get('system.security.csrf', false) && !(new \Symfony\Component\Security\Csrf\CsrfTokenManager())->isTokenValid($l_token)) {
            throw new ErrorException('CSRF-Token mismatch!');
        }

        if (is_countable($l_mandator_data) && count($l_mandator_data) > 0) {
            $l_mandants = [];
            $l_preferred_language = null;

            if (count($l_mandator_data) === 1) {
                isys_application::instance()->container->get('template')->assign('directlogin', true);
                $session->connect_mandator(key($l_mandator_data));
                if ($session->start_dbsession() != null) {
                    $session->delete_expired_sessions();
                }

                if ($session->login(isys_application::instance()->container->get('database'), $_POST['login_username'], $_POST['login_password'], false, false, true)) {
                    // @see  ID-6833  The license was not verified correctly.
                    if (class_exists("isys_module_licence")) {
                        $l_licence = new isys_module_licence();
                        $l_licence->verify();

                        global $g_license_token;
                        if ($g_license_token) {
                            $licenseService = LicenseServiceFactory::createDefaultLicenseService(isys_application::instance()->container->get('database_system'), $g_license_token);
                            $lastCheck = $licenseService->getLastSuccessfulLicenseServerCommuncation();
                            if ($lastCheck) {
                                // Use 'DateTime' instead of 'Carbon'.
                                $now = new DateTime();
                                $lastCheckDate = new DateTime($lastCheck['created']);

                                if ($now->diff($lastCheckDate)->days >= 3 && $now > $lastCheckDate) {
                                    $licenseService->refresh();
                                    $l_licence->verify();
                                }
                            }
                        }

                        if ($l_licence->isThisTheEnd() && !$l_licence->isTrial()) {
                            $adminCenterUrl = isys_application::instance()->www_path . 'admin';

                            echo '<div class="box-red p5">' .
                                'Your i-doit license is missing for over 30 days.<br />' .
                                'Please install a valid license in the i-doit <a href="' . $adminCenterUrl . '">admin center</a>.' .
                                '</div>';

                            $session->logout();
                            die;
                        }
                    }

                    echo '<script>window.location.reload();</script>';
                    die();
                }
            }

            foreach ($l_mandator_data as $l_mandator) {
                $l_mandants[$l_mandator['id']] = $l_mandator['title'];
                $l_user_id = $l_mandator['user_id'];
                if ($l_preferred_language === null) {
                    $l_preferred_language = $l_mandator['preferred_language'];
                }
            }
            if (isset($_GET[C__CMDB__GET__TENANT])) {
                $requiredTenantId = $_GET[C__CMDB__GET__TENANT];
            } else {
                $requiredTenantId = null;
            }

            // Show available mandators in SELECT and disable text fields.
            isys_application::instance()->container->get('template')->assign("mandant_options", $l_mandants)
                ->assign('requiredTenantId', $requiredTenantId)
                ->assign("languages", isys_application::instance()->container->get('language')
                    ->fetch_available_languages())
                ->assign('preferred_language', $l_preferred_language);
        }

        $l_session_errors = $session->get_errors();

        if (is_countable($l_session_errors) && count($l_session_errors) > 0 && is_countable($l_mandator_data) && count($l_mandator_data) <= 0) {
            // Removed: Check for rights -> isys_rs_system
        } else {
            if (is_null($l_mandator_data)) {
                $l_error = "No mandators found in system database!";
            } elseif (is_countable($l_mandator_data) && count($l_mandator_data) == 0) {
                $l_error = "Invalid username or password!";
            }
        }

        if (!isset($l_error)) {
            // If no error occurred - load clients.
            echo isys_application::instance()->container->get('template')->fetch($g_template["start_page"], null, null, null, true);

            die;
        }
    }
} catch (UnexpectedValueException $e) {
    $l_error = 'Your i-doit log file or directory (<code>{i-doit}/log/</code>) is not writeable.';

    isys_application::instance()->container->get('logger')->error($e->getMessage());
} catch (ErrorException $e) {
    if (strlen($e->getMessage()) > 100) {
        $l_error = 'Login failed: ' . isys_glob_cut_string($e->getMessage(), 100) . '...' . substr($e->getMessage(), -100);
    } else {
        $l_error = 'Login failed: ' . $e->getMessage();
    }

    isys_application::instance()->container->get('logger')->error($e->getMessage());
}
