<?php
/**
 * i-doit
 * For "Single Sign On" (SSO). This file is included by hypergate.inc.php - if the user is not logged in, yet, and SSO is activated
 *
 * Checked via:
 *  isys_tenantsettings::get('session.sso.active', false) &&
 *  isys_tenantsettings::get('session.sso.mandator-id', '1') > 0 && (isset($_SERVER['REMOTE_USER']) && $_SERVER['REMOTE_USER'] != '')
 *
 * @package     i-doit
 * @subpackage  General
 * @copyright   synetics GmbH
 * @license     http://www.gnu.org/licenses/agpl-3.0.html GNU AGPLv3
 */

use idoit\Component\Login\SsoUser;

if (isset($_GET['logout'])) {
    // Display SSO-Message after logout and prevent endless redirections.
    $l_error = "Re-Login with user: <a href='?'>" . ($_SERVER['REMOTE_USER'] ? $_SERVER['REMOTE_USER'] : $_SERVER['REDIRECT_REMOTE_USER']) . "</a>";
} else {
    $l_session = isys_application::instance()->container->get('session');

    $l_sso_user = ($_SERVER['REMOTE_USER'] ? $_SERVER['REMOTE_USER'] : $_SERVER['REDIRECT_REMOTE_USER']);

    if (strstr($l_sso_user, '\\')) {
        $l_sso_user = substr($l_sso_user, strpos($l_sso_user, '\\') + 1);
    } elseif (strstr($l_sso_user, '@') && !isys_settings::get('session.sso.use-domain-part', '1')) {
        // @see ID-9433 Consider the domain part by default!
        $l_sso_user = substr($l_sso_user, 0, strpos($l_sso_user, '@'));
    }

    if ($l_sso_user) {
        $mandatorIds = (array)explode(',', $_GET[C__CMDB__GET__TENANT] ?? isys_settings::get('session.sso.mandator-id'));

        foreach ($mandatorIds as $mandatorId) {
            if (!$l_session->connect_mandator((int)$mandatorId)) {
                continue;
            }

            if (!is_object(isys_application::instance()->container->get('database'))) {
                continue;
            }

            $l_userarray = isys_component_dao_user::instance(isys_application::instance()->container->get('database'))
                ->get_user(null, $l_sso_user)
                ->get_row();

            if (!$l_userarray) {
                continue;
            }

            $l_session->delete_current_session();
            $l_session->start_dbsession();
            $l_session->setUserEntity(new SsoUser());

            if (!$l_session->login(isys_application::instance()->container->get('database'), $l_sso_user, null, true, true, true)) {
                continue;
            }

            // Check and populate current license.
            if (class_exists('isys_module_licence')) {
                // todo licensing 2.0
                $l_licence = new isys_module_licence();
                $l_licence->verify();

                if ($l_licence->isThisTheEnd() && !$l_licence->isTrial()) {
                    $adminCenterUrl = isys_application::instance()->www_path . 'admin';

                    echo '<div class="box-red p5">' .
                        'Your i-doit license is missing for over 30 days.<br />' .
                        'Please install a valid license in the i-doit <a href="' . $adminCenterUrl . '">admin center</a>.' .
                        '</div>';

                    $l_session->logout();
                    die;
                }
            }

            // @See ID-8670 just call this method to set the available tenants to the session
            $l_session->fetch_mandators($l_sso_user, '');

            // @see  ID-5008  Append the GET parameter when redirecting to keep the "deeplink" intact.
            header('Location: index.php' . isys_helper_link::create_url($_GET));
            die;
        }

        $l_error = "Single-Sign-On user could not be found in i-doit! Login manually.";
    }

    // Clear all sessions, because this login failed!
    $l_session->logout();
}
