'use strict';

function smartyFilesUpload(id, type, options) {
    const $container = $(id);

    if (!$container) {
        return;
    }

    const uploader = new qq.FileUploader({
        element: $container.down('.container'),
        params: {type: type},
        action: options.ajaxURL,
        multiple: false,
        autoUpload: false,
        sizeLimit: (options.sizeLimit || 0),
        allowedExtensions: (options.validExtensions || []),
        onSubmit: function (id, filename) {
            $container.fire('uploader:onSubmit', { uploader: uploader, id: id, filename: filename });
            $uploadButton.removeClassName('hide');
            $fileSearchButton.addClassName('hide');
        },
        onComplete: function (id, filename, json) {
            if (json.success) {
                $container.fire('uploader:onComplete', { uploader: uploader, id: id, filename: filename, json: json });
                idoit.callbackManager.triggerCallback('smarty-ajax-file-upload', json.data);
            } else {
                idoit.Notify.error(json.message);
            }
            uploader.clearStoredFiles();
            $uploadButton.addClassName('hide');
            $fileSearchButton.removeClassName('hide');
        },
        onCancel: function (id, filename) {
            $container.fire('uploader:onCancel', { uploader: uploader, id: id, filename: filename });
            $uploadButton.addClassName('hide');
            $fileSearchButton.removeClassName('hide');
        },
        onError: function (id, filename, xhr) {
            $container.fire('uploader:onCancel', { uploader: uploader, id: id, filename: filename, xhr: xhr });
            idoit.Notify.error('Failed to upload');

            uploader.clearStoredFiles();
            $uploadButton.addClassName('hide');
            $fileSearchButton.removeClassName('hide');
        },
        dragText: idoit.Translate.get('LC_FILEBROWSER__DROP_FILE'),
        multipleFileDropNotAllowedMessage: idoit.Translate.get('LC_FILEBROWSER__SINGLE_FILE_UPLOAD'),
        uploadButtonText: '<img src="' + window.dir_images + 'axialis/basic/zoom.svg" alt="" />' +
                          '<span class="text-normal">' + idoit.Translate.get('LC__UNIVERSAL__FILE_ADD') + '</span>',
        cancelButtonText: '&nbsp;',
        failUploadText: idoit.Translate.get('LC__UNIVERSAL__ERROR')
    });

    $container.down('.btn:not(.qq-upload-button)').on('click', function () {
        idoit.callbackManager.triggerCallback('smarty-ajax-file-upload-before-upload', uploader);
        uploader.uploadStoredFiles();
    });

    // Re-select this button, because it gets created by the file-uploader.
    const $uploadButton = $container.down('button');
    const $fileSearchButton = $container.down('.qq-upload-button');
}
