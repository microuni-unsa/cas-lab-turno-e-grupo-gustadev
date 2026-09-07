<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" name="C__CATG__AP_CONTROLLER__ASSIGNED_OBJECT" ident="LC__CMDB__CATG__AP_CONTROLLER__ASSIGNED_OBJECT"}]</td>
        <td class="value">
            [{isys
            name="C__CATG__AP_CONTROLLER__ASSIGNED_OBJECT"
            type="f_popup"
            p_strPopupType="browser_object_ng"
            catFilter="C__CATS__ACCESS_POINT"
            callback_accept="$('C__CATG__AP_CONTROLLER__ASSIGNED_OBJECT__HIDDEN').fire('apcSelection:updated');"
            callback_detach="$('C__CATG__AP_CONTROLLER__ASSIGNED_OBJECT__HIDDEN').fire('apcSelection:removed');"}]
        </td>
    </tr>
</table>
<script type="text/javascript">
(function () {

"use strict";

const assignedObject = $('C__CATG__AP_CONTROLLER__ASSIGNED_OBJECT__HIDDEN');

if (assignedObject) {
    assignedObject.on('apcSelection:updated', function(){
        const objectSelection = assignedObject.getValue();

        new Ajax.Request('[{$ap_controller_ajax_url}]',
        {
            parameters: {
                rmc_object: objectSelection
            },
            method: "post"
        });
    })
}

}());
</script>
