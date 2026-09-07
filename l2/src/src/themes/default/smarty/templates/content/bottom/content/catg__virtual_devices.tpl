[{if !$device_type}]
<div id="selection">
	<h3 class="p5 gradient border-bottom">[{isys type="lang" ident="LC__CMDB__CATG__VD__CHOOSE_VIRTUAL_HW"}]</h3>

	<table class="contentTable">
		<tr>
			<td>
				<button id="button_network" class="ml5 btn" type="button" data-show-id="network" data-type-value="[{$smarty.const.C__VIRTUAL_DEVICE__NETWORK}]">
					<img src="[{$dir_images}]icons/network-workgroup.png" alt="" class="mr5"/><strong>[{isys type="lang" ident="LC__CMDB__CATG__NETWORK"}]</strong>
				</button>

				<button id="button_storage" class="ml5 btn" type="button" data-show-id="storage" data-type-value="[{$smarty.const.C__VIRTUAL_DEVICE__STORAGE}]">
					<img src="[{$dir_images}]icons/drive-removable-media.png" alt="" class="mr5"/><strong>[{isys type="lang" ident="LC__CATG__STORAGE"}]</strong>
				</button>

				<button id="button_interface" class="ml5 btn" type="button" data-show-id="interface" data-type-value="[{$smarty.const.C__VIRTUAL_DEVICE__INTERFACE}]">
					<img src="[{$dir_images}]icons/desktop-peripherals.png" alt="" class="mr5"/><strong>[{isys type="lang" ident="LC__CMDB__CATG__UNIVERSAL_INTERFACE"}]</strong>
				</button>
			</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
	document.observe('dom:loaded', function() {
		$('scroller').down('.commentaryTable').addClassName('hide');
	});

    $('button_network', 'button_storage', 'button_interface').invoke('on', 'click', function () {
        $(this.readAttribute('data-show-id')).removeClassName('hide');
        $('device_type').setValue(this.readAttribute('data-type-value'));

        $('selection').addClassName('hide');
        $('scroller').down('.commentaryTable').removeClassName('hide');
    });
</script>
[{/if}]

<input type="hidden" name="device_type" id="device_type" value="[{$device_type|default:0}]" />

<div id="network" class="hide">
	<h3 class="p5">[{isys type="lang" ident="LC__CMDB__CATG__NETWORK"}]</h3>

	<h4 class="p5 gradient border-bottom border-top">[{isys type="lang" ident="LC__CMDB__CATG__VD__LOCAL_DEVICE"}]</h4>
	<table class="contentTable" style="border-top: none; border-bottom: none;">
		<tr>
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__LOCAL_NETWORK_PORT' ident="LC__CMDB__CATG__VD__LOCAL_NETWORK_PORT"}]</td>
			<td class="value">[{isys type="f_dialog" id="C__CMDB__CATG__VD__LOCAL_NETWORK_PORT" name="C__CMDB__CATG__VD__LOCAL_NETWORK_PORT"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__NETWORK_TYPE' ident="LC__CMDB__CATG__VD__TYPE"}]</td>
			<td class="value">[{isys type="f_dialog" id="C__CMDB__CATG__VD__NETWORK_TYPE" name="C__CMDB__CATG__VD__NETWORK_TYPE" p_strTable="isys_virtual_network_type"}]</td>
		</tr>
	</table>

	<h4 class="p5 gradient border-bottom border-top">[{isys type="lang" ident="LC__CMDB__CATG__VD__HOST_RESOURCE"}]</h4>
	<table class="contentTable" style="border-top: none;">
		<tr id="network_port">
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__HOST_NETWORK_PORT' ident="LC__CMDB__CATG__VD__HOST_NETWORK_PORT"}]</td>
			<td class="value">[{isys type="f_dialog" id="C__CMDB__CATG__VD__HOST_NETWORK_PORT" name="C__CMDB__CATG__VD__HOST_NETWORK_PORT"}]</td>
		</tr>
		<tr>
			<td colspan="2">
				[{$device_type2}]
			</td>
		</tr>
		<tr id="network_port_group">
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__SWITCH_PORT_GROUP' ident="LC__CMDB__CATG__VD__SWITCH_PORT_GROUP"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__VD__SWITCH_PORT_GROUP"}]</td>
		</tr>
	</table>
</div>

<div id="storage" class="hide">
	<h3 class="p5">[{isys type="lang" ident="LC__CATG__STORAGE"}]</h3>

	<h4 class="p5 gradient border-bottom border-top">[{isys type="lang" ident="LC__CMDB__CATG__VD__LOCAL_DEVICE"}]</h4>
	<table class="contentTable" style="border-top: none; border-bottom: none;">
		<tr>
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__LOCAL_STORAGE' ident="LC__CMDB__CATG__VD__LOCAL_STORAGE"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__VD__LOCAL_STORAGE"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__STORAGE_TYPE' ident="LC__CMDB__CATG__VD__TYPE"}]</td>
			<td class="value">[{isys type="f_dialog" p_strTable="isys_virtual_storage_type" name="C__CMDB__CATG__VD__STORAGE_TYPE"}]</td>
		</tr>
		<tr>
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__DISK_IMAGE_LOCATION' ident="LC__CMDB__CATG__VD__DISK_IMAGE_LOCATION"}]</td>
			<td class="value">[{isys type="f_text" name="C__CMDB__CATG__VD__DISK_IMAGE_LOCATION"}]</td>
		</tr>
	</table>

	<h4 class="p5 gradient border-bottom border-top">[{isys type="lang" ident="LC__CMDB__CATG__VD__HOST_RESOURCE"}]</h4>
	<table class="contentTable" style="border-top: none;">
		<tr>
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__HOST_STORAGE' ident="LC__CMDB__CATG__VD__HOST_STORAGE"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__VD__HOST_STORAGE"}]</td>
		</tr>
	</table>
