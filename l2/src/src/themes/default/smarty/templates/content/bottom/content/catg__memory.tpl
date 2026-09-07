<table class="contentTable">
    [{if $new_catg_memory}]
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__MEMORY_QUANTITY' ident="LC__CMDB_CATG__MEMORY_QUANTITY"}]</td>
        <td class="value">[{isys type="f_count" name="C__CATG__MEMORY_QUANTITY"}]</td>
    </tr>
    [{/if}]
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__MEMORY_TITLE_ID' ident="LC__CMDB_CATG__MEMORY_TITLE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__MEMORY_TITLE_ID" p_strTable="isys_memory_title" p_bDbFieldNN="0" tab="10"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__MEMORY_MANUFACTURER' ident="LC__CMDB_CATG__MEMORY_MANUFACTURER"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__MEMORY_MANUFACTURER" p_strTable="isys_memory_manufacturer" p_bDbFieldNN="0" tab="20"}]</td>
    </tr>
    <tr>
        <td class="category-spacer" colspan="2"><hr /></td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__MEMORY_TYPE' ident="LC__CMDB_CATG__MEMORY_TYPE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" name="C__CATG__MEMORY_TYPE" p_strTable="isys_memory_type" p_bDbFieldNN="0" tab="30"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__MEMORY_CAPACITY' ident="LC__CMDB_CATG__MEMORY_CAPACITY"}]</td>
        <td class="value">
            [{isys type="f_text" name="C__CATG__MEMORY_CAPACITY"}]
            [{isys type="f_dialog" name="C__CATG__MEMORY_UNIT" p_strTable="isys_memory_unit"}]
        </td>
    </tr>
    [{if $new_catg_memory}]
    <tr>
        <td class="category-spacer" colspan="2"><hr /></td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__MEMORY_TOTALCAPACITY' ident="LC__CATG__CMDB_MEMORY_TOTALCAPACITY"}]</td>
        <td class="value">[{isys type="f_text" p_nCols="5" p_bReadonly="1" p_bDisabled="1" name="C__CATG__MEMORY_TOTALCAPACITY"}]</td>
    </tr>
    [{/if}]
</table>

<script type="text/javascript">
    (function () {
        'use strict';

        idoit.callbackManager.registerCallback('memory__calc_capacity', function () {

            const $quantity = $('C__CATG__MEMORY_QUANTITY');
            const $capacity = $('C__CATG__MEMORY_CAPACITY');
            const $total_capacity = $('C__CATG__MEMORY_TOTALCAPACITY');
            let capacity = 0;
            let quantity = 0;

            if ($quantity && $total_capacity) {
                capacity = parseFloat($capacity.getValue());
                quantity = parseFloat($quantity.getValue());

                if (isNaN(capacity) || isNaN(quantity)) {
                    capacity = 0;
                    quantity = 0;
                }

                // @see ID-10455 Use a simple workaround to prevent rounding errors.
				$total_capacity.setValue(parseFloat((capacity * quantity).toPrecision(12)) + ' ' + $('C__CATG__MEMORY_UNIT').down('option:selected').innerHTML);
			}
		});
	}());
</script>
