<input type="hidden" name="querybuilder" value="[{$querybuilder}]" id="querybuilder">
<input type="hidden" name="report_mode" value="" id="report_mode">
<div id="report-list" class="h100">
[{include file="content/bottom/content/object_table_list.tpl"}]
</div>
<script type="text/javascript">
    (() => {
        'use strict';

        const $exportButton = $('navbar_item_export');
        const $purgeButton = $('navbar_item_purge');

        if ($exportButton) {
            $exportButton.on('click', () => {
                const selectedReports = $('report-list').select('.table-body-container input:checked');

                if (selectedReports.length) {
                    document.location.href = '?' + Object.toQueryString({
                        '[{$smarty.const.C__GET__MODULE_ID}]': '[{$smarty.const.C__MODULE__REPORT}]',
                        'exportReportConfig': 1,
                        'reportIds': Object.toJSON(selectedReports.invoke('getValue'))
                    });
                } else {
                    idoit.Notify.error('[{isys type="lang" ident="LC__REPORT__POPUP__REPORT_DUPLICATE__NO_REPORT_SELECTED"}]', { life: 10 });
                }
            });
        }

        // @see ID-10975 Moved 'purge' logic here.
        if ($purgeButton) {
            $purgeButton.on('click', () => {
                const selectedReports = $('report-list').select('.table-body-container input:checked').invoke('getValue');

                if (selectedReports.length === 0) {
                    idoit.Notify.error('[{isys type="lang" ident="LC__REPORT__PURGE_NO_REPORTS_SELECTED"}]', { life: 10 });
                    return;
                }

                new Ajax.Request(idoit.Router.getRoute('report.delete.check'), {
                    parameters: { ids: JSON.stringify(selectedReports) },
                    onComplete: function (xhr) {
                        if (!is_json_response(xhr, true)) {
                            return;
                        }

                        const json = xhr.responseJSON;

                        if (!json.success) {
                            idoit.Notify.error(json.message, { sticky: true });
                            return;
                        }

                        if (!confirm(json.data)) {
                            return;
                        }

                        new Ajax.Request(idoit.Router.getRoute('report.delete'), {
                            parameters: { ids: JSON.stringify(selectedReports) },
                            onComplete: function (xhr) {
                                if (!is_json_response(xhr, true)) {
                                    return;
                                }

                                const json = xhr.responseJSON;

                                if (!json.success) {
                                    idoit.Notify.error(json.message, { sticky: true });
                                    return;
                                }

                                idoit.Notify.success('[{isys type="lang" ident="LC__REPORT__PURGED_CONFIRMATION"}]', { life: 10 });

                                document.location.reload();
                            }
                        });
                    }
                });
            });
        }
    })();
</script>