</div>

<div id="interface" class="hide">
	<h3 class="p5">[{isys type="lang" ident="LC__CMDB__CATG__UNIVERSAL_INTERFACE"}]</h3>

	<h4 class="p5 gradient border-bottom border-top">[{isys type="lang" ident="LC__CMDB__CATG__VD__LOCAL_DEVICE"}]</h4>
	<table class="contentTable" style="border-top: none; border-bottom: none;">
		<tr>
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__LOCAL_INTERFACE' ident="LC__CMDB__CATG__VD__LOCAL_INTERFACE"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__VD__LOCAL_INTERFACE"}]</td>
		</tr>
	</table>

	<h4 class="p5 gradient border-bottom border-top">[{isys type="lang" ident="LC__CMDB__CATG__VD__HOST_RESOURCE"}]</h4>
	<table class="contentTable" style="border-top: none;">
		<tr>
			<td class="key">[{isys type='f_label' name='C__CMDB__CATG__VD__HOST_INTERFACE' ident="LC__CMDB__CATG__VD__HOST_INTERFACE"}]</td>
			<td class="value">[{isys type="f_dialog" name="C__CMDB__CATG__VD__HOST_INTERFACE"}]</td>
		</tr>
	</table>
</div>

<script type="text/javascript">
    const $netType = $('C__CMDB__CATG__VD__NETWORK_TYPE');
	var net_port_group = $('network_port_group'),
		net_port = $('network_port');

	if ($netType) {
        $netType.on('change', function () {
			if (this.selectedIndex == this.options.length - 1) {
				net_port_group.show();
				net_port.hide();
				new Effect.Highlight('C__CMDB__CATG__VD__SWITCH_PORT_GROUP', {startcolor:'#ddffdd', restorecolor: '#fbfbfb'});
			} else {
				net_port.show();
				net_port_group.hide();
				new Effect.Highlight('C__CMDB__CATG__VD__HOST_NETWORK_PORT', {startcolor:'#ddffdd', restorecolor: '#fbfbfb'});
			}
		});

		// This is very very evil... We should check for a value here.
		if ($netType.selectedIndex == $netType.options.length - 1) {
			net_port_group.show();
			net_port.hide();
		} else {
			net_port.show();
			net_port_group.hide();
		}
	} else {
		if ('[{$static_device_type}]' == '4') {
			net_port_group.show();
			net_port.hide();
		} else {
			net_port_group.hide();
			net_port.show();
		}
	}

	if ($netType) {
		$netType.simulate('change');
	}

	[{if $device_type == $smarty.const.C__VIRTUAL_DEVICE__NETWORK}]
	$('network').removeClassName('hide');
	[{elseif $device_type == $smarty.const.C__VIRTUAL_DEVICE__STORAGE}]
	$('storage').removeClassName('hide');
	[{elseif $device_type == $smarty.const.C__VIRTUAL_DEVICE__INTERFACE}]
	$('interface').removeClassName('hide');
	[{/if}]
</script>
