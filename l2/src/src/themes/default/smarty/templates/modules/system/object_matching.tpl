<script type="text/javascript">
    'use strict';

    var updateMinimumMatch = function (matchings, minimumMatch) {
        var $minimumMatchSelect  = $(minimumMatch),
            minimumMatchOldValue = $minimumMatchSelect.getValue(),
            minimumMatchLength   = $F(matchings + '__selected_values').split(',').length,
            i;

        if (minimumMatchLength > 0) {
            if (matchings == 'C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER') {
                $('filterInfo').toggleClassName('hide', false);
            }

            $minimumMatchSelect.update('');

            for (i = 1; i <= minimumMatchLength; i++) {
                $minimumMatchSelect.insert(new Element('option', {value: i}).update(i));
            }

            $minimumMatchSelect.setValue(minimumMatchOldValue);
        }
    };
</script>

<input type="hidden" id="profileID" name="profileID" value="[{$profileID}]">

<p class="sys-message sys-message_info [{if !$filterSelected}]hide[{/if}]" id="filterInfo">
    [{isys type="lang" p_bHtmlEncode=false ident="LC__MODULE__SYSTEM__OBJECT_MATCHING__FILTER__INFO"}]
</p>

<table class="contentTable">
    <tr>
        <td class="key">[{isys type="f_label" name="C__MODULE__SYSTEM__OBJECT_MATCHING__TITLE" ident="LC__MODULE__SYSTEM__OBJECT_MATCHING__TITLE"}]</td>
        <td class="value">[{isys type="f_text" name="C__MODULE__SYSTEM__OBJECT_MATCHING__TITLE"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS" ident="LC__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS"}]</td>
        <td class="value">[{isys type="f_dialog_list"  name="C__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS" p_bits=true add_callback="updateMinimumMatch('C__MODULE__SYSTEM__OBJECT_MATCHING__MATCHINGS', 'C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH');"}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type="f_label" name="C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH" ident="LC__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH"}]</td>
        <td class="value">[{isys type="f_dialog" name="C__MODULE__SYSTEM__OBJECT_MATCHING__MINIMUM_MATCH" p_strStyle="width:80px;" p_bDbFieldNN="1"}]</td>
    </tr>
    <tr>
        <td class="key">
            [{isys type="f_label" name="C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER" ident="LC__MODULE__SYSTEM__OBJECT_MATCHING__FILTER" description="LC__MODULE__SYSTEM__OBJECT_MATCHING__FILTER__DESCRIPTION"}]
        </td>
        <td class="value">[{isys type="f_dialog_list"  name="C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER" p_bits=true add_callback="updateMinimumMatch('C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER', 'C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM');"}]</td>
    </tr>
    <tr>
        <td class="key">
            [{isys type="f_label" name="C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM" ident="LC__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM" description="LC__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM__DESCRIPTION"}]
        </td>
        <td class="value">[{isys type="f_dialog" name="C__MODULE__SYSTEM__OBJECT_MATCHING__FILTER_MINIMUM" p_strStyle="width:80px;" p_bDbFieldNN="1"}]</td>
    </tr>
</table>
