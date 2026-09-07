<script type="text/javascript">
    'use strict';

    function initLicenseTokenChange() {
        [{if isys_auth_synetics_admin::instance()->canAccessAdminCenter()}]
        const $licenseTokenButton = $('license-token-button');
        const $licenseTokenField = $('license-token-field');
        const $licenseTokenLoadingIndicator = $('license-token-loading-indicator');
        $licenseTokenLoadingIndicator.toggle();

        $licenseTokenField.on('keydown', () => $licenseTokenField.removeClassName('input-error'));

        $licenseTokenButton.on('click', function () {
            $licenseTokenField.removeClassName('input-error');
            $licenseTokenLoadingIndicator.toggle();
            $licenseTokenButton.setAttribute('disabled', 'disabled');
            if (!$licenseTokenField.value) {
                idoit.Notify.error('[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_EMPTY"}]')
                $licenseTokenField.toggleClassName('input-error');
                $licenseTokenLoadingIndicator.toggle();
                $licenseTokenButton.removeAttribute('disabled');
                return;
            }

            new Ajax.Request('[{$wwwPath}]/synetics_admin/api/licenseToken', {
                method:     'POST',
                parameters: {
                    licenseToken: $licenseTokenField.value
                },
                onComplete: function (xhr) {
                    $licenseTokenLoadingIndicator.toggle();
                    $licenseTokenButton.removeAttribute('disabled');
                    const json = xhr.responseJSON;

                    if (json.success) {
                        window.location.reload();
                    } else {
                        let message = json.error || '[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_GENERIC"}]'
                        if (json.type === 'permission') {
                            message = '[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR_NO_PERMISSION"}]';
                        }

                        idoit.Notify.error(message);
                        $licenseTokenField.toggleClassName('input-error');
                    }
                }
            });
        });
        [{/if}]
    }
</script>
<div class="w100 display-flex justify-content-center">
    [{if $error === 'system-is-offline'}]
    <div class="box-blue p20 display-flex mw50">
        <div class="pr10">
            <img src="[{$dir_images}]axialis/basic/button-info.svg" />
        </div>
        <div>
            <h1 class="mb10">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__SYSTEM_OFFLINE_HEADER"}]</h1>
            <p>[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__SYSTEM_OFFLINE_MESSAGE" p_bHtmlEncode=false}]</p>
            <a href="[{$smarty.const.C__SFM_STANDALONE_URL}]" target="_blank" class="btn mt20">
                <img src="[{isys_application::instance()->www_path}]src/classes/modules/synetics_admin/templates/img/subscription-and-add-ons.svg" alt="" />
                <span>[{isys type="lang" ident="LC__SYNETICS_ADMIN"}]</span>
            </a>
        </div>
    </div>
    [{elseif $error === 'permission-missing'}]
    <div class="box-red p20 display-flex  mw50">
        <div class="pr10">
            <img src="[{$dir_images}]axialis/basic/symbol-forbidden.svg" />
        </div>
        <div>
            <h1 class="mb10">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__PERMISSION_HEADER"}]</h1>
            <p>[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__PERMISSION_MESSAGE" p_bHtmlEncode=false}]</p>
        </div>
    </div>
    [{elseif $error === 'portal-auth-failed'}]
        <div class="box-red p20 display-flex  mw50">
            <div class="pr10">
                <img src="[{$dir_images}]axialis/basic/symbol-forbidden.svg" />
            </div>
            <div>
                <h1 class="mb10">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__PORTAL_AUTH_FAILED_HEADER"}]</h1>
                <p>
                    [{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__PORTAL_AUTH_FAILED_MESSAGE" p_bHtmlEncode=false}]
                </p>
                <div class="mt20 mb20">
                    <label class="display-block mb5" for="license-token-field">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR"}]</label>
                    <input type="text" value="" class="input input-size-medium" id="license-token-field"
                           placeholder="[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_FIELD_PLACEHOLDER"}]" />
                    <button class="btn" type="button" id="license-token-button">
                        <img src="[{$dir_images}]axialis/user-interface/loading.svg" class="animation-rotate mr5" alt="Loading" id="license-token-loading-indicator" />
                        [{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_SAVE"}]
                    </button>
                </div>

                <p>
                    <a href="[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE_REQUEST_A_TOKEN_URL"}]" target="_blank">
                        [{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE_REQUEST_A_TOKEN"}]
                    </a>
                </p>

                <script type="text/javascript">
                    initLicenseTokenChange();
                </script>
            </div>
        </div>
        [{elseif $error === 'portal-connection-failed'}]
        <div class="box-red p20 display-flex mw50">
            <div class="pr10">
                <img src="[{$dir_images}]axialis/basic/symbol-forbidden.svg" />
            </div>
            <div>
                <h1 class="mb10">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__PORTAL_CONNECTION_FAILED_HEADER"}]</h1>
                <p>
                    [{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__PORTAL_CONNECTION_FAILED_MESSAGE" values=[$errorParamA, $errorParamB]}]
                </p>
            </div>
        </div>
    [{elseif $error === 'license-token-missing'}]
    <div class="box-blue p20 display-flex">
        <div class="pr10">
            <img src="[{$dir_images}]axialis/basic/button-info.svg" />
        </div>
        <div>
            <h1 class="mb10">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_HEADER"}]</h1>
            <p>[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE" p_bHtmlEncode=false}]</p>

            <div class="mt20 mb20">
                <label class="display-block mb5" for="license-token-field">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR"}]</label>
                <input type="text" value="" class="input input-size-medium" id="license-token-field"
                       placeholder="[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_FIELD_PLACEHOLDER"}]" />
                <button class="btn" type="button" id="license-token-button">
                    <img src="[{$dir_images}]axialis/user-interface/loading.svg" class="animation-rotate mr5" alt="Loading" id="license-token-loading-indicator" />
                    [{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_SAVE"}]
                </button>
            </div>

            <p>
                <a href="[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE_REQUEST_A_TOKEN_URL"}]" target="_blank">
                    [{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE_REQUEST_A_TOKEN"}]
                </a>
            </p>

            <script type="text/javascript">
                initLicenseTokenChange();
            </script>
        </div>
    </div>
    [{else}]
    <div class="box-red p20 display-flex mw50">
        <div class="pr10">
            <img src="[{$dir_images}]axialis/basic/button-info.svg" />
        </div>
        <div>
            <h1 class="mb10">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_HEADER"}]</h1>
            <p>[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE" p_bHtmlEncode=false}]</p>

            <div class="mt20 mb20">
                <label class="display-block mb5" for="license-token-field">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_ERROR"}]</label>
                <input type="text" value="" class="input input-size-medium" id="license-token-field"
                       placeholder="[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_FIELD_PLACEHOLDER"}]" />
                <button class="btn" type="button" id="license-token-button">
                    <img src="[{$dir_images}]axialis/user-interface/loading.svg" class="animation-rotate mr5" alt="Loading" id="license-token-loading-indicator" />
                    [{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_SAVE"}]
                </button>
            </div>

            <p>
                <a href="[{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE_REQUEST_A_TOKEN_URL"}]" target="_blank">
                    [{isys type="lang" ident="LC__SYNETICS_ADMIN__ERROR__LICENSE_TOKEN_MISSING_MESSAGE_REQUEST_A_TOKEN"}]
                </a>
            </p>

            <script type="text/javascript">
                initLicenseTokenChange();
            </script>
        </div>
    </div>
    [{/if}]
</div>
