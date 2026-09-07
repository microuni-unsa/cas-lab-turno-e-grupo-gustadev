<div class="p20">
    <div class="mb20 display-flex">
        <button type="button" class="btn" id="add_multiple_counters_button" [{if !$canCreate}]disabled="disabled"[{/if}]>
            <img src="[{$dir_images}]axialis/basic/symbol-add.svg" alt="" /><span>[{isys type="lang" ident="LC__UNIVERSAL__ADD_COUNTER"}]</span>
        </button>

        [{isys type="f_count" name="count_of_custom_counters" p_bDisabled=!$canCreate}]
    </div>

    <table id="countersTable" class="mainTable">
        <colgroup>
            <col style="width:60%;" />
            <col style="width:20%;" />
            <col style="width:10%;" />
            <col style="width:10%;" />
        </colgroup>
        <thead>
        <tr>
            <th>[{isys type="lang" ident="LC__MODULE__QCW__TITLE"}]</th>
            <th>[{isys type="lang" ident="LC__REGEDIT__VALUE"}]</th>
            <th>
                <img title="[{isys type="lang" ident="LC__CUSTOM_COUNTER__RESET_INFO"}]" src="[{$dir_images}]axialis/basic/button-info.svg" data-tooltip="1" class="vam">
                <span class="vam">[{isys type="lang" ident="LC__CUSTOM_COUNTER__RESET"}]</span>
            </th>
            <th>[{isys type="lang" ident="LC__MODULE__QCW__DELETE"}]</th>
        </tr>
        </thead>
        <tbody>
        [{foreach from=$data item="value" key="key"}]
            <tr>
                <td>
                    <input type="hidden" name="counter[[{$key}]][key]" value="[{$key}]" />
                    <input type="hidden" class="deleter" name="counter[[{$key}]][is_deleted]" value="0" />
                    %[{$key}]%
                </td>
                <td class="counter_column">
                    <input type="hidden" class="counter" name="counter[[{$key}]][value]" value="[{$value}]" />
                    <span>[{$value}]</span>
                </td>
                <td>
                    <button class="btn element-reset fr" [{if !$canEdit}]disabled="disabled"[{/if}]>
                        <img src="[{$dir_images}]axialis/basic/symbol-update.svg" alt=""><span>[{isys type="lang" ident="LC__CUSTOM_COUNTER__RESET"}]</span>
                    </button>
                </td>
                <td>
                    <button class="btn element-delete" [{if !$isSupervisor}]disabled="disabled"[{/if}]>
                        <img src="[{$dir_images}]axialis/industry-manufacturing/waste-bin.svg" alt=""><span>[{isys type="lang" ident="LC__MODULE__QCW__DELETE"}]</span>
                    </button>
                </td>
            </tr>
        [{/foreach}]
        [{foreach from=$new_counters item="value"}]
            <tr class="new-counter">
                <td>
                    <div class="input-group">
                        <div class="input-group-addon input-group-addon-unstyled">%COUNTER_</div>
                        <input class="input input-block" type="text" name="new_counters[key][]" value="[{$value}]">
                        <div class="input-group-addon input-group-addon-unstyled">%</div>
                    </div>
                </td>
                <td><input class="input input-block" type="hidden" name="new_counters[value][]" value="1">1</td>
                <td></td>
                <td>
                    <button type="button" class="btn element-delete">
                        <img src="[{$dir_images}]axialis/industry-manufacturing/waste-bin.svg" alt=""><span>[{isys type="lang" ident="LC__MODULE__QCW__DELETE"}]</span>
                    </button>
                </td>
            </tr>
            [{/foreach}]
        </tbody>
    </table>
</div>

<script>
    const $table = $('countersTable');

    $('add_multiple_counters_button').on('click', function () {
        var countOfFields = $F('count_of_custom_counters');
        do {
            $row = new Element('tr',{ className:'new-counter'});
            $row.insert(new Element('td').update(new Element('div', { className: 'input-group'})
                    .insert(new Element('div', { className: 'input-group-addon input-group-addon-unstyled'}).update('%COUNTER_'))
                    .insert(new Element('input',{ className: 'input input-block', type: 'text', name: 'new_counters[key][]', value: ''}))
                    .insert(new Element('div', { className: 'input-group-addon input-group-addon-unstyled'}).update('%'))
                    .insert(new Element('input',{ className: 'deleter', type:'hidden'}))))
                .insert(new Element('td')
                    .insert(new Element('input',{ className: 'input input-block', type: 'hidden', name: 'new_counters[value][]', value: 1}))
                    .insert('1'))
                .insert(new Element('td'))
                .insert(new Element('td')
                    .insert(new Element('a',{ className: "btn element-delete"})
                        .insert(new Element('img',{ src: window.dir_images + 'axialis/industry-manufacturing/waste-bin.svg', alt: ''}))
                        .insert(new Element('span').update('[{isys type="lang" ident="LC__MODULE__QCW__DELETE"}]'))));
            $table.down('tbody').insert($row);
            countOfFields--;
        } while (countOfFields>0)
        // Find out how many fields already exist:

    })
    $table.on('click', '.element-delete', function(ev, el) {
        if(confirm('[{isys type="lang" ident="LC__CUSTOM_COUNTER__DELETE_ALERT" p_bHtmlEncode=0}]')){
            $row = el.up().up();
            if($row.hasClassName('new-counter')) {
                $row.remove();
            } else {
                $row.down('.deleter').value = 1;
                $row.hide();
            }
        }
    })
    $table.on('click', '.element-reset', function(ev, el) {
        if(confirm('[{isys type="lang" ident="LC__CUSTOM_COUNTER__RESET_INFO" p_bHtmlEncode=0}]')){
            el.up().up().down('.counter').value = 1;
            el.up().up().down('.counter_column').down('span').innerText = '1';
        }
    })
</script>