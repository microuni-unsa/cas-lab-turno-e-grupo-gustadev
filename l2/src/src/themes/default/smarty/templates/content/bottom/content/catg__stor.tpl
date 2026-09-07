<input type="hidden" name="stor_id" value="[{$stor_id}]" />

<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_TYPE" ident="LC__CATG__STORAGE_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__STORAGE_TYPE"}]</td>
	</tr>

	[{if $new_catg_stor == "1"}]
	<tr>
        <td class="category-spacer" colspan="2"><hr /></td>
	</tr>
	[{isys type='f_title_suffix_counter' name='C__CATG__STORAGE__SUFFIX' title_identifier='C__CATG__STORAGE_TITLE' label_counter='LC__CMDB__CATG__STORAGE__NUMBER_NEW'}]
	[{/if}]

	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_TITLE" ident="LC__CATG__STORAGE_TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_TITLE"}]</td>
	</tr>
	<tr class="type-not-san">
        <td class="category-spacer" colspan="2"><hr /></td>
	</tr>
	<tr class="type-basics type-tape">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_MANUFACTURER" ident="LC__CATG__STORAGE_MANUFACTURER"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__STORAGE_MANUFACTURER"}]</td>
	</tr>
	<tr class="type-basics type-tape">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_MODEL" ident="LC__CATG__STORAGE_MODEL"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__STORAGE_MODEL"}]</td>
	</tr>
	<tr class="type-basics type-tape">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_CAPACITY" ident="LC__CATG__STORAGE_CAPACITY"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_CAPACITY"}] [{isys type="f_dialog" name="C__CATG__STORAGE_UNIT"}]</td>
	</tr>
	<tr class="type-hd">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_HOTSPARE" ident="LC__CATG__STORAGE_HOTSPARE"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_HOTSPARE" p_bDbFieldNN="1"}]</td>
	</tr>
	<tr class="type-not-san">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_CONNECTION_TYPE" ident="LC__CATG__STORAGE_CONNECTION_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__STORAGE_CONNECTION_TYPE"}]</td>
	</tr>
	<tr class="type-hd">
        <td class="category-spacer" colspan="2"><hr /></td>
	</tr>
	<tr>
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_CONTROLLER" ident="LC__CATG__STORAGE_CONTROLLER"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_CONTROLLER"}]</td>
	</tr>
	<tr class="type-hd">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_RAIDGROUP" ident="LC__CATG__RAIDGROUP"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_RAIDGROUP"}]</td>
	</tr>
	<tr class="type-basics">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_SERIAL' ident="LC__CATG__STORAGE_SERIAL"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_SERIAL"}]</td>
	</tr>
	<tr class="type-raid">
        <td class="category-spacer" colspan="2"><hr /></td>
	</tr>
	<tr class="type-raid">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_RAIDLEVEL' ident="LC__CATG__STORAGE_RAIDLEVEL"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_RAIDLEVEL" id="C__CATG__STORAGE_RAIDLEVEL"}]</td>
	</tr>
	<tr class="type-raid">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_CONNECTION' ident="LC__CATG__STORAGE_CONNECTION"}]</td>
		<td class="value">[{isys type="f_dialog_list" name="C__CATG__STORAGE_CONNECTION"}]</td>
	</tr>
	<tr class="type-raid">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_RAID_TOTALCAPACITY' ident="LC__CATG__CMDB_MEMORY_TOTALCAPACITY"}]</td>
		<td class="value">[{isys type="f_data" name="C__CATG__STORAGE_RAID_TOTALCAPACITY" id="C__CATG__STORAGE_RAID_TOTALCAPACITY"}]</td>
	</tr>
	<tr class="type-raid">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_RAID_TOTALCAPACITY_REAL' ident="LC__CATG__CMDB__MEMORY__USABLE_TOTALCAPACITY"}]</td>
		<td class="value">[{isys type="f_data" name="C__CATG__STORAGE_RAID_TOTALCAPACITY_REAL" id="C__CATG__STORAGE_RAID_TOTALCAPACITY_REAL"}]</td>
	</tr>
	<tr class="type-san">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_SANPOOL' ident="LC__CATG__STORAGE_SANPOOL"}]</td>
		<td class="value">[{isys type="f_dialog" name="C__CATG__STORAGE_SANPOOL"}]</td>
	</tr>
	<tr class="type-streamer">
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_FC_ADDRESS' ident="LC__CATG__STORAGE_FC_ADDRESS"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_FC_ADDRESS"}]</td>
	</tr>
	<tr class="type-tape type-streamer">
		<td class="key">[{isys type="f_label" name="C__CATG__STORAGE_LTO_TYPE" ident="LC__CATG__STORAGE_LTO_TYPE"}]</td>
		<td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__STORAGE_LTO_TYPE"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__STORAGE_FIRMWARE' ident="LC__CATG__STORAGE_FIRMWARE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__STORAGE_FIRMWARE"}]</td>
	</tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__STORAGE_SLOT' ident="LC__CATG__STORAGE_SLOT"}]</td>
        <td class="value">[{isys type="f_text" name="C__CATG__STORAGE_SLOT"}]</td>
    </tr>
