<div class="gradient content-header">
    <img src="../images/icons/silk/wrench.png" class="vam mr5" /><span class="bold headline vam">Expert settings</span>
</div>

<form id="settings">
    <table id="expertTable" cellpadding="2" cellspacing="0" width="90%" class="m20" style="table-layout: fixed">
        <colgroup>
            <col style="width:250px;" />
            <col style="width:330px;" />
            <col style="width:auto;" />
        </colgroup>
        <tbody>
            [{foreach $settings as $key => $value}]
            <tr>
                <td>
                    <input type="hidden" name="remove__[{$key}]" class="remove" value="0" />
                    [{$key}]
                </td>
                <td>
                    [{if is_scalar($value) && strstr($value, "\n")}]
                    <textarea
                        rows="8"
                        class="input"
                        placeholder="[{$setting.placeholder}]"
                        name="settings__[{$key}]">[{$value|default:$setting.default}]</textarea>
                    [{else}]
                <input class="input" type="text" name="setting__[{$key}]" value="[{$value|default:$setting.default}]" />
                    [{/if}]
                </td>
                <td>
                    <button type="button" class="remove hide btn ml-auto" title="Remove this setting" data-action="delete">
                        <img src="../images/axialis/basic/symbol-cancel.svg" alt="" />
                    </button>
                </td>
            </tr>
            [{/foreach}]
            <tr>
                <td><input class="input" type="text" name="custom__key[]" value="" placeholder="key" style="width: 95%" /></td>
                <td><input class="input" type="text" name="custom__value[]" value="" placeholder="value" /></td>
                <td>
                    <button type="button" class="btn" title="Add additional setting" data-action="add-new-setting">
                        <img src="../images/axialis/basic/symbol-add.svg" alt="" />
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</form>

<div class="m10">
    <button type="button" class="btn bold" id="saveConfig">
        <img src="../images/icons/silk/disk.png" class="mr5" /><span>Save</span>
    </button>

    <div class="p5 mt10" id="configSaveResult" style="display:none;"></div>
    <br class="cb" />
</div>

<script type="text/javascript">
    const $expertTable = $('expertTable');

    $expertTable.on('mouseover', 'tbody tr', function (ev) {
        const $row = ev.findElement('tr');

        if (ev.altKey) {
            const $link = $row.down('[data-action="delete"]');
            if ($link) {
                $link.removeClassName('hide');
            }
        }
    });

    $expertTable.on('mouseout', 'tbody tr', function (ev) {
        const $row = ev.findElement('tr');
        const $link = $row.down('[data-action="delete"]');

        if ($link) {
            $link.addClassName('hide');
        }
    });

    $expertTable.on('click', '[data-action="delete"]', function (ev) {
        const $tr = ev.findElement('button').up('tr');

        if ($tr) {
            const input = $tr.down('td:first-child').down('input.remove');

            if (input) {
                if (input.getValue() == '0') {
                    input.setValue('1');
                    $tr.setStyle('text-decoration:line-through;')
                        .select('td')
                        .invoke('addClassName', 'bg-red');
                } else {
                    input.setValue('0');
                    $tr.setStyle('text-decoration:none;')
                        .select('td')
                        .invoke('removeClassName', 'bg-red');
                }
            } else {
                alert('Error deleting line.');
            }
        }
    });

    $expertTable.on('click', '[data-action="add-new-setting"]', function (ev) {
        const $button = ev.findElement('button');

        $expertTable.down('tbody').insert($button.up('tr').innerHTML);

        $button.remove();

        $expertTable.select('tr:last-child input').invoke('setValue', '');
    });

    $('saveConfig').on('click', function () {

        new Ajax.Request('?req=settings&expert=1&action=save', {
            parameters: {
                settings: JSON.stringify($('settings').serialize(true))
            },
            onComplete: function (xhr) {
                var json        = xhr.responseJSON,
                    message,
                    $saveResult = $('configSaveResult').removeClassName('note').removeClassName('error');

                if (json) {
                    message = json.message;
                    $saveResult.addClassName(json.success ? 'note' : 'error');

                    if (json.success) {
                        setTimeout(function() {
                            window.location.reload();
                        }, 2500);
                    }
                } else {
                    message = 'Unknown Error. Config was not saved!';
                    $saveResult.addClassName('error');
                }

                $saveResult.update(message).show();
            }
        });

    });
</script>
