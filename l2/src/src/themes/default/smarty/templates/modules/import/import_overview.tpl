<script type="text/javascript">
    [{include file="modules/import/import_javascript.js"}]
</script>

<div class="p5" style="height: 40px;">
    [{isys type="f_file_ajax" name="import-file-upload" uploadType="import.file-upload"}]
</div>

<hr />

<input type="hidden" name="file" id="selected_file" />
<input type="hidden" name="type" id="type" />
<input type="hidden" name="verbose" id="1" />

<div class="bg-white">
    <ul id="tabs" class="m0 gradient browser-tabs">
        <li><a href="#import_cmdb">i-doit XML</a></li>
        [{if $inventory_import}]<li><a href="#import_inventory">H-Inventory XML</a></li>[{/if}]
    </ul>

    <div id="import_cmdb">
        [{include file="modules/import/import_cmdb.tpl"}]
    </div>

    [{if $inventory_import}]
    <div id="import_inventory">
        [{include file="modules/import/import_inventory.tpl"}]
    </div>
    [{/if}]
</div>

<script type="text/javascript">
    const $uploadContainer = $('import-file-upload');

    if ($uploadContainer) {
        $uploadContainer.on('uploader:onComplete', function (ev) {
            window.location.reload();
        });
    }

    new Tabs('tabs', {
        wrapperClass: 'browser-tabs',
        contentClass: 'browser-tab-content',
        tabClass:     'mouse-pointer'
    });
</script>
