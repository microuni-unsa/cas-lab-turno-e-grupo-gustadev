<div id="popup-location-error">
    <div class="popup-header-ng">
        <h1>[{isys type="lang" ident="LC__CMDB__CATG__SPATIALLY_CONNECTED_OBJECTS_ERROR_1"}]</h1>
        <button type="button" class="btn btn-secondary popup-closer ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img alt="close" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>

    <div class="popup-content p10">
        <p>[{isys type="lang" ident="LC__CMDB__CATG__SPATIALLY_CONNECTED_OBJECTS_ERROR_2"}]</p>
    </div>

    <div class="popup-footer-ng">
        <button type="button" class="btn popup-closer">
            <img src="[{$dir_images}]axialis/basic/symbol-cancel.svg" alt="" /><span>[{isys type="lang" ident="LC_UNIVERSAL__ABORT"}]</span>
        </button>
    </div>
</div>

<script type="text/javascript">
    (function () {
        'use strict';

        $('popup-location-error').on('click', '.popup-closer', function () {
            popup_close();
        });
    })();
</script>
