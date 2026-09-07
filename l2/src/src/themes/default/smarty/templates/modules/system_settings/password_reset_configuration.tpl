<!-- ID-11319 Add specific section with custom logic for password reset -->
<div class="collapse-container border-bottom">
    <div class="toggle-header bg-neutral-200 p5 display-flex align-items-center">
        <button type="button" class="btn btn-secondary mr5" data-tooltip="1" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]">
            <img src="[{$dir_images}]axialis/user-interface/angle-right-small.svg" alt="">
        </button>
        <h2>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__PASSWORD_RESET"}]</h2>
    </div>

    <div class="content-container border-top hide">

        [{if !$smtpConfigured}]
        <div class="m20 box-yellow display-flex">
            <div class="p10"><img src="[{$dir_images}]axialis/basic/warning.svg" alt="" /></div>
            <div class="pt10 pr10 pb10">
                <strong>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__PASSWORD_RESET__NO_SMTP_CONFIGURED"}]</strong>
                <p class="mt10 mb20">[{isys type="lang" ident="LC__SETTINGS__SYSTEM__PASSWORD_RESET__NO_SMTP_CONFIGURED_TEXT" p_bHtmlEncode=false}]</p>
                <a href="[{$smtpConfigurationUrl}]" target="_blank" class="btn">
                    <img src="[{$dir_images}]axialis/web-email/external-link.svg" alt="" /><span>[{isys type="lang" ident="LC__SETTINGS__SYSTEM__PASSWORD_RESET__GO_TO_SMTP_CONFIGURATION" p_bHtmlEncode=false}]</span>
                </a>
                <a href="[{isys type="lang" ident="LC__SETTINGS__SYSTEM__PASSWORD_RESET__KB_LINK"}]" target="_blank" class="ml20 btn btn-secondary">
                    <span>[{isys type="lang" ident="LC__UNIVERSAL__LINK_TO_KB"}]</span>
                </a>
            </div>
        </div>
        [{/if}]

        <table class="contentTable p0 mt10 mb10">
            <colgroup>
                <col width="205">
                <col width="350">
                <col width="*">
            </colgroup>
            <tr>
                <td class="key vat">
                    <label for="system.passwort-reset.enabled">[{isys type="lang" ident="LC__SETTINGS__SYSTEM__PASSWORD_RESET__ENABLED"}]</label>
                </td>
                <td class="[{if $isEditing}]vat[{/if}] value pl20">
                    [{if $isEditing}]
                    <select name="settings[Tenant-wide][system.passwort-reset.enabled]" id="system.passwort-reset.enabled" class="input input-medium" [{if !$smtpConfigured}]disabled="disabled"[{/if}]>
                        <option value="1" [{if isset($settings['system.passwort-reset.enabled']) && 1 == $settings['system.passwort-reset.enabled']}]selected="selected"[{/if}]>[{isys type="lang" ident="LC__UNIVERSAL__ENABLED"}]</option>
                        <option value="0" [{if !isset($settings['system.passwort-reset.enabled']) || (isset($settings['system.passwort-reset.enabled']) && 1 != $settings['system.passwort-reset.enabled'])}]selected="selected"[{/if}]>[{isys type="lang" ident="LC__UNIVERSAL__DISABLED"}]</option>
                    </select>
                    [{else}]

                    [{if isset($settings['system.passwort-reset.enabled']) && 1 == $settings['system.passwort-reset.enabled']}]
                        [{isys type="lang" ident="LC__UNIVERSAL__ENABLED"}]
                    [{/if}]

                    [{if !isset($settings['system.passwort-reset.enabled']) || (isset($settings['system.passwort-reset.enabled']) && 1 != $settings['system.passwort-reset.enabled'])}]
                        [{isys type="lang" ident="LC__UNIVERSAL__DISABLED"}]
                    [{/if}]

                    [{/if}]
                </td>
                <td class="pl20 text-blue">
                    <div style="width: 50%;">
                        <img src="[{$dir_images}]axialis/basic/button-info.svg" class="vam" alt="*"/> [{isys type="lang" ident="LC__SETTINGS__SYSTEM__PASSWORD_RESET__ENABLED_DESCRIPTION"}]
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>
