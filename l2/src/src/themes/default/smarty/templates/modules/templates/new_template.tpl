<table class="contentTable m10">
    <tr>
        <td class="key">[{isys type="lang" ident="LC__CMDB__OBJTYPE"}]</td>
        <td class="value">[{isys type="f_dialog" name="object_type"}]</td>
    </tr>
    <tr>
        <td></td>
        <td class="value">
            <button type="button" class="ml20 btn" id="create_template" disabled="disabled">
                <span>[{isys type="lang" ident="LC__TEMPLATE__CREATE_NEW_TEMPLATE"}]</span>
            </button>
        </td>
    </tr>
</table>

<script type="text/javascript">
	var $objectTypeSelection = $('object_type'),
	    $createTemplateButton = $('create_template');

    $objectTypeSelection.on('change', function () {
        if ($objectTypeSelection.getValue() > 0) {
            $createTemplateButton.enable();
        } else {
            $createTemplateButton.disable();
        }
    });

    $createTemplateButton.on('click', function () {
        var selectedObjectType = $objectTypeSelection.getValue(),
            $template = $('body').down('[name="template"]');

        if ($createTemplateButton.disabled || selectedObjectType <= 0) {
            return false;
        }

        if ($template) {
            $template.setValue('1');
        }

        $('navMode').setValue('[{$smarty.const.C__NAVMODE__NEW}]');

        $('isys_form')
	        .writeAttribute('action', '?[{$smarty.const.C__CMDB__GET__VIEWMODE}]=[{$smarty.const.C__CMDB__VIEW__LIST_OBJECT}]&[{$smarty.const.C__CMDB__GET__OBJECTTYPE}]=' + selectedObjectType)
			.submit();
    });
</script>
