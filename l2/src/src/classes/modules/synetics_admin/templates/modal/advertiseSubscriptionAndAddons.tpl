<div id="advertise-subscription-and-addons">
    <div class="modal-header">
        <h1 class="ml5">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_HEADER"}]</h1>
        <button type="button" class="btn btn-secondary modal-close ml-auto" title="[{isys type="lang" ident="LC__POPUP__CLOSE"}]" data-tooltip="1">
            <img class="fr mouse-pointer" alt="" src="[{$dir_images}]axialis/user-interface/window-control-close.svg" />
        </button>
    </div>
    <div class="modal-content">
        <div id="advertise-subscription-and-addons-image-container">
            <div class="bubbles-image">
                <img src="[{isys_module_pro::getWwwPath()}]images/download-center-info.png" alt="" class="bubbles-image" />
            </div>

            <img src="[{isys_module_synetics_admin::getWwwPath()}]templates/img/subscription-and-add-ons.svg" alt="" class="feature-icon" />
        </div>
        <div class="p20" style="font-size: 1.4rem; line-height: 1.8rem;">
            <p><strong>[{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_A"}]</strong></p>
            <p class="mt15">[{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_B"}]</p>

            <p class="mt20"><img src="[{$dir_images}]axialis/user-interface/button-toggle-on.svg" class="vam" alt="" /> [{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_C"}]</p>
            <p class="mt15"><img src="[{$dir_images}]axialis/user-interface/button-toggle-on.svg" class="vam" alt="" /> [{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_D"}]</p>
            <p class="mt15"><img src="[{$dir_images}]axialis/user-interface/button-toggle-on.svg" class="vam" alt="" /> [{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_E"}]</p>
            <p class="mt15"><img src="[{$dir_images}]axialis/user-interface/button-toggle-on.svg" class="vam" alt="" /> [{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_F"}]</p>
            <p class="mt15"><img src="[{$dir_images}]axialis/user-interface/button-toggle-on.svg" class="vam" alt="" /> [{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_CONTENT_G"}]</p>
        </div>
    </div>
    <div class="modal-footer">
        <a class="btn mr5" href="[{$g_link__administration}]">
            <img src="[{$dir_images}]axialis/user-interface/arrow-up.svg" alt="" />
            <span>[{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_BUTTON"}]</span>
        </a>

        <button type="button" class="btn modal-close">
            <span>[{isys type="lang" ident="LC__SYNETICS_ADMIN__ADVERTISE_SUBSCRIPTION_AND_ADDONS__MODAL_LATER"}]</span>
        </button>
    </div>
</div>
<style>
    [{include file=$cssPath}]
</style>
<script>
    (function () {
        'use strict';

        const $modal = $('advertise-subscription-and-addons');

        $modal.on('click', '.modal-close', function () {
            Modal.close($modal.up('.modal'));
        });
    })();
</script>
