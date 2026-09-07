[{if $inherited_guest_systems}]
    <h2 class="mt10 mb10">[{isys type="lang" ident="LC__CMDB__CATG__GUEST_SYSTEM_INHERITED_BY_CLUSTER"}]</h2>

	<table cellspacing="0" class="mainTable mt10 border-top text-neutral-400" id="mainTableAddition">
		<colgroup>
			<col/>
			<col/>
			<col/>
			<col/>
			<col/>
			<col/>
		</colgroup>
        <thead>
        <tr>
            <th></th>
            <th>[{isys type="lang" ident="LC__CMDB__CATG__GUEST_SYSTEM"}]</th>
            <th>[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]</th>
            <th>[{isys type="lang" ident="LC__CATP__IP__HOSTNAME"}]</th>
            <th>[{isys type="lang" ident="LC__CMDB__CATG__NETWORK__PRIM_IP"}]</th>
            <th>[{isys type="lang" ident="LC__CMDB__CATG__GUEST_SYSTEM_RUNS_ON"}]</th>
        </tr>
        </thead>
		<tbody>
		[{foreach $inherited_guest_systems as $inheritance}]
			<tr class="listRow">
				<td><input type="checkbox" disabled="disabled" class="checkbox"></td>
				<td>[{$inheritance.obj_title}]</td>
				<td>[{$inheritance.obj_type_title}]</td>
				<td>[{$inheritance.hostname}]</td>
				<td>[{$inheritance.ip_address}]</td>
				<td>[{$inheritance.runs_on}]</td>
			</tr>
		[{/foreach}]
		</tbody>
	</table>
	<script type="text/javascript">
		const $subTableCols = $('mainTableAddition').select('col');

        $('scroller').down('.mainListContainer').addClassName('border-bottom');

		// This little script will set the "mainTableAddition" columns to the same width as the ones in main table.
		$('scroller').down('.mainTable-header').select('th').each(function ($th, i) {
			$subTableCols[i].setStyle({width: $th.getWidth() + 'px'});
		});
	</script>
[{/if}]
