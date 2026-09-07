[{isys_group name="login"}]
<input type="hidden" name="HTTP_GOTO" value="[{$HTTP_GOTO}]" />

<img src="[{$dir_images}]login-testimonial.svg" class="login-testimonial" />

<div class="login-container display-flex">
    <div class="login-box">
        <div class="display-flex align-items-center">
            <img class="fr" src="[{$dir_images}]logo.png" alt="i-doit" height="24" />

            <span class="ml10 mt10">
                Version: [{isys_application::instance()->info->get('version')}] [{isys_application::instance()->info->get('step')}] [{isys_application::instance()->info->get('type')}]
            </span>

            [{if !$isCloud}]
            <a class="btn btn-secondary ml-auto" href="[{isys_application::instance()->www_path}]admin">
                <img class="fr" src="[{$dir_images}]axialis/basic/gear.svg" alt="" /><span>Admin-Center</span>
            </a>
            [{/if}]
        </div>

        [{if !$logFileWritable}]
        <div class="bg-red p20" id="login_error">
            <strong id="login_error_header" class="mb20">i-doit filesystem error</strong>
            <p id="login_error_message">
                Your i-doit log file or directory (<code>{i-doit}/log/</code>) is not writeable.
                This is neccessary for logging the access to i-doit.
                Please make sure this file is writeable and try again.
            </p>
        </div>
        [{/if}]

        <div class="bg-red p20" id="login_error" [{if empty($login_error)}]style="display:none;"[{/if}]>
            <strong id="login_error_header" class="mb20">[{$login_header|default:"i-doit system error"}]:</strong>
            <p id="login_error_message">[{$login_error}]</p>
        </div>

        <div id="ajax-loading" class="display-flex align-items-center justify-content-center" style="display:none">
            <img src="[{$dir_images}]axialis/user-interface/loading.svg" class="animation-rotate mr10" /><strong>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</strong>
        </div>

        [{if !empty($welcomeMessage)}]
            <div class="welcome-message bg-light-turquois" style="margin-top: 32px">
                [{$welcomeMessage}]
            </div>
        [{/if}]

        <div style="margin-top: 32px">
            <label class="display-block text-bold mb5" for="login_username">[{isys type="lang" ident="Username"}]</label>
            <input class="input input-block" type="text" [{$username_disable}] name="login_username" id="login_username" value="[{$login_username}]" />
        </div>

        <div style="margin-top: 24px">
            <label class="display-block text-bold mb5" for="login_password">[{isys type="lang" ident="Password"}]</label>
            <input class="input input-block" type="password" [{$password_disable}] name="login_password" id="login_password" value="" autocomplete="off" />
        </div>

        <div id="login_mandator_selection" style="display:none; margin-top: 24px">
            <!-- To be filled by JS -->
        </div>

        <div class="display-flex align-items-center" style="margin-top: 32px; margin-bottom: 42px">
            [{if $resetPasswordActive}]
            <a href="[{isys_application::instance()->www_path}]forgot-password">Forgot password?</a>
            [{/if}]
            <button name="login_submit" type="button" id="login_submit" class="ml-auto btn">
                <img src="[{$dir_images}]axialis/basic/login.svg"><span>Login</span>
            </button>
        </div>

        [{if $ssoActive}]
        <div class="auth-container">
            <div class="auth-separator"><span>Or</span></div>
            <a href="[{$ssoLoginLink}]" class="btn btn-block">
                <img src="[{$dir_images}]axialis/security/key.svg"><span>SSO Login</span>
            </a>
        </div>
        [{/if}]

        <div class="display-flex justify-content-evenly align-items-center">
            <a href="https://www.i-doit.com/en/imprint/" class="text-underline" target="_blank">Imprint / Legal</a>
            <a href="https://www.i-doit.com/en/privacy-policy/" class="text-underline" target="_blank">Privacy Policy</a>
            <a href="https://www.i-doit.com" class="text-underline" target="_blank">i-doit.com - &copy; synetics gmbh</a>
        </div>
    </div>

    <div class="login-testimonial">
        <h1>[{$loginTestimonial}]</h1>
        <h2>Turn a world of data into<br />a galaxy of knowledge.</h2>
    </div>
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        var $form           = $('isys_form'),
            $login_username = $('login_username'),
            $login_password = $('login_password'),
            $login_submit   = $('login_submit'),
            $login_error    = $('login_error'),
            $ajax_loading   = $('ajax-loading');

        // Do the initial setup
        $form.writeAttribute('action', '[{$HTTP_GOTO}]');
        $login_username.focus();

        $login_username.on('keypress', function (ev) {
            if (ev.keyCode == Event.KEY_RETURN) {
                ev.preventDefault();
                load_mandants('isys_form', 'login_mandator_selection');
            }
        });

        $login_password.on('keypress', function (ev) {
            if (ev.keyCode == Event.KEY_RETURN) {
                ev.preventDefault();
                load_mandants('isys_form', 'login_mandator_selection');
            }
        });

        $login_submit.on('click', function () {
            load_mandants('isys_form', 'login_mandator_selection');
        });

        function load_mandants(p_form, p_target) {
            $login_username.enable();
            $login_password.enable();

            var formdata = $(p_form).serialize(true);

            $login_submit.hide();

            if ($(p_form)) {
                $ajax_loading.show();
                $login_error.hide();

                new Ajax.Updater(p_target, document.location.href, {
                    method:       'post',
                    asynchronous: true,
                    onSuccess:    function (xhr) {
                        $login_username.disable();
                        $login_password.disable();
                        $ajax_loading.hide();
                        $(p_target).appear();
                    },
                    onFailure:    function () {
                        $login_error.show();
                        $('login_error_message').update('Error.');
                        $ajax_loading.hide();
                    },
                    parameters:   formdata,
                    evalScripts:  true
                });
            } else {
                alert('Form:' + p_form + ' does not exist.\n - Check your load_mandants() parameters.');
            }
        }
    })();
</script>
[{/isys_group}]