</table>

<script type="text/javascript">
    (function () {
        'strict mode';

        const $typeSelector = $('C__CATG__STORAGE_TYPE');
        const typeHD = +'[{$smarty.const.C__STOR_TYPE_DEVICE_HD}]';
        const typeFloppy = +'[{$smarty.const.C__STOR_TYPE_DEVICE_FLOPPY}]';
        const typeCdRom = +'[{$smarty.const.C__STOR_TYPE_DEVICE_CD_ROM}]';
        const typeTape = +'[{$smarty.const.C__STOR_TYPE_DEVICE_TAPE}]';
        const typeStick = +'[{$smarty.const.C__STOR_TYPE_DEVICE_STICK}]';
        const typeSSD = +'[{$smarty.const.C__STOR_TYPE_DEVICE_SSD}]';
        const typeSdCard = +'[{$smarty.const.C__STOR_TYPE_DEVICE_SD_CARD}]';
        const typeStreamer = +'[{$smarty.const.C__STOR_TYPE_DEVICE_STREAMER}]';
        const typeNVMe = +'[{$smarty.const.C__STOR_TYPE_DEVICE_NVME}]';

        const processFieldVisibility = (selectedId) => {
            const $allFields = $$('.type-not-san,.type-hd,.type-raid,.type-san,.type-streamer,.type-basics,.type-tape');
            const mapping = {
                '.type-hd,.type-not-san,.type-basics,.type-tape': [typeHD, typeSSD, typeSdCard],
                '.type-san,.type-tape': [typeTape],
                '.type-streamer,.type-basics': [typeStreamer],
                '.type-basics,.type-not-san': [-1, typeFloppy, typeCdRom, typeStick, typeNVMe]
            };

            for (let selector in mapping) {
                if (mapping.hasOwnProperty(selector) && mapping[selector].includes(selectedId)) {
                    $allFields.invoke('hide');
                    $$(selector).invoke('show');
                    return;
                }
            }

            // @see ID-11369 If no predefined mapping works out, show all fields.
            $allFields.invoke('show');
        };

        if ($typeSelector) {
            $typeSelector.on('change', () => processFieldVisibility(+$typeSelector.getValue()));
        }

        processFieldVisibility($$('input[name="SM2__C__CATG__STORAGE_TYPE[p_strSelectedID]"]')[0].getValue());

        idoit.Require.require('smartyRaid', function () {
            raidcalc('[{$raid.numdisks}]', '[{$raid.each}]', '[{$raid.level}]', 'C__CATG__STORAGE_RAID_TOTALCAPACITY', 'C__CATG__STORAGE_RAID_TOTALCAPACITY_REAL');
        });
    })();
</script>
