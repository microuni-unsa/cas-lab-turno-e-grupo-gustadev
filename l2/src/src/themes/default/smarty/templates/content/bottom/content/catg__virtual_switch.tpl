<script type="text/javascript">
	var scp_count = parseInt('[{$scp_count}]'),
			vmk_count = parseInt('[{$vmk_count}]'),
			pg_count = parseInt('[{$pg_count}]'),
			ip_list = '<option value="-1">-</option>';

	[{foreach $ip_list as $ip_id => $ip}]
	ip_list += '<option value="[{$ip_id}]">[{$ip}]</option>';
	[{/foreach}]

	function remove_service_console_port(id) {
		scp_count--;
		$('scp_' + id).remove();

		if (scp_count == 0)
		{
			$('scp_table').hide();
			$('scp_none').show();
		}
        $('body').fire('update:tooltips');
	}

	function remove_vmkernel_port(id) {
		vmk_count--;
		$('vmk_' + id).remove();

		if (vmk_count == 0)
		{
			$('vmk_table').hide();
			$('vmk_none').show();
		}
        $('body').fire('update:tooltips');
	}

	function remove_port_group(id) {
		pg_count--;
		$('pg_' + id).remove();

		if (pg_count == 0)
		{
			$('pg_table').hide();
			$('pg_none').show();
		}
        $('body').fire('update:tooltips');
	}

	function add_port_group() {
		$('pg_none').hide();
		$('pg_table').show();

		var $tr      = new Element('tr', {
			    id:   'pg_' + pg_count,
			    name: 'pg_' + pg_count
		    }),
		    $input_a = new Element('input', {
			    type:      'text',
			    className: 'input input-small',
			    name:      'C__CATG__VSWITCH_PG_NAME_' + pg_count
		    }),
		    $input_b = new Element('input', {
			    type:      'text',
			    className: 'input input-small',
			    name:      'C__CATG__VSWITCH_PG_VLANID_' + pg_count
		    }),

		    $remover = new Element('button', {type: 'button', className: 'btn btn-small fr', title: '[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]', 'data-tooltip': 1, onclick: 'remove_port_group(' + pg_count + ');'}).update(
				    new Element('img', {
					    src: '[{$dir_images}]axialis/basic/symbol-cancel.svg'
				    })
		    );

		$('pg_table_body').insert(
				$tr.insert(new Element('td').update($input_a))
				   .insert(new Element('td').update($input_b))
				   .insert(new Element('td'))
				   .insert(new Element('td').update($remover)));

		pg_count++;
        $('body').fire('update:tooltips');
	}

	function add_service_console_port() {
		$('scp_none').hide();
		$('scp_table').show();

		var $tr      = new Element('tr', {
			    id:   'scp_' + scp_count,
			    name: 'scp_' + scp_count
		    }),
		    $input   = new Element('input', {
			    type:      'text',
			    className: 'input input-small',
			    name:      'C__CATG__VSWITCH_SCP_NAME_' + scp_count
		    }),
		    $select  = new Element('select', {
			    name:      'C__CATG__VSWITCH_SCP_ADDRESS_' + scp_count,
			    className: 'input input-small'
		    }),

		    $remover = new Element('button', {type: 'button', className: 'btn btn-small fr', title: '[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]', 'data-tooltip': 1, onclick: 'remove_service_console_port(' + scp_count + ');'}).update(
				    new Element('img', {
					    src: '[{$dir_images}]axialis/basic/symbol-cancel.svg'
				    })
		    );

		$select.innerHTML = ip_list;

		$('scp_table_body').insert(
				$tr.insert(new Element('td').update($input))
				   .insert(new Element('td').update($select))
				   .insert(new Element('td').update($remover)));

		scp_count++;
        $('body').fire('update:tooltips');
	}

	function add_vmkernel_port() {
		$('vmk_none').hide();
		$('vmk_table').show();

		var $tr      = new Element('tr', {
			    id:   'vmk_' + vmk_count,
			    name: 'vmk_' + vmk_count
		    }),
		    $input   = new Element('input', {
			    type:      'text',
			    className: 'input input-small',
			    name:      'C__CATG__VSWITCH_VMK_NAME_' + vmk_count
		    }),
		    $select  = new Element('select', {
			    name:      'C__CATG__VSWITCH_VMK_ADDRESS_' + vmk_count,
			    className: 'input input-small'
		    }),

		    $remover = new Element('button', {type: 'button', className: 'btn btn-small fr', title: '[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]', 'data-tooltip': 1, onclick: 'remove_vmkernel_port(' + vmk_count + ');'}).update(
				    new Element('img', {
					    src: '[{$dir_images}]axialis/basic/symbol-cancel.svg'
				    })
		    );

		$select.innerHTML = ip_list;

		$('vmk_table_body').insert(
				$tr.insert(new Element('td').update($input))
				   .insert(new Element('td').update($select))
				   .insert(new Element('td').update($remover)));

		vmk_count++;

        $('body').fire('update:tooltips');
	}
</script>

<table class="contentTable">
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__VSWITCH_TITLE' ident="LC__CMDB__CATG__TITLE"}]</td>
		<td class="value">[{isys type="f_text" name="C__CATG__VSWITCH_TITLE" tab="1"}]</td>
	</tr>
	<tr>
		<td class="key">[{isys type='f_label' name='C__CATG__VSWITCH_PORTS' ident="LC__CMDB__CATG__VIRTUAL_SWITCH__PORTS"}]</td>
		<td class="value">[{isys type="f_dialog_list" emptyMessage="LC__CMDB__CATG__VIRTUAL_SWITCH__PORTS_EMPTY" placeholder="LC__CMDB__CATG__VIRTUAL_SWITCH__PORTS_PLACEHOLDER" name="C__CATG__VSWITCH_PORTS" p_bLinklist="1"}]</td>
	</tr>
