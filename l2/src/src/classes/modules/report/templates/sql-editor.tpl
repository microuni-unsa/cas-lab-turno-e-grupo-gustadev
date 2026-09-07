<input type="hidden" id="report_id" name="report_id" value="[{$report_id}]" />

[{if !empty($querybuilder_warning)}]
<div class="p10">
    [{$querybuilder_warning}]
</div>
[{/if}]
<table class="contentTable">
	<tr>
		<td class="key">[{isys type="f_label" ident="LC__REPORT__FORM__TITLE" name="title"}]</td>
		<td class="value">[{isys type="f_text" name="title"}]</td>
	</tr>
    <tr>
        <td class="key vat">[{isys type="f_label" name="report_category" ident="LC_UNIVERSAL__CATEGORY"}]</td>
        <td class="value">[{isys type="f_dialog" p_bDbFieldNN=1 name="report_category" id="report_category" p_bEditMode=1}]</td>
    </tr>
	<tr>
		<td class="key vat">[{isys type="f_label" ident="LC__REPORT__FORM__DESCRIPTION" name="description"}]</td>
		<td class="value">[{isys type="f_textarea" p_nCols="100" p_nRows="5" name="description"}]</td>
	</tr>
    <tr>
        <td class="key vat">[{isys type="f_label" ident="LC__REPORT__FORM__COMPRESSED_MULTIVALUE_RESULTS"}]</td>
        <td class="value">[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=get_smarty_arr_YES_NO() p_bSort=false name="compressed_multivalue_results" p_strClass="input-mini" p_bEditMode=1}]</td>
    </tr>
    <tr>
        <td class="key vat">[{isys type="f_label" ident="LC__REPORT__FORM__SHOW_HTML"}]</td>
        <td class="value">[{isys type="f_dialog" p_bDbFieldNN=1 p_arData=get_smarty_arr_YES_NO() p_bSort=false name="show_html" p_strClass="input-mini" p_bEditMode=1}]</td>
    </tr>
    <tr>
        <td></td>
        <td class="value pl20">
            [{isys type="f_button" icon="images/icons/silk/table_refresh.png" name="preview_button" p_strValue="LC__UNIVERSAL__PREVIEW" p_bInfoIconSpacer="1" p_onClick=""}]
        </td>
    </tr>
	<tr>
		<td class="key vat">[{isys type="f_label" ident="LC__REPORT__FORM__QUERY" name="query"}]</td>
		<td class="value">
			<pre id="editor" data-name="query">[{isys type="f_data" name="query" p_plain=true}]</pre>
			[{isys type="f_textarea" p_nCols="100" p_nRows="15" name="query" id="query" p_bInfoIconSpacer=0 p_strStyle="display:none;"}]
		</td>
	</tr>
</table>

<style type="text/css" media="screen">
    .ace_editor {
        border: 1px solid #aaa;
        margin: 0 0 0 20px;
        height: 450px;
        width: 97%;
    }
</style>

<script type="text/javascript">
    (function () {
        'use strict';

        [{include file="`$report_assets_dir`js/report.js"}]

        idoit.Require.requireQueue(['ace', 'aceLanguageTools'], function () {
            window.ace.require('ace/ext/language_tools');

            const queryEditor = window.ace.edit('editor', {
                theme: 'ace/theme/clouds',
                mode: 'ace/mode/sql',
                minLines: 10,
                maxLines: 45,
                autoScrollEditorIntoView: true,
                enableBasicAutocompletion: true,
                enableSnippets: false,
                enableLiveAutocompletion: true
            });

            queryEditor.session.on('change', function (e) {
                $('query').setValue(queryEditor.session.getValue());
            });
        });

        $('preview_button').on('click', function () {
            get_popup('report', '', 800, 508, {func: 'report_preview_sql'});
        });

        // @see ID-10832 React on 'form:saved' event and set the 'report_id'.
        document.on('form:saved', function (ev) {
            const $reportId = $('report_id');

            if (!$reportId || !ev.memo.response.responseJSON) {
                return;
            }

            $reportId.setValue(ev.memo.response.responseJSON.id);
        });
    })();
</script>
