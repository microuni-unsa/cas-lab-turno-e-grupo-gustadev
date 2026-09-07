<table class="contentTable">
    [{if isys_glob_is_edit_mode()}]
    <tr>
        <td class="key">[{isys type='lang' ident='LC__CMDB__CATG__IMAGE_UPLOADED_IMAGES'}]</td>
        <td class="value">[{isys type='f_dialog' p_bDbFieldNN=0 name='C__CATG__IMAGE_SELECTION' id='C__CATG__IMAGE_SELECTION'}]</td>
    </tr>
    <tr>
        <td class="key">[{isys type='f_label' name='C__CATG__IMAGE_SELECTION' ident='LC__CMDB__CATG__IMAGE_OBJ_FILE'}]</td>
        <td class="value pl20">
            <div class="py5" style="height: 40px;">
                [{isys type="f_file_ajax" name="catg-image-upload" uploadType="cmdb.object-image"}]
            </div>
            <p class="p5 text-blue display-flex align-items-center">
                <img src="[{$dir_images}]axialis/basic/button-info.svg" class="mr5" /><span>[{isys type="lang" ident="LC__CMDB__CATG__IMAGE_DESCRIPTION"}]</span>
            </p>
        </td>
    </tr>
    [{else}]
    <tr>
        <td class="key">[{isys type='lang' ident="LC__CMDB__CATG__IMAGE_OBJ_FILE"}]</td>
        <td class="value pl20">
            [{if $imageName}]
            <p>[{$imageName}]</p>
            [{/if}]

            [{if $imageName && $downloadUrl}]
            <a class="btn mt10" href="[{$downloadUrl}]" target="_blank">
                <img src="[{$dir_images}]axialis/basic/symbol-download.svg" alt="" />
                <span>[{isys type="lang" ident="LC__UNIVERSAL__DOWNLOAD_FILE"}]</span>
            </a>
            [{/if}]
        </td>
    </tr>
    [{/if}]
</table>

<script type="text/javascript">
    (function () {
        'use strict';

        const $imageSelection = $('C__CATG__IMAGE_SELECTION');
        const $imageHeader = $('object_image_header');
        const $uploader = $('catg-image-upload');

        if ($imageSelection && $imageHeader) {
            $imageSelection.on('change', function (ev) {
                const imagePath = $imageSelection.getValue();

                if (imagePath === '-1') {
                    $imageHeader.writeAttribute('src', window.dir_images + 'objecttypes/empty.png')
                } else {
                    $imageHeader.writeAttribute('src', idoit.Router.getRoute('cmdb.object.image-name', { filename: imagePath }))
                }
            });
        }

        if ($uploader) {
            $uploader.on('uploader:onSubmit', function (ev) {
                // Set 'object-id' (GET-) parameter.
                ev.memo.uploader.setParams({
                    'object-id': +'[{$object_id}]',
                    'type': 'cmdb.object-image'
                });
            });

            $uploader.on('uploader:onComplete', function (ev) {
                const result = ev.memo.json.data.callbackResult;

                if (!result) {
                    idoit.Notify.warning('[{isys type="lang" ident="LC__UNIVERSAL__FILE_UPLOAD__FAILED"}]')
                    return;
                }

                // Create new image entry.
                $imageSelection.insert(new Element('option', { value: result }).update(result));

                // Select newly uploaded image.
                $imageSelection.setValue(result);
                $imageSelection.highlight();
            });
        }
    })();
</script>
