<table id="expertTable" class="listing layout-fixed p0">
    <colgroup>
        <col style="width:35%;" />
        <col style="width:35%;" />
        <col style="width:30%;" />
    </colgroup>
    <thead>
    <tr>
        <th>Key</th>
        <th>Value</th>
        <th>Type</th>
    </tr>
    </thead>
    <tbody>
    [{foreach from=$settings item="keys" key="type"}]
        [{foreach from=$keys item="value" key="key"}]
        [{if strpos($key, 'admin.') !== 0}]
        <tr>
            <td>
                <input type="hidden" name="remove_settings[[{$type}]][[{$key}]]" class="remove" value="0" />
                [{$key}]
            </td>
            <td>
                [{if is_scalar($value) && strstr($value, "\n")}]

                <div class="overflow-auto">
                    [{isys
                        type="f_textarea"
                        name="settings[`$type`][`$key`]"
                        p_strValue=$value|default:$setting.default
                        p_strClass="input-block"
                        p_bInfoIconSpacer=0}]
                    </div>
                [{else}]

                <div class="overflow-auto">
                    [{isys
                        type="f_text"
                        name="settings[`$type`][`$key`]"
                        p_strValue=$value|default:$setting.default
                        skipHtmlEntities=true
                        p_strClass="input-block"
                        p_bInfoIconSpacer=0}]
                </div>

                [{/if}]
            </td>
            <td class="pl5">
                <div class="display-flex align-items-center">
                    <span>[{$type}]</span>
                    <button type="button" class="remove hide btn ml-auto" title="Remove this setting" data-tooltip="1" data-action="delete">
                        <img src="[{$dir_images}]/axialis/basic/symbol-cancel.svg" alt="" />
                    </button>
                </div>
            </td>
        </tr>
        [{/if}]
        [{/foreach}]
    [{/foreach}]
    [{if $isEditing}]
    <tr>
        <td>
            <input class="input input-block" type="text" name="custom_settings[key][]" value="" placeholder="key" />
        </td>
        <td>
            <input class="input input-block" type="text" name="custom_settings[value][]" value="" placeholder="value" />
        </td>
        <td style="padding-left:5px;">
            <select name="custom_settings[type][]" class="input input-mini">
                <option value="[{isys_module_system_settings::TENANT_WIDE}]">[{isys_module_system_settings::TENANT_WIDE}]</option>
                <option value="[{isys_module_system_settings::USER}]">[{isys_module_system_settings::USER}]</option>
            </select>
            <button type="button" class="btn" title="Add additional setting" data-tooltip="1" data-action="add-new-setting">
                <img src="[{$dir_images}]/axialis/basic/symbol-add.svg" alt="" />
            </button>
        </td>
    </tr>
    [{/if}]
    </tbody>
</table>
[{if $isEditing}]
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
                idoit.Notify.warning('Error deleting line.');
            }
        }
    });

    $expertTable.on('click', '[data-action="add-new-setting"]', function (ev) {
        const $button = ev.findElement('button');

        $expertTable.down('tbody').insert($button.up('tr').innerHTML);

        $button.remove();

        $expertTable.select('tr:last-child input').invoke('setValue', '');

        $('contentWrapper').scrollTop = $('contentWrapper').down('table').getHeight();
    });
</script>
[{/if}]
