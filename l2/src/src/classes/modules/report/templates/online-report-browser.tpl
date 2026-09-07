<div id="reportBrowser" style="display:none; width:100%;">
    <table class="mainTable mouse-pointer" style="white-space: normal;">
        <colgroup>
            <col>
            <col>
            <col style="min-width: 150px">
        </colgroup>
        <thead>
        <tr>
            <th>[{isys type="lang" ident="LC__UNIVERSAL__TITLE"}]</th>
            <th>[{isys type="lang" ident="LC__CMDB__CATG__DESCRIPTION"}]</th>
            <th>[{isys type="lang" ident="LC__REPORT__ACTION"}]</th>
        </tr>
        </thead>
        <tbody id="reports">

        </tbody>
    </table>
</div>

<script type="text/javascript">
    [{include file="`$report_assets_dir`js/report.js"}]

    // @see ID-10031 Update the ajax request to a GET and only pass the version.
    new Ajax.Request('[{$config.www_dir}]proxy.php?version=[{$gProductInfo.version}]', {
        method:     'GET',
        onSuccess:  function (transport) {
            const $report_table = $('reports');
            const reports = transport.responseJSON;

            if (!reports) {
                alert(transport.responseText.stripTags());
                return;
            }

            for (var i = 0; i < reports.length; i++) {
                $report_table
                    .insert(new Element('tr', { 'data-index': i })
                        .update(new Element('td').update(reports[i].isys_report__title))
                        .insert(new Element('td').update(reports[i].isys_report__description))
                        .insert(new Element('td', { className: 'py5' })
                            .update(new Element('button', { type: 'button', className: 'btn mx5', title: '[{isys type="lang" ident="LC__REPORT__POPUP__REPORT_PREVIEW"}]', 'data-tooltip': 1, 'data-preview': 1 })
                                .update(new Element('img', { src:'[{$dir_images}]axialis/basic/zoom.svg' })))
                            .insert(new Element('button', { type: 'button', className: 'btn mx5', title: '[{isys type="lang" ident="LC__REPORT__DOWNLOAD"}]', 'data-tooltip': 1, 'data-download': 1 })
                                .update(new Element('img', { src:'[{$dir_images}]axialis/basic/symbol-download.svg' })))));
            }

            $report_table.on('click', 'button[data-preview]', function (ev) {
                const index = ev.findElement('tr').readAttribute('data-index');

                get_popup('report', '', 800, 508, { func: 'report_preview_sql', query: reports[index].isys_report__query });
            });

            $report_table.on('click', 'button[data-download]', function (ev) {
                const index = ev.findElement('tr').readAttribute('data-index');

                new Ajax.Updater(
                    'infoBox',
                    '?ajax=1&[{$smarty.const.C__GET__MODULE_ID}]=[{$smarty.const.C__MODULE__REPORT}]&request=[{isys_report_dao::AJAX_REPORT_DOWNLOAD}]',
                    {
                        method:     'POST',
                        parameters: {
                            reportID: reports[index].isys_report__id,
                            title:    reports[index].isys_report__title,
                            desc:     reports[index].isys_report__description,
                            query:    reports[index].isys_report__query
                        },
                        onSuccess:  function () {
                            $('reports').down('tr[data-index="' + index + '"]').highlight();
                        }
                    }
                );
            });

            Effect.Appear('reportBrowser', {duration: 0.25});

            $('body').fire('update:tooltips');
        }
    });
</script>
