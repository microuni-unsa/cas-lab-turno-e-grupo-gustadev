<table class="contentTable">
    <tr>
        <td class="key vat">[{isys type="f_label" name="C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS__VIEW" ident="LC__CMDB__CATG__MAINTENANCE_OBJ_MAINTENANCE"}]</td>
        <td class="value">
            [{isys
                title="LC__BROWSER__TITLE__CONTRACTS"
                name="C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS"
                type="f_popup"
                p_strPopupType="browser_object_ng"
                callback_accept="idoit.callbackManager.triggerCallback('contract_assignment__get_contract');"}]
        </td>
    </tr>
    <tr>
        <td colspan="2" class="p20">
            <table id="contract_information_table" class="w100 border">
                [{if (isset($contract_information))}]
                [{foreach $contract_information as $title => $row}]
                <tr>
                    <td class="key">[{isys type="lang" ident=$title}]</td>
                    <td class="value pl20">
                        [{if strstr($row, 'costs') || strstr($row, 'sum')}]
                            [{isys type="f_money_number" p_strValue=$contract[$row] p_bEditMode=0 p_bInfoIconSpacer=0}]
                        [{elseif $title == 'LC__CMDB__CATS__CONTRACT__NOTICE_VALUE' || $title == 'LC__CMDB__CATS__CONTRACT__MAINTENANCE_PERIOD'}]
                            [{$row}]
                        [{else}]
                            [{isys type="lang" ident=$contract[$row]}]
                        [{/if}]
                    </td>
                </tr>
                [{/foreach}]
                [{/if}]
            </table>

            [{if (isset($contract_information))}]
            <input type="hidden" id="assigned_contract__startdate" data-view="[{isys_application::instance()->container->get('locales')->fmt_date($contract['isys_cats_contract_list__start_date'])}]" value="[{$contract['isys_cats_contract_list__start_date']}]" />
            <input type="hidden" id="assigned_contract__enddate" data-view="[{isys_application::instance()->container->get('locales')->fmt_date($contract['isys_cats_contract_list__end_date'])}]" value="[{$contract['isys_cats_contract_list__end_date']}]" />
            <input type="hidden" id="reaction_rate" value="[{$contract['isys_cats_contract_list__isys_contract_reaction_rate__id']}]" />
            [{/if}]
        </td>
    </tr>
    <tr>
        <td class="key">
            <label for="subcontract" class="fr">
                <input class="m5" type="checkbox" id="subcontract" name="subcontract" value="1" onClick="idoit.callbackManager.triggerCallback('contract_assignment__handle_subcontract', this);" [{if !$smarty.get.editMode && $smarty.post.navMode != $smarty.const.C__NAVMODE__EDIT}]disabled="disabled"[{/if}] [{if ($subcontract)}]checked="checked"[{/if}] />
                <span class="m5">[{isys type="lang" ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__ACHIEVEMENT_CERTIFICATE"}]</span>
            </label>
        </td>
    </tr>
</table>

<table class="contentTable mt5 [{if !($subcontract)}]hide[{/if}]" id="subcontract_table">
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__VIEW'  ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START"}]</td>
        <td class="value">[{isys type="f_popup" name="C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START" p_strPopupType="calendar"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__VIEW' ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END"}]</td>
        <td class="value">[{isys type="f_popup" name="C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END" p_strPopupType="calendar"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__CONTRACT_ASSIGNMENT__MAINTENANCE_PERIOD' ident="LC__CMDB__CATS__CONTRACT__MAINTENANCE_END"}]</td>
        <td class="value">[{isys type="f_data" name="C__CATG__CONTRACT_ASSIGNMENT__MAINTENANCE_PERIOD"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" ident="LC__CMDB__CATS__CONTRACT__REACTION_RATE" name="C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE"}]</td>
        <td class="value">[{isys type="f_popup" p_strPopupType="dialog_plus" p_strTable="isys_contract_reaction_rate" name="C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE"}]</td>
    </tr>
</table>

<script type='text/javascript'>
    (function () {
        'use strict';

        const assignSubcontract = function () {
            const $startDate = $('assigned_contract__startdate');
            const $endDate = $('assigned_contract__enddate');
            const $reactionRate = $('reaction_rate');
            const $subContract = $('subcontract');

            if (!$startDate || !$endDate || !$reactionRate) {
                idoit.Notify.info('[{isys type="lang" ident="LC__CMDB__CATG__CONTRACT_ASSIGNMENT__SELECT_CONTRACT"}]');

                if ($subContract.checked) {
                    $subContract.setValue(0);
                }

                return false;
            }

            var startHidden       = $startDate.getValue(),
                startView         = $startDate.getAttribute('data-view'),
                l_end_hidden      = $endDate.getValue(),
                endView           = $endDate.getAttribute('data-view'),
                l_reaction_rate   = $reactionRate.getValue(),

                startFieldView    = $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__VIEW'),
                startFieldHidden  = $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN'),
                endFieldView      = $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__VIEW'),
                endFieldHidden    = $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN'),
                reactionRateField = $('C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE');

            if (startView !== '01.01.1970' && $subContract.checked) {
                if (startFieldHidden.getValue().blank()) {
                    startFieldView.setValue(startView);
                    startFieldHidden.setValue(startHidden);
                }
            } else {
                startFieldView.setValue('');
                startFieldHidden.setValue('');
            }

            if (endView !== '01.01.1970' && $subContract.checked) {
                if (endFieldHidden.getValue().blank()) {
                    endFieldView.setValue(endView);
                    endFieldHidden.setValue(l_end_hidden);
                }
            } else {
                endFieldView.setValue('');
                endFieldHidden.setValue('');
            }

            if (reactionRateField.getValue() < 0 && l_reaction_rate > 0 && $subContract.checked) {
                reactionRateField.setValue(l_reaction_rate);
            }

            return true;
        };

        idoit.callbackManager
            .registerCallback('contract_assignment__get_contract', function () {
                var l_contractID = $F('C__CATG__CONTRACT_ASSIGNMENT__CONNECTED_CONTRACTS__HIDDEN');

                if (l_contractID > 0) {
                    new Ajax.Request('?ajax=1&call=contract', {
                        method:     'post',
                        parameters: {
                            contractID: l_contractID
                        },
                        onSuccess:  function (transport) {
                            $('contract_information_table').update(transport.responseText);
                            assignSubcontract();
                        }
                    });
                }
            })
            .registerCallback('contract_assignment__handle_subcontract', function ($checkbox) {
                if ($checkbox.checked && !$checkbox.disabled) {
                    if (assignSubcontract()) {
                        $('subcontract_table').removeClassName('hide');
                    }
                } else {
                    $('subcontract_table').addClassName('hide');
                    $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__VIEW').setValue('');
                    $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_START__HIDDEN').setValue('');
                    $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__VIEW').setValue('');
                    $('C__CATG__CONTRACT_ASSIGNMENT__CONTRACT_END__HIDDEN').setValue('');
                    $('C__CATG__CONTRACT_ASSIGNMENT__REACTION_RATE').setValue('-1');
                }
            });

        [{if $smarty.get.editMode || $smarty.post.navMode == $smarty.const.C__NAVMODE__EDIT}]
        idoit.callbackManager.triggerCallback('contract_assignment__handle_subcontract', $('subcontract'));
        [{/if}]
    }());
</script>

<style>
    #contract_information_table {
        background-color: rgba(0, 0, 0, .05);
    }

    #contract_information_table .key {
        width: 185px;
    }
</style>
