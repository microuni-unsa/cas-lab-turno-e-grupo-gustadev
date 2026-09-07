<div class="fr m10">
    <a href="?req=settings&expert=1">Expert settings</a>
</div>

<div class="gradient content-header">
	<img src="../images/icons/silk/wrench.png" class="vam mr5" /><span class="bold headline vam">System settings</span>
</div>

<form id="settings">
[{foreach $definition as $headline => $systemSettings}]
    <h3 style="border-top:1px solid #ddd; border-bottom:1px solid #ddd; background-color: #eee; padding: 10px;">[{$headline}]</h3>

    <table cellpadding="2" cellspacing="0" width="90%" class="m20" style="table-layout: fixed">
        <colgroup>
            <col style="width:250px;" />
        </colgroup>
    [{foreach $systemSettings as $key => $data}]
        <tr>
            <td>[{isys type="lang" ident=$data.title}]</td>
            <td>
                [{if $data.type == 'select'}]
                <select name="setting__[{$key}]" class="input">
                    [{foreach $data.options as $value => $label}]
                    <option value="[{$value}]" [{if $settings[$key] == $value}]selected="selected"[{/if}] >[{isys type="lang" ident=$label}]</option>
                    [{/foreach}]
                </select>
                [{elseif $data.type == 'multiselect'}]
                <select name="setting__[{$key}]" class="input chosen" multiple>
                    [{foreach $data.options as $value => $label}]
                    <option value="[{$value}]" [{if in_array($value, $settings[$key])}]selected="selected"[{/if}]>[{isys type="lang" ident=$label}]</option>
                    [{/foreach}]
                </select>
                [{elseif $data.type == 'textarea'}]
                    <textarea type="text" class="input" name="setting__[{$key}]" placeholder="[{$data.placeholder|escape:"html"}]">[{$settings[$key]|escape:"html"|default:$data.default}]</textarea>
                [{elseif $data.type == 'password'}]
                    <input type="password" class="input fl" name="setting__[{$key}]" value="[{$settings[$key]}]" placeholder="[{$data.placeholder|escape:"html"}]" data-type="password" data-value="[{$settings[$key]}]" />
                    <input type="hidden" class="hide" id="setting__[{$key}]__action" name="setting__[{$key}]__action" value="unchanged" />
                    <div class="fl p5">
                        <img src="../images/axialis/basic/padlock-filled.svg" title="password field" alt="" />
                    </div>
                [{else}]
                    <input type="text" class="input" name="setting__[{$key}]" value="[{$settings[$key]|escape:"html"|default:$data.default}]" placeholder="[{$data.placeholder|escape:"html"}]" />
                [{/if}]

            </td>
        </tr>
    [{/foreach}]
    </table>
[{/foreach}]
</form>

<div class="m10">
    <button type="button" class="btn bold" id="saveConfig">
        <img src="../images/icons/silk/disk.png" class="mr5" /><span>Save</span>
    </button>

	<div class="p5 mt10" id="configSaveResult" style="display:none;"></div>
	<br class="cb" />
</div>

<!-- @see ID-9310 Include chosen -->
<script type="text/javascript" src="../src/tools/js/chosen/chosen.js"></script>

<script type="text/javascript">
    const $passwordFields = $$('[data-type="password"]');

    $('saveConfig').on('click', function () {
        new Ajax.Request('?req=settings&action=save', {
            parameters: {
                settings: JSON.stringify($('settings').serialize(true))
            },
            onComplete: function (xhr) {
                const json = xhr.responseJSON;
                const $saveResult = $('configSaveResult').removeClassName('note').removeClassName('error');
                let message;

                if (json) {
                    message = json.message;
                    $saveResult.addClassName(json.success ? 'note' : 'error');
                } else {
                    message = 'Unknown Error. Config was not saved!';
                    $saveResult.addClassName('error');
                }

                $saveResult.update(message).show();
            }
        });
    });

    $$('select.chosen').each(($select) => new Chosen($select));

    // When the content of a password field is changed, we note that down so that the new value gets saved.
    $passwordFields.invoke('on', 'change', (ev) => {
        ev.findElement('input').next('[type="hidden"]').setValue('changed');
    });

    // Append the "focus" and "blur" event, that empties the value to enforce the user to input the complete password.
    $passwordFields.invoke('on', 'focus', (ev) => {
        const $field = ev.findElement('input');
        const $fieldAction = $field.next('[type="hidden"]');

        if ($fieldAction.getValue() === 'unchanged') {
            $field.setValue('');
        }
    });

    $passwordFields.invoke('on', 'blur', (ev) => {
        const $field = ev.findElement('input');
        const $fieldAction = $field.next('[type="hidden"]');

        if ($fieldAction.getValue() === 'unchanged' && $field.getValue().blank()) {
            $field.setValue($field.readAttribute('data-value'));
        }
    });

    // add SMTP checker
    $$('h3', '#settings').each(function (h3) {
        if (h3.innerHTML === 'SMTP') {
            const table = h3.next('table');
            if (table) {
                table.insert({
                    after: '<div class="m10"><button type="button" id="checkSMTP" class="btn">Check SMTP connection</button></div>'
                });
            }
        }
    });
    const checkSMTPBtn = $('checkSMTP');
    if (checkSMTPBtn) {
        checkSMTPBtn.observe('click', function (event) {
            const settings = $('settings').serialize(true);
            new Ajax.Request('?req=settings&action=checkSMTP', {
                method:     'POST',
                parameters: {
                    host:            settings['setting__system.email.smtp-host'],
                    port:            settings['setting__system.email.port'],
                    timeout:         settings['setting__system.email.connection-timeout'],
                    username:        settings['setting__system.email.username'],
                    password:        settings['setting__system.email.password'],
                    'smtp-auto-tls': settings['setting__system.email.smtp-auto-tls']
                },
                onComplete: function (xhr) {
                    let smptResult = $('smptResult');
                    if (!smptResult) {
                        checkSMTPBtn.insert({
                            after: '<span class="m10 p5" id="smptResult"></span>'
                        });
                        smptResult = $('smptResult');
                    }
                    smptResult.removeClassName('note').removeClassName('error');

                    const json = xhr.responseJSON;
                    let message;
                    if (json && json.message) {
                        message = json.message;
                        smptResult.addClassName(json.success ? 'note' : 'error');
                    } else {
                        message = 'Unknown Error.';
                        smptResult.addClassName('error');
                    }
                    smptResult.update(message);
                }
            });
        });
    }
</script>
