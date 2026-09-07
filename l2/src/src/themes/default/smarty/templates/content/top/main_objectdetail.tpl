[{assign var=contentTop value=$smarty.session.viewMode.contentTop}]

<div id="aj_result"></div>

<div id="contentHeader">
    <div>
        <div class="contentHeaderImage">
            [{isys type="object_image"}]
        </div>

        <div class="display-flex" id="contentTopTitle">
            <h1 style="display: flex; align-items: center">
                [{if $content_title != ""}]
                [{$content_title|escape}]: [{isys type="f_data" name="C__CATG__TITLE" p_bInfoIconSpacer="0"}]
                [{else}]
                [{isys type="f_data" name="C__CATG__TITLE" p_bInfoIconSpacer="0"}] [{if $categoryTitle}]([{$categoryTitle|escape}])[{else}]-[{/if}]
                [{/if}]

                [{if isset($g_locked)}]<span class="red">- LOCKED ([{$lock_user}]) -</span>[{/if}]
            </h1>
        </div>

        <button id="contentHeaderTableToggle" type="button" class="btn btn-secondary" title="[{isys type="lang" ident="LC__UNIVERSAL__TOGGLE_VIEW"}]" data-tooltip="1">
            <img src="[{$dir_images}]axialis/user-interface/angle-down-small.svg" alt="" />
        </button>

        [{if isys_application::isPro() && $barcodeVisible}]
            [{if $barcodeType === "qr"}]
                [{if $barcodeLinkType === $smarty.const.C__QRCODE__LINK__IQR}]
                <div class="ml-auto bg-white border">
                    <a href="iqr://[{$object_id}]">
                        <img id="barcode" class="qr-code" src="[{$barcodeUrl}]" alt="QR code" />
                    </a>
                </div>
                [{else}]
                <div class="ml-auto bg-white border">
                    <a href="javascript:" onclick="var w = window.open('[{$barcodePopupUrl|escape}]', 'QR', 'width=640, height=480, dependant=yes, toolbar=no, location=no, status=no, menubar=no'); w.focus();">
                        <img id="barcode" class="qr-code" src="[{$barcodeUrl}]" alt="QR code" />
                    </a>
                </div>
                [{/if}]
            [{else}]
                <div class="ml-auto bg-white text-center border">
                    <a href="javascript:" onclick="var w = window.open('[{$barcodePopupUrl|escape}]', 'Barcode', 'width=640, height=480, dependant=yes, toolbar=no, location=no, status=no, menubar=no'); w.focus();">
                        <img id="barcode" class="barcode" src="[{$barcodeUrl}]" alt="Barcode" />
                        <code class="text-black">[{$barcodeSysId}]</code>
                    </a>
                </div>
            [{/if}]
        [{/if}]
    </div>

    <div id="contentHeaderTable" class="hide">
        <table id="contentHeaderInnerTable" class="mt10 [{if $contentTop == "off"}]hide[{/if}]">
            <tr>
                <td>[{isys type="lang" ident="LC__CMDB__CATG__SYSID" p_bInfoIconSpacer="0"}]</td>
                <td class="text-bold">[{isys type="f_data" name="C__CATG__SYSID" p_bInfoIconSpacer="0"}]</td>
                <td class="pl20">[{isys type="lang" ident="LC__CMDB__CATG__LOCATION"}]</td>
                <td class="text-bold">[{isys type="f_data" name="C__CATG__LOCATION" p_bInfoIconSpacer="0"}]</td>
            </tr>
            <tr>
                <td>[{isys type="lang" ident="LC__CMDB__CATG__PURPOSE"}]</td>
                <td class="text-bold">[{isys type="f_data" name="C__CATG__PURPOSE" p_bInfoIconSpacer="0"}]</td>
                <td class="pl20">[{isys type="lang" ident="LC__CMDB__CATG__CONTACT"}]</td>
                <td class="text-bold">[{isys type="f_data" name="C__CATG__CONTACT" p_bInfoIconSpacer="0"}]</td>
            </tr>
            <tr>
                <td>[{isys type="lang" ident="LC__CMDB__CATG__RELATION"}]</td>
                <td class="text-bold">[{isys type="f_data" name="C__CATG__RELATIONS" p_bInfoIconSpacer="0"}]</td>
                <td class="pl20">[{isys type="lang" ident="LC__OBJECTDETAIL__ACCESS"}]</td>
                <td class="text-bold">[{isys type="f_data" name="C__CATG__ACCESS" p_bInfoIconSpacer="0"}]</td>
            </tr>
        </table>

        [{if isset($index_includes.contenttopobjectdetail)}]
        [{if is_array($index_includes.contenttopobjectdetail)}]
        [{foreach from=$index_includes.contenttopobjectdetail item=template}]
        [{include file=$template}]
        [{/foreach}]
        [{else}]
        [{include file=$index_includes.contenttopobjectdetail}]
        [{/if}]
        [{/if}]
    </div>
</div>

<script type="text/javascript">
    const $headerToggle = $('contentHeaderTableToggle');
    const $headerTable = $('contentHeaderTable');

    if ($headerToggle && $headerTable) {
        $headerToggle.on('click', function () {
            $headerTable.toggleClassName('hide');
            $headerToggle.down('img')
                .writeAttribute('src', window.dir_images + 'axialis/user-interface/angle-' + ($headerTable.hasClassName('hide') ? 'down' : 'up') + '-small.svg');
        });
    }

    [{if $contentTop == "off"}]
    const $barcode = $('barcode');

    if ($barcode) {
        $barcode.hide();
    }
    [{/if}]
</script>
