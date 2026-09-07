<div id="system">
    <!-- ID-11319 Include specific section for password reset -->
    [{if $showPasswordReset}]
    [{include file="./password_reset_configuration.tpl"}]
    [{/if}]

    [{foreach $definition as $headline => $definition_content}]
    <div class="collapse-container border-bottom">
        <div class="toggle-header bg-neutral-200 p5 display-flex align-items-center">
            <button type="button" class="btn btn-secondary mr5" data-tooltip="1" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]">
                <img src="[{$dir_images}]axialis/user-interface/angle-right-small.svg" alt="">
            </button>
            <h2>[{isys type="lang" ident=$headline}]</h2>
        </div>

        <div class="content-container border-top hide">
            <table class="contentTable p0 mt10 mb10">
                <colgroup>
                    <col width="205">
                    <col width="350">
                    <col width="*">
                </colgroup>
                [{foreach $definition_content as $key => $setting}]
                [{if !isset($setting.hidden)}]
                <tr>
                    <td class="key vat">
                        <label for="[{$key}]">[{isys type="lang" ident=$setting.title}]</label>
                    </td>
                    <td class="[{if $isEditing}]vat[{/if}] value pl20">
                        [{if $setting.type == 'select'}]

                            [{isys
                                type="f_dialog"
                                name="settings[`$systemWideKey`][`$key`]"
                                id=$key
                                p_bDbFieldNN=true
                                p_bSort=false
                                p_arData=$setting.options
                                p_strSelectedID=$settings.$key|default:$settings.default
                                p_strClass="input-medium"
                                p_bInfoIconSpacer=0}]

                        [{elseif $setting.type == 'textarea'}]

                            [{isys
                                type="f_textarea"
                                name="settings[`$systemWideKey`][`$key`]"
                                id=$key
                                p_strValue=$settings.$key|default:$setting.default
                                p_strPlaceholder=$setting.placeholder
                                p_strClass="input-medium"
                                p_bInfoIconSpacer=0}]

                        [{elseif $setting.type == 'password'}]

                            [{isys
                                type="f_password"
                                name="settings[`$systemWideKey`][`$key`]"
                                id=$key
                                p_strValue=$settings.$key|default:$setting.default
                                p_strPlaceholder=$setting.placeholder
                                p_strClass="input-medium"
                                p_bInfoIconSpacer=0}]

                        [{elseif $setting.type == 'color'}]

                            [{isys
                                type="f_colorpicker"
                                name="settings[`$systemWideKey`][`$key`]"
                                id=$key
                                p_strValue=$settings.$key|default:$setting.default
                                placeholder=$setting.placeholder
                                containerClass=""
                                size="medium"}]

                        [{else}]

                            [{isys
                                type="f_text"
                                name="settings[`$systemWideKey`][`$key`]"
                                id=$key
                                p_strValue=$settings.$key|default:$setting.default
                                p_strPlaceholder=$setting.placeholder
                                p_strClass="input-medium"
                                p_validation_rule=$setting.validationRule|default:$setting.type
                                p_bInfoIconSpacer=0}]

                        [{/if}]
                    </td>
                    <td class="pl20 text-blue">
                        [{if $setting.description}]
                        <div style="width: 50%;">
                            <img src="[{$dir_images}]axialis/basic/button-info.svg" class="vam" alt="*"/> [{isys type="lang" p_bHtmlEncode=false ident=$setting.description}]
                        </div>
                        [{/if}]
                    </td>
                </tr>
                [{/if}]
                [{/foreach}]
            </table>
        </div>
    </div>
    [{/foreach}]
</div>

<script type="text/javascript">
    (function () {
        'use strict';


        const $systemOverview = $('system');
        const $expandAllButton = $('navbar_item_expand-all');
        const $collapseAllButton = $('navbar_item_collapse-all');

        $systemOverview.on('click', '.toggle-header .btn-secondary', function (ev) {
            var $button = ev.findElement('button'),
                $container = $button.up('.collapse-container').down('.content-container').toggleClassName('hide');

            if ($container.hasClassName('hide')) {
                $button.down('img').writeAttribute('src', window.dir_images + 'axialis/user-interface/angle-right-small.svg');
            } else {
                $button.down('img').writeAttribute('src', window.dir_images + 'axialis/user-interface/angle-down-small.svg');
            }
        });

        $systemOverview.down('.toggle-header .btn-secondary').simulate('click');

        // @see ID-11236 Allow user to expand / collapse all sections.
        $expandAllButton.on('click', () => {
            // Only select the currently hidden containers
            const $hiddenDefinitions = $systemOverview.select('.content-container.hide');

            for (let i in $hiddenDefinitions) {
                if (!$hiddenDefinitions.hasOwnProperty(i)) {
                    continue;
                }

                $hiddenDefinitions[i].up('div').down('.toggle-header button').simulate('click');
            }
        });

        $collapseAllButton.on('click', () => {
            // Only select the containers that are currently shown.
            const $hiddenDefinitions = $systemOverview.select('.content-container:not(.hide)');

            for (let i in $hiddenDefinitions) {
                if (!$hiddenDefinitions.hasOwnProperty(i)) {
                    continue;
                }

                $hiddenDefinitions[i].up('div').down('.toggle-header button').simulate('click');
            }
        });

        $('contentWrapper').on('change', '[data-validation-rule]', function (ev) {
            var $element = ev.findElement('input'),
                value    = $element.getValue(),
                newValue = value,
                rule     = $element.readAttribute('data-validation-rule');

            switch (rule) {
                case 'comma-separated-ids':
                    // @see ID-10463 Validate specific value.
                    newValue = value.replace(/[^\d,]/g, '').split(',').filter((d) => d).join(',');
                    break;

                case 'int':
                    // Remove everything that is not a digit.
                    newValue = parseInt(value.replace(/\D/g, ''));

                    if (isNaN(newValue)) {
                        newValue = 0;
                    }

                    break;

                case 'color':
                    // Remove everything that is not a hex-character.
                    newValue = '#' + value.replace(/[^a-f0-9]/gi, '').substr(0, 6);

                    break;

                case 'float':
                    // First replace all commas with dots. Then remove everything that is not a digit or a dot. Then remove all dots from the beginning and end
                    newValue = parseFloat(value.replace(/,/g, '.').replace(/[^\d.]/g, '').replace(/(^\.+|\.+$)/, ''));

                    if (isNaN(newValue)) {
                        newValue = 0;
                    }

                    break;
            }

            value = value.toString();
            newValue = newValue.toString();

            if (value !== newValue) {
                idoit.Notify.info('[{isys type="lang" ident="LC__CMDB__SANITATION__CHANGED_VALUE" p_bHtmlEncode=false}]'.replace('%s', value.encodeHTML())
                    .replace('%s', newValue.encodeHTML()), {life:10});
            }

            $element.setValue(newValue);
        });
    })();
</script>