</table>

<h2 class="border-bottom border-top bg-neutral-200 mt10 p5 display-flex align-items-center">
	[{isys type="lang" ident="LC__CMDB__CATG__VSWITCH__PORT_GROUPS"}]
	[{if isys_glob_is_edit_mode()}]
		<button type="button" class="ml-auto btn btn-small" onclick="add_port_group();">
			<img src="[{$dir_images}]axialis/basic/symbol-add.svg" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
		</button>
	[{/if}]
</h2>
<table class="contentTable p0" style="border-top: none;">
	<tr>
		<td class="p0">
			<p id="pg_none" class="p5 m5 box-blue" style="display:none">[{isys type="lang" ident="LC__CMDB__CATG__VSWITCH__PG_NONE"}]</p>

			<table id="pg_table" class="listing" cellspacing="0">
				<thead>
				<tr>
					<th>[{isys type="lang" ident="LC_UNIVERSAL__NAME"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATG__VSWITCH__VLAN_ID"}]</th>
					<th>[{isys type="lang" ident="LC__CMDB__CATG__GUEST_SYSTEMS"}]</th>
					[{if isset($smarty.post.navMode) && ($smarty.post.navMode == 2 || $smarty.post.navMode == 1)}]
						<th style="width: 20px; text-align:right;">[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]</th>
					[{/if}]
				</tr>
				</thead>
				<tbody id="pg_table_body">
				[{$pg_data}]
				</tbody>
			</table>
		</td>
	</tr>
</table>

<h2 class="border-bottom border-top bg-neutral-200 mt10 p5 display-flex align-items-center">
	[{isys type="lang" ident="LC__CMDB__CATG__VSWITCH__SERVICE_CONSOLE_PORTS"}]
	[{if isys_glob_is_edit_mode()}]
		<button type="button" class="ml-auto btn btn-small" onclick="add_service_console_port();">
            <img src="[{$dir_images}]axialis/basic/symbol-add.svg" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
		</button>
	[{/if}]
</h2>
<table class="contentTable p0" style="border-top: none;">
	<tr>
		<td class="p0">
			<p id="scp_none" class="p5 m5 box-blue" style="display:none">[{isys type="lang" ident="LC__CMDB__CATG__VSWITCH__SCP_NONE"}]</p>

			<table id="scp_table" class="listing" cellspacing="0">
				<thead>
				<tr>
					<th>[{isys type="lang" ident="LC_UNIVERSAL__NAME"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__IP_ADDRESS"}]</th>
					[{if isset($smarty.post.navMode) && ($smarty.post.navMode == 2 || $smarty.post.navMode == 1)}]
						<th style="width: 20px; text-align:right;">[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]</th>
					[{/if}]
				</tr>
				</thead>
				<tbody id="scp_table_body">
				[{$scp_data}]
				</tbody>
			</table>
		</td>
	</tr>
</table>

<h2 class="border-bottom border-top bg-neutral-200 mt10 p5 display-flex align-items-center">
	[{isys type="lang" ident="LC__CMDB__CATG__VSWITCH__VMKERNEL_PORTS"}]
	[{if isys_glob_is_edit_mode()}]
		<button type="button" class="ml-auto btn btn-small" onclick="add_vmkernel_port();">
            <img src="[{$dir_images}]axialis/basic/symbol-add.svg" /><span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_ADD"}]</span>
		</button>
	[{/if}]
</h2>
<table class="contentTable p0" style="border-top: none;">
	<tr>
		<td class="p0">
			<p id="vmk_none" class="p5 m5 box-blue" style="display:none">[{isys type="lang" ident="LC__CMDB__CATG__VSWITCH__VMK_NONE"}]</p>

			<table id="vmk_table" class="listing" cellspacing="0">
				<thead>
				<tr>
					<th>[{isys type="lang" ident="LC_UNIVERSAL__NAME"}]</th>
					<th>[{isys type="lang" ident="LC__CATG__IP_ADDRESS"}]</th>
					[{if isset($smarty.post.navMode) && ($smarty.post.navMode == 2 || $smarty.post.navMode == 1)}]
						<th style="width: 20px; text-align:right;">[{isys type="lang" ident="LC__UNIVERSAL__REMOVE"}]</th>
					[{/if}]
				</tr>
				</thead>
				<tbody id="vmk_table_body">
				[{$vmk_data}]
				</tbody>
			</table>
		</td>
	</tr>
</table>

<script type="text/javascript">
    if (scp_count == 0) {
        $('scp_table').hide();
        $('scp_none').show();
    }

    if (vmk_count == 0) {
        $('vmk_table').hide();
        $('vmk_none').show();
    }

    if (pg_count == 0) {
        $('pg_table').hide();
        $('pg_none').show();
    }
</script>

<style>
	#pg_table th:first-of-type,
	#pg_table td:first-of-type,
	#scp_table th:first-of-type,
	#scp_table td:first-of-type,
	#vmk_table th:first-of-type,
	#vmk_table td:first-of-type,
	#pg_table th:nth-child(2),
	#pg_table td:nth-child(2),
	#scp_table th:nth-child(2),
	#scp_table td:nth-child(2),
	#vmk_table th:nth-child(2),
	#vmk_table td:nth-child(2) {
		width: 30%;
	}
</style>
