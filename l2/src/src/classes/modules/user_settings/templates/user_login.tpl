<table class="contentTable ml20">
    <tr>
        <td class="key"></td>
        <td class="value pl20">
            <div class="display-flex align-items-center">
                <img src="[{$dir_images}]axialis/basic/button-info.svg" class="mr5" alt="" /><span class="text-blue">[{$title}]</span>
            </div>
        </td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CONTACT__PERSON_PASSWORD" name="C__CONTACT__PERSON_PASSWORD"}]</td>
        <td class="value">[{isys type="f_password" name="C__CONTACT__PERSON_PASSWORD"}]</td>
    </tr>
    [{if isys_glob_is_edit_mode()}]
    <tr>
        <td class="key"></td>
        <td class="value">[{isys type="f_popup" p_strPopupType="change_password" name="C__CONTACT__CHANGE_PASSWORD_POPUP"}]</td>
    </tr>
    [{/if}]
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CONTACT__PERSON_PASSWORD_RESET_EMAIL" name="C__CONTACT__PERSON_PASSWORD_RESET_EMAIL"}]</td>
        <td class="value">[{isys type="f_text" name="C__CONTACT__PERSON_PASSWORD_RESET_EMAIL"}]</td>
    </tr>
</table>
[{if $tfa_exists}]
<div class="p10 border-top border-bottom bg-neutral-200 display-flex align-items-center mt20">
    <h2>[{isys type="lang" ident="LC__USER_SETTINGS__TFA"}]</h2>
    <a class="ml20 btn" target="_blank" href="[{isys type="lang" ident="LC__USER_SETTINGS__TFA__KB_LINK"}]">
        <img src="[{$dir_images}]axialis/basic/link.svg" alt=""><span>Knowledge Base</span>
    </a>
</div>

<div class="p20">
    [{if $tfa_secret}]
    <div class="box-green p10 mb20 display-flex align-items-center">
        <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="" class="mr10" />
        <p>[{isys type="lang" ident="LC__USER_SETTINGS__TFA__CURRENTLY_ACTIVE"}]</p>
    </div>
    <div class="display-flex flex-container-row align-items-center" style="min-width: 100%">
        <img src="[{$tfa_qrcode}]" width="150" height="150" class="display-block" alt="qr-code" />
        <div class="display-flex align-items-center">
            <strong class="pl10 pr20">[{isys type="lang" ident="LC__USER_SETTINGS__TFA__ACTION_OR"}]</strong>

            <div>
                <input type="password" value="[{$tfa_secret}]" class="input" style="width:200px;" readonly="readonly" id="tfa_secret_input" />
                <button id="tfa_copy_secret" type="button" class="btn">[{isys type="lang" ident="LC__USER_SETTINGS__TFA__COPY_SECRET"}]</button>
                <label class="pt10 display-block">
                    <input type="checkbox" id="tfa_reveal_secret" class="mr5" /><span>[{isys type="lang" ident="LC__USER_SETTINGS__TFA__REVEAL_SECRET"}]</span>
                </label>
            </div>
        </div>
    </div>
    <p class="mt10 mb10">
        [{isys type="lang" ident="LC__USER_SETTINGS__TFA__HOWTO"}]
    </p>
    [{else}]
    <div class="box-blue p10 mb20 mt10 display-flex align-items-center">
        <img src="[{$dir_images}]axialis/basic/button-info.svg" alt="" class="mr10" />
        <p>[{isys type="lang" ident="LC__USER_SETTINGS__TFA__CURRENTLY_INACTIVE"}]</p>
    </div>
    [{/if}]
    [{if isys_glob_is_edit_mode()}]
    <button type="button" class="btn mt20" id="tfa_button">
        <img src="[{$dir_images}]axialis/basic/gear.svg" alt="" />
        <span>
        [{if $tfa_secret}]
        [{isys type="lang" ident="LC__USER_SETTINGS__TFA__DEACTIVATE"}]
        [{else}]
        [{isys type="lang" ident="LC__USER_SETTINGS__TFA__ACTIVATE"}]
        [{/if}]
        </span>
    </button>
    [{/if}]
</div>
[{/if}]
<script type="text/javascript">
    (function () {
        'use strict';

        const tfaSecret = '[{$tfa_secret}]';
        const $copySecretButton = $('tfa_copy_secret');
        const $revealSecretButton = $('tfa_reveal_secret');
        const $secretInput = $('tfa_secret_input');
        const $actionButton = $('tfa_button');

        if (tfaSecret) {
            if (!navigator.clipboard) {
                $copySecretButton.hide();
            }

            $copySecretButton.on('click', function () {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(tfaSecret);
                    idoit.Notify.success('[{isys type="lang" ident="LC__USER_SETTINGS__TFA__SECRET_COPIED"}]', { life: 5 });
                    return;
                }

                idoit.Notify.error('[{isys type="lang" ident="LC__USER_SETTINGS__TFA__SECRET_COPY_ERROR"}]');
            });

            $revealSecretButton.on('change', function () {
                if ($secretInput.readAttribute('type') !== 'text') {
                    $secretInput.writeAttribute('type', 'text');
                } else {
                    $secretInput.writeAttribute('type', 'password');
                }
            });
        }

        if ($actionButton) {
            $actionButton.on('click', function () {
                if (tfaSecret) {
                    new Ajax.Request(idoit.Router.getRoute('tfa.deactivation-modal'), {
                        method:     'get',
                        onComplete: function (xhr) {
                            Modal.open(xhr.responseText, { maxWidth: 500, maxHeight: 300 });
                        }
                    });
                } else {
                    new Ajax.Request(idoit.Router.getRoute('tfa.activation-modal'), {
                        method:     'get',
                        onComplete: function (xhr) {
                            Modal.open(xhr.responseText, { maxWidth: 500, maxHeight: 475 });
                        }
                    });
                }
            });
        }

        /* [{if $passwordValidationError === true}] */
        $$('input[type="password"]')
            .invoke('addClassName', 'input-error')
            .invoke('on', 'keypress', function () {
                this.removeClassName('input-error');
            });
        /* [{/if}] */

        /* [{if $emailValidationError === true}] */
        $('C__CONTACT__PERSON_PASSWORD_RESET_EMAIL')
            .addClassName('input-error')
            .on('keypress', function () {
                this.removeClassName('input-error');
            });
        /* [{/if}] */
    })();
</script>
