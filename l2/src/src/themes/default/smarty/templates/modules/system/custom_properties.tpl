<div class="m5 p5 text-blue display-flex align-items-center">
	<img src="[{$dir_images}]axialis/basic/button-info.svg" class="mr5" /><span>[{isys type="lang" ident="LC__CUSTOM_PROPERTIES__HELP" values=array($i)}]</span>
</div>

[{if is_array($data)}]
	[{foreach from=$data item=item key=key}]
		<fieldset class="overview">
			<legend><span>[{$item.title}]</span></legend>

			<table class="contentTable">
				<colgroup>
					<col style="width:100px" />
					<col style="width:47%" />
				</colgroup>
				<tbody>
				[{foreach from=$item.data item=custom_property_data key=custom_field_name}]
					[{counter assign="i" print=false}]
					<tr>
						<td class="key">
							[{isys type="lang" ident="LC__CUSTOM_PROPERTIES__FIELD_X" values=array($i)}]
						</td>
						<td class="value">
							[{isys type="f_text" name="data[$key][$custom_field_name]" p_strPlaceholder="LC__CUSTOM_PROPERTIES__NAME" p_strValue=$custom_property_data.info.title p_bNoTranslation=true}]
						</td>
						<td>
							[{isys type="lang" ident="LC__CUSTOM_PROPERTIES__KEY"}]: <strong>[{$custom_field_name}]</strong>
						</td>
					</tr>
				[{/foreach}]
				</tbody>
			</table>
		</fieldset>
	[{/foreach}]
[{/if}]

<div id="ajax_return" class="m5 p5" style="display:none;"></div>
