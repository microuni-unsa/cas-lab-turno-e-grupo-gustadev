<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" name="C__SETTING__STATUS__SHOW_FILTER" ident="LC__CMDB__SYSTEM_SETTING__SHOW_FILTER"}] (my-doit)</td>
		<td class="value">[{isys type="f_dialog" name="C__SETTING__STATUS__SHOW_FILTER" p_bDbFieldNN="1" p_strClass="input input-small"}]</td>
	</tr>
</table>

<table class="mainTable" cellpadding="0" cellspacing="0">
	<thead>
		<tr>
			<th>ID</th>
			<th>Status</th>
			<th>[{isys type="lang" ident="LC__CMDB__OBJTYPE__CONST_NAME"}]</th>
			<th>[{isys type="lang" ident="LC__CMDB__OBJTYPE__CONST"}]</th>
			<th>[{isys type="lang" ident="LC__UNIVERSAL__COLOR"}]</th>
			[{if isys_glob_is_edit_mode()}]<th>Optionen</th>[{/if}]
			</tr>
	</thead>
	<tbody id="cmdb-status-table-body">
	[{foreach $cmdb_status as $s}]
		<tr>
			<td>[{$s.isys_cmdb_status__id}]</td>
			[{if isys_glob_is_edit_mode()}]
			<td>[{isys type="lang" ident=$s.isys_cmdb_status__title}]</td>
			<td><input type="text" class="input input-size-block" name="status_title[[{$s.isys_cmdb_status__id}]]" value="[{$s.isys_cmdb_status__title}]" /></td>
			<td><input type="text" class="input input-size-block" style="color:#777;" name="status_const[[{$s.isys_cmdb_status__id}]]" value="[{$s.isys_cmdb_status__const}]" /></td>
			<td>
                [{isys
                    type="f_colorpicker"
                    id="status_color_"|cat:$s.isys_cmdb_status__id
                    name="status_color["|cat:$s.isys_cmdb_status__id|cat:"]"
                    p_strValue=$s.isys_cmdb_status__color
                    size="small"
                    containerClass=""}]
            </td>
			<td>
				<button class="btn" type="button" onclick="if (confirm('[{isys type="lang" ident="LC__UNIVERSAL__REALLY_DELETE"}]?')) { this.up().up().remove(); $('delStatus').value = $('delStatus').value + '[{$s.isys_cmdb_status__id}],'; }">
					<img src="[{$dir_images}]axialis/industry-manufacturing/waste-bin.svg" alt="" /><span>[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]</span>
				</button>
			</td>
			[{else}]
			<td>[{isys type="lang" ident=$s.isys_cmdb_status__title}]</td>
			<td><strong>[{$s.isys_cmdb_status__title}]</strong></td>
			<td>[{$s.isys_cmdb_status__const}]</td>
			<td><div class="cmdb-marker" style="background-color:[{$s.isys_cmdb_status__color}]; width:12px; height:12px;"></div> [{$s.isys_cmdb_status__color}]</td>
			[{/if}]
		</tr>
	[{/foreach}]
	</tbody>
</table>

<input type="hidden" name="delStatus" id="delStatus" value="" />

[{if isys_glob_is_edit_mode()}]
<button type="button" class="btn m10" id="create-new-cmdb-status">
    <img src="[{$dir_images}]axialis/basic/symbol-add.svg" alt="" /><span>[{isys type="lang" ident="LC__UNIVERSAL__NEW_VALUE"}]</span>
</button>
[{/if}]

<script type="text/javascript">
    const $createNewCmdbStatusButton = $('create-new-cmdb-status');

    if ($createNewCmdbStatusButton) {
        $createNewCmdbStatusButton.on('click', function() {
            const $cmdbStatusTitle = new Element('input', { type: 'text', className: 'input input-size-block', name: 'new_status_title[]' });
            const $cmdbStatusConstant = new Element('input', { type: 'text', className: 'input input-size-block', name: 'new_status_const[]', value: 'C__CMDB_STATUS__' });
            const $cmdbStatusColor = new Element('input', { type: 'text', className: 'input input-size-small', value: '#ffffff', name: 'new_status_color[]', 'data-colorpicker': '' });

            const $removeButton = new Element('button', { className: 'btn', type: 'button' })
                .update(new Element('img', { src: window.dir_images + 'axialis/industry-manufacturing/waste-bin.svg', alt: '' }))
                .insert(new Element('span').update('[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]'));

            $cmdbStatusTitle.on('keyup', function () {
                $cmdbStatusConstant.setValue('C__CMDB_STATUS__' + slugify($cmdbStatusTitle.getValue()).replace(/-/g, '_').toUpperCase());
            });

            $removeButton.on('click', function () {
                $(this).up("tr").remove();
            });

            $('cmdb-status-table-body')
                .insert(new Element('tr')
                    .update(new Element('td').update('-'))
                    .insert(new Element('td').update('-'))
                    .insert(new Element('td').update($cmdbStatusTitle))
                    .insert(new Element('td').update($cmdbStatusConstant))
                    .insert(new Element('td').update($cmdbStatusColor))
                    .insert(new Element('td').update($removeButton))
                );
        });
    }
</script>
