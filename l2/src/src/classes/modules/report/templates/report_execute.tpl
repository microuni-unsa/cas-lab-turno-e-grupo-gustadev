<input type="hidden" name="report_id" value="[{$report_id}]">
<input type="hidden" name="querybuilder" id="querybuilder" value='[{$querybuilder|escape:"javascript"}]'>

<div class="p10 display-flex align-items-start" id="reportHeader">
	<div>
        <h2 [{if $reportDescription}]class="mb10"[{/if}]>
            <span id="report-title">[{isys type="lang" ident=$reportTitle}]</span>
            <span class="ml10 text-neutral-400 report-matches" data-count="[{$rowcount}]">[[{$rowcount}] [{isys type="lang" ident="LC__REPORT__MATCHES"}]]</span>
        </h2>
        <p>[{isys type="lang" ident=$reportDescription|nl2br}]</p>
    </div>

    <div class="ml-auto export-report [{if $showReportExport === false}]hide[{/if}]">
        [{if $allowedObjectGroup}]
        <button type="button" id="createObjectGroup" class="btn btn-secondary">
            <img src="[{$dir_images}]axialis/basic/symbol-add.svg" alt="" /><span>[{isys type="lang" ident="LC__CMDB__OBJECT_BROWSER__CREATE_NEW_OBJECT_GROUP_FROM_REPORT"}]</span>
        </button>
        [{/if}]
        <button type="button" class="btn btn-secondary ml5 export-btn" data-object-id="[{$objectId}]" data-report-id="[{$report_id}]" data-export-type="txt">
            <img src="[{$dir_images}]axialis/documents-folders/document-format-txt.svg" alt="" /><span>TXT</span>
        </button>
        <button type="button" class="btn btn-secondary ml5 export-btn" data-object-id="[{$objectId}]" data-report-id="[{$report_id}]" data-export-type="csv">
            <img src="[{$dir_images}]axialis/documents-folders/document-format-csv.svg" alt="" /><span>CSV</span>
        </button>
        <button type="button" class="btn btn-secondary ml5 export-btn" data-object-id="[{$objectId}]" data-report-id="[{$report_id}]" data-export-type="xml">
            <img src="[{$dir_images}]axialis/documents-folders/document-format-xml.svg" alt="" /><span>XML</span>
        </button>
        <button type="button" class="btn btn-secondary ml5 export-btn" data-object-id="[{$objectId}]" data-report-id="[{$report_id}]" data-export-type="pdf">
            <img src="[{$dir_images}]axialis/documents-folders/document-type-pdf.svg" alt="" /><span>PDF</span>
        </button>
    </div>
</div>

<script type="text/javascript">
    [{include file="`$report_assets_dir`js/report.js"}]
</script>

[{if is_array($listing.headers)}]
    [{include file="./listing.tpl" reportId=$report_id}]
[{elseif $listing.num eq 0}]
	<div class="p5">
		<p>[{isys type="lang" ident="LC__REPORT__EMPTY_RESULT"}]</p>
	</div>
[{else}]
	<div class="p5">
		<p>[{isys type="lang" ident="LC__REPORT__EXCEPTION_TRIGGERED"}]</p>
	</div>
[{/if}]
