[{if is_array($cmdb_status)}]
<h2 class="ml20 mt20 mb10">
    <span class="vam">CMDB-Status Filter</span>
</h2>

<div id="status" class="ml20 mr20">
	<select name="cmdb_status[0]" class="input input-block mb5">
		[{foreach from=$cmdb_status item=s key=k}]
		<option [{if $smarty.session.cmdb_status.0 eq $k}]selected="selected" [{/if}]value=[{$k}]>[{$s}]</option>
		[{/foreach}]
	</select>

	<select name="cmdb_status[1]" class="input input-block mb5">
		[{foreach from=$cmdb_status item=s key=k}]
		<option [{if $smarty.session.cmdb_status.1 eq $k}]selected="selected" [{/if}]value=[{$k}]>[{$s}]</option>
		[{/foreach}]
	</select>

	<select name="cmdb_status[2]" class="input input-block mb5">
		[{foreach from=$cmdb_status item=s key=k}]
		<option [{if $smarty.session.cmdb_status.2 eq $k}]selected="selected" [{/if}]value=[{$k}]>[{$s}]</option>
		[{/foreach}]
	</select>

	<button type="button" class="btn">
		<img src="[{$dir_images}]axialis/imaging/filter.svg" alt="" /><span>[{isys type="lang" ident="LC_UNIVERSAL__FILTER"}]</span>
	</button>
</div>

<script type="text/javascript">
	/*
	 * This is a Workaround for wrong connection between status and cmdb_status in sql-queries.
	 * To prevent this we allow only Template as single status or the others in combination.
	 */
	var $status = $('status'),
		$statusSelect = $status.select('select');

	$statusSelect.invoke('on', 'change', function () {
		if ($statusSelect.invoke('getValue').in_array('[{$smarty.const.C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE}]')) {
			$statusSelect.invoke('disable');

			$statusSelect.each(function ($el) {
				if ($el.getValue() == '[{$smarty.const.C__CMDB_STATUS__IDOIT_STATUS_TEMPLATE}]') {
					$el.enable();
				}
			});
		} else {
			$statusSelect.invoke('enable');
		}
	});

	$statusSelect.invoke('simulate', 'change');

	$status.down('button').on('click', function () {
		$('isys_form').writeAttribute('action', '[{$www_dir}]').submit();
	});
</script>
[{/if}]
