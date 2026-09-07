
<div id="catg-custom-[{$catg_custom_id}]" class="pt5 pb5 custom-category-content">
    <input type="hidden" name="catg_custom_id" value="[{$catg_custom_id}]" />

    [{foreach $fields as $key => $field}]

    <div class="c-field-entry cb" [{if $field.displayInTable}]data-in-table="true"[{/if}]>
        [{if $field.displayInTable}]
        <table class="contentTable p0">
            <tr [{if ($field.visibility == 'hidden')}]class="hide"[{/if}]>
                <td class="key vat">
                    <div class="c-field-label">[{isys type='f_label' name="C__CATG__CUSTOM__`$key`" ident=$field.title|escape:"html"}]</div>
                </td>
                <td class="value" [{if $field.show_inline}]data-inline="true"[{/if}] id="field-[{$key}]">
                    <div class="c-field-value">[{include file=$field.template}]</div>
                </td>
            </tr>
        </table>
        [{else}]
            [{if $field.popup eq "report_browser" && $report_data.$key.listing}]
                [{include
                    file="src/classes/modules/report/templates/custom_fields/report.tpl"
                    rowcount=$report_data.$key.rowcount
                    listing=$report_data.$key.listing
                    result=$report_data.$key.result
                    reportTitle = $report_data.$key.reportTitle
                    trClickActive=$report_data.$key.trClickActive
                    report_id=$report_data.$key.report_id
                    querybuilder=$report_data.$key.querybuilder
                    reportDescription=$report_data.$key.reportDescription
                    showReportExport=$report_data.$key.showReportExport
                    objectId=$report_data.$key.objectId
                }]
                [{if isset($reportExecutionFailed)}]
                    <p class="box-red m10 p10">
                        [{isys type="lang" ident="LC__MODULE__CUSTOM_FIELDS__REPORT_EXECUTION_FAULTY"}]
                    </p>
                [{/if}]
            [{else}]
                [{if file_exists($field.template)}]
                    [{include file=$field.template}]
                [{else}]
                    <p class="box-red p5 m5">The template for the attribute "[{$field.title|escape:"html"}]" could not be found.</p>
                    <!-- [{$field|json_encode}] -->
                [{/if}]
            [{/if}]
        [{/if}]
    </div>
    [{/foreach}]
</div>

<style type="text/css">
    [data-contains-inline] {
        clear: both;
    }

    [data-contains-inline] > div {
        float: left;
    }

    .c-field-label {
        display: inline;
    }

    [{if $layout === top}]
    .c-field-label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .c-field-entry[data-in-table] {
        padding-left: 205px;
    }

    .custom-category-content > div {
        margin-bottom:10px;
    }

    .custom-category-content > div > div.mini {
        width: 155px;
    }

    .custom-category-content > div > div.small {
        width: 310px;
    }

    .custom-category-content .key {
        margin-left: 20px;
    }
    [{/if}]
</style>

<script type="text/javascript">
    (function () {
        'use strict';

        idoit.Require.require('fileUploader');

        const $customCategoryContainer = $('catg-custom-[{$catg_custom_id}]');

        try {
            if ('[{$layout|default:"left"}]' === '[{isys_module_custom_fields::LABEL_POSITION_TOP}]') {
                processTopLayout();
            } else {
                processLeftLayout();
            }
        } catch (e) {
            idoit.Notify.warning('A problem occured while re-arranging the fields: ' + e);
        }

        function processLeftLayout() {
            var $currentTable,
                $currentTD,
                $currentItem,
                $currentLabel,
                $targetTable;

            while ($currentTD = $customCategoryContainer.down('[data-inline]')) {
                // Remove the attribute to prevent multiple iterations.
                $currentTD.writeAttribute('data-inline', null);

                $currentItem = $currentTD.down('.c-field-value');

                // If there is no immediate DIV, cancel.
                if (!$currentItem) {
                    continue;
                }

                $currentTable = $currentTD.up('.c-field-entry');

                // We can't use a 'label' selector here because it won't exist in view mode.
                $currentLabel = $currentTable.down('td .c-field-label');

                $targetTable = $currentTable.previous('.c-field-entry').down('table');

                // Append the current label to the target label (if it contains something)
                if (!$currentLabel.innerText.blank()) {
                    $targetTable.down('td').insert(' / ').insert($currentLabel);
                }

                // Write the 'data-contains-inline' attribute and move the current item here.
                $targetTable.down('td', 1).writeAttribute('data-contains-inline', 'yup').insert($currentItem);

                $currentTable.remove();
            }

            // Now we make the "inline items" fit nicely:
            $customCategoryContainer
                .select('[data-contains-inline]')
                .each(function($container) {
                    const chilrenCount = $container.select('> div').length;

                    $container
                        .select('.input-size-normal')
                        .invoke('removeClassName', 'input-size-normal')
                        .invoke('addClassName', chilrenCount >= 3 ? 'input-size-mini' : 'input-size-small');
                });
        }

        $customCategoryContainer.on('click', 'tr.mouse-pointer', function (ev) {
            const $input = ev.findElement('.c-field-value').down('.input')
            const placeholder = ev.findElement('tr').down('code').innerText;

            $input.setValue($input.getValue() + placeholder);
        });

        function processTopLayout() {
            var $currentTable,
                $currentFormfield,
                putInPreviousLine,
                $previous;

            while ($currentTable = $customCategoryContainer.down('table.contentTable')) {
                // We can't use a 'label' selector here because it won't exist in view mode.
                $currentFormfield = $currentTable.down('td', 1);
                putInPreviousLine = $currentFormfield.hasAttribute('data-inline');

                const $newElement = new Element('div', {className:'fl'})
                    .update(new Element('div', {className:'key'}).update($currentTable.down('td').innerHTML))
                    .insert(new Element('div', {className:'value'}).update($currentFormfield.down('.c-field-value')));

                if (putInPreviousLine && $previous) {
                    $previous.insert($newElement);
                } else {
                    $previous = $currentTable.up('.c-field-entry').insert($newElement);
                }

                $currentTable.remove();
            }

            // Now we make the "inline items" fit nicely:
            $customCategoryContainer
                .select('.c-field-entry')
                .each(function($container) {
                    const chilrenCount = $container.select('> div').length;

                    // Remove all entry DIVs with no content.
                    if (chilrenCount === 0) {
                        $container.remove();
                        return;
                    }

                    const cssSuffix = (chilrenCount === 1 ? 'normal' : (chilrenCount === 2 ? 'small' : 'mini'));

                    if (chilrenCount > 1) {
                        $container
                            .select(' > div')
                            .invoke('addClassName', cssSuffix);
                    }

                    $container
                        .select('.input-size-normal')
                        .invoke('removeClassName', 'input-size-normal')
                        .invoke('addClassName', 'input-size-' + cssSuffix);

                    $container
                        .insert(new Element('br', {className:'cb', style:'font-size:0; line-height:0'}));
                });
        }
    })();
</script>
