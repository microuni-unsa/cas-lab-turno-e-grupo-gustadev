[{isys_group name="tom.popup.upload-modal"}]
<div id="file-upload">
    <div class="modal-header">
        <h1>[{isys type="lang" ident="LC_FILEBROWSER__UPLOAD_NEW_FILE"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img class="fr mouse-pointer" alt="" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

    <div class="modal-content">
        <div class="droppable-overlay">

            <div>
                [{isys type="lang" ident="LC__CMDB__CATS__FILE__DRAG_FILE_HERE"}]<br /><br />

                [{isys type="f_file_ajax" name="file-upload-field" uploadType="cmdb.global-file-category"}]<br />

                <strong>[{isys type="lang" ident="LC__CMDB__CATS__FILE__MAX_FILE_SIZE"}] [{$uploadMaxSize}]</strong>
            </div>

        </div>
        <div class="form pr20 hide">
            <table class="contentTable">
                <tr>
                    <td class="key">[{isys type="f_label" name="selected-file" ident="LC_FILEBROWSER__SELECTED_FILE"}]</td>
                    <td class="value pl20">
                        <div class="input-group">
                            [{isys type="f_text" name="selected-file"}]
                            <button id="clear-selected-file" type="button" class="btn" title="[{isys type="lang" ident="LC_FILEBROWSER__UPLOAD_NEW_FILE"}]" data-tooltip="1">
                                <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="[{isys type="lang" ident="LC_FILEBROWSER__UPLOAD_NEW_FILE"}]">
                            </button>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td class="key">[{isys type="f_label" name="selected-file-name" ident="LC__CMDB__CATS__FILE_NAME"}]</td>
                    <td class="value pl20">[{isys type="f_text" name="selected-file-name"}]</td>
                </tr>
                <tr>
                    <td class="key">[{isys type="f_label" name="" ident="LC__CMDB__CATS__FILE_CATEGORY"}]</td>
                    <td class="value pl20">[{isys type="f_popup" p_strPopupType="dialog_plus" name="selected-file-category"}]</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="modal-footer">
        <button id="file-upload-button" type="button" class="btn mr5" disabled>
            <img src="[{$dir_images}]axialis/basic/symbol-ok.svg" alt="">
            <span>[{isys type="lang" ident="LC_FILEBROWSER__UPLOAD_AND_SAVE"}]</span>
        </button>

        <button type="button" class="btn popup-closer">
            <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="">
            <span>[{isys type="lang" ident="LC__UNIVERSAL__BUTTON_CANCEL"}]</span>
        </button>
    </div>
</div>
[{/isys_group}]

<script type="text/javascript">
    (function () {
        'use strict';

        const $fileUpload = $('file-upload');
        const $fileUploadOverlay = $fileUpload.down('.droppable-overlay');
        const $fileUploadForm = $fileUpload.down('.form');
        const $fileUploadField = $('file-upload-field');
        const $fileUploadButton = $('file-upload-button');
        let uploader;

        const $clearSelectedFile = $('clear-selected-file');
        const $selectedFile = $('selected-file');
        const $selectedFileName = $('selected-file-name');
        const $selectedFileCategory = $('selected-file-category');

        $('body').fire('update:tooltips');

        $fileUpload.select('.popup-closer').invoke('on', 'click', function () {
            Modal.close($fileUpload.up('.modal'));
        });

        const resetUpload = () => {
            // Revert the 'hidden' upload button, is part of the 'filesUpload.js' code
            $fileUploadField.down('.qq-upload-button').removeClassName('hide');
            // Clear the stored files.
            uploader.clearStoredFiles();

            $fileUploadOverlay.removeClassName('hide');
            $fileUploadForm.addClassName('hide');
            $fileUploadButton.disable();

            $fileUploadButton
                .down('img')
                .removeClassName('animation-rotate')
                .writeAttribute('src', window.dir_images + 'axialis/basic/symbol-add.svg');
        }

        // Define some actions to 'reset' the upload state.
        $clearSelectedFile.on('click', () => resetUpload());
        $fileUploadField.on('uploader:onCancel', () => resetUpload());
        $fileUploadField.on('uploader:onCancel', () => resetUpload());

        $fileUploadField.on('uploader:onSubmit', (ev) => {
            uploader = ev.memo.uploader;

            $selectedFile.setValue(ev.memo.filename);
            $selectedFileName.writeAttribute('placeholder', ev.memo.filename).setValue('');

            $fileUploadOverlay.addClassName('hide');
            $fileUploadForm.removeClassName('hide');
            $fileUploadButton.enable();
        });

        $fileUploadButton.on('click', function () {
            $fileUploadButton
                .down('img')
                .addClassName('animation-rotate')
                .writeAttribute('src', window.dir_images + 'axialis/user-interface/loading.svg');

            uploader.setParams({
                'object-type-id': +'[{$smarty.const.C__OBJTYPE__FILE}]',
                'object-title': $selectedFileName.getValue(),
                'file-category': $selectedFileCategory.getValue()
            });

            uploader.uploadStoredFiles();
        });

        $fileUploadField.on('uploader:onComplete', (ev) => {
            const $newFileButton = $('new-file-button');

            // Reset the upload modal.
            resetUpload();

            // We use the 'file button' to communicate our changes to the file browser.
            if ($newFileButton) {
                Modal.close($fileUpload.up('.modal'));
                $newFileButton.fire('file:uploaded', { category: $selectedFileCategory.getValue(), objectId: ev.memo.json.data.callbackResult });
            }
        });
    }());
</script>
