<div id="file-browser">
    <div class="modal-header">
        <h1>[{isys type="lang" ident="LC__POPUP__BROWSER__FILE_TITLE"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img class="fr mouse-pointer" alt="" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

	<div class="modal-content">
		<div class="left-panel">
            <div class="loading-indicator">
                <img src="[{$dir_images}]axialis/user-interface/loading.svg" class="animation-rotate mr5" alt="Loading" />
                <span>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
            </div>

            <div class="search-bar">
                <input type="text" class="input input-block" placeholder="Search" /><img src="[{$dir_images}]axialis/basic/zoom.svg" alt="">
            </div>

            <div class="category-list">
                <ul class="category-list-content">
                    <!-- Will be filled by JS -->
                </ul>
            </div>
		</div>

        <div class="separator"></div>

        <div class="right-panel">
            <div class="loading-indicator">
                <img src="[{$dir_images}]axialis/user-interface/loading.svg" class="animation-rotate mr5" alt="Loading" />
                <span>[{isys type="lang" ident="LC__UNIVERSAL__LOADING"}]</span>
            </div>

            <div>
                <strong>[{isys type="lang" ident="LC__UNIVERSAL__LOAD"}]</strong>
                <button id="new-file-button" type="button" class="ml20 btn">
                    <img src="[{$dir_images}]axialis/basic/symbol-add.svg" alt="" />
                    <span>[{isys type="lang" ident="LC_FILEBROWSER__NEW_FILE"}]</span>
                </button>
            </div>

            <div class="display-flex mt20 mb20">
                <div class="search-bar m0">
                    <input type="text" class="input input-block" placeholder="Search" /><img src="[{$dir_images}]axialis/basic/zoom.svg" alt="">
                </div>

                <!--
                <button type="button" class="btn">
                    <img src="[{$dir_images}]axialis/database/table-column.svg" alt="" />
                    <span>### Columns</span>
                </button>
                -->
            </div>

            <div class="file-table">
                <table class="mainTable w100">
                    <colgroup>
                        <col style="width: 35px" />
                        <col style="width: 20%" />
                        <col style="width: 20%" />
                        <col style="width: 10%" />
                        <col style="width: 10%" />
                        <col style="width: 10%" />
                        <col style="width: auto" />
                    </colgroup>
                    <thead data-table="table-header">
                    <tr>
                        <th></th>
                        <th data-order-field="fileName" data-order-direction="asc">
                            <span class="overflowable">[{isys type="lang" ident="LC__CMDB__CATS__FILE_NAME"}]</span>
                            <img src="[{$dir_images}]axialis/user-interface/arrow-up.svg" class="sort-arrow" alt="" />
                        </th>
                        <th data-order-field="objectTitle" data-order-direction="asc">
                            <span class="overflowable">[{isys type="lang" ident="LC_UNIVERSAL__NAME"}]</span>
                            <img src="[{$dir_images}]axialis/user-interface/arrow-up.svg" class="sort-arrow" alt="" />
                        </th>
                        <th data-order-field="categoryTitle" data-order-direction="asc">
                            <span class="overflowable">[{isys type="lang" ident="LC__CMDB__CATS__FILE_CATEGORY"}]</span>
                            <img src="[{$dir_images}]axialis/user-interface/arrow-up.svg" class="sort-arrow" alt="" />
                        </th>
                        <th data-order-field="uploadUserObjectTitle" data-order-direction="asc">
                            <span class="overflowable">[{isys type="lang" ident="LC__CMDB__CATS__FILE_UPLOAD_FROM"}]</span>
                            <img src="[{$dir_images}]axialis/user-interface/arrow-up.svg" class="sort-arrow" alt="" />
                        </th>
                        <th data-order-field="revision" data-order-direction="asc">
                            <span class="overflowable">[{isys type="lang" ident="LC__CMDB__CATS__FILE_REVISION"}]</span>
                            <img src="[{$dir_images}]axialis/user-interface/arrow-up.svg" class="sort-arrow" alt="" />
                        </th>
                        <th data-order-field="uploadDate" data-order-direction="asc">
                            <span class="overflowable">[{isys type="lang" ident="LC__CMDB__CATS__FILE_UPLOAD_DATE"}]</span>
                            <img src="[{$dir_images}]axialis/user-interface/arrow-up.svg" class="sort-arrow" alt="" />
                        </th>
                    </tr>
                    </thead>
                    <tbody data-table="table-body">

                    </tbody>
                </table>
            </div>
        </div>
	</div>

	<div class="modal-footer">
		<button id="file-browser-save" type="button" class="btn mr5">
			<img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="">
            <span>[{isys type="lang" ident="LC_UNIVERSAL__ACCEPT"}]</span>
		</button>

		<button type="button" class="btn popup-closer">
			<img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="">
            <span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
		</button>
	</div>
</div>

<script type="text/javascript">
	(function () {
		"use strict";

        const $fileBrowser = $('file-browser');
        const $newFileButton = $('new-file-button');

        idoit.Translate.set('LC_FILEBROWSER__ALL_FILES', '[{isys type="lang" ident="LC_FILEBROWSER__ALL_FILES"}]');
        idoit.Translate.set('LC__CMDB__LIST_ORDER_ASCENDING', '[{isys type="lang" ident="LC__CMDB__LIST_ORDER_ASCENDING"}]');
        idoit.Translate.set('LC__CMDB__LIST_ORDER_DESCENDING', '[{isys type="lang" ident="LC__CMDB__LIST_ORDER_DESCENDING"}]');

        idoit.Require.require('fileBrowser', () => {
            const fileBrowser = new FileBrowser($fileBrowser, { selectedFile: +'[{$parameters.selectedFile}]' });

            [{if !$allowedToUpload}]
            $newFileButton.addClassName('hide');
            [{else}]
            $newFileButton.on('click', function () {
                $newFileButton
                    .down('img')
                    .addClassName('animation-rotate')
                    .writeAttribute('src', window.dir_images + 'axialis/user-interface/loading.svg');

                new Ajax.Request(idoit.Router.getRoute('cmdb.browse-files.upload-modal'), {
                    method:     'get',
                    parameters: { selectedCategory: $fileBrowser.down('.category-list-content .selected').readAttribute('data-category') },
                    onComplete: function (xhr) {
                        $newFileButton
                            .down('img')
                            .removeClassName('animation-rotate')
                            .writeAttribute('src', window.dir_images + 'axialis/basic/symbol-add.svg');

                        Modal.open(xhr.responseText, { maxWidth: 450, maxHeight: 450 });
                    }
                });
            });

            $newFileButton.on('file:uploaded', (ev) => {
                const newObjectId = ev.memo.objectId;
                const selectedCategory = ev.memo.category;

                // Preselect the new file
                fileBrowser.setSelectedFile(newObjectId);

                // Reload categories (in case a new one was added).
                fileBrowser.loadCategories(selectedCategory <= 0 ? 'all' : selectedCategory);

                // Remove any left-over tooltips.
                setTimeout(() => { $('body').fire('update:tooltips'); }, 250);
            });
            [{/if}]

            $('file-browser-save').on('click', function () {
                const selection = fileBrowser.getSelectedFile();

                if (selection !== null) {
                    const $viewElement = $('[{$parameters.returnView}]');
                    const $hiddenElement = $('[{$parameters.returnHidden}]');

                    $viewElement.setValue('[{isys type="lang" ident="LC__CMDB__OBJTYPE__FILE"}] » ' + selection.objectTitle);
                    $hiddenElement.setValue(selection.objectId);
                }

                try {
                    // @see ID-10914 Apply callback code.
                    [{$parameters.callbackAccept}]
                } catch (e) {
                    idoit.Notify.error(e);
                }

                Modal.close($fileBrowser.up('.modal'));
            });
        });

        $fileBrowser.select('.popup-closer').invoke('on', 'click', function () {
			Modal.close($fileBrowser.up('.modal'));
		});
	}());
</script>
